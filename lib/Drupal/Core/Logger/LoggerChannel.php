<?php

namespace Drupal\Core\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
final class LoggerChannel extends AbstractLogger implements LoggerChannelInterface
{
    /**
     * The name of the channel of this logger instance.
     *
     * @var string
     */
    protected $channel;

    /**
     * @var LoggerInterface[]
     */
    protected $loggers = [];

    /**
     * Map of PSR3 log constants to RFC 5424 log constants.
     *
     * @var int[]
     */
    protected $levelTranslation = [
        LogLevel::EMERGENCY => WATCHDOG_EMERGENCY,
        LogLevel::ALERT     => WATCHDOG_ALERT,
        LogLevel::CRITICAL  => WATCHDOG_CRITICAL,
        LogLevel::ERROR     => WATCHDOG_ERROR,
        LogLevel::WARNING   => WATCHDOG_WARNING,
        LogLevel::NOTICE    => WATCHDOG_NOTICE,
        LogLevel::INFO      => WATCHDOG_INFO,
        LogLevel::DEBUG     => WATCHDOG_DEBUG,
    ];

    /**
     * Constructs a LoggerChannel object
     *
     * @param string $channel
     *   The channel name for this instance
     */
    public function __construct($channel)
    {
        $this->channel = $channel;
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = [])
    {
        $context += [
            'channel'     => $this->channel,
            'link'        => '',
            'user'        => null,
            'uid'         => \Drupal::currentUser()->id(),
            'request_uri' => $_SERVER['REQUEST_URI'],
            'referer'     => '',
            'ip'          => '',
            'timestamp'   => time(),
        ];

        if (is_string($level)) {
            // Convert to integer equivalent for consistency with RFC 5424.
            $level = $this->levelTranslation[$level];
        }

        foreach ($this->sortLoggers() as $logger) {
            $logger->log($level, $message, $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setLoggers(array $loggers)
    {
        $this->loggers = $loggers;
    }

    /**
     * {@inheritDoc}
     */
    public function addLogger(LoggerInterface $logger, $priority = 0)
    {
        $this->loggers[$priority][] = $logger;
    }

    /**
     * Sorts loggers according to priority.
     *
     * @return array
     *   An array of sorted loggers by priority.
     */
    public function sortLoggers()
    {
        $sorted = [];
        krsort($this->loggers);

        foreach ($this->loggers as $loggers) {
            $sorted = array_merge($sorted, $loggers);
        }

        return $sorted;
    }
}

