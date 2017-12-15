<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use MakinaCorpus\Drupal\Sf\CacheWarmer\TemplatePathsCacheWarmer;
use MakinaCorpus\Drupal\Sf\Twig\Environment;
use MakinaCorpus\Drupal\Sf\Twig\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Fixes Twig configuration to be more resilient and more friendly with the
 * original Symfony framework.
 */
class TwigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('twig')) {
            return;
        }

        // We must proceed with a few overrides of the Twig_Environment class
        // since we are going to integrate it a bit deeper with both Drupal.
        $twigEnvDefinition = $container->getDefinition('twig');
        $twigEnvDefinition->setClass(Environment::class);
        $twigEnvDefinition->addMethodCall('addExtension', [new Definition(Extension::class)]);

        // Very specific fix for TwigBundle ^3
        if (!$container->has('fragment.handler') && !$container->hasAlias('fragment.handler')) {
            $container->removeDefinition('twig.runtime.httpkernel');
        }

        // And now, go for the template path cache warmer, we need to override
        // its class because we are not using the Symfony original template
        // locator but a decorating service instead. See the class documentation
        // for more extensive explaination.
        if ($container->hasDefinition('templating.cache_warmer.template_paths')) {
            $container->getDefinition('templating.cache_warmer.template_paths')->setClass(TemplatePathsCacheWarmer::class);
        }

        // If the Twig bridge from Symfony is present and loaded, do not load
        // our own translation extension but Symfony's one instead: since this
        // commit:
        //
        //   https://github.com/symfony/symfony/commit/24e9cf215590d1090b3d4acbf07e1fb44a973ca8
        //
        // which aims to extend support to Twig 2.x, the translator extension
        // is not loaded by name anymore, but by class, which makes it not
        // possible to override, we need to use the same class name.
        if (class_exists('\\Symfony\\Bridge\\Twig\\Extension\\TranslationExtension') &&
            $container->hasDefinition('twig.extension.trans') &&
            $container->hasDefinition('translator')
        ) {
            $container
                ->getDefinition('twig.extension.trans')
                ->setClass('Symfony\\Bridge\\Twig\\Extension\\TranslationExtension')
                ->setArguments([new Reference('translator')])
            ;
        }

        $this->registerDrupalNamespaces($container);
    }

    /**
     * Register Drupal namespaces in Twig
     */
    private function registerDrupalNamespaces(ContainerBuilder $container)
    {
        $id = 'twig.loader.filesystem';
        if ($container->hasAlias($id)) {
            $id = (string)$container->getAlias($id);
        }
        if (!$container->hasDefinition($id)) {
            return;
        }
        $definition = $container->getDefinition($id);

        $result = \Database::getConnection()->query("SELECT name, filename FROM {system} WHERE status = 1 AND (filename LIKE 'sites/%' OR filename LIKE 'profiles/%')");
        foreach ($result as $row) {
            $definition->addMethodCall('addPath', [DRUPAL_ROOT.'/'.dirname($row->filename), $row->name]);
        }
    }
}
