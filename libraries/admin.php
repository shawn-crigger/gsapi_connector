<?php

/**
 * @package   GSAPI_Connector
 * @author    Surfside Web
 * @license   GPL-2.0+
 * @link      https://surfsideweb.com
 * @copyright 2018 Wil Hatfield
 *
 * @gsapi_connector
 * Plugin Name:       Grand Strand Connector for WordPress
 * Plugin URI:
 * Description:       Does stuff and things, we'll add a description after we see what all we do with this.
 * Version:           1.5.0
 * Author:            Surfside Web
 * Author URI:        https://surfsideweb.com
 * Text Domain:       gsapi-demo
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
	die();
}
/**
 * Grand Strand Connector for WordPress
 */
class GSAPI_Connector_Admin
{
	/**
	 * Holds the permission required to access and change settings
	 * @var string
	 */
	private static $cap = 'manage_options';
	/**
	 * Holds the plugin settings
	 * @static
	 * @access     private
	 * @var        array
	 */
	static private $settings = NULL;
	/**
	 * Holds language entries
	 * @static
	 * @access     private
	 * @var        array
	 */
	static $language = NULL;
	public function __construct()
	{
		if (!is_admin()) return;
		add_action('contextual_help', array(&$this, 'add_screen_object_help'), 10, 3);
		add_action('admin_menu', array(&$this, 'setup_menu'));
		add_action('admin_init', array(&$this, 'settings_init'));
		self::$language = array('hint_apiKey' => 'This would be the key you received from the Grand Strand API', 'hint_password' => 'This would be the password for that key', 'notice_missing_apikey' => 'Your GSAPI API login details need to be filled in.', 'hint_gmaps' => 'You need a Google API key to use the maps',);
		self::$settings = GSAPI_Connector::get_settings();
		if (trim(self::$settings['gsapi_apikey']) === '' or trim(self::$settings['gsapi_password']) === '') {
			add_action('admin_notices', array(&$this, 'display_missing_apikey_notice'));
		}
	}
	// ------------------------------------------------------------------------
	public function setup_menu()
	{
		add_submenu_page('edit.php?post_type=gsplace', 'GSAPI Connector', 'Settings', self::$cap, 'gsplace_settings', array(&$this, 'options_page'));
		// remove_submenu_page('edit.php?post_type=gsplace', 'post-new.php?post_type=gsplace');
		// remove_submenu_page('edit.php?post_type=gsplace', 'edit.php?post_type=gsplace');
	}
	// ------------------------------------------------------------------------
	public function settings_init()
	{
		register_setting('gsapi_settings_page', 'gsapi_settings');
		add_settings_section('gsapi_settings_section', __('', 'gsapi'), array(&$this, 'settings_section_callback'), 'gsapi_settings_page');
		add_settings_field('gsapi_apikey', __('GSAPI API Key', 'gsapi'), array(&$this, 'apikey_render'), 'gsapi_settings_page', 'gsapi_settings_section');
		add_settings_field('gsapi_password', __('GSAPI Password Key', 'gsapi'), array(&$this, 'password_render'), 'gsapi_settings_page', 'gsapi_settings_section');
		add_settings_field('gmaps_key', __('Google Maps API Key', 'gsapi'), array(&$this, 'gmaps_render'), 'gsapi_settings_page', 'gsapi_settings_section');
		add_settings_section('gsapi_css1settings_section', __('', 'gsapi'), array(&$this, 'css1settings_section_callback'), 'gsapi_settings_page');
		add_settings_field('gsapi_primarycolor', __('Details Primary Color', 'gsapi'), array(&$this, 'primarycolor_render'), 'gsapi_settings_page', 'gsapi_css1settings_section');
		add_settings_field('gsapi_secondarycolor', __('Details Secondary Color', 'gsapi'), array(&$this, 'secondarycolor_render'), 'gsapi_settings_page', 'gsapi_css1settings_section');
		add_settings_field('gsapi_accentcolor', __('Details Accent Color', 'gsapi'), array(&$this, 'accentcolor_render'), 'gsapi_settings_page', 'gsapi_css1settings_section');
		add_settings_section('gsapi_css2settings_section', __('', 'gsapi'), array(&$this, 'css2settings_section_callback'), 'gsapi_settings_page');
		add_settings_field('gsapi_photowidth', __('Details Photo Width', 'gsapi'), array(&$this, 'photowidth_render'), 'gsapi_settings_page', 'gsapi_css2settings_section');
		add_settings_field('gsapi_primarycolor2', __('Details Primary Color', 'gsapi'), array(&$this, 'primarycolor2_render'), 'gsapi_settings_page', 'gsapi_css2settings_section');
		add_settings_field('gsapi_secondarycolor2', __('Details Secondary Color', 'gsapi'), array(&$this, 'secondarycolor2_render'), 'gsapi_settings_page', 'gsapi_css2settings_section');
		add_settings_field('gsapi_accentcolor2', __('Details Accent Color', 'gsapi'), array(&$this, 'accentcolor2_render'), 'gsapi_settings_page', 'gsapi_css2settings_section');

	}
	// ------------------------------------------------------------------------
	public function gmaps_render()
	{
		$options = self::$settings;
		$value = isset($options['gmaps_key']) ? $options['gmaps_key'] : '';
		echo "<input class='regular-text' type='text' name='gsapi_settings[gmaps_key]' value='{$value}'>";
		echo '<p class="description" id="hint-apiKey">' . self::$language['hint_gmaps'] . '</p>';
	}
	// ------------------------------------------------------------------------


	public function apikey_render()
	{
		$options = self::$settings;
		$value = isset($options['gsapi_apikey']) ? $options['gsapi_apikey'] : '';
		echo "<input class='regular-text' type='text' name='gsapi_settings[gsapi_apikey]' value='{$value}'>";
		echo '<p class="description" id="hint-apiKey">' . self::$language['hint_apiKey'] . '</p>';
	}
	// ------------------------------------------------------------------------
	function password_render()
	{
		$options = self::$settings;
		$value = isset($options['gsapi_password']) ? $options['gsapi_password'] : '';
		echo "<input class='regular-text' type='text' name='gsapi_settings[gsapi_password]' value='{$value}'>";
		echo '<p class="description" id="hint-password">' . self::$language['hint_password'] . '</p>';
	}
	// ------------------------------------------------------------------------
	function settings_section_callback()
	{
		echo __('<h3>Enter your API Key information here.</h3>', 'gsapi');
	}
	// ------------------------------------------------------------------------
	function css1settings_section_callback()
	{
		echo __('<h3>Enter your Category Style information here.</h3>', 'gsapi');
	}
	// ------------------------------------------------------------------------
	public function primarycolor_render()
	{
		$options = self::$settings;
		$value = isset($options['gsapi_primarycolor']) ? $options['gsapi_primarycolor'] : '';
		echo "<input class='regular-text' type='text' name='gsapi_settings[gsapi_primarycolor]' value='{$value}'>";
		echo '<p class="description" id="hint-primarycolor">' . self::$language['hint_primarycolor'] . '</p>';
	}
	// ------------------------------------------------------------------------
	public function secondarycolor_render()
	{
		$options = self::$settings;
		$value = isset($options['gsapi_secondarycolor']) ? $options['gsapi_secondarycolor'] : '';
		echo "<input class='regular-text' type='text' name='gsapi_settings[gsapi_secondarycolor]' value='{$value}'>";
		echo '<p class="description" id="hint-secondarycolor">' . self::$language['hint_secondarycolor'] . '</p>';
	}
	// ------------------------------------------------------------------------
	// ------------------------------------------------------------------------
	public function accentcolor_render()
	{
		$options = self::$settings;
		$value = isset($options['gsapi_accentcolor']) ? $options['gsapi_accentcolor'] : '';
		echo "<input class='regular-text' type='text' name='gsapi_settings[gsapi_accentcolor]' value='{$value}'>";
		echo '<p class="description" id="hint-accentcolor">' . self::$language['hint_accentcolor'] . '</p>';
	}
	// ------------------------------------------------------------------------
	function css2settings_section_callback()
	{
		echo __('<h3>Enter your Details Style information here.</h3>', 'gsapi');
	}
	// ------------------------------------------------------------------------
	public function photowidth_render()
	{
		$options = self::$settings;
		$value = isset($options['gsapi_photowidth']) ? $options['gsapi_photowidth'] : '';
		echo "<input class='regular-text' type='text' name='gsapi_settings[gsapi_photowidth]' value='{$value}'>";
		echo '<p class="description" id="hint-photowidth">' . self::$language['hint_photowidth'] . '</p>';
	}
	// ------------------------------------------------------------------------
	public function primarycolor2_render()
	{
		$options = self::$settings;
		$value = isset($options['gsapi_primarycolor']) ? $options['gsapi_primarycolor'] : '';
		echo "<input class='regular-text' type='text' name='gsapi_settings[gsapi_primarycolor]' value='{$value}'>";
		echo '<p class="description" id="hint-primarycolor">' . self::$language['hint_primarycolor'] . '</p>';
	}
	// ------------------------------------------------------------------------
	public function secondarycolor2_render()
	{
		$options = self::$settings;
		$value = isset($options['gsapi_secondarycolor']) ? $options['gsapi_secondarycolor'] : '';
		echo "<input class='regular-text' type='text' name='gsapi_settings[gsapi_secondarycolor]' value='{$value}'>";
		echo '<p class="description" id="hint-secondarycolor">' . self::$language['hint_secondarycolor'] . '</p>';
	}
	// ------------------------------------------------------------------------
	public function accentcolor2_render()
	{
		$options = self::$settings;
		$value = isset($options['gsapi_accentcolor']) ? $options['gsapi_accentcolor'] : '';
		echo "<input class='regular-text' type='text' name='gsapi_settings[gsapi_accentcolor]' value='{$value}'>";
		echo '<p class="description" id="hint-accentcolor">' . self::$language['hint_accentcolor'] . '</p>';
	}
	// ------------------------------------------------------------------------


	public function options_page()
	{
		if (isset($_GET['settings-updated'])) {
			// add settings saved message with the class of "updated"
			add_settings_error('gsapi_settings_messages', 'gsapi_settings_message', __('Settings Saved', 'gsapi'), 'updated');
		}
		// show error/update messages
		settings_errors('gsapi_settings_messages');
		echo '<div id="poststuff">';
		echo '<div class="wrap">';
		echo '<div class="column-2">';
		echo '<div id="ip_check_settings_metabox" class="postbox"> ' . '<button type="button" class="handlediv" aria-expanded="true">' . '<span class="screen-reader-text">Toggle panel: GSAPI Settings</span>' . '<span class="toggle-indicator" aria-hidden="true"></span>' . '</button><h3 class="hndle ui-sortable-handle">' . '<span>GSAPI Settings</span></h3>' . '<div class="inside">';
		echo '<form action="options.php" method="post">';
		settings_fields('gsapi_settings_page');
		do_settings_sections('gsapi_settings_page');
		submit_button();
		echo '</div></div>';
		echo '</form>';
		echo '</div></div></div>';
	}
	// ------------------------------------------------------------------------

	/**
	 * Creates a Help tab on Each admin Page with all the available Screen options.
	 * @static
	 *
	 * @param      array    $contextual_help  The contextual help
	 * @param      string   $screen_id        The screen identifier
	 * @param      object   $screen           The screen
	 *
	 * @return     array
	 */
	static public function add_screen_object_help($contextual_help, $screen_id, $screen)
	{
		// The add_help_tab function for screen was introduced in WordPress 3.3.
		if (!method_exists($screen, 'add_help_tab')) return $contextual_help;
		global $hook_suffix;
		// List screen properties
		$variables = '<ul style="width:50%;float:left;"> <strong>Screen variables </strong>' . sprintf('<li> Screen id : %s</li>', $screen_id) . sprintf('<li> Screen base : %s</li>', $screen->base) . sprintf('<li>Parent base : %s</li>', $screen->parent_base) . sprintf('<li> Parent file : %s</li>', $screen->parent_file) . sprintf('<li> Hook suffix : %s</li>', $hook_suffix) . '</ul>';
		// Append global $hook_suffix to the hook stems
		$hooks = array("load-$hook_suffix", "admin_print_styles-$hook_suffix", "admin_print_scripts-$hook_suffix", "admin_head-$hook_suffix", "admin_footer-$hook_suffix");
		// If add_meta_boxes or add_meta_boxes_{screen_id} is used, list these too
		if (did_action('add_meta_boxes_' . $screen_id)) $hooks[] = 'add_meta_boxes_' . $screen_id;
		if (did_action('add_meta_boxes')) $hooks[] = 'add_meta_boxes';
		// Get List HTML for the hooks
		$hooks = '<ul style="width:50%;float:left;"> <strong>Hooks </strong> <li>' . implode('</li><li>', $hooks) . '</li></ul>';
		// Combine $variables list with $hooks list.
		$help_content = $variables . $hooks;
		// Add help panel
		$screen->add_help_tab(array('id' => 'wptuts-screen-help', 'title' => 'Screen Information', 'content' => $help_content,));
		return $contextual_help;
	}
	// ------------------------------------------------------------------------

	/**
	 * Display error message if API key is missing
	 */
	public function display_missing_apikey_notice()
	{
		echo '<div class="notice notice-error is-dismissible"><p>';
		echo self::$language['notice_missing_apikey'];
		echo '</p></div>';
	}
	// ------------------------------------------------------------------------
	// ------------------------------------------------------------------------

}
