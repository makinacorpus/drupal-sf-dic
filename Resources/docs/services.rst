Services this Module Provides
=============================
.. note::

   This list is not complete, but is updated from time to 
   time, the best way to have a complete list is to read the services.yml file.

This modules provides a set of Drupal 8 API-compatible services that you should 
use in your own Drupal 7 module in order to reduce the future porting time to 
Drupal 8 :

 *  **service_container** : ``\Symfony\Component\DependencyInjection\ContainerInterface``
    instance that points to the current container itself

 *  **request_stack** : ``\Symfony\Component\HttpFoundation\RequestStack``
    instance, yes, a real one in your Drupal 7

 *  **database** : ``\DatabaseConnection`` instance that points to the Drupal
    default database
    
 *  **event_dispatcher** : ``Symfony\Component\EventDispatcher\EventDispatcher``
    which allows you to use it at your convenence; 
    
    .. note::
      please be aware that only the
      ``KernelEvents::TERMINATE`` event is run as of now (others are not
      revelant in a Drupal 7 site)

 *  **entity.manager** : ``\Drupal\Core\Entity\EntityManager`` passthru that
    only defines the ``getStorage($entity_type)`` method that will return
    ``\DrupalEntityControllerInterface`` instance

 *  You can fetch ``\Drupal\Core\Entity\EntityStorageInterface`` instances
    via the entity manager service, which are compatible with Drupal 8 interface

 *  **module_handler**:  ``\Drupal\Core\Extension\ModuleHandler`` passthru uses
    Drupal 7 module.inc functions

 *  **cache_factory** : ``\Drupal\Core\Cache\CacheFactory`` instance, working as
    expected

 *  **cache.NAME** (where *NAME* in *bootstrap*, *default*, *entity*, *menu*,
    *render*, *data*) : ``\Drupal\Core\Cache\CacheBackendInterface`` specific
    implementation that will proxify toward the Drupal 7 cache layer, fully
    API compatible with Drupal 8

 *  **logger.factory** : ``Drupal\Core\Logger\LoggerChannelFactory`` compatible
    service that will allow you to inject loggers into your services instead of
    using the ``watchdog`` function

 *  **logger.channel.NAME** (where *NAME* in *default*, *php*, *image*, *cron*,
    *file*, *form*) : ``Psr\Log\LoggerInterface`` instances, also happen
    to be ``Drupal\Core\Logger\LoggerChannelInterface`` implementations

 *  **form_builder** : ``Drupal\Core\Form\FormBuilder`` instance, with a single
    method implemented: ``getForm`` which allows you to spawn Drupal 8 style
    forms in your Drupal 7 site, the implementation is transparent

 *  **current_user** : ``Drupal\Core\Session\AccountProxy`` compatible with
    ``Drupal\Core\Session\AccountInterface`` which proxifies the current
    user, note that it also replaces the ``$GLOBALS['user']`` object

 *  **path.alias_manager** : ``Drupal\Core\Path\AliasManagerInterface`` that
    does pretty much the same thing as Drupal 7 does but keeping compatibility

 *  **path.alias_storage** : ``Drupal\Core\Path\AliasStorageInterface`` that
    does pretty much the same thing as Drupal 7 does but keeping compatibility

 *  **path.current** : ``Drupal\Core\Path\CurrentPathStack`` that you should
    always use as your object dependencies whenever you need the current path,
    instead of using ``current_path()`` or ``$_GET['q']``

 *  You should use ``\Drupal\Core\Session\AccountInterface`` whenever you
    need a user account which is not meant to be manipulated as an entity, for
    example for various access checks