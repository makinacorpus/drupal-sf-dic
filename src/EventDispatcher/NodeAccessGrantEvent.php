<?php

namespace MakinaCorpus\Drupal\Sf\EventDispatcher;

use Drupal\Core\Session\AccountInterface;

use Symfony\Component\EventDispatcher\Event;

/**
 * Collect user grants event
 */
final class NodeAccessGrantEvent extends Event
{
    const EVENT_NODE_ACCESS_GRANT = 'node:accessgrant';

    private $grants = [];
    private $account;
    private $op;

    public function __construct(AccountInterface $account, $op)
    {
        $this->account = $account;
        $this->op = $op;
    }

    /**
     * Get user account
     *
     * @return \Drupal\Core\Session\AccountInterface
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Get operation
     *
     * @return string
     */
    public function getOperation()
    {
        return $this->op;
    }

    /**
     * Lookup for existing grant
     *
     * @param string $realm
     * @param int $gid
     *
     * @return bool
     */
    public function has($realm, $gid)
    {
        return isset($this->grants[$realm][(string)$gid]);
    }

    /**
     * Remove grants for the whole realm if exists
     *
     * @param string|string[] $realm
     */
    public function removeWholeRealm($realm)
    {
        if (is_array($realm)) {
            foreach ($realm as $single) {
                unset($this->grants[$single]);
            }
        } else {
            unset($this->grants[$realm]);
        }
    }

    /**
     * Remove grant if exists
     *
     * @param string $realm
     * @param int $gid
     */
    public function remove($realm, $gid)
    {
        unset($this->grants[$realm][(string)$gid]);
    }

    /**
     * Add grant
     *
     * Be silent about if the grant already exists
     *
     * @param string $realm
     * @param int $gid
     */
    public function add($realm, $gid)
    {
        return $this->grants[$realm][(string)$gid] = $gid;
    }

    /**
     * Is there any grant at all?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->grants);
    }

    /**
     * Get the Drupal grants
     *
     * @return array
     */
    public function getResult()
    {
        return $this->grants;
    }
}
