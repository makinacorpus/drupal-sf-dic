<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use MakinaCorpus\Drupal\Sf\Routing\NullRouter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Modifies a few definitions, before the optimizations passes goes.
 *
 * Optimization passes will convert aliases to their original references,
 * so we need to ensure that a few of our definitions get their aliases
 * resolved right at this moment.
 */
class FrameworkBundleIntegrationEarlyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // When in fullstack mode, with the framework bundle enabled, since
        // we have our own 'http_kernel' service definition, we must reenable
        // the 'argument_resolver' service if exists
        if ($container->hasDefinition('argument_resolver') || $container->hasAlias('argument_resolver')) {
            $kernelDefinition = $container->getDefinition('http_kernel');
            $kernelArguments = $kernelDefinition->getArguments();
            $kernelArguments[3] = new Reference('argument_resolver');
            $kernelDefinition->setArguments($kernelArguments);
        }

        // Add a foo router
        if (!$container->has('router')) {
            $container->addDefinitions(['router' => (new Definition())->setClass(NullRouter::class)]);
        }
    }
}
