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

    public function createUrl($path = null, array $options = [])
    {
        return url($path, $options = []);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_path';
    }
}
