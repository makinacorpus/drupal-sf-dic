HTTP Cache handling
===================

This module will convert all Drupal responses into Symfony responses and deal
with delivery itself whenever possible, this allows to share common code to
deliver both Symfony and Drupal responses.

If you with to both use Drupal page cache and use ``Cache-Control`` and
``Vary`` HTTP headers in your Symfony response, we do heavily advice that
you do not let Drupal handle the ``Vary`` cookie by itself.

This means that, in order to ensure no side effects, add
those lines into your ``settings.php`` file:

.. code-block:: php

   <? php

   $conf['omit_vary_cookie'] = true;

Please note that pages cached when using this module will not use the gzip
feature.

This module will automatically restore the ``Vary: Cookie`` header by merging
it to Symfony responses, if you wish to completly disable it, set this variable:

.. code-block:: php

   <? php

   $conf['kernel.set_vary_cookie'] = false;

