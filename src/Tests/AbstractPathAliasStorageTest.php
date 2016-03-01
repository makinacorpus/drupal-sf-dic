<?php

namespace MakinaCorpus\Drupal\Sf\Tests;

use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Path\AliasManager;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Path\AliasStorageInterface;

abstract class AbstractPathAliasStorageTest extends AbstractDrupalTest
{
    /**
     * @var AliasStorageInterface
     */
    private $aliasStorage;

    /**
     * @var AliasManagerInterface
     */
    private $aliasManager;

    /**
     * @return AliasStorageInterface
     */
    abstract protected function createAliasStorage();

    protected function setUp()
    {
        parent::setUp();

        $this->getDrupalContainer(); // Enfore full bootstrap

        $this->aliasStorage =  $this->createAliasStorage();
        $this->aliasStorage->save('duplicate-langcode', 'alias-fr', 'fr');
        $this->aliasStorage->save('duplicate-langcode', 'alias-en', 'en');
        $this->aliasStorage->save('duplicate-langcode', 'alias-und', LanguageInterface::LANGCODE_NOT_SPECIFIED);
        $this->aliasStorage->save('duplicate-alias-1', 'duplicate-alias', LanguageInterface::LANGCODE_NOT_SPECIFIED);
        $this->aliasStorage->save('duplicate-alias-2', 'duplicate-alias', LanguageInterface::LANGCODE_NOT_SPECIFIED);
        $this->aliasStorage->save('normal-source', 'normal-alias', LanguageInterface::LANGCODE_NOT_SPECIFIED);

        $this->aliasManager = new AliasManager($this->aliasStorage);

        $GLOBALS['language'] = new Language();
    }

    public function testBasicBehavior()
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

    public function testDelete()
    {
        // @todo
    }

    public function testAliasManagerCacheAfterDelete()
    {
        // @todo
    }
}
