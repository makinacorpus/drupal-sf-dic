<?php

namespace Drupal\Core;

/**
 * Defines an object that holds information about a URL.
 *
 * @rewritten
 */
class Url
{
    private $routeName;
    private $routeParameters = [];
    private $options = [];
    private $external = false;
    private $uri;

    /**
     * Constructs a new Url object.
     *
     * In most cases, use Url::fromRoute() or Url::fromUri() rather than
     * constructing Url objects directly in order to avoid ambiguity and make your
     * code more self-documenting.
     *
     * @param string $route_name
     *   The name of the route
     * @param array $route_parameters
     *   (optional) An associative array of parameter names and values.
     * @param array $options
     *   (optional) An associative array of additional options, with the following
     *   elements:
     *   - 'query': An array of query key/value-pairs (without any URL-encoding)
     *     to append to the URL. Merged with the parameters array.
     *   - 'fragment': A fragment identifier (named anchor) to append to the URL.
     *     Do not include the leading '#' character.
     *   - 'absolute': Defaults to FALSE. Whether to force the output to be an
     *     absolute link (beginning with http:). Useful for links that will be
     *     displayed outside the site, such as in an RSS feed.
     *   - 'language': An optional language object used to look up the alias
     *     for the URL. If $options['language'] is omitted, it defaults to the
     *     current language for the language type LanguageInterface::TYPE_URL.
     *   - 'https': Whether this URL should point to a secure location. If not
     *     defined, the current scheme is used, so the user stays on HTTP or HTTPS
     *     respectively. TRUE enforces HTTPS and FALSE enforces HTTP.)
     */
    public function __construct($route_name, $route_parameters = [], $options = [])
    {
        $this->routeName = $route_name;
        $this->routeParameters = $route_parameters;
        $this->options = $options;
    }

    /**
     * Indicates if this Url is external.
     *
     * @return bool
     */
    public function isExternal()
    {
        return $this->external;
    }

    /**
     * Indicates if this Url has a Drupal route.
     *
     * @return bool
     */
    public function isRouted()
    {
        return !$this->external;
    }

    /**
     * Returns the route name.
     *
     * @return string
     *
     * @throws \UnexpectedValueException.
     *   If this is a URI with no corresponding route.
     */
    public function getRouteName()
    {
        if ($this->external) {
            throw new \UnexpectedValueException('External URLs do not have an internal route name.');
        }

        return $this->routeName;
    }

    /**
     * Returns the route parameters.
     *
     * @return array
     *
     * @throws \UnexpectedValueException.
     *   If this is a URI with no corresponding route.
     */
    public function getRouteParameters()
    {
        if ($this->external) {
            throw new \UnexpectedValueException('External URLs do not have internal route parameters.');
        }

        return $this->routeParameters;
    }

    /**
     * Returns the URL options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Gets a specific option.
     *
     * @param string $name
     *   The name of the option.
     *
     * @return mixed
     *   The value for a specific option, or NULL if it does not exist.
     */
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Returns the URI value for this Url object.
     *
     * Only to be used if self::$unrouted is TRUE.
     *
     * @return string
     *   A URI not connected to a route. May be an external URL.
     *
     * @throws \UnexpectedValueException
     *   Thrown when the URI was requested for a routed URL.
     */
    public function getUri()
    {
        if (!$this->external) {
            throw new \UnexpectedValueException('This URL has a Drupal route, so the canonical form is not a URI.');
        }

        return $this->uri;
    }

    /**
     * Sets the value of the absolute option for this Url.
     *
     * @param bool $absolute
     *   (optional) Whether to make this Url absolute or not. Defaults to TRUE.
     *
     * @return $this
     */
    public function setAbsolute($absolute = true)
    {
        $this->options['absolute'] = $absolute;

        return $this;
    }
}
