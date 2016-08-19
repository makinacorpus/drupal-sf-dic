<?php

namespace MakinaCorpus\Drupal\Sf\EventDispatcher;

use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

use Symfony\Component\EventDispatcher\Event;

class NodeAccessEvent extends Event
{
    const EVENT_NODE_ACCESS = 'node:access';

    private $node;
    private $account;
    private $op;
    private $byVote = false;

    private $ignored = 0;
    private $allowed = 0;
    private $denied = 0;

    private $result = NODE_ACCESS_IGNORE;

    public function __construct($node, AccountInterface $account, $op, $byVote = false)
    {
        // And yes, node can a string...
        $this->node = $node;
        $this->account = $account;
        $this->op = $op;
        $this->byVote = $byVote;
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
     * Get account
     *
     * @return AccountInterface
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
     * You don't care about this node
     */
    public function ignore()
    {
        ++$this->ignored;
    }

    /**
     * You say I grant access to this node
     */
    public function allow()
    {
        ++$this->allowed;

        if (NODE_ACCESS_DENY !== $this->result) {
            $this->result = NODE_ACCESS_ALLOW;
        }
    }

    /**
     * You say you shall not pass (it takes precedence over allow)
     */
    public function deny()
    {
        ++$this->denied;

        $this->result = NODE_ACCESS_DENY;

        // Where the actual magic happens please read the README.md file.
        if (!$this->byVote) {
            $this->stopPropagation();
        }
    }

    /**
     * Get the normal result
     *
     * @return int
     *   NODE_ACCESS_* constant
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get the result by voter count
     *
     * @return int
     *   NODE_ACCESS_* constant
     */
    public function getResultByVote()
    {
        if (NODE_ACCESS_IGNORE === $this->result) {
            return NODE_ACCESS_IGNORE;
        }
        if ($this->denied < $this->allowed) {
            return NODE_ACCESS_ALLOW;
        }
        return NODE_ACCESS_DENY;
    }
}
