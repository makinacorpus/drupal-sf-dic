<?php

namespace MakinaCorpus\Drupal\Sf\Security\Voter;

use MakinaCorpus\ACL\Manager;
use MakinaCorpus\Drupal\Sf\Security\DrupalUser;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Rewrite the ACL voter in order to always give Drupal account instances
 * to the real acl voter, allowing Drupal modules to transparently always
 * work on real drupal user account
 */
class DrupalAclVoter implements VoterInterface
{
    private $manager;

    /**
     * Default constructor
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $vote = VoterInterface::ACCESS_ABSTAIN;

        $user = $token->getUser();
        if ($user instanceof DrupalUser) {
            $account = $user->getDrupalAccount();
        } else {
            $account = $token;
        }

        foreach ($attributes as $attribute) {

            if (!is_string($attribute)) {
                continue;
            }

            $local = $this->manager->vote($account, $subject, $attribute);

            if (Manager::ALLOW === $local) {
                return VoterInterface::ACCESS_GRANTED;
            }
            if (Manager::DENY === $local) {
                $vote = VoterInterface::ACCESS_DENIED;
            }
        }

        return $vote;
    }
}
