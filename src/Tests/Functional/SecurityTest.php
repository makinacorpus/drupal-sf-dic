<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Functional;

use Drupal\Core\Session\Account;
use Drupal\node\Node;
use Drupal\user\User;
use MakinaCorpus\Drupal\Sf\Security\DrupalUser;
use MakinaCorpus\Drupal\Sf\Security\Authorization\TokenAwareAuthorizationChecker;
use MakinaCorpus\Drupal\Sf\Security\Token\UserToken;
use MakinaCorpus\Drupal\Sf\Security\Voter\DrupalNodeAccessVoter;
use MakinaCorpus\Drupal\Sf\Security\Voter\DrupalPermissionVoter;
use MakinaCorpus\Drupal\Sf\Tests\AbstractDrupalTest;
use MakinaCorpus\Drupal\Sf\Tests\Mockup\SecurityNullAuthenticationManager;
use MakinaCorpus\Drupal\Sf\Tests\Mockup\SecurityToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SecurityTest extends AbstractDrupalTest
{
    public function setUp()
    {
        parent::setUp();

        if (!$this->moduleExists('sf_acl')) {
            $this->markTestSkipped("You must enable the sf_acl module to run this test");
        }
        if (!interface_exists('\\Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface')) {
            $this->markTestSkipped("You must have downloaded the symfony/security component to run this test");
        }

        // forces bootstrap
        $this->getDrupalContainer();
    }

    public function testTokenAwareAuthorizationCheckerAndDrupalPermissionVoter()
    {
        // We are in Drupal, user with uid 1 can access everything
        $superUser = new User();
        $superUser->uid = 1;
        $superUser->roles = [1 => 1];
        $superToken = new UserToken();
        $superToken->setUser(new DrupalUser($superUser));

        // And anonymous pretty much nothing
        $dumbUser = new User();
        $dumbUser->uid = 0;
        $dumbUser->roles = [0 => 0];
        $dumbToken = new UserToken();
        $dumbToken->setUser(new DrupalUser($dumbUser));

        // We are working in a fully bootstrapped Drupal, in theory
        // the permission voter is setup, we can send isGranted() calls
        // using permission names: sending a non existing permission
        // will always return false for any user, but always true for
        // the user with uid 1 (Drupal core default behavior)
        $permission = 'a drupal permission that does not exists';

        $tokenStorage = new TokenStorage();
        $authenticationManager = new SecurityNullAuthenticationManager();
        $accessDecisionManager = new AccessDecisionManager([new DrupalPermissionVoter()]);

        $defaultAuthorizationChecker = new AuthorizationChecker($tokenStorage, $authenticationManager, $accessDecisionManager);
        $tokenAwareAuthorizationChecker = new TokenAwareAuthorizationChecker($defaultAuthorizationChecker, $accessDecisionManager);

        // First check results for the current user (should not be allowed)
        // Then the super user (should be allowed)
        $tokenStorage->setToken($superToken);
        $this->assertTrue($defaultAuthorizationChecker->isGranted($permission, null));
        $this->assertTrue($tokenAwareAuthorizationChecker->isGranted($permission, null));
        $this->assertTrue($tokenAwareAuthorizationChecker->isGranted($permission, null, $superUser));
        $this->assertFalse($tokenAwareAuthorizationChecker->isGranted($permission, null, $dumbUser));

        // And do the exact opposite
        $tokenStorage->setToken($dumbToken);
        $this->assertFalse($defaultAuthorizationChecker->isGranted($permission, null));
        $this->assertFalse($tokenAwareAuthorizationChecker->isGranted($permission, null));
        $this->assertTrue($tokenAwareAuthorizationChecker->isGranted($permission, null, $superUser));
        $this->assertFalse($tokenAwareAuthorizationChecker->isGranted($permission, null, $dumbUser));
    }

    public function testDrupalNodeAccessVoter()
    {
        $voter = new DrupalNodeAccessVoter();

        // Pollute the module_implements cache to only let the 'node' module
        // to be in there.
        module_implements('node_access');
        $cache = &drupal_static('module_implements');
        $cache['node_access'] = ['node' => null, 'sf_acl' => null];

        // Create an awesome volume of random data to test, inject pretty
        // much everything, and just ensure that node_access() and the
        // voter will return the same, using the rule we determined. We will
        // add special tests for later failures or bug found, if we find any.
        foreach (range(0, 50) as $nodeId) {

            $node = new Node();
            $node->type = 'page';
            node_object_prepare($node);
            $node->nid = $nodeId;
            $node->vid = $nodeId;

            foreach (range(0, 50) as $userId) {

                $account = new Account();
                $account->uid = $userId;
                $account->roles = [1 => 1];

                // This gives more or less 5% chances  that the current user
                // is the current node owner.
                $owner = rand($userId - 10, $userId + 10);

                // Give an arbitrary status
                $node->status = rand(0, 1);
                $node->uid = $owner;

                $drupalAccessView = node_access('view', $node, $account);
                $drupalAccessUpdate = node_access('update', $node, $account);
                $drupalAccessDelete = node_access('delete', $node, $account);

                $token = new SecurityToken();
                $token->setUser(new DrupalUser($account));

                $canView = $voter->vote($token, $node, ['view']);
                $canUpdate = $voter->vote($token, $node, ['update']);
                $canDelete = $voter->vote($token, $node, ['delete']);
                $canAnything = $voter->vote($token, $node, ['non existing permission']);

                // Sad Drupal is sad, but UID 1 will always have all rights
                // node_access() legacy function will filter out anything that's
                // not 'view', 'update' or 'delete', but we won't: so UID 1 has
                // always all rights on the non existing permission.
                if (1 !== (int)$userId) {
                    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $canAnything);
                } else {
                    $this->assertSame(VoterInterface::ACCESS_GRANTED, $canAnything);
                }

                if ($drupalAccessView) {
                    $this->assertSame(VoterInterface::ACCESS_GRANTED, $canView);
                } else {
                    $this->assertContains($canView, [VoterInterface::ACCESS_DENIED, VoterInterface::ACCESS_ABSTAIN]);
                }

                if ($drupalAccessUpdate) {
                    $this->assertSame(VoterInterface::ACCESS_GRANTED, $canUpdate);
                } else {
                    $this->assertContains($canUpdate, [VoterInterface::ACCESS_DENIED, VoterInterface::ACCESS_ABSTAIN]);
                }

                if ($drupalAccessDelete) {
                    $this->assertSame(VoterInterface::ACCESS_GRANTED, $canDelete);
                } else {
                    $this->assertContains($canDelete, [VoterInterface::ACCESS_DENIED, VoterInterface::ACCESS_ABSTAIN]);
                }
            }
        }
    }
}
