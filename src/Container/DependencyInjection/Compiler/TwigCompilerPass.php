<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class TwigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.filesystem');

        // register bundles as Twig namespaces
        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {

//             $dir = $container->getParameter('kernel.root_dir').'/Resources/'.$bundle.'/views';
//             if (is_dir($dir)) {
//                 $this->addTwigPath($twigFilesystemLoaderDefinition, $dir, $bundle);
//             }

            $reflection = new \ReflectionClass($class);
            $dir = dirname($reflection->getFileName()).'/Resources/views';
            if (is_dir($dir)) {
                $this->addTwigPath($twigFilesystemLoaderDefinition, $dir, $bundle);
            }
        }
    }

    private function addTwigPath($twigFilesystemLoaderDefinition, $dir, $bundle)
    {
        $name = $bundle;
        if ('Bundle' === substr($name, -6)) {
            $name = substr($name, 0, -6);
        }
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', array($dir, $name));
    }
}
