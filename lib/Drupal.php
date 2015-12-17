<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Drupal 8 compatibility
 */
class Drupal
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    static protected $container;

    /**
     * Find all services.yml files
     *
     * @return string[]
     *   Path to files
     */
    static protected function _findFiles()
    {
        $ret = [];

        // Add self
        require_once DRUPAL_ROOT . '/includes/common.inc';
        $ret['sf_dic'] = drupal_get_path('module', 'sf_dic') . '/sf_dic.services.yml';

        // Find all module.services.yml files, note that this will do a
        // file_exists() per module, but this will skipped whenever the
        // container file will be cached
        foreach (array_keys(system_list('module_enabled')) as $module) {
            $filename = drupal_get_path('module', $module) . '/' . $module . '.services.yml';
            if (file_exists($filename)) {
                $ret[$module] = $filename;
            }
        }

        return $ret;
    }

    /**
     * This is not a compatilibity layer but the main initializer
     */
    static public function _init()
    {
        if (null !== self::$container) {
            return;
        }

        $cachepath = variable_get('sf_cache_path', conf_path() . '/files');
        $cachefile = $cachepath . '/container.php';

        if (@include_once $cachefile) {
            self::$container = new ProjectServiceContainer();
            return;
        }

        $container = new ContainerBuilder();

        // Build a new container, we need to find all modules having a
        // services.yml file defined
        foreach (static::_findFiles() as $filename) {
            $loader = new YamlFileLoader($container, new FileLocator(dirname($filename)));
            $loader->load(basename($filename));
        }

        $container->compile();

        $oups = file_put_contents(
            $cachefile,
            (new PhpDumper($container))
                ->dump([
                    'base_class' => '\MakinaCorpus\Drupal\Sf\Container\Container',
                ])
        );

        if (false === $oups) {
            throw new RuntimeException("Cannot write the container file !");
        }

        self::$container = $container;
    }

    /**
     * Sets a new global container
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    static public function setContainer(ContainerInterface $container)
    {
        static::$container = $container;
    }

    /**
     * Unsets the global container
     */
    static public function unsetContainer()
    {
        static::$container = null;
    }

    /**
     * Returns the currently active global container
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     *
     * @throws \LogicException
     */
    static public function getContainer()
    {
        if (null === static::$container) {
            throw new LogicException('\Drupal::$container is not initialized yet. \Drupal::setContainer() must be called with a real container.');
        }

        return static::$container;
    }

    /**
     * Has the container been initialized
     *
     * @return bool
     */
    static public function hasContainer()
    {
        return null !== static::$container;
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
        return static::getContainer()->get($id);
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
        return DRUPAL_ROOT;
    }

      /**
       * Returns the current primary database
       *
       * @return \DatabaseConnection
       *   The current active database's master connection.
       */
      static public function database()
      {
          return Database::getConnection();
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
