<?php

namespace Drupal\filter\Plugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
abstract class FilterBase extends PluginBase implements FilterInterface
{
    /**
     * @var mixed[]
     */
    protected $settings = [];

    /**
     * Default constructor
     */
    public function __construct(array $configuration, $pluginId, $pluginDefinition)
    {
        parent::__construct($configuration, $pluginId, $pluginDefinition);

        if (isset($configuration['settings'])) {
            $this->settings = (array)$configuration['settings'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function settingsForm(array $form, FormStateInterface $formState)
    {
        // Implementations should work with and return $form. Returning an empty
        // array here allows the text format administration form to identify whether
        // the filter plugin has any settings form elements.
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($text, $langcode)
    {
        return $text;
    }

    /**
     * {@inheritdoc}
     */
    public function tips($long = false)
    {
    }
}
