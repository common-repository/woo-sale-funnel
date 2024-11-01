<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://makewebbetter.com
 * @since      1.0.0
 *
 * @package    woo_Sale_Funnel
 * @subpackage woo_Sale_Funnel/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    woo_Sale_Funnel
 * @subpackage woo_Sale_Funnel/includes
 * @author     Make Web Better <webmaster@makewebbetter.com>
 */
class Mwb_Sale_Funnel {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Mwb_Sale_Funnel_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'mwb-sale-funnel';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Mwb_Sale_Funnel_Loader. Orchestrates the hooks of the plugin.
	 * - Mwb_Sale_Funnel_i18n. Defines internationalization functionality.
	 * - Mwb_Sale_Funnel_Admin. Defines all hooks for the admin area.
	 * - Mwb_Sale_Funnel_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mwb-sale-funnel-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mwb-sale-funnel-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mwb-sale-funnel-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-mwb-sale-funnel-public.php';

		$this->loader = new Mwb_Sale_Funnel_Loader();

		/**
		 * Load forge manager class, to extend the forge functionalities.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mwb-sale-funnel-forge-manager.php';

		new Mwb_Sale_Funnel_Forge();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Mwb_Sale_Funnel_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Mwb_Sale_Funnel_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Mwb_Sale_Funnel_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_filter('manage_edit-shop_order_columns',$plugin_admin,'mwb_sf_add_columns_to_admin_orders',11);
		$this->loader->add_action('manage_shop_order_posts_custom_column',$plugin_admin,'mwb_sf_add_upsell_orders_to_parent',10,2);
		$this->loader->add_filter('restrict_manage_posts',$plugin_admin,'mwb_sf_restrict_manage_posts');
		$this->loader->add_filter('request',$plugin_admin,'mwb_sf_request_query');
		
		$this->loader->add_filter('preview_post_link',$plugin_admin,'mwb_sf_preview_post_link', 10, 2);
		$this->loader->add_filter('post_row_actions',$plugin_admin,'mwb_sf_post_row_actions', 10, 2);
		$this->loader->add_filter('manage_edit-mwbfunnel_columns',$plugin_admin,'mwb_sf_mwbfunnel_columns');
		$this->loader->add_action( 'manage_mwbfunnel_posts_custom_column', $plugin_admin, 'mwb_sf_mwbfunnel_columns_data', 10, 2 );

		$this->loader->add_filter( 'bulk_actions-edit-mwbfunnel', $plugin_admin, 'mwb_sf_mwbfunnel_bulk_actions' );
		$this->loader->add_filter( 'handle_bulk_actions-edit-mwbfunnel', $plugin_admin, 'mwb_sf_handle_mwbfunnel_bulk_actions' , 10, 3 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Mwb_Sale_Funnel_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'template_redirect', $plugin_public, 'mwb_sf_add_product_to_cart' );
		$this->loader->add_action( 'woocommerce_get_checkout_order_received_url', $plugin_public, 'mwb_sf_get_checkout_order_received_url', 10, 2 );
		$this->loader->add_action( 'woocommerce_before_checkout_billing_form', $plugin_public, 'mwb_sf_woocommerce_before_checkout_billing_form');
		$this->loader->add_action( 'woocommerce_order_items_table',$plugin_public,'mwb_sf_pro_order_items_table' );
		$this->loader->add_filter('woocommerce_get_order_item_totals',$plugin_public,'mwb_sf_get_order_item_totals',10 ,2);
		$this->loader->add_filter('woocommerce_order_get_total',$plugin_public,'mwb_sf_get_new_total',10,2);
		$this->loader->add_filter('woocommerce_my_account_my_orders_query',$plugin_public,'mwb_sf_my_account_my_orders_query',11,1);
		$this->loader->add_filter('woocommerce_get_item_count',$plugin_public,'mwb_sf_get_item_count',11,3);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Mwb_Sale_Funnel_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
