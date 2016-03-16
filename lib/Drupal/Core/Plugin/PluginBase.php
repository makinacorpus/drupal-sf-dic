<?php

/**
 * @file
 * Contains \Drupal\Core\Plugin\PluginBase.
 */

namespace Drupal\Core\Plugin;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * This is seriously not even close from the original object in Drupal 8,
 * nevertheless if you extend it, your object will remain API compatible.
 */
abstract class PluginBase
{
    use StringTranslationTrait;

    public function __construct(array $configuration, $pluginId, $pluginDefinition)
    {
    }
}
