<?php

namespace MakinaCorpus\Drupal\Sf\Routing\Generator;

use Symfony\Component\Routing\Generator\UrlGenerator as BaseUrlGenerator;

class UrlGenerator extends BaseUrlGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        if (null === $this->routes->get($name)) {

            // Drupal to the rescue
            // @todo From what I remember, there was a few other stuff to take
            // care of in this... can't really remember what...
            if ($parameters) {
                $tokens = [];
                foreach ($parameters as $key => $value) {
                    $tokens['%' . $key] = $value;
                }
                $name = strtr($name, $tokens);
            }

            return url($name, ['absolute' => self::ABSOLUTE_URL === $referenceType]);
        }

        return parent::generate($name, $parameters, $referenceType);
    }
}
