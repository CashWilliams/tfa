<?php
/**
 * @file
 * Contains Drupal\tfa\TfaLoginPluginManager.
 */

namespace Drupal\tfa;

use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;


class TfaLoginPluginManager extends \Drupal\Core\Plugin\DefaultPluginManager {
	/**
	 * Constructs a new TfaLogin plugin manager.
	 *
	 * @param \Traversable $namespaces
	 *   An object that implements \Traversable which contains the root paths
	 *   keyed by the corresponding namespace to look for plugin implementations.
	 * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
	 *   Cache backend instance to use.
	 * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
	 *   The module handler.
	 */
	public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
		parent::__construct('Plugin/TfaLogin', $namespaces, $module_handler, 'Drupal\tfa\TfaLoginInterface', 'Drupal\tfa\Annotation\TfaLogin');
		$this->alterInfo('tfa_login_info');
		$this->setCacheBackend($cache_backend, 'tfa_login');
	}

}