<?php

namespace Drupal\Core\Logger;

use Psr\Log\LoggerInterface;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
final class LoggerChannelFactory implements LoggerChannelFactoryInterface
{
    /**
     * Array of all instantiated logger channels keyed by channel name
     *
     * @var \Drupal\Core\Logger\LoggerInterface[]
     */
    protected $channels = [];

    /**
     * {@inheritdoc}
     */
    public function get($channel)
    {
        if (!isset($this->channels[$channel])) {
            $instance = new LoggerChannel($channel);

            // Pass the loggers to the channel.
            $instance->setLoggers($this->loggers);
            $this->channels[$channel] = $instance;
        }

        return $this->channels[$channel];
    }

    /**
     * {@inheritdoc}
     */
    public function addLogger(LoggerInterface $logger, $priority = 0)
    {
        // Store it so we can pass it to potential new logger instances.
        $this->loggers[$priority][] = $logger;

        // Add the logger to already instantiated channels.
        foreach ($this->channels as $channel) {
            $channel->addLogger($logger, $priority);
        }
    }
}
