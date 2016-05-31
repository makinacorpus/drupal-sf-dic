<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;

/**
 * Drupal file url
 */
class DrupalFileExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('file_url', [$this, 'createFileUrl'], ['is_safe' => ['html']]),
        ];
    }

    public function createFileUrl($uri)
    {
        return file_create_url($uri);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_file';
    }
}
