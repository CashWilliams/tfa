<?php
/**
 * Class TfaSetup
 */
class TfaSetup {

	/**
	 * @var TfaBasePlugin
	 */
	protected $setupPlugin;

	/**
	 * TFA Setup constructor.
	 *
	 * @param array $plugins
	 *   Plugins to instansiate.
	 *
	 *   Must include key:
	 *
	 *     - 'setup'
	 *       Class name of TfaBasePlugin implementing TfaSetupPluginInterface.
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
		if (empty($plugins['setup'])) {
			// @todo throw exception?
		}
		$this->setupPlugin = new $plugins['setup']($context);
		$this->context = $context;
		$this->context['plugins'] = $plugins;
	}

	/**
	 * Run any begin setup processes.
	 */
	public function begin() {
		// Invoke begin method on setup plugin.
		if (method_exists($this->setupPlugin, 'begin')) {
			$this->setupPlugin->begin();
		}
	}

	/**
	 * Get plugin form.
	 *
	 * @param array $form
	 * @param array $form_state
	 * @return array
	 */
	public function getForm(array $form, array &$form_state) {
		return $this->setupPlugin->getSetupForm($form, $form_state);
	}

	/**
	 * Validate form.
	 *
	 * @param array $form
	 * @param array $form_state
	 * @return bool
	 */
	public function validateForm(array $form, array &$form_state) {
		return $this->setupPlugin->validateSetupForm($form, $form_state);
	}

	/**
	 * Return process error messages.
	 *
	 * @return array
	 */
	public function getErrorMessages() {
		return $this->setupPlugin->getErrorMessages();
	}

	/**
	 *
	 * @param array $form
	 * @param array $form_state
	 * @return bool
	 */
	public function submitForm(array $form, array &$form_state) {
		return $this->setupPlugin->submitSetupForm($form, $form_state);
	}

	/**
	 *
	 * @return array
	 */
	public function getContext() {
		if (method_exists($this->setupPlugin, 'getPluginContext')) {
			$pluginContext = $this->setupPlugin->getPluginContext();
			$this->context['setup_context'] = $pluginContext;
		}
		return $this->context;
	}
}