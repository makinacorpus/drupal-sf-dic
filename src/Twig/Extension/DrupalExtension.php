<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;

use Drupal\node\NodeInterface;

/**
 * Drupal various rendering functions
 */
class DrupalExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('entity_bundle', [$this, 'entityBunde']),
            new \Twig_SimpleFunction('field_name', [$this, 'fieldName']),
            new \Twig_SimpleFunction('file_icon', [$this, 'fileIcon'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('file_url', [$this, 'createFileUrl'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('human_size', [$this, 'humanFileSize'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('image', [$this, 'renderImage'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('image_size', [$this, 'imageSize'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('image_src', [$this, 'imageSrc'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('image_url', [$this, 'imageSrc'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('node_field', [$this, 'fieldView'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('node_view', [$this, 'nodeView'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('pager', [$this, 'renderPager'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('pager_existing', [$this, 'renderExistingPager'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('render', [$this, 'doRender'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('render_block', [$this, 'doRenderBlock'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('render_children', [$this, 'doRenderChildren'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('date', [$this, 'formatDate']),
            new \Twig_SimpleFilter('time_diff', [$this, 'formatInterval']),
        ];
    }

    /**
     * Render array
     */
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

    /**
     * Render array children
     */
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

    /**
     * File URL
     */
    public function createFileUrl($uri)
    {
        return file_create_url($uri);
    }

    /**
     * Human file size
     */
    public function humanFileSize($size, $decimals = 2)
    {
        $sz = 'KMGTP';
        $factor = floor((strlen($size) - 1) / 3);

        return sprintf("%.{$decimals}f", $size / pow(1024, $factor)).@$sz[(int)$factor - 1]."o";
    }

    /**
     * File icon
     */
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
     * Because Drupal does need timestamps...
     *
     * @param mixed $date
     *
     * @return int
     */
    private function dateToTimestamp($date)
    {
        if (null === $date) {
            return;
        }
        if (is_integer($date)) {
            return $date;
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->getTimestamp();
        }

        // I don't really agree with this condition but it's necessary
        // to ensure the compatibility with the default date filter.
        if (ctype_digit($date) || (!empty($date) && '-' === $date[0] && ctype_digit(substr($date, 1)))) {
            return (int)$date;
        }

        $instance = new \DateTime($date);
        if ($instance) {
            return $instance->getTimestamp();
        }
    }

    /**
     * Format interval
     */
    public function formatInterval($date, $now = null)
    {
        $timestamp = $this->dateToTimestamp($date);

        if (null === $timestamp) {
            return ''; // I'm really sorry I'm not Mme Irma
        }

        if (null === $now) {
            $referenceTimestamp = time();
        } else {
            $referenceTimestamp = $this->dateToTimestamp($now);

            if (null === $referenceTimestamp) {
                return ''; // Still not Mme Irma
            }
        }

        return format_interval($referenceTimestamp - $timestamp);
    }

    /**
     * Format date
     */
    public function formatDate($date, $format = 'medium', $timezone = null, $langcode = null)
    {
        $timestamp = $this->dateToTimestamp($date);

        if (null === $timestamp) {
            return ''; // I'm really sorry I'm not Mme Irma
        }

        $drupalDateTypes = system_get_date_types();

        if (isset($drupalDateTypes[$format])) {
            $formatted = format_date($timestamp, $format, '', $timezone, $langcode);
        } else {
            $formatted = format_date($timestamp, 'custom', $format, $timezone, $langcode);
        }

        return $formatted;
    }

    /**
     * Render image
     *
     * @param string|\stdClass $image
     * @param null|string $style
     *
     * @return string
     *   The image URI suitable for href and src attributes
     */
    public function renderImage($image, $style = null, $attributes = [])
    {
        if (empty($image)) {
            return '';
        }

        $uri      = null;
        $options  = [];

        if (is_scalar($image)) {
            $uri = (string)$image;
        } else {
            if (is_object($image)) {
                $image = (array)$image;
            }
            $uri = $image['uri'];
            foreach (['width', 'height', 'alt', 'title'] as $property) {
                if (isset($image[$property]) && empty($options[$property])) {
                    $options[$property] = $image[$property];
                }
            }
        }

        if ($style) {
            $hook = 'image_style';
            $options['style_name'] = $style;
        } else {
            $hook = 'image';
        }

        return theme($hook, ['path' => $uri, 'attributes'  => $attributes] + $options);
    }

    /**
     * Build image src/uri
     *
     * @param string|\stdClass $image
     * @param null|string $style
     *
     * @return string
     *   The image URI suitable for href and src attributes
     */
    public function imageSrc($image, $style = null)
    {
        if (empty($image)) {
            return '';
        }

        $uri = null;

        if (is_scalar($image)) {
            $uri = (string)$image;
        } else {
            if (is_object($image)) {
                $image = (array)$image;
            }
            $uri = $image['uri'];
        }

        if ($style) {
            return image_style_url($style, $uri);
        } else {
            return file_create_url($uri);
        }
    }

    /**
     * Render an existing pager
     */
    public function renderExistingPager($element = 0)
    {
        return theme('pager', ['element' => $element]);
    }

    /**
     * Render current arbitrary pager
     */
    public function renderPager($limit, $total, $page = 1, $element = 0)
    {
        pager_default_initialize($total, $limit, $element);

        return theme('pager', ['element' => $element]);
    }

    /**
     * Get human readable bundle name
     *
     * @param string $entityType
     * @param string $bundle
     *
     * @return string
     */
    public function entityBunde($entityType, $bundle)
    {
        if ($info = entity_get_info($entityType)) {
            if (isset($info['bundles'][$bundle])) {
                return $info['bundles'][$bundle]['label'];
            }
        }
    }

    /**
     * Get human readable field name
     *
     * @param string $fieldName
     * @param string $entityType
     * @param string $bundle
     *
     * @return string
     */
    public function fieldName($fieldName, $entityType = null, $bundle = null)
    {
        if ($entityType && $bundle) {
            if ($instance = field_info_instance($entityType, $fieldName, $bundle)) {
                return $instance['label'];
            }
        }
        if ($field = field_info_field($fieldName)) {
            if (isset($field['instance_settings']['label'])) {
                return $field['instance_settings']['label'];
            }
        }
    }

    /**
     * Render a node field
     */
    public function fieldView($node, $field, $view_mode = 'default')
    {
        if (!$node instanceof NodeInterface) {
            return '';
        }

        if (!field_get_items('node', $node, $field)) {
            return '';
        }

        $output = field_view_field('node', $node, $field, $view_mode);

        return drupal_render($output);
    }

    /**
     * Render a single node or an array of nodes
     */
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
     * Render a Drupal block
     */
    public function doRenderBlock($module, $delta)
    {
        // $block = block_load($module, $delta);
        $blocks = module_invoke($module, 'block_info');

        if (!isset($blocks[$delta])) {
            return;
        }

        $block = (object)$blocks[$delta];
        $block->module = $module;
        $block->delta = $delta;
        $block->status = 1;
        $block->region = 'content';
        $block->weight = 0;
        $block->theme = $GLOBALS['theme_key'];

        // Those won't serve any purpose but avoid nasty PHP warnings
        $block->title = '';
        $block->pages = '';
        $block->visibility = 0; // BLOCK_VISIBILITY_NOTLISTED (block module could be disabled)
        $block->custom = null;

        $blockList = [$block];
        drupal_alter('block_list', $blockList);

        $render_array = _block_get_renderable_array(_block_render_blocks($blockList));

        return drupal_render($render_array);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_render';
    }
}
