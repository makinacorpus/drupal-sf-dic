<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;

/**
 * Drupal various rendering functions
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
        if ($parameters) {
            $tokens = [];
            foreach ($parameters as $key => $value) {
                $tokens['%' . $key] = $value;
            }
            $route = strtr($route, $tokens);
        }

        return url($route);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_path';
    }
}
