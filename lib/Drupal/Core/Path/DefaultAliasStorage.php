<?php

namespace Drupal\Core\Path;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Based upon the Drupal 8 implementation, but fully working with the
 * Drupal 7 database schema, this is a CUSTOM implementation, NOT the
 * Drupal 8 one.
 */
class DefaultAliasStorage implements AliasStorageInterface
{
    /**
     * @var \DatabaseConnection
     */
    protected $db;

    /**
     * @var ModuleHandlerInterface
     */
    protected $moduleHandler;

    /**
     * Default constructor
     *
     * @param \DatabaseConnection $db
     * @param ModuleHandlerInterface $moduleHandler
     */
    public function __construct(\DatabaseConnection $db, ModuleHandlerInterface $moduleHandler)
    {
        $this->db = $db;
        $this->moduleHandler = $moduleHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function save($source, $alias, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED, $pid = null)
    {
        $path = [
            'source'    => $source,
            'alias'     => $alias,
            'language'  => $langcode,
        ];

        if ($pid) {

            $this
                ->db
                ->merge('url_alias')
                ->key(['pid' => $pid])
                ->fields($path)
                ->execute()
            ;

            $this->moduleHandler->invokeAll('path_update', [$path]);

            $path['pid'] = $pid;

        } else {

            $this
                ->db
                ->insert('url_alias')
                ->fields($path)
                ->execute()
            ;

            $this->moduleHandler->invokeAll('path_insert', [$path]);
        }

        \Drupal::service('path.alias_manager')->cacheClear($source);

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function load($conditions)
    {
        if (!$conditions) {
            return;
        }

        $select = $this->db->select('url_alias', 'u');

        foreach ($conditions as $field => $value) {
            if ($field == 'source' || $field == 'alias') {
                // Use LIKE for case-insensitive matching (stupid).
                $select->condition('u.' . $field, $this->db->escapeLike($value), 'LIKE');
            } else {
                if ('langcode' === $field) { // Drupal 7 compat
                    $field = 'language';
                }
                $select->condition('u.' . $field, $value);
            }
        }

        return $select
            ->fields('u')
            ->orderBy('u.pid', 'DESC')
            ->range(0, 1)
            ->execute()
            ->fetchAssoc('pid')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($conditions)
    {
        if (!$conditions) {
            return;
        }

        $path = $this->load($conditions);
        $query = $this->db->delete('url_alias');

        foreach ($conditions as $field => $value) {
            if ($field == 'source' || $field == 'alias') {
                // Use LIKE for case-insensitive matching (still stupid).
                $query->condition($field, $this->db->escapeLike($value), 'LIKE');
            } else {
                if ('langcode' === $field) { // Drupal 7 compat
                    $field = 'language';
                }
                $query->condition($field, $value);
            }
        }

        $deleted = $query->execute();
        // @todo Switch to using an event for this instead of a hook.
        $this->moduleHandler->invokeAll('path_delete', [$path]);

        \Drupal::service('path.alias_manager')->cacheClear();

        return $deleted;
    }

    /**
     * {@inheritdoc}
     */
    public function lookupPathAlias($path, $langcode)
    {
        // See the queries above. Use LIKE for case-insensitive matching.
        $source = $this->db->escapeLike($path);

        $query = $this
            ->db
            ->select('url_alias', 'u')
            ->fields('u', ['alias'])
            ->condition('u.source', $source, 'LIKE')
        ;

        if (LanguageInterface::LANGCODE_NOT_SPECIFIED === $langcode) {
            $langcodeList = [$langcode];
        } else {
            $langcodeList = [$langcode, LanguageInterface::LANGCODE_NOT_SPECIFIED];
            if (LanguageInterface::LANGCODE_NOT_SPECIFIED < $langcode) {
                $query->orderBy('u.language', 'DESC');
            } else {
                $query->orderBy('u.language', 'ASC');
            }
        }

        return $query
            ->orderBy('u.pid', 'DESC')
            ->condition('u.language', $langcodeList)
            ->execute()
            ->fetchField()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function lookupPathSource($path, $langcode)
    {
        // See the queries above. Use LIKE for case-insensitive matching.
        $alias = $this->db->escapeLike($path);

        $query = $this
            ->db
            ->select('url_alias', 'u')
            ->fields('u', ['source'])
            ->condition('u.alias', $alias, 'LIKE')
        ;

        if (LanguageInterface::LANGCODE_NOT_SPECIFIED === $langcode) {
            $langcodeList = [$langcode];
        } else {
            $langcodeList = [$langcode, LanguageInterface::LANGCODE_NOT_SPECIFIED];
            if (LanguageInterface::LANGCODE_NOT_SPECIFIED < $langcode) {
                $query->orderBy('u.language', 'DESC');
            } else {
                $query->orderBy('u.language', 'ASC');
            }
        }

        return $query
            ->orderBy('u.pid', 'DESC')
            ->condition('u.language', $langcodeList)
            ->execute()
            ->fetchField()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function aliasExists($alias, $langcode, $source = null)
    {
        // Use LIKE and NOT LIKE for case-insensitive matching (stupid).
        $query = $this
            ->db
            ->select('url_alias')
            ->condition('alias', $this->db->escapeLike($alias), 'LIKE')
            ->condition('language', $langcode)
        ;

        if (!empty($source)) {
            $query->condition('source', $this->db->escapeLike($source), 'NOT LIKE');
        }

        $query->addExpression('1');

        return (bool)$query
            ->range(0, 1)
            ->execute()
            ->fetchField()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function preloadPathAlias($sources, $langcode)
    {
        // VERY IMPORTANT PIECE OF DOCUMENTATION, BECAUSE CORE DOES NOT
        // DOCUMENT IT VERY WELL:
        //  - the query inverse all the orders 'pid' and 'language' compared
        //    to the original ::lookupPathAlias() method
        //  - smart little bitches, it seems they didn't know how to write it
        //    correctly in SQL (and neither do I actually) - so they rely on
        //    the fetchAllKeyed() method, which iterates in order on the rows
        //    making them squashing the previously fetched one

        $query = $this
            ->db
            ->select('url_alias', 'u')
            ->fields('u', ['source', 'alias'])
        ;

        $condition = new \DatabaseCondition('OR');
        foreach ($sources as $source) {
            // See the queries above. Use LIKE for case-insensitive matching.
            $condition->condition('u.source', $this->db->escapeLike($source), 'LIKE');
        }
        $query->condition($condition);

        if (LanguageInterface::LANGCODE_NOT_SPECIFIED === $langcode) {
            $langcodeList = [$langcode];
        } else {
            $langcodeList = [$langcode, LanguageInterface::LANGCODE_NOT_SPECIFIED];
            // !!! condition here is inversed from the lookup*() methods
            if (LanguageInterface::LANGCODE_NOT_SPECIFIED > $langcode) {
                $query->orderBy('u.language', 'DESC');
            } else {
                $query->orderBy('u.language', 'ASC');
            }
        }

        return $query
            // !!! order here is inversed from the lookup*() methods
            ->orderBy('u.pid', 'ASC')
            ->condition('u.language', $langcodeList)
            ->execute()
            ->fetchAllKeyed()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasesForAdminListing($header, $keys = NULL)
    {
        $query = $this
            ->db
            ->select('url_alias', 'u')
            ->extend('PagerDefault')
            ->extend('TableSort')
        ;

        if ($keys) {
            // Replace wildcards with PDO wildcards.
            $values = '%' . preg_replace('!\*+!', '%', $keys) . '%';

            $query
                ->condition(
                    db_or()
                        ->condition('u.alias', $values, 'LIKE')
                        ->condition('u.source', $values, 'LIKE')
                )
            ;
        }

        return $query
            ->fields('u')
            ->orderByHeader($header)
            ->limit(50)
            ->execute()
            ->fetchAll()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function pathHasMatchingAlias($initial_substring)
    {
        $query = $this->db->select('url_alias', 'u');
        $query->addExpression(1);

        return (bool)$query
            ->condition('u.source', $this->db->escapeLike($initial_substring) . '%', 'LIKE')
            ->range(0, 1)
            ->execute()
            ->fetchField()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelist()
    {
        return $this->db->query("SELECT DISTINCT SUBSTRING_INDEX(source, '/', 1) AS path FROM {url_alias}")->fetchCol();
    }
}
