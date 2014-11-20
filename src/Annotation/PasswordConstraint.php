<?php

/**
 * @file
 * Contains Drupal\password_policy\Annotation\PasswordConstraint.
 */

namespace Drupal\password_policy\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a password constraint annotation object.
 *
 * @Annotation
 */
class PasswordConstraint extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the constraint type.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $title;

	/**
	 * The description shown to users.
	 *
	 * @ingroup plugin_translatable
	 *
	 * @var \Drupal\Core\Annotation\Translation
	 */
	public $description;


	/**
	 * The error message shown if the constraint fails.
	 *
	 * @ingroup plugin_translatable
	 *
	 * @var \Drupal\Core\Annotation\Translation
	 */
	public $error_message;

	/**
	 * The path to the policy form.
	 *
	 * @var string
	 */
	public $policy_path;

	/**
	 * The path to the policy update form. Note: MUST use token for the ID
	 *
	 * @var string
	 */
	public $policy_update_path;

	/**
	 * The token for the ID in the policy_update_path.
	 *
	 * @var string
	 */
	public $policy_update_token;

}
