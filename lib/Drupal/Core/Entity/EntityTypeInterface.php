<?php

namespace Drupal\Core\Entity;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
interface EntityTypeInterface
{
    /**
     * The maximum length of ID, in characters.
     */
    const ID_MAX_LENGTH = 32;

    /**
     * The maximum length of bundle name, in characters.
     */
    const BUNDLE_MAX_LENGTH = 32;

    /**
     * Gets an array of entity keys.
     *
     * @return array
     *   An array describing how the Field API can extract certain information
     *   from objects of this entity type:
     *   - id: The name of the property that contains the primary ID of the
     *     entity. Every entity object passed to the Field API must have this
     *     property and its value must be numeric.
     *   - revision: (optional) The name of the property that contains the
     *     revision ID of the entity. The Field API assumes that all revision IDs
     *     are unique across all entities of a type. If this entry is omitted
     *     the entities of this type are not revisionable.
     *   - bundle: (optional) The name of the property that contains the bundle
     *     name for the entity. The bundle name defines which set of fields are
     *     attached to the entity (e.g. what nodes call "content type"). This
     *     entry can be omitted if this entity type exposes a single bundle (such
     *     that all entities have the same collection of fields). The name of this
     *     single bundle will be the same as the entity type.
     *   - label: (optional) The name of the property that contains the entity
     *     label. For example, if the entity's label is located in
     *     $entity->subject, then 'subject' should be specified here. If complex
     *     logic is required to build the label,
     *     \Drupal\Core\Entity\EntityInterface::label() should be used.
     *   - langcode: (optional) The name of the property that contains the
     *     language code. For instance, if the entity's language is located in
     *     $entity->langcode, then 'langcode' should be specified here.
     *   - uuid: (optional) The name of the property that contains the universally
     *     unique identifier of the entity, which is used to distinctly identify
     *     an entity across different systems.
     */
    public function getKeys();

    /**
     * Gets a specific entity key.
     *
     * @param string $key
     *   The name of the entity key to return.
     *
     * @return string|bool
     *   The entity key, or FALSE if it does not exist.
     *
     * @see self::getKeys()
     */
    public function getKey($key);

    /**
     * Indicates if a given entity key exists.
     *
     * @param string $key
     *   The name of the entity key to check.
     *
     * @return bool
     *   TRUE if a given entity key exists, FALSE otherwise.
     */
    public function hasKey($key);

    /**
     * Gets the label for the bundle.
     *
     * @return string
     *   The bundle label.
     */
    public function getBundleLabel();

    /**
     * Gets the name of the entity's base table.
     *
     * @todo Used by SqlContentEntityStorage only.
     *
     * @return string|null
     *   The name of the entity's base table, or NULL if none exists.
     */
    public function getBaseTable();

    /**
     * Indicates whether entities of this type have multilingual support.
     *
     * At an entity level, this indicates language support and at a bundle level
     * this indicates translation support.
     *
     * @return bool
     */
    public function isTranslatable();

    /**
     * Indicates whether entities of this type have revision support.
     *
     * @return bool
     */
    public function isRevisionable();

    /**
     * Gets the name of the entity's revision data table.
     *
     * @todo Used by SqlContentEntityStorage only.
     *
     * @return string|null
     *   The name of the entity type's revision data table, or NULL if none
     *   exists.
     */
    public function getRevisionDataTable();

    /**
     * Gets the name of the entity's revision table.
     *
     * @todo Used by SqlContentEntityStorage only.
     *
     * @return string|null
     *   The name of the entity type's revision table, or NULL if none exists.
     */
    public function getRevisionTable();

    /**
     * Gets the name of the entity's data table.
     *
     * @todo Used by SqlContentEntityStorage only.
     *
     * @return string|null
     *   The name of the entity type's data table, or NULL if none exists.
     */
    public function getDataTable();

    /**
     * Gets the human-readable name of the entity type.
     *
     * This label should be used to present a human-readable name of the
     * entity type.
     *
     * @return string
     *   The human-readable name of the entity type.
     */
    public function getLabel();

    /**
     * Gets the lowercase form of the human-readable entity type name.
     *
     * @return string
     *   The lowercase form of the human-readable entity type name.
     *
     * @see \Drupal\Core\Entity\EntityTypeInterface::getLabel()
     */
    public function getLowercaseLabel();

    /**
     * Gets the uppercase plural form of the name of the entity type.
     *
     * This should return a human-readable version of the name that can refer
     * to all the entities of the given type, collectively. An example usage of
     * this is the page title of a page devoted to a collection of entities such
     * as "Workflows" (instead of "Workflow entities").
     *
     * @return string
     *   The collection label.
     */
    public function getCollectionLabel();

    /**
     * Gets the indefinite singular form of the name of the entity type.
     *
     * This should return the human-readable name for a single instance of
     * the entity type. For example: "opportunity" (with the plural as
     * "opportunities"), "child" (with the plural as "children"), or "content
     * item" (with the plural as "content items").
     *
     * @return string
     *   The singular label.
     */
    public function getSingularLabel();

    /**
     * Gets the indefinite plural form of the name of the entity type.
     *
     * This should return the human-readable name for more than one instance of
     * the entity type. For example: "opportunities" (with the singular as
     * "opportunity"), "children" (with the singular as "child"), or "content
     * items" (with the singular as "content item").
     *
     * @return string
     *   The plural label.
     */
    public function getPluralLabel();

    /**
     * Gets the label's definite article form for use with a count of entities.
     *
     * This label should be used when the quantity of entities is provided. The
     * name should be returned in a form usable with a count of the
     * entities. For example: "1 opportunity", "5 opportunities", "1 child",
     * "6 children", "1 content item", "25 content items".
     *
     * @param int $count
     *   The item count to display if the plural form was requested.
     *
     * @return string
     *   The count label.
     */
    public function getCountLabel($count);
}
