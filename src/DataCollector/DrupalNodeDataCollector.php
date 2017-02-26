<?php

namespace MakinaCorpus\Drupal\Sf\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Drupal node storage operations data collector
 */
class DrupalNodeDataCollector extends DataCollector
{
    /**
     * @var EntityManagerProxy
     */
    private $entityManager;

    /**
     * Default constructor
     *
     * @param EntityManagerProxy $entityManager
     */
    public function __construct(EntityManagerProxy $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Get node storage
     *
     * @return EntityStorageProxy
     */
    private function getStorage()
    {
        $storage = $this->entityManager->getStorage('node');

        if (!$storage instanceof EntityStorageProxy) {
            return new EntityStorageProxy($storage);
        }

        return $storage;
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
        $this->data = [
            'load' => array_map([$this, 'cleanQuery'], $this->getStorage()->getLoadCalls()),
        ];
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getLoadData()
    {
        return $this->data['load'];
    }

    /**
     * Count total time (ms)
     *
     * @return int
     */
    public function getLoadTotalTime()
    {
        $time = 0;

        foreach ($this->data['load'] as $query) {
            $time += $query['time'];
        }

        return floor($time * 1000);
    }

    /**
     * Count queries
     *
     * @return int
     */
    public function getLoadCount()
    {
        return count($this->data['load']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_node';
    }
}
