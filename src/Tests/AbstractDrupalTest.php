<?php

namespace MakinaCorpus\Drupal\Sf\Container\Tests;

use Symfony\Component\DependencyInjection\ContainerInterface;

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
        if (!isset($_SERVER['HTTP_REFERER'])) {
            $_SERVER['HTTP_REFERER'] = '';
        }
        if (!isset($_SERVER['SERVER_PROTOCOL']) || ($_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.0' && $_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.1')) {
            $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        }

        drupal_settings_initialize();
    }

    /**
     * Find if a Drupal instance is configured for testing and bootstrap it if
     * found.
     *
     * @return \DatabaseConnection
     */
    static private function findDrupalDatabaseConnection()
    {
        if (self::$bootstrapped) {
            return self::$databaseConnection;
        }

        $variableName = 'DRUPAL_PATH';

        // Try to find out the right site root.
        $directory = null;
        if (isset($GLOBALS[$variableName])) {
            $directory = $GLOBALS[$variableName];
        } else {
            $directory = getenv($variableName);
            if (!$directory) {
                throw new \RuntimeException(sprintf("You must configure the %s environment or phpunit variable", $variableName));
            }
        }

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
            throw(sprintf("%s: could not parse core version", $bootstrapInc));
            return null;
        }

        // We are OK to go
        define('DRUPAL_ROOT', $directory);
        require_once $bootstrapInc;

        self::$bootstrapped = true;

        drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);

        return self::$databaseConnection = \Database::getConnection();
    }

    /**
     * @var \DatabaseConnection
     */
    private $db;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Get current Drupal site database connection
     *
     * @return \DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $this->db;
    }

    /**
     * Get current Drupal site service container
     *
     * @return ContainerInterface
     */
    protected function getDrupalContainer()
    {
        return \Drupal::getContainer();
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->db = self::findDrupalDatabaseConnection();

        // @todo
        //   - create temporary container
        //   - create connection on temporary database
        //   - pseudo a minimal site
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        // @todo later destroy temporary container
    }
}
