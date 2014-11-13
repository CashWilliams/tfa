<?php

/**
 * @file
 * Contains \Drupal\tfa\TfaSendPluginInterface.
 */

namespace Drupal\tfa;

/**
 * Interface TfaSendPluginInterface
 *
 * Send plugins interact with the Tfa begin() process to communicate a code
 * during the start of the TFA process.
 *
 * Implementations of a send plugin should also be a validation plugin.
 */
interface TfaSendPluginInterface {

  /**
   * TFA process begin.
   */
  public function begin();
}