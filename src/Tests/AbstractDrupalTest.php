<?php

namespace MakinaCorpus\Drupal\Sf\Tests;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use MakinaCorpus\Drupal\Sf\DefaultAppKernel;
use MakinaCorpus\Drupal\Sf\Kernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Please be aware that when working with this base class, everything you do
 * you actually do in the real Drupal site, there is no environment isolation
 * so it will mess up with your data.
 */
abstract class AbstractDrupalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Is a Drupal instance bootstrapped
     *
     * @var bool
     */
    static private $bootstrapped = false;

    /**
     * A database connection object from Drupal
     *
     * @var mixed
     */
    static private $databaseConnection;

    /**
     * _drupal_bootstrap_configuration() override
     */
    static private function bootstrapConfiguration()
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = '127.0.0.1';
        }
        if (!isset($_SERVER['HTTP_REFERER'])) {
            $_SERVER['HTTP_REFERER'] = '';
        }
        if (!isset($_SERVER['SERVER_PROTOCOL']) || ($_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.0' && $_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.1')) {
            $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        }
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        }
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            $_SERVER['REQUEST_METHOD'] = 'GET';
        }

        //drupal_settings_initialize();
    }

    /**
     * Find if a Drupal instance is configured for testing and bootstrap it if
     * found.
     *
     * @return \DatabaseConnection
     */
    static private function findDrupalDatabaseConnection()
    {
        if (self::$databaseConnection) {
            return self::$databaseConnection;
        }

        $variableName = 'DRUPAL_PATH';

        // Try to find out the right site root.
        $directory = getenv($variableName);

        if (!is_dir($directory)) {
            throw new \RuntimeException(sprintf("%s: directory does not exists", $directory));
        }
        if (!file_exists($directory . '/index.php')) {
            throw new \RuntimeException(sprintf("%s: directory is not a PHP application directory", $directory));
        }

        $bootstrapInc = $directory . '/includes/bootstrap.inc';
        if (!is_file($bootstrapInc)) {
            throw new \RuntimeException(sprintf("%s: is a not a Drupal installation or version mismatch", $directory));
        }

        if (!$handle = fopen($bootstrapInc, 'r')) {
            throw new \RuntimeException(sprintf("%s: cannot open for reading", $bootstrapInc));
        }

        $buffer = fread($handle, 512);
        fclose($handle);

        $matches = [];
        if (preg_match("/^\s*define\('VERSION', '([^']+)'/ims", $buffer, $matches)) {
            list($parsedMajor) = explode('.', $matches[1]);
        }
        if (!isset($parsedMajor) || empty($parsedMajor)) {
            throw new \RuntimeException(sprintf("%s: could not parse core version", $bootstrapInc));
        }

        // realpath() is necessary in order to avoid symlinks messing up with
        // Drupal path when testing in a console which hadn't hardened the env
        // using a chroot() unlink PHP-FPM
        if (defined('DRUPAL_ROOT')) {
            if (DRUPAL_ROOT !== realpath($directory)) {
                throw new \LogicException(sprintf("'DRUPAL_ROOT' is already defined and does not point toward the same root"));
            }
        } else {
            define('DRUPAL_ROOT', realpath($directory));
        }

        require_once $bootstrapInc;

        self::bootstrapConfiguration();

        drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);

        return self::$databaseConnection = \Database::getConnection();
    }

    /**
     * @var \ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nullModuleHandler;

    /**
     * @var \DrupalCacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nullLegacyCache;

    /**
     * @var CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nullCacheBackend;

    /**
     * @var \DatabaseConnection
     */
    private $db;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \stdClass[]
     */
    private $accounts = [];

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * Create a Drupal user
     *
     * @param string[] $permissionList
     *   Permission string list
     * @param string $name
     *   Name for debugging purposes
     *
     * @return AccountInterface
     */
    protected function createDrupalUser($permissionList = [], $name = null)
    {
        /* @var $storage \Drupal\Core\Entity\EntityStorageInterface */
        $storage = $this->getDrupalContainer()->get('entity.manager')->getStorage('user');

        $account = $storage->create();
        $this->accounts[] = $account;
        // SHA1 reduces size and avoid data truncation in database - which leads
        // to real errors with PosgreSQL - MySQL should be ashamed to let this
        // happen
        $stupidHash = sha1(uniqid() . mt_rand());
        $account->mail = $stupidHash . '@example.com';
        $account->roles = [];

        if ($name) {
            $account->name = substr($name . ' ' . rand(0, 99999), 0, 59);
        } else {
            $account->name = $stupidHash;
        }

        // Fake user access cache for testing
        if ($permissionList) {
            $data = &drupal_static('user_access');
            $data[$account->uid] = array_combine($permissionList, $permissionList);

            // Also, find the first role for each permission and set them
            // to the user
            $database = $this->getDatabaseConnection();
            $roleIdList = $database->query(
                "
                    SELECT DISTINCT MIN(rid)
                    FROM {role_permission}
                    WHERE permission IN (:perm)
                    GROUP BY permission
                ",
                [':perm' => $permissionList]
            )->fetchCol();

            $account->roles = array_combine($roleIdList, $roleIdList);
        }

        $storage->save($account);

        return $account;
    }

    /**
     * Get Drupal anonymous user
     *
     * @return AccountInterface
     */
    final protected function getAnonymousUser()
    {
        return drupal_anonymous_user();
    }

    /**
     * @return ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getNullModuleHandler()
    {
        if (!$this->nullModuleHandler) {
            $this->nullModuleHandler = $this->createMock('\Drupal\Core\Extension\ModuleHandlerInterface');
        }

        return $this->nullModuleHandler;
    }

    /**
     * @return CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getNullCacheBackend()
    {
        if (!$this->nullCacheBackend) {
            $this->nullCacheBackend = $this->createMock('\Drupal\Core\Cache\CacheBackendInterface');
        }

        return $this->nullCacheBackend;
    }

    /**
     * @return \DrupalCacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getNullLegacyCache()
    {
        if (!$this->nullLegacyCache) {
            $this->nullLegacyCache = $this->createMock('\DrupalCacheInterface');
        }

        return $this->nullLegacyCache;
    }

    /**
     * Get current Drupal site database connection
     *
     * @return \DatabaseConnection
     */
    final protected function getDatabaseConnection()
    {
        return $this->db;
    }

    protected function createKernelInstance($env, $debug = true)
    {
        if (class_exists('\AppKernel')) {
            return new \AppKernel($env, $debug);
        } else {
            return new DefaultAppKernel($env, $debug);
        }
    }

    /**
     * Get kernel
     *
     * @return KernelInterface
     */
    private function getKernel()
    {
        if (!$this->kernel) {

            if (!self::$bootstrapped) {
                // Avoid Drupal attempt to return a cached page while we are
                // actually unit testing it
                drupal_bootstrap(DRUPAL_BOOTSTRAP_CONFIGURATION);
                $GLOBALS['conf']['cache'] = 0;
                drupal_page_is_cacheable(false);
                drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
                self::$bootstrapped = true;
            }

            $this->kernel = $this->createKernelInstance(uniqid('test_'));

            $this->kernel->boot();
            $this
                ->kernel
                ->getContainer()
                ->get('request_stack')
                ->push(
                    \MakinaCorpus\Drupal\Sf\Http\Request::createFromGlobals()
                )
            ;

            \Drupal::_setKernel($this->kernel);
        }

        return $this->kernel;
    }

    /**
     * Get current Drupal site service container
     *
     * @return ContainerInterface
     */
    final protected function getDrupalContainer()
    {
        return $this->getKernel()->getContainer();
    }

    /**
     * Is module enabled
     *
     * @param unknown $module
     *
     * @return bool
     */
    final protected function moduleExists($module)
    {
        self::findDrupalDatabaseConnection();
        drupal_bootstrap(DRUPAL_BOOTSTRAP_LANGUAGE);

        $list = system_list('module_enabled');

        return isset($list[$module]);
    }

    /**
     * Get the entity manager
     *
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDrupalContainer()->get('entity.manager');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        // Do not catch anything, not finding the connection is an error.
        $this->db = self::findDrupalDatabaseConnection();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        if ($this->accounts) {
            foreach ($this->accounts as $account) {
                if ($account->uid) {
                    user_delete($account->uid);
                }
            }
        }

        unset(
            $this->kernel,
            $this->nullCacheBackend,
            $this->nullLegacyCache,
            $this->nullModuleHandler
        );

        parent::tearDown();
    }
}
