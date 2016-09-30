<?php

use Drupal\Core\Session\AccountInterface;

use MakinaCorpus\Drupal\Sf\Kernel;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Drupal 8 compatibility
 */
class Drupal
{
    /**
     * @var KernelInterface
     */
    static protected $kernel;

    /**
     * @var mixed[]
     */
    static protected $bundles = [];

    /**
     * Set kernel
     *
     * @param KernelInterface $kernel
     */
    static public function _setKernel(KernelInterface $kernel)
    {
        self::$kernel = $kernel;
    }

    static private function _buildKernel()
    {
        if (!self::$kernel) {

            $env    = empty($GLOBALS['conf']['kernel.environment']) ? 'dev' : $GLOBALS['conf']['kernel.environment'];
            $debug  = !isset($GLOBALS['conf']['kernel.debug']) ? true : $GLOBALS['conf']['kernel.debug'];

            if (class_exists('AppKernel')) {
                self::$kernel = new AppKernel($env, $debug);
            } else {
                self::$kernel = new Kernel($env, $debug);
            }

            // Keep the Symfony bootstrap optimization, even though we broke it
            // a little by loading the kernel before; lots of other classes will
            // still benefit from it.
            @include_once self::$kernel->getCacheDir() . '/classes.map';

            // @todo serious ugly patch, see registerBundles()
            if (self::$bundles) {
                self::$kernel->addExtraBundles(array_values(self::$bundles));
            }
        }

        return self::$kernel;
    }

    /**
     * Register bundles
     *
     * Important note: this must run before sf_dic_boot() which means that you
     * have only two entry points for this:
     *  - either hardcode the call into your settings.php file (recommended);
     *  - or do it in a hook_boot() called before the sf_dic module one.
     *
     * @param BundleInterface[] $bundles
     */
    static public function registerBundles($bundles)
    {
        // @todo serious ugly patch, because unsetContainer() needs the bundles
        //   to be set once again. Find a better way to register Drupal modules
        //   bundles
        foreach ($bundles as $bundle) {
            $class = get_class($bundle);
            if (!isset(self::$bundles[$class])) {
                self::$bundles[$class] = $bundle;
            }
        }
    }

    /**
     * Get kernel
     *
     * @return KernelInterface
     */
    static public function _getKernel()
    {
        return self::_buildKernel();
    }

    /**
     * Returns the currently active global container
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    static public function getContainer()
    {
        $kernel = self::_getKernel();

        // We consider that once this called you cannot register bundles anymore
        $kernel->boot();

        return $kernel->getContainer();
    }

    /**
     * Has the container been initialized
     *
     * @return bool
     */
    static public function hasContainer()
    {
        return true;
    }

    /**
     * Unset and invalidate the container
     */
    static public function unsetContainer()
    {
        $kernel = self::_getKernel();

        // We need to spawn the kernel (if not already) in order to clear the
        // cache folder manually, we will then reset it.
        if ($kernel instanceof Kernel) {
            $kernel->dropCache();
        }

        // Fully reset the container, and prey for other modules to find the
        // right one. In theory, if they didn't referenced their services into
        // statics, it should be fine, new one will transparently replace the
        // old one in \Drupal::service() calls.

        // Please note that as a side effect, it will boot() again the bundles
        // if they are registered bundles, they might mess up with globals or
        // configuration.
        self::$kernel = null;
    }

    /**
     * Retrieves a service from the container
     *
     * Use this method if the desired service is not one of those with a dedicated
     * accessor method below. If it is listed below, those methods are preferred
     * as they can return useful type hints.
     *
     * @param string $id
     *   The ID of the service to retrieve
     *
     * @return mixed
     *   The specified service
     */
    static public function service($id)
    {
        return self::getContainer()->get($id);
    }

    /**
     * Indicates if a service is defined in the container
     *
     * @param string $id
     *   The ID of the service to check
     *
     * @return bool
     */
    static public function hasService($id)
    {
        // Check hasContainer() first in order to always return a Boolean.
        return static::hasContainer() && static::getContainer()->has($id);
    }

    /**
     * Gets the app root.
     *
     * @return string
     */
    static public function root()
    {
        return self::$kernel->getAppRoot();
    }

    /**
     * Returns the current primary database
     *
     * @return \DatabaseConnection
     */
    static public function database()
    {
        return \Database::getConnection();
    }

    /**
     * Returns the current user
     *
     * @return AccountInterface
     */
    static public function currentUser()
    {
        return self::getContainer()->get('current_user');
    }

    /**
     * Returns the form builder service
     *
     * @return \Drupal\Core\Form\FormBuilderInterface
     */
    public static function formBuilder()
    {
        return static::getContainer()->get('form_builder');
    }

    /**
     * Indicates if there is a currently active request object.
     *
     * @return bool
     *   TRUE if there is a currently active request object, FALSE otherwise.
     */
    public static function hasRequest()
    {
        // Check hasContainer() first in order to always return a Boolean.
        return
            static::hasContainer() &&
            static::getContainer()->has('request_stack') /* &&
            static::getContainer()->get('request_stack')->getCurrentRequest() !== null */
        ;
    }

    /**
     * Retrieves the currently active request object.
     *
     * Note: The use of this wrapper in particular is especially discouraged. Most
     * code should not need to access the request directly.  Doing so means it
     * will only function when handling an HTTP request, and will require special
     * modification or wrapping when run from a command line tool, from certain
     * queue processors, or from automated tests.
     *
     * If code must access the request, it is considerably better to register
     * an object with the Service Container and give it a setRequest() method
     * that is configured to run when the service is created.  That way, the
     * correct request object can always be provided by the container and the
     * service can still be unit tested.
     *
     * If this method must be used, never save the request object that is
     * returned.  Doing so may lead to inconsistencies as the request object is
     * volatile and may change at various times, such as during a subrequest.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     *   The currently active request object.
     */
    public static function request()
    {
        return static::getContainer()->get('request_stack')->getCurrentRequest();
    }

    /**
     * Retrives the request stack.
     *
     * @return \Symfony\Component\HttpFoundation\RequestStack
     *   The request stack
     */
    public static function requestStack()
    {
        return static::getContainer()->get('request_stack');
    }

    /**
     * Returns the requested cache bin.
     *
     * @param string $bin
     *   (optional) The cache bin for which the cache object should be returned,
     *   defaults to 'default'.
     *
     * @return \Drupal\Core\Cache\CacheBackendInterface
     *   The cache object associated with the specified bin.
     *
     * @ingroup cache
     */
  //   public static function cache($bin = 'default') {
  //     return static::getContainer()->get('cache.' . $bin);
  //   }

    /**
     * Returns a queue for the given queue name.
     *
     * The following values can be set in your settings.php file's $settings
     * array to define which services are used for queues:
     * - queue_reliable_service_$name: The container service to use for the
     *   reliable queue $name.
     * - queue_service_$name: The container service to use for the
     *   queue $name.
     * - queue_default: The container service to use by default for queues
     *   without overrides. This defaults to 'queue.database'.
     *
     * @param string $name
     *   The name of the queue to work with.
     * @param bool $reliable
     *   (optional) TRUE if the ordering of items and guaranteeing every item
     *   executes at least once is important, FALSE if scalability is the main
     *   concern. Defaults to FALSE.
     *
     * @return \Drupal\Core\Queue\QueueInterface
     *   The queue object for a given name.
     */
  //   public static function queue($name, $reliable = FALSE) {
  //     return static::getContainer()->get('queue')->get($name, $reliable);
  //   }

    /**
     * Returns the default http client.
     *
     * @return \GuzzleHttp\Client
     *   A guzzle http client instance.
     */
  //   public static function httpClient() {
  //     return static::getContainer()->get('http_client');
  //   }
}
