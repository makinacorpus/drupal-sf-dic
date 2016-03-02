<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\ParameterBag;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class DrupalParameterBag extends ParameterBag
{
    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        if (isset($GLOBALS['conf']) && array_key_exists($name, $GLOBALS['conf'])) {
            return $GLOBALS['conf'][$name];
        }
        if (parent::has($name)) {
            return parent::get($name);
        }

        trigger_error(sprintf("%s: container parameter or drupal variable is undefined", $name), E_USER_DEPRECATED);

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function has($name)
    {
        return (isset($GLOBALS['conf']) && array_key_exists($name, $GLOBALS['conf'])) || parent::has($name);
    }
}
