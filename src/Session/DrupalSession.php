<?php

namespace MakinaCorpus\Drupal\Sf\Session;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;

/**
 * Very basic session replacement that directly reads and write through the
 * $_SESSION superglobal instead of relying onto a SessionStorageInterface
 * backend.
 *
 * Beware that dues to Drupal lazy session handling, $_SESSION superglobal
 * might not be intialized sometime.
 */
class DrupalSession extends Session
{
    private $metadataBag;

    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        // throw new \LogicException("You should not call this method, this is a basic replacement for basic features.");
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return isset($_SESSION) && array_key_exists($name, $_SESSION);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        // It happens that, if called priori to session_start() call that this
        // variable is uninitialized, which causes notices to happen, let's
        // avoid that.
        if (isset($_SESSION) && array_key_exists($name, $_SESSION)) {
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
        return isset($_SESSION) ? $_SESSION : [];
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
        if (isset($_SESSION)) {
            foreach (array_keys($_SESSION) as $key) {
                unset($_SESSION[$key]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return drupal_session_started();
    }

    /**
     * Returns an iterator for attributes.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        if (isset($_SESSION)) {
            return new \ArrayIterator($_SESSION);
        }
        return new \EmptyIterator();
    }

    /**
     * Returns the number of attributes.
     *
     * @return int The number of attributes
     */
    public function count()
    {
        return isset($_SESSION) ? count($_SESSION) : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate($lifetime = null)
    {
        // \trigger_error("You should not call this method, this is a basic replacement for basic features.", E_USER_DEPRECATED);
    }

    /**
     * {@inheritdoc}
     */
    public function migrate($destroy = false, $lifetime = null)
    {
        // \trigger_error("You should not call this method, this is a basic replacement for basic features.", E_USER_DEPRECATED);
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        // Do nothing, Drupal will handle it.
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
        // \trigger_error("You should not call this method, this is a basic replacement for basic features.", E_USER_DEPRECATED);
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
        // \trigger_error("You should not call this method, this is a basic replacement for basic features.", E_USER_DEPRECATED);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataBag()
    {
        if (!$this->metadataBag) {
            $this->metadataBag = new MetadataBag();
        }

        return $this->metadataBag;
    }

    /**
     * {@inheritdoc}
     */
    public function registerBag(SessionBagInterface $bag)
    {
        // \trigger_error("You should not call this method, this is a basic replacement for basic features.", E_USER_DEPRECATED);
    }

    /**
     * {@inheritdoc}
     */
    public function getBag($name)
    {
        // \trigger_error("You should not call this method, this is a basic replacement for basic features.", E_USER_DEPRECATED);
    }

    /**
     * Gets the flashbag interface.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface
     */
    public function getFlashBag()
    {
        return new DrupalFlashBag();
    }
}
