<?php

namespace Drupal\user;

class CompatUserController extends \UserController
{
    /**
     * {@inheritoc}
     */
    protected function buildQuery($ids, $conditions = [], $revision_id = false)
    {
        return parent::buildQuery($ids, $conditions, $revision_id)
            ->extend('\Drupal\Core\Entity\FetchClassQueryExtender')
            ->setObjectClass('\Drupal\user\User')
        ;
    }
}
