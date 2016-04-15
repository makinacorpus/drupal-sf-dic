# Use Twig For Drupal 7

## Installation

You may use [TFD7](http://tfd7.rocks/) for theming, but if you use this module,
you should not use their provided Drupal engine.

First, add the tfd7 dependency into your composer.json file, add the custom
repository toward their git, this way:

```json
{
    ...
    "repositories": [
        {
            "type" : "vcs",
            "url" : "git@github.com:tfd7/tfd7.git"
        }
    ],
    "require" : {
        ....
        "symfony/dependency-injection" : "~3.0",
        "symfony/templating" : "~3.0",
        "symfony/twig-bridge" : "~3.0",
        "symfony/twig-bundle" : "~3.0",
        "tfd7/tfd7": "dev-master",
        "twig/extensions": "~1.3",
        "twig/twig": "~1.20|~2.0"
    }
}
```

Please note that if you want to use Twig, all the dependencies written above
are mandatory, and you must use them in the specified versions. Once you
upgraded your composer installation, copy the
```Resources/engine/twig.engine``` file (within this module) into either one
of the ```profiles/MYPROFILE/themes/engines/twig/twig.engine``` or
```themes/engines/twig/twig.engine``` locations.

Rebuild your cache, and that's it.

## Usage

### Twig template naming convention

The only thing you have to know is that any module may provide ```.html.twig```
files, and the twig engine will automatically take over it. If you want to do
a more advanced twig usage, and benefit from all Twig advanced features, you
need to know that all the Drupal provided Twig templates will have the following
identifier:

    [theme|module]:NAME:PATH/TO/FILE.html.twig

### Other template usage within your twig templates

For example, let's say you have the ```tabouret``` module defining the

    tabouret/templates/chaises.html.twig

the identifier would then be:

module:tabouret:templates/chaise.html.twig

If you want to write a twig file extending this one, you may add into your ```.html.twig``` file:

```twig
{% extends 'module:tabouret:templates/chaise.html.twig' %}

My maginificient HTML code.
```

And you're good to do.

### Twig files from bundles

You can use Twig files from bundles, you have to follow the Symgfony Twig usage
conventions and it'll work transparently.

### Arbitrary render a template

You may just:

```php
return sf_dic_twig_render('module:tabouret:templates/chaise.html.twig', ['some' => $variable]);
```

And you are good to go.
