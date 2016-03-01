<?php

namespace MakinaCorpus\Drupal\Sf\Container\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class DrupalLogger extends AbstractLogger
{
    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = [])
    {
        switch ($level) {

            case LogLevel::CRITICAL:
                $level = WATCHDOG_CRITICAL;
                break;

            case LogLevel::DEBUG:
                $level = WATCHDOG_DEBUG;
                break;

            case LogLevel::EMERGENCY:
                $level = WATCHDOG_EMERGENCY;
                break;

            case LogLevel::ERROR:
                $level = WATCHDOG_ERROR;
                break;

            case LogLevel::INFO:
                $level = WATCHDOG_INFO;
                break;

            case LogLevel::NOTICE:
                $level = WATCHDOG_NOTICE;
                break;

            case LogLevel::WARNING:
                $level = WATCHDOG_WARNING;
                break;

            default:
                $level = WATCHDOG_INFO;
                break;
        }

        $context += ['channel' => 'unknown'];

        watchdog($context['channel'], $message, $context, $level);
    }
}
