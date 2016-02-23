<?php

namespace Drupal\node;

class CompatNodeController extends \NodeController
{
    /**
     * {@inheritoc}
     */
    protected function buildQuery($ids, $conditions = [], $revision_id = false)
    {
        return parent::buildQuery($ids, $conditions, $revision_id)
            ->extend('\Drupal\Core\Entity\FetchClassQueryExtender')
            ->setObjectClass('\Drupal\node\Node')
        ;
    }
}
