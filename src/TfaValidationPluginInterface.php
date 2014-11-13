<?php

/**
 * @file
 * Contains \Drupal\tfa\TfaValidationPluginInterface.
 */

namespace Drupal\tfa;

/**
 * Interface TfaValidationPluginInterface
 *
 * Validation plugins interact with the Tfa form processes to provide code entry
 * and validate submitted codes.
 */
interface TfaValidationPluginInterface {

  /**
   * Get TFA process form from plugin.
   *
   * @param array $form
   * @param array $form_state
   * @return array Form API array.
   */
  public function getForm(array $form, array &$form_state);

  /**
   * Validate form.
   *
   * @param array $form
   * @param array $form_state
   * @return bool Whether form passes validation or not
   */
  public function validateForm(array $form, array &$form_state);
}