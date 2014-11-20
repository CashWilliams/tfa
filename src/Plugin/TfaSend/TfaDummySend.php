<?php

/**
 * Send plugin test.
 *
 * @TfaSend(
 *   id = "tfa_dummy_send",
 *   title = @Translation("Testing send plugin"),
 *   description = @Translation("Testing only")
 * )
 */

namespace Drupal\tfa\Plugin\TfaSend;

use Drupal\tfa\TfaSendInterface;

/**
 * Class TfaDummySend
 */
class TfaDummySend implements TfaSendInterface {
	//TODO - Fill out params/return variables
	/**
	 * TFA process begin.
	 */
	public function begin(){
		return TRUE;
	}
}
