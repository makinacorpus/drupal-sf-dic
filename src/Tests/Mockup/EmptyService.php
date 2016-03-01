<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Mockup;

use Drupal\Core\Database\DatabaseConnectionAwareTrait;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class EmptyService
{
    use ContainerAwareTrait;
    use DatabaseConnectionAwareTrait;
}
