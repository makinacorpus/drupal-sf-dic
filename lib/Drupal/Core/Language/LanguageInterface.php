<?php

/**
 * @file
 * Contains \Drupal\Core\Language\LanguageInterface.
 */

namespace Drupal\Core\Language;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
interface LanguageInterface
{
    /**
     * The language code used when no language is explicitly assigned (yet).
     *
     * Should be used when language information is not available or cannot be
     * determined. This special language code is useful when we know the data
     * might have linguistic information, but we don't know the language.
     *
     * See http://www.w3.org/International/questions/qa-no-language#undetermined.
     */
    const LANGCODE_NOT_SPECIFIED = LANGUAGE_NONE;

    /**
     * Language written left to right. Possible value of $language->direction.
     */
    const DIRECTION_LTR = 0;

    /**
     * Language written right to left. Possible value of $language->direction.
     */
    const DIRECTION_RTL = 1;

    /**
     * Gets the name of the language.
     *
     * @return string
     *   The human-readable name of the language (in the language that was
     *   used to construct this object).
     */
    public function getName();

    /**
     * Gets the ID (language code).
     *
     * @return string
     *   The language code.
     */
    public function getId();

    /**
     * Gets the text direction (left-to-right or right-to-left).
     *
     * @return string
     *   Either self::DIRECTION_LTR or self::DIRECTION_RTL.
     */
    public function getDirection();

    /**
     * Gets the weight of the language.
     *
     * @return int
     *   The weight, used to order languages with larger positive weights sinking
     *   items toward the bottom of lists.
     */
    public function getWeight();

    /**
     * Returns whether this language is the default language.
     *
     * @return bool
     *   Whether the language is the default language.
     */
    public function isDefault();

    /**
     * Returns whether this language is locked.
     *
     * @return bool
     *   Whether the language is locked or not.
     */
    public function isLocked();
}
