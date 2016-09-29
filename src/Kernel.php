<?php

namespace MakinaCorpus\Drupal\Sf;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;

use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\ParameterBag\DrupalParameterBag;

use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class Kernel extends BaseKernel
{
    protected $extraBundles = [];
    protected $inDrupal = true;
    protected $isFullStack = false;
    protected $cacheDir = null;

    /**
     * Default constructor
     *
     * @param string $environment
     * @param boolean $debug
     */
    public function __construct($environment = 'prod', $debug = false, $inDrupal = true)
    {
        // Compute the kernel root directory
        if (empty($GLOBALS['conf']['kernel.root_dir'])) {
            $rootDir = DRUPAL_ROOT . '/../app';
            if (is_dir($rootDir)) {
                $this->rootDir = $rootDir;
            } else if (function_exists('conf_path')) {
                $this->rootDir = DRUPAL_ROOT . '/' . conf_path();
            } else {
                throw new \LogicException("could not find a valid kernel.root_dir candidate");
            }
        } else {
            $this->rootDir = $GLOBALS['conf']['kernel.root_dir'];
        }

        if ($rootDir = realpath($this->rootDir)) {
            if (!$rootDir) {
                // Attempt to automatically create the root directory
                if (!mkdir($rootDir, 0750, true)) {
                    throw new \LogicException(sprintf("%s: unable to create directory", $rootDir));
                }
                if (!$rootDir = realpath($rootDir)) {
                    throw new \LogicException(sprintf("%s: unable to what ??", $rootDir));
                }
                $this->rootDir = $rootDir;
            }
        }

        // And cache directory
        if (empty($GLOBALS['conf']['kernel.cache_dir'])) {
            $this->cacheDir = $this->rootDir . '/cache/' . $environment;
        } else {
            $this->cacheDir = $GLOBALS['conf']['kernel.cache_dir'] . '/' . $environment;
        }

        if ($cacheDir = realpath($this->cacheDir)) {
            if (!$cacheDir) {
                // Attempt to automatically create the root directory
                if (!mkdir($cacheDir, 0750, true)) {
                    throw new \LogicException(sprintf("%s: unable to create directory", $cacheDir));
                }
                if (!$cacheDir = realpath($cacheDir)) {
                    throw new \LogicException(sprintf("%s: unable to what ??", $cacheDir));
                }
                $this->cacheDir = $cacheDir;
            }
        }

        $this->inDrupal = $inDrupal;
        if ($inDrupal) {
            if (!empty($GLOBALS['conf']['kernel.symfony_all_the_way'])) {
                $this->isFullStack = true;
            }
        }

        // In case this was set, even if empty, remove it to ensure that
        // the Drupal parameter bag won't override the kernel driven
        // parameters with 'NULL' values which would make the container
        // unhappy and raise exception while resolving path values
        $GLOBALS['conf']['kernel.root_dir'] = $this->rootDir;
        $GLOBALS['conf']['kernel.cache_dir'] = $this->cacheDir;

        parent::__construct($environment, $debug);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * This container is supposed to be dynamically populated, this function
     * allows you to register extra bundles
     *
     * @param BundleInterface[] $bundles
     */
    public function addExtraBundles($bundles)
    {
        if ($this->booted) {
            throw new \LogicException("Kernel is booted, you cannot add extra bundles anymore");
        }

        if (!is_array($bundles)) {
            $bundles = [$bundles];
        }

        foreach ($bundles as $bundle) {
            if (!$bundle instanceof BundleInterface) {
                throw new \LogicException(sprintf("Bundle must be an instance of Symfony\Component\HttpKernel\Bundle\BundleInterface"));
            }
            $this->extraBundles[] = $bundle;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        // @todo - I guess this should happen elsewhere...
        if ($this->inDrupal) {

            // Registering TwigBundle will provide a full Twig environement
            // for our Drupal site but won't have any major impact on the rest
            // so we can safely assume that our users will always want it
            if (class_exists('\Symfony\Bundle\TwigBundle\TwigBundle')) {
                $this->extraBundles[] = new \Symfony\Bundle\TwigBundle\TwigBundle();
            }

            // But, for the next three, it sounds more complicated, this will
            // bring a lot of things in there they probably won't want, let's
            // just give them a choice to disable it
            if ($this->isFullStack) {
                if (class_exists('\Symfony\Bundle\FrameworkBundle\FrameworkBundle')) {
                    $this->extraBundles[] = new \Symfony\Bundle\FrameworkBundle\FrameworkBundle();
                    $this->isFullStack = true;
                }
                if (class_exists('\Symfony\Bundle\MonologBundle\MonologBundle')) {
                    $this->extraBundles[] = new \Symfony\Bundle\MonologBundle\MonologBundle();
                }
                if (class_exists('\Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle')) {
                    $this->extraBundles[] = new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle();
                }
//                 if (class_exists('\Doctrine\Bundle\DoctrineBundle\DoctrineBundle')) {
//                     $this->extraBundles[] = new Doctrine\Bundle\DoctrineBundle\DoctrineBundle();
//                 }
            }
        }

        return $this->extraBundles;
    }

    /**
     * {inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        if ($this->isFullStack) {

            // Reproduce the config_ENV.yml file from Symfony, but keep it
            // optional instead of forcing its usage
            $customConfigFile = $this->rootDir . '/config/config_' . $this->getEnvironment() . '.yml';
            if (!file_exists($customConfigFile)) {
                // Else attempt with a default one
                $customConfigFile = $this->rootDir . '/config/config.yml';
            }
            if (!file_exists($customConfigFile)) {
                // If no file is provided by the user, just use the default one
                // that provide sensible defaults for everything to work fine
                $customConfigFile = __DIR__ . '/../Resources/config/config.yml';
            }

            $loader->load($customConfigFile);
        }
    }

    /**
     * Drop cache
     */
    public function dropCache()
    {
        (new Filesystem())->remove($this->getCacheDir());
    }

    /**
     * Lookup for drupal-8 style modules and allows them to interact with the
     * container building process by injecting services
     *
     * @return string[]
     *   Values are various found MODULE.services.yml files realpath
     */
    protected function discoverDrupalServicesDefinitionFiles()
    {
        $ret = [];

        $rootDir = realpath(DRUPAL_ROOT);
        require_once $rootDir. '/includes/common.inc';

        $ret['sf_dic'] = $rootDir . '/' . drupal_get_path('module', 'sf_dic') . '/sf_dic.services.yml';

        // Find all module.services.yml files, this will do a file_exists() per
        // module, but this will skipped whenever the container file is cached
        foreach (array_keys(system_list('module_enabled')) as $module) {

            // Skip current module and keep it first allowing other modules to
            // overrides our services
            if ('sf_dic' === $module) {
                continue;
            }

            $filename = $rootDir . '/' . drupal_get_path('module', $module) . '/' . $module . '.services.yml';

            if (file_exists($filename)) {
                $ret[$module] = $filename;
            }
        }

        return $ret;
    }

    /**
     * Lookup for drupal-8 style modules and allows them to interact with the
     * container building process by adding compiler passes
     *
     * @return ServiceProviderInterface[]
     *   Keys are modules names while values are the service provider instances
     */
    protected function discoverDrupalServiceProviders()
    {
        $ret = [];

        $rootDir = realpath(DRUPAL_ROOT);
        require_once $rootDir. '/includes/common.inc';

        $this->serviceProviders = [];

        foreach (array_keys(system_list('module_enabled')) as $module) {

            $filename = $rootDir . '/' . drupal_get_path('module', $module) . '/' . $module . '.container.php';

            if (file_exists($filename)) {
                include_once $filename;
            }

            $className = 'Drupal\\Module\\' . $module . '\\ServiceProvider';

            if (class_exists($className)) {
                $provider = new $className();
                if ($provider instanceof ServiceProviderInterface) {
                    $ret[$module] = $provider;
                }
            }
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerBuilder()
    {
        if ($this->inDrupal) {
            $container = new ContainerBuilder(new DrupalParameterBag($this->getKernelParameters()));
        } else {
            $container = new ContainerBuilder(new ParameterBag($this->getKernelParameters()));
        }

        $container->setParameter('kernel.drupal_site_path', DRUPAL_ROOT . '/' . conf_path());

        if (class_exists('ProxyManager\Configuration') && class_exists('Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator')) {
            $container->setProxyInstantiator(new RuntimeInstantiator());
        }

        return $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildContainer()
    {
        $container = parent::buildContainer();

        if ($this->inDrupal) {

            foreach ($this->discoverDrupalServicesDefinitionFiles() as $filename) {
                $loader = new YamlFileLoader($container, new FileLocator(dirname($filename)));
                $loader->load(basename($filename));
            }

            foreach ($this->discoverDrupalServiceProviders() as $provider) {
                $provider->register($container);
            }
        }

        return $container;
    }
}
