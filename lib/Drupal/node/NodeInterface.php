<?php

namespace Drupal\node;

use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
interface NodeInterface extends EntityInterface, EntityChangedInterface, EntityOwnerInterface, EntityPublishedInterface
{
    /**
     * Gets the node type.
     *
     * @return string
     *   The node type.
     */
    public function getType();

    /**
     * Gets the node title.
     *
     * @return string
     *   Title of the node.
     */
    public function getTitle();

    /**
     * Sets the node title.
     *
     * @param string $title
     *   The node title.
     *
     * @return \Drupal\node\NodeInterface
     *   The called node entity.
     */
    public function setTitle($title);

    /**
     * Gets the node creation timestamp.
     *
     * @return int
     *   Creation timestamp of the node.
     */
    public function getCreatedTime();

    /**
     * Sets the node creation timestamp.
     *
     * @param int $timestamp
     *   The node creation timestamp.
     *
     * @return \Drupal\node\NodeInterface
     *   The called node entity.
     */
    public function setCreatedTime($timestamp);

    /**
     * Returns the node promotion status.
     *
     * @return bool
     *   TRUE if the node is promoted.
     */
    public function isPromoted();

    /**
     * Sets the node promoted status.
     *
     * @param bool $promoted
     *   TRUE to set this node to promoted, FALSE to set it to not promoted.
     *
     * @return \Drupal\node\NodeInterface
     *   The called node entity.
     */
    public function setPromoted($promoted);

    /**
     * Returns the node sticky status.
     *
     * @return bool
     *   TRUE if the node is sticky.
     */
    public function isSticky();

    /**
     * Sets the node sticky status.
     *
     * @param bool $sticky
     *   TRUE to set this node to sticky, FALSE to set it to not sticky.
     *
     * @return \Drupal\node\NodeInterface
     *   The called node entity.
     */
    public function setSticky($sticky);
}
