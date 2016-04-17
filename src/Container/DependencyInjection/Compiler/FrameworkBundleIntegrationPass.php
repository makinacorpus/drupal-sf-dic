<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * A few things don't go as round as we'd expect when enabling the framework
 * bundle into Drupal, so we are going to hardcode-fix some components
 * definitions to glue the missing pieces altogether
 */
class FrameworkBundleIntegrationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // By registering the framework bundle, we also inherit from Symfony
        // default URL generator, which will cause us great pain because of
        // Drupal routes will not be known by the framework and throw a few
        // exceptions.
        if ($container->has('router.default')) {
            $container
                ->getDefinition('router.default')
                ->setClass('MakinaCorpus\Drupal\Sf\Routing\Router')
            ;
        }
    }
}
