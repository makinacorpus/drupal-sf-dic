<?php

namespace Drupal\Module\sf_acl;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use MakinaCorpus\ACL\Bridge\Symfony\ACLBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(ContainerBuilder $container)
    {
        // Check dependency on the php_acl library.
        if ($container->has('php_acl.manager') || $container->hasAlias('php_acl.manager')) {
            $bundles = $container->getParameter('kernel.bundles');

            // Only load our services if necessary, we do need the security
            // bundle to be loaded for this to work
            if (in_array('Symfony\\Bundle\\SecurityBundle\\SecurityBundle', $bundles)) {
                $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Resources/config'));
                $loader->load('services.yml');
            }
        }
    }

    /**
     * {@inhertidoc}
     */
    public function registerBundles()
    {
        if (class_exists('MakinaCorpus\\ACL\\Bridge\\Symfony\\ACLBundle')) {
            return [
                new ACLBundle(),
            ];
        }
    }
}
