<?php

namespace MakinaCorpus\Drupal\Sf\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class UserToken extends AbstractToken
{
    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return [$this->getUser()->getUsername(), $this->getUser()->getPassword()];
    }
}
