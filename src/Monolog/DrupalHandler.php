<?php

namespace MakinaCorpus\Drupal\Sf\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class DrupalHandler extends AbstractProcessingHandler
{
    static public function monologToDrupal($level)
    {
        switch ($level)
        {
            case Logger::DEBUG:
                return WATCHDOG_DEBUG;

            case Logger::INFO:
                return WATCHDOG_INFO;

            case Logger::NOTICE:
                return WATCHDOG_NOTICE;

            case Logger::WARNING:
                return WATCHDOG_WARNING;

            case Logger::ERROR:
                return WATCHDOG_ERROR;

            case Logger::CRITICAL:
                return WATCHDOG_CRITICAL;

            case Logger::ALERT:
                return WATCHDOG_ALERT;

            case Logger::EMERGENCY:
                return WATCHDOG_EMERGENCY;

            default:
                return WATCHDOG_ERROR;
        }
    }

    /**
     * Override default constructor.
     *
     * Default log level is debug, but it seems that the monolog bundle does
     * not sets it right on custom services (no setLevel() calls, no constructor
     * arguments).
     */
    public function __construct($level = Logger::ERROR, $bubble = true)
    {
        $this->setLevel($level);
        $this->bubble = $bubble;
    }

    /**
     * Recursive map a set of arrays
     *
     * @param ...$arrays
     *
     * @return string[]
     */
    private function recursiveMap()
    {
        $ret = [];

        $arrays = func_get_args();

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $ret = array_merge($ret, $this->recursiveMap($value));
                } else if (is_string($value)) {
                    $ret['{' . $key . '}'] = (string)$value;
                }
            }
        }

        return $ret;
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        // Pre-bootstrap errors
        if (!function_exists('module_implements')) {
            return;
        }

        $request = \Drupal::requestStack()->getCurrentRequest();

        // Remove unwanted stuff from the context, do not attempt to serialize
        // potential PDO instances of stuff like that may lie into unserialized
        // exceptions in there
        $message = empty($record['formatted']) ? $record['message'] : $record['formatted'];
        foreach ($record['context'] as $key => $value) {
            // @todo temporary avoir Array to string conversion warnings
            if (!is_array($value)) {
                $record['context'][$key] = (string)$value;
            }
        }

        $entry = [
            'severity'    => self::monologToDrupal($record['level']),
            'type'        => $record['channel'],
            'message'     => $message,
            'variables'   => $this->recursiveMap($record),
            'link'        => '',
            'user'        => null,
            'uid'         => \Drupal::currentUser()->id(),
            'request_uri' => $request ? $request->getRequestUri() : null,
            'referer'     => $request ? $request->headers->get('referer') : null,
            'ip'          => $request ? $request->getClientIp() : null,
            'timestamp'   => $request ? $record['datetime']->getTimestamp() : null,
        ];

        foreach (module_implements('watchdog') as $module) {
            module_invoke($module, 'watchdog', $entry);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter()
    {
        return new DrupalFormatter();
    }
}
