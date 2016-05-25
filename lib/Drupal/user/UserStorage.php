<?php

namespace Drupal\user;

use Drupal\Core\Entity\DefaultEntityStorageProxy;

class UserStorage extends DefaultEntityStorageProxy
{
    /**
     * {@inheritdoc}
     */
    public function create(array $values = array())
    {
        // @todo Handle values
        $user = new User();
        $user->setIsNew(true);

        return $user;
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
    public function save($entity)
    {
        if ($entity instanceof User) {
            $entity->setIsNew(false);
        }

        return user_save($entity);
    }
}
