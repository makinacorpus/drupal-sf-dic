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

### Hard way, if not

You may use the ```Composer Manager``` module although it's untested, or you
if it's not too late you probably should provide a global ```composer.json```
for your Drupal site.

Pease refer to [Composer template for Drupal projects](https://github.com/drupal-composer/drupal-project/tree/7.x)
to have a nice exemple for doing this.

## Usage

### Defining this module as a dependency

Any module relying on it should express it's dependency via the its info file:

```ini
dependencies[] = sf_dic
```

Please note that this is required, this information being cached by Drupal, it
allows this module to attempt finding ```MYMODULE.services.yml``` files
without parsing all existing modules in your Drupal instance.

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




