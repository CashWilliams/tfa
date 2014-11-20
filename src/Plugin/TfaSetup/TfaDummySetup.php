<?php

/**
 * Setup plugin test.
 *
 * @TfaSetup(
 *   id = "tfa_dummy_setup",
 *   title = @Translation("Testing setup plugin"),
 *   description = @Translation("Testing only")
 * )
 */

namespace Drupal\tfa\Plugin\TfaSetup;

use Drupal\tfa\TfaSetupInterface;

/**
 * Class TfaDummySetup
 */
class TfaDummySetup implements TfaSetupInterface {
	//TODO - Eval return params or form pass by ref
	/**
	 * @param array $form
	 * @param array $form_state
	 */
	public function getSetupForm(array $form, array &$form_state){
		$form['status'] = array(
			'#type' => 'item',
			'#markup' => '<p>TFA Dummy Setup</p>'
		);
	}

	//TODO - Eval return params or form pass by ref
	/**
	 * @param array $form
	 * @param array $form_state
	 */
	public function validateSetupForm(array $form, array &$form_state){
		//nothing here
	}

	/**
	 * @param array $form
	 * @param array $form_state
	 * @return bool
	 */
	public function submitSetupForm(array $form, array &$form_state){
		return TRUE;
	}
}
