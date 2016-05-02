<?php

namespace Drupal\node;

use Drupal\Core\Entity\DefaultEntityStorageProxy;

class NodeStorage extends DefaultEntityStorageProxy
{
    /**
     * {@inheritdoc}
     */
    public function create(array $values = array())
    {
        // @todo Handle values
        $node = new Node();

        foreach ($values as $key => $value) {
            $node->$key = $value;
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(array $entities)
    {
        return node_delete_multiple(
            array_map(
                function ($entity) {
                    return is_numeric($entity) ? $entity : $entity->id();
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
        return node_save($entity);
    }
}
