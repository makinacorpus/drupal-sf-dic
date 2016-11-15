<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Mockup;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SecurityToken extends AbstractToken
{
    public function getCredentials()
    {
        throw new \LogicException("tests should never get here");
    }
}
