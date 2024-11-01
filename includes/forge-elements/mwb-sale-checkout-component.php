<?php
/**
* Checkout Return Function
*
* @since     1.0.0
* @param    array    $atts    Attributes Array
*/
function mwb_sf_forge_element_checkout( $atts, $content = null ) {
	return do_shortcode('[woocommerce_checkout]');
}
/**
* Checkout Component
*
* @since     1.0.0
* @param     array 		$data 		Component Array    
*/
add_filter('forge_elements', 'mwb_sf_forge_checkout_metadata');
function mwb_sf_forge_checkout_metadata($data){
	$data['checkout_felement'] = array(
		'title' => __('Checkout Element', 'mwb-sale-funnel'),
		'description' => __('Displays WooCommerce Checkout', 'mwb-sale-funnel'),
		'group' => 'layout',
		'callback' => 'mwb_sf_forge_element_checkout',
		'fields' => array(
		
		)
	);
	return $data;
}