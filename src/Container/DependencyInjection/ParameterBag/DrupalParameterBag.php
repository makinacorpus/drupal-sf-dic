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
            if (parent::has($name)) {
                return parent::get($name);
            }
            if (isset($GLOBALS['conf']) && array_key_exists($name, $GLOBALS['conf'])) {
                return $GLOBALS['conf'][$name];
            }

            throw new \InvalidArgumentException(sprintf("%s: parameter does not exist"));
        }

        if (isset($GLOBALS['conf']) && array_key_exists($name, $GLOBALS['conf'])) {
            return $GLOBALS['conf'][$name];
        }
        if (parent::has($name)) {
            return parent::get($name);
        }

        // This should be logged, somehow
        // trigger_error(sprintf("%s: container parameter or drupal variable is undefined", $name), E_USER_DEPRECATED);

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
