<?php

namespace Drupal\Module\sf_dic;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;

use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\VariablesCompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterListenersPass('event_dispatcher', 'event_listener', 'event_subscriber'));

        // @todo This will make all variables read-only for the container
        //   which I'm not against but could cause some problems...
        // $container->addCompilerPass(new VariablesCompilerPass());
    }
}
