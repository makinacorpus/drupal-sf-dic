<?php

namespace MakinaCorpus\Drupal\Sf\Translation;

use Symfony\Component\Translation\TranslatorInterface;

class Translator implements TranslatorInterface
{
    private $locale;

    public function __construct($locale = null)
    {
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        return t($id, $parameters, [
            'context'   => $domain, // Sorry for this
            'langcode'  => $locale,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        $singular = null;
        $plural = null;

        $forms = explode('|', $id);
        foreach ($forms as $form) {
            if (false !== strpos($form, '{1}')) {
                $singular = str_replace('{1}', '@count', $form);
            }
            if (false !== strpos($form, 'Inf[')) {
                $plural = preg_replace('/\]\d+,Inf\[/', '@count', $form);
            }
        }

        $parameters['@count'] = $number;

        return format_plural($number, $singular, $plural, $parameters, [
            'context'   => $domain, // Sorry for this
            'langcode'  => $locale,
        ]);
    }
}
