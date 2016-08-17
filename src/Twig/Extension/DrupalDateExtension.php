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
            new \Twig_SimpleFilter('time_diff', [$this, 'formatInterval']),
        ];
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_date';
    }
}

