<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;

use MakinaCorpus\Drupal\Sf\Routing\Router;

/**
 * Drupal path extension WITH Symfony router
 */
class DrupalPathExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('path', [$this, 'createUrl'], ['is_safe' => ['html']]),
        ];
    }

    public function createUrl($route, array $parameters = [])
    {
        $options = [];

        if ($parameters) {
            $tokens = [];

            foreach ($parameters as $key => $value) {
                $token = '%' . $key;

                if (false === strpos($route, $token)) {
                    // We must, as per twig path() function signature, add unused
                    // parameters as GET parameters
                    $options['query'][$key] = $value;
                } else {
                    $tokens[$token] = $value;
                }
            }

            $route = strtr($route, $tokens);
        }

        return url($route, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_path';
    }
}
