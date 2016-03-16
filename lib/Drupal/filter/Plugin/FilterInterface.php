<?php

namespace Drupal\filter\Plugin;

use Drupal\Core\Form\FormStateInterface;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 *
 * In theory you would register those using plugins, but this is not going to
 * happen using this module, we won't bring annotations there. Neither we will
 * bring the Drupal plugins subsystems. We just needed the interface.
 */
interface FilterInterface
{
    /**
     * Generates a filter's settings form.
     *
     * @param array $form
     *   A minimally prepopulated form array.
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   The state of the (entire) configuration form.
     *
     * @return array
     *   The $form array with additional form elements for the settings of this
     *   filter. The submitted form values should match $this->settings.
     */
    public function settingsForm(array $form, FormStateInterface $form_state);

    /**
     * Prepares the text for processing.
     *
     * Filters should not use the prepare method for anything other than escaping,
     * because that would short-circuit the control the user has over the order in
     * which filters are applied.
     *
     * @param string $text
     *   The text string to be filtered.
     * @param string $langcode
     *   The language code of the text to be filtered.
     *
     * @return string
     *   The prepared, escaped text.
     */
    public function prepare($text, $langcode);

    /**
     * Performs the filter processing.
     *
     * @param string $text
     *   The text string to be filtered.
     * @param string $langcode
     *   The language code of the text to be filtered.
     *
     * @return \Drupal\filter\FilterProcessResult
     *   The filtered text, wrapped in a FilterProcessResult object, and possibly
     *   with associated assets, cacheability metadata and placeholders.
     *
     * @see \Drupal\filter\FilterProcessResult
     */
    public function process($text, $langcode);

    /**
     * Generates a filter's tip.
     *
     * A filter's tips should be informative and to the point. Short tips are
     * preferably one-liners.
     *
     * @param bool $long
     *   Whether this callback should return a short tip to display in a form
     *   (FALSE), or whether a more elaborate filter tips should be returned for
     *   template_preprocess_filter_tips() (TRUE).
     *
     * @return string|null
     *   Translated text to display as a tip, or NULL if this filter has no tip.
     *
     * @todo Split into getSummaryItem() and buildGuidelines().
     */
    public function tips($long = false);
}
