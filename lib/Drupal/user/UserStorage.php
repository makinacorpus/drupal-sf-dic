<?php

namespace Drupal\user;

use Drupal\Core\Entity\DefaultEntityStorageProxy;
use Drupal\Core\Entity\EntityInterface;

class UserStorage extends DefaultEntityStorageProxy
{
    /**
     * {@inheritdoc}
     */
    public function create(array $values = array())
    {
        // @todo Handle values
        return new User();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(array $entities)
    {
        return user_delete_multiple(
            array_map(
                function (UserInterface $entity) {
                    return $entity->id();
                },
                $entities
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function save(EntityInterface $entity)
    {
        return user_save($entity);
    }
}
