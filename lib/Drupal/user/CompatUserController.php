<?php

namespace Drupal\user;

class CompatUserController extends \UserController
{
    /**
     * Default constructor
     */
    public function __construct($entityType)
    {
        parent::__construct($entityType);

        if ($this->cache) {
            $anonymous = $GLOBALS['conf']['drupal_anonymous_user_object'];

            // Anonymous cannot have any role, and it cannot have any fields
            // so skip the attachLoad() parent function for it and just call
            // hooks in case a few modules do something with the anonymous
            // user.
            $anonymous->roles[DRUPAL_ANONYMOUS_RID] = 'anonymous user';

            // Call hook_entity_load().
            foreach (module_implements('entity_load') as $module) {
                $function = $module . '_entity_load';
                $function(["0" => $anonymous], $this->entityType);
            }
            // Call hook_TYPE_load(). The first argument for hook_TYPE_load() are
            // always the queried entities, followed by additional arguments set in
            // $this->hookLoadArguments.
            $args = array_merge(["0" => $anonymous], $this->hookLoadArguments);
            foreach (module_implements($this->entityInfo['load hook']) as $module) {
                call_user_func_array($module . '_' . $this->entityInfo['load hook'], $args);
            }

            $this->entityCache["0"] = $anonymous;
        }
    }

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
