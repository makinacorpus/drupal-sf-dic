<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\Cache.
 */

namespace Drupal\Core\Cache;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
class Cache
{
    /**
     * Indicates that the item should never be removed unless explicitly deleted
     */
    const PERMANENT = CacheBackendInterface::CACHE_PERMANENT;
}
