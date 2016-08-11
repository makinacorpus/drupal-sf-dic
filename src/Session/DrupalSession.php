<?php

namespace MakinaCorpus\Drupal\Sf\Session;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

/**
 * Very basic session replacement that directly reads and write through the
 * $_SESSION superglobal instead of relying onto a SessionStorageInterface
 * backend.
 */
class DrupalSession extends Session
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        throw new \LogicException("You should not call this method, this is a basic replacement for basic features.");
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return array_key_exists($name, $_SESSION);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        if (is_array($_SESSION) && array_key_exists($name, $_SESSION)) {
            return $_SESSION[$name];
        }
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $_SESSION;
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $_SESSION[$name] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        unset($_SESSION[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        throw new \LogicException("You should not call this method, this is a basic replacement for basic features.");
    }

    /**
     * Returns an iterator for attributes.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new \ArrayIterator($_SESSION);
    }

    /**
     * Returns the number of attributes.
     *
     * @return int The number of attributes
     */
    public function count()
    {
        return count($_SESSION);
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate($lifetime = null)
    {
        throw new \LogicException("You should not call this method, this is a basic replacement for basic features.");
    }

    /**
     * {@inheritdoc}
     */
    public function migrate($destroy = false, $lifetime = null)
    {
        throw new \LogicException("You should not call this method, this is a basic replacement for basic features.");
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        throw new \LogicException("You should not call this method, this is a basic replacement for basic features.");
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        throw new \LogicException("You should not call this method, this is a basic replacement for basic features.");
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        throw new \LogicException("You should not call this method, this is a basic replacement for basic features.");
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataBag()
    {
        throw new \LogicException("You should not call this method, this is a basic replacement for basic features.");
    }

    /**
     * {@inheritdoc}
     */
    public function registerBag(SessionBagInterface $bag)
    {
        throw new \LogicException("You should not call this method, this is a basic replacement for basic features.");
    }

    /**
     * {@inheritdoc}
     */
    public function getBag($name)
    {
        throw new \LogicException("You should not call this method, this is a basic replacement for basic features.");
    }

    /**
     * Gets the flashbag interface.
     *
     * @return FlashBagInterface
     */
    public function getFlashBag()
    {
        return new DrupalFlashBag();
    }
}