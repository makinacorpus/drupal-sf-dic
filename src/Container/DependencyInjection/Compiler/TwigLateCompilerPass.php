<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class TwigLateCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        //
        // Second argument, ie. index 1, is $rootPath which defaults to "%kernel.project_dir%",
        // in our case, this argument is relatively useless since all namespaces will have an
        // absolute path right from the start: replace it by anything that's inside the
        // open_basedir PHP directive.
        //
        // Another argument, third argument ie. index 2 or the "twig.loader.filesystem"
        // service is redundant and must be overriden too.
        //
        // @see https://github.com/twigphp/Twig/issues/2707
        //
        // Be kind, and only do it when the "kernel.realpath" is not set or set to false
        //
        if (empty($GLOBALS['conf']['kernel.realpath'])) {
            if ($container->has('twig.loader.native_filesystem')) {
                $definition = $container->getDefinition('twig.loader.native_filesystem');
                $definition->replaceArgument(1, __DIR__);
            }
            if ($container->has('twig.loader.filesystem')) {
                $definition = $container->getDefinition('twig.loader.filesystem');
                $definition->replaceArgument(2, __DIR__);
            }
        }
        // 
    }
}
