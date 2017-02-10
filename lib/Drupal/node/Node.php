<?php

namespace Drupal\node;

use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
class Node implements NodeInterface
{
    public $nid;
    public $vid;
    public $type;
    public $language = LanguageInterface::LANGCODE_NOT_SPECIFIED;
    public $title = '';
    public $uid = 0;
    public $status = null;  // Must be null: see node_object_prepare().
    public $created = 0;
    public $changed = 0;
    public $promote = null; // Must be null: see node_object_prepare().
    public $sticky = null;  // Must be null: see node_object_prepare().

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
        return $this->nid;
    }

    /**
     * Gets the language of the entity.
     *
     * @return \Drupal\Core\Language\LanguageInterface
     *   The language object.
     */
    public function language()
    {
        // @todo ugly as hell, but does the job...
        $language = new Language();
        $language->language = $this->language ? $this->language : LanguageInterface::LANGCODE_NOT_SPECIFIED;

        return $language;
    }

    /**
     * {@inheritdoc}
     */
    public function isNew()
    {
        return !$this->nid;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityTypeId()
    {
        return 'node';
    }

    /**
     * {@inheritdoc}
     */
    public function bundle()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function label()
    {
        return entity_label('node', $this);
    }

    /**
     * {@inheritdoc}
     */
    public function url($rel = 'canonical', $options = [])
    {
        return url('node/' . $this->id(), $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createDuplicate()
    {
        $node = clone $this;
        $node->nid = null;
        $node->vid = null;

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->title = $title;

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
    public function setCreatedTime($timestamp)
    {
        $this->created = (int)$timestamp;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isPromoted()
    {
        return (bool)$this->promote;
    }

    /**
     * {@inheritdoc}
     */
    public function setPromoted($promoted)
    {
        $this->promote = (int)(bool)$promoted;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isSticky()
    {
        return (bool)$this->sticky;
    }

    /**
     * {@inheritdoc}
     */
    public function setSticky($sticky)
    {
        $this->sticky = (int)(bool)$sticky;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isPublished()
    {
        return (bool)$this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublished($published)
    {
        $this->status = (int)(bool)$published;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChangedTime()
    {
        return $this->changed;
    }

    /**
     * {@inheritdoc}
     */
    public function setChangedTime($timestamp)
    {
        $this->changed = (int)$timestamp;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner()
    {
        // Not proud of this one (TM).
        return user_load($this->uid);
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner(UserInterface $account)
    {
        $this->uid = $account->id();
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnerId()
    {
        return $this->uid;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwnerId($uid)
    {
        $this->uid = (int)$uid;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function access($operation, AccountInterface $account = null, $return_as_object = false)
    {
        return (bool)node_access($operation, $this, $account);
    }
}
