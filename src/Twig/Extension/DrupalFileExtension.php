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
            new \Twig_SimpleFunction('file_icon', [$this, 'fileIcon'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('human_size', [$this, 'humanFileSize'], ['is_safe' => ['html']]),
        ];
    }

    public function createFileUrl($uri)
    {
        return file_create_url($uri);
    }

    public function humanFileSize($size, $decimals = 2)
    {
        $sz = 'KMGTP';
        $factor = floor((strlen($size) - 1) / 3);

        return sprintf("%.{$decimals}f", $size / pow(1024, $factor)).@$sz[(int)$factor - 1]."o";
    }

    public function fileIcon($file)
    {
        $file = (object) $file;
        $build = [
            '#theme' => 'file_icon',
            '#file'  => $file,
        ];

        return drupal_render($build);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_file';
    }
}
