<?php

namespace MakinaCorpus\Drupal\Sf\Twig;

/**
 * Original code this was forked from TFD7 project:
 *   https://github.com/TFD7/TFD7
 *
 * All credits to its authors.
 *
 * @author René Bakx
 * @see http://tfd7.rocks for more information
 */
class Extension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return [
            'base_path' => base_path(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeVisitors()
    {
        return [new NodeVisitor()];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('attributes', 'drupal_attributes', ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('check_plain', 'check_plain', ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('defaults', [$this, 'getDefault']),
            new \Twig_SimpleFilter('machine_name', [$this, 'renderMachineName'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('size', 'format_size', ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('ucfirst', 'ucfirst'),
            new \Twig_SimpleFilter('without', [$this, 'withoutFilter'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('t', [$this, 'trans'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('array_search', 'array_search'),
            new \Twig_SimpleFunction('children', [$this, 'getElementChildren']),
            new \Twig_SimpleFunction('classname', 'get_class'),
            new \Twig_SimpleFunction('get_form_errors', [$this, 'getDrupalFormErrors']),
            new \Twig_SimpleFunction('hide', [$this, 'drupalHide']),
            new \Twig_SimpleFunction('machine_name', [$this, 'renderMachineName'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('module_exists', 'module_exists'),
            new \Twig_SimpleFunction('t', [$this, 'trans'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('theme_get_setting', 'theme_get_setting'),
            new \Twig_SimpleFunction('variable_get', 'variable_get'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sf_dic_env';
    }

    /**
     * Render string as machine name
     */
    public function renderMachineName($string)
    {
        return preg_replace(['/[^a-z0-9]/', '/_+/'], '_', strtolower($string));
    }

    /**
     * You should NOT use this.
     */
    public function trans($message, array $arguments = array(), $domain = null, $locale = null)
    {
        @trigger_error(sprintf("You should not use the |t filter, but the |trans filter instead.", __METHOD__), E_USER_DEPRECATED);

        return t($message, $arguments, [
            'context'   => $domain, // Sorry for this
            'langcode'  => $locale,
        ]);
    }

    /**
     * Get current Drupal form errors (this is damn stupid, but keeping it for compat).
     */
    public function getDrupalFormErrors()
    {
        $errors = form_get_errors();
        if (!empty($errors)) {
            $newErrors = array();
            foreach ($errors as $key => $error) {
                $newKey = str_replace('submitted][', 'submitted[', $key);
                if ($newKey !== $key) {
                    $newKey = $newKey . ']';
                }
                $newErrors[$newKey] = $error;
            }
            $errors = $newErrors;
        }
        return $errors;
    }

    /**
     * Wrapper for element_children
     */
    public function getElementChildren($render_array)
    {
        if ($render_array && is_array($render_array)) {
            $children = [];
            foreach (element_children($render_array) as $key) {
                $children[] = $render_array[$key];
            }
            return $children;
        }
        return [];
    }

    /**
     * Wrapper for hide() function
     */
    public function drupalHide($value)
    {
        if (is_array($value)) {
            hide($value);
        }
    }

    /**
     * Some quite stupid legacy function, keeping it anyway.
     */
    public function getDefault()
    {
        foreach (function_get_args() as $value) {
            if ($value) {
                return $value;
            }
        }
    }

    /**
     * Drupal 8 without filter
     */
    public function withoutFilter($input, $keys_to_remove)
    {
        if ($input instanceof \ArrayAccess) {
            $filtered = clone $input;
        } else if (is_array($input)) {
            $filtered = $input;
        } else {
            return $input;
        }
        foreach ($keys_to_remove as $key) {
            unset($filtered[$key]);
        }
        return $filtered;
    }
}
