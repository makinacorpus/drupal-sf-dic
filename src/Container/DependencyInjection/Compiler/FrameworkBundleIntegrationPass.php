<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

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

        // When NOT in fullstack mode, we need to provide a null implementation
        // for the controller resolver service, else the container will be
        // unable to spawn the http kernel service
        if (!$container->has('controller_resolver')) {
            $container->addDefinitions([
                'controller_resolver' => (new Definition())
                    ->setClass('controller_resolver')
            ]);
        }
    }
}
