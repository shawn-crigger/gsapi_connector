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

// ----- -------------------------------------------------------------------

register_activation_hook(__FILE__, 'gsapi_rewrite_flush');
register_deactivation_hook(__FILE__, 'gsapi_rewrite_flush');

function gsapi_rewrite_flush()
{
	flush_rewrite_rules();
}
// ----- -------------------------------------------------------------------


if (!function_exists('load_view')) :

	/** THIS STUPID THING DON'T WORK RIGHT
	 * Loads theme files in appropriate hierarchy:
	 * 1) child theme,
	 * 2) parent template,
	 * 3) plugin resources.
	 * will look in the events/directory in a theme and the views/ directory in the plugin, applys filter c_template_{plugin_name}_{template} after loading.
	 *
	 * @param string $template template file to search for
	 * @param string $plugin_name name of plugin
	 * @return template path
	 **/
	function load_view($template, $data = array(), $plugin_name = 'gsapi_connector')
	{
		// whether or not .php was added
		$template_slug = rtrim($template, '.php');
		$template = $template_slug . '.php';

		if (file_exists(get_template_directory(__FILE__) . '/gsapi/views/listing.php')) {
			$file = get_template_directory(__FILE__) . '/gsapi/views/listing.php';
		} elseif ($theme_file = locate_template(array($plugin_name . '/' . $template))) {
			$file = $theme_file;
		} else {
			$base = trailingslashit(__DIR__) . '/..//';
			$file = $base . 'views/' . $template;
		}
		extract($data);
		ob_start();
		require(apply_filters("gsapi_{$plugin_name}_{$template}", $file));
		return ob_get_clean();
	}

endif;

// ------------------------------------------------------------------------

if (!function_exists('make_slug_id')) :

	function make_slug_id($string, $id)
	{
		$string = strtolower($string);
		$string = str_replace([" ", ' ', '--', '+'], "-", $string);
		$string = str_replace(["&"], "-and-", $string);
		$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
		$string = str_replace('--', '-', $string); // Replaces all double hyphens with single hyphens.
		return $string . '-' . $id;
	}

endif;

// ------------------------------------------------------------------------

if (!function_exists('id_from_slug')) :

	function id_from_slug($slug)
	{
		$parts = explode('-', $slug);
		return array_pop($parts);
		$rev = array_reverse($parts);
		return $rev[0];
	}

endif;

if (!function_exists('get_stars')) :

	function get_stars($ratescore, $points, $form = null)
	{

		$rating = $ratescore / 2;
		$rate = "";
		for ($i = 1; $i < $rating; $i++) {
			$rate .= "<i class=\"fa fa-star gold-star\"></i>";
		}
		if ($rating - floor($rating)  >= 0.50) {
			$rate .= "<i class=\"fa fa-star-half gold-star\"></i>";
		} else if ($rating <= 0.49) {
			$rate = "<i class=\"fa fa-star-half gold-star\"></i>";
		}

		if ($form == 'long') {
			return $rate . ' Rating: ' . $ratescore . ' Popular: ' . $points;
		} else {
			return $rate;
		}
	}

endif;


if (!function_exists('google_map')) :

	function google_map($lat, $lng) {
?>
		<div id="map"></div>
		<style>
			#map {
				margin-top: 30px;
				width: 100%;
				height: 400px;
				background-color: grey;
			}
		</style>
		<script>
			// Initialize and add the map
			function initMap() {
				// The location of Uluru
				var uluru = {
					lat: <?php echo $lat; ?>,
					lng: <?php echo $lng; ?>
				};
				// The map, centered at Uluru
				var map = new google.maps.Map(
					document.getElementById('map'), {
						zoom: 16,
						center: uluru
					});
				// The marker, positioned at Uluru
				// var marker = new google.maps.Marker({ position: uluru, map: map});

				//var iconBase = 'https://maps.google.com/mapfiles/kml/shapes/';
				var icon = {
					url: "https://www.grandstrandapi.com/photos/palmettopointer.png",
					scaledSize: new google.maps.Size(28, 50),
					origin: new google.maps.Point(0, 0),
					anchor: new google.maps.Point(22, 50)
				};
				var marker = new google.maps.Marker({
					position: uluru,
					map: map,
					icon: icon
				});

			}
		</script>
		<!--Load the API from the specified URL
		* The async attribute allows the browser to render the page while the API loads
		* The key parameter will contain your own API key (which is not needed for this tutorial)
		* The callback parameter executes the initMap() function
		-->
		<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo GMAPSKEY; ?>&callback=initMap">
		</script>
	<?php
	}

endif;

// ------------------------------------------------------------------------

if (!function_exists('getLatLng')) :

	function getLatLng()
	{
		$latlng = '';
		$_COOKIE['latlng'] = '';
	?>
		<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=<?php echo GMAPSKEY; ?>"></script>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				navigator.geolocation.getCurrentPosition(function(position, html5Error) {
					geo_loc = processGeolocationResult(position);
					//$("#latlng").html(geo_loc);
					document.cookie = "latlng=" + geo_loc;
				});

				function processGeolocationResult(position) {
					html5Lat = position.coords.latitude; //Get latitude
					html5Lon = position.coords.longitude; //Get longitude
					//html5Accuracy = position.coords.accuracy; //Get accuracy in meters
					//return (html5Lat).toFixed(8) + "," + (html5Lon).toFixed(8) + "," + (html5Accuracy).toFixed(2);
					return (html5Lat).toFixed(8) + "," + (html5Lon).toFixed(8);
				}
			});
		</script><?php
							$latlng = $_COOKIE['latlng'];
							//unset($_COOKIE['latlng']);
							return $latlng;
						}

endif;

// ------------------------------------------------------------------------

if (!function_exists('dump')) :

	/**
	 * Outputs the given variables with formatting and location. Huge props
	 * out to Phil Sturgeon for this one (http://philsturgeon.co.uk/blog/2010/09/power-dump-php-applications).
	 * To use, pass in any number of variables as arguments.
	 *
	 * @return void
	 */

	function dump()
	{
		list($callee) = debug_backtrace();
		$arguments = func_get_args();
		$total_arguments = count($arguments);

		echo '<fieldset style="background: #fefefe !important; border:2px red solid; padding:5px">';
		echo '<legend style="background:lightgrey; padding:5px;">' . $callee['file'] . ' @ line: ' . $callee['line'] . '</legend><pre>';

		$i = 0;
		foreach ($arguments as $argument) {
			echo '<br/><strong>Debug #' . (++$i) . ' of ' . $total_arguments . '</strong>: ';
			if ((is_array($argument) || is_object($argument))) {
				print_r($argument);
			} else {
				var_dump($argument);
			}
		}

		echo '</pre>' . PHP_EOL;
		echo '</fieldset>' . PHP_EOL;
	}

endif;

if (!function_exists('slick_dom')) :
	function slick_dom() {

		return <<< EOD

			jQuery(document).ready(function($) {
				$('.responsive').slick({
					dots: false,
					infinite: true,
					speed: 300,
					slidesToShow: 2,
					slidesToScroll: 1,
					autoplay: true,
					autoplaySpeed: 2000,
					responsive: [{
							breakpoint: 1024,
							settings: {
								slidesToShow: 2,
								slidesToScroll: 2,
								infinite: true,
								dots: false
							}
						},
						{
							breakpoint: 600,
							settings: {
								slidesToShow: 2,
								slidesToScroll: 2
							}
						},
						{
							breakpoint: 480,
							settings: {
								slidesToShow: 1,
								slidesToScroll: 1
							}
						}
						// You can unslick at a given breakpoint now by adding:
						// settings: "unslick"
						// instead of a settings object
					]
				});
			});
EOD;
	}


endif;

if (!function_exists('slick')) :

	function slick($size) {

		$slick = <<< EOD

jQuery(document).ready(function($) {
$('.photo-carousel').slick({
dots: true,
infinite: false,
speed: 300,
autoplay: true,
autoplaySpeed: 1200,
arrows: true,
slidesToShow: {$size},
slidesToScroll: 1,
responsive: [
{
breakpoint: 1024,
settings: {
slidesToShow: 2,
slidesToScroll: 2,
infinite: true,
dots: true
}
},
{
breakpoint: 600,
settings: {
slidesToShow: 2,
slidesToScroll: 2
}
},
{
breakpoint: 480,
settings: {
slidesToShow: 1,
slidesToScroll: 1
}
}
// You can unslick at a given breakpoint now by adding:
// settings: "unslick"
// instead of a settings object
]

});
});
EOD;



		return $slick;
	}

endif;

if (!class_exists('Gamajo_Template_Loader')) :

	/**
	 * Template loader.
	 *
	 * Originally based on functions in Easy Digital Downloads (thanks Pippin!).
	 *
	 * When using in a plugin, create a new class that extends this one and just overrides the properties.
	 *
	 * @package Gamajo_Template_Loader
	 * @author  Gary Jones
	 */
	class Gamajo_Template_Loader
	{
		/**
		 * Prefix for filter names.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		protected $filter_prefix = 'your_plugin';
		/**
		 * Directory name where custom templates for this plugin should be found in the theme.
		 *
		 * For example: 'your-plugin-templates'.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		protected $theme_template_directory = 'plugin-templates';

		/**
		 * Reference to the root directory path of this plugin.
		 *
		 * Can either be a defined constant, or a relative reference from where the subclass lives.
		 *
		 * e.g. YOUR_PLUGIN_TEMPLATE or plugin_dir_path( dirname( __FILE__ ) ); etc.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		protected $plugin_directory = 'YOUR_PLUGIN_DIR';

		/**
		 * Directory name where templates are found in this plugin.
		 *
		 * Can either be a defined constant, or a relative reference from where the subclass lives.
		 *
		 * e.g. 'templates' or 'includes/templates', etc.
		 *
		 * @since 1.1.0
		 *
		 * @var string
		 */
		protected $plugin_template_directory = 'templates';

		/**
		 * Internal use only: Store located template paths.
		 *
		 * @var array
		 */
		private $template_path_cache = array();

		/**
		 * Internal use only: Store variable names used for template data.
		 *
		 * Means unset_template_data() can remove all custom references from $wp_query.
		 *
		 * Initialized to contain the default 'data'.
		 *
		 * @var array
		 */
		private $template_data_var_names = array('data');

		/**
		 * Clean up template data.
		 *
		 * @since 1.2.0
		 */
		public function __destruct()
		{
			$this->unset_template_data();
		}

		/**
		 * Retrieve a template part.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Template slug.
		 * @param string $name Optional. Template variation name. Default null.
		 * @param bool   $load Optional. Whether to load template. Default true.
		 * @return string
		 */
		public function get_template_part($slug, $name = null, $load = true)
		{
			// Execute code for this part.
			do_action('get_template_part_' . $slug, $slug, $name);
			do_action($this->filter_prefix . '_get_template_part_' . $slug, $slug, $name);

			// Get files names of templates, for given slug and name.
			$templates = $this->get_template_file_names($slug, $name);

			// Return the part that is found.
			return $this->locate_template($templates, $load, false);
		}

		/**
		 * Make custom data available to template.
		 *
		 * Data is available to the template as properties under the `$data` variable.
		 * i.e. A value provided here under `$data['foo']` is available as `$data->foo`.
		 *
		 * When an input key has a hyphen, you can use `$data->{foo-bar}` in the template.
		 *
		 * @since 1.2.0
		 *
		 * @param mixed  $data     Custom data for the template.
		 * @param string $var_name Optional. Variable under which the custom data is available in the template.
		 *                         Default is 'data'.
		 * @return Gamajo_Template_Loader
		 */
		public function set_template_data($data, $var_name = 'data')
		{
			global $wp_query;

			$wp_query->query_vars[$var_name] = (object) $data;

			// Add $var_name to custom variable store if not default value.
			if ('data' !== $var_name) {
				$this->template_data_var_names[] = $var_name;
			}

			return $this;
		}

		/**
		 * Remove access to custom data in template.
		 *
		 * Good to use once the final template part has been requested.
		 *
		 * @since 1.2.0
		 *
		 * @return Gamajo_Template_Loader
		 */
		public function unset_template_data()
		{
			global $wp_query;

			// Remove any duplicates from the custom variable store.
			$custom_var_names = array_unique($this->template_data_var_names);

			// Remove each custom data reference from $wp_query.
			foreach ($custom_var_names as $var) {
				if (isset($wp_query->query_vars[$var])) {
					unset($wp_query->query_vars[$var]);
				}
			}

			return $this;
		}

		/**
		 * Given a slug and optional name, create the file names of templates.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Template slug.
		 * @param string $name Template variation name.
		 * @return array
		 */
		protected function get_template_file_names($slug, $name)
		{
			$templates = array();
			if (isset($name)) {
				$templates[] = $slug . '-' . $name . '.php';
			}
			$templates[] = $slug . '.php';

			/**
			 * Allow template choices to be filtered.
			 *
			 * The resulting array should be in the order of most specific first, to least specific last.
			 * e.g. 0 => recipe-instructions.php, 1 => recipe.php
			 *
			 * @since 1.0.0
			 *
			 * @param array  $templates Names of template files that should be looked for, for given slug and name.
			 * @param string $slug      Template slug.
			 * @param string $name      Template variation name.
			 */
			return apply_filters($this->filter_prefix . '_get_template_part', $templates, $slug, $name);
		}

		/**
		 * Retrieve the name of the highest priority template file that exists.
		 *
		 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
		 * inherit from a parent theme can just overload one file. If the template is
		 * not found in either of those, it looks in the theme-compat folder last.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $template_names Template file(s) to search for, in order.
		 * @param bool         $load           If true the template file will be loaded if it is found.
		 * @param bool         $require_once   Whether to require_once or require. Default true.
		 *                                     Has no effect if $load is false.
		 * @return string The template filename if one is located.
		 */
		public function locate_template($template_names, $load = false, $require_once = true)
		{

			// Use $template_names as a cache key - either first element of array or the variable itself if it's a string.
			$cache_key = is_array($template_names) ? $template_names[0] : $template_names;

			// If the key is in the cache array, we've already located this file.
			if (isset($this->template_path_cache[$cache_key])) {
				$located = $this->template_path_cache[$cache_key];
			} else {

				// No file found yet.
				$located = false;

				// Remove empty entries.
				$template_names = array_filter((array) $template_names);
				$template_paths = $this->get_template_paths();

				// Try to find a template file.
				foreach ($template_names as $template_name) {
					// Trim off any slashes from the template name.
					$template_name = ltrim($template_name, '/');

					// Try locating this template file by looping through the template paths.
					foreach ($template_paths as $template_path) {
						if (file_exists($template_path . $template_name)) {
							$located = $template_path . $template_name;
							// Store the template path in the cache.
							$this->template_path_cache[$cache_key] = $located;
							break 2;
						}
					}
				}
			}

			if ($load && $located) {
				load_template($located, $require_once);
			}

			return $located;
		}

		/**
		 * Return a list of paths to check for template locations.
		 *
		 * Default is to check in a child theme (if relevant) before a parent theme, so that themes which inherit from a
		 * parent theme can just overload one file. If the template is not found in either of those, it looks in the
		 * theme-compat folder last.
		 *
		 * @since 1.0.0
		 *
		 * @return mixed|void
		 */
		protected function get_template_paths()
		{
			$theme_directory = trailingslashit($this->theme_template_directory);

			$file_paths = array(
				10  => trailingslashit(get_template_directory()) . $theme_directory,
				100 => $this->get_templates_dir(),
			);

			// Only add this conditionally, so non-child themes don't redundantly check active theme twice.
			if (get_stylesheet_directory() !== get_template_directory()) {
				$file_paths[1] = trailingslashit(get_stylesheet_directory()) . $theme_directory;
			}

			/**
			 * Allow ordered list of template paths to be amended.
			 *
			 * @since 1.0.0
			 *
			 * @param array $var Default is directory in child theme at index 1, parent theme at 10, and plugin at 100.
			 */
			$file_paths = apply_filters($this->filter_prefix . '_template_paths', $file_paths);

			// Sort the file paths based on priority.
			ksort($file_paths, SORT_NUMERIC);

			return array_map('trailingslashit', $file_paths);
		}

		/**
		 * Return the path to the templates directory in this plugin.
		 *
		 * May be overridden in subclass.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		protected function get_templates_dir()
		{
			return trailingslashit($this->plugin_directory) . $this->plugin_template_directory;
		}
	}

	if (!defined('GSAPI_DIR')) {
		define('GSAPIDIR', plugin_dir_path(dirname(__FILE__)));
	}
	/**
	 * Template loader for GSAPI
	 *
	 * Only need to specify class properties here.
	 *
	 */
	class GS_Template_Loader extends Gamajo_Template_Loader
	{
		/**
		 * Prefix for filter names.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		protected $filter_prefix = 'gsapi_connector';

		/**
		 * Directory name where custom templates for this plugin should be found in the theme.
		 *
		 * @since 1.0.0
		 * @type string
		 */
		protected $theme_template_directory = 'templates';

		/**
		 * Reference to the root directory path of this plugin.
		 *
		 * @since 1.0.0
		 * @type string
		 */
		protected $plugin_directory = GSAPIDIR;
	}

endif;
