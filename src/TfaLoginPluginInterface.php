<?php

/**
 * @file
 * Contains \Drupal\tfa\TfaLoginPluginInterface.
 */

namespace Drupal\tfa;

/**
 * Interface TfaLoginPluginInterface
 *
 * Login plugins interact with the Tfa loginAllowed() process prior to starting
 * a TFA process.
 */
interface TfaLoginPluginInterface {

  /**
   * Whether authentication should be interrupted.
   *
   * @return bool
   */
  public function loginAllowed();
}
