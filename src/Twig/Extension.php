<?php

namespace MakinaCorpus\Drupal\Sf\Twig;

/**
 * Original code this was forked from TFD7 project:
 *   https://github.com/TFD7/TFD7
 *
 * All credits to its authors.
 *
 * @author René Bakx
 * @see http://tfd7.rocks for more information
 */
class Extension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return [
            'base_path' => base_path(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeVisitors()
    {
        return [new NodeVisitor()];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            'defaults' => new \Twig_SimpleFilter('defaults', 'tfd_defaults_filter'),
            'size' => new \Twig_SimpleFilter('size', 'format_size'),
            'plural' => new \Twig_SimpleFilter('plural', 'format_plural'),
            'url' => new \Twig_SimpleFilter('url', 'tfd_url'),
            't' => new \Twig_SimpleFilter('t', 't'),
            'attributes' => new \Twig_SimpleFilter('attributes', 'drupal_attributes'),
            'check_plain' => new \Twig_SimpleFilter('check_plain', 'check_plain'),
            'ucfirst' => new \Twig_SimpleFilter('ucfirst', 'ucfirst'),
            'wrap' => new \Twig_SimpleFilter('wrap', 'tfd_wrap_text'),
            'machine_name' => new \Twig_SimpleFilter('machine_name', 'tfd_machine_name'),
            'truncate' => new \Twig_SimpleFilter('truncate', 'tfd_truncate_text'),
            'without' => new \Twig_SimpleFilter('without', 'tfd_without'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'theme_get_setting' => new \Twig_SimpleFunction('theme_get_setting', 'theme_get_setting'),
            'module_exists' => new \Twig_SimpleFunction('module_exists', 'module_exists'),
            'hide' => new \Twig_SimpleFunction('hide', 'tfd_hide'),
            'url' => new \Twig_SimpleFunction('url', 'tfd_url'),
            'classname' => new \Twig_SimpleFunction('classname', 'get_class'),
            'variable_get' => new \Twig_SimpleFunction('variable_get', 'variable_get'),
            'array_search' => new \Twig_SimpleFunction('array_search', 'array_search'),
            'machine_name' => new \Twig_SimpleFunction('machine_name', 'tfd_machine_name'),
            'image_url' => new \Twig_SimpleFunction('image_url', 'tfd_image_url'),
            'image_size' => new \Twig_SimpleFunction('image_size', 'tfd_image_size'),
            'get_form_errors' => new \Twig_SimpleFunction('get_form_errors', 'tfd_form_get_errors'),
            'children' => new \Twig_SimpleFunction('children', 'tfd_get_children'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sf_dic_env';
    }
}
