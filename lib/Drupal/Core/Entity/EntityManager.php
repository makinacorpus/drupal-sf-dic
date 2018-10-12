<?php

namespace Drupal\Core\Entity;

use Drupal\node\NodeStorage;
use Drupal\user\UserStorage;

/**
 * Factory to fetch Drupal 7 entity controllers.
 */
class EntityManager implements EntityTypeManagerInterface
{
    /**
     * Get entity controller
     *
     * @param string $entityType
     *
     * @return EntityStorageInterface
     */
    public function getStorage($entityType)
    {
        switch ($entityType) {

            case 'node':
                return new NodeStorage($entityType);

            case 'user':
                return new UserStorage($entityType);

            default:
                if (!\entity_get_info($entityType)) {
                    throw new \InvalidArgumentException(sprintf('%s: entity type does not exist', $entityType));
                }

                return new DefaultEntityStorageProxy($entityType);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($entity_type_id, $exception_on_invalid = true)
    {
        $type = \entity_get_info($entity_type_id);

        if (!$type) {
            throw new \InvalidArgumentException(sprintf('%s: entity type does not exist', $entity_type_id));
        }

        return new EntityType($entity_type_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinitions()
    {
        return \array_map(function ($entityTypeId) { return new EntityType($entityTypeId); }, \array_keys(\entity_get_info()));
    }
}
