<?php

namespace MakinaCorpus\Drupal\Sf\Tests;

use Drupal\Core\Session\Account;
use Drupal\node\Node;

use MakinaCorpus\Drupal\Sf\Security\DrupalUser;
use MakinaCorpus\Drupal\Sf\Security\Voter\DrupalNodeAccessVoter;
use MakinaCorpus\Drupal\Sf\Tests\Mockup\SecurityToken;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SecurityTest extends AbstractDrupalTest
{
    public function setUp()
    {
        parent::setUp();

//         if (!$this->moduleExists('sf_acl')) {
//             $this->markTestSkipped("You must enable the sf_acl module to run this test");
//         }
        if (!interface_exists('\\Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface')) {
            $this->markTestSkipped("You must have downloaded the symfony/security component to run this test");
        }

        // forces bootstrap
        $this->getDrupalContainer();
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
                $account->roles = [DRUPAL_AUTHENTICATED_RID => DRUPAL_AUTHENTICATED_RID];

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
                $canAnything = $voter->vote($token, $node, ['create']);

                $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $canAnything);

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
