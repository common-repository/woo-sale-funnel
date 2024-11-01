<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://makewebbetter.com
 * @since             1.0.0
 * @package           Woo_Sale_Funnel
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce Sale Funnel
 * Plugin URI:        makewebbetter.com/woo-sale-funnel
 * Description:       An easy and effective way for implementing sale funnels in WooCommerce by MWB.
 * Version:           1.0.0
 * Author:            makewebbetter 
 * Author URI:        http://makewebbetter.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mwb-sale-funnel
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
define('MWB_PLUGIN_URL', plugin_dir_url(__FILE__));
$activated = true;

if (function_exists('is_multisite') && is_multisite())
{
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) || !is_plugin_active( 'forge/forge.php' ) )
	{
		$activated = false;
	}
}
else
{
	if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) || !in_array('forge/forge.php', apply_filters('active_plugins', get_option('active_plugins'))))
	{
		$activated = false;
	}
}

if($activated)
{

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-mwb-sale-funnel.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_mwb_sale_funnel() {

		$plugin = new Mwb_Sale_Funnel();
		$plugin->run();

	}
	run_mwb_sale_funnel();

	add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'mwb_sf_plugin_settings_link');

	function mwb_sf_plugin_settings_link( $links ) 
	{
		$links[] = '<a href="' .
			admin_url( 'edit.php?post_type=mwbfunnel' ) .
			'">' . __('Settings',"mwb-sale-funnel") .'</a>';
		return $links;
	}
}
else {
	/**
	 * Show warning message if woocommerce is not install
	 * @since 1.0.0
	 * @name mwb_wuc_plugin_error_notice()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */

	function mwb_sf_plugin_error_notice()
 	{ ?>
 		<div class="error notice is-dismissible">
 			<p><?php _e( 'Woocommerce or Forge is not activated, Please activate Woocommerce or Forge first to install WooCommerce Sale Funnel', 'mwb-sale-funnel' ); ?></p>
   		</div>
   		<style>
   		#message{display:none;}
   		</style>
   	<?php 
 	} 

 	add_action( 'admin_init', 'mwb_sf_plugin_deactivate' );  

 	/**
 	 * Call Admin notices
 	 * 
 	 * @name mwb_wocuf_pro_plugin_deactivate()
 	 * @author makewebbetter<webmaster@makewebbetter.com>
 	 * @link http://www.makewebbetter.com/
 	 */ 	
  	function mwb_sf_plugin_deactivate()
	{
	   deactivate_plugins( plugin_basename( __FILE__ ) );
	   add_action( 'admin_notices', 'mwb_sf_plugin_error_notice' );
	}
}
