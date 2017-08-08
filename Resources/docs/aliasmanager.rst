Using Drupal 8 Path Alias Manager in Drupal 7
=============================================
This module provides an API-compatible with strictly the same behaviour as
the Drupal 8 component with the same name.

It overrides the runtime to provides its own ``path.inc`` include, which means
that you cannot override it anymore. **Intead, you MUST override the ``AliasManager``
or the ``AliasStorageInterface`` components** using the dependency injection
container.

Additional features
-------------------

Path alias blacklist
^^^^^^^^^^^^^^^^^^^^
You can provide an alias blacklist, which means that no aliases will ever be
displayed for entries that match this blacklist. For convenience reasons,
existing aliases will still match the original path source, but displaying
links using those sources will never use the stored aliases.

In order to use this, set the ``path_alias_blacklist`` variable, which can
be either:

 *  a string with path that may contain wildcards (eg. ``user/*``) each string
    must be separated by a newline character \n;

 *  an array of strings, each string must be a path that may contain wildcards
    as well.

Internally it uses the ``drupal_match_path()`` function for matching the
blacklist.
