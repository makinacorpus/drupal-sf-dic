<?php

namespace MakinaCorpus\Drupal\Sf\Security\Http\Firewall;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * False implementation that will allow Drupal to manage session
 * itself instead of leaving this from Symfony firewalls.
 */
class ContextListener implements ListenerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
    }
}
