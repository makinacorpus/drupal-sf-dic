<?php

namespace Drupal\Core\Extension;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
interface ModuleHandlerInterface
{
    /**
     * Determines whether a given module is enabled.
     *
     * @param string $module
     *   The name of the module (without the .module extension).
     *
     * @return bool
     *   TRUE if the module is both installed and enabled.
     */
    public function moduleExists($module);

    /**
     * Loads an include file for each enabled module.
     *
     * @param string $type
     *   The include file's type (file extension).
     * @param string $name
     *   (optional) The base file name (without the $type extension). If omitted,
     *   each module's name is used; i.e., "$module.$type" by default.
     */
    public function loadAllIncludes($type, $name = null);

    /**
     * Loads a module include file.
     *
     * Examples:
     * @code
     *   // Load node.admin.inc from the node module.
     *   $this->loadInclude('node', 'inc', 'node.admin');
     *   // Load content_types.inc from the node module.
     *   $this->loadInclude('node', 'inc', ''content_types');
     * @endcode
     *
     * @param string $module
     *   The module to which the include file belongs.
     * @param string $type
     *   The include file's type (file extension).
     * @param string $name
     *   (optional) The base file name (without the $type extension). If omitted,
     *   $module is used; i.e., resulting in "$module.$type" by default.
     *
     * @return string|false
     *   The name of the included file, if successful; FALSE otherwise.
     */
    public function loadInclude($module, $type, $name = null);

    /**
     * Retrieves a list of hooks that are declared through hook_hook_info().
     *
     * @return array
     *   An associative array whose keys are hook names and whose values are an
     *   associative array containing a group name. The structure of the array
     *   is the same as the return value of hook_hook_info().
     *
     * @see hook_hook_info()
     */
    public function getHookInfo();

    /**
     * Determines which modules are implementing a hook.
     *
     * @param string $hook
     *   The name of the hook (e.g. "help" or "menu").
     *
     * @return array
     *   An array with the names of the modules which are implementing this hook.
     */
    public function getImplementations($hook);

    /**
     * Resets the cached list of hook implementations.
     */
    public function resetImplementations();

    /**
     * Returns whether a given module implements a given hook.
     *
     * @param string $module
     *   The name of the module (without the .module extension).
     * @param string $hook
     *   The name of the hook (e.g. "help" or "menu").
     *
     * @return bool
     *   TRUE if the module is both installed and enabled, and the hook is
     *   implemented in that module.
     */
    public function implementsHook($module, $hook);

    /**
     * Invokes a hook in a particular module.
     *
     * @param string $module
     *   The name of the module (without the .module extension).
     * @param string $hook
     *   The name of the hook to invoke.
     * @param ...
     *   Arguments to pass to the hook implementation.
     *
     * @return mixed
     *   The return value of the hook implementation.
     */
    public function invoke($module, $hook, array $args = []);

    /**
     * Invokes a hook in all enabled modules that implement it.
     *
     * @param string $hook
     *   The name of the hook to invoke.
     * @param array $args
     *   Arguments to pass to the hook.
     *
     * @return array
     *   An array of return values of the hook implementations. If modules return
     *   arrays from their implementations, those are merged into one array.
     */
    public function invokeAll($hook, array $args = []);

    /**
     * Passes alterable variables to specific hook_TYPE_alter() implementations.
     *
     * This dispatch function hands off the passed-in variables to type-specific
     * hook_TYPE_alter() implementations in modules. It ensures a consistent
     * interface for all altering operations.
     *
     * A maximum of 2 alterable arguments is supported. In case more arguments need
     * to be passed and alterable, modules provide additional variables assigned by
     * reference in the last $context argument:
     * @code
     *   $context = array(
     *     'alterable' => &$alterable,
     *     'unalterable' => $unalterable,
     *     'foo' => 'bar',
     *   );
     *   $this->alter('mymodule_data', $alterable1, $alterable2, $context);
     * @endcode
     *
     * Note that objects are always passed by reference in PHP5. If it is absolutely
     * required that no implementation alters a passed object in $context, then an
     * object needs to be cloned:
     * @code
     *   $context = array(
     *     'unalterable_object' => clone $object,
     *   );
     *   $this->alter('mymodule_data', $data, $context);
     * @endcode
     *
     * @param string|array $type
     *   A string describing the type of the alterable $data. 'form', 'links',
     *   'node_content', and so on are several examples. Alternatively can be an
     *   array, in which case hook_TYPE_alter() is invoked for each value in the
     *   array, ordered first by module, and then for each module, in the order of
     *   values in $type. For example, when Form API is using $this->alter() to
     *   execute both hook_form_alter() and hook_form_FORM_ID_alter()
     *   implementations, it passes array('form', 'form_' . $form_id) for $type.
     * @param mixed $data
     *   The variable that will be passed to hook_TYPE_alter() implementations to be
     *   altered. The type of this variable depends on the value of the $type
     *   argument. For example, when altering a 'form', $data will be a structured
     *   array. When altering a 'profile', $data will be an object.
     * @param mixed $context1
     *   (optional) An additional variable that is passed by reference.
     * @param mixed $context2
     *   (optional) An additional variable that is passed by reference. If more
     *   context needs to be provided to implementations, then this should be an
     *   associative array as described above.
     */
    public function alter($type, &$data, &$context1 = null, &$context2 = null);

    /**
     * Gets the human readable name of a given module.
     *
     * @param string $module
     *   The machine name of the module which title should be shown.
     *
     * @return string
     *   Returns the human readable name of the module or the machine name passed
     *   in if no matching module is found.
     */
    public function getName($module);
}
