# Drupal 7 - Symfony - Dependency injection

This project has been maturing enough to fill those needs:

 *  brings the Symfony 3 dependency injection container to Drupal 7 ;
 *  brings a Drupal 8 forward-compatibility layer for easier module porting ;
 *  brings the ability to use Symfony bundles into Drupal 7 as long as they
    use a limited set of Symfony features.

## Installation

### Easy way, if your Drupal 7 project is composer based

This module works with composer, and should be installed using it, add:

```json
    "require" : {
        "makinacorpus/drupal-sf-dic" : "*"
    }
```

to your project ```composer.json``` file or to your Drupal module that
should use it as a dependency.

Pease refer to [Composer template for Drupal projects](https://github.com/drupal-composer/drupal-project/tree/7.x)
to have a nice exemple for doing this.

### Hard way, if not

You may use the ```Composer Manager``` module although it's untested, or you
if it's not too late you probably should provide a global ```composer.json```
for your Drupal site.

## Usage

### As a forward-compatible Drupal 8 module

#### Defining this module as a dependency

Any module relying on it should express it's dependency via the its info file:

```ini
dependencies[] = sf_dic
```

You may also provide a valid ```composer.json``` (not required, but it would
be a good practice to provide one).

#### Defining your services

Then you just need to write a ```MYMODULE.services.yml``` file in your module
folder:

```yaml
parameters:
    mymodule_some_param: 42

services:
    mymodule_some_service:
        class: "\MyModule\Class"
        argument: ... # Anything that is Symfony compatible
```

Please refer to [Symfony's dependency injection container documentation](http://symfony.com/doc/3.0/components/dependency_injection/index.html).

#### Fetch your services via the Drupal 8 compatibility layer

The right way of doing it would be to never use the compatibility layer and
introduce all your services via your services definition file.

Nevertheless, at some point, you will need to get a specific service in the
Drupal 7 oldschool legacy procedural code, in case you would just need to:

```php
function mymodule_do_something() {
  /** @var $myService \MyModule\SomeService */
  $myService = \Drupal::service('mymodule_some_service');
}
```

The container itself is supposed to be kept hidden, but if you wish to fetch
at some point the container, you might do it this way:

```php
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
```

#### Register compiler pass

I am sorry for this one, it'd need a little bit of magic to make it easy and
working at the same time, so here is the arbitrary choose way: In Drupal 8
you can define classes implementing the
```Drupal\Core\DependencyInjection\ServiceProviderInterface``` interface, which
is also defined by this module.

But, because Drupal 7 is not Drupal 8, you will need to arbitrarily write a
class named ```Drupal\Module\MYMODULE\ServiceProvider``` which implements
this interface, and write it into the MYMODULE.container.php file.

For example, let's say your module name is ```kitten_killer```, you would write
the ```kitten_killer.container.php``` file containing the following code:

```php
<?php

// Note that the namespace here container the lowercased Drupal internal
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
```

#### Services this module provides

In order to provide a solid basis for working with Drupal 7 modules in order
to have them converted to Drupal 8 later, this modules provides a set of Drupal
8 API-compatible services that you should use in your own in order to reduce
the future porting time:

 *  **service_container**: ```\Symfony\Component\DependencyInjection\ContainerInterface```
    instance that points to the current container itself

 *  **request_stack**: ```\Symfony\Component\HttpFoundation\RequestStack```
    instance, yes, a real one in your Drupal 7

 *  **database**: ```\DatabaseConnection``` instance that points to the Drupal
    default database

 *  **event_dispatcher**: ```Symfony\Component\EventDispatcher\EventDispatcher```
    which allows you to use it at your convenence; please be aware that only the
    ```KernelEvents::TERMINATE``` event is run as of now (others are not
    revelant in a Drupal 7 site)

 *  **entity.manager**: ```\Drupal\Core\Entity\EntityManager``` passthru that
    only defines the ```getStorage($entity_type)``` method that will return
    ```\DrupalEntityControllerInterface``` instance

 *  You can fetch ```\Drupal\Core\Entity\EntityStorageInterface``` instances
    via the entity manager service, which are compatible with Drupal 8 interface

 *  **module_handler**: ```\Drupal\Core\Extension\ModuleHandler``` passthru uses
    Drupal 7 module.inc functions

 *  **cache_factory**: ```\Drupal\Core\Cache\CacheFactory``` instance, working as
    expected

 *  **cache.NAME** (where *NAME* in *bootstrap*, *default*, *entity*, *menu*,
    *render*, *data*) ```\Drupal\Core\Cache\CacheBackendInterface``` specific
    implementation that will proxify toward the Drupal 7 cache layer, fully
    API compatible with Drupal 8

 *  **logger.factory**: ```Drupal\Core\Logger\LoggerChannelFactory``` compatible
    service that will allow you to inject loggers into your services instead of
    using the ```watchdog``` function

 *  **logger.channel.NAME** (where *NAME* in *default*, *php*, *image*, *cron*,
    *file*, *form*): ```Psr\Log\LoggerInterface``` instances, also happen
    to be ```Drupal\Core\Logger\LoggerChannelInterface``` implementations

 *  **form_builder**: ```Drupal\Core\Form\FormBuilder``` instance, with a single
    method implemented: ```getForm``` which allows you to spawn Drupal 8 style
    forms in your Drupal 7 site, the implementation is transparent

 *  **current_user**: ```Drupal\Core\Session\AccountProxy``` compatible with
    ```Drupal\Core\Session\AccountInterface``` which proxifies the current
    user, note that it also replaces the ```$GLOBALS['user']``` object

 *  **path.alias_manager**: ```Drupal\Core\Path\AliasManagerInterface``` that
    does pretty much the same thing as Drupal 7 does but keeping compatibility

 *  **path.alias_storage**: ```Drupal\Core\Path\AliasStorageInterface``` that
    does pretty much the same thing as Drupal 7 does but keeping compatibility

 *  **path.current**: ```Drupal\Core\Path\CurrentPathStack``` that you should
    always use as your object dependencies whenever you need the current path,
    instead of using ```current_path()``` or ```$_GET['q']```

 *  You should use ```\Drupal\Core\Session\AccountInterface``` whenever you
    need a user account which is not meant to be manipulated as an entity, for
    example for various access checks

#### A few weird things this modules does you should be aware of

 *  Both ```\Drupal\node\NodeInterface``` and ```\Drupal\user\UserInterface```
    are implemented and automatically in use via the Drupal 7 entity controllers
    but you may also load them using entity storage services

 *  ```path_inc``` variable is enforced and you cannot change it using your
    ```settings.php``` file, instead your module should override the
    **path.alias_storage** or **path.alias_manager** services

 *  The path alias whitelist performance hack for both Drupal 7 and 8 has been
    hardened and excludes per default every path that is an admin path, if you
    don't like it, you probably then should override the **path.alias_manager**
    service

 *  global ```$language``` variable is replaced by a
    ```\Drupal\Core\Language\LanguageInterface``` instance

 *  All the Drupal variables are set as a container parameters, which mean that
    you can use all of them as services parameters. Please note that the side
    effect of this is that if you wish to change a variable and use the new
    value as a service parameter, you will need to rebuild the container.

#### Working with forms

##### Defining your form

In order to be able to use Drupal 8 style forms, you may spawn them with 2
different methods. First you should define a form implementing
```FormInterface``` or extending ```FormBase```.

Please note that it is API compatible with Drupal 8 so you should read the
Drupal 8 documentation. Please notice there are a few missing methods a few
differences when dealing with entites and URLs, since Drupal 8 does not handle
those the same way as Drupal 7.

```php

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

```

##### Using the form builder

In any kind of code returning a render array, directly call:

```php
function my_module_some_page() {
  $build = [];

  $build['form'] = \Drupal::formBuilder()->getForm('\\MyVendor\\MyModule');

  return $build;
}
```

##### Using your forms in menu

Because we had to hack a bit the way Drupal spawn this forms (don't worry they
still are 100% Drupal working forms) if you use the hook menu you must replace
the ```drupal_get_form``` page callback with ```sf_dic_page_form``` in
order for it to work, and that's pretty much it:

```php
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
```

### As a Symfony bundle user

#### What can I do ?

This module can natively use Symfony bundles into the Drupal application, but
you must ackowledge the fact that you cannot use the whole Symfony API:

 *  you may use ```*Bundle``` class, the ```Extension``` class, and provide
    services via the ```Resource/config``` folder;

 *  you may use Twig natively (the ```TwigBundle``` is automatically registered
    in the kernel if the classes are found) where template naming convention
    are the Symfony naming conventions;

 *  this module provide an abstract controller class
    ```MakinaCorpus\Drupal\Sf\Controller``` that you may extend in order to
    provide controllers, you must then now that your controllers won't behave
    like symfony components, but there return will only be used to fill in the
    page ```content``` Drupal region;

 *  you may use anything you want as long as you set it as composer dependencies
    case in which you might want to see the next chapter. Please note that it
    will only work to the packages you pulled extend.

#### Bring the necessary dependencies

You must require a few packages for this to work, if you want twig you must:
```sh
composer require symfony/templating
composer require symfony/twig-bundle
```

You probably also want to add the ```twig/extensions``` package.

Alternatively, if you are not afraid and have beard, you could just:

```sh
composer require symfony/symfony
```

Which should work gracefully, note that we are *not* using the Symfony full
stack so most of its code won't be in use.

#### Register one or more bundles

Bundle registration must happen before the ```sf_dic``` module ```hook_boot()```
implementation, this means that you have only one place where you can do it:
by implementing your own ```hook_boot()``` implementation and setting the
weight of your module under the ```sf_dic``` module's weight.

Once you did that, and I know will know how to do it, you may register your
bundles this way:

```php
/**
 * Implements hook_boot().
 */
function MYMODULE_boot() {
  \Drupal::registerBundles([
    new MyVendor\SomeBundle\MyVendorSomeBundle(),
    // [...]
  ]);
}
```

And that's it!

## Use Twig For Drupal 7

### Installation

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

### Usage

#### Twig template naming convention

The only thing you have to know is that any module may provide ```.html.twig```
files, and the twig engine will automatically take over it. If you want to do
a more advanced twig usage, and benefit from all Twig advanced features, you
need to know that all the Drupal provided Twig templates will have the following
identifier: ```[theme|module]:NAME:PATH/TO/FILE.html.twig```

#### Other template usage within your twig templates

For example, let's say you have the ```tabouret``` module defining the
```tabouret/templates/chaises.html.twig```, the identifier would then
be:

```module:tabouret:templates/chaise.html.twig```

If you want to write a twig file extending this one, you may add into your
```.html.twig``` file:

```twig
{% extends 'module:tabouret:templates/chaise.html.twig' %}

My maginificient HTML code.
```

And you're good to do.

#### Twig files from bundles

You can use Twig files from bundles, you have to follow the Symgfony Twig usage
conventions and it'll work transparently.

#### Arbitrary render a template

You may just:

```php
return sf_dic_twig_render('module:tabouret:templates/chaise.html.twig', ['some' => $variable]);
```

And you are good to go.

## Working with event dispatcher

Create an event subscribe implementing ```Symfony\Component\EventDispatcher\EventSubscriberInterface```
then register it in your **my_module.services.yml** file by adding it the
```event_subscriber``` tag:

```yaml
services:
  my_module_some_event:
    class: MyVendor\MyModule\EventSubscriber\SomeEventSubscriber
    tags: [{ name: event_subscriber }]
```

And that's pretty much it.

### Not all services can go in the container

There is no way to allow this module to get the enabled module list before the
```DRUPAL_BOOTSTRAP_CONFIGURATION``` phase (if you are lucky and caches are
set) or before the ```DRUPAL_BOOTSTRAP_DATABASE``` phase (because Drupal 7
will need the database to get the module list). That's why the ```hook_boot()```
implementation in this module will remain. This means that there is absolutly no
way to allow cache backends services to be in the service container, sad, but
true story.

Long story short: any service you would want to involve in any pre-hook_boot()
running code cannot be set in the container.

### Compiled container is a PHP file

Which means that if you run Drupal with multiple web heads that don't share the
same filesystem, you might experience container desync problems on rebuild.
Future plans to solve this is to provide a cache based container such as Drupal
8 does.
