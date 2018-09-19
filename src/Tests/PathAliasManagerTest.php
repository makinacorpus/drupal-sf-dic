<?php

namespace MakinaCorpus\Drupal\Sf\Tests;

use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Path\AliasManager;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Path\AliasStorageInterface;

use MakinaCorpus\Drupal\Sf\Tests\Mockup\ArrayAliasStorage;

class PathAliasManagerTest extends AbstractDrupalTest
{
    /**
     * @var AliasStorageInterface
     */
    private $aliasStorage;

    /**
     * @var AliasManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aliasManager;

    protected function setUp()
    {
        // We need to bootstrap at least Drupal configuration so that
        // variable_get() is defined
        parent::setUp();
        $this->getDrupalContainer();

        if (!defined('LANGUAGE_NONE')) {
            define('LANGUAGE_NONE', 'und');
        }

        // Avoid variable_get() and variable_set() calls
        $GLOBALS['conf']['path_alias_whitelist'] = false;

        $this->aliasStorage = new ArrayAliasStorage();
        $this->aliasStorage->save('duplicate-langcode', 'alias-fr', 'fr');
        $this->aliasStorage->save('duplicate-langcode', 'alias-en', 'en');
        $this->aliasStorage->save('duplicate-langcode', 'alias-und', LanguageInterface::LANGCODE_NOT_SPECIFIED);
        $this->aliasStorage->save('duplicate-alias-1', 'duplicate-alias', LanguageInterface::LANGCODE_NOT_SPECIFIED);
        $this->aliasStorage->save('duplicate-alias-2', 'duplicate-alias', LanguageInterface::LANGCODE_NOT_SPECIFIED);
        $this->aliasStorage->save('normal-source', 'normal-alias', LanguageInterface::LANGCODE_NOT_SPECIFIED);

        $this->aliasManager = new AliasManager($this->aliasStorage);

        $GLOBALS['language'] = new Language();
    }

    public function tearDown()
    {
        variable_del('path_alias_whitelist');
    }

    public function testTheMockUp()
    {
        // Tests registered aliases
        $this->assertSame('alias-en', $this->aliasStorage->lookupPathAlias('duplicate-langcode', 'en'));
        $this->assertSame('alias-fr', $this->aliasStorage->lookupPathAlias('duplicate-langcode', 'fr'));
        $this->assertSame('alias-und', $this->aliasStorage->lookupPathAlias('duplicate-langcode', LanguageInterface::LANGCODE_NOT_SPECIFIED));
        // Non existing language returns the undefined language alias
        $this->assertSame('alias-und', $this->aliasStorage->lookupPathAlias('duplicate-langcode', 'martian'));
        // More basic tests
        $this->assertSame('duplicate-langcode', $this->aliasStorage->lookupPathSource('alias-fr', 'fr'));
        $this->assertSame('duplicate-langcode', $this->aliasStorage->lookupPathSource('alias-en', 'en'));
        $this->assertFalse($this->aliasStorage->lookupPathSource('alias-fr', LanguageInterface::LANGCODE_NOT_SPECIFIED));
        $this->assertFalse($this->aliasStorage->lookupPathSource('alias-en', LanguageInterface::LANGCODE_NOT_SPECIFIED));
        $this->assertFalse($this->aliasStorage->lookupPathSource('alias-fr', 'en'));
        $this->assertFalse($this->aliasStorage->lookupPathSource('alias-en', 'fr'));
    }

    public function testThatDuplicateLanguagesDontMessUp()
    {
        $alias = $this->aliasManager->getAliasByPath('duplicate-langcode', 'fr');
        $this->assertSame('alias-fr', $alias);
        $alias = $this->aliasManager->getAliasByPath('duplicate-langcode', 'en');
        $this->assertSame('alias-en', $alias);
        $alias = $this->aliasManager->getAliasByPath('duplicate-langcode', LanguageInterface::LANGCODE_NOT_SPECIFIED);
        $this->assertSame('alias-und', $alias);
        // Default is 'en', see ::setUp()
        $alias = $this->aliasManager->getAliasByPath('duplicate-langcode');
        $this->assertSame('alias-en', $alias);
        // Non existing language returns the undefined language alias
        $alias = $this->aliasManager->getAliasByPath('duplicate-langcode', 'martian');
        $this->assertSame('alias-und', $alias);

        $source = $this->aliasManager->getPathByAlias('alias-fr', 'fr');
        $this->assertSame('duplicate-langcode', $source);
        $source = $this->aliasManager->getPathByAlias('alias-en', 'en');
        $this->assertSame('duplicate-langcode', $source);
        $source = $this->aliasManager->getPathByAlias('alias-fr', 'en');
        $this->assertSame('alias-fr', $source);
        $source = $this->aliasManager->getPathByAlias('alias-en', 'fr');
        $this->assertSame('alias-en', $source);
        $source = $this->aliasManager->getPathByAlias('alias-fr', LanguageInterface::LANGCODE_NOT_SPECIFIED);
        $this->assertSame('alias-fr', $source);
        $source = $this->aliasManager->getPathByAlias('alias-en', LanguageInterface::LANGCODE_NOT_SPECIFIED);
        $this->assertSame('alias-en', $source);
        // This one WONT be found due to the fact that current language is 'en'
        $source = $this->aliasManager->getPathByAlias('alias-fr');
        $this->assertSame('alias-fr', $source);
        // This one WILL be found due to the fact that current language is 'en'
        $source = $this->aliasManager->getPathByAlias('alias-en');
        $this->assertSame('duplicate-langcode', $source);
        // Non existing language with no reccord in undefined language should
        // return the asked value itself
        $source = $this->aliasManager->getPathByAlias('alias-fr', 'martian');
        $this->assertSame('alias-fr', $source);
        $source = $this->aliasManager->getPathByAlias('alias-en', 'martian');
        $this->assertSame('alias-en', $source);
    }

    public function testThatNonExistingAliasIsAlias()
    {
        $alias = $this->aliasManager->getAliasByPath('pouet', 'fr');
        $this->assertSame('pouet', $alias);

        $alias = $this->aliasManager->getAliasByPath('pouet', 'fr');
        $this->assertSame('pouet', $alias);
    }

    public function testThatNonExistingSourceIsSource()
    {
        $source = $this->aliasManager->getPathByAlias('pouet', 'en');
        $this->assertSame('pouet', $source);

        $source = $this->aliasManager->getPathByAlias('pouet', 'en');
        $this->assertSame('pouet', $source);
    }

    public function testNormalBahvior()
    {
        // When providing no language, default is used, but trying to lookup a
        // non existing language should yield the same result
        foreach ([null, 'martian'] as $langcode) {
            $alias = $this->aliasManager->getAliasByPath('duplicate-alias-1', $langcode);
            $this->assertSame('duplicate-alias', $alias);
            $source = $this->aliasManager->getPathByAlias('duplicate-alias', $langcode);
            $this->assertSame('duplicate-alias-1', $source);
            $alias = $this->aliasManager->getAliasByPath('normal-source', $langcode);
            $this->assertSame('normal-alias', $alias);
            $source = $this->aliasManager->getPathByAlias('normal-alias', $langcode);
            $this->assertSame('normal-source', $source);
        }
    }
}
