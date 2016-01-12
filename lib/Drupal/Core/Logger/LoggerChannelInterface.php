<?php

/**
 * @file
 * Contains \Drupal\Core\Logger\LoggerChannelInterface.
 */

namespace Drupal\Core\Logger;

use Psr\Log\LoggerInterface;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
interface LoggerChannelInterface extends LoggerInterface
{
    /**
     * Sets the loggers for this channel
     *
     * @param LoggerInterface[] $loggers
     */
    public function setLoggers(array $loggers);

    /**
     * Adds a logger
     *
     * @param LoggerInterface $logger
     * @param int $priority
     */
    public function addLogger(LoggerInterface $logger, $priority = 0);
}
