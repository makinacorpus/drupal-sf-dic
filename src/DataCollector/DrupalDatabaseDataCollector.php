<?php

namespace MakinaCorpus\Drupal\Sf\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

/**
 * Drupal database data collector, as of now it will only support the
 * default.default database connection.
 */
class DrupalDatabaseDataCollector extends DataCollector implements LateDataCollectorInterface
{
    const LOGGER_KEY = 'makinacorpus.drupal_database';

    /**
     * @var \DatabaseLog
     */
    private $logger;

    /**
     * Default constructor
     *
     * @param \DatabaseConnection $connection
     */
    public function __construct(\DatabaseConnection $database)
    {
        $this->logger = $database->getLogger();
    }

    /**
     * Clean given data
     */
    public function cleanQuery(array $data)
    {
        if (!empty($data['caller']['args'])) {
            foreach ($data['caller']['args'] as $index => $argument) {
                if (is_object($argument)) {
                    $data['caller']['args'][$index] = 'type: ' . get_class($argument);
                } else if (is_array($argument)) {
                    $data['caller']['args'][$index] = '(array)';
                }
            }
        }
        if (!empty($data['args'])) {
            foreach ($data['args'] as $index => $argument) {
                if (is_object($argument)) {
                    $data['args'][$index] = 'type: ' . get_class($argument);
                }
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array_map([$this, 'cleanQuery'], $this->logger->get(self::LOGGER_KEY));
    }

    /**
     * {@inheritdoc}
     */
    public function lateCollect()
    {
        $this->data = array_map([$this, 'cleanQuery'], $this->logger->get(self::LOGGER_KEY));
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
    public function findTenBiggest()
    {
        // Work on a copy
        $ret = $this->data;

        usort($ret, function ($a, $b) {
            return $a['time'] < $b['time'] ? 1 : 0;
        });

        return array_slice($ret, 0, 10);
    }

    /**
     * Count total time (ms)
     *
     * @return int
     */
    public function getTotalTime()
    {
        $time = 0;

        foreach ($this->data as $query) {
            $time += $query['time'];
        }

        return floor($time * 1000);
    }

    /**
     * Count queries
     *
     * @return int
     */
    public function getQueryCount()
    {
        return count($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_database';
    }
}
