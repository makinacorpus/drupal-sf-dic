Using the Symfony Web Debug Toolbar in Drupal 7
===============================================
.. warning::

   Please consider the fact that this remains **an experimental feature** and it 
   might interfer with some Drupal AJAX queries.

If you use the Symfony full stack, and registered the framework bundle, you
can now, if you wish, make use of the Symfony profiler and web debug toolbar
in Drupal.

For this, start with :

* :ref:`bundles` and set-up both Symfony full-stack framework and the router
* :ref:`twig`

Enable the debug and web profiler bundles
-----------------------------------------
You need to provide your own ``AppKernel`` implementation via the
``app/AppKernel.php`` file.
See :ref:`bundles_kernel` for more details about how to provide your own custom kernel.

Once you have your own kernel, add this into the ``registerBundles()`` method:

.. code-block:: php

   <? php
   
   public function registerBundles()
       {
           $bundles = [
               new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
               // ...
   
           ];
   
           if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
               $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
               $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
           }
   
           return $bundles;
   }

Add the necessary configuration
-------------------------------
In the ``app/config_dev.yml`` file:

.. code-block:: yml

   framework:
       router:
           resource: "%kernel.root_dir%/config/routing_dev.yml"
           strict_requirements: true
           profiler: { only_exceptions: false }
   
   web_profiler:
       toolbar: true
       intercept_redirects: false

In the ``app/routing_dev.yml`` file:

.. code-block:: yml

   _wdt:
       resource: "@WebProfilerBundle/Resources/config/routing/wdt.xml"
       prefix:   /_wdt
   
   _profiler:
       resource: "@WebProfilerBundle/Resources/config/routing/profiler.xml"
       prefix:   /_profiler
   
   _errors:
       resource: "@TwigBundle/Resources/config/routing/errors.xml"
       prefix:   /_error

   _main:
       resource:   routing.yml

Of course, you need to create those files, or merge them accordingly to what
already exists into.

Next step: have fun!
--------------------
It should be enough for it to work. 
