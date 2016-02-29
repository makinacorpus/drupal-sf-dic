<?php

namespace Drupal\Core\Form;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
interface FormBuilderInterface
{
    /**
     * Alias of drupal_get_form() handling FromInterface forms
     *
     * @param string $formClass
     *   Form class name
     *
     * @return mixed[]
     *   drupal_render() friendly structure
     */
    public function getForm($formClass);
}
