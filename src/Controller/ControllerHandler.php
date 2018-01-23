<?php

namespace MakinaCorpus\Drupal\Sf\Controller;

use MakinaCorpus\Drupal\Sf\DrupalPageResponse;
use MakinaCorpus\Drupal\Sf\DrupalResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Converts Drupal menu item page arguments to a Symfony controller.
 */
class ControllerHandler
{
    private $argumentResolver;
    private $container;
    private $currentError;
    private $dispatcher;
    private $doHandleExceptions = false;
    private $exitCallback = 'drupal_exit';
    private $httpKernel;

    /**
     * Default constructor
     *
     * @param ArgumentResolverInterface $argumentResolver
     * @param ContainerInterface $container
     * @param EventDispatcherInterface $dispatcher
     * @param callable $exitCallback
     */
    public function __construct(
        ArgumentResolverInterface $argumentResolver,
        ContainerInterface $container,
        EventDispatcherInterface $dispatcher,
        HttpKernelInterface $httpKernel,
        $exitCallback = 'drupal_exit',
        $doHandleExceptions = false
    ) {
        // Allow this behavior to be set within settings.php if not set
        // elsewhere, note that in unit tests, variable_get() will not
        // exist
        if (!$doHandleExceptions && function_exists('variable_get')) {
            $doHandleExceptions = variable_get('kernel.handle_exceptions', false);
        }

        $this->argumentResolver = $argumentResolver;
        $this->container = $container;
        $this->dispatcher = $dispatcher;
        $this->doHandleExceptions = $doHandleExceptions;
        $this->exitCallback = $exitCallback;
        $this->httpKernel = $httpKernel;
    }

    /**
     * Normalize controller
     *
     * @param string|callable $controller
     *   Controller
     * @param string $defaultMethodName
     *   Default controller method name
     * @param string $defaultMethodSuffix
     *   Default controller method suffix
     *
     * @return callable
     *   Normalize controller
     */
    private function normalizeController($controller, $defaultMethodName = 'render', $defaultMethodSuffix = 'Action')
    {
        $method = null;

        // CLASS::STATIC_METHOD is not supported here.
        if (is_string($controller) && false !== strpos($controller, '::')) {
            list($controller, $method) = explode('::', $controller, 2);
        }

        if (is_string($controller)) {
            if (class_exists($controller)) {
                $controller = new $controller();
            } else if (class_exists($controller . 'Controller')) {
                $controller .= 'Controller';
                $controller = new $controller();
            } else if ($this->container->has($controller)) {
                $controller = $this->container->get($controller);
            }
        } else if (!is_callable($controller)) {
            throw new \InvalidArgumentException(sprintf("%s: is not callable or class does not exist", $controller));
        }

        if (null === $method && $defaultMethodName) {
            $method = $defaultMethodName;
        }

        if (is_object($controller)) {
            // Attempt to derivate method name using the 'Action' suffix.
            if (!method_exists($controller, $method)) {
                $method = $method . $defaultMethodSuffix;
                if (!method_exists($controller, $method)) {
                    // Sorry, but there's nothing we can execute...
                    throw new \InvalidArgumentException(sprintf(
                        "%s::%s(), %s::%s%s(), %s::%s(): method does not exists",
                        get_class($controller), $method, get_class($controller), $method, $defaultMethodSuffix, get_class($controller), $defaultMethodName
                    ));
                }
            }

            // We cannot use the ContainerAwareInterface since in SF3 the recommended way
            // is to use the ContainerAwareTrait which won't give any meta information on
            // the object about weither or not we should inject the container.
            if (method_exists($controller, 'setContainer')) {
                $controller->setContainer($this->container);
            }

            // Controller is a callable.
            $controller = [$controller, $method];
        }

        return $controller;
    }

    /**
     * Inject request into arguments
     *
     * @param callable $controller
     *   Controller
     * @param array $arguments
     *   Original arguments
     * @param Request $request
     *   Request
     *
     * @return array
     *   Arguments with injected request if one is typed as such
     *
     * @deprecated
     *   All controllers should use the resolver instead, and must not rely
     *   upon the menu router arguments; Drupal 8 deprecated this as well.
     */
    private function injectRequestIntoArguments($controller, array $arguments, Request $request)
    {
        $reflection = null;

        // This is derived from Symfony's ArgumentMetadataFactory class.
        if (is_array($controller)) {
            $reflection = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && !$controller instanceof \Closure) {
            $reflection = (new \ReflectionObject($controller))->getMethod('__invoke');
        } else {
            $reflection = new \ReflectionFunction($controller);
        }

        foreach (array_values($reflection->getParameters()) as $index => $rp) {
            /* @var $rp \ReflectionParameter */
            if ($rc = $rp->getClass()) {
                if (Request::class === $rc->getName()) {
                    array_splice($arguments, $index, 0, [$request]);
                }
            }
        }

        return $arguments;
    }

    /**
     * Has the last execution throw an error
     *
     * @return bool
     */
    public function hasError()
    {
        return null !== $this->currentError;
    }

    /**
     * Get the last execution error
     *
     * @return null|\Throwable
     */
    public function getError()
    {
        return $this->currentError;
    }

    /**
     * Execute controller, but do not handle response
     *
     * @param string|callable $controller
     *   Controller
     * @param Request $request
     *   Incomming request
     * @param array $funcArguments
     *   Drupal menu item given arguments
     * @param string $defaultMethodName
     *   Default controller method name
     * @param string $defaultMethodName
     *   Default controller method suffix
     *
     * @return string|Response
     */
    public function execute($controller, Request $request, array $funcArguments = [], $defaultMethodName = null, $defaultMethodSuffix = null)
    {
        $this->currentError = null;

        $controller = $this->normalizeController($controller, $defaultMethodName, $defaultMethodSuffix);

        // Try to make the controller work with the argument resolver, in case
        // of failure fallback on legacy Drupal menu item page arguments method.
        // Legacy menu page arguments is deprecated from Drupal 8.4.x, see
        //   https://www.drupal.org/node/2720233
        try {
            $arguments = $this->argumentResolver->getArguments($request, $controller);
        } catch (\RuntimeException $e) {

            // If arguments fail to resolve, just switch to legacy menu item page
            // arguments, but in case the router item has no arguments defined, this
            // means that it was not meant to work this way, just fail in this case.
            // In all cases, we must keep the legacy behavior which is to append the
            // request manually if asked. This is a bad behavior and should be marked
            // as deprecated.
            $arguments = $this->injectRequestIntoArguments($controller, $funcArguments, $request);

            // Empty arguments while the argument resolver failed means we cannot
            // fullfill controller requirement, instead of letting it throw PHP
            // warnings, just propagate the exception.
            if (empty($arguments)) {
                throw new NotFoundHttpException('Not Found', $e);
            }
        }

        return call_user_func_array($controller, $arguments);
    }

    /**
     * Execute controller and handle reponse
     *
     * @param string|callable $controller
     *   Controller
     * @param Request $request
     *   Incomming request
     * @param array $funcArguments
     *   Drupal menu item given arguments
     * @param string $defaultMethodName
     *   Default controller method name
     * @param string $defaultMethodName
     *   Default controller method suffix
     *
     * @return null|int|string|Response
     */
    public function handle($controller, Request $request, array $funcArguments = [], $defaultMethodName = null, $defaultMethodSuffix = null)
    {
        try {
            $response = $this->execute($controller, $request, $funcArguments, $defaultMethodName, $defaultMethodSuffix);

            if (!$response instanceof Response) {
                $response = new DrupalResponse($response);
            }

            return $this->prepareResponseForDrupal($request, $response);

        } catch (\Exception $e) {
            return $this->handleException($request, $e);
        }
    }

    /**
     * Handle non-catchable (ie. nor 403 nor 404) errors
     *
     * This stripped-down code from Symfony, all credits to its original author.
     *
     * @param Request $request
     * @param \Throwable $exception
     *
     * @return Response
     */
    public function handleError(Request $request, $exception)
    {
        $event = new GetResponseForExceptionEvent($this->httpKernel, $request, HttpKernelInterface::MASTER_REQUEST, $exception);
        $this->dispatcher->dispatch(KernelEvents::EXCEPTION, $event);

        // a listener might have replaced the exception
        $exception = $event->getException();

        if (!$event->hasResponse()) {
            throw $exception;
        }

        $response = $event->getResponse();

        // the developer asked for a specific status code
        if ($response->headers->has('X-Status-Code')) {
            $response->setStatusCode($response->headers->get('X-Status-Code'));

            $response->headers->remove('X-Status-Code');
        } elseif (!$response->isClientError() && !$response->isServerError() && !$response->isRedirect()) {
            // ensure that we actually have an error response
            if ($exception instanceof HttpExceptionInterface) {
                // keep the HTTP status code and headers
                $response->setStatusCode($exception->getStatusCode());
                $response->headers->add($exception->getHeaders());
            } else {
                $response->setStatusCode(500);
            }
        }

        return $response;
    }

    /**
     * Handle response
     *
     * @param Request $request
     * @param \Throwable $exception
     *
     * @return null|int|string|Response
     */
    public function handleException(Request $request, $exception)
    {
        $this->currentError = $exception;

        /** @var \Throwable $e */
        if ($exception instanceof AccessDeniedException) {
            return MENU_ACCESS_DENIED;

        } else if ($exception instanceof AccessDeniedHttpException) {
            return MENU_ACCESS_DENIED;

        } else if ($exception instanceof NotFoundHttpException) {
            return MENU_NOT_FOUND;

        } else {
            if ($exception instanceof HttpException) {
                switch ($exception->getStatusCode()) {

                    case 403:
                        return MENU_ACCESS_DENIED;

                    case 404:
                        return MENU_NOT_FOUND;
                }
            }

            if ($this->doHandleExceptions) {
                return $this->handleError($request, $exception);
            }

            throw $exception;
        }
    }

    /**
     * Handle response
     *
     * @param Request $request
     * @param int|string|array|Response $response
     *
     * @return null|int|string|Response
     */
    public function prepareResponseForDrupal(Request $request, $response)
    {
        $isFragmentRoute = false;

        // Allow fragment renderers to work.
        if ($this->container->hasParameter('fragment.path')) {
            $fragmentPath = $this->container->getParameter('fragment.path');
            $isFragmentRoute = current_path() === trim($fragmentPath, '/');
        }

        // Deprecated backward compatibility
        if (is_int($response) || is_string($response) || is_array($response)) {
            return $response;
        }

        if (!$response instanceof Response) {
            throw new \LogicException(sprintf("Response is not a %s instance, nor an int, nor a string, nor an array", Response::class));
        }

        // @todo Partial support only of symfony http response, headers and others
        //   are ignored, we should probably have a custom delivery callback in order
        //   to fully support this.
        if (/* \Drupal::request()->isXmlHttpRequest() || */
            $isFragmentRoute ||
            $response instanceof JsonResponse ||
            $response instanceof BinaryFileResponse ||
            $response instanceof StreamedResponse ||
            $response instanceof RedirectResponse ||
            ($response instanceof Response && 0 === strpos($response->headers->get('Content-Type'), 'application/xml')) ||
            ($response instanceof Response && 0 === strpos($response->headers->get('Content-Type'), 'application/json')) ||
            // Ignoring the XmlHttpRequest should always be the right method, but
            // sorry, I have to do otherwise as of now. As a side note, stripos()
            // is very fast, and substr() is much much faster, so this should not
            // be noticeable in any way in term of performances.
            (($buffer = substr($response->getContent(), 0, 100)) && false !== stripos($buffer, 'Symfony Web Debug Toolbar'))
        ){
            // Very sad hack, but we do need this, in case the response in not
            // handled by Drupal itself we must commit the session prior to
            // sending the response, in order to ensure that session cookies
            // are sent along with it.
            drupal_session_commit();

            $response->send();
            if ($this->exitCallback) {
                call_user_func($this->exitCallback);
            }
            return;
        }

        // Normal response handling
        if ($response instanceof Response) {

            // First attempt to get status code we may have missed
            $status = $response->getStatusCode();

            if (($status >= 300 && $status < 400) ||
                // Same note as above, substr() and stripos() are really fast.
                (($buffer = substr($response->getContent(), -100)) && false !== stripos($response->getContent(), '</html>'))
            ){
                $response->send();
                if ($this->exitCallback) {
                    call_user_func($this->exitCallback);
                }
                return;
            }

            // If nothing happend, this is probably a valid response just send
            // it to the browser as normal, we just may need to convert it to
            // string first so that Drupal delivery callbacks won't fail.
            // Do not do it when the request is an AJAX request, nobody wants
            // a full page during those.
            if (!$request->isXmlHttpRequest()) {
                $response = DrupalPageResponse::create($response->getContent(), $response->getStatusCode(), $response->headers->allPreserveCase());
            }
        }

        return $response;
    }
}
