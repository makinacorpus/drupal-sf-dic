<?php

namespace Drupal\Core;

/**
 * Defines an object that holds information about a link.
 *
 * @rewritten
 */
class Link
{
    /**
     * The text of the link.
     *
     * @var string
     */
    private $text;

    /**
     * The URL of the link.
     *
     * @var \Drupal\Core\Url
     */
    private $url;

    /**
     * Constructs a new Link object.
     *
     * @param string $text
     *   The text of the link.
     * @param \Drupal\Core\Url $url
     *   The url object.
     */
    public function __construct($text, Url $url) {
      $this->text = $text;
      $this->url = $url;
    }

    /**
     * Creates a Link object from a given route name and parameters.
     *
     * @param string $text
     *   The text of the link.
     * @param string $route_name
     *   The name of the route
     * @param array $route_parameters
     *   (optional) An associative array of parameter names and values.
     * @param array $options
     *   The options parameter takes exactly the same structure.
     *   See \Drupal\Core\Url::fromUri() for details.
     *
     * @return static
     */
    public static function createFromRoute($text, $route_name, $route_parameters = [], $options = []) {
      return new static($text, new Url($route_name, $route_parameters, $options));
    }

    /**
     * Creates a Link object from a given Url object.
     *
     * @param string $text
     *   The text of the link.
     * @param \Drupal\Core\Url $url
     *   The Url to create the link for.
     *
     * @return static
     */
    public static function fromTextAndUrl($text, Url $url) {
      return new static($text, $url);
    }

    /**
     * Returns the text of the link.
     *
     * @return string
     */
    public function getText() {
      return $this->text;
    }

    /**
     * Returns the URL of the link.
     *
     * @return \Drupal\Core\Url
     */
    public function getUrl() {
      return $this->url;
    }
}
