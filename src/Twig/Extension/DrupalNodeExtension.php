<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;

/**
 * Drupal image style display
 */
class DrupalNodeExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('node_view', [$this, 'nodeView'], ['is_safe' => ['html']]),
        ];
    }

    public function nodeView($node, $view_mode = 'full')
    {
        if (empty($node)) {
            return '';
        }

        if (is_array($node)) {
            $build = node_view_multiple($node, $view_mode);
        }
        else {
            $build = node_view($node, $view_mode);
        }
        return drupal_render($build);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_node';
    }
}
