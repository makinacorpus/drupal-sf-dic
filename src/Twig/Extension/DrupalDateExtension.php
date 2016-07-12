<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;


/**
 * Twig extension providing a date filter which uses drupal date formats.
 * This date filter overwrites the default one but stays compatible with it.
 */
class DrupalDateExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('date', [$this, 'formatDate']),
        ];
    }

    public function formatDate($date, $format = 'medium', $timezone = null, $langcode = null)
    {
        $timestamp = 0;

        if (is_integer($date)) {
            $timestamp = $date;
        }
        elseif ($date instanceof \DateTimeInterface) {
            $timestamp = $date->getTimestamp();
        }
        elseif (is_string($date)) {
            // I don't really agree with this condition but it's necessary
            // to ensure the compatibility with the default date filter.
            if (ctype_digit($date) || (!empty($date) && '-' === $date[0] && ctype_digit(substr($date, 1)))) {
                $timestamp = (integer) $date;
            } else {
                $timestamp = strtotime($date);
                if ($timestamp === false) {
                    return '';
                }
            }
        }
        else {
          return '';
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_date';
    }
}

