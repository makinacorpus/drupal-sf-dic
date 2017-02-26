<?php

namespace MakinaCorpus\Drupal\Sf\DataCollector;

use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Proxy that record all entity load actions
 */
class EntityStorageProxy implements EntityStorageInterface
{
    private $nested;
    private $loads = [];

    /**
     * Default constructor
     *
     * @param EntityStorageInterface $nested
     */
    public function __construct(EntityStorageInterface $nested)
    {
        $this->nested = $nested;
    }

    /**
     * Determine the routine that called this function.
     *
     * @link http://www.php.net/debug_backtrace
     * @return array
     */
    private function findCaller()
    {
        $stack = debug_backtrace();
        $stack_count = count($stack);
        for ($i = 1; $i < $stack_count; ++$i) {
            $stack[$i] += ['args' => []];
            return array(
                'file'      => $stack[$i]['file'],
                'line'      => $stack[$i]['line'],
                'function'  => $stack[$i + 1]['function'],
                'class'     => isset($stack[$i + 1]['class']) ? $stack[$i + 1]['class'] : null,
                'type'      => isset($stack[$i + 1]['type']) ? $stack[$i + 1]['type'] : null,
                'args'      => $stack[$i + 1]['args'],
            );
        }
    }

    /**
     * Get load information
     *
     * @return array
     */
    public function getLoadCalls()
    {
        return $this->loads;
    }

    /**
     * {@inheritdoc}
     */
    public function resetCache(array $ids = null)
    {
        return $this->nested->resetCache();
    }

    /**
     * {@inheritdoc}
     */
    public function loadMultiple($ids = false)
    {
        $time = microtime(true);

        $ret = $this->nested->loadMultiple($ids);
        $askCount = count($ids);

        $this->loads[] = [
            'multiple'  => true,
            'asked'     => is_array($ids) ? $askCount : 0,
            'found'     => count($ret),
            'dups'      => count(array_unique($ids)) !== $askCount,
            'ids'       => is_array($ids) ? $ids : [],
            'ret'       => array_keys($ret),
            'caller'    => $this->findCaller(),
            'time'      => microtime(true) - $time,
        ];

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        $time = microtime(true);

        $ret = $this->nested->load($id);

        $this->loads[] = [
            'multiple'  => false,
            'asked'     => 1,
            'found'     => (int)!empty($ret),
            'dups'      => false,
            'ids'       => [$id],
            'ret'       => $ret ? [$id] : [],
            'caller'    => $this->findCaller(),
            'time'      => microtime(true) - $time,
        ];

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUnchanged($id)
    {
        return $this->nested->loadUnchanged($id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRevision($revision_id)
    {
        return $this->nested->loadRevision($revision_id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRevision($revision_id)
    {
        return $this->nested->deleteRevision($revision_id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadByProperties(array $values = [])
    {
        return $this->nested->loadByProperties($values);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $values = [])
    {
        return $this->nested->create($values);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(array $entities)
    {
        return $this->nested->delete($entities);
    }

    /**
     * {@inheritdoc}
     */
    public function save($entity)
    {
        return $this->nested->save($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityTypeId()
    {
        return $this->nested->getEntityTypeId();
    }
}
