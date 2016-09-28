<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use MakinaCorpus\Drupal\Sf\Doctrine\ConnectionFactory;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Attempt to configure Doctrine to use the Drupal database connection instead
 * of making its own if none are declared.
 */
class DoctrinePasstroughPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('doctrine.dbal.connection_factory')) {
            $definition = $container->getDefinition('doctrine.dbal.connection_factory');

            $definition->setClass(ConnectionFactory::class);
        }
    }
}
