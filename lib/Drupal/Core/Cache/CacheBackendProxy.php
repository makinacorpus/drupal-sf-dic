<?php

namespace Drupal\Core\Cache;

/**
 * Drupal 8 API proxy to Drupal 7 backends
 */
class CacheBackendProxy implements CacheBackendInterface
{
    /**
     * @var \DrupalCacheInterface
     */
    private $backend;

    /**
     * Default constructor
     *
     * @param \DrupalCacheInterface $backend
     */
    public function __construct(\DrupalCacheInterface $backend)
    {
        $this->backend = $backend;
    }

    /**
     * {inheritdoc}
     */
    public function get($cid, $allow_invalid = false)
    {
        return $this->backend->get($cid);
    }

    /**
     * {inheritdoc}
     */
    public function getMultiple(&$cids, $allow_invalid = false)
    {
        return $this->backend->getMultiple($cids);
    }

    /**
     * WARNING: Tags will be ignored, sorry
     *
     * {inheritdoc}
     */
    public function set($cid, $data, $expire = Cache::PERMANENT, array $tags = [])
    {
        return $this->backend->set($cid, $data, $expire);
    }

    /**
     * WARNING: Tags will be ignored, sorry
     *
     * {inheritdoc}
     */
    public function setMultiple(array $items)
    {
        foreach ($items as $cid => $item) {
            $this->backend->set($cid, $item['data'], $item['expires'], $item['tags']);
        }
    }

    /**
     * {inheritdoc}
     */
    public function delete($cid)
    {
        return $this->backend->clear($cid, false);
    }

    /**
     * {inheritdoc}
     */
    public function deleteMultiple(array $cids)
    {
        foreach ($cids as $cid) {
            $this->backend->clear($cid, false);
        }
    }

    /**
     * {inheritdoc}
     */
    public function deleteAll()
    {
        return $this->backend->clear('*', true);
    }

    /**
     * WARNING: Proxy to delete equivalent
     *
     * {inheritdoc}
     */
    public function invalidate($cid)
    {
        return $this->delete($cid);
    }

    /**
     * WARNING: Proxy to delete equivalent
     *
     * {inheritdoc}
     */
    public function invalidateMultiple(array $cids)
    {
        return $this->deleteMultiple($cids);
    }

    /**
     * WARNING: Proxy to delete equivalent
     *
     * {inheritdoc}
     */
    public function invalidateAll()
    {
        return $this->deleteAll();
    }

    /**
     * WARNING: This does nothing
     *
     * {inheritdoc}
     */
    public function garbageCollection()
    {
    }

    /**
     * WARNING: This does nothing
     *
     * {inheritdoc}
     */
    public function removeBin()
    {
    }
}
