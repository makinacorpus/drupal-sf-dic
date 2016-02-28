<?php

namespace Drupal\Core\Path;

use Symfony\Component\HttpFoundation\Request;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
class CurrentPathStack
{
    /**
     * Returns the path of the current request
     *
     * @param Request $request
     *
     * @return string
     *   Returns the path, without leading slashes
     */
    public function getPath(Request $request = null)
    {
        return current_path();
    }
}
