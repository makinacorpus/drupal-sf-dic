<?php

/**
 * @file
 * Contains \Drupal\user\UserInterface.
 */

namespace Drupal\user;

use Drupal\Core\Session\Account;
use Drupal\Core\Session\AccountInterface;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
class User extends Account implements UserInterface
{
    public $isNew = false;

    /**
     * {@inheritdoc}
     */
    public function uuid()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function id()
    {
        return $this->uid;
    }

    /**
     * {@inheritdoc}
     */
    public function language()
    {
        return $this->language;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsNew($toggle = true)
    {
        $this->isNew = (bool)$toggle;
    }

    /**
     * {@inheritdoc}
     */
    public function isNew()
    {
        return !$this->uid && $this->isNew;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityTypeId()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function bundle()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function label()
    {
        return $this->getDisplayName();
    }

    /**
     * {@inheritdoc}
     */
    public function url($rel = 'canonical', $options = [])
    {
        return url('user/' . $this->id(), $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createDuplicate()
    {
        $user = clone $this;
        $user->uid = null;
        $user->name = null;
        $user->mail = null;

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($rid)
    {
        return isset($this->roles[$rid]);
    }

    /**
     * {@inheritdoc}
     */
    public function addRole($rid)
    {
        $this->roles[$rid] = $rid;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($rid)
    {
        unset($this->roles[$rid]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUsername($username)
    {
        $this->name = $username;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->pass;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedTime()
    {
        return $this->created;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastLoginTime()
    {
        return $this->access;
    }

    /**
     * {@inheritdoc}
     */
    public function setLastLoginTime($timestamp)
    {
        $this->access = (int)$timestamp;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return (bool)$this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function isBlocked()
    {
        return !$this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialEmail()
    {
        // @todo ?
        return $this->mail;
    }

    /**
     * {@inheritdoc}
     */
    public function access($operation, AccountInterface $account = null, $return_as_object = false)
    {
        throw new \Exception("Not implemented yet");
    }
}
