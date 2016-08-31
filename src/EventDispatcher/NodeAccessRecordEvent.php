<?php

namespace MakinaCorpus\Drupal\Sf\EventDispatcher;

use Drupal\node\NodeInterface;

use Symfony\Component\EventDispatcher\Event;

/**
 * Collect node records event
 */
final class NodeAccessRecordEvent extends Event
{
    const EVENT_NODE_ACCESS_RECORD = 'node:accessrecord';

    private $node;
    private $matrix;
    private $allowedRealms;

    public function __construct(NodeInterface $node, $allowedRealms = null)
    {
        $this->node = $node;
        $this->matrix = new NodeAccessMatrix();
        $this->allowedRealms = $allowedRealms;
    }

    /**
     * If this hook is running at runtime, this allows users to determine if the
     * caller needs to check grants for this realm or not; if you, the person
     * implementing this event, need to do CPU costly computation, or external
     * backend queries (SQL or cache) for a certain realm, this gives you the
     * possibility of skipping that.
     *
     * @param string $realm
     *
     * @return bool
     */
    public function isRealmAllowed($realm)
    {
        if (null === $this->allowedRealms) {
            return true;
        }
        if (!$this->allowedRealms) {
            return false;
        }
        return in_array($realm, $this->allowedRealms);
    }

    /**
     * Get node
     *
     * @return NodeInterface
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Has the current realm/gid
     *
     * @param string $realm
     * @param int $gid
     */
    public function exists($realm, $gid)
    {
        return $this->matrix->exists($realm, $gid);
    }

    /**
     * Lookup for existing grant
     *
     * @param string $realm
     * @param int $gid
     *
     * @return array
     *   The associated grant, if exists
     */
    public function get($realm, $gid)
    {
        return $this->matrix->get($realm, $gid);
    }

    /**
     * Remove grant if exists
     *
     * @param string $realm
     * @param int $gid
     */
    public function remove($realm, $gid)
    {
        return $this->matrix->remove($realm, $gid);
    }

    /**
     * Remove grants for whole realm if exist
     *
     * @param string|string[] $realm
     */
    public function removeWholeRealm($realm)
    {
        return $this->matrix->removeWholeRealm($realm);
    }

    /**
     * Alter group identifiers of existing grants
     *
     * For the given realm(s) change the associated given gid to the new
     * one instead, without changing anything else.
     *
     * @param string|string[] $realmList
     *   One or more realms to alter
     * @param int $oldGid
     *   Group identifier to look for
     * @param int $newGid
     *   Group identifier to replace the old one with
     */
    public function replaceGroupId($realmList, $oldGid, $newGid)
    {
        $this->matrix->replaceGroupId($realmList, $oldGid, $newGid);
    }

    /**
     * Upsert a grant
     *
     * @param string $realm
     * @param int $gid
     * @param bool $view
     * @param bool $update
     * @param bool $delete
     * @param int $priority
     */
    public function upsert($realm, $gid, $view = true, $update = false, $delete = false, $priority = 0)
    {
        return $this->matrix->upsert($realm, $gid, $view, $update, $delete, $priority);
    }

    /**
     * Add grant
     *
     * @param string $realm
     * @param int $gid
     * @param bool $view
     * @param bool $update
     * @param bool $delete
     * @param int $priority
     */
    public function add($realm, $gid, $view = true, $update = false, $delete = false, $priority = 0)
    {
        return $this->matrix->add($realm, $gid, $view, $update, $delete, $priority);
    }

    /**
     * Get optimized internal grant matrix
     *
     * @return NodeAccessMatrix
     */
    public function getGrantMatrix()
    {
        return $this->matrix;
    }

    /**
     * Get the Drupal grants
     *
     * This converts the current internal matrix to Drupal grant array, do not
     * use it outside of the original event dispatcher
     *
     * @return array
     */
    public function toDrupalGrantList()
    {
        return $this->matrix->toDrupalGrantList();
    }
}
