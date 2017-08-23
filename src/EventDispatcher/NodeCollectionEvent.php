<?php

namespace MakinaCorpus\Drupal\Sf\EventDispatcher;

use Drupal\node\NodeInterface;

class NodeCollectionEvent extends EntityCollectionEvent
{
    const EVENT_LOAD = 'node:load';

    /**
     * Constructor
     *
     * @param string $eventName
     * @param NodeInterface[] $nodes
     * @param int $userId
     * @param array $arguments
     */
    public function __construct($eventName, array $nodes, $userId = null, array $arguments = [])
    {
        parent::__construct($eventName, 'node', $nodes, $userId, $arguments);
    }

    /**
     * Get the nodes
     *
     * @return NodeInterface[]
     */
    public function getNodes()
    {
        return parent::getEntities();
    }
}
