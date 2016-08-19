<?php

namespace MakinaCorpus\Drupal\Sf\EventDispatcher;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is where the magic happens, please read the README.md file in that
 * very same folder for an accurate description on how it works.
 */
class NodeAccessSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            NodeAccessEvent::EVENT_NODE_ACCESS => [
                ['lastResortAccessCheck', -2048],
            ],
        ];
    }

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Default constructor
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * This is the most generic node access implementat that could exists, so
     * it needs to run in last resort only, see README.md file for more
     * information.
     *
     * TL;DR; it mostly collects all node records from all modules, then all
     * user grants from all modules, then it intersects it.
     */
    public function lastResortAccessCheck(NodeAccessEvent $e)
    {
        if ('create' === $e->getOperation()) {
            // This module cannot make any assumptions about the create
            // operations, neither node records, just return from here
            return $e->ignore();
        }

        // User grants event is supposedly faster than node records events
        // since that in Drupal API and implementation, it is the only hook
        // to run during ALL HTTP hits, without any exeception
        $e2 = new NodeAccessGrantEvent($e->getAccount(), $e->getOperation());
        $this->eventDispatcher->dispatch(NodeAccessGrantEvent::EVENT_NODE_ACCESS_GRANT, $e2);

        if ($e2->isEmpty()) {
            // Nothing to check upon, we cannot possibly determine any right.
            return $e->ignore();
        }

        $grants = $e2->getResult();

        // Now build the node records event and set the allowed realms to ensure
        // that you won't need to recompute everything
        $e3 = new NodeAccessRecordEvent($e->getNode(), array_keys($grants));
        $this->eventDispatcher->dispatch(NodeAccessRecordEvent::EVENT_NODE_ACCESS_RECORD, $e3);

        if ($e3->getGrantMatrix()->can($grants, $e->getOperation())) {
            return $e->allow();
        }

        return $e->ignore();
    }
}
