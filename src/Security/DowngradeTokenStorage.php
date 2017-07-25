<?php

namespace MakinaCorpus\Drupal\Sf\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Not so gracefull downgrade of the token storage implementation, this will
 * always return a new instance of the currently logged in Drupal account as
 * a Symfony token
 */
class DowngradeTokenStorage implements TokenStorageInterface
{
    private $token;

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        if (!$this->token) {
            $this->token = new DrupalUser($GLOBALS['user']);
        }

        return $this->token;
    }

    /**
     * {@inheritdoc}
     */
    public function setToken(TokenInterface $token = null)
    {
        $this->token = $token;
    }
}
