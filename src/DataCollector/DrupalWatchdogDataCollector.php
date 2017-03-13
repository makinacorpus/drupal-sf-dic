<?php

namespace MakinaCorpus\Drupal\Sf\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

/**
 * Collects watchdog entries.
 *
 * @see sf_dic_watchdog()
 */
class DrupalWatchdogDataCollector extends DataCollector implements LateDataCollectorInterface
{
    /**
     * Default constructor
     */
    public function __construct()
    {
        if (empty($this->data)) {
            $this->data = [];
        }
    }

    /**
     * Log new watchdog entry
     */
    public function logEntry(array $logEntry)
    {
        unset($logEntry['user']);

        $this->data[] = $logEntry;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function lateCollect()
    {
        return $this->data;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_watchdog';
    }
}
