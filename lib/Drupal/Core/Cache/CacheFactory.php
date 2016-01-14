<?php

namespace Drupal\Core\Cache;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
class CacheFactory implements CacheFactoryInterface
{
    /**
     * Instantiates a cache backend class for a given cache bin
     *
     * Classes implementing CacheBackendInterface can register
     * themselves both as a default implementation and for specific bins.
     *
     * @param string $bin
     *   The cache bin for which a cache backend object should be returned.
     *
     * @return CacheBackendInterface
     *   The cache backend object associated with the specified bin.
     */
    public function get($bin)
    {
        return new CacheBackendProxy(_cache_get_object($bin));
    }
}
