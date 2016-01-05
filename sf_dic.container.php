<?php

namespace Drupal\Module\sf_dic;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;

use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\VariablesCompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServiceProvider implements ServiceProviderInterface
{
   /**
    * {@inheritdoc}
    */
   public function register(ContainerBuilder $container)
   {
       $container->addCompilerPass(new VariablesCompilerPass());
   }
}
