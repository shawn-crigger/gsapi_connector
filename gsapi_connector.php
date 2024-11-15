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

use GSAPI\Template\Loader;

if (!defined('ABSPATH')) {
	exit;
}

define('GSAPI_PATH', __DIR__);
define('GSAPI_URL', untrailingslashit(plugin_dir_url(__FILE__)));
define('GSAPI_TEXTDOMAIN', 'gsapi');

require_once GSAPI_PATH . '/vendor/autoload.php';

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$GLOBALS['gsapi_connector'] = new GSAPI_Connector();


/**
 * Grand Strand Connector for WordPress
 */
class GSAPI_Connector
{
	/**
	 * GSAPI API Key
	 * @var string|null
	 */
	protected $_api_key  = NULL;
	/**
	 * GSAPI API Password
	 * @var string|null
	 */
	protected $_password = NULL;
	/**
	 * Developer Debug Switch
	 * @var string|null
	 */
	protected $_debug  = FALSE;
	/**
	 * Google Maps API Key
	 * @var string|null
	 */
	protected $_gmaps_key = NULL;
	/**
	 * Holds the plugin settings
	 * @static
	 * @access     protected
	 * @var        array
	 */
	static protected $settings = NULL;
	/**
	 * Holds the plugin settings
	 * @var        array|null
	 */
	public $api_response = NULL;

	// ------------------------------------------------------------------------

	public function __construct()
	{
		self::get_settings();
		require_once GSAPI_PATH . '/helpers/helpers.php';

		$this->_api_key  = self::$settings['gsapi_apikey'] ?? NULL;
		$this->_password  = self::$settings['gsapi_password'] ?? NULL;
		$this->_api_key  = self::$settings['gsapi_apikey'] ?? NULL;
		$this->_gmaps_key = self::$settings['gmaps_key'] ?? NULL;

		define('GMAPSKEY', $this->_gmaps_key);

		if (is_admin()) {
			require(trailingslashit(__DIR__) . 'libraries/admin.php');
			$admin = new GSAPI_Connector_Admin();
		}
		add_action('init', array(&$this, 'create_cpt'), 1);
		add_action('init', array(&$this, 'add_rewrite_rules'), 3);
		add_filter('query_vars', array(&$this, 'add_query_vars'), 2);
		add_action('template_redirect', array(&$this, 'load_assets'));

		add_shortcode('gsapi_category', array(&$this, 'fetch_categories'));
		add_action('template_include',  array(&$this, 'change_template'));
		// add_filter('do_parse_request',  array(&$this, 'wporg_add_custom_query'), 10, 3);
		// add_filter('template_include',  array(&$this, 'gsapi_template_include'));
		// add_filter('post_type_link', array(&$this, 'modify_places_links'), 10, 2);
		// add_action('template_include',  array(&$this, 'gsplaces_page_template'), 99);
	}

	// ------------------------------------------------------------------------

	/**
   * Fitler to set the rewrite rules.
   * @static
   * @access public
	 */
	public function add_rewrite_rules()
	{

		add_rewrite_rule('^gscat/([^/]*)/$', 'index.php?cat=$matches[1]', 'top');
		add_rewrite_rule('^gscat/([^/]*)/page/([^/]*)/$', 'index.php?cat=$matches[1]&pagenum=$matches[2]', 'top');
		// add_rewrite_rule('^cat/([^/]*)/?', 'index.php?cat=$matches[1]', 'top');
		// add_rewrite_rule('^([^/]*)/ethiopian-restaurants-([0-9]{3,5})/$', 'index.php?pagename=$matches[1]&cat=$matches[2]', 'top');
		add_rewrite_rule('^pagenum/([^/]*)/?', 'index.php?pagenum=$matches[1]', 'top');
		add_rewrite_rule('^listing/([^/]*)/?', 'index.php?listing=$matches[1]', 'top');

		add_rewrite_rule('^gsplace/([^/]*)-([^/]*)/$', 'index.php?gsplace=$matches[1]&id=$matches[2]', 'top');
		// Convert ?cat=853 to '/cat-title-id'
		add_rewrite_rule('^locations/([^/]*)-([^/]*)-([0-9]{3,5})/$', 'index.php?gslocations=$matches[1]&id=$matches[2]', 'top');
		// add_rewrite_rule('^([^/]*)/([^/]*)-([0-9]{3,5})/$', 'index.php?category_name=$matches[1]&cat=$matches[2]&id=$matches[3]', 'top');
		add_rewrite_rule('^gscat/([^/]*)-([^/]*)/$', 'index.php?gscat=$matches[1]&id=$matches[2]', 'top');
		add_filter('document_title_parts', array(&$this, 'set_page_title'), 10, 1);
	}

	// ------------------------------------------------------------------------


	public function wporg_add_custom_query( $do_parse, $wp, $extra_query_vars ) {
			if ( 'gsplace' === $extra_query_vars['custom_arg'] ) {
					return false;
			}

			return $do_parse;
	}



	/**
	 * Add possible query vars
	 * @param  array $query_vars
	 * @return void
	 */
	function add_query_vars($query_vars)
	{
		$query_vars[] = 'sort';
		$query_vars[] = 'listing';
		$query_vars[] = 'pagenum';
		$query_vars[] = 'perpage';
		$query_vars[] = 'gscat';
		$query_vars[] = 'cat';
		$query_vars[] = 'gsplace';
		return $query_vars;
	}

	// ------------------------------------------------------------------------

	/**
	 * WP Filter to set the page title to a more SEO friendly version.
	 * @see document_title_parts
	 * @return void
	 */
	public function set_page_title()
	{
		global $wp_query;

		$return = '';

		if ($this->_debug === TRUE) {
			$query_debug = json_encode($wp_query);
			echo "<script type='text/javascript'>";
			echo "console.log({$query_debug});";
			// print_r($wp_query);
			// echo "-->";
			echo "</script>";
		}


		if (isset($wp_query->query) && isset($wp_query->query['post_type'])) {

			$site_title = get_bloginfo('name');
			if ($wp_query->query['post_type'] == 'gsplace') {
				$post_title = $this->api_response[0]->name;
				$post_title = $site_title . '| About ' . $post_title;
				$return = $post_title;
			} elseif ($wp_query->query['post_type'] == 'gscat') {
				$post_title = $wp_query->query['name'];
				$post_title = $site_title . ' | Business Listings in ' . $post_title;
				$return = $post_title;
			}
		}

		return array($return, null, null, null);
	}

	// ------------------------------------------------------------------------

	function gsplaces_page_template( $template ) {
			$file_name = 'single_gsplace.php';

			if ( is_page( 'gsplace' ) ) {
					if ( locate_template( $file_name ) ) {
							$template = locate_template( $file_name );
					} else {
							// Template not found in theme's folder, use plugin's template as a fallback
							$template = dirname( __FILE__ ) . '/templates/' . $file_name;
					}
			}

			return $template;
	}


	// ------------------------------------------------------------------------

	function change_template($template)
	{
		global $wp_query;

		if (get_query_var('gsplace', false) !== false) {
			$this->api_response = $this->_fetch_detail();
			if (file_exists(get_template_directory(__FILE__) . '/gsapi/templates/gsplace.php')) {
				$newTemplate = get_template_directory(__FILE__) . '/gsapi/templates/gsplace.php';
				//Check plugin directory next
			} else {
				$newTemplate = plugin_dir_path(__FILE__) . '/templates/gsplace.php';
			}
			$template = $newTemplate;
		}

		if (get_query_var('gscat', false) !== false) {
			$id  = id_from_slug(get_query_var('gscat'));
			$atts= array(
				'category' => $id,
			);
			return $this->fetch_categories($atts, null);
			/*
			if (file_exists(get_template_directory(__FILE__) . '/gsapi/templates/listings.php')) {
				$newTemplate = get_template_directory(__FILE__) . '/gsapi/templates/listings.php';
				//Check plugin directory next
			} else {
				$newTemplate = plugin_dir_path(__FILE__) . '/templates/listings.php';
			}
			$template = $newTemplate;
			*/
		}

		$wp_query->is_404 = false;
		header("HTTP/2.0 200 OK");

		return $template;
	}

	// ------------------------------------------------------------------------


	public function _fetch_detail()
	{
		//global $wp_query;

		$id  = id_from_slug(get_query_var('gsplace'));
		$url = 'https://grandstrandapi.com/api/v1/places/detail/' . $id;

		$data = $this->_get_json($url, array('med' => 4, 'des' => 1));

		$data = (array) $data;
		$data['error'] = NULL;
		if (isset($data['message']) && strlen($data['message']) > 0)
		{
			$data['error'] = $data['message'];

			return $data;
		}

		$data['details'] = $data['details'][0];
		return $data;
	}

	// ------------------------------------------------------------------------

	private function get_title_name()
	{
		//global $wp_query;

		$id  = id_from_slug(get_query_var('gsplace'));

		$url = 'https://grandstrandapi.com/api/v1/places/detail/' . $id;
		$args['med'] = 0;
		$head = $this->_get_json($url, $args);
		$head = (array) $head;

		$head['error'] = NULL;
		if (isset($head['message'])) {
			$head['error'] = $head['message'];
		} else {
			$head['details'] = $head['details'][0];
		}

		return $head['details']->name;
	}

	// ------------------------------------------------------------------------

	public function load_assets()
	{
		global $wp_query;
		$addSlick = false;
		if (isset($wp_query->query) && isset($wp_query->query['post_type'])) {

			if ($wp_query->query['post_type'] == 'gsplace') {
				$addSlick = true;
			}
		}
		$ver = time();
		// Get the Path to this plugin's folder
		$path = plugin_dir_url(__FILE__) . 'assets/';
		wp_enqueue_script('listing-js', $path . 'js/connector.js', array('jquery'), $ver);
		wp_enqueue_style('bs-grid',     $path . 'css/bootstrap-grid.min.css', array(), $ver);
		wp_enqueue_style('fontawesome', $path . 'css/font-awesome.min.css', array(), $ver);
		wp_enqueue_style('listing-css', $path . 'css/connector.css', array(), $ver);
		if ($addSlick) {
			wp_enqueue_script('slick-js', $path . 'slick/slick.min.js', array('jquery'), '1.6.0', true);
			wp_add_inline_script( 'slick-js', slick_dom(), 'after');
			wp_enqueue_style('slick-css', $path . 'slick/slick.css', '1.6.0', 'all');
			wp_enqueue_style('slick-theme-css', $path .  'slick/slick-theme.css', '1.6.0', 'all');
		}
		wp_enqueue_style('lity-css', $path . 'lity/dist/lity.min.css', '2.3.1', 'all');
		wp_enqueue_script('lity-js', $path . 'lity/dist/lity.min.js', array('jquery'), '2.3.1', true);
	}

	// ------------------------------------------------------------------------

	private function create_subcats($category = 0, $parent = 0)
	{
		$args = array('test' => 'no');
		$current = $category;
		/*
		if ($parent > 0) {
			$args['pid'] = $parent;
			$current = $parent;
		}
		*/
		//		dump($current, $category, $parent);

		$url  = 'https://grandstrandapi.com/api/v1/category/' . $category;
		$data = $this->_get_json($url, $args);

		if (!isset($data->category_info)) return;

		$select = "<select id=\"subcats\" name=\"subcats\">\n";
		$cats   = '';
		$hasParent = false;
		if (isset($data->category_info)) {
			foreach ($data->category_info as $row) {
				$parentId = $row->pid;
				if (is_numeric($parentId) && $parentId > 0) {
					if ($parentId == $current) {
						$parentSlug = make_slug_id($row->name, $parentId);
					}
				}
				$hasParent = ($row->id == $row->pid) ? true : $hasParent;
				$sel = ($current == $row->id) ? ' selected="selected" ' : NULL;
				$slug = make_slug_id($row->name,$row->id);
				$cats .= "<option {$sel} value=\"{$slug}\" >{$row->plural}</option>\n";
			}
		}

		if ( ! $hasParent && $current != 845) {
      $cats = "<option value=\"{$parentSlug}\">Back to Parent</option>\n" . $cats;
    }

		if (isset($data->subcategories)) {
			foreach ($data->subcategories as $row) {
				$sel = ($current == $row->id) ? ' selected="selected" ' : NULL;
				$slug = make_slug_id($row->name, $row->id);
				$cats .= "<option {$sel} value=\"{$slug}\" >{$row->plural}</option>\n";
			}
		}
		$cats = $select . $cats;
		$cats .= "</select>";
		return $cats;
	}

	// ------------------------------------------------------------------------

	// ------------------------------------------------------------------------

	/**
	 * Output business listings from the GSAPI for the category, sorted by populatity, paginated if turned on or not.
	 *
	 * [gsapi_category category="" sort="popular" paginate="yes" perpage="20"]
	 *
	 * @param  array  $atts
	 * @param  string $content
	 * @return string
	 */
	function fetch_categories($atts, $content = null)
	{
		$lat = '';
		$lng = '';
		/*
		if (get_query_var('sort') == 'distance') {
			list($lat, $lng) = explode(',', $_COOKIE['latlng']);
			//echo "Using distance ".$lat." AND ".$lng;
			if (!$lat || !$lng) {
				$lat = '33.7025';
				$lng = '-78.8810';
			}
		}
		*/

		$atts = shortcode_atts(
			array(
				'category' => 0,
				'sort'     => 'popular',
				'paginate' => 'yes',
				'perpage'  => 20,
				'subcats'  => 'yes',
				'parent'   => 0,
				'lat'      => '33.65799330',
				'lng'      => '-79.02494050',
			),
			$atts
		);

		extract($atts);
		$sort     = in_array($sort, array('popular', 'distance')) ? 'popular' : $sort;
		$paginate = in_array($paginate, array('yes', 'no')) ? 'yes' : $paginate;
		$subcats  = in_array($subcats,  array('yes', 'no')) ? 'yes' : $subcats;
		$category = (int) $category;
		$parent   = (int) $parent;
		$cat      = (int) get_query_var('cat');
		$url      = 'https://grandstrandapi.com/api/v1/places/' . $category;

		$sorted = get_query_var('sort');
		$sort   = in_array($sorted, array('popular', 'distance')) ? $sorted : $sort;

		$args = array(
			'pop' => ($sort == 'popular') ? 1 : 0,
		);

		if ($parent > 0 && 0 >= $cat) {
			$args['pid'] = (int) $parent;
		} elseif ($cat > 0) {
			echo $cat;
			$url = 'https://grandstrandapi.com/api/v1/places/' . $cat;
			$category = $cat;
			$parent = 0;
		}
		if (get_query_var('sort') == 'distance') {
			if (is_string($lat) or is_float($lat)) {
				$args['lat'] = $lat;
			}

			if (is_string($lng) or is_float($lng)) {
				$args['lng'] = $lng;
			}
		}

		if ('yes' == $paginate) {
			$pp         = (int) $perpage;
			$page       = (int) get_query_var('pagenum');
			$current    = (0 >= $page) ? 1 : $page;
			$page       = ($current - 1) * $pp;
			$args['pp'] = $pp;
			$args['st'] = $page;
		}
		if ($this->_debug === TRUE) {
			echo "<pre>";
			print_r($args);
			echo "</pre>";
		}
		$data  = $this->_get_json($url, $args);

		if (isset($data->message) && 'No places found.' == $data->message) {
			echo 'No places found.';
			return;
		}

		$total = isset($data->extras[0]->total) ? $data->extras[0]->total : 0;
		$pages = floor($total / $pp);

		$next = ($current >= $pages) ? NULL : ($current + 1);
		$prev = (1 >= $current) ? NULL : ($current - 1);
		if ('yes' == $paginate) {
			$data->places['paginate'] = $paginate;
			$data->places['pages']    = $pages;
			$data->places['current']  = $current;
			$data->places['next']     = $next;
			$data->places['prev']     = $prev;
			$data->places['perpage']  = $perpage;
		}

		$data->places['cats'] = $this->create_subcats($category, $parent);
		$data->places['category'] = $category;

		$data = load_view('listing.php', $data->places, 'gsapi_connector');
		echo $data;

		return $content;
	}

	// ------------------------------------------------------------------------

	/**
	 * Queries the GSAPI and returns the JSON results.
	 *
	 * @access  private
	 * @param   array  $args Array of arguments to send to the GSAPI
	 * @param   string $url  URL of the GSAPI endpoint
	 * @return  object   FALSE on failure, array on success
	 */
	private function _get_json($url = 'https://grandstrandapi.com/api/v1/places/', $args = array())
	{
		$token = $this->_get_token();

		$request = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'timeout'       => 45,
				'sslverify'     => true,
				'Accept'        => 'application/json;ver=1.0',
				'Content-Type'  => 'application/json; charset=UTF-8',
				'Host'          => 'grandstrandapi.com'
			),
			'method'  => 'GET',
		);

		if (!empty($args) && is_array($args)) {
			$url = add_query_arg($args, $url);
		} else {
			$request['body'] = json_encode($args);
		}
		//dump($url, $request['body']);

		$response = wp_remote_get($url, $request);
		$response = json_decode($response['body']);

		// if ( ! is_object( $response ) ) return FALSE;
		return $response;
	}

	// ------------------------------------------------------------------------
	/**
	 * Returns auth token
	 * @access  private
	 * @return string Bearer Token to access the GSAPI.
	 */
	private function _get_token()
	{
		// get token
		$url = 'https://grandstrandapi.com/api/v1/authenticate';
		$apiKey = urlencode($this->_api_key);
		$password = urlencode($this->_password);
		$args = array(
			'headers' => array('content-type' => 'application/x-www-form-urlencoded'),
			'body' => "api_key={$apiKey}&password={$password}",
		);
		$request = wp_remote_post($url, $args);

		if (is_wp_error($request)) return false;
		$body = wp_remote_retrieve_body($request);
		$data = json_decode($body);
		if (!is_object($data) or !isset($data->token)) {
			// echo __FUNCTION__ . ':' . __LINE__ . '<br>' . __FILE__ . '<br>';
			// dump( $request, $body );
			return false;
		}

		return $data->token;
	}

	// ------------------------------------------------------------------------

	/**
	 * Register Business Listings Custom Post Type
	 * @return void
	 */
	public function create_cpt()
	{
		$labels = array(
			'name'                  => _x('Business Listings', 'Post Type General Name', 'gsapi'),
			'singular_name'         => _x('Business Listing', 'Post Type Singular Name', 'gsapi'),
			'menu_name'             => __('GSAPI', 'gsapi'),
			'name_admin_bar'        => __('GSAPI', 'gsapi'),
			'archives'              => __('Item Archives', 'gsapi'),
			'attributes'            => __('Item Attributes', 'gsapi'),
			'parent_item_colon'     => __('Parent Item:', 'gsapi'),
			'all_items'             => __('All Items', 'gsapi'),
			'add_new_item'          => __('Add New Item', 'gsapi'),
			'add_new'               => __('Add New', 'gsapi'),
			'new_item'              => __('New Item', 'gsapi'),
			'edit_item'             => __('Edit Item', 'gsapi'),
			'update_item'           => __('Update Item', 'gsapi'),
			'view_item'             => __('View Item', 'gsapi'),
			'view_items'            => __('View Items', 'gsapi'),
			'search_items'          => __('Search Item', 'gsapi'),
			'not_found'             => __('Not found', 'gsapi'),
			'not_found_in_trash'    => __('Not found in Trash', 'gsapi'),
			'featured_image'        => __('Featured Image', 'gsapi'),
			'set_featured_image'    => __('Set featured image', 'gsapi'),
			'remove_featured_image' => __('Remove featured image', 'gsapi'),
			'use_featured_image'    => __('Use as featured image', 'gsapi'),
			'insert_into_item'      => __('Insert into item', 'gsapi'),
			'uploaded_to_this_item' => __('Uploaded to this item', 'gsapi'),
			'items_list'            => __('Items list', 'gsapi'),
			'items_list_navigation' => __('Items list navigation', 'gsapi'),
			'filter_items_list'     => __('Filter items list', 'gsapi'),
		);
		$args = array(
			'label'                 => __('Business Listing', 'gsapi'),
			'description'           => __('GSAPI Business Listings', 'gsapi'),
			'labels'                => $labels,
			'supports'              => array('title'),
			'taxonomies'            => array(),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 95,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => false,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'rewrite'				        => array('slug' => 'gsplace'),
			'capability_type'       => 'post',
			'menu_icon'             => 'dashicons-location-alt',
		);
		register_post_type('gsplace', $args);

		/*
		$labels = array(
			'name'                  => _x('GSAPI Category', 'Post Type General Name', 'gsapi'),
			'singular_name'         => _x('GSAPI Category', 'Post Type Singular Name', 'gsapi'),
			'menu_name'             => __('GSAPI Categories', 'gsapi'),
			'name_admin_bar'        => __('GSAPI Categories', 'gsapi'),
			'archives'              => __('Item Archives', 'gsapi'),
			'attributes'            => __('Item Attributes', 'gsapi'),
			'parent_item_colon'     => __('Parent Item:', 'gsapi'),
			'all_items'             => __('All Items', 'gsapi'),
			'add_new_item'          => __('Add New Item', 'gsapi'),
			'add_new'               => __('Add New', 'gsapi'),
			'new_item'              => __('New Item', 'gsapi'),
			'edit_item'             => __('Edit Item', 'gsapi'),
			'update_item'           => __('Update Item', 'gsapi'),
			'view_item'             => __('View Item', 'gsapi'),
			'view_items'            => __('View Items', 'gsapi'),
			'search_items'          => __('Search Item', 'gsapi'),
			'not_found'             => __('Not found', 'gsapi'),
			'not_found_in_trash'    => __('Not found in Trash', 'gsapi'),
			'featured_image'        => __('Featured Image', 'gsapi'),
			'set_featured_image'    => __('Set featured image', 'gsapi'),
			'remove_featured_image' => __('Remove featured image', 'gsapi'),
			'use_featured_image'    => __('Use as featured image', 'gsapi'),
			'insert_into_item'      => __('Insert into item', 'gsapi'),
			'uploaded_to_this_item' => __('Uploaded to this item', 'gsapi'),
			'items_list'            => __('Items list', 'gsapi'),
			'items_list_navigation' => __('Items list navigation', 'gsapi'),
			'filter_items_list'     => __('Filter items list', 'gsapi'),
		);
		$args = array(
			'label'                 => __('Categories', 'gsapi'),
			'description'           => __('GSAPI Categories', 'gsapi'),
			'labels'                => $labels,
			'supports'              => array(),
			'taxonomies'            => array(),
			'hierarchical'          => true,
			'public'                => true,
			'show_ui'               => false,
			'show_in_menu'          => false,
			'menu_position'         => 95,
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => false,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'rewrite'				        => array('slug' => 'cat'),
			'capability_type'       => 'post',
			'menu_icon'             => 'dashicons-location-alt',
		);
		register_post_type('gscat', $args);
		*/
		$this->create_taxonomies();
		flush_rewrite_rules();
	}

	// ------------------------------------------------------------------------

	/**
	 * Gets the settings.
	 * @static
	 * @return     array  The settings.
	 */
	static public function get_settings()
	{
		if (!self::$settings) {
			self::$settings = get_option('gsapi_settings');
		}
		return self::$settings;
	}

	// ------------------------------------------------------------------------

	/**
	 * Register Locations and Categories Custom Taxonomies
	 *
	 * @return void
	 */
	function create_taxonomies()
	{
		$labels = array(
			'name'                       => _x('Locations', 'Taxonomy General Name', 'gsapi'),
			'singular_name'              => _x('Location', 'Taxonomy Singular Name', 'gsapi'),
			'menu_name'                  => __('Locations', 'gsapi'),
			'all_items'                  => __('All Items', 'gsapi'),
			'parent_item'                => __('Parent Item', 'gsapi'),
			'parent_item_colon'          => __('Parent Item:', 'gsapi'),
			'new_item_name'              => __('New Item Name', 'gsapi'),
			'add_new_item'               => __('Add New Item', 'gsapi'),
			'edit_item'                  => __('Edit Item', 'gsapi'),
			'update_item'                => __('Update Item', 'gsapi'),
			'view_item'                  => __('View Item', 'gsapi'),
			'separate_items_with_commas' => __('Separate items with commas', 'gsapi'),
			'add_or_remove_items'        => __('Add or remove items', 'gsapi'),
			'choose_from_most_used'      => __('Choose from the most used', 'gsapi'),
			'popular_items'              => __('Popular Items', 'gsapi'),
			'search_items'               => __('Search Items', 'gsapi'),
			'not_found'                  => __('Not Found', 'gsapi'),
			'no_terms'                   => __('No items', 'gsapi'),
			'items_list'                 => __('Items list', 'gsapi'),
			'items_list_navigation'      => __('Items list navigation', 'gsapi'),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => false,
			'show_admin_column'          => false,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => array('slug' => 'gslocations'),
		);
		register_taxonomy('gslocations', array('gsplace', 'gscat'), $args);

		$labels = array(
			'name'                       => _x('Categories', 'Taxonomy General Name', 'gsapi'),
			'singular_name'              => _x('Category', 'Taxonomy Singular Name', 'gsapi'),
			'menu_name'                  => __('Categories', 'gsapi'),
			'all_items'                  => __('All Items', 'gsapi'),
			'parent_item'                => __('Parent Item', 'gsapi'),
			'parent_item_colon'          => __('Parent Item:', 'gsapi'),
			'new_item_name'              => __('New Item Name', 'gsapi'),
			'add_new_item'               => __('Add New Item', 'gsapi'),
			'edit_item'                  => __('Edit Item', 'gsapi'),
			'update_item'                => __('Update Item', 'gsapi'),
			'view_item'                  => __('View Item', 'gsapi'),
			'separate_items_with_commas' => __('Separate items with commas', 'gsapi'),
			'add_or_remove_items'        => __('Add or remove items', 'gsapi'),
			'choose_from_most_used'      => __('Choose from the most used', 'gsapi'),
			'popular_items'              => __('Popular Items', 'gsapi'),
			'search_items'               => __('Search Items', 'gsapi'),
			'not_found'                  => __('Not Found', 'gsapi'),
			'no_terms'                   => __('No items', 'gsapi'),
			'items_list'                 => __('Items list', 'gsapi'),
			'items_list_navigation'      => __('Items list navigation', 'gsapi'),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => false,
			'show_admin_column'          => false,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'supports'                   => array('title'),
			'rewrite'                    => array('slug' => 'gscat'),
		);
		register_taxonomy('gscat', array('gsplace'), $args);

	}
}// GSAPI_Connector();
