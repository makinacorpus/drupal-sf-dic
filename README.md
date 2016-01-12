# Drupal 7 - Symfony - Dependency injection

Brings the Symfony 3 dependency injection container to Drupal 7 along with
a limited Drupal 8 API compatibility layer.

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

### Defining this module as a dependency

Any module relying on it should express it's dependency via the its info file:

```ini
dependencies[] = sf_dic
```

You may also provide a valid ```composer.json``` (not required, but it would
be a good practice to provide one).

### Defining your services

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

### Fetch your services via the Drupal 8 compatibility layer

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

### Register compiler pass

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

## Services this module provides

 *  **service_container**: ```\Symfony\Component\DependencyInjection\ContainerInterface```
    instance that points to the current container itself

 *  **database**: ```\DatabaseConnection``` instance that points to the Drupal
    default database

 *  **entity.manager**: ```\Drupal\Core\Entity\EntityManager``` passthrough that
    only defines the ```getStorage($entity_type)``` method that will return
    ```\DrupalEntityControllerInterface``` instance

 *  **logger.factory**: ```Drupal\Core\Logger\LoggerChannelFactory``` compatible
    service that will allow you to inject loggers into your services instead of
    using the ```watchdog``` function

 *  **logger.channel.NAME** (where *NAME* in *default*, *php*, *image*, *cron*,
    *file*, *form*): ```Psr\Log\LoggerInterface``` instances, also happen
    to be ```Drupal\Core\Logger\LoggerChannelInterface``` implementations

 *  **form_builder**: ```Drupal\Core\Form\FormBuilder``` instance, with a single
    method implemented: ```getForm``` which allows you to spawn Drupal 8 style
    forms in your Drupal 7 site, the implementation is transparent

 *  All the Drupal variables are set as a container parameters, which mean that
    you can use all of them as services parameters. Please note that the side
    effect of this is that if you wish to change a variable and use the new
    value as a service parameter, you will need to rebuild the container.

## Working with forms

### Defining your form

In order to be able to use Drupal 8 style forms, you may spawn them with 2
different methods. First you should define a form implementing
```FormInterface``` or extending ```FormBase```:

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

### Using the form builder

In any kind of code returning a render array, directly call:

```php
function my_module_some_page() {
  $build = [];

  $build['form'] = \Drupal::formBuilder()->getForm('\\MyVendor\\MyModule');

  return $build;
}
```

### Using your forms in menu

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
    'page arguments'    => ['MakinaCorpus\Drupal\Sf\Container\Tests\Mockup\FormImplements', "42"],
    'access callback'   => true,
    'type'              => MENU_CALLBACK,
  ];

  // ...

  return $items;
}
```

## Known issues

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
