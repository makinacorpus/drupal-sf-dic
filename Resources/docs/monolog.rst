Using Monolog in Drupal 7
=========================

.. warning::

   This feature is **experimental**, enable test it before running it in production!
   Notably, formatting is broken with dblog as of now.

This module brings integration for monolog into Drupal 7 with the condition that
you use Symfony's FrameworkBundle and MonologBundle, both being registered to
you container.

For a correct setup please follow the :ref:`bundles` documentation first.

There is two ways of it setting up:

*   either Drupal is master of logging, and monolog is just a bridge over the
    Drupal watchdog system;

*   either monolog is master of logging, and this module provides a
    ``hook_watchdog()`` implementation that fowards everything that Drupal
    catches to monolog.

You may use either one or the other, but you cannot use both at the same time.

Installing monolog
------------------
First install the Monolog bundle dependency:

.. code-block:: sh

   composer require symfony/monolog-bundle

Then add to your ``AppKernel.php`` file, in the ``registerBundles`` method:

.. code-block:: php
   
   <? php
   
   new \Symfony\Bundle\MonologBundle\MonologBundle()

Configuring Monolog
-------------------
As a bridge to Drupal watchdog
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Formatter and handler implementations are provided by this module. In order to
set it up, you only need to add to you ``config.yml`` file :

.. code-block:: yaml

   monolog:
       handlers:
           drupal:
               type: service
               id: drupal.monolog_handler
               level: warning

.. note ::
   Of course, you can still add as many handlers as you wish, but beware that
   since Drupal's watchdog *is not* bridged to Monolog, Drupal own's errors
   won't pass into those handlers.

As the master of things
^^^^^^^^^^^^^^^^^^^^^^^

.. warning::

   This is not yet implemented, will come soon!

Set into your Drupal's ``settings.php`` file :

.. code-block:: php

   <? php
   
   $conf['kernel.symfony_monolog_is_master'] = true;

This will enable this module's ``hook_watchdog()`` implementation that will
bridge all messages to the various Monolog handlers.

Please also ensure you disabled all other Drupal logging modules, for example
using Drush :

.. code-block:: sh

   drush -y dis dblog syslog

You may then proceed with advanced configuration.

Advanced configuration
``````````````````````
Here is a sample ``config.yml`` monolog section :

.. code-block:: yaml

   monolog:
       handler:
           # Per default send everything to the current environment file
           main:
               type:   stream
               path:   "%kernel.logs_dir%/%kernel.environment%.log"
               level:  debug

For a more advanced configuration, please refer to Symfony's manual :
`Logging with Monolog <https://symfony.com/doc/current/logging.html>`_.

