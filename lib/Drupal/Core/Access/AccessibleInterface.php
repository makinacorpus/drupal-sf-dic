<?php

/**
 * @file
 * Contains \Drupal\Core\Access\AccessibleInterface.
 */

namespace Drupal\Core\Access;

use Drupal\Core\Session\AccountInterface;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
interface AccessibleInterface
{
    /**
     * Checks data value access.
     *
     * @param string $operation
     *   The operation to be performed.
     * @param AccountInterface $account
     *   (optional) The user for which to check access, or NULL to check access
     *   for the current user. Defaults to NULL.
     * @param bool $return_as_object
     *   (optional) Defaults to FALSE.
     *
     * @return bool
     *
     * WARNING: This implementation does not support $return_as_object
     *   it will always return a boolean, I'm just keeping it interface
     *   compatible.
     */
    public function access($operation, AccountInterface $account = null, $return_as_object = false);
}
