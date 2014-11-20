<?php

/**
 * @file TFA module classes
 * Contains \Drupal\tfa\Tfa.
 */

namespace Drupal\tfa;

use Drupal\Component\Utility\String;

/**
 * Class Tfa
 */
class Tfa {

  /**
   * @var array
   */
  protected $context;

  /**
   * @var TfaBasePlugin
   */
  protected $validatePlugin;

  /**
   * @var array
   */
  protected $loginPlugins = array();

  /**
   * @var array
   */
  protected $fallbackPlugins = array();

  /**
   * @var bool
   */
  protected $complete = FALSE;

  /**
   * @var bool
   */
  protected $fallback = FALSE;

  /**
   * TFA constructor.
   *
   * @param array $plugins
   *   Plugins to instansiate.
   *
   *   Must include key:
   *
   *     - 'validate'
   *       Class name of TfaBasePlugin implementing TfaValidationPluginInterface.
   *
   *   May include keys:
   *
   *     - 'login'
   *       Array of classes of TfaBasePlugin implementing TfaLoginPluginInterface.
   *
   *     - 'fallback'
   *       Array of classes of TfaBasePlugin that can be used as fallback processes.
   *
   * @param array $context
   *   Context of TFA process.
   *
   *   Must include key:
   *
   *     - 'uid'
   *       Account uid of user in TFA process.
   *
   */
  public function __construct(array $plugins, array $context) {

    new HookDiscovery('tfa_api');

    if (empty($plugins)) {
      throw new \RuntimeException(
        String::format('TFA must have at least 1 valid plugin',
          array('@function' => 'Tfa::__construct')));
    }
    if (empty($plugins['validate'])) {
      throw new \RuntimeException(
        String::format('TFA must have at least 1 valid plugin',
          array('@function' => 'Tfa::__construct')));
    }

    $this->validatePlugin = new $plugins['validate']($context);
    if (!empty($plugins['login'])) {
      foreach ($plugins['login'] as $class) {
        $this->loginPlugins[] = new $class($context);
      }
    }
    if (!empty($plugins['fallback'])) {
      $plugins['fallback'] = array_unique($plugins['fallback']);
      // @todo consider making plugin->ready a class method?
      foreach ($plugins['fallback'] as $key => $class) {
        if ($class === $plugins['validate']) {
          unset($plugins['fallback'][$key]);
          continue; // Skip this fallback if its same as validation.
        }
        $fallback = new $class($context);
        // Only plugins that are ready can stay.
        if ($fallback->ready()) {
          $this->fallbackPlugins[] = $class;
        }
        else {
          unset($plugins['fallback'][$key]);
        }
      }
      if (!empty($this->fallbackPlugins)) {
        $this->fallback = TRUE;
      }
    }
    $this->context = $context;
    $this->context['plugins'] = $plugins;
  }

  /**
   * Whether authentication should be allowed and not interrupted.
   *
   * If any plugin returns TRUE then authentication is not interrupted by TFA.
   *
   * @return bool
   */
  public function loginAllowed() {
    if (!empty($this->loginPlugins)) {
      foreach ($this->loginPlugins as $class) {
        if ($class->loginAllowed()) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Determine if TFA process is ready.
   *
   * @return bool Whether process can begin or not.
   */
  public function ready() {
    return $this->validatePlugin->ready();
  }

  /**
   * Get TFA process form from plugin.
   *
   * @param array $form
   * @param array $form_state
   * @return array Form API array.
   */
  public function getForm(array $form, array &$form_state) {
    $form = $this->validatePlugin->getForm($form, $form_state);
    // Allow login plugins to modify form.
    if (!empty($this->loginPlugins)) {
      foreach ($this->loginPlugins as $class) {
        if (method_exists($class, 'getForm')) {
          $form = $class->getForm($form, $form_state);
        }
      }
    }
    return $form;
  }

  /**
   * Checks if user is allowed to continue with plugin action.
   *
   * @param string $window
   * @return bool
   */
  public function floodIsAllowed($window = '') {
    if (method_exists($this->validatePlugin, 'floodIsAllowed')) {
      return $this->validatePlugin->floodIsAllowed($window);
    }
    return TRUE;
  }

  /**
   * Validate form.
   *
   * @param array $form
   * @param array $form_state
   * @return bool
   */
  public function validateForm(array $form, array &$form_state) {
    return $this->validatePlugin->validateForm($form, $form_state);
  }

  /**
   * Return process error messages.
   *
   * @return array
   */
  public function getErrorMessages() {
    return $this->validatePlugin->getErrorMessages();
  }

  /**
   * Invoke submitForm() on plugins.
   *
   * @param array $form
   * @param array $form_state
   * @return bool Whether the validate plugin is complete.
   *   FALSE will cause tfa_form_submit() to rebuild the form for multi-step.
   */
  public function submitForm(array $form, array &$form_state) {
    // Handle fallback if set.
    if ($this->fallback && isset($form_state['values']['fallback']) && $form_state['values']['op'] === $form_state['values']['fallback']) {
      // Change context to next fallback and reset validatePlugin.
      $this->context['plugins']['validate'] = array_shift($this->context['plugins']['fallback']);
      $class = $this->context['plugins']['validate'];
      $this->validatePlugin = new $class($this->context);
      if (empty($this->context['plugins']['fallback'])) {
        $this->fallback = FALSE;
      }
      // Record which plugin is activated as fallback.
      $this->context['active_fallback'] = $this->context['plugins']['validate'];
    }
    // Otherwise invoke plugin submitForm().
    elseif (method_exists($this->validatePlugin, 'submitForm')) {
      // Check if plugin is complete.
      $this->complete = $this->validatePlugin->submitForm($form, $form_state);
    }
    // Allow login plugins to handle form submit.
    if (!empty($this->loginPlugins)) {
      foreach ($this->loginPlugins as $class) {
        if (method_exists($class, 'submitForm')) {
          $class->submitForm($form, $form_state);
        }
      }
    }
    return $this->complete;
  }

  /**
   * Begin the TFA process.
   */
  public function begin() {
    // Invoke begin method on send validation plugins.
    if (method_exists($this->validatePlugin, 'begin')) {
      $this->validatePlugin->begin();
    }
  }

  /**
   * Whether the TFA process has any fallback proceses.
   *
   * @return bool
   */
  public function hasFallback() {
    return $this->fallback;
  }

  /**
   * Return TFA context.
   *
   * @return array
   */
  public function getContext() {
    if (method_exists($this->validatePlugin, 'getPluginContext')) {
      $pluginContext = $this->validatePlugin->getPluginContext();
      $this->context['validate_context'] = $pluginContext;
    }
    return $this->context;
  }

  /**
   * Run TFA process finalization.
   */
  public function finalize() {
    // Invoke plugin finalize.
    if (method_exists($this->validatePlugin, 'finalize')) {
      $this->validatePlugin->finalize();
    }
    // Allow login plugins to act during finalization.
    if (!empty($this->loginPlugins)) {
      foreach ($this->loginPlugins as $class) {
        if (method_exists($class, 'finalize')) {
          $class->finalize();
        }
      }
    }
  }

}












