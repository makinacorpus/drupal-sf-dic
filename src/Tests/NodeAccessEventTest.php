<?php

namespace MakinaCorpus\Drupal\Sf\Tests;

use Drupal\node\Node;
use Drupal\user\User;

use MakinaCorpus\Drupal\Sf\EventDispatcher\NodeAccessEvent;

use Symfony\Component\EventDispatcher\EventDispatcher;
use MakinaCorpus\Drupal\Sf\EventDispatcher\NodeAccessGrantEvent;
use MakinaCorpus\Drupal\Sf\EventDispatcher\NodeAccessRecordEvent;
use MakinaCorpus\Drupal\Sf\EventDispatcher\NodeAccessSubscriber;

class NodeAccessEventTest extends \PHPUnit_Framework_TestCase
{
    private $dispatcher;

    public function setUp()
    {
        $this->dispatcher = new EventDispatcher();

        // Ok, skip it now, but we have to actually bootstrap Drupal...
        $this->markTestSkipped("Sorry; but I do need to fix this bootstrap");

        if (!defined('DRUPAL_ANONYMOUS_RID')) {
            define('DRUPAL_ANONYMOUS_RID', 0);
        }
        if (!defined('DRUPAL_AUTHENTICATED_RID')) {
            define('DRUPAL_AUTHENTICATED_RID', 0);
        }
        if (!defined('LANGUAGE_NONE')) {
            define('LANGUAGE_NONE', 'und');
        }

        if (!defined('NODE_ACCESS_IGNORE')) {
            define('NODE_ACCESS_IGNORE', null);
        }
        if (!defined('NODE_ACCESS_ALLOW')) {
            define('NODE_ACCESS_ALLOW', 'allow');
        }
        if (!defined('NODE_ACCESS_DENY')) {
            define('NODE_ACCESS_DENY', 'deny');
        }

        require_once __DIR__ . '/../../modules/sf_entity_event/sf_entity_event.module';
    }

    public function testNodeAccessDenyDoStopPropagation()
    {
        $count = 0;

        // First to run
        $this->dispatcher->addListener(NodeAccessEvent::EVENT_NODE_ACCESS, function (NodeAccessEvent $e) use (&$count) {
            ++$count;
            $e->ignore();
        }, 0);

        // This one should run
        $this->dispatcher->addListener(NodeAccessEvent::EVENT_NODE_ACCESS, function (NodeAccessEvent $e) use (&$count) {
            ++$count;
            $e->allow();
        }, -8);

        // This one should too
        $this->dispatcher->addListener(NodeAccessEvent::EVENT_NODE_ACCESS, function (NodeAccessEvent $e) use (&$count) {
            ++$count;
            $e->deny();
        }, -16);

        // This one should not
        $this->dispatcher->addListener(NodeAccessEvent::EVENT_NODE_ACCESS, function (NodeAccessEvent $e) {
            throw new \Exception("You shall not pass");
        }, -32);

        $account = new User();
        $node = new Node();
        $this->dispatcher->dispatch(NodeAccessEvent::EVENT_NODE_ACCESS, new NodeAccessEvent($node, $account, 'view'));

        $this->assertSame(3, $count);
    }

    public function testNodeAccessCreateDoNoRunGrant()
    {
        $this->dispatcher->addSubscriber(new NodeAccessSubscriber($this->dispatcher));

        $this->dispatcher->addListener(NodeAccessGrantEvent::EVENT_NODE_ACCESS_GRANT, function (NodeAccessGrantEvent $e) {
            throw new \Exception("You shall not pass");
        });

        $this->dispatcher->addListener(NodeAccessRecordEvent::EVENT_NODE_ACCESS_RECORD, function (NodeAccessRecordEvent $e) {
            throw new \Exception("You shall not pass");
        });

        $account = new User();
        $node = new Node();
        $this->dispatcher->dispatch(NodeAccessEvent::EVENT_NODE_ACCESS, new NodeAccessEvent($node, $account, 'create'));

        try {
            $this->dispatcher->dispatch(NodeAccessEvent::EVENT_NODE_ACCESS, new NodeAccessEvent($node, $account, 'view'));

            // It should have thrown exceptions here
            $this->fail();

        } catch (\Exception $e) {}
    }

    public function testNodeAccessMatrix()
    {
        // @todo
    }
}
