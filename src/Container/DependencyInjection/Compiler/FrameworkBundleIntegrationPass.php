<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use MakinaCorpus\Drupal\Sf\Config\FileLocator as CustomFileLocator;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * A few things don't go as round as we'd expect when enabling the framework
 * bundle into Drupal, so we are going to hardcode-fix some components
 * definitions to glue the missing pieces altogether
 */
class FrameworkBundleIntegrationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // Cache warmer does more harm than good with Drupal.
        if ($container->hasDefinition('cache_warmer')) {
            $container->removeDefinition('cache_warmer');
        }
        if ($container->hasAlias('cache_warmer')) {
            $container->removeAlias('cache_warmer');
        }

        // When not working with symfony, we need to provide a file locator
        // service of our own instead of the symfony's one
        if (!$container->hasDefinition('file_locator') && !$container->hasAlias('file_locator')) {
            $container->addDefinitions([
                'file_locator' => (new Definition())
                    ->setClass(CustomFileLocator::class)
                    ->addArgument(new Reference('kernel'))
            ]);
        } else {
            // We are working with fullstack, and our users might have changed
            // the global resource directory to somewhere safer than Drupal's
            // sites/SITE folder, case in which we must honnor the user's
            // configuration
            $globalResourcesPath = variable_get('kernel.global_resources_dir');
            if (!$globalResourcesPath) {
                $globalResourcesPath = DRUPAL_ROOT . '/' . conf_path() . '/Resources';
            }

            $definition = $container->getDefinition('file_locator');
            $definition->setArguments([
                new Reference('kernel'),
                $globalResourcesPath,
            ]);
        }

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

        // Also replace the translator with our own
        if ($container->hasDefinition('translator.drupal')) {
            $container->setAlias('translator', 'translator.drupal');
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
