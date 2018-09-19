<?php

/**
 * @file
 * Contains \Drupal\Core\Language\Language.
 */

namespace Drupal\Core\Language;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
class Language implements LanguageInterface
{
    public $language = 'en';
    public $name = 'English';
    public $native = 'English';
    public $direction = self::DIRECTION_LTR;
    public $enabled = 1;
    public $plurals = 0;
    public $formula = '';
    public $domain = '';
    public $prefix = '';
    public $weight = 0;
    public $javascript = '';
    public $provider = 'language-default'; // LANGUAGE_NEGOTIATION_DEFAULT is sometime undefined.

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->language;
    }

    /**
     * {@inheritdoc}
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * {@inheritdoc}
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * {@inheritdoc}
     */
    public function isDefault()
    {
        return language_default()->language === $this->language;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocked()
    {
        return false;
    }
}
