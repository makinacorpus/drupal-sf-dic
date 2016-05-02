<?php

namespace Drupal\Core\Entity;

use Drupal\node\NodeStorage;
use Drupal\user\UserStorage;

/**
 * Factory to fetch Drupal 7 entity controllers.
 */
class EntityManager
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
                if (!entity_get_info($entityType)) {
                    throw new \InvalidArgumentException(sprintf('%s: entity type does not exist', $entityType));
                }

                return new DefaultEntityStorageProxy($entityType);
        }
    }
}
