<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Mockup\FooBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MockupFooBundle extends Bundle
{
    private $called = false;

    public function haveIBeenCalled()
    {
        return $this->called;
    }

    public function build(ContainerBuilder $container)
    {
        $this->called = true;

        $container
            ->getParameterBag()
            ->add([
                'fake_bundle_foo_in_build' => 666
            ])
        ;
    }
}
