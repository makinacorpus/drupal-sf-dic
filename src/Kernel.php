<?php

namespace MakinaCorpus\Drupal\Sf;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;

use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\ParameterBag\DrupalParameterBag;

use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Config\ConfigCache;

abstract class Kernel extends BaseKernel
{
    protected $isFullStack = false;
    protected $cacheDir = null;
    protected $logDir = null;

    /**
     * Default constructor
     *
     * @param string $environment
     * @param boolean $debug
     */
    public function __construct($environment = 'prod', $debug = false)
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
            }
            $this->rootDir = $rootDir;
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
            }
            $this->cacheDir = $cacheDir;
        }

        // And finally, the logs directory
        if (empty($GLOBALS['conf']['kernel.logs_dir'])) {
            $this->logDir = $this->rootDir . '/logs';
        } else {
            $this->logDir = $GLOBALS['conf']['kernel.logs_dir'];
        }

        if ($logDir = realpath($this->logDir)) {
            if (!$logDir) {
                // Attempt to automatically create the root directory
                if (!mkdir($logDir, 0750, true)) {
                    throw new \LogicException(sprintf("%s: unable to create directory", $logDir));
                }
                if (!$logDir = realpath($logDir)) {
                    throw new \LogicException(sprintf("%s: unable to what ??", $logDir));
                }
            }
            $this->logDir = $logDir;
        }

        if (!empty($GLOBALS['conf']['kernel.symfony_all_the_way'])) {
            $this->isFullStack = true;
        }

        // In case this was set, even if empty, remove it to ensure that
        // the Drupal parameter bag won't override the kernel driven
        // parameters with 'NULL' values which would make the container
        // unhappy and raise exception while resolving path values
        $GLOBALS['conf']['kernel.root_dir'] = $this->rootDir;

        // More specific something for cache_dir, since the environment
        // name is suffixed, we cannot just store it, else in case of
        // cache clear/kernel drop, the second kernel will have the env
        // name appened a second time, and everything will fail.
        // I know, this is a very messed-up side effect due to wrongly
        // written settings.php files, but I should keep this for safety.
        if (empty($GLOBALS['conf']['kernel.cache_dir'])) {
            unset($GLOBALS['conf']['kernel.cache_dir']);
        }
        if (empty($GLOBALS['conf']['kernel.logs_dir'])) {
            unset($GLOBALS['conf']['kernel.logs_dir']);
        }

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
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return $this->logDir;
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
    private function discoverDrupalServicesDefinitionFiles()
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
    private function discoverDrupalServiceProviders()
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
        $container = new ContainerBuilder(new DrupalParameterBag($this->getKernelParameters()));
        $container->setParameter('kernel.drupal_site_path', DRUPAL_ROOT . '/' . conf_path());

        if (class_exists('ProxyManager\Configuration') && class_exists('Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator')) {
            $container->setProxyInstantiator(new RuntimeInstantiator());
        }

        return $container;
    }

    /**
     * I am so, so sorry I had to rewrite this, just because once the container
     * has been require_once'ed, it cannot be a second time during the same PHP
     * runtime, and container refresh does not work upon Drupal module enable.
     *
     * {@inheritdoc}
     */
    protected function initializeContainer()
    {
        $class = $this->getContainerClass();
        $cache = new ConfigCache($this->getCacheDir().'/'.$class.'.php', $this->debug);
        $fresh = true;
        if (!$cache->isFresh()) {
            $container = $this->buildContainer();
            $container->compile();
            $this->dumpContainer($cache, $container, $class, $this->getContainerBaseClass());

            $fresh = false;

            // Those 2 lines are the actual patch.
            $this->container = $container;
            return;
        }

        require_once $cache->getPath();

        $this->container = new $class();
        $this->container->set('kernel', $this);

        if (!$fresh && $this->container->has('cache_warmer')) {
            $this->container->get('cache_warmer')->warmUp($this->container->getParameter('kernel.cache_dir'));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function buildContainer()
    {
        $container = parent::buildContainer();

        foreach ($this->discoverDrupalServicesDefinitionFiles() as $filename) {
            $loader = new YamlFileLoader($container, new FileLocator(dirname($filename)));
            $loader->load(basename($filename));
        }

        foreach ($this->discoverDrupalServiceProviders() as $provider) {
            $provider->register($container);
        }

        return $container;
    }
}
