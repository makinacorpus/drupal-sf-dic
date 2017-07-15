<?php

namespace MakinaCorpus\Drupal\Sf\Http;

use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpFoundation\Request as BaseRequest;

/**
 * When not working with Symfony full stack, we still need this to be able to
 * spawn the 'http_kernel' service, even if unused.
 *
 * @codeCoverageIgnore
 */
class NullControllerResolver implements ControllerResolverInterface
{
    public function getController(BaseRequest $request)
    {
        return false;
    }

    public function getArguments(BaseRequest $request, $controller)
    {
        return [];
    }
}
