<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ParametersToVariablesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        global $conf;

        foreach ($container->getParameterBag()->all() as $key => $value) {
            if (!array_key_exists($key, $conf)) {
                $conf[$key] = $value;
            }
        }
    }
}
