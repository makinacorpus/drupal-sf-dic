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

    /**
     * This method is not in the interface, but \Symfony\Component\Security\Http\Firewall\ContextListener
     * implements it, and the kernel will attempt to run it anyway.
     *
     * @param bool $logoutOnUserChange
     */
    public function setLogoutOnUserChange($logoutOnUserChange)
    {
    }
}
