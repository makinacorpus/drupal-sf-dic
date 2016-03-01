<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Mockup;

use Drupal\Core\Extension\ModuleHandlerInterface;

class NullModuleHandler implements ModuleHandlerInterface
{
    public function moduleExists($module)
    {
    }

    public function loadAllIncludes($type, $name = null)
    {
        return module_load_all_includes($type, $name);
    }

    public function loadInclude($module, $type, $name = null)
    {
        return module_load_include($type, $module, $name);
    }

    public function getHookInfo()
    {
        return [];
    }

    public function getImplementations($hook)
    {
        return [];
    }

    public function writeCache()
    {
    }

    public function resetImplementations()
    {
    }

    public function implementsHook($module, $hook)
    {
        return false;
    }

    public function invoke($module, $hook, array $args = [])
    {
    }

    public function invokeAll($hook, array $args = [])
    {
        return [];
    }

    public function alter($type, &$data, &$context1 = null, &$context2 = null)
    {
    }

    public function getName($module)
    {
    }
}

