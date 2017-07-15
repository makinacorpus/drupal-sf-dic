<?php

namespace Drupal\Module\sf_dic;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\AddConsoleCommandPass;
use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\BreadcumbBuilderRegisterPass;
use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\ContainerBuilderDebugDumpPass;
use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\ControllerArgumentValueResolverPass;
use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\DoctrinePasstroughPass;
use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\FrameworkBundleIntegrationEarlyPass;
use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\FrameworkBundleIntegrationPass;
use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\PropertyInfoPass;
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
        $loader->load('translation.yml');

        $bundles = $container->getParameter('kernel.bundles');

        $container->addCompilerPass(new BreadcumbBuilderRegisterPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $container->addCompilerPass(new RegisterListenersPass('event_dispatcher', 'event_listener', 'event_subscriber'), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new FrameworkBundleIntegrationEarlyPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $container->addCompilerPass(new FrameworkBundleIntegrationPass(), PassConfig::TYPE_BEFORE_REMOVING);

        if (class_exists('Symfony\\Component\\Console\\Command\\Command')) {
            $container->addCompilerPass(new AddConsoleCommandPass());
        }

        // TwigBundle will automatically be registered in the kernel.
        if (class_exists('Symfony\\Bundle\\TwigBundle\\TwigBundle')) {
            $container->addCompilerPass(new TwigCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
            $loader->load('templating.yml');
        }

        if (!variable_get('kernel.symfony_all_the_way', false)) {
            if (!in_array('Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle', $bundles)) {
                if ($container->getParameter('kernel.debug')) {
                    $container->addCompilerPass(new ContainerBuilderDebugDumpPass(), PassConfig::TYPE_AFTER_REMOVING);
                }

                // Use our own implementation, we can use this without the framework bundle!
                $container->addCompilerPass(new ControllerArgumentValueResolverPass());
                $loader->load('argument-resolver-degraded.yml');

                if (class_exists('Symfony\\Component\\PropertyAccess\\PropertyAccessor')) {
                    $loader->load('property_access.yml');
                }
                if (class_exists('Symfony\\Component\\PropertyInfo\\PropertyInfoExtractor')) {
                    $loader->load('property_info.yml');
                    $container->addCompilerPass(new PropertyInfoPass());
                }

                // We do need to force a few symfony compoenents to be loaded
                $loader->load('translation-degraded.yml');

                // Also load annotations if available
                if (class_exists('Doctrine\\Common\\Annotations\\AnnotationReader')) {
                    $loader->load('annotations-degraded.yml');
                }
            }
        }

        if (in_array('Symfony\\Bundle\\SecurityBundle\\SecurityBundle', $bundles)) {
            $loader->load('security.yml');
        } else{
            $loader->load('security-degraded.yml');
        }
        if (in_array('Symfony\\Bundle\\MonologBundle\\MonologBundle', $bundles)) {
            $loader->load('logging.yml');
        }
        if (in_array('Symfony\\Bundle\\WebProfilerBundle\\WebProfilerBundle', $bundles)) {
            $loader->load('profiler.yml');
        }

        $container->addCompilerPass(new DoctrinePasstroughPass() /*, PassConfig::TYPE_AFTER_REMOVING */);
    }
}
