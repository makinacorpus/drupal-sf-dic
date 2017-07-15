<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Unit\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

class CallableController
{
    use ContainerAwareTrait;

    public function __invoke(Request $request)
    {
        return "It works";
    }
}
