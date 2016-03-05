<?php

namespace Drupal\Core\Path;

class AliasManager implements AliasManagerInterface
{
    /**
     * Looking up for a source
     */
    const SOURCE = 'src';

    /**
     * Looking up for an alias
     */
    const ALIAS = 'dst';

    /**
     * @var AliasStorageInterface
     */
    protected $storage;

    /**
     * @var string[]
     */
    protected $data = [];

    /**
     * @var boolean
     */
    protected $dataIsUpdated = false;

    /**
     * @var string[]
     */
    protected $whitelist;

    /**
     * @var boolean
     */
    protected $excludeAdminPath = true;

    /**
     * Default constructor
     *
     * @param AliasStorageInterface $storage
     */
    public function __construct(AliasStorageInterface $storage, $excludeAdminPath = true)
    {
        $this->storage = $storage;
        $this->excludeAdminPath = $excludeAdminPath;

        $this->whitelist = $this->whitelistInit();

        if (null === $this->whitelist) {
            $this->whitelistRebuild();
        }

        $this->data[self::ALIAS] = [];
        $this->data[self::SOURCE] = [];
    }

    public function whitelistInit()
    {
        // Keeping Drupal 7 way of storing the whitelist, good enough for us.
        // Please consider that we are going to exclude all admin paths from
        // the whitelist, and drop them no matter there are aliases or not,
        // admin path are not supposed to have aliases.
        return variable_get('path_alias_whitelist');
    }

    /**
     * Not part of the interface but will be used by the custom 'path_inc' file
     */
    public function whitelistRebuild($source = null)
    {
        // When paths are inserted, only rebuild the whitelist if the system
        // path has a top level component which is not already in the whitelist.
        if (!empty($source)) {
            if (isset($this->whitelist[strtok($source, '/')])) {
                return;
            }
        }

        // For each alias in the database, get the top level component of the
        // system path it corresponds to. This is the portion of the path before
        // the first '/', if present, otherwise the whole path itself.
        // PS: Sorry for hardcoded db_query()
        $this->whitelist = [];

        $result = db_query("SELECT DISTINCT SUBSTRING_INDEX(source, '/', 1) AS path FROM {url_alias}");

        foreach ($result as $row) {
            $this->whitelist[$row->path] = TRUE;
        }

        variable_set('path_alias_whitelist', $this->whitelist);
    }

    protected function lookup($type, $lookup, $langcode = null)
    {
        if (self::SOURCE !== $type) {

            // Exclude all admin paths
            if ($this->excludeAdminPath && path_is_admin($lookup)) {
                return $lookup;
            }

            // Also check for whitelist
            if (false !== $this->whitelist && !isset($this->whitelist[strtok($lookup, '/')])) {
                return $lookup;
            }
        }

        if (!$langcode) {
            $langcode = $GLOBALS['language']->getId();
        }

        // Compute cache key lookup
        if (self::SOURCE === $type) {
            $dest = self::ALIAS;
        } else {
            $type = self::ALIAS;
            $dest = self::SOURCE;
        }

        if (isset($this->data[$type][$langcode][$lookup])) {
            $found = $this->data[$type][$langcode][$lookup];

            return $found ? $found : $lookup;
        }

        $this->dataIsUpdated = true;

        if (self::SOURCE === $type) {
            $found = $this->storage->lookupPathSource($lookup, $langcode);
        } else {
            $found = $this->storage->lookupPathAlias($lookup, $langcode);
        }

        if ($found) {
            $this->data[$type][$langcode][$lookup] = $found;
            $this->data[$dest][$langcode][$found] = $lookup;
        } else {
            $this->data[$type][$langcode][$lookup] = false;
        }

        return $found ? $found : $lookup;
    }

    /**
     * {@inheritdoc}
     */
    public function getPathByAlias($alias, $langcode = null)
    {
        return $this->lookup(self::SOURCE, $alias, $langcode);
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasByPath($path, $langcode = null)
    {
        return $this->lookup(self::ALIAS, $path, $langcode);
    }

    /**
     * {@inheritdoc}
     */
    public function cacheClear($source = null)
    {
        $this->whitelistRebuild($source);

        if ($source) {
            foreach ($this->data[self::ALIAS] as $langcode => &$map) {
                if (isset($map[$source])) {
                    unset($this->data[self::SOURCE][$langcode][$map[$source]]);
                    unset($map[$source]);
                }
            }
        } else {
            $this->data = [self::ALIAS => [], self::SOURCE => []];
        }

        $this->dataIsUpdated = true;
    }
}
