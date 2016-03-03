<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;

/**
 * Drupal various rendering functions
 */
class DrupalRenderExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('render', [$this, 'doRender'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('render_children', [$this, 'doRenderChildren'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('field', [$this, 'doRenderField'], ['is_safe' => ['html']]),
        ];
    }

    public function doRender($object)
    {
        if (null === $object || [] === $object) {
            return '';
        }
        if (!is_array($object)) {
            return (string)$object;
        }
        return drupal_render($object);
    }

    public function doRenderChildren($object)
    {
        if (null === $object || [] === $object) {
            return '';
        }
        if (!is_array($object)) {
            throw new \InvalidArgumentException("render_children() input must be an array");
        }
        return drupal_render_children($object);
    }

    public function doRenderField($fieldContent, $options = [])
    {
        throw new \Exception("Not implemented yet");
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_render';
    }
}
