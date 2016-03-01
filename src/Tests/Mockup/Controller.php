<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Mockup;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

class Controller
{
    use ContainerAwareTrait;

    public function render()
    {
        return 'normal rendering';
    }

    public function doIHaveAContainer()
    {
        if (!$this->container) {
            throw new \LogicException("I should have a container!");
        }
    }

    public function IWillThrowSomething()
    {
        throw new \Exception("ouyé");
    }

    public function otherMethod()
    {
        return 'another method';
    }

    public function someAction()
    {
        return 'some action';
    }

    public function anActionWithRequest(Request $request)
    {
        if ($request) {
            return 'it works';
        }
    }

    public function anotherActionWithRequest($a, $b, Request $request)
    {
        return "it works too, $a, $b";
    }

    public function anActionWithARequestAnywhere($a, $b, Request $request, $c)
    {
        return "it is still working, $a, $b, $c";
    }

    public function anActionWith2Requests($a, Request $request, $b, $c, Request $request)
    {
        return "it works again, $a, $b, $c";
    }
}
