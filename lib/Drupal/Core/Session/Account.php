<?php

namespace Drupal\Core\Session;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
class Account implements AccountInterface
{
    public $uid = 0;
    public $name = "Anonymous";
    public $pass = '';
    public $mail = '';
    public $theme;
    public $signature;
    public $signature_format;
    public $created;
    public $access;
    public $login;
    public $status;
    public $timezone;
    public $language;
    public $picture;
    public $init;
    public $data;
    public $roles = [];

    /**
     * {@inheritdoc}
     */
    public function id()
    {
        return $this->uid;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles($exclude_locked_roles = FALSE)
    {
        $roles = $this->roles;

        if ($exclude_locked_roles) {
            unset(
                $roles[AccountInterface::ANONYMOUS_ROLE],
                $roles[AccountInterface::AUTHENTICATED_ROLE]
            );
        }

        return array_keys($roles);
    }

    /**
     * {@inheritdoc}
     */
    public function hasPermission($permission)
    {
        return user_access($permission, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthenticated()
    {
        return 0 != $this->uid;
    }

    /**
     * {@inheritdoc}
     */
    public function isAnonymous()
    {
        return 0 == $this->uid;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreferredLangcode($fallback_to_default = TRUE)
    {
        if ($this->language) {
            return $this->language;
        }
        if ($fallback_to_default) {
            return $GLOBALS['language']->language;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPreferredAdminLangcode($fallback_to_default = TRUE)
    {
        return $this->getPreferredLangcode($fallback_to_default);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccountName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return filter_xss(format_username($this));
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->mail;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeZone()
    {
        return $this->timezone;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastAccessedTime()
    {
        return $this->access;
    }
}
