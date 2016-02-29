<?php

namespace Drupal\Core\Extension;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
class ModuleHandler implements ModuleHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function moduleExists($module)
    {
        return module_exists($module);
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllIncludes($type, $name = null)
    {
        return module_load_all_includes($type, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function loadInclude($module, $type, $name = null)
    {
        return module_load_include($type, $module, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHookInfo()
    {
        return module_hook_info();
    }

    /**
     * {@inheritdoc}
     */
    public function getImplementations($hook)
    {
        return module_implements($hook);
    }

    /**
     * {@inheritdoc}
     */
    public function writeCache()
    {
        // Keep that as a noop.
    }

    /**
     * {@inheritdoc}
     */
    public function resetImplementations()
    {
        module_implements(null, false, true);
    }

    /**
     * {@inheritdoc}
     */
    public function implementsHook($module, $hook)
    {
        return in_array($module, module_implements($hook));
    }

    /**
     * {@inheritdoc}
     */
    public function invoke($module, $hook, array $args = [])
    {
        array_unshift($args, $hook);
        array_unshift($args, $module);
        return call_user_func_array('module_invoke', $args);
    }

    /**
     * {@inheritdoc}
     */
    public function invokeAll($hook, array $args = [])
    {
        array_unshift($args, $hook);
        return call_user_func_array('module_invoke_all', $args);
    }

    /**
     * {@inheritdoc}
     */
    public function alter($type, &$data, &$context1 = null, &$context2 = null)
    {
        return drupal_alter($type, $data, $context1, $context2);
    }

    /**
     * {@inheritdoc}
     */
    public function getName($module)
    {
        $info = system_get_info('module', $module);
        return isset($info['name']) ? $info['name'] : $module;
    }
}

