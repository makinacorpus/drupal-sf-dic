<?php

namespace MakinaCorpus\Drupal\Sf\Tests;

class ContollerTest extends AbstractDrupalTest
{
    const CONTROLLER_CLASS = '\MakinaCorpus\Drupal\Sf\Tests\Mockup\Controller';

    protected function setUp()
    {
        parent::setUp();

        $this->getDrupalContainer(); // full bootstrap, load this module.
    }

    public function testControllerRenderMethod()
    {
        $output = sf_dic_page(self::CONTROLLER_CLASS, 1, 2, 3, 4, 5);
        $this->assertSame('normal rendering', $output);
    }

    public function testWithFullMethodName()
    {
        $output = sf_dic_page(self::CONTROLLER_CLASS . '::otherMethod', 1, 2, 3, 4, 5);
        $this->assertSame('another method', $output);
    }

    public function testNormalAction()
    {
        $output = sf_dic_page(self::CONTROLLER_CLASS . '::some', 1, 2, 3, 4, 5);
        $this->assertSame('some action', $output);
    }

    public function testVariousRequestActions()
    {
        $output = sf_dic_page(self::CONTROLLER_CLASS . '::anActionWithRequest');
        $this->assertSame('it works', $output);

        $output = sf_dic_page(self::CONTROLLER_CLASS . '::anotherActionWithRequest', 1, 2, 3, 4, 5);
        $this->assertSame('it works too, 1, 2', $output);

        $output = sf_dic_page(self::CONTROLLER_CLASS . '::anActionWithARequestAnywhere', 7, 8, 9);
        $this->assertSame('it is still working, 7, 8, 9', $output);

        $output = sf_dic_page(self::CONTROLLER_CLASS . '::anActionWith2Requests', 10, 11, 12);
        $this->assertSame('it works again, 10, 11, 12', $output);
    }

    public function testControllerHasContainer()
    {
        sf_dic_page(self::CONTROLLER_CLASS . '::doIHaveAContainer');
    }
}
