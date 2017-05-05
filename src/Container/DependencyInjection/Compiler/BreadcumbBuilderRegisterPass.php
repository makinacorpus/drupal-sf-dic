<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register breadcrumb builders
 */
class BreadcumbBuilderRegisterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('breadcrumb')) {
            return;
        }

        $services = [];
        foreach ($container->findTaggedServiceIds('breadcrumb_builder') as $id => $attributes) {
            $def = $container->getDefinition($id);

            $class = $container->getParameterBag()->resolveValue($def->getClass());
            $refClass = new \ReflectionClass($class);

            if (!$refClass->implementsInterface(BreadcrumbBuilderInterface::class)) {
                throw new \InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, BreadcrumbBuilderInterface::class));
            }

            if (empty($attributes[0]['priority'])) {
                $priority = 0;
            } else {
                $priority = (int)$attributes[0]['priority'];
            }

            $services[$id] = $priority;
        }

        if ($services) {
            // Sort references using priority
            arsort($services);

            $references = array_map(
                function ($id) {
                    return new Reference($id);
                },
                array_keys($services)
            );

            $definition = $container->getDefinition('breadcrumb');
            $definition->setArguments([$references]);
        }
    }
}
