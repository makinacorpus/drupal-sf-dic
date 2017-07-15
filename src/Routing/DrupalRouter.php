<?php

namespace MakinaCorpus\Drupal\Sf\Routing;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Decorates Symfony's router to manage Drupal URLs transparently
 */
class DrupalRouter implements RouterInterface
{
    /**
     * Generate Drupal URL from route and parameters
     *
     * @param string $name
     *   Route name, which will be used as a Drupal path
     * @param array $parameters
     *   Route parameters
     * @param int $referenceType
     *
     * @return string
     */
    static public function generateDrupalUrl($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        $options = [];

        if ($parameters) {
            $tokens = [];

            foreach ($parameters as $key => $value) {
                $token = '%' . $key;

                if ($key === '_fragment') {
                    // Handle symfony 3.2 _fragment parameter
                    $options['fragment'] = $value;
                } elseif (false === strpos($name, $token)) {
                    // We must, as per twig path() function signature, add unused
                    // parameters as GET parameters
                    $options['query'][$key] = $value;
                } else {
                    $tokens[$token] = $value;
                }
            }

            $name = strtr($name, $tokens);
        }

        if (self::ABSOLUTE_URL === $referenceType) {
            $options['absolute'] = true;
        }

        return url($name, $options);
    }

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Default constructor
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getRouteCollection()
    {
        return $this->router->getRouteCollection();
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function match($pathinfo)
    {
        return $this->router->match($pathinfo);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setContext(RequestContext $context)
    {
        return $this->router->setContext($context);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getContext()
    {
        return $this->router->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        try {
            return $this->router->generate($name, $parameters, $referenceType);
        } catch (RouteNotFoundException $e) {
            // Let it fallback with Drupal URL.
        }

        // @todo
        //   should we use drupal_valid_path() ?
        // Drupal to the rescue
        // @todo From what I remember, there was a few other stuff to take
        // care of in this... can't really remember what...
        return self::generateDrupalUrl($name, $parameters, $referenceType);
    }
}
