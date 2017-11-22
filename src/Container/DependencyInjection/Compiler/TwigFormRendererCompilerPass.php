<?php


namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;


use MakinaCorpus\Drupal\Sf\Twig\SecureTwigRendererEngine;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TwigFormRendererCompilerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('twig.form.engine')) {
            return;
        }

        $definition = $container->getDefinition('twig.form.engine');
        $definition->setClass(SecureTwigRendererEngine::class);
        $definition->addMethodCall('setSecureEnvironment', [new Reference('twig')]);
    }
}
