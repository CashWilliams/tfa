<?php

/**
 * @file
 * Contains Drupal\tfa\Form\SettingsForm.
 */

namespace Drupal\tfa\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\String;

class SettingsForm extends ConfigFormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'tfa_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tfa.settings');
    $form = array();
    $plugins = $send_plugins = $validate_plugins = $login_plugins = array();

		//TODO - Wondering if all modules extend TfaBasePlugin


		//Get Login Plugins
		$plugin_manager = \Drupal::service('plugin.manager.tfa.login');
		$login_plugins = $plugin_manager->getDefinitions();

		//Get Send Plugins
		$plugin_manager = \Drupal::service('plugin.manager.tfa.send');
		$send_plugins = $plugin_manager->getDefinitions();

		//Get Validation Plugins
		$plugin_manager = \Drupal::service('plugin.manager.tfa.validation');
		$validate_plugins = $plugin_manager->getDefinitions();

		//Get Setup Plugins
		$plugin_manager = \Drupal::service('plugin.manager.tfa.setup');
		$setup_plugins = $plugin_manager->getDefinitions();



    // Check if mcrypt plugin is available.
    /*
    if (!extension_loaded('mcrypt')) {
      // @todo allow alter in case of other encryption libs.
      drupal_set_message(t('The TFA module requires the PHP Mcrypt extension be installed on the web server. See <a href="!link">the TFA help documentation</a> for setup.', array('!link' => \Drupal\Core\Url::fromRoute('help.page'))), 'error');

      return parent::buildForm($form, $form_state);;
    }
    */

    // Return if there are no plugins.
		//TODO - Why check for plugins here?
    //if (empty($plugins) || empty($validate_plugins)) {
		if (empty($validate_plugins)) {
      //drupal_set_message(t('No plugins available for validation. See <a href="!link">the TFA help documentation</a> for setup.', array('!link' => \Drupal\Core\Url::fromRoute('help.page'))), 'error');
      drupal_set_message(t('No plugins available for validation. See the TFA help documentation for setup.'), 'error');
      return parent::buildForm($form, $form_state);;
    }

    // Option to enable entire process or not.
    $form['tfa_enabled'] = array(
      '#type'          => 'checkbox',
      '#title'         => t('Enable TFA'),
      '#default_value' => $config->get('tfa_enabled'),
      '#description'   => t('Enable TFA for account authentication.'),
    );




		//TODO - Get Weight for validate items

    if (count($validate_plugins)) {

			$form['validate_plugins'] = array(
				'#type' => 'table',
				'#header' => array(t('Validation Plugins'), t('Weight'),),
				'#empty' => t('There are no constraints for the selected user roles'),
				'#tableselect' => TRUE,
				'#tabledrag' => array(
					array(
						'action' => 'order',
						'relationship' => 'sibling',
						'group' => 'validate-plugins-order-weight',
					),
				),
				'#default_value' => ($config->get('tfa_validate_plugins'))?$config->get('tfa_validate_plugins'):array(),
			);

			$c=0;

			foreach($validate_plugins as $validate_plugin){
				$id = (string) $validate_plugin['id'];
				$title = (string) $validate_plugin['title'];
				// TableDrag: Mark the table row as draggable.
				$form['validate_plugins'][$id]['#attributes']['class'][] = 'draggable';
				// TableDrag: Sort the table row according to its existing/configured weight.
				$form['validate_plugins'][$id]['#weight'] = $c;

				// Some table columns containing raw markup.
				$form['validate_plugins'][$id]['title'] = array(
					'#markup' => String::checkPlain($title),
				);

				// TableDrag: Weight column element.
				$form['validate_plugins'][$id]['weight'] = array(
					'#type' => 'weight',
					'#title' => t('Weight for @title', array('@title' => $title)),
					'#title_display' => 'invisible',
					'#default_value' => $c,
					// Classify the weight element for #tabledrag.
					'#attributes' => array('class' => array('validate-plugins-order-weight')),
				);

				$c++;
			}



    }
    else {
      $form['no_validate'] = array(
        '#value'  => 'markup',
        '#markup' => t('No available validation plugins available. TFA process will not occur.'),
      );
    }


		// Enable login plugins.
    if (count($login_plugins) >= 1) {
			$login_form_array = array();

			foreach($login_plugins as $login_plugin){
				$id = $login_plugin['id'];
				$title = $login_plugin['title'];
				$login_form_array[$id] = (string) $title;
			}

      $form['tfa_login'] = array(
        '#type'          => 'checkboxes',
        '#title'         => t('Login plugins'),
        '#options'       => $login_form_array,
				'#default_value' => ($config->get('tfa_login_plugins'))?$config->get('tfa_login_plugins'):array(),
        '#description'   => t('Plugins that can allow a user to skip the TFA process. If any plugin returns true the user will not be required to follow TFA. <strong>Use with caution.</strong>'),
      );
    }

		// Enable send plugins.
		if (count($send_plugins) >= 1) {
			$send_form_array = array();

			foreach($send_plugins as $send_plugin){
				$id = $send_plugin['id'];
				$title = $send_plugin['title'];
				$send_form_array[$id] = (string) $title;
			}

			$form['tfa_send'] = array(
				'#type'          => 'checkboxes',
				'#title'         => t('Send plugins'),
				'#options'       => $send_form_array,
				'#default_value' => ($config->get('tfa_send_plugins'))?$config->get('tfa_send_plugins'):array(),
				//TODO - Fill in description
				'#description'   => t('Not sure what this is'),
			);
		}

		// Enable setup plugins.
		if (count($setup_plugins) >= 1) {
			$setup_form_array = array();

			foreach($setup_plugins as $setup_plugin){
				$id = $setup_plugin['id'];
				$title = $setup_plugin['title'];
				$setup_form_array[$id] = $title;
			}

			$form['tfa_setup'] = array(
				'#type'          => 'checkboxes',
				'#title'         => t('Setup plugins'),
				'#options'       => $setup_form_array,
				'#default_value' => ($config->get('tfa_setup_plugins'))?$config->get('tfa_setup_plugins'):array(),
				//TODO - Fill in description
				'#description'   => t('Not sure what this is'),
			);
		}

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    return parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

		$config = $this->config('tfa.settings');

		$config->config('tfa.settings')
      ->set('tfa_enabled', $form_state->getValue('tfa_enabled'))
			->set('tfa_setup_plugins', array_filter($form_state->getValue('tfa_setup')))
			->set('tfa_send_plugins', array_filter($form_state->getValue('tfa_send')))
			->set('tfa_login_plugins', array_filter($form_state->getValue('tfa_login')))
			->set('tfa_validate_plugins', array_filter($form_state->getValue('validate_plugins')))
      ->save();

		parent::submitForm($form, $form_state);
  }
}
