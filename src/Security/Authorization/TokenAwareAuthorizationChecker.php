<?php

namespace MakinaCorpus\Drupal\Sf\Security\Authorization;

use Drupal\Core\Session\AccountInterface;

use MakinaCorpus\Drupal\Sf\Security\DrupalUser;
use MakinaCorpus\Drupal\Sf\Security\Token\UserToken;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TokenAwareAuthorizationChecker implements AuthorizationCheckerInterface
{
    private $authorizationChecker;
    private $accessDecisionManager;

    /**
     * Default constructor
     *
     * @param AuthorizationCheckerInterface $nested
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        AccessDecisionManagerInterface $accessDecisionManager
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->accessDecisionManager = $accessDecisionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($attributes, $object = null, $user = null)
    {
        if (!$user) {
            return $this->authorizationChecker->isGranted($attributes, $object);
        }

        $token = null;

        if ($user instanceof TokenInterface) {
            $token = $user;
        } else if ($user instanceof AccountInterface) {
            $token = new UserToken();
            $token->setUser(new DrupalUser($user));
        } else {
            $token = new UserToken();
            $token->setUser($user);
        }

        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }

        return $this->accessDecisionManager->decide($token, $attributes, $object);
    }
}
