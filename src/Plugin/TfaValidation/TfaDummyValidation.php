<?php

/**
 * Validation plugin test.
 *
 * @TfaValidation(
 *   id = "tfa_dummy_validation",
 *   title = @Translation("Testing validation plugin"),
 *   description = @Translation("Testing only")
 * )
 */

namespace Drupal\tfa\Plugin\TfaValidation;

use Drupal\tfa\TfaValidationInterface;

/**
 * Class TfaDummyValidation
 */
class TfaDummyValidation implements TfaValidationInterface {


	/**
	 * @copydoc TfaValidationInterface::getForm()
	 */
	public function getForm(array $form, array &$form_state) {
		$form['dummy'] = array(
			'#type' => 'markup',
			'#title' => t('You are using TFA Dummy.'),
		);
		return $form;
	}

	/**
	 * @copydoc TfaValidationInterface::getForm()
	 */
	public function validateForm(array $form, array &$form_state) {
		return TRUE;
	}
}
