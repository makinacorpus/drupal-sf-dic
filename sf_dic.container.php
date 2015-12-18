<?php

namespace Drupal\Module\sf_dic;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServiceProvider implements ServiceProviderInterface
{
   /**
    * {@inheritdoc}
    */
   public function register(ContainerBuilder $container)
   {
       // This is actually a sample, but there is nothing to do here
       // in real life. Just use the same schema in your modules for
       // implementing the Symfony equivalent of bundles.
   }
}
