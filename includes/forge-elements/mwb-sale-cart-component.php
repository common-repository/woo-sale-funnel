<?php

/**
* Cart Return Function
*
* @since     1.0.0
* @param    array    $atts    Attributes Array
*/
$funnel_step_id_cart = 0;
$cart_button_text = '';
function mwb_sf_forge_element_cart( $atts, $content = null ) {
	global $post;
	$output = "";
	if( !isset($post)) {
		if( isset($_POST['postid'])) {
			$post = get_post( $_POST['postid'] );
		}
	}
	if( isset($post) && $post->post_type == 'mwbfunnelstep') {
		$fstep_id = $post->ID;


		$funnel_id = get_post_meta( $fstep_id, 'mwb_funnel_step_active', true);
		if( isset($funnel_id) && $funnel_id != null ) {
			$funnel_steps = get_post_meta( $funnel_id, 'mwb_funnel_steps', true);
						
			foreach ($funnel_steps as $key => $value) {
				if( $value[0] == $fstep_id ) {
					break;
				}
			}
			if( isset($funnel_steps[$key+1]) ) {
				wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );
				$attributes = extract(shortcode_atts(array(
					'sf_cart_checkout_button_text' => 'Proceed to checkout'
					),
				$atts));
				$GLOBALS['funnel_step_id_cart'] = $funnel_steps[$key+1][0];
				$GLOBALS['cart_button_text'] = $sf_cart_checkout_button_text;
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
				remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );
				add_action( 'woocommerce_proceed_to_checkout', 'mwb_sf_navigate_forge', 20 );
				add_action( 'woocommerce_cart_collaterals', 'mwb_sf_cart_total_template', 10 );
			}
		}
	}
	return do_shortcode('[woocommerce_cart]');
}
/**
* Including Cart Template
*
* @since     1.0.0
*/
function mwb_sf_cart_total_template() {
	wc_get_template( 'cart/cart-totals.php' );
}
/**
* Cart Button Text
*
* @since     1.0.0
*/
function mwb_sf_navigate_forge() {
	?>
		<a href="<?php echo get_permalink($GLOBALS['funnel_step_id_cart']);?>" class="checkout-button button alt wc-forward">
			<?php echo $GLOBALS['cart_button_text']; ?>
		</a>
	<?php
}
/**
* Cart Component
*
* @since     1.0.0
* @param     array 		$data 		Component Array    
*/
add_filter('forge_elements', 'mwb_sf_forge_cart_metadata');
function mwb_sf_forge_cart_metadata($data){
	$data['cart_felement'] = array(
		'title' => __('Cart Element', 'mwb-sale-funnel'),
		'description' => __('Displays WooCommerce Cart', 'mwb-sale-funnel'),
		'group' => 'layout',
		'callback' => 'mwb_sf_forge_element_cart',
		'fields' => array(
			array(
				'name' => 'sf_cart_checkout_button_text',
				'label' => __('Checkout Button Text', 'mwb-sale-funnel'),
				'type' => 'text',
				'default' => 'Proceed to checkout',
			),
		)
	);
	return $data;
}