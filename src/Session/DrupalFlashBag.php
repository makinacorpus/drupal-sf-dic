<?php

namespace MakinaCorpus\Drupal\Sf\Session;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class DrupalFlashBag extends FlashBag
{
    /**
     * {@inheritdoc}
     */
    public function initialize(array &$flashes)
    {
        $_SESSION['messages'] = &$flashes;
    }

    /**
     * {@inheritdoc}
     */
    public function add($type, $message)
    {
        // Convert to Drupal error levels
        switch ($type) {

            case 'notice':
            case 'info':
            case 'status':
            case 'success':
                $type = 'status';
                break;

            case 'danger':
            case 'error':
                $type = 'error';
                break;

            case 'warning':
            case 'debug':
                $type = 'warning';
                break;
        }

        $_SESSION['messages'][$type][] = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function peek($type, array $default = array())
    {
        return isset($_SESSION['messages'][$type]) ? $_SESSION['messages'][$type] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function peekAll()
    {
        if (isset($_SESSION['messages'])) {
            return $_SESSION['messages'];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function get($type, array $default = array())
    {
        if (!isset($_SESSION['messages'][$type])) {
            return $default;
        }

        $return = $_SESSION['messages'][$type];

        unset($_SESSION['messages'][$type]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $return = $_SESSION['messages'];
        unset($_SESSION['messages']);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function set($type, $messages)
    {
        $_SESSION['messages'][$type] = (array) $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function setAll(array $messages)
    {
        $_SESSION['messages'] = $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function has($type)
    {
        return !empty($_SESSION['messages'][$type]);
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        return array_keys($_SESSION['messages']);
    }
}
