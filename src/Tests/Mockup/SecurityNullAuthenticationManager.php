<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Mockup;

use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SecurityNullAuthenticationManager implements AuthenticationManagerInterface
{
    public function authenticate(TokenInterface $token)
    {
        $token->setAuthenticated(true);

        return $token;
    }
}
