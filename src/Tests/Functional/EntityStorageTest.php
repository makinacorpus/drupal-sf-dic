<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Functionnal;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Node;
use Drupal\node\NodeStorage;
use Drupal\user\User;
use Drupal\user\UserInterface;
use Drupal\user\UserStorage;
use MakinaCorpus\Drupal\Sf\Tests\AbstractDrupalTest;

class EntityStorageTest extends AbstractDrupalTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->getDrupalContainer(); // Force full bootstrap

        if (!getenv('FORCE_TESTS') && module_exists('pathauto')) {
            $this->markTestSkipped("pathauto module, due to strict \stdClass typing, will cause our code to crash");
        }
    }

    public function testEntityManager()
    {
        $container = $this->getDrupalContainer();

        $this->assertTrue($container->has('entity.manager'));

        /** @var \Drupal\Core\Entity\EntityManager $entityManager */
        $entityManager = $container->get('entity.manager');

        foreach (['node', 'user', 'taxonomy_term'] as $entityType) {
            $entityStorage = $entityManager->getStorage($entityType);
            $this->assertInstanceOf(EntityStorageInterface::class, $entityStorage);
        }
    }

    public function testNodeStorage()
    {
        /** @var \Drupal\Core\Entity\EntityManager $entityManager */
        $entityManager = $this->getDrupalContainer()->get('entity.manager');
        $nodeStorage = $entityManager->getStorage('node');
        $this->assertInstanceOf(NodeStorage::class, $nodeStorage);

        $node = $nodeStorage->create();
        $this->assertInstanceOf(Node::class, $node);

        /** @var \Drupal\node\Node $node */
        $node->setOwnerId(12)->setSticky(true)->setTitle("bla bla");
        $node->type = 'test';
        $nodeStorage->save($node);

        $nodeStorage->resetCache();
        /** @var \Drupal\node\Node $compare */
        $compare = $nodeStorage->load($node->id());
        $this->assertInstanceOf(Node::class, $compare);

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
        /** @var \Drupal\Core\Entity\EntityManager $entityManager */
        $entityManager = $this->getDrupalContainer()->get('entity.manager');
        $userStorage = $entityManager->getStorage('user');
        $this->assertInstanceOf(UserStorage::class, $userStorage);

        /** @var \Drupal\user\User $user */
        $user = $userStorage->create();
        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertInstanceOf(AccountInterface::class, $user);

        $prefix = uniqid('robert-');
        $user->setUsername($prefix)->setEmail($prefix . '@smith.com');
        $userStorage->save($user);

        $userStorage->resetCache();
        /** @var \Drupal\user\User $compare */
        $compare = $userStorage->load($user->id());
        $this->assertInstanceOf(User::class, $compare);

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
