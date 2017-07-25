<?php

namespace MakinaCorpus\Drupal\Sf\Security;

use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * This is a null implementation, DrupalUser token will always be authenticated.
 */
class DowngradeAuthenticationManager implements AuthenticationManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
    }
}
