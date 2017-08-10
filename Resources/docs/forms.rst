Using Drupal 8 Forms in Drupal 7
================================
Defining your form
------------------
In order to be able to use Drupal 8 style forms, you may spawn them with 2
different methods. First you should define a form implementing
``FormInterface`` or extending ``FormBase``.

.. note::

   This API is compatible with Drupal 8 so you should read the `Drupal 8 
   documentation <https://www.drupal.org/docs/8>`_. 
   
   Please notice there are a few missing methods a few differences when dealing 
   with entites and URLs, since Drupal 8 does not handle those the same way as 
   Drupal 7.

.. code-block:: php

   <?php
   
   namespace MyVendor\MyModule;
   
   use Drupal\Core\Form\FormBase;
   use Drupal\Core\Form\FormStateInterface;
   
   class MyForm extends FormBase
   {
     public function buildForm($form, FormStateInterface $form_state)
     {
       // build a form API array, classical then
       return $form;
     }
   
     public function submitForm(&$form, FormStateInterface $form_state)
     {
       // do something...
     }
   }

Using the form builder
----------------------

In any kind of code returning a render array, directly call :

.. code-block:: php

   <?php
   
   function my_module_some_page() {
     $build = [];
   
     $build['form'] = \Drupal::formBuilder()->getForm('\\MyVendor\\MyModule');
   
     return $build;
   }

Using your forms in menu
------------------------

Because we had to hack a bit the way Drupal spawn this forms (don't worry they
still are 100% Drupal working forms) if you use the hook menu you must replace
the ``drupal_get_form`` page callback with ``sf_dic_page_form`` in
order for it to work, and that's pretty much it :

.. code-block:: php

   <?php
   
   /**
    * Implements hook_menu().
    */
   function sf_dic_test_menu() {
     $items = [];
   
     $items['test/form/implements'] = [
       'page callback'     => 'sf_dic_page_form',
       'page arguments'    => ['MakinaCorpus\Drupal\Sf\Tests\Mockup\FormImplements', "42"],
       'access callback'   => true,
       'type'              => MENU_CALLBACK,
     ];
   
     // ...
   
     return $items;
   }