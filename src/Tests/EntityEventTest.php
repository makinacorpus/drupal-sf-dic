<?php

namespace MakinaCorpus\Drupal\Sf\Tests;

use Drupal\node\Node;

use MakinaCorpus\Drupal\Sf\Tests\Mockup\EntityEventListener;
use MakinaCorpus\Drupal\Sf\EventDispatcher\EntityCollectionEvent;
use MakinaCorpus\Drupal\Sf\EventDispatcher\EntityEvent;
use MakinaCorpus\Drupal\Sf\EventDispatcher\NodeCollectionEvent;
use MakinaCorpus\Drupal\Sf\EventDispatcher\NodeEvent;

use Symfony\Component\EventDispatcher\EventDispatcher;

class EntityEventTest extends AbstractDrupalTest
{
    public function setUp()
    {
        parent::setUp();

        $this->markTestSkipped("This needs rewrite");

        if (!module_exists('sf_entity_event')) {
            $this->markTestSkipped("You must enable the sf_entity_event module to run this test");
        }

        // forces bootstrap
        $this->getDrupalContainer();
    }

    public function testAllHooks()
    {
        $dispatcher = new EventDispatcher();
        $this->getDrupalContainer()->set('event_dispatcher', $dispatcher);

        $listener = $this->createMock(EntityEventListener::class);

        $dispatcher->addListener(EntityEvent::EVENT_DELETE, [$listener, 'onEntityDelete']);
        $listener->expects($this->atLeast(1))->method('onEntityDelete');
        $dispatcher->addListener(EntityEvent::EVENT_INSERT, [$listener, 'onEntityInsert']);
        $listener->expects($this->atLeast(1))->method('onEntityInsert');
        $dispatcher->addListener(EntityEvent::EVENT_PREINSERT, [$listener, 'onEntityPreinsert']);
        $listener->expects($this->atLeast(1))->method('onEntityPreinsert');
        $dispatcher->addListener(EntityEvent::EVENT_PRESAVE, [$listener, 'onEntityPresave']);
        $listener->expects($this->atLeast(1))->method('onEntityPresave');
        $dispatcher->addListener(EntityEvent::EVENT_PREUPDATE, [$listener, 'onEntityPreupdate']);
        $listener->expects($this->atLeast(1))->method('onEntityPreupdate');
        $dispatcher->addListener(EntityEvent::EVENT_SAVE, [$listener, 'onEntitySave']);
        $listener->expects($this->atLeast(1))->method('onEntitySave');
        $dispatcher->addListener(EntityEvent::EVENT_UPDATE, [$listener, 'onEntityUpdate']);
        $listener->expects($this->atLeast(1))->method('onEntityUpdate');
        $dispatcher->addListener(EntityEvent::EVENT_VIEW, [$listener, 'onEntityView']);
        $listener->expects($this->atLeast(1))->method('onEntityView');

        $dispatcher->addListener(EntityCollectionEvent::EVENT_LOAD, [$listener, 'onEntityLoad']);
        $listener->expects($this->atLeast(1))->method('onEntityLoad');
        $dispatcher->addListener(EntityCollectionEvent::EVENT_PREPAREVIEW, [$listener, 'onEntityPrepareView']);
        $listener->expects($this->atLeast(1))->method('onEntityPrepareview');

        $dispatcher->addListener(NodeEvent::EVENT_DELETE, [$listener, 'onNodeDelete']);
        $listener->expects($this->atLeast(1))->method('onNodeDelete');
        $dispatcher->addListener(NodeEvent::EVENT_INSERT, [$listener, 'onNodeInsert']);
        $listener->expects($this->atLeast(1))->method('onNodeInsert');
        $dispatcher->addListener(NodeEvent::EVENT_PREINSERT, [$listener, 'onNodePreinsert']);
        $listener->expects($this->atLeast(1))->method('onNodePreinsert');
        $dispatcher->addListener(NodeEvent::EVENT_PRESAVE, [$listener, 'onNodePresave']);
        $listener->expects($this->atLeast(1))->method('onNodePresave');
        $dispatcher->addListener(NodeEvent::EVENT_PREUPDATE, [$listener, 'onNodePreupdate']);
        $listener->expects($this->atLeast(1))->method('onNodePreupdate');
        $dispatcher->addListener(NodeEvent::EVENT_SAVE, [$listener, 'onNodeSave']);
        $listener->expects($this->atLeast(1))->method('onNodeSave');
        $dispatcher->addListener(NodeEvent::EVENT_UPDATE, [$listener, 'onNodeUpdate']);
        $listener->expects($this->atLeast(1))->method('onNodeUpdate');
        $dispatcher->addListener(NodeEvent::EVENT_VIEW, [$listener, 'onNodeView']);
        $listener->expects($this->atLeast(1))->method('onNodeView');

        $dispatcher->addListener(NodeCollectionEvent::EVENT_LOAD, [$listener, 'onNodeLoad']);
        $listener->expects($this->atLeast(1))->method('onNodeLoad');

        // New entity
        $entity = new Node();
        $entity->type = 'page';
        // Update/insert careless hooks
        module_invoke_all('entity_insert', $entity, 'node');
        module_invoke_all('entity_view', $entity, 'node');
        module_invoke_all('entity_prepare_view', [$entity], 'node');
        module_invoke_all('entity_presave', $entity, 'node');
        // Hooks that need an identifier
        $entity->nid = 13;
        $entity->original = clone $entity;
        module_invoke_all('entity_load', [$entity], 'node');
        module_invoke_all('entity_delete', $entity, 'node');
        module_invoke_all('entity_update', $entity, 'node');
        module_invoke_all('entity_presave', $entity, 'node');
        // Re-run the presave hook
        module_invoke_all('entity_presave', $entity, 'node');

        // New entity
        $node = new Node();
        $node->type = 'page';
        // Update/insert careless hooks
        module_invoke_all('node_insert', $node);
        module_invoke_all('node_view', $node);
        module_invoke_all('node_presave', $node);
        // Hooks that need an identifier
        $node->nid = 12;
        $node->original = clone $node;
        module_invoke_all('node_load', [$node]);
        module_invoke_all('node_delete', $node);
        module_invoke_all('node_update', $node);
        module_invoke_all('node_presave', $node);
        // Re-run the presave hook
        module_invoke_all('node_presave', $node);
    }
}
