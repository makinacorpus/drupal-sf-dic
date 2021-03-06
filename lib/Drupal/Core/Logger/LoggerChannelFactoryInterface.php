<?php

namespace Drupal\Core\Logger;

use Psr\Log\LoggerInterface;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
interface LoggerChannelFactoryInterface
{
    /**
     * Retrieves the registered logger for the requested channel.
     *
     * @return \Drupal\Core\Logger\LoggerChannelInterface
     *   The registered logger for this channel.
     */
    public function get($channel);

    /**
     * Adds a logger.
     *
     * Here is were all services tagged as 'logger' are being retrieved and then
     * passed to the channels after instantiation.
     *
     * @param \Psr\Log\LoggerInterface $logger
     *   The PSR-3 logger to add.
     * @param int $priority
     *   The priority of the logger being added.
     */
    public function addLogger(LoggerInterface $logger, $priority = 0);
}
