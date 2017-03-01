<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Provides a few helpers to be able to foward rendering to controllers without
 * breaking the Request chain, ie. allowing other requests than GET to propagate
 * like HMVC rendering. Please note this also disallow normal fragment renderer
 * cache abilities for those requests.
 */
class HttpRenderExtension extends \Twig_Extension
{
    private $kernel;
    private $requestStack;

    /**
     * Default constructor
     *
     * @param HttpKernelInterface $kernel
     * @param RequestStack $requestStack
     */
    public function __construct(HttpKernelInterface $kernel, RequestStack $requestStack)
    {
        $this->kernel = $kernel;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('http_forward', [$this, 'doForward'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Directly render a controller without using the fragment handler, bypassing
     * any cache it would provide, but allowing POST requests to go down the stack
     * providing a Hierarchical-MVC/PAC like implementation
     *
     * @param string $controller
     *   The controller name (a string like BlogBundle:Post:index)
     * @param array $path
     *   An array of path parameters, if none, will inherit from the master request
     * @param array $query
     *   An array of query parameters, if none, will inherit from the master request
     *
     * @return string
     *   The rendered controller
     */
    public function doForward($controller, array $path = [], array $query = null)
    {
        //
        // Degraded version of \Symfony\Bundle\FrameworkBundle\Controller\Controller::forward()
        // method that just re-execute another controller using the (almost) exact request
        // duplicate, in order for us to catch POST and data.
        //
        // We have three problems to solve at this point:
        //
        //  - when building directly the form from here, we cannot use the SaurFormBundle:Form
        //    controller to handle the submission, and sad story, we don't want to copy/paste
        //    the controller code;
        //
        //  - when using the fragment.handler, the built sub-request cannot be a POST request
        //    because it'd loose compatiblity with ESI or hinclude, reason why it's hardcoded
        //    this way, and we cannot submit forms in there;
        //
        //  - when we do use the fragment.handler, everything goes fine if we force the form
        //    action to be the real form route, but this cause another problems: in case of
        //    erroneous form validation, it forces us to do a redirect request, but this will
        //    loose the form error context (since redirect will do a GET request without data)
        //    and the form won't render with the errors within.
        //
        // To conclude, we don't have any choice than doing some customish code to render the
        // controller and let it do its job as it was the master request (even it's not) and
        // it does work fine.
        //
        // There is an interesting thread about this in Symfony's issues:
        //
        //    https://github.com/symfony/symfony/issues/2147
        //
        // and we are in the exact same context as the original issuer: we actually do some
        // kind of HMVC variant by displaying forms into nodes into fields into blocks into
        // regions, and there is no way to get arround that.
        //

        $request = $this->requestStack->getCurrentRequest();

        $path['_forwarded'] = $request->attributes;
        $path['_controller'] = $controller;
        $subRequest = $request->duplicate($query, null, $path);

        $response = $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);

        return $this->deliverResponse($request, $response);
    }

    /**
     * Deliver response
     *
     * @param Request $request
     * @param Response $response
     *
     * @return string
     */
    protected function deliverResponse(Request $request, Response $response)
    {
        if ($response instanceof RedirectResponse) {
            $url = $response->getTargetUrl();

            if (false === strpos('://', $url)) { // URL is not absolute
                $url = $GLOBALS['base_url'] . $url;
            }

            foreach ($response->headers->getCookies() as $cookie) {
                header('Set-Cookie: ' . $cookie, false);
            }

            drupal_goto($url);
        }

        if (!$response->isSuccessful()) {
            throw new \RuntimeException(sprintf('Error when rendering "%s" (Status code is %s).', $request->getUri(), $response->getStatusCode()));
        }

        return $response->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_http_render';
    }
}
