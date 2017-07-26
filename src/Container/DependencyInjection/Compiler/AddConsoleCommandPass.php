<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * This class was mostly copy/pasted from the Symfony FrameworkBundle (v3.3) all
 * credits to its original author.
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class AddConsoleCommandPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $commandServices = $container->findTaggedServiceIds('console.command', true);
        $serviceIds = array();

        foreach ($commandServices as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class = $container->getParameterBag()->resolveValue($definition->getClass());

            if (!$r = new \ReflectionClass($class)) {
                throw new \InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            }
            if (!$r->isSubclassOf(Command::class)) {
                throw new \InvalidArgumentException(sprintf('The service "%s" tagged "console.command" must be a subclass of "%s".', $id, Command::class));
            }

            $commandId = 'console.command.'.strtolower(str_replace('\\', '_', $class));
            if ($container->hasAlias($commandId) || isset($serviceIds[$commandId])) {
                $commandId = $commandId.'_'.$id;
            }
            if (!$definition->isPublic()) {
                $container->setAlias($commandId, $id);
                $id = $commandId;
            }

            $serviceIds[$commandId] = $id;
        }

        $container->setParameter('console.command.ids', $serviceIds);
    }
}
