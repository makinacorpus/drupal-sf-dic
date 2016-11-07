<?php

namespace MakinaCorpus\Drupal\Sf\Security\Voter;

use MakinaCorpus\Drupal\Sf\Security\DrupalUser;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Use the Symfony security component voter interface to vote using Drupal
 * permissions for granting access
 */
class DrupalPermissionVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (null !== $subject || 'permission' !== $subject) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof DrupalUser) {
            return false;
        }

        return user_access($attribute, $user->getDrupalAccount());
    }
}
