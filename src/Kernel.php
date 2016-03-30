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
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpFoundation\Request;

class Kernel extends BaseKernel
{
    /**
     * @var BundleInterface[]
     */
    protected $extraBundles = [];

    /**
     * @var boolean
     */
    protected $inDrupal = true;

    /**
     * @var boolean
     */
    protected $isFullStack = false;

    /**
     * Default constructor
     *
     * @param string $environment
     * @param boolean $debug
     */
    public function __construct($environment = 'prod', $debug = false, $inDrupal = true)
    {
        if (!empty($GLOBALS['conf']['kernel.root_dir'])) {
            $this->rootDir = $GLOBALS['conf']['kernel.root_dir'];
        } else {
            $this->rootDir = DRUPAL_ROOT . '/../app';
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

        $this->inDrupal = $inDrupal;

        parent::__construct($environment, $debug);
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
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if (false === $this->booted) {
            $this->boot();
        }

        // Sorry, no real request handle there since we are in a Drupal instance
        if ($this->container->has('request_stack')) {
            $this->container->get('request_stack')->push($request);
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
            if (variable_get('kernel.symfony_all_the_way', true)) {
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
            }
        }

        return $this->extraBundles;
    }

    /**
     * {inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // @todo This needs to be overridable, and should be controlled by the
        // site owner instead... Maybe this could be loaded from settings.php
        // file at some point, or just put into the site/default/config folder
        if ($this->isFullStack) {
            $loader->load(__DIR__ . '/../Resources/config/config.yml');
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
