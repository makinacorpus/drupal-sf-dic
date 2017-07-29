<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Registers twig extensions if present
 */
class TwigExtensionsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('twig')) {
            return;
        }

        $classes = [
            'twig.extensions.array' => '\\Twig_Extensions_Extension_Array',
            'twig.extensions.date' => '\\Twig_Extensions_Extension_Date',
            'twig.extensions.intl' => '\\Twig_Extensions_Extension_Intl',
            'twig.extensions.text' => '\\Twig_Extensions_Extension_Text',
        ];
        foreach ($classes as $serviceId => $class) {
            if (class_exists($class)) {
                $container->addDefinitions([
                    $serviceId => (new Definition())
                        ->setPublic(false)
                        ->setClass($class)
                        ->addTag('twig.extension')
                ]);
            }
        }
    }
}
