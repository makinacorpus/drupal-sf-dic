<?php

namespace Drupal\Core\Entity;

/**
 * Factory to fetch Drupal 7 entity controllers
 */
class EntityManager
{
    /**
     * Get entity controller
     *
     * @return \DrupalEntityControllerInterface
     */
    public function getStorage($entity_type)
    {
        return entity_get_controller($entity_type);
    }
}
