<?php

namespace Drupal\Core\Entity;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
interface EntityChangedInterface
{
    /**
     * Gets the timestamp of the last entity change for the current translation.
     *
     * @return int
     *   The timestamp of the last entity save operation.
     */
    public function getChangedTime();

    /**
     * Sets the timestamp of the last entity change for the current translation.
     *
     * @param int $timestamp
     *   The timestamp of the last entity save operation.
     *
     * @return $this
     */
    public function setChangedTime($timestamp);
}
