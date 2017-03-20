<?php

namespace MakinaCorpus\Drupal\Sf\Routing;

use Symfony\Bundle\FrameworkBundle\Routing\Router as BaseRouter;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class Router extends BaseRouter
{
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
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        try {
            return $this->getGenerator()->generate($name, $parameters, $referenceType);
        } catch (RouteNotFoundException $e) {
            // @todo
            //   should we use drupal_valid_path() ?
            // Drupal to the rescue
            // @todo From what I remember, there was a few other stuff to take
            // care of in this... can't really remember what...
            return self::generateDrupalUrl($name, $parameters, $referenceType);
        }
    }
}
