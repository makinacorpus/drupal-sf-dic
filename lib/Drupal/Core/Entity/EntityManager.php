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
                return new DefaultEntityStorageProxy($entityType);
        }
    }
}
