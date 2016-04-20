<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Mockup;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Path\AliasStorageInterface;

class ArrayAliasStorage implements AliasStorageInterface
{
    /**
     * @var string[][]
     */
    protected $items = [];

    /**
     * @var int
     */
    protected $pid = 1;

    /**
     * {inheritdoc}
     */
    public function save($source, $alias, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED, $pid = null)
    {
        if ($pid) {
            if (isset($this->items[$pid])) {
                $this->items[$pid] = [
                    'source'    => $source,
                    'alias'     => $alias,
                    'langcode'  => $langcode,
                    'original'  => $this->items[$pid],
                ];
            }
        } else {
            $pid = $this->pid++;
            $this->items[$pid] = [
                'source'    => $source,
                'alias'     => $alias,
                'langcode'  => $langcode,
            ];
        }
        return $this->items[$pid];
    }

    /**
     * {inheritdoc}
     */
    public function load($conditions)
    {
        throw new \Exception("This object is not meant to test that");
    }

    /**
     * {inheritdoc}
     */
    public function delete($conditions)
    {
        throw new \Exception("This object is not meant to test that");
    }

    /**
     * {inheritdoc}
     */
    public function lookupPathAlias($path, $langcode)
    {
        $nonExisting = false;

        foreach ($this->items as $item) {
            if ($item['source'] === $path) {
                if ($item['langcode'] === $langcode) {
                    return $item['alias'];
                } else if ($item['langcode'] === LanguageInterface::LANGCODE_NOT_SPECIFIED) {
                    $nonExisting = $item['alias'];
                }
            }
        }

        return $nonExisting;
    }

    /**
     * {inheritdoc}
     */
    public function lookupPathSource($path, $langcode)
    {
        $nonExisting = false;

        foreach ($this->items as $item) {
            if ($item['alias'] === $path) {
                if ($item['langcode'] === $langcode) {
                    return $item['source'];
                } else if ($item['langcode'] === LanguageInterface::LANGCODE_NOT_SPECIFIED) {
                    $nonExisting = $item['source'];
                }
            }
        }

        return $nonExisting;
    }

    /**
     * {inheritdoc}
     */
    public function aliasExists($alias, $langcode, $source = null)
    {
        foreach ($this->items as $item) {
            if ($item['alias'] === $alias && $item['langcode'] === $langcode) {
                if ($source) {
                    if ($item['source'] === $source) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasesForAdminListing($header, $keys = null)
    {
        throw new \Exception("This object is not meant to test that");
    }

    /**
     * {@inheritdoc}
     */
    public function pathHasMatchingAlias($initialSubstring)
    {
        throw new \Exception("This object is not meant to test that");
    }

    /**
     * {@inheritdoc}
     */
    public function preloadPathAlias($sources, $langcode)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelist()
    {
        return [];
    }
}
