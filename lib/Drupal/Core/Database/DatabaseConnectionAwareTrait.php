<?php

namespace Drupal\Core\Database;

trait DatabaseConnectionAwareTrait
{
    /**
     * @var \DatabaseConnection
     */
    protected $databaseConnection;

    /**
     * Set database connection
     *
     * @param \DatabaseConnection $connection
     */
    public function setDatabaseConnection(\DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }
}
