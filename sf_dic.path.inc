<?php
/**
 * @file
 * path.inc replacement for usage with the Drupal 8 API as much as possible.
 */

use Drupal\Core\CacheDecorator\CacheDecoratorInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * {@inheritdoc}
 *
 * COPY/PASTE OF CORE CODE.
 */
function drupal_path_initialize() {
  // Ensure $_GET['q'] is set before calling drupal_normal_path(), to support
  // path caching with hook_url_inbound_alter().
  if (empty($_GET['q'])) {
    $_GET['q'] = variable_get('site_frontpage', 'node');
  }
  $_GET['q'] = drupal_get_normal_path($_GET['q']);
}

/**
 * {@inheritdoc}
 */
function drupal_lookup_path($action, $path = '', $path_language = NULL) {
  switch ($action) {

    case 'wipe':
      \Drupal::service('path.alias_manager')->cacheClear();
      return false;

    case 'alias':
      return \Drupal::service('path.alias_manager')->getAliasByPath($path, $path_language);

    case 'source':
      return \Drupal::service('path.alias_manager')->getPathByAlias($path, $path_language);
  }
}

/**
 * {@inheritdoc}
 */
function drupal_cache_system_paths() {
  $aliasManager = \Drupal::service('path.alias_manager');
  if ($aliasManager instanceof CacheDecoratorInterface) {
    $aliasManager->writeCache();
  }
}

/**
 * {@inheritdoc}
 */
function drupal_get_path_alias($path = NULL, $path_language = NULL) {
  if ($path == NULL) {
    $path = $_GET['q'];
  }
  if (path_alias_is_blacklisted($path)) {
    return $path;
  }
  return \Drupal::service('path.alias_manager')->getAliasByPath($path, $path_language);
}

/**
 * {@inheritdoc}
 *
 * COPY/PASTE OF CORE CODE.
 */
function drupal_get_normal_path($path, $path_language = NULL) {
  $original_path = $path;

  if ($path == NULL) {
    $path = $_GET['q'];
  }
  $path = \Drupal::service('path.alias_manager')->getPathByAlias($path, $path_language);

  // Allow other modules to alter the inbound URL. We cannot use drupal_alter()
  // here because we need to run hook_url_inbound_alter() in the reverse order
  // of hook_url_outbound_alter().
  foreach (array_reverse(module_implements('url_inbound_alter')) as $module) {
    $function = $module . '_url_inbound_alter';
    $function($path, $original_path, $path_language);
  }

  return $path;
}

/**
 * {@inheritdoc}
 *
 * COPY/PASTE OF CORE CODE.
 */
function drupal_is_front_page() {
  // Use the advanced drupal_static() pattern, since this is called very often.
  static $drupal_static_fast;
  if (!isset($drupal_static_fast)) {
    $drupal_static_fast['is_front_page'] = &drupal_static(__FUNCTION__);
  }
  $is_front_page = &$drupal_static_fast['is_front_page'];

  if (!isset($is_front_page)) {
    // As drupal_path_initialize updates $_GET['q'] with the 'site_frontpage' path,
    // we can check it against the 'site_frontpage' variable.
    $is_front_page = ($_GET['q'] == variable_get('site_frontpage', 'node'));
  }

  return $is_front_page;
}

/**
 * {@inheritdoc}
 *
 * COPY/PASTE OF CORE CODE.
 */
function drupal_match_path($path, $patterns) {
  $regexps = &drupal_static(__FUNCTION__);

  if (!isset($regexps[$patterns])) {
    // Convert path settings to a regular expression.
    // Therefore replace newlines with a logical or, /* with asterisks and the <front> with the frontpage.
    $to_replace = array(
      '/(\r\n?|\n)/', // newlines
      '/\\\\\*/',     // asterisks
      '/(^|\|)\\\\<front\\\\>($|\|)/' // <front>
    );
    $replacements = array(
      '|',
      '.*',
      '\1' . preg_quote(variable_get('site_frontpage', 'node'), '/') . '\2'
    );
    $patterns_quoted = preg_quote($patterns, '/');
    $regexps[$patterns] = '/^(' . preg_replace($to_replace, $replacements, $patterns_quoted) . ')$/';
  }
  return (bool)preg_match($regexps[$patterns], $path);
}

/**
 * {@inheritdoc}
 *
 * COPY/PASTE OF CORE CODE.
 */
function current_path() {
  // We could use: \Drupal::service('path.current')->getPath();
  // but we won't because Drupal 7 gets nothing good to be managed via the
  // Symfony request, most modules will continue to use $_GET['q'] sadly.
  return $_GET['q'];
}

/**
 * {@inheritdoc}
 */
function drupal_path_alias_whitelist_rebuild($source = NULL) {
  $aliasManager = \Drupal::service('path.alias_manager');
  if (method_exists($aliasManager, 'whitelistRebuild')) {
    $aliasManager->whitelistRebuild();
  }
}

/**
 * {@inheritdoc}
 */
function path_load($conditions) {
  if (!is_array($conditions)) {
    $conditions = ['pid' => $conditions];
  }
  return \Drupal::service('path.alias_storage')->load($conditions);
}

/**
 * {@inheritdoc}
 */
function path_save(&$path, $clearCaches = true) {
  if (empty($path['language']) && isset($path['langcode'])) {
    $path['language'] = $path['langcode']; // Tiny bit of compat and flexibility.
  }
  \Drupal::service('path.alias_storage')->save(
    $path['source'],
    $path['alias'],
    isset($path['language']) ? $path['language'] : LanguageInterface::LANGCODE_NOT_SPECIFIED,
    isset($path['pid']) ? $path['pid'] : null
  );
  if ($clearCaches) {
    \Drupal::service('path.alias_manager')->cacheClear($path['source']);
  }
}

/**
 * {@inheritdoc}
 */
function path_delete($criteria) {
  if (!is_array($criteria)) {
    $criteria = ['pid' => $criteria];
  }
  \Drupal::service('path.alias_storage')->delete($criteria);
  if (isset($criteria['source'])) {
    \Drupal::service('path.alias_manager')->cacheClear($criteria['source']);
  } else {
    \Drupal::service('path.alias_manager')->cacheClear();
  }
}

/**
 * {@inheritdoc}
 *
 * COPY/PASTE OF CORE CODE.
 */
function path_is_admin($path) {
  $path_map = &drupal_static(__FUNCTION__);
  if (!isset($path_map['admin'][$path])) {
    $patterns = path_get_admin_paths();
    $path_map['admin'][$path] = drupal_match_path($path, $patterns['admin']);
    $path_map['non_admin'][$path] = drupal_match_path($path, $patterns['non_admin']);
  }
  return $path_map['admin'][$path] && !$path_map['non_admin'][$path];
}

/**
 * {@inheritdoc}
 *
 * COPY/PASTE OF CORE CODE.
 */
function path_get_admin_paths() {
  $patterns = &drupal_static(__FUNCTION__);
  if (!isset($patterns)) {
    $paths = module_invoke_all('admin_paths');
    drupal_alter('admin_paths', $paths);
    // Combine all admin paths into one array, and likewise for non-admin paths,
    // for easier handling.
    $patterns = array();
    $patterns['admin'] = array();
    $patterns['non_admin'] = array();
    foreach ($paths as $path => $enabled) {
      if ($enabled) {
        $patterns['admin'][] = $path;
      }
      else {
        $patterns['non_admin'][] = $path;
      }
    }
    $patterns['admin'] = implode("\n", $patterns['admin']);
    $patterns['non_admin'] = implode("\n", $patterns['non_admin']);
  }
  return $patterns;
}

/**
 * {@inheritdoc}
 *
 * COPY/PASTE OF CORE CODE.
 */
function drupal_valid_path($path, $dynamic_allowed = FALSE) {
  global $menu_admin;
  // We indicate that a menu administrator is running the menu access check.
  $menu_admin = TRUE;
  if ($path == '<front>' || url_is_external($path)) {
    $item = array('access' => TRUE);
  }
  elseif ($dynamic_allowed && preg_match('/\/\%/', $path)) {
    // Path is dynamic (ie 'user/%'), so check directly against menu_router table.
    if ($item = db_query("SELECT * FROM {menu_router} where path = :path", array(':path' => $path))->fetchAssoc()) {
      $item['link_path']  = $item['path'];
      $item['link_title'] = $item['title'];
      $item['external']   = FALSE;
      $item['options'] = '';
      _menu_link_translate($item);
    }
  }
  else {
    $item = menu_get_item($path);
  }
  $menu_admin = FALSE;
  return $item && $item['access'];
}

/**
 * {@inheritdoc}
 */
function drupal_clear_path_cache($source = NULL) {
  \Drupal::service('path.alias_manager')->cacheClear($source);
}
