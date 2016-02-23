<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class VariablesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $conf = $GLOBALS['conf'];

        unset($conf['drupal_anonymous_user_object']);

        $container->getParameterBag()->add($conf);
    }
}
