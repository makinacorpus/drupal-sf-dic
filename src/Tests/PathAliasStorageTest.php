<?php

namespace MakinaCorpus\Drupal\Sf\Container\Tests;

use Drupal\Core\Path\DefaultAliasStorage;

class PathAliasStorageTest extends AbstractPathAliasStorageTest
{
    protected function createAliasStorage()
    {
        return new DefaultAliasStorage(
            $this->getDatabaseConnection(),
            $this->getNullModuleHandler()
        );
    }
}
