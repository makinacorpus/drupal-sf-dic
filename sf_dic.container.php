<?php

namespace Drupal\Module\sf_dic;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;

use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\AddConsoleCommandPass;
use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\ParametersToVariablesPass;
use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\TwigCompilerPass;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Resources/config'));

        $container->addCompilerPass(new RegisterListenersPass('event_dispatcher', 'event_listener', 'event_subscriber'));
        $container->addCompilerPass(new ParametersToVariablesPass());

        if (class_exists('Symfony\\Component\\Console\\Command\\Command')) {
            $container->addCompilerPass(new AddConsoleCommandPass());
        }

        // TwigBundle will automatically be registered in the kernel.
        // @todo
        //   - I guess this should be in an extension file instead...
        if (class_exists('\Symfony\Bundle\TwigBundle\TwigBundle')) {
            $loader->load('templating.yml');

            $container->addCompilerPass(new TwigCompilerPass());
        }
    }
}
