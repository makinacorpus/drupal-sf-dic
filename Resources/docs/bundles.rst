.. _bundles:

Bringing Symfony 3 Fullstack and Bundles into Drupal 7
======================================================
This module can natively use Symfony bundles into the Drupal application, but
you must ackowledge the fact that you cannot use the whole Symfony API :

 *  you may use ``*Bundle`` class, the ``Extension`` class, and provide
    services via the ``Resource/config`` folder;

 *  you may use Twig natively (the ``TwigBundle`` is automatically registered
    in the kernel if the classes are found) where template naming convention
    are the Symfony naming conventions;

 *  this module provide an abstract controller class
    ``MakinaCorpus\Drupal\Sf\Controller`` that you may extend in order to
    provide controllers, you must then now that your controllers won't behave
    like symfony components, but there return will only be used to fill in the
    page ``content`` Drupal region;

 *  use the router component, as any Symfony application, but you must know that
    controllers will be executed by a specific Drupal menu router callback instead
    of being run by the HttpKernel. Router usage is disabled per default;

 *  you may use anything you want as long as you set it as composer dependencies
    case in which you might want to see the next chapter. Please note that it
    will only work to the packages you pulled extend.

Installation
------------
.. note::

   Bringing Symfony 3 Fullstack into Drupal 7 can only work with the Drupal 
   Clean URL function enabled.
   
Bringing in required dependencies
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
You must require a few packages for this to work, if you want twig you must :

.. code-block:: sh

   composer require symfony/templating
   composer require symfony/twig-bundle

You probably also want to add the ``twig/extensions`` package.

Alternatively, if you are not afraid and have beard, you could just:

.. code-block:: sh

   composer require symfony/symfony

Which should work gracefully, note that we are *not* using the Symfony full
stack so most of its code won't be in use.


Enable the fullstack framework usage
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You must use the ``symfony/symfony`` package as a dependency, then add
the following variables to your ``settings.php`` file :

.. code-block:: php
   
   <? php
   
   $conf['kernel.symfony_all_the_way'] = true;

Configuration
-------------

Configuration directory structure
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Symfony will need a kernel root directory for loading its configuration and
default resources, the commonly seen ``app`` folder in Symfony applications.

Directory structure when using this module is exactly the same, if the ``app``
directory exists as follows :

.. code-block:: php
   
   <? php
   
   /path/to/project/www/index.php # Where Drupal lives
   /path/to/project/app

You will then find the following directory structure :

*   ``app/config/config.yml`` where your configuration is stored;
*   ``app/config/config_ENVIRONMENT.yml`` in opposition to a common Symfony
    application, these files are optional and will fallback on the ``config.yml``
    file, but you may use them;
*   ``app/config/parameters.yml`` where your parameters are stored;
*   ``app/routing.yml`` where the routing happens;
*   ``app/Resources/`` where you may put additional resources, such as Twig
    template overrides.

In case the ``app`` directory does not exists, the module will fallback onto
the following structure :

*   ``www/sites/default/config/config.yml``;
*   ``www/sites/default/config/config_ENVIRONMENT.yml``;
*   ``www/sites/default/config/parameters.yml``;
*   ``www/sites/default/routing.yml``;
*   ``www/sites/default/Resources/``.

Please also note that if you don't provide any ``config.yml`` file, the module
will automatically fallback on its own implementation, you might find in :
``drupal-sf-dic/Resources/config/config.yml``.


Overriding configuration
^^^^^^^^^^^^^^^^^^^^^^^^
In order to override the configuration and provide your own, you must copy
the following files into the previously mentionned ``config/`` directory :

*   ``drupal-sf-dic/Resources/config/config.yml``;
*   ``drupal-sf-dic/Resources/config/parameters.yml``.

.. _bundles_kernel:

Working with bundles
--------------------
You may, as any Symfony application, provider your own kernel implementation,
for this, copy the `sample/AppKernel.php <https://github.com/makinacorpus/drupal-sf-dic/blob/master/Resources/docs/sample/AppKernel.php>`_ file and set
your own bundles.

For it to work, you need the ``AppKernel.php`` file to be automatically loaded, 
for this use composer. Let's consider you placed the file at this location : 
``app/AppKernel.php``, you may add the following into your ``composer.json`` 
file :

.. code-block:: json

   {
       "autoload" : {
           "files" : [
               "app/AppKernel.php"
           ]
       }
   }

And then :

.. code-block:: sh

   composer dump-autoload

Other considerations
--------------------

Using Symfony for 403 and 404 pages
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
You may use Symfony for your basic error pages, yet Drupal cannot catch
exceptions without modifying its source code, we still can catch 403 and
404 errors using the Drupal configuration.

For this, you need to go Symfony all the way as described above, then add
the following variables into your ``settings.php`` file :

.. code-block:: php
   
   <? php
   
   $conf['site_403'] = 'symfony/access-denied';
   $conf['site_404'] = 'symfony/not-found';

Using the router
^^^^^^^^^^^^^^^^
You can use the Symfony router, and build 100% Symfony compatible code, please see
https://symfony.com/doc/current/book/routing.html


Register your bundle's services.yml file
````````````````````````````````````````
You must first tell this module you will use the Symfony router by adding the
following variable to your ``settings.php`` file :

.. code-block:: php
   
   <? php
   
   $conf['kernel.symfony_router_enable'] = true;

Then add a ``sites/default/routing.yml`` file, containing :

.. code-block:: yaml

   my_bundle:
       resource: "@MyVendorMyBundle/Resources/config/routing.yml"
       prefix: /
