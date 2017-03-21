<?php

namespace MakinaCorpus\Drupal\Sf\Monolog;

use Monolog\Formatter\LineFormatter;

/**
 * Very basic formatter for Drupal watchdog.
 */
class DrupalFormatter extends LineFormatter
{
    /**
     * Override default constructor.
     */
    public function __construct()
    {
        parent::__construct("%message% <pre>%extra%</pre>", null, true);
    }

    protected function normalizeException($e)
    {
        $str = parent::normalizeException($e);

        return '<pre>' . $str . '</pre>';
    }

    /**
     * {@inheritdoc}
     */
    protected function toJson($data, $ignoreErrors = false)
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
