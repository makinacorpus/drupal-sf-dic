<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;

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
    }
}
