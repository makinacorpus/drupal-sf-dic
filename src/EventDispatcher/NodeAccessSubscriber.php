<?php

namespace MakinaCorpus\Drupal\Sf\EventDispatcher;

use Drupal\Core\Session\AccountInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is where the magic happens, please read the README.md file in that
 * very same folder for an accurate description on how it works.
 */
class NodeAccessSubscriber implements EventSubscriberInterface
{
    const STATIC_CACHE_KEY = 'sf_dic_user_grants';

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

    private $eventDispatcher;
    private $userGrantCache;

    /**
     * Default constructor
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        // Weird, but necessary so that the drupal static cache clear calls
        // get this erased too; we need to exclude tests and degraded envs.
        if (function_exists('drupal_static')) {
            $this->userGrantCache = &drupal_static(self::STATIC_CACHE_KEY, []);
        } else {
            $this->userGrantCache = [];
        }
    }

    /**
     * Clear internal caches
     */
    public function resetCache()
    {
        if (function_exists('drupal_static')) {
            drupal_static_reset(self::STATIC_CACHE_KEY);
        }

        $this->userGrantCache = &drupal_static(self::STATIC_CACHE_KEY, []);
    }

    /**
     * Collect and cache current user account grant cache
     *
     * @param AccountInterface $user
     * @param string $permission
     *
     * @return int[][]
     */
    private function collectUserGrants(AccountInterface $user, $permission = 'view')
    {
        $userId = $user->id();

        if (isset($this->userGrantCache[$permission][$userId])) {
            return $this->userGrantCache[$permission][$userId];
        }

        // User grants event is supposedly faster than node records events
        // since that in Drupal API and implementation, it is the only hook
        // to run during ALL HTTP hits, without any exeception
        $event = new NodeAccessGrantEvent($user, $permission);
        $this->eventDispatcher->dispatch(NodeAccessGrantEvent::EVENT_NODE_ACCESS_GRANT, $event);

        return $this->userGrantCache[$permission][$userId] = $event->getResult();
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

        $grants = $this->collectUserGrants($e->getAccount(), $e->getOperation());

//         // @todo we definitely do want to have a more pragmatic approach that
//         //   tells us if the node is managed by another module or not!
//         if (empty($grants) {
//             // Nothing to check upon, we cannot possibly determine any right.
//             return $e->ignore();
//         }

        // Now build the node records event and set the allowed realms to ensure
        // that you won't need to recompute everything
        $e3 = new NodeAccessRecordEvent($e->getNode(), array_keys($grants));
        $this->eventDispatcher->dispatch(NodeAccessRecordEvent::EVENT_NODE_ACCESS_RECORD, $e3);

        if ($e3->getGrantMatrix()->can($grants, $e->getOperation())) {
            return $e->allow();
        }

        // @todo Find a way to be more pragmatic, by doing deny() here, we do
        // prevent nodes that are not managed by node_access ACLs to use the
        // normal Drupal workflow, which will cause serious errors for most
        // Drupal sites.
        // But, the other way arround, if we do ignore() instead, because we
        // may have a partial set of grants in the grant matrix, this may cause
        // false positive (there isn't all grant lines in the matrix, so it
        // might be empty, so we cannot know if the node is managed or not by
        // a module).
        if ($e3->getGrantMatrix()->isEmpty()) {
            return $e->ignore();
        }

        if (NODE_ACCESS_ALLOW !== $e->getResult()) {
            return $e->deny();
        }
    }
}
