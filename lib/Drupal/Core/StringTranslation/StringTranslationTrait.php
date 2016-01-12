<?php

namespace Drupal\Core\StringTranslation;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
trait StringTranslationTrait
{
    /**
     * Pass-throught to t()
     *
     * @param string $string
     * @param string[] $args
     * @param mixed[] $options
     *
     * @return string
     */
    protected function t($string, array $args = [], array $options = [])
    {
        return t($string, $args, $options);
    }

    /**
     * Pass-throught to format_plural()
     *
     * @param int $count
     * @param string $singular
     * @param string $plural
     * @param string[] $args
     * @param mixed[] $options
     *
     * @return string
     */
    protected function formatPlural($count, $singular, $plural, array $args = [], array $options = [])
    {
        return format_plural($count, $singular, $plural, $args, $options);
    }
}
