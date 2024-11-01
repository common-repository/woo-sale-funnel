<?php
/**
 * Extend the forge functionality.
 *
 * @link       http://makewebbetter.com
 * @since      1.0.0
 *
 * @package    woo_Sale_Funnel
 * @subpackage woo_Sale_Funnel/includes
 */

/**
 * Extends the elements of forge to add funnel support.
 *
 * @package    woo_Sale_Funnel
 * @subpackage woo_Sale_Funnel_Forge/includes
 * @author     Make Web Better <webmaster@makewebbetter.com>
 */
class Mwb_Sale_Funnel_Forge {

	public function __construct(){

		$this->load_elements();
	}
	/**
	 * Includes all forge components
	 *
	 * @since      1.0.0
	 */
	public function load_elements(){

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forge-elements/mwb-first-product-component.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forge-elements/mwb-sale-cart-component.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forge-elements/mwb-sale-checkout-component.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forge-elements/mwb-sale-navigator-button-component.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forge-elements/mwb-sale-one-click-upsell-component.php';
	}
}