<?php

/**
 * @file
 * Contains \Drupal\Core\DrupalKernelInterface.
 */

namespace Drupal\Core;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * The interface for DrupalKernel, the core of Drupal.
 *
 * This interface extends Symfony's KernelInterface and adds methods for
 * responding to modules being enabled or disabled during its lifetime.
 */
interface DrupalKernelInterface extends /* HttpKernelInterface, */ ContainerAwareInterface
{
    /**
     * Boots the current kernel.
     *
     * @return $this
     */
    public function boot();

    /**
     * Shuts down the kernel.
     */
    public function shutdown();

    /**
     * Discovers available serviceProviders.
     *
     * @return array
     *   The available serviceProviders.
     */
    public function discoverServiceProviders();

    /**
     * Gets the current container.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     *   A ContainerInterface instance.
     */
    public function getContainer();

    /**
     * Returns the cached container definition - if any.
     *
     * This also allows inspecting a built container for debugging purposes.
     *
     * @return array|NULL
     *   The cached container definition or NULL if not found in cache.
     */
    //public function getCachedContainerDefinition();

    /**
     * Gets the app root.
     *
     * @return string
     */
    public function getAppRoot();

    /**
     * Force a container rebuild.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function rebuildContainer();

    /**
     * Invalidate the service container for the next request.
     */
    public function invalidateContainer();

    /**
     * Helper method that does request related initialization.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The current request.
     */
    public function preHandle(Request $request);
}
