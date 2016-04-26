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

    public function getTranslator()
    {
        return $this;
    }

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
            new \Twig_SimpleFilter('trans', array($this, 'trans'), ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('transchoice', array($this, 'transchoice'), ['is_safe' => ['html']]),
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

    public function transChoice($message, $count, array $arguments = array(), $domain = null, $locale = null)
    {
        $singular = null;
        $plural = null;

        $forms = explode('|', $message);
        foreach ($forms as $form) {
            if (false !== strpos($form, '{1}')) {
                $singular = str_replace('{1}', '@count', $form);
            }
            if (false !== strpos($form, 'Inf[')) {
                $plural = preg_replace('/\]\d+,Inf\[/', '@count', $form);
            }
        }

        $arguments['@count'] = $count;

        return format_plural($count, $singular, $plural, $arguments, [
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
