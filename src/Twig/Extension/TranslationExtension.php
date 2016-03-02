<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;

use Symfony\Bridge\Twig\TokenParser\TransTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransChoiceTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransDefaultDomainTokenParser;
use Symfony\Bridge\Twig\NodeVisitor\TranslationNodeVisitor;
use Symfony\Bridge\Twig\NodeVisitor\TranslationDefaultDomainNodeVisitor;

/**
 * Provides integration of the Translation component with Twig.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TranslationExtension extends \Twig_Extension
{
    private $translator;
    private $translationNodeVisitor;

    public function __construct(\Twig_NodeVisitorInterface $translationNodeVisitor = null)
    {
        if (!$translationNodeVisitor) {
            $translationNodeVisitor = new TranslationNodeVisitor();
        }

        $this->translationNodeVisitor = $translationNodeVisitor;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('trans', array($this, 'trans')),
            new \Twig_SimpleFilter('transchoice', array($this, 'transchoice')),
        );
    }

    /**
     * Returns the token parser instance to add to the existing list.
     *
     * @return array An array of Twig_TokenParser instances
     */
    public function getTokenParsers()
    {
        return array(
            // {% trans %}Symfony is great!{% endtrans %}
            new TransTokenParser(),

            // {% transchoice count %}
            //     {0} There is no apples|{1} There is one apple|]1,Inf] There is {{ count }} apples
            // {% endtranschoice %}
            new TransChoiceTokenParser(),

            // {% trans_default_domain "foobar" %}
            new TransDefaultDomainTokenParser(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeVisitors()
    {
        return array($this->translationNodeVisitor, new TranslationDefaultDomainNodeVisitor());
    }

    public function getTranslationNodeVisitor()
    {
        return $this->translationNodeVisitor;
    }

    public function trans($message, array $arguments = array(), $domain = null, $locale = null)
    {
        return t($message, $arguments, [
            'context'   => $domain, // Sorry for this
            'langcode'  => $locale,
        ]);
    }

    public function transchoice($message, $count, array $arguments = array(), $domain = null, $locale = null)
    {
        return format_plural($count, strtr($message, '%count%', '@count'), $arguments, [
            'context'   => $domain, // Sorry for this
            'langcode'  => $locale,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'translator';
    }
}
