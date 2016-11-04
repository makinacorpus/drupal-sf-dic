<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class TwigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('twig')) {
            return;
        }

        if (class_exists('\TFD_Environment')) {
            $twigEnvDefinition = $container->getDefinition('twig');
            $twigEnvDefinition->setClass('MakinaCorpus\Drupal\Sf\Twig\TFD\Environment');

            if (class_exists('\TFD_Extension')) {
                $twigEnvDefinition->addMethodCall('addExtension', [new Definition('TFD_Extension')]);
            }
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
        if (class_exists('\Symfony\Bridge\Twig\Extension\TranslationExtension')) {
            if ($container->hasDefinition('twig.extension.trans')) {
                $container
                    ->getDefinition('twig.extension.trans')
                    ->setClass(TranslationExtension::class)
                    ->setArguments([new Reference('translator')])
                ;
            }
        }
    }
}
