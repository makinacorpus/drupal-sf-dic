<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;

use MakinaCorpus\Drupal\Sf\Routing\Router;

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
        return Router::generateDrupalUrl($route, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_path';
    }
}
