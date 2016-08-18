<?php

namespace MakinaCorpus\Drupal\Sf\EventDispatcher;

use Drupal\node\NodeInterface;

class NodeEvent extends EntityEvent
{
    const EVENT_DELETE      = 'node:delete';
    const EVENT_INSERT      = 'node:insert';
    const EVENT_PREINSERT   = 'node:preinsert';
    const EVENT_PREPARE     = 'node:prepare';
    const EVENT_PREUPDATE   = 'node:preupdate';
    const EVENT_PRESAVE     = 'node:presave';
    const EVENT_SAVE        = 'node:save';
    const EVENT_UPDATE      = 'node:update';
    const EVENT_VIEW        = 'node:view';

    /**
     * Constructor
     *
     * @param string $eventName
     * @param NodeInterface $node
     * @param int $userId
     * @param array $arguments
     */
    public function __construct($eventName, NodeInterface $node, $userId = null, array $arguments = [])
    {
        parent::__construct($eventName, 'node', $node, $userId, $arguments);
    }

    /**
     * Is the current event a clone operation
     *
     * @todo this is ucms only and should not live here
     *   in the future, remove this
     *
     * @return boolean
     */
    public function isClone()
    {
        return self::EVENT_INSERT === $this->getEventName() && !empty($this->getNode()->parent_nid);
    }

    /**
     * Get the node
     *
     * @return NodeInterface
     */
    public function getNode()
    {
        return $this->getEntity();
    }
}
