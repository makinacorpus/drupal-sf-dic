<?php

namespace MakinaCorpus\Drupal\Sf\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Provides a set of helpers you may apply on controllers to transparently
 * use Drupal 'destination' parameter redirection, with fallback on the
 * HTTP referer whenever possible.
 *
 * This trait is dependent from the Symfony router, but in real life you may
 * use it transparently without, providing a less precise but working
 * Drupal-only implementation.
 */
trait RefererControllerTrait
{
    /**
     * Redirect to route, method from the Symfony controller
     *
     * @param string $route
     * @param string[] $parameters
     * @param int $status
     *
     * @return RedirectResponse
     */
    abstract protected function redirectToRoute($route, array $parameters = [], $status = 302);

    /**
     * Returns true if the service id is defined.
     *
     * @param string $id
     *
     * @return bool
     */
    abstract protected function has($id);

    /**
     * Get action URL from request
     *
     * @param Request $request
     *   Incomming request
     *
     * @return string
     */
    protected function getActionUrl(Request $request)
    {
        list($route, $arguments) = $this->getRequestRouteAndParams($request);

        $destination = $request->query->get('destination');
        if ($destination) {
            $arguments['destination'] = $destination;
        }

        return $this->generateUrl($route, $arguments);
    }

    /**
     * Get current request route and parameters
     *
     * @param Request $request
     *   Incomming request
     *
     * @return null|array
     *   If found, first value of the array is the current route, second
     *   value is an array of the parameters values
     */
    private function getRequestRouteAndParams(Request $request)
    {
        $route = $request->attributes->get('_route');

        if ($route) {
            return [
                $route,
                $request->attributes->get('_route_params'),
            ];
        }

        $forwarded = $request->attributes->get('_forwarded');
        if ($forwarded instanceof ParameterBag) {
            return [
                $forwarded->get('_route'),
                $forwarded->get('_route_params', []),
            ];
        }
    }

    /**
     * Get route and parameters from the referer header
     *
     * @param Request $request
     *   Incomming request
     *
     * @return null|array
     *   If found, first value of the array is the current route, second
     *   value is an array of the parameters values
     */
    private function getRefererRouteAndParams(Request $request)
    {
        if (!$this->has('router')) {
            return;
        }

        $referer = $request->headers->get('referer');
        if (empty($referer)) {
            throw new \RuntimeException("Client browser provided no referer");
        }

        $baseUrl = $request->getBaseUrl();

        if ($baseUrl) {
            $pos = strpos($referer, $baseUrl);
        } else {
            $pos = 0;
        }

        $lastPath = substr($referer, $pos + strlen($baseUrl));

        return $this->get('router')->match($lastPath);
    }

    /**
     * Redirect to the given destination parameter
     *
     * @param Request $request
     *   Incomming request
     * @param int $status
     *   HTTP redirect status code
     *
     * @return RedirectResponse
     */
    protected function redirectToDestination(Request $request, $status = 302)
    {
        if (!$request->query->has('destination')) {
            throw new \RuntimeException("Request query does not have any 'destination' parameter");
        }

        return new RedirectResponse(
            $request->getBaseUrl() . '/' . trim($request->query->get('destination'), '/')
        );
    }

    /**
     * Redirect to the Drupal batch API batch process page, if a batch is set
     *
     * @param Request $request
     *   Incomming request
     * @param int $status
     *   HTTP redirect status code
     *
     * @return RedirectResponse
     */
    protected function redirectToBatch(Request $request)
    {
        $batch = batch_get();

        if ($batch && $batch['id']) {
            return $this->redirectToRoute('batch', ['id' => $batch['id']]);
        }

        return $this->redirectToReferer($request);
    }

    /**
     * Redirect to destination or referer
     *
     * @param Request $request
     *   Incomming request
     * @param string $defaultRoute
     *   Default route if no route found
     * @param mixed[] $defaultParameters
     *   Default parameters if no route found
     * @param int $status
     *   HTTP redirect status code
     * @param bool $useDestination
     *   If set to true, attempt to detect the Drupal 'destination' parameter
     *   instead of using the referer, then fallback on the referer
     *
     * @return RedirectResponse
     */
    protected function redirectToReferer(Request $request, $defaultRoute = null, array $defaultParameters = [], $status = 302, $useDestination = true)
    {
        if ($useDestination && $request->query->has('destination')) {
            return $this->redirectToDestination($request);
        }

        try {
            $params = $this->getRefererRouteAndParams($request);

            return new RedirectResponse($this->generateUrl($params['_route'], ['slug' => $params['slug']]), $status);

        } catch (\RuntimeException $e) {

            // Anything could go wrong, ensure at least fallback will work
            if ($defaultRoute) {
                return new RedirectResponse($this->generateUrl($defaultRoute, $defaultParameters), $status);
            }

            list($route, $arguments) = $this->getRequestRouteAndParams($request);

            return new RedirectResponse($this->generateUrl($route, $arguments), $status);
        }
    }
}
