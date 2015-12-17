<?php

namespace MakinaCorpus\Drupal\Sf\Container\Tests;

use Drupal\Core\Database\DatabaseConnectionAwareTrait;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class EmptyService
{
    use ContainerAwareTrait;
    use DatabaseConnectionAwareTrait;
}
