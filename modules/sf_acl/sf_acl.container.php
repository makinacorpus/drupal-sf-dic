<?php

namespace Drupal\Module\sf_acl;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;

use MakinaCorpus\ACL\Impl\Symfony\DependencyInjection\DynamicACLRegisterPass;
use MakinaCorpus\ACL\Impl\Symfony\DependencyInjection\ManagerRegisterPass;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Resources/config'));

        // Do not register anything if the package does not exist
        if (class_exists('\MakinaCorpus\ACL\Resource')) {
            $loader->load('php-acl.yml');

            $container->addCompilerPass(new DynamicACLRegisterPass(), PassConfig::TYPE_BEFORE_REMOVING);
            $container->addCompilerPass(new ManagerRegisterPass(), PassConfig::TYPE_BEFORE_REMOVING);
        }
    }
}
