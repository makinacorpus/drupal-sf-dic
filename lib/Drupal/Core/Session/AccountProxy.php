<?php

/**
 * @file
 * Contains \Drupal\Core\Session\AccountProxy.
 */

namespace Drupal\Core\Session;

use Drupal\Core\Entity\EntityManager;
use Drupal\user\User;

/**
 * This is inspired from Drupal 8 homonymous class, but this is not the same
 * implementation, at all.
 */
class AccountProxy implements AccountInterface
{
    /**
     * @var AccountInterface
     */
    private $account = null;

    /**
     * @var \stdClass
     */
    private $originalAccount = null;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Default constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Pass-throught for Drupal 7 compatibility
     */
    public function __set($name, $value)
    {
        $this->originalAccount->{$name} = $value;
    }

    /**
     * Pass-throught for Drupal 7 compatibility
     */
    public function __get($name)
    {
        if ($this->account) {
            if (property_exists($this->account, $name)) {
                return $this->account->{$name};
            }
        }
        // A few properties don't exist on the {users} table structure but are
        // set and read by the session handler, such as the 'timestamp' property
        // set in _druapl_session_read() and read in _drupal_session_write().
        if (property_exists($this->originalAccount, $name)) {
            return $this->originalAccount->{$name};
        }
        throw new \LogicException(sprintf("Attempt to access the non existing property '%s' on the global \$user", $name));
    }

    /**
     * Sad but true story, session.inc does a few empty($user->uid) checks
     * which will fail without this implemented, making user session to be
     * dropped at the first site hit.
     */
    public function __isset($name)
    {
        return ($this->account && property_exists($this->account, $name)) || property_exists($this->originalAccount, $name);
    }

    /**
     * Set original account as stdClass that Drupal 7 will in globals
     */
    public function setOriginalAccount($account)
    {
        if ($this->originalAccount) {
            throw new \LogicException("Why would you set me twice ?");
        }

        $this->originalAccount = $account;
    }

    /**
     * Gets the currently wrapped account
     *
     * @return AccountInterface
     */
    public function getAccount()
    {
        if (!$this->account) {
            if ($this->originalAccount && $this->originalAccount->uid) {
                // Prey for Drupal being correctly initialized at this point
                $this->account = $this->entityManager->getStorage('user')->load($this->originalAccount->uid);
            } else if ($this->originalAccount) {
                // User is not logged in, but we do have already a User
                // instance for anonymous user set
                $this->account = $this->originalAccount;
            }
        }

        if (!$this->account instanceof AccountInterface) {
            // @todo why do the hell this happens only during unit tests?
            //   seriously god I hate Drupal, and whatever is arround it
            $values = (array)$this->account;
            $this->account = new User();
            foreach ($values as $key => $value) {
                $this->account->{$key} = $value;
            }
        }

        return $this->account;
    }

    /**
     * {@inheritdoc}
     */
    public function id()
    {
        return $this->originalAccount ? $this->originalAccount->uid : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles($exclude_locked_roles = FALSE)
    {
        return $this->getAccount()->getRoles($exclude_locked_roles);
    }

    /**
     * {@inheritdoc}
     */
    public function hasPermission($permission)
    {
        return $this->getAccount()->hasPermission($permission);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthenticated()
    {
        return $this->getAccount()->isAuthenticated();
    }

    /**
     * {@inheritdoc}
     */
    public function isAnonymous()
    {
        return $this->getAccount()->isAnonymous();
    }

    /**
     * {@inheritdoc}
     */
    public function getPreferredLangcode($fallback_to_default = TRUE)
    {
        return $this->getAccount()->getPreferredLangcode($fallback_to_default);
    }

    /**
     * {@inheritdoc}
     */
    public function getPreferredAdminLangcode($fallback_to_default = TRUE)
    {
        return $this->getAccount()->getPreferredAdminLangcode($fallback_to_default);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->getAccountName();
    }

    /**
     * {@inheritdoc}
     */
    public function getAccountName()
    {
        return $this->getAccount()->getAccountName();
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->getAccount()->getDisplayName();
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->getAccount()->getEmail();
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeZone()
    {
        return $this->getAccount()->getTimeZone();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastAccessedTime()
    {
        return $this->getAccount()->getLastAccessedTime();
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialAccountId($account_id)
    {
        if (isset($this->account)) {
            throw new \LogicException('AccountProxyInterface::setInitialAccountId() cannot be called after an account was set on the AccountProxy');
        }

        $this->initialAccountId = $account_id;
    }
}
