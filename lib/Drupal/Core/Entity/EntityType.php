<?php

namespace Drupal\Core\Entity;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
class EntityType implements EntityTypeInterface
{
    private $entityType;

    /**
     * Default constructor
     */
    public function __construct($entityType)
    {
        $this->entityType = $entityType;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys()
    {
        return \entity_get_info($this->entityType)['entity keys'];
    }

    /**
     * {@inheritdoc}
     */
    public function getKey($key)
    {
        throw new \Exception("Not implemented yet.");
    }

    /**
     * {@inheritdoc}
     */
    public function hasKey($key)
    {
        throw new \Exception("Not implemented yet.");
    }

    /**
     * {@inheritdoc}
     */
    public function getBundleLabel()
    {
        throw new \Exception("Not implemented yet.");
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTable()
    {
        return \entity_get_info($this->entityType)['base table'];
    }

    /**
     * {@inheritdoc}
     */
    public function isTranslatable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isRevisionable()
    {
        return !empty(\entity_get_info($this->entityType)['entity_keys']['revision']);
    }

    /**
     * {@inheritdoc}
     */
    public function getRevisionDataTable()
    {
        return \entity_get_info($this->entityType)['revision table'];
    }

    /**
     * {@inheritdoc}
     */
    public function getRevisionTable()
    {
        return \entity_get_info($this->entityType)['revision table'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTable()
    {
        return \entity_get_info($this->entityType)['base table'];
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        throw new \Exception("Not implemented yet.");
    }

    /**
     * {@inheritdoc}
     */
    public function getLowercaseLabel()
    {
        throw new \Exception("Not implemented yet.");
    }

    /**
     * {@inheritdoc}
     */
    public function getCollectionLabel()
    {
        throw new \Exception("Not implemented yet.");
    }

    /**
     * {@inheritdoc}
     */
    public function getSingularLabel()
    {
        throw new \Exception("Not implemented yet.");
    }

    /**
     * {@inheritdoc}
     */
    public function getPluralLabel()
    {
        throw new \Exception("Not implemented yet.");
    }

    /**
     * {@inheritdoc}
     */
    public function getCountLabel($count)
    {
        throw new \Exception("Not implemented yet.");
    }
}
