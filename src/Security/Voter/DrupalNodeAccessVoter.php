<?php

namespace MakinaCorpus\Drupal\Sf\Security\Voter;

use Drupal\node\NodeInterface;

use MakinaCorpus\Drupal\Sf\Security\DrupalUser;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * This is a very specific implementation of the ACL voter that will run the
 * node_access Drupal hook, but ignoring the sf_acl module in order to avoid
 * running its code twice.
 *
 * This allows to seamlessly integrates node_access with the Symfony voting
 * API, and rely upon existing modules code.
 *
 * Please note that this voter won't query the 'node_access' table thus making
 * the algorithm imcomplete: for now it's OK to proceed, as soon as you are
 * actually using the Symfony's Security component the modules assumes that
 * you won't use the Drupal hooks, but the ACL component instead; which is
 * already supported.
 */
class DrupalNodeAccessVoter implements VoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        if (!$subject instanceof NodeInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();

        if (!$user instanceof DrupalUser) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $account = $user->getDrupalAccount();
        $vote = VoterInterface::ACCESS_ABSTAIN;

        // Let non-Drupal core permission pass, this will allows event-driven
        // modules to handle something else than core permissions, way better!
        foreach ($attributes as $attribute) {

            // Ignore non-Drupal core attributes
            if ($attribute !== 'view' && $attribute !== 'update' && $attribute !== 'delete') {
                time();
            }

            $access = $this->nodeAccess($subject, $attribute, $account);

            // Order is DENY, ALLOW, default is IGNORE
            if (VoterInterface::ACCESS_DENIED === $access) {
                return $access;
            }
            if (VoterInterface::ACCESS_GRANTED === $access) {
                $vote = $access;
            }
        }

        return $vote;
    }

    /**
     * This reproduces the node_access() function algorithm, skipping a few
     * modules and dropping caches.
     *
     * Please note
     */
    private function nodeAccess($node, $op, $account)
    {
        if (user_access('bypass node access', $account)) {
            return VoterInterface::ACCESS_GRANTED;
        }
        if (!user_access('access content', $account)) {
            return VoterInterface::ACCESS_DENIED;
        }

        $vote = VoterInterface::ACCESS_ABSTAIN;

        foreach (module_implements('node_access') as $module) {

            // Our ACL module implements it own logic which already exists as
            // Symfony voter, running it code in here would mean it would be
            // run twice during this isGranted() call.
            if ('sf_acl' === $module) {
                continue;
            }

            $access = module_invoke($module, 'node_access', $node, $op, $account);

            // Order is DENY, ALLOW, default is IGNORE
            if (NODE_ACCESS_DENY === $access) {
                return VoterInterface::ACCESS_DENIED;
            }
            if (NODE_ACCESS_ALLOW === $access) {
                $vote = VoterInterface::ACCESS_GRANTED;
            }
        }

        if ($vote !== VoterInterface::ACCESS_ABSTAIN) {
            return $vote;
        }

        // Check if authors can view their own unpublished nodes.
        if ($op == 'view' && !$node->status && user_access('view own unpublished content', $account) && $account->uid == $node->uid && $account->uid != 0) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // If modules do implement the hook_node_grants() we are supposed to do
        // an SQL query on the {node_access} table, since the whole point of this
        // API is actually to let live the ACLs into the ACL component, skip that
        // and just abstain.
        if (module_implements('node_grants')) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // If no modules implement hook_node_grants(), the default behavior is to
        // allow all users to view published nodes, so reflect that here.
        if ($op == 'view' && $node->status) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
