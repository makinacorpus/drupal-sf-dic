<?php

namespace Drupal\Module\sf_dic;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\TwigCompilerPass;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Resources/config'));

        $container->addCompilerPass(
            new RegisterListenersPass('event_dispatcher', 'event_listener', 'event_subscriber')
        );

        // TwigBundle will automatically be registered in the kernel.
        // @todo
        //   - I guess this should be in an extension file instead...
        if (class_exists('\Symfony\Bundle\TwigBundle\TwigBundle')) {
            $loader->load('templating.yml');

            $container->addCompilerPass(new TwigCompilerPass());
        }
    }
}
