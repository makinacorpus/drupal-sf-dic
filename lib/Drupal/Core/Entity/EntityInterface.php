<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityInterface.
 */

namespace Drupal\Core\Entity;

use Drupal\Core\Access\AccessibleInterface;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
interface EntityInterface extends AccessibleInterface
{
    /**
     * Gets the entity UUID (Universally Unique Identifier).
     *
     * The UUID is guaranteed to be unique and can be used to identify an entity
     * across multiple systems.
     *
     * @return string|null
     *   The UUID of the entity, or NULL if the entity does not have one.
     */
    public function uuid();

    /**
     * Gets the identifier.
     *
     * @return string|int|null
     *   The entity identifier, or NULL if the object does not yet have an
     *   identifier.
     */
    public function id();

    /**
     * Gets the language of the entity.
     *
     * @return \Drupal\Core\Language\LanguageInterface
     *   The language object.
     */
    public function language();

    /**
     * Determines whether the entity is new.
     *
     * Usually an entity is new if no ID exists for it yet. However, entities may
     * be enforced to be new with existing IDs too.
     *
     * @return bool
     *   TRUE if the entity is new, or FALSE if the entity has already been saved.
     *
     * @see \Drupal\Core\Entity\EntityInterface::enforceIsNew()
     */
    public function isNew();

    /**
     * Gets the ID of the type of the entity.
     *
     * @return string
     *   The entity type ID.
     */
    public function getEntityTypeId();

    /**
     * Gets the bundle of the entity.
     *
     * @return string
     *   The bundle of the entity. Defaults to the entity type ID if the entity
     *   type does not make use of different bundles.
     */
    public function bundle();

    /**
     * Gets the label of the entity.
     *
     * @return string|null
     *   The label of the entity, or NULL if there is no label defined.
     */
    public function label();

    /**
     * Gets the public URL for this entity.
     *
     * @param string $rel
     *   The link relationship type, for example: canonical or edit-form.
     * @param array $options
     *   See \Drupal\Core\Routing\UrlGeneratorInterface::generateFromRoute() for
     *   the available options.
     *
     * @return string
     *   The URL for this entity.
     *
     * @deprecated in Drupal 8.0.0, intended to be removed in Drupal 9.0.0
     *   Please use toUrl() instead.
     *
     * @see \Drupal\Core\Entity\EntityInterface::toUrl
     */
    public function url($rel = 'canonical', $options = array());

    /**
     * Creates a duplicate of the entity.
     *
     * @return static
     *   A clone of $this with all identifiers unset, so saving it inserts a new
     *   entity into the storage system.
     */
    public function createDuplicate();
}
