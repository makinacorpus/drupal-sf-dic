<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use MakinaCorpus\Drupal\Sf\CacheWarmer\TemplatePathsCacheWarmer;
use MakinaCorpus\Drupal\Sf\Templating\Loader\TemplateLocator as FallbackTemplateLocator;
use MakinaCorpus\Drupal\Sf\Twig\Extension\TranslationExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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

        // We must proceed with a few overrides of the TFD_Environment class
        // if present, since we are going to integrate it a bit deeper with
        // both Drupal and Symfony.
        if (class_exists('\TFD_Environment')) {
            $twigEnvDefinition = $container->getDefinition('twig');
            $twigEnvDefinition->setClass('MakinaCorpus\Drupal\Sf\Twig\TFD\Environment');

            if (class_exists('\TFD_Extension')) {
                $twigEnvDefinition->addMethodCall('addExtension', [new Definition('TFD_Extension')]);
            }
        }

        // If Symfony is present, do not override Symfony's services with our
        // and use the original ones instead, thus leaving untouched their
        // original class in definition, and allow side components, such as
        // Symfony's cache warmers to have the input they are waiting for.
        if (class_exists('Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator') && $container->hasDefinition('templating.locator')) {
            $locatorDefinition = $container->getDefinition('templating.locator');
            $locatorClass = $container->getParameterBag()->resolveValue($locatorDefinition->getClass());
            $locatorClass = ltrim($locatorClass, '\\');
            // Do not override if the class is not our own, this ould mean
            // someone else had overriden it instead of us, and I don't want
            // to make users unhappy.
            if ($locatorClass === FallbackTemplateLocator::class) {
                $locatorDefinition->setClass('Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator');
            }
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
        if (class_exists('\Symfony\Bridge\Twig\Extension\TranslationExtension') &&
            $container->hasDefinition('twig.extension.trans') &&
            $container->hasDefinition('translator')
        ) {
            $container
                ->getDefinition('twig.extension.trans')
                ->setClass('Symfony\Bridge\Twig\Extension\TranslationExtension')
                ->setArguments([new Reference('translator')])
            ;
        }
    }
}
