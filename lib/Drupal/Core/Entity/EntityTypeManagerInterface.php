<?php

namespace Drupal\Core\Entity;

/**
 * Provides an interface for entity type managers.
 */
interface EntityTypeManagerInterface
{
    /**
     * Creates a new storage instance.
     *
     * @param string $entity_type
     *   The entity type for this storage.
     *
     * @return \Drupal\Core\Entity\EntityStorageInterface
     *   A storage instance.
     */
    public function getStorage($entity_type);

    /**
     * {@inheritdoc}
     *
     * @return \Drupal\Core\Entity\EntityTypeInterface|null
     */
    public function getDefinition($entity_type_id, $exception_on_invalid = TRUE);

    /**
     * {@inheritdoc}
     *
     * @return \Drupal\Core\Entity\EntityTypeInterface[]
     */
    public function getDefinitions();
}
