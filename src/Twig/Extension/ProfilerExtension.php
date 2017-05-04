<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;

/**
 * Profiler helpers
 */
class ProfilerExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('sql_compress', [$this, 'compressSql'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('sql_format', [$this, 'formatSql'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Compress SQL
     *
     * @param string $string
     *   SQL code to format
     *
     * @return string
     */
    public function compressSql($string, $highlight = true)
    {
        if (class_exists('\SqlFormatter')) {
            return \SqlFormatter::highlight(\SqlFormatter::compress($string, $highlight));
        } else {
            return nl2br(htmlentities(str_replace("\n\n", "\n", $string)));
        }
    }

    /**
     * Format SQL
     *
     * @param string $string
     *   SQL code to format
     *
     * @return string
     */
    public function formatSql($string, $highlight = true)
    {
        if (class_exists('\SqlFormatter')) {
            return \SqlFormatter::format($string, $highlight);
        } else {
            return nl2br(htmlentities(str_replace("\n\n", "\n", $string)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_profiler';
    }
}
