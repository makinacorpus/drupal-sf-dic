<?php

namespace MakinaCorpus\Drupal\Sf\Routing;

use Symfony\Bundle\FrameworkBundle\Routing\Router as BaseRouter;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class Router extends BaseRouter
{
    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        try {
            return $this->getGenerator()->generate($name, $parameters, $referenceType);
        } catch (RouteNotFoundException $e) {
            // @todo
            //   should we use drupal_valid_path() ?
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
    }
}
