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
        $controller = entity_get_controller($entityType);

        if (!$controller) {
            throw new \InvalidArgumentException(sprintf("%s: entity type does not exist", $entityType));
        }

        switch ($entityType) {

            case 'node':
                return new NodeStorage($controller, $entityType);

            case 'user':
                return new UserStorage($controller, $entityType);

            default:
                return new DefaultEntityStorageProxy($controller, $entityType);
        }
    }
}
