<?php

namespace Drupal\Core\Form;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
interface FormStateInterface
{
    /**
     * Returns a reference to the complete form array.
     *
     * @return array
     *   The complete form array.
     */
    public function &getCompleteForm();

    /**
     * Stores the complete form array.
     *
     * @param array $complete_form
     *   The complete form array.
     *
     * @return $this
     */
    public function setCompleteForm(array &$complete_form);

    /**
     * Ensures an include file is loaded whenever the form is processed.
     *
     * Example:
     * @code
     *   // Load node.admin.inc from Node module.
     *   $form_state->loadInclude('node', 'inc', 'node.admin');
     * @endcode
     *
     * Use this function instead of module_load_include() from inside a form
     * constructor or any form processing logic as it ensures that the include file
     * is loaded whenever the form is processed. In contrast to using
     * module_load_include() directly, this method makes sure the include file is
     * correctly loaded also if the form is cached.
     *
     * @param string $module
     *   The module to which the include file belongs.
     * @param string $type
     *   The include file's type (file extension).
     * @param string|null $name
     *   (optional) The base file name (without the $type extension). If omitted,
     *   $module is used; i.e., resulting in "$module.$type" by default.
     *
     * @return string|false
     *   The filepath of the loaded include file, or false if the include file was
     *   not found or has been loaded already.
     *
     * @see module_load_include()
     */
    public function loadInclude($module, $type, $name = null);

    /**
     * Sets the value of the form state.
     *
     * @param array $form_state_additions
     *   An array of values to add to the form state.
     *
     * @return $this
     */
    public function setFormState(array $form_state_additions);

    /**
     * Sets the redirect for the form.
     *
     * WARNING: This method differs from Drupal 8 since Drupal 7 does not have
     * the same routing API
     *
     * @param string $path
     *   Path where to redirect
     * @param array $options
     *   An associative array of additional options. See url() for more info.
     *
     * @return $this
     */
    public function setRedirect($path, array $options = []);

    /**
     * Sets the entire set of arbitrary data.
     *
     * @param array $storage
     *   The entire set of arbitrary data to store for this form.
     *
     * @return $this
     */
    public function setStorage(array $storage);

    /**
     * Returns the entire set of arbitrary data.
     *
     * @return array
     *   The entire set of arbitrary data to store for this form.
     */
    public function &getStorage();

    /**
     * Gets any arbitrary property.
     *
     * @param string|array $property
     *   Properties are often stored as multi-dimensional associative arrays. If
     *   $property is a string, it will return $storage[$property]. If $property
     *   is an array, each element of the array will be used as a nested key. If
     *   $property = ['foo', 'bar'] it will return $storage['foo']['bar'].
     *
     * @return mixed
     *   A reference to the value for that property, or null if the property does
     *   not exist.
     */
    public function &get($property);

    /**
     * Sets a value to an arbitrary property.
     *
     * @param string|array $property
     *   Properties are often stored as multi-dimensional associative arrays. If
     *   $property is a string, it will use $storage[$property] = $value. If
     *   $property is an array, each element of the array will be used as a nested
     *   key. If $property = ['foo', 'bar'] it will use
     *   $storage['foo']['bar'] = $value.
     * @param mixed $value
     *   The value to set.
     *
     * @return $this
     */
    public function set($property, $value);

    /**
     * Determines if an arbitrary property is present.
     *
     * @param string $property
     *   Properties are often stored as multi-dimensional associative arrays. If
     *   $property is a string, it will return isset($storage[$property]). If
     *   $property is an array, each element of the array will be used as a nested
     *   key. If $property = ['foo', 'bar'] it will return
     *   isset($storage['foo']['bar']).
     */
    public function has($property);

    /**
     * Returns the submitted and sanitized form values.
     *
     * @return array
     *   An associative array of values submitted to the form.
     */
    public function &getValues();

    /**
     * Returns the submitted form value for a specific key.
     *
     * @param string|array $key
     *   Values are stored as a multi-dimensional associative array. If $key is a
     *   string, it will return $values[$key]. If $key is an array, each element
     *   of the array will be used as a nested key. If $key = array('foo', 'bar')
     *   it will return $values['foo']['bar'].
     * @param mixed $default
     *   (optional) The default value if the specified key does not exist.
     *
     * @return mixed
     *   The value for the given key, or null.
     */
    public function &getValue($key, $default = null);

    /**
     * Sets the submitted form values.
     *
     * This should be avoided, since these values have been validated already. Use
     * self::setUserInput() instead.
     *
     * @param array $values
     *   The multi-dimensional associative array of form values.
     *
     * @return $this
     */
    public function setValues(array $values);

    /**
     * Sets the submitted form value for a specific key.
     *
     * @param string|array $key
     *   Values are stored as a multi-dimensional associative array. If $key is a
     *   string, it will use $values[$key] = $value. If $key is an array, each
     *   element of the array will be used as a nested key. If
     *   $key = array('foo', 'bar') it will use $values['foo']['bar'] = $value.
     * @param mixed $value
     *   The value to set.
     *
     * @return $this
     */
    public function setValue($key, $value);

    /**
     * Removes a specific key from the submitted form values.
     *
     * @param string|array $key
     *   Values are stored as a multi-dimensional associative array. If $key is a
     *   string, it will use unset($values[$key]). If $key is an array, each
     *   element of the array will be used as a nested key. If
     *   $key = array('foo', 'bar') it will use unset($values['foo']['bar']).
     *
     * @return $this
     */
    public function unsetValue($key);

    /**
     * Determines if a specific key is present in the submitted form values.
     *
     * @param string|array $key
     *   Values are stored as a multi-dimensional associative array. If $key is a
     *   string, it will return isset($values[$key]). If $key is an array, each
     *   element of the array will be used as a nested key. If
     *   $key = array('foo', 'bar') it will return isset($values['foo']['bar']).
     *
     * @return bool
     *   true if the $key is set, false otherwise.
     */
    public function hasValue($key);

    /**
     * Determines if a specific key has a value in the submitted form values.
     *
     * @param string|array $key
     *   Values are stored as a multi-dimensional associative array. If $key is a
     *   string, it will return empty($values[$key]). If $key is an array, each
     *   element of the array will be used as a nested key. If
     *   $key = array('foo', 'bar') it will return empty($values['foo']['bar']).
     *
     * @return bool
     *   true if the $key has no value, false otherwise.
     */
    public function isValueEmpty($key);

    /**
     * Changes submitted form values during form validation.
     *
     * Use this function to change the submitted value of a form element in a form
     * validation function, so that the changed value persists in $form_state
     * through to the submission handlers.
     *
     * Note that form validation functions are specified in the '#validate'
     * component of the form array (the value of $form['#validate'] is an array of
     * validation function names). If the form does not originate in your module,
     * you can implement hook_form_FORM_ID_alter() to add a validation function
     * to $form['#validate'].
     *
     * @param array $element
     *   The form element that should have its value updated; in most cases you
     *   can just pass in the element from the $form array, although the only
     *   component that is actually used is '#parents'. If constructing yourself,
     *   set $element['#parents'] to be an array giving the path through the form
     *   array's keys to the element whose value you want to update. For instance,
     *   if you want to update the value of $form['elem1']['elem2'], which should
     *   be stored in $form_state->getValue(array('elem1', 'elem2')), you would
     *   set $element['#parents'] = array('elem1','elem2').
     * @param mixed $value
     *   The new value for the form element.
     *
     * @return $this
     */
    public function setValueForElement(array $element, $value);

    /**
     * Determines if any forms have any errors.
     *
     * @return bool
     *   true if any form has any errors, false otherwise.
     */
    public static function hasAnyErrors();

    /**
     * Files an error against a form element.
     *
     * When a validation error is detected, the validator calls this method to
     * indicate which element needs to be changed and provide an error message.
     * This causes the Form API to not execute the form submit handlers, and
     * instead to re-display the form to the user with the corresponding elements
     * rendered with an 'error' CSS class (shown as red by default).
     *
     * The standard behavior of this method can be changed if a button provides
     * the #limit_validation_errors property. Multistep forms not wanting to
     * validate the whole form can set #limit_validation_errors on buttons to
     * limit validation errors to only certain elements. For example, pressing the
     * "Previous" button in a multistep form should not fire validation errors
     * just because the current step has invalid values. If
     * #limit_validation_errors is set on a clicked button, the button must also
     * define a #submit property (may be set to an empty array). Any #submit
     * handlers will be executed even if there is invalid input, so extreme care
     * should be taken with respect to any actions taken by them. This is
     * typically not a problem with buttons like "Previous" or "Add more" that do
     * not invoke persistent storage of the submitted form values. Do not use the
     * #limit_validation_errors property on buttons that trigger saving of form
     * values to the database.
     *
     * The #limit_validation_errors property is a list of "sections" within
     * $form_state->getValues() that must contain valid values. Each "section" is
     * an array with the ordered set of keys needed to reach that part of
     * $form_state->getValues() (i.e., the #parents property of the element).
     *
     * Example 1: Allow the "Previous" button to function, regardless of whether
     * any user input is valid.
     *
     * @code
     *   $form['actions']['previous'] = array(
     *     '#type' => 'submit',
     *     '#value' => t('Previous'),
     *     '#limit_validation_errors' => [],       // No validation.
     *     '#submit' => array('some_submit_function'),  // #submit required.
     *   );
     * @endcode
     *
     * Example 2: Require some, but not all, user input to be valid to process the
     * submission of a "Previous" button.
     *
     * @code
     *   $form['actions']['previous'] = array(
     *     '#type' => 'submit',
     *     '#value' => t('Previous'),
     *     '#limit_validation_errors' => array(
     *       // Validate $form_state->getValue('step1').
     *       array('step1'),
     *       // Validate $form_state->getValue(array('foo', 'bar')).
     *       array('foo', 'bar'),
     *     ),
     *     '#submit' => array('some_submit_function'), // #submit required.
     *   );
     * @endcode
     *
     * This will require $form_state->getValue('step1') and everything within it
     * (for example, $form_state->getValue(array('step1', 'choice'))) to be valid,
     * so calls to self::setErrorByName('step1', $message) or
     * self::setErrorByName('step1][choice', $message) will prevent the submit
     * handlers from running, and result in the error message being displayed to
     * the user. However, calls to self::setErrorByName('step2', $message) and
     * self::setErrorByName('step2][groupX][choiceY', $message) will be
     * suppressed, resulting in the message not being displayed to the user, and
     * the submit handlers will run despite $form_state->getValue('step2') and
     * $form_state->getValue(array('step2', 'groupX', 'choiceY')) containing
     * invalid values. Errors for an invalid $form_state->getValue('foo') will be
     * suppressed, but errors flagging invalid values for
     * $form_state->getValue(array('foo', 'bar')) and everything within it will
     * be flagged and submission prevented.
     *
     * Partial form validation is implemented by suppressing errors rather than by
     * skipping the input processing and validation steps entirely, because some
     * forms have button-level submit handlers that call Drupal API functions that
     * assume that certain data exists within $form_state->getValues(), and while
     * not doing anything with that data that requires it to be valid, PHP errors
     * would be triggered if the input processing and validation steps were fully
     * skipped.
     *
     * @param string $name
     *   The name of the form element. If the #parents property of your form
     *   element is array('foo', 'bar', 'baz') then you may set an error on 'foo'
     *   or 'foo][bar][baz'. Setting an error on 'foo' sets an error for every
     *   element where the #parents array starts with 'foo'.
     * @param string $message
     *   (optional) The error message to present to the user.
     *
     * @return $this
     */
    public function setErrorByName($name, $message = '');

    /**
     * Flags an element as having an error.
     *
     * @param array $element
     *   The form element.
     * @param string $message
     *   (optional) The error message to present to the user.
     *
     * @return $this
     */
    public function setError(array &$element, $message = '');

    /**
     * Clears all errors against all form elements made by self::setErrorByName().
     */
    public function clearErrors();

    /**
     * Returns an associative array of all errors.
     *
     * @return array
     *   An array of all errors, keyed by the name of the form element.
     */
    public function getErrors();

    /**
     * Returns the error message filed against the given form element.
     *
     * Form errors higher up in the form structure override deeper errors as well
     * as errors on the element itself.
     *
     * @param array $element
     *   The form element to check for errors.
     *
     * @return string|null
     *   Either the error message for this element or null if there are no errors.
     */
    public function getError(array $element);

    /**
     * Sets the form to be rebuilt after processing.
     *
     * @param bool $rebuild
     *   (optional) Whether the form should be rebuilt or not. Defaults to true.
     *
     * @return $this
     */
    public function setRebuild($rebuild = true);

    /**
     * Determines if the form should be rebuilt after processing.
     *
     * @return bool
     *   true if the form should be rebuilt, false otherwise.
     */
    public function isRebuilding();

    /**
     * Converts support notations for a form callback to a valid callable.
     *
     * Specifically, supports methods on the form/callback object as strings when
     * they start with ::, for example "::submitForm()".
     *
     * @param string|array $callback
     *   The callback.
     *
     * @return array|string
     *   A valid callable.
     */
    public function prepareCallback($callback);

    /**
     * Returns the form object that is responsible for building this form.
     *
     * @return \Drupal\Core\Form\FormInterface
     *   The form object.
     */
    public function getFormObject();

    /**
     * Sets the form object that is responsible for building this form.
     *
     * @param \Drupal\Core\Form\FormInterface $form_object
     *   The form object.
     *
     * @return $this
     */
    public function setFormObject(FormInterface $form_object);

    /**
     * Sets this form to be cached.
     *
     * @param bool $cache
     *   true if the form should be cached, false otherwise.
     *
     * @return $this
     *
     * @throws \LogicException
     *   If the current request is using an HTTP method that must not change
     *   state (e.g., GET).
     */
    public function setCached($cache = true);

    /**
     * Determines if the form should be cached.
     *
     * @return bool
     *   true if the form should be cached, false otherwise.
     */
    public function isCached();

    /**
     * Prevents the form from being cached.
     *
     * @return $this
     */
    public function disableCache();

    /**
     * Sets the limited validation error sections.
     *
     * @param array|null $limit_validation_errors
     *   The limited validation error sections.
     *
     * @return $this
     *
     * @see \Drupal\Core\Form\FormState::$limit_validation_errors
     */
    public function setLimitValidationErrors($limit_validation_errors);

    /**
     * Retrieves the limited validation error sections.
     *
     * @return array|null
     *   The limited validation error sections.
     *
     * @see \Drupal\Core\Form\FormState::$limit_validation_errors
     */
    public function getLimitValidationErrors();

    /**
     * Sets the HTTP method to use for the form's submission.
     *
     * This is what the form's "method" attribute should be, not necessarily what
     * the current request's HTTP method is. For example, a form can have a
     * method attribute of POST, but the request that initially builds it uses
     * GET.
     *
     * @param string $method
     *   Either "GET" or "POST". Other HTTP methods are not valid form submission
     *   methods.
     *
     * @see \Drupal\Core\Form\FormState::$method
     * @see \Drupal\Core\Form\FormStateInterface::setRequestMethod()
     *
     * @return $this
     */
    public function setMethod($method);

    /**
     * Returns the HTTP form method.
     *
     * @param string
     *   The HTTP form method.
     *
     * @return bool
     *   true if the HTTP form method matches.
     *
     * @see \Drupal\Core\Form\FormState::$method
     */
    public function isMethodType($method_type);

    /**
     * Enforces that validation is run.
     *
     * @param bool $must_validate
     *   If true, validation will always be run.
     *
     * @return $this
     */
    public function setValidationEnforced($must_validate = true);

    /**
     * Checks if validation is enforced.
     *
     * @return bool
     *   If true, validation will always be run.
     */
    public function isValidationEnforced();

    /**
     * Prevents the form from redirecting.
     *
     * @param bool $no_redirect
     *   If true, the form will not redirect.
     *
     * @return $this
     */
    public function disableRedirect($no_redirect = true);

    /**
     * Determines if redirecting has been prevented.
     *
     * @return bool
     *   If true, the form will not redirect.
     */
    public function isRedirectDisabled();

    /**
     * Sets the submit handlers.
     *
     * @param array $submit_handlers
     *   An array of submit handlers.
     *
     * @return $this
     */
    public function setSubmitHandlers(array $submit_handlers);

    /**
     * Gets the submit handlers.
     *
     * @return array
     *   An array of submit handlers.
     */
    public function getSubmitHandlers();

    /**
     * Sets temporary data.
     *
     * @param array $temporary
     *   Temporary data accessible during the current page request only.
     *
     * @return $this
     */
    public function setTemporary(array $temporary);

    /**
     * Gets temporary data.
     *
     * @return array
     *   Temporary data accessible during the current page request only.
     */
    public function getTemporary();

    /**
     * Gets an arbitrary value from temporary storage.
     *
     * @param string|array $key
     *   Properties are often stored as multi-dimensional associative arrays. If
     *   $key is a string, it will return $temporary[$key]. If $key is an array,
     *   each element of the array will be used as a nested key. If
     *   $key = ['foo', 'bar'] it will return $temporary['foo']['bar'].
     *
     * @return mixed
     *   A reference to the value for that key, or null if the property does
     *   not exist.
     */
    public function &getTemporaryValue($key);

    /**
     * Sets an arbitrary value in temporary storage.
     *
     * @param string|array $key
     *   Properties are often stored as multi-dimensional associative arrays. If
     *   $key is a string, it will use $temporary[$key] = $value. If $key is an
     *   array, each element of the array will be used as a nested key. If
     *   $key = ['foo', 'bar'] it will use $temporary['foo']['bar'] = $value.
     * @param mixed $value
     *   The value to set.
     *
     * @return $this
     */
    public function setTemporaryValue($key, $value);

    /**
     * Determines if a temporary value is present.
     *
     * @param string $key
     *   Properties are often stored as multi-dimensional associative arrays. If
     *   $key is a string, it will return isset($temporary[$key]). If $key is an
     *   array, each element of the array will be used as a nested key. If
     *   $key = ['foo', 'bar'] it will return isset($temporary['foo']['bar']).
     */
    public function hasTemporaryValue($key);

    /**
     * Sets the form element that triggered submission.
     *
     * @param array|null $triggering_element
     *   The form element that triggered submission, of null if there is none.
     *
     * @return $this
     */
    public function setTriggeringElement($triggering_element);

    /**
     * Gets the form element that triggered submission.
     *
     * @return array|null
     *   The form element that triggered submission, of null if there is none.
     */
    public function &getTriggeringElement();

    /**
     * Sets the validate handlers.
     *
     * @param array $validate_handlers
     *   An array of validate handlers.
     *
     * @return $this
     */
    public function setValidateHandlers(array $validate_handlers);

    /**
     * Gets the validate handlers.
     *
     * @return array
     *   An array of validate handlers.
     */
    public function getValidateHandlers();
}
