<?php
/*
 * Original code this was forked from TFD7 project:
 *   https://github.com/TFD7/TFD7
 *
 * All credits to its authors.
 *
 * @author René Bakx
 * @see http://tfd7.rocks for more information
 */

/**
 * Wrapper around the default drupal render function.
 * This function is a bit smarter, as twig passes a NULL if the item you want to
 * be rendered is not found in the $context (aka template variables!)
 *
 * @param $var array item from the render array of doom item you wish to be rendered.
 * @return string
 */
function tfd_render($var) {
  if (is_array($var)) {
    return render($var);
  }
  return $var;
}

/**
 * Wrapper around the default drupal hide function.
 *
 * This function is a bit smarter, as twig passes a NULL if the item you want to
 * be hiden is not found in the $context (aka template variables!)
 *
 * @param $var array item from the render array of doom item you wish to hide.
 * @return mixed
 */
function tfd_hide($var) {
  if (!is_null($var) && !is_scalar($var) && count($var) > 0) {
    hide($var);
  }
}

/**
 * Additional Twig filter provided in Drupal 8 to render array ommitting
 * certain elements in the array
 *
 * example {{ content|without(['links','language']) }}
 *
 * @param $input array
 * @param $keys_to_remove array
 * @return array
 */
function tfd_without($input, $keys_to_remove) {
  if ($input instanceof \ArrayAccess) {
    $filtered = clone $input;
  }
  else {
    $filtered = $input;
  }
  foreach ($keys_to_remove as $key) {
    if (isset($filtered[$key])) {
      unset($filtered[$key]);
    }
  }
  return $filtered;
}

function tfd_defaults_filter($value, $defaults = NULL) {
  $args = func_get_args();
  $args = array_filter($args);
  if (count($args)) {
    return array_shift($args);
  }
  else {
    return NULL;
  }
}

/**
 * Wraps the given text with a HTML tag
 * @param $value
 * @param $tag
 * @return string
 */
function tfd_wrap_text($value, $tag) {
  $value = tfd_render($value);
  if (!empty($value)) {
    return sprintf('<%s>%s</%s>', $tag, trim($value), $tag);
  }
}

function tfd_form_get_errors() {
  $errors = form_get_errors();
  if (!empty($errors)) {
    $newErrors = array();
    foreach ($errors as $key => $error) {
      $newKey = str_replace('submitted][', 'submitted[', $key);
      if ($newKey !== $key) {
        $newKey = $newKey . ']';
      }
      $newErrors[$newKey] = $error;
    }
    $errors = $newErrors;
  }
  return $errors;
}

function tfd_image_url($filepath, $preset = NULL) {
  if (is_array($filepath)) {
    $filepath = $filepath['filepath'];
  }
  if ($preset) {
    return image_style_url($preset, $filepath);
  }
  else {
    return $filepath;
  }
}


function tfd_image_size($filepath, $preset, $asHtml = TRUE) {
  if (is_array($filepath)) {
    $filepath = $filepath['filepath'];
  }
  $info = image_get_info(image_style_url($preset, $filepath));
  $attr = array(
    'width' => (string) $info['width'],
    'height' => (string) $info['height']
  );
  if ($asHtml) {
    return drupal_attributes($attr);
  }
  else {
    return $attr;
  }
}


function tfd_url($item, $options = array()) {
  if (is_numeric($item)) {
    $ret = url('node/' . $item, (array) $options);
  }
  else {
    $parsed = drupal_parse_url($item);
    $options += $parsed;
    $ret = url($parsed['path'], (array) $options);
  }
  return check_url($ret);
}

/**
 *
 * @param $value
 * @param int $length
 * @param bool $elipse
 * @param bool $words
 * @return string
 */
function tfd_truncate_text($value, $length = 300, $elipse = TRUE, $words = TRUE) {
  $value = tfd_render($value);
  if (drupal_strlen($value) > $length) {
    $value = drupal_substr($value, 0, $length);
    if ($words) {
      $regex = "(.*)\b.+";
      if (function_exists('mb_ereg')) {
        mb_regex_encoding('UTF-8');
        $matches = [];
        $found = mb_ereg($regex, $value, $matches);
      }
      else {
        $matches = [];
        $found = preg_match("/$regex/us", $value, $matches);
      }
      if ($found) {
        $value = $matches[1];
      }
    }
    // Remove scraps of HTML entities from the end of a strings
    $value = rtrim(preg_replace('/(?:<(?!.+>)|&(?!.+;)).*$/us', '', $value));
    if ($elipse) {
      $value .= ' ' . t('...');
    }
  }
  return $value;
}

function tfd_machine_name($string) {
  return preg_replace(array('/[^a-z0-9]/', '/_+/'), '_', strtolower($string));
}

/**
 * Return the children of an element
 *
 * @param $render_array
 * @return array
 */
function tfd_get_children($render_array) {
  if (!empty($render_array) && is_array($render_array)) {
    $children = array();
    foreach (element_children($render_array) as $key) {
      $children[] = $render_array[$key];
    }
    return $children;
  }
  return array();
}
