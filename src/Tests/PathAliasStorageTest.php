<?php

namespace MakinaCorpus\Drupal\Sf\Tests;

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
