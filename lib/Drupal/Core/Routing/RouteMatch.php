<?php

namespace Drupal\Core\Routing;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides an interface for classes representing the result of routing.
 *
 * Routing is the process of selecting the best matching candidate from a
 * collection of routes for an incoming request. The relevant properties of a
 * request include the path as well as a list of raw parameter values derived
 * from the URL. If an appropriate route is found, raw parameter values will be
 * upcast automatically if possible.
 *
 * The route match object contains useful information about the selected route
 * as well as the raw and upcast parameters derived from the incoming
 * request.
 *
 * @ingroup routing
 *
 * @custom
 */
class RouteMatch implements RouteMatchInterface
{
    private $request;

    /**
     * Default constructor
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteName()
    {
        return $this->request->attributes->get('_route');
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($parameter_name)
    {
        return $this->request->get($parameter_name);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->request->query;
    }
}
