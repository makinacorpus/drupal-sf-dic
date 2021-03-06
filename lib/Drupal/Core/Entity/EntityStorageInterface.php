<?php

namespace Drupal\Core\Entity;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
interface EntityStorageInterface
{
    /**
     * Resets the internal, static entity cache.
     *
     * @param $ids
     *   (optional) If specified, the cache is reset for the entities with the
     *   given ids only.
     */
    public function resetCache(array $ids = NULL);

    /**
     * Loads one or more entities.
     *
     * @param $ids
     *   An array of entity IDs, or FALSE to load all entities.
     *
     * @return \Drupal\Core\Entity\EntityInterface[]
     *   An array of entity objects indexed by their IDs. Returns an empty array
     *   if no matching entities are found.
     */
    public function loadMultiple($ids = FALSE);

    /**
     * Loads one entity.
     *
     * @param mixed $id
     *   The ID of the entity to load.
     *
     * @return \Drupal\Core\Entity\EntityInterface|null
     *   An entity object. NULL if no matching entity is found.
     */
    public function load($id);

    /**
     * Loads an unchanged entity from the database.
     *
     * @param mixed $id
     *   The ID of the entity to load.
     *
     * @return \Drupal\Core\Entity\EntityInterface|null
     *   The unchanged entity, or NULL if the entity cannot be loaded.
     *
     * @todo Remove this method once we have a reliable way to retrieve the
     *   unchanged entity from the entity object.
     */
    public function loadUnchanged($id);

    /**
     * Load a specific entity revision.
     *
     * @param int|string $revision_id
     *   The revision id.
     *
     * @return \Drupal\Core\Entity\EntityInterface|null
     *   The specified entity revision or NULL if not found.
     */
    public function loadRevision($revision_id);

    /**
     * Delete a specific entity revision.
     *
     * A revision can only be deleted if it's not the currently active one.
     *
     * @param int $revision_id
     *   The revision id.
     */
    public function deleteRevision($revision_id);

    /**
     * Load entities by their property values.
     *
     * @param array $values
     *   An associative array where the keys are the property names and the
     *   values are the values those properties must have.
     *
     * @return \Drupal\Core\Entity\EntityInterface[]
     *   An array of entity objects indexed by their ids.
     */
    public function loadByProperties(array $values = array());

    /**
     * Constructs a new entity object, without permanently saving it.
     *
     * @param array $values
     *   (optional) An array of values to set, keyed by property name. If the
     *   entity type has bundles, the bundle key has to be specified.
     *
     * @return \Drupal\Core\Entity\EntityInterface
     *   A new entity object.
     */
    public function create(array $values = array());

    /**
     * Deletes permanently saved entities.
     *
     * @param array $entities
     *   An array of entity objects to delete.
     *
     * @throws \Drupal\Core\Entity\EntityStorageException
     *   In case of failures, an exception is thrown.
     */
    public function delete(array $entities);

    /**
     * Saves the entity permanently.
     *
     * @param \Drupal\Core\Entity\EntityInterface $entity
     *   The entity to save.
     *
     * @return
     *   SAVED_NEW or SAVED_UPDATED is returned depending on the operation
     *   performed.
     *
     * @throws \Drupal\Core\Entity\EntityStorageException
     *   In case of failures, an exception is thrown.
     */
    public function save($entity);

    /**
     * Gets the entity type ID.
     *
     * @return string
     *   The entity type ID.
     */
    public function getEntityTypeId();
}
