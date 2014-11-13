<?php

/**
 * @file
 * Contains Drupal\tfa\Form\SettingsForm.
 */

namespace Drupal\tfa\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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

    // Gather plugins.
    foreach (\Drupal::moduleHandler()->invokeAll('tfa_api', []) as $key => $data) {
      if (is_subclass_of($data['class'], 'TfaBasePlugin')) {
        $plugins[$key] = $data;
      }
      if (in_array('TfaValidationPluginInterface', class_implements($data['class']))) {
        $validate_plugins[$key] = $data['name'];
      }
      if (in_array('TfaSendPluginInterface', class_implements($data['class']))) {
        $send_plugins[$key] = $data['name'];
      }
      elseif (in_array('TfaLoginPluginInterface', class_implements($data['class']))) {
        $login_plugins[$key] = $data['name'];
      }
    }

    // Check if mcrypt plugin is available.
    /*
    if (!extension_loaded('mcrypt')) {
      // @todo allow alter in case of other encryption libs.
      drupal_set_message(t('The TFA module requires the PHP Mcrypt extension be installed on the web server. See <a href="!link">the TFA help documentation</a> for setup.', array('!link' => \Drupal\Core\Url::fromRoute('help.page'))), 'error');

      return parent::buildForm($form, $form_state);;
    }
    */

    // Return if there are no plugins.
    if (empty($plugins) || empty($validate_plugins)) {
      //drupal_set_message(t('No plugins available for validation. See <a href="!link">the TFA help documentation</a> for setup.', array('!link' => \Drupal\Core\Url::fromRoute('help.page'))), 'error');
      drupal_set_message(t('No plugins available for validation. See the TFA help documentation for setup.'), 'error');
      return parent::buildForm($form, $form_state);;
    }

    $form['plugins'] = array(
      '#type'  => 'fieldset',
      '#title' => t('Available plugins'),
    );
    $items = array();
    foreach ($plugins as $key => $plugin) {
      $message = '<strong>@name</strong> (%type)';
      // Include message whether plugin is set.
      if ($config->get('tfa_enabled') && $config->get('tfa_validate_plugin') === $key
      ) {
        $message .= ' - active validator';
      }
      elseif ($config->get('tfa_enabled') && in_array($key, $config->get('tfa_login_plugins'))
      ) {
        $message .= ' - active login';
      }
      elseif ($config->get('tfa_enabled') && in_array($key, $config->get('tfa_fallback_plugins'))
      ) {
        $message .= ' - active fallback';
      }
      elseif ($config->get('tfa_enabled')) {
        $message .= ' - unused';
      }
      $items[] = t($message, array(
        '%type' => $this->tfa_class_types($plugin['class']),
        '@name' => $plugin['name']
      ));
    }
    $form['plugins']['list'] = array(
      '#value'  => 'markup',
      '#markup' => _theme('item_list', array('items' => $items)),
    );

    // Option to enable entire process or not.
    $form['tfa_enabled'] = array(
      '#type'          => 'checkbox',
      '#title'         => t('Enable TFA'),
      '#default_value' => $config->get('tfa_enabled'),
      '#description'   => t('Enable TFA for account authentication.'),
    );

    // Reusable #states for tfa_enabled.
    $enabled_state = array(
      'visible' => array(
        ':input[name="tfa_enabled"]' => array('checked' => TRUE)
      )
    );

    // Default active plugin
    if (count($validate_plugins) >= 1) {
      $form['tfa_validate'] = array(
        '#type'          => 'select',
        '#title'         => t('Default validation plugin'),
        '#options'       => $validate_plugins,
        '#default_value' => $config->get('tfa_validate_plugin'),
        '#description'   => t('Plugin that will be used as the default TFA process.'),
        '#states'        => $enabled_state,
      );
    }
    else {
      $form['no_validate'] = array(
        '#value'  => 'markup',
        '#markup' => t('No available validation plugins available. TFA process will not occur.'),
      );
    }

    // Order of fallback plugins
    if (count($validate_plugins) > 1) {
      $enabled_fallback = $config->get('tfa_fallback_plugins');
      $form['tfa_fallback'] = array(
        '#type'        => 'fieldset',
        '#title'       => t('Validation fallback plugins'),
        '#description' => t('Fallback plugins and order. Note, if a fallback plugin is not setup for an account it will not be active in the TFA form.'),
        '#states'      => $enabled_state,
        '#tree'        => TRUE,
      );
      // First enabled.
      foreach ($enabled_fallback as $order => $key) {
        $validate_state             = array(
          'invisible' => array(
            ':input[name="tfa_validate"]' => array('value' => $key)
          )
        );
        $form['tfa_fallback'][$key] = array(
          'enable' => array(
            '#title'         => $validate_plugins[$key],
            '#type'          => 'checkbox',
            '#default_value' => TRUE,
            // Don't show options that are set as the main validation plugin.
            '#states'        => $validate_state,
          ),
          'weight' => array(
            '#type'          => 'weight',
            '#title'         => t('Order'),
            '#default_value' => $order,
            '#delta'         => 10,
            '#title_display' => 'invisible',
            '#states'        => $validate_state,
          ),
        );
      }
      // Then other plugins.
      foreach ($validate_plugins as $key => $plugin_name) {
        if (isset($form['tfa_fallback'][$key])) {
          continue;
        }
        $validate_state             = array(
          'invisible' => array(
            ':input[name="tfa_validate"]' => array('value' => $key)
          )
        );
        $form['tfa_fallback'][$key] = array(
          'enable' => array(
            '#title'         => $plugin_name,
            '#type'          => 'checkbox',
            '#default_value' => in_array($key, $enabled_fallback) ? TRUE : FALSE,
            // Don't show options that are set as the main validation plugin.
            '#states'        => $validate_state,
          ),
          'weight' => array(
            '#type'          => 'weight',
            '#title'         => t('Order'),
            '#default_value' => in_array($key, $enabled_fallback) ? array_search($key, $enabled_fallback) : 0,
            '#delta'         => 10,
            '#title_display' => 'invisible',
            '#states'        => $validate_state,
          ),
        );
      }
    }

    // Enable login plugins.
    if (count($login_plugins) >= 1) {
      $form['tfa_login'] = array(
        '#type'          => 'checkboxes',
        '#title'         => t('Login plugins'),
        '#options'       => $login_plugins,
        '#default_value' => $config->get('tfa_login_plugins'),
        '#description'   => t('Plugins that can allow a user to skip the TFA process. If any plugin returns true the user will not be required to follow TFA. <strong>Use with caution.</strong>'),
        '#states'        => $enabled_state,
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
    parent::submitForm($form, $form_state);

    $this->config('tfa.settings')
      ->set('tfa_enabled', $form_state->getValue('tfa_enabled'))
      ->save();
  }
}
