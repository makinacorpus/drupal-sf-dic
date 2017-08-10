Getting started
===============

Installation
------------

Easy way : if your Drupal 7 project is composer based
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
This module works with composer, and should be installed using it, go in your 
project repository and just type the following line in your terminal :

.. code-block:: sh

    composer require makinacorpus/drupal-sf-dic

Please refer to this `Composer template for Drupal projects <https://github.com/drupal-composer/drupal-project/tree/7.x/>`_
to have a nice exemple for doing this.

Once Composer is installed and autoload dumped, you may add these lines to your
``settings.php`` :

.. code-block:: php

   <? php
   
   include_once DRUPAL_ROOT . '/../vendor/autoload.php';
   $conf['kernel.cache_dir'] = DRUPAL_ROOT . '/../cache/';
   $conf['kernel.logs_dir'] = DRUPAL_ROOT . '/../logs/';
   
Hard way : if your Drupal 7 project is not composer based
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
You may use the ``Composer Manager`` module although it's untested, or if it's 
not too late you probably should provide a global ``composer.json`` for your 
Drupal site.

Write your first forward-compatible Drupal 8 module
---------------------------------------------------

Step 1: Define this module as a dependency
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Any module relying on it should express it's dependency via the its info file:

.. code-block:: ini

   dependencies[] = sf_dic

You may also provide a valid ``composer.json`` (not required, but it would
be a good practice to provide one).

Step 2: Define your service
^^^^^^^^^^^^^^^^^^^^^^^^^^^
Then you just need to write a ``MYMODULE.services.yml`` file in your module
folder:

.. code-block:: yaml

   parameters:
       mymodule_some_param: 42
   
   services:
       mymodule_some_service:
           class: "\MyModule\Class"
           argument: ... # Anything that is Symfony compatible

Please refer to `Symfony's dependency injection container documentation <http://symfony.com/doc/3.0/components/dependency_injection>`_.

Step 3: Fetch your services via the Drupal 8 compatibility layer
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
.. note::

   The right way of doing this would be to never use the compatibility layer and
   introduce all your services via your services definition file.

Nevertheless, at some point, you will need to get a specific service in the
Drupal 7 oldschool legacy procedural code, in case you would just need to :

.. code-block:: php

   <? php
   
   function mymodule_do_something() {
     /** @var $myService \MyModule\SomeService */
     $myService = \Drupal::service('mymodule_some_service');
   }

The container itself is supposed to be kept hidden, but if you wish to fetch
at some point the container, you might do it this way :

.. code-block:: php

   <? php
   
   function mymodule_do_something() {
     // The Drupal 8 way.
     $container = \Drupal::getContainer();
   
     // A more generic way (choose either one, the one upper is prefered).
     /** @var $container \Symfony\Component\DependencyInjection\ContainerInterface */
     $container = \Drupal::service('service_container');
   
     // From this point, you might use some parameters given by the various modules
     // services definitions.
     $someValue = $container->getParameter('some_module.some_param');
   }

Step 4: Register compiler pass
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
I am sorry for this one, it'd need a little bit of magic to make it easy and
working at the same time, so here is the arbitrary choose way: In Drupal 8
you can define classes implementing the ``Drupal\Core\DependencyInjection\ServiceProviderInterface`` 
interface, which is also defined by this module.

But, because Drupal 7 is not Drupal 8, you will need to arbitrarily write a
class named ```Drupal\Module\MYMODULE\ServiceProvider``` which implements
this interface, and write it into the MYMODULE.container.php file.

For example, let's say your module name is ``kitten_killer``, you would write
the ``kitten_killer.container.php`` file containing the following code :

.. code-block:: php

   <?php
   
   // Note that the namespace here contains the lowercased Drupal internal
   // module name, if you don't, the container builder won't find it.
   namespace Drupal\Module\kitten_killer;
   
   use Drupal\Core\DependencyInjection\ServiceProviderInterface;
   
   use Symfony\Component\DependencyInjection\ContainerBuilder;
   
   // You MUST NOT change the class name.
   class ServiceProvider implements ServiceProviderInterface
   {
     /**
      * {@inheritdoc}
      */
     public function register(ContainerBuilder $container)
     {
       // From this point you can arbitrarily use the container the way you
       // wish and register anything you need.
       $container->addCompilerPass(new MyModule\DependencyInjection\SomeCompilerPass());
     }
   }


Working with event dispatcher
-----------------------------
Create an event subscriber implementing ``Symfony\Component\EventDispatcher\EventSubscriberInterface`` 
then register it in your ``my_module.services.yml`` file by adding it the
**event_subscriber** tag :

.. code-block:: yaml

   services:
     my_module_some_event:
       class: MyVendor\MyModule\EventSubscriber\SomeEventSubscriber
       tags: [{ name: event_subscriber }]


A few weird things this modules does you should be aware of
-----------------------------------------------------------
* Both ``\Drupal\node\NodeInterface`` and ``\Drupal\user\UserInterface`` are 
  implemented and automatically in use via the Drupal 7 entity controllers
  but you may also load them using entity storage services
  
*  ``path_inc`` variable is enforced and you cannot change it using your
   ``settings.php`` file, instead your module should override the
   **path.alias_storage** or **path.alias_manager** services

* Global ``$language`` variable is replaced by a
  ``\Drupal\Core\Language\LanguageInterface`` instance

*  All the Drupal variables are set as a container parameters, which mean that
   you can use all of them as services parameters. Please note that the side
   effect of this is that if you wish to change a variable and use the new
   value as a service parameter, you will need to rebuild the container.

And that's pretty much it.

Not all services can go in the container
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
There is no way to allow this module to get the enabled module list before the
``DRUPAL_BOOTSTRAP_CONFIGURATION`` phase (unless you are lucky and caches are
set) or before the ``DRUPAL_BOOTSTRAP_DATABASE`` phase (because Drupal 7
will need the database to get the module list). That's why the ``hook_boot()``
implementation in this module will remain. This means that there is absolutly no
way to allow cache backends services to be in the service container, sad, but
true story.

Long story short: any service you would want to involve in any pre-hook_boot()
running code cannot be set in the container.

Compiled container is a PHP file
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Which means that if you run Drupal with multiple web heads that don't share the
same filesystem, you might experience container desync problems on rebuild.
Future plans to solve this is to provide a cache based container such as Drupal
8 does.