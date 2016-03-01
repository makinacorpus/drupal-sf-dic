<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Mockup;

class NullDrupalCache implements \DrupalCacheInterface
{
    public function get($cid)
    {
        return false;
    }

    public function getMultiple(&$cids)
    {
        return [];
    }

    public function set($cid, $data, $expire = CACHE_PERMANENT)
    {
    }

    public function clear($cid = null, $wildcard = false)
    {
    }

    public function isEmpty()
    {
        return true;
    }
}