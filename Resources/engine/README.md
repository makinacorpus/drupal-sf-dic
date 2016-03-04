Here lies the twig theme engine for Drupal.

Sadly, Drupal relies on the file path, so need to copy the twig.engine as
your profile "themes/twig/twig.engine file"

Please note that if you want the auto render() feature or a few other helpers
that comes with Twig For Drupal 7 (tfd7) you need to add it to your composer.json
for it being properly autoloaded, this modules compiler passes will do the rest
and autoconfigure everything properly to use it.

