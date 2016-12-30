<?php

namespace Drupal\node;

class CompatNodeController extends \NodeController
{
    /**
     * {@inheritoc}
     */
    protected function buildQuery($ids, $conditions = [], $revision_id = false)
    {
        $query = parent::buildQuery($ids, $conditions, $revision_id);

        // user_node_load() goes here, much less requests in the end.
        $query->leftJoin('users', 'u', 'u.uid = base.uid');
        $query->addField('u', 'name', 'name');
        $query->addField('u', 'picture', 'picture');
        $query->addField('u', 'data', 'data');

        return $query
            ->extend('\Drupal\Core\Entity\FetchClassQueryExtender')
            ->setObjectClass('\Drupal\node\Node')
        ;
    }
}
