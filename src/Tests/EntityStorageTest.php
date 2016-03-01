<?php

namespace MakinaCorpus\Drupal\Sf\Container\Tests;

use Drupal\Core\Entity\EntityManager;

class EntityStorageTest extends AbstractDrupalTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->getDrupalContainer(); // Force full bootstrap

        if (module_exists('pathauto')) {
            $this->markTestSkipped("Some modules are stupid, we cannot test under those conditions");
        }
    }

    public function testEntityManager()
    {
        $container = $this->getDrupalContainer();

        $this->assertTrue($container->has('entity.manager'));

        /* @var $entityManager EntityManager */
        $entityManager = $container->get('entity.manager');

        foreach (['node', 'user', 'taxonomy_term'] as $entityType) {
            $entityStorage = $entityManager->getStorage($entityType);
            $this->assertInstanceOf('\Drupal\Core\Entity\EntityStorageInterface', $entityStorage);
        }
    }

    public function testNodeStorage()
    {
        /* @var $entityManager EntityManager */
        $entityManager = $this->getDrupalContainer()->get('entity.manager');
        $nodeStorage = $entityManager->getStorage('node');
        $this->assertInstanceOf('\Drupal\node\NodeStorage', $nodeStorage);

        $node = $nodeStorage->create();
        $this->assertInstanceOf('\Drupal\node\Node', $node);

        /* @var $node \Drupal\node\Node */
        $node->setOwnerId(12)->setSticky(true)->setTitle("bla bla");
        $node->type = 'test';
        $nodeStorage->save($node);

        $nodeStorage->resetCache();
        /* @var $compare \Drupal\node\Node */
        $compare = $nodeStorage->load($node->id());
        $this->assertInstanceOf('\Drupal\node\Node', $compare);

        $this->assertEquals($node->id(), $compare->id());
        $this->assertSame($node->getTitle(), $compare->getTitle());
        $this->assertSame("bla bla", $compare->getTitle());
        $this->assertEquals(12, $compare->getOwnerId());
        $this->assertSame('test', $compare->bundle());
        $this->assertSame('test', $compare->getType());
        $this->assertSame('node', $compare->getEntityTypeId());
    }

    public function testUserStorage()
    {
        /* @var $entityManager EntityManager */
        $entityManager = $this->getDrupalContainer()->get('entity.manager');
        $userStorage = $entityManager->getStorage('user');
        $this->assertInstanceOf('\Drupal\user\UserStorage', $userStorage);

        $user = $userStorage->create();
        $this->assertInstanceOf('\Drupal\user\User', $user);
        $this->assertInstanceOf('\Drupal\user\UserInterface', $user);
        $this->assertInstanceOf('\Drupal\Core\Session\AccountInterface', $user);

        /* @var $user \Drupal\user\User */
        $prefix = uniqid('robert-');
        $user->setUsername($prefix)->setEmail($prefix . '@smith.com');
        $userStorage->save($user);

        $userStorage->resetCache();
        /* @var $compare \Drupal\user\User */
        $compare = $userStorage->load($user->id());
        $this->assertInstanceOf('\Drupal\user\User', $compare);

        $this->assertSame('user', $user->getEntityTypeId());
        $this->assertEquals($user->id(), $compare->id());
        $this->assertSame($prefix . '@smith.com', $user->getEmail());
        $this->assertSame($user->getEmail(), $compare->getEmail());
        $this->assertSame($prefix, $user->getAccountName());
        $this->assertSame($prefix, $user->getDisplayName());
        $this->assertSame($user->getEmail(), $compare->getEmail());

        // @todo test role modification and get
    }
}
