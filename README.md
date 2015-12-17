# Drupal 7 Symfony - Dependency injection container

Brings the Symfony 3 dependency injection container to Drupal 7 along with
a limited Drupal 8 API compatibility layer.

## Installation

This module works with composer, and should be installed using it, add:

```json
    "require" : {
        "makinacorpus/drupal-sf-dic" : "*"
    }
```

to your project ```composer.json``` file or to your Drupal module that
should use it as a dependency.

## Usage

### Defining this module as a dependency

Any module relying on it should express it's dependency via the its info file:

```ini
dependencies[] = sf_dic
```

Please note that this is required, this information being cached by Drupal, it
allows this module to attempt finding ```services.yml``` files without parsing
all existing modules in your Drupal instance.

You may also provide a valid ```composer.json``` (not required, but it would
be a good practice to provide one).

### Defining your services

Then you just need to write a ```services.yml``` file in your module folder:

```yaml
services:
    my_module_service:
        class: "\MyModule\Class"
        argument: ... # Anything that is Symfony compatible
```

### Fetch your services via the Drupal 8 compatibility layer

@todo

