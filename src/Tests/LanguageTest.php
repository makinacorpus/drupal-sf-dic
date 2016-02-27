<?php

namespace MakinaCorpus\Drupal\Sf\Container\Tests;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\Language;

class LanguageTest extends AbstractDrupalTest
{
    protected function setUp()
    {
        $GLOBALS['conf']['language_default'] = (object)[
            'language'    => 'en',
            'name'        => 'English',
            'native'      => 'English',
            'direction'   => 0,
            'enabled'     => 1,
            'plurals'     => 0,
            'formula'     => '',
            'domain'      => '',
            'prefix'      => '',
            'weight'      => 0,
            'javascript'  => '',
        ];
    }

    public function testProxyAndConsts()
    {
        // Ensures that copy/pasted then modified code points toward Drupal
        // 7 constants and not Drupal 8 values
      $this->assertSame(0, LanguageInterface::DIRECTION_LTR);
      $this->assertSame(1, LanguageInterface::DIRECTION_RTL);
      $this->assertSame(LANGUAGE_NONE, LanguageInterface::LANGCODE_NOT_SPECIFIED);
    }

    public function testOtherIsNotDefault()
    {
        // Forces a full bootstrap.
        $this->getDrupalContainer();

        $fr = new Language();
        $fr->language = 'fr';

        $this->assertFalse($fr->isDefault());
    }

    public function testDefaultLanguage()
    {
        // Forces a full bootstrap.
        $this->getDrupalContainer();

        $global = $GLOBALS['language'];

        $this->assertInstanceOf('\Drupal\Core\Language\Language', $global);
        $this->assertInstanceOf('\Drupal\Core\Language\LanguageInterface', $global);

        // And now ensure default values
        $this->assertSame('en', $global->language);
        $this->assertSame('English', $global->name);
        $this->assertSame(0, $global->direction);

        // And getters
        /* @var $global LanguageInterface */
        $this->assertSame('en', $global->getId());
        $this->assertSame('English', $global->getName());
        $this->assertSame(0, $global->getWeight());
        $this->assertSame(LanguageInterface::DIRECTION_LTR, $global->getDirection());
        $this->assertTrue($global->isDefault());
        $this->assertFalse($global->isLocked());
    }
}
