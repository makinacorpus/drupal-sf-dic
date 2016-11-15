<?php

namespace Drupal\Module\sf_acl;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;

use MakinaCorpus\ACL\Impl\Symfony\DependencyInjection\ManagerRegisterPass;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use MakinaCorpus\ACL\Impl\Symfony\DependencyInjection\ManagerAwareRegisterPass;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Resources/config'));

        // Do not register anything if the package does not exist
        // @todo if symfony bundle is present, load a different configuration files with
        //   only drupal integration components provided
        if (class_exists('\MakinaCorpus\ACL\Resource')) {

            $loader->load('acl.yml');
            $container->addCompilerPass(new ManagerRegisterPass(), PassConfig::TYPE_BEFORE_REMOVING);
            $container->addCompilerPass(new ManagerAwareRegisterPass(), PassConfig::TYPE_BEFORE_REMOVING);

            $bundles = $container->getParameter('kernel.bundles');
            if (in_array('Symfony\\Bundle\\SecurityBundle\\SecurityBundle', $bundles)) {
                $loader->load('acl-security.yml');
            }
        }
    }
}
