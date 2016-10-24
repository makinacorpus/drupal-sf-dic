<?php

namespace Drupal\Module\sf_dic;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;

use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\AddConsoleCommandPass;
use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\ContainerBuilderDebugDumpPass;
use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\DoctrinePasstroughPass;
use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\FrameworkBundleIntegrationPass;
use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\TwigCompilerPass;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
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

        $container->addCompilerPass(new RegisterListenersPass('event_dispatcher', 'event_listener', 'event_subscriber'), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new FrameworkBundleIntegrationPass(), PassConfig::TYPE_BEFORE_REMOVING);

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

        if (!variable_get('kernel.symfony_all_the_way', false)) {
            if (!class_exists('\Symfony\Bundle\FrameworkBundle\FrameworkBundle')) {
                if ($container->getParameter('kernel.debug')) {
                    $container->addCompilerPass(new ContainerBuilderDebugDumpPass(), PassConfig::TYPE_AFTER_REMOVING);
                }
            }
        }

        $bundles = $container->getParameter('kernel.bundles');
        if (in_array('Symfony\\Bundle\\SecurityBundle\\SecuritykBundle', $bundles)) {
            $loader->load('security.yml');
        }

        $container->addCompilerPass(new DoctrinePasstroughPass() /*, PassConfig::TYPE_AFTER_REMOVING */);
    }
}
