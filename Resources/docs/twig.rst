.. _twig:


Using Twig For Drupal 7
=======================
Installation
------------
To the twig engine, first install the dependencies with :

.. code-block:: sh

   composer require "symfony/twig-bundle:3.1.*"

.. note:: 

   If you want to use Twig, all the dependencies written above
   are mandatory, and you must use them in the specified versions.

Add ``engine = twig`` to your theme info file, rebuild your cache, and that's it.

.. note:: 

   If you plan to inherit from a non twig-theme, you may need the latest patch 
   from https://www.drupal.org/node/1545964

Usage
-----
Twig template naming convention
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
The only thing you have to know is that any module may provide ``.html.twig``
files, and the twig engine will automatically take over it. If you want to do
a more advanced twig usage, and benefit from all Twig advanced features, you
need to know that all the Drupal provided Twig templates will have the following
identifier :

    ``[theme|module]:NAME:PATH/TO/FILE.html.twig``

Other template usage within your twig templates
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
For example, let's say you have the ``tabouret`` module defining the

    ``tabouret/templates/chaises.html.twig``

the identifier would then be :

   ``module:tabouret:templates/chaise.html.twig``

If you want to write a twig file extending this one, you may add into your 
``.html.twig`` file :

.. code-block:: twig
   
   {% extends 'module:tabouret:templates/chaise.html.twig' %}

   My maginificient HTML code.

And you're good to do.

Twig files from bundles
^^^^^^^^^^^^^^^^^^^^^^^
You can use Twig files from bundles, you have to follow the Symgfony Twig usage
conventions and it'll work transparently.

Arbitrary render a template
^^^^^^^^^^^^^^^^^^^^^^^^^^^
You may just:

.. code-block:: php

   <? php
   
   return sf_dic_twig_render('module:tabouret:templates/chaise.html.twig', ['some' => $variable]);

And you're good to go.
