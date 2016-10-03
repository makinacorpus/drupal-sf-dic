<?php

namespace MakinaCorpus\Drupal\Sf\Tests;

use MakinaCorpus\Drupal\Sf\Kernel;
use MakinaCorpus\Drupal\Sf\Tests\Mockup\FooBundle\MockupFooBundle;

/**
 * @todo this test needs rewrite
 */
class BundleTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if ($rootDir = getenv('DRUPAL_PATH')) {
            define('DRUPAL_ROOT', $rootDir);
        } else {
            $this->markTestSkipped("Please configure the 'DRUPAL_PATH' environment variable");
        }

        $this->markTestSkipped("This needs rewrite");
    }

    public function testArbitraryLoading()
    {
        $bundle = new MockupFooBundle();

        $kernel = new Kernel(uniqid('test_'), true, false);
        $kernel->addExtraBundles([$bundle]);
        $kernel->boot();

        // And the build method was correctly invoked
        $this->assertTrue($kernel->getContainer()->hasParameter('fake_bundle_foo_in_build'));
        $this->assertSame(666, $kernel->getContainer()->getParameter('fake_bundle_foo_in_build'));

        // Test container has the bundle services
        $this->assertTrue($kernel->getContainer()->hasParameter('fake_bundle_foo'));
        $this->assertSame(42, $kernel->getContainer()->getParameter('fake_bundle_foo'));
        $this->assertTrue($kernel->getContainer()->has('fake_bundle_service'));
        $this->assertInstanceOf('stdClass', $kernel->getContainer()->get('fake_bundle_service'));
    }
}
