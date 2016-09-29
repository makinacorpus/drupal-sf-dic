<?php

namespace MakinaCorpus\Drupal\Sf\Doctrine;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory as DoctrineConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;

/**
 * Overrides the createConnection() method in order to provider the existing
 * Drupal \PDO instance whenever possible.
 */
class ConnectionFactory extends DoctrineConnectionFactory
{
    /**
     * {@inheritdoc}
     */
    public function createConnection(array $params, Configuration $config = null, EventManager $eventManager = null, array $mappingTypes = array())
    {
        // @todo
        //   - handle connection name

        // Matches for default parameters
        if (empty($params['password']) && empty($params['port']) && 'localhost' === $params['host']) {
            // Attempt to create connection using the Drupal \PDO instance
            if (class_exists('\Database')) {

                $connection = \Database::getConnection();

                switch ($connection->driver()) {

                    case 'mysql':
                        $driver = 'pdo_mysql';
                        break;

                    case 'pgsql':
                        $driver = 'pdo_pgsql';
                        break;

                    case 'sqlite':
                        $driver = 'pdo_sqlite';
                        break;

                    default:
                        throw new \InvalidArgumentException(sprintf("%s: cannot map driver to doctrine's", $connection->driver()));
                }

                $drupalInfo = $connection->getConnectionOptions();
                if (!empty($drupalInfo['prefix']['default'])) {
                    throw new \InvalidArgumentException("Drupal to Doctrine DBAL convertion does not supports table name prefix");
                }

                $map = [
                    'database' => 'dbname',
                    'port' => 'port',
                    'password' => 'password',
                    'username' => 'user',
                    'host' => 'host',
                ];

                $params = ['driver' => $driver];
                foreach ($map as $key => $target) {
                    if (!empty($drupalInfo[$key])) {
                        $params[$target] = $drupalInfo[$key];
                    }
                }

                return parent::createConnection($params, $config, $eventManager, $mappingTypes);
                // return parent::createConnection(['driver' => $driver, 'pdo' => $connection], $config, $eventManager, $mappingTypes);
            }
        }

        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
    }
}
