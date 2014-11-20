<?php

/**
 * Login plugin test.
 *
 * @TfaLogin(
 *   id = "tfa_dummy_send",
 *   title = @Translation("Testing send plugin"),
 *   description = @Translation("Testing only")
 * )
 */

namespace Drupal\tfa\Plugin\TfaLogin;

use Drupal\tfa\TfaSendInterface;

/**
 * Class TfaDummySend
 */
class TfaDummySend implements TfaSendInterface {

}
