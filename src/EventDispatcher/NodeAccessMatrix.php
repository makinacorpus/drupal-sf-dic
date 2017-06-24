<?php

namespace MakinaCorpus\Drupal\Sf\EventDispatcher;

/**
 * This object only aims to help modules to implement a custom version of the
 * hook_node_access() by adding a generic method for checking node access
 * rights based upon a set of node records and user grants.
 */
final class NodeAccessMatrix
{
    private $grants = [];

    /**
     * Build matrix from Drupal grants
     *
     * @param array $grants
     */
    public function __construct(array $grants = [])
    {
        $this->fromDrupalGrantList($grants);
    }

    /**
     * Convert a single Drupal grant to this object internal matrix
     *
     * @param array $grant
     */
    private function fromDrupalGrant($grant)
    {
        $this->upsert($grant['realm'], $grant['gid'], (bool)$grant['grant_view'], (bool)$grant['grant_update'], (bool)$grant['grant_delete']);
    }

    /**
     * Convert Drupal grant list to this object internal matrix
     *
     * @param array $grants
     */
    private function fromDrupalGrantList($grants)
    {
        foreach ($grants as $grant) {
            $this->fromDrupalGrant($grant);
        }
    }

    /**
     * Existing grant?
     *
     * @param string $realm
     * @param int $gid
     *
     * @return bool
     */
    public function exists($realm, $gid)
    {
        return isset($this->grants[$realm][(string)$gid]);
    }

    /**
     * Lookup for existing grant
     *
     * @param string $realm
     * @param int $gid
     *
     * @return array
     *   The associated grant, if exists
     */
    public function get($realm, $gid)
    {
        $gid = (string)$gid;
        if (isset($this->grants[$realm][$gid])) {
            $grant = $this->grants[$realm][$gid];
            return [
                'realm'         => $realm,
                'gid'           => (int)$gid,
                'grant_view'    => (int)$grant[0],
                'grant_update'  => (int)$grant[1],
                'grant_delete'  => (int)$grant[2],
            ];
        }
    }

    /**
     * Remove grant if exists
     *
     * @param string $realm
     * @param int $gid
     */
    public function remove($realm, $gid)
    {
        unset($this->grants[$realm][(string)$gid]);
    }

    /**
     * Remove grants for whole realm if exist
     *
     * @param string|string[] $realm
     */
    public function removeWholeRealm($realm)
    {
        if (is_array($realm)) {
            foreach ($realm as $single) {
                unset($this->grants[$single]);
            }
        } else {
            unset($this->grants[$realm]);
        }
    }

    /**
     * Alter group identifiers of existing grants
     *
     * For the given realm(s) change the associated given gid to the new
     * one instead, without changing anything else.
     *
     * @param string|string[] $realmList
     *   One or more realms to alter
     * @param int $oldGid
     *   Group identifier to look for
     * @param int $newGid
     *   Group identifier to replace the old one with
     */
    public function replaceGroupId($realmList, $oldGid, $newGid)
    {
        if (!is_array($realmList)) {
            $realmList = [$realmList];
        }

        $oldGid = (string)$oldGid;
        $newGid = (string)$newGid;

        foreach ($realmList as $altered) {
            if (isset($this->grants[$altered][$oldGid])) {
                $this->grants[$altered][$newGid] = $this->grants[$altered][$oldGid];
                unset($this->grants[$altered][$oldGid]);
            }
        }
    }

    /**
     * Upsert a grant
     *
     * @param string $realm
     * @param int $gid
     * @param bool $view
     * @param bool $update
     * @param bool $delete
     * @param int $priority
     */
    public function upsert($realm, $gid, $view = true, $update = false, $delete = false, $priority = 0)
    {
        $this->grants[$realm][(string)$gid] = [(bool)$view, (bool)$update, (bool)$delete, (int)$priority];
    }

    /**
     * Add grant
     *
     * @param string $realm
     * @param int $gid
     * @param bool $view
     * @param bool $update
     * @param bool $delete
     * @param int $priority
     */
    public function add($realm, $gid, $view = true, $update = false, $delete = false, $priority = 0)
    {
        if ($this->exists($realm, $gid)) {
            throw new \InvalidArgumentException(sprintf("a grant for realm %s with gid %d already exists", $realm, $gid));
        }

        $this->grants[$realm][(string)$gid] = [(bool)$view, (bool)$update, (bool)$delete, (int)$priority];
    }

    /**
     * Converts back to Drupal grant list
     */
    public function toDrupalGrantList()
    {
        $ret = [];

        foreach ($this->grants as $realm => $groups) {
            foreach ($groups as $gid => $grants) {
                $ret[] = [
                    'realm'         => $realm,
                    'gid'           => (int)$gid,
                    'grant_view'    => (int)$grants[0],
                    'grant_update'  => (int)$grants[1],
                    'grant_delete'  => (int)$grants[2],
                    'priority'      => (int)$grants[3],
                ];
            }
        }

        return $ret;
    }

    /**
     * Is grant list empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->grants);
    }

    /**
     * Can user?
     *
     * @param array $userMatrix
     * @param string $op
     *
     * @return bool
     */
    public function can($userMatrix, $op)
    {
        switch ($op) {
            case 'view':
                $index = 0;
                break;
            case 'update':
                $index = 1;
                break;
            case 'delete':
                $index = 2;
                break;
            default:
                //trigger_error(sprintf("allowed operations are 'view', 'update' and 'delete': '%s' given", $op), E_USER_ERROR);
                return false;
        }

        foreach ($userMatrix as $realm => $groups) {
            if (isset($this->grants[$realm])) {
                foreach ($groups as $gid) {
                    if (!empty($this->grants[$realm][(string)$gid][$index])) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Can user view?
     *
     * @param array $userMatrix
     * @param int $index
     *
     * @return bool
     */
    public function canRead($userMatrix)
    {
        return $this->can($userMatrix, 'view');
    }

    /**
     * Can user update?
     *
     * @param array $userMatrix
     * @param int $index
     *
     * @return bool
     */
    public function canUpdate($userMatrix)
    {
        return $this->can($userMatrix, 'update');
    }

    /**
     * Can user delete?
     *
     * @param array $userMatrix
     * @param int $index
     *
     * @return bool
     */
    public function canDelete($userMatrix)
    {
        return $this->can($userMatrix, 'delete');
    }
}
