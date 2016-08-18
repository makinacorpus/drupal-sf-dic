<?php

namespace MakinaCorpus\Drupal\Sf\EventDispatcher;

use Drupal\node\NodeInterface;

use Symfony\Component\EventDispatcher\Event;

class NodeAccessRecordEvent extends Event
{
    const EVENT_NODE_ACCESS_RECORD = 'node:accessrecord';

    private $node;
    private $grants = [];

    public function __construct(NodeInterface $node, array $grants = [])
    {
        $this->node = $node;
        $this->grants = $grants;
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
     * Is there an already existing grant for this realm/gid
     *
     * Having duplicates causes Drupal to make PDO exceptions
     *
     * @param string $realm
     * @param int $gid
     * @param bool $remove
     *    For internal use mostly, but settings this to true will also delete
     *    the duplicate grant
     *
     * @return array
     *   The associated grant, if exists
     */
    private function doLookup($realm, $gid, $remove = false)
    {
        foreach ($this->grants as $index => $grant) {
            if ($realm === $grant['realm'] && $gid === $grant['gid']) {
                if ($remove) {
                    unset($this->grants[$index]);
                }
                return $grant;
            }
        }
        return false;
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
    public function lookup($realm, $gid)
    {
        return $this->doLookup($realm, $gid);
    }

    /**
     * Remove grant if exists
     *
     * @param string $realm
     * @param int $gid
     */
    public function remove($realm, $gid)
    {
        $this->doLookup($realm, $gid, true);
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
    public function upsert($realm, $gid, $view = null, $update = null, $delete = null, $priority = 0)
    {
        $this->remove($realm, $gid, true);
        $this->add($realm, $gid, $view, $update, $delete, $priority);
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
    public function add($realm, $gid, $view = false, $update = false, $delete = false, $priority = 0)
    {
        if ($this->lookup($realm, $gid)) {
            throw new \InvalidArgumentException(sprintf("a grant for realù %s with gid %d already exists", $realm, $gid));
        }

        $this->grants[] = [
            'realm'         => $realm,
            'gid'           => $gid,
            'grant_view'    => (int)(bool)$view,
            'grant_update'  => (int)(bool)$view,
            'grant_delete'  => (int)(bool)$view,
            'priority'      => (int)$priority,
        ];
    }

    /**
     * Get the Drupal grants
     *
     * @return array
     */
    public function getGrants()
    {
        return $this->grants;
    }
}
