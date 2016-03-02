<?php

namespace MakinaCorpus\Drupal\Sf\Tests;

use MakinaCorpus\Drupal\Sf\Tests\Mockup\FooBundle\MockupFooBundle;

use Symfony\Bundle\TwigBundle\TwigBundle;

class TwigBundleTest extends AbstractDrupalTest
{
    protected function setUp()
    {
        parent::setUp();

        if (!$this->getDrupalContainer()->has('twig')) {
            $this->markTestSkipped("Incomplete environment");
        }
    }

    protected function addExtraBundles()
    {
        return [
            new MockupFooBundle(),
        ];
    }

    public function testModuleAsBundle()
    {
        // @todo
    }

    public function testArbitraryLoading()
    {
        $output = $this->getDrupalContainer()->get('twig')->render('MockupFooBundle:Controller:down.html.twig', []);
        $this->assertSame('<p>This is controller level</p>', $output);

        $output = $this->getDrupalContainer()->get('twig')->render('MockupFooBundle::topLevel.html.twig', []);
        $this->assertSame('<p>This is top level</p>', $output);
    }
}
