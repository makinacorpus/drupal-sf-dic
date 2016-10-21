<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;

/**
 * Drupal image style display
 */
class DrupalPagerExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('pager', [$this, 'renderPager'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('pager_existing', [$this, 'renderExistingPager'], ['is_safe' => ['html']]),
        ];
    }

    public function renderExistingPager($element = 0)
    {
        return theme('pager', ['element' => $element]);
    }

    public function renderPager($limit, $total, $page = 1, $element = 0)
    {
        pager_default_initialize($total, $limit, $element);

        return theme('pager', ['element' => $element]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_pager';
    }
}
