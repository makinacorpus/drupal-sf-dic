<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;

use Drupal\node\NodeInterface;

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
            new \Twig_SimpleFunction('entity_bundle', [$this, 'entityBunde']),
            new \Twig_SimpleFunction('field_name', [$this, 'fieldName']),
            new \Twig_SimpleFunction('node_view', [$this, 'nodeView'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('node_field', [$this, 'fieldView'], ['is_safe' => ['html']]),
        ];
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
