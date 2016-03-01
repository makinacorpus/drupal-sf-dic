<?php

/**
 * @file
 * Contains \Drupal\Core\DrupalKernel.
 */

namespace Drupal\Core;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\TerminableInterface;

use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\ParameterBag\DrupalParameterBag;

/**
 * Simpler variation of Drupal 8 kernel.
 */
class DrupalKernel implements DrupalKernelInterface, TerminableInterface
{
    use ContainerAwareTrait;

    /**
     * @var ServiceProviderInterface[]
     */
    protected $serviceProviders;

    /**
     * @var boolean
     */
    protected $isRunningTests = false;

    /**
     * Default constructor
     *
     * @param boolean $isRunningTests
     *   Set this to true when running unit tests
     *   @todo best way should be to extend this class instead
     */
    public function __construct($isRunningTests = false)
    {
        $this->isRunningTests = $isRunningTests;
    }

    /**
     * Get compiled container target filename
     *
     * @return string
     */
    protected function getContainerPhpFilename()
    {
        if (!empty($GLOBALS['drupal_test_info'])) {
            $test_info = $GLOBALS['drupal_test_info'];
            $filename = 'container.' . $test_info['test_run_id'] . '.php';
        } else {
            $filename = 'container.php';
        }

        if (isset($GLOBALS['sf_cache_path'])) {
            $path = $GLOBALS['sf_cache_path'];
        } else {
            $path = conf_path() . '/files';
        }

        return $this->getAppRoot() . '/' . $path . '/' . $filename;
    }

    /**
     * Find all services.yml files
     *
     * @return string[]
     *   Path to files
     */
    protected function findServicesDefinitionFiles()
    {
        $ret = [];

        $rootdir = $this->getAppRoot();

        require_once $rootdir . '/includes/common.inc';

        $ret['sf_dic'] = $rootdir . '/' . drupal_get_path('module', 'sf_dic') . '/sf_dic.services.yml';

        // Find all module.services.yml files, this will do a file_exists() per
        // module, but this will skipped whenever the container file is cached
        foreach (array_keys(system_list('module_enabled')) as $module) {

            // Skip current module and keep it first allowing other modules to
            // overrides our services
            if ('sf_dic' === $module) {
                continue;
            }

            $filename = $rootdir . '/' . drupal_get_path('module', $module) . '/' . $module . '.services.yml';

            if (file_exists($filename)) {
                $ret[$module] = $filename;
            }
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function discoverServiceProviders()
    {
        $rootdir = $this->getAppRoot();

        require_once $rootdir . '/includes/common.inc';

        $this->serviceProviders = [];

        foreach (array_keys(system_list('module_enabled')) as $module) {

            $filename = $rootdir . '/' . drupal_get_path('module', $module) . '/' . $module . '.container.php';

            if (file_exists($filename)) {
                include_once $filename;
            }

            $className = 'Drupal\\Module\\' . $module . '\\ServiceProvider';

            if (class_exists($className)) {
                $provider = new $className();
                if ($provider instanceof ServiceProviderInterface) {
                    $this->serviceProviders[$module] = $provider;
                }
            }
        }
    }

    /**
     * Discover and compile container
     *
     * @return ContainerInterface
     */
    protected function compileContainer($targetFile = null)
    {
        // DrupalParameterBag allows to resolve Drupal variables as parameters
        // at compile time, but will froze the used variables once compiled
        $container = new ContainerBuilder(new DrupalParameterBag());

        // Build a new container, we need to find all modules having a services
        // file defined and aggregate them into the container
        foreach ($this->findServicesDefinitionFiles() as $filename) {
            $loader = new YamlFileLoader($container, new FileLocator(dirname($filename)));
            $loader->load(basename($filename));
        }

        $this->discoverServiceProviders();
        foreach ($this->serviceProviders as $provider) {
            $provider->register($container);
        }

        $container->compile();

        if ($targetFile) {

            $oups = file_put_contents(
                $targetFile,
                (new PhpDumper($container))
                    ->dump([
                        'base_class' => '\MakinaCorpus\Drupal\Sf\Container\Container',
                    ])
            );

            if (false === $oups) {
                throw new \RuntimeException("Cannot write the container file !");
            }
        }

        return $container;
    }

    /**
     * Load or compile the container
     *
     * @return ContainerInterface
     */
    protected function loadContainer()
    {
        $filename = true;

        if (!$this->isRunningTests) {
            $filename = $this->getContainerPhpFilename();

            if (@include_once $filename) {
                return new \ProjectServiceContainer();
            }
        }

        return $this->compileContainer($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if (!$this->container) {
            $this->loadContainer();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function shutdown()
    {
        // @todo
    }

    /**
     * Returns the cached container definition - if any.
     *
     * This also allows inspecting a built container for debugging purposes.
     *
     * @return array|NULL
     *   The cached container definition or NULL if not found in cache.
     */
    // public function getCachedContainerDefinition();

    /**
     * {@inheritdoc}
     */
    public function getAppRoot()
    {
        return realpath(DRUPAL_ROOT);
    }

    public function getContainer()
    {
        if (!$this->container) {
            $this->container = $this->loadcontainer();
        }

        return $this->container;
    }

    /**
     * {inheritdoc}
     */
    public function rebuildContainer()
    {
        $this->invalidateContainer();

        return $this->getContainer();
    }

    /**
     * {inheritdoc}
     */
    public function invalidateContainer()
    {
        $filename = $this->getContainerPhpFilename();

        if (file_exists($filename)) {
            if (!@unlink($filename)) {
                throw new \RuntimeException("Cannot delete the container file !");
            }
        }

        $this->container = null;
    }

    /**
     * {@inheritdoc}
     */
    public function preHandle(Request $request)
    {
        $this->getContainer()->get('request_stack')->push($request);
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(Request $request, Response $response)
    {
        // @todo
    }
}
