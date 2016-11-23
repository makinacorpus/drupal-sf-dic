<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\ParameterBag;

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class DrupalParameterBag extends ParameterBag
{
    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        try {
            return parent::get($name);
        } catch (ParameterNotFoundException $e) {
            // In drupal, non existing parameters are allowed.
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDrupalOverride($name, $default)
    {
        if ('kernel.' === substr($name, 0, 7)) {
            // Kernel variables must be driven by the kernel only: here is the
            // explainaintion (not a simple one):
            //  - at kernel boot time, we compute a kernel.cache_dir using
            //    the defined global $conf variable, and suffixing with the
            //    env name;
            //  - we leave the global $conf untouched, because if we do need to
            //    rebuild the kernel and container from the same request, it
            //    must keep the original value to append once again the env
            //    name;
            //  - some compiler passes or other pieces of code will directly
            //    fetch the kernel parameter to resolve values (for example:
            //    "%kernel.cache_dir%/annotations" for the annotations cached
            //    reader directory);
            //  - this will use the $conf value, which has not the environment
            //    name prepended, and won't look for the right file at the right
            //    place.
            return $default;
        }

        if (isset($GLOBALS['conf']) && array_key_exists($name, $GLOBALS['conf'])) {
            return $GLOBALS['conf'][$name];
        }

        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function set($name, $value)
    {
        $this->parameters[strtolower($name)] = $this->getDrupalOverride($name, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function add(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->parameters[strtolower($key)] = $this->getDrupalOverride($key, $value);
        }
    }
}
