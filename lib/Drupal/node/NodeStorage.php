<?php

namespace Drupal\node;

use Drupal\Core\Entity\DefaultEntityStorageProxy;
use Drupal\Core\Entity\EntityInterface;

class NodeStorage extends DefaultEntityStorageProxy
{
    /**
     * {@inheritdoc}
     */
    public function create(array $values = array())
    {
        // @todo Handle values
        return new Node();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(array $entities)
    {
        return node_delete_multiple(
            array_map(
                $entities,
                function (NodeInterface $entity) {
                    return $entity->id();
                }
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function save(EntityInterface $entity)
    {
        return node_save($entity);
    }
}
