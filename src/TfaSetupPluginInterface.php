<?php

/**
 * @file
 * Contains \Drupal\tfa\TfaSetupPluginInterface.
 */

namespace Drupal\tfa;

/**
 * Interface TfaSetupPluginInterface
 *
 * Setup plugins are used by TfaSetup for configuring a plugin.
 *
 * Implementations of a begin plugin should also be a validation plugin.
 */
interface TfaSetupPluginInterface {

  /**
   * @param array $form
   * @param array $form_state
   */
  public function getSetupForm(array $form, array &$form_state);

  /**
   * @param array $form
   * @param array $form_state
   */
  public function validateSetupForm(array $form, array &$form_state);

  /**
   * @param array $form
   * @param array $form_state
   * @return bool
   */
  public function submitSetupForm(array $form, array &$form_state);

}