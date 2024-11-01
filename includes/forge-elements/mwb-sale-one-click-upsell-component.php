<?php 
$upsell_pid = 0;
$skip_url = '';
/**
* Upsell Return Function
*
* @since     1.0.0
* @param    array    $atts    Attributes Array
*/
function mwb_sf_forge_element_upsell($atts, $content = null) {
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
			
			$attributes = extract(shortcode_atts(array(
				'mwb_search_fupsell' 			=> '',
				'mwb_fsku_upsell'				=> '1',
				'mwb_frating_upsell'			=> '1',
				'mwb_fshort_upsell'				=> '1',
				'mwb_flong_upsell'				=> '1',
				'mwb_ftitle_upsell'				=> '1',
				'mwb_fprice_upsell'				=> '1',
				'mwb_fdatatabs_upsell'			=> '1',
				'mwb_frelated_upsell'			=> '1',
				'mwb_fupsell_upsell'			=> '1'
				),
			$atts));
			
			if( (isset($_GET['okey']) && $_GET['okey'] != null) || isset($_REQUEST['forge_layout']) || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'forge_request_save_form')) {
				if(isset($mwb_search_fupsell) && $mwb_search_fupsell != null && $mwb_search_fupsell ) {

					$product = wc_get_product($mwb_search_fupsell);
					if( isset($product) && $product != null ) {
						$product_type = $product->get_type();
						if( $product_type == 'simple' ) {
							$funnel_steps = get_post_meta( $funnel_id,'mwb_funnel_steps', true);
									
							foreach ($funnel_steps as $key => $value) {
								if( $value[0] == $fstep_id ) {
									break;
								}
							}
							if( isset($funnel_steps[$key+1]) ) {
								$order_key = isset($_GET['okey']) ? '/?okey='.$_GET['okey']:'';
								$redirect = get_permalink($funnel_steps[$key+1][0]).$order_key;
								$GLOBALS['skip_url'] = $redirect;
							}
							else {
								$order_key = isset($_GET['okey']) ? $_GET['okey']:'';
								$order_id = wc_get_order_id_by_order_key($order_key);
								$order = wc_get_order($order_id);
								if( $order ) {
									$GLOBALS['skip_url'] = $order->get_checkout_order_received_url();
								}
								else {
									$GLOBALS['skip_url'] = wc_get_page_permalink('shop');
								}
								
							}
							if ( $mwb_frating_upsell != '1' ) {
								remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
							}
							if ( $mwb_fshort_upsell != '1' ) {
								remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
							}
							if ( $mwb_fsku_upsell != '1' ) {
								remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
							}
							if ( $mwb_ftitle_upsell != '1' ) {
								remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
							}
							if ( $mwb_fprice_upsell != '1' ) {
								remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
							}
							if ( $mwb_fdatatabs_upsell != '1' ) {
								remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
							}
							$GLOBALS['upsell_pid'] = $mwb_search_fupsell;
							remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
							
							remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
							
							remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
							
							add_action('woocommerce_single_product_summary', 'mwb_sf_simple_add_to_cart',31);
							$output .=do_shortcode('[product_page id="'.$mwb_search_fupsell.'"]');
							if ( current_theme_supports( 'wc-product-gallery-zoom' ) ) {
								wp_enqueue_script( 'zoom' );
							}
							if ( current_theme_supports( 'wc-product-gallery-slider' ) ) {
								wp_enqueue_script( 'flexslider' );
							}
							if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
								wp_enqueue_script( 'photoswipe-ui-default' );
								wp_enqueue_style( 'photoswipe-default-skin' );
								add_action( 'wp_footer', 'woocommerce_photoswipe' );
							}
							wp_enqueue_script( 'wc-single-product' );
						}
						else {
							$output = __('Only simple product type are supported.','mwb-sale-funnel');
						}
					}
					else {
						$output = __('Invalid Product ID.','mwb-sale-funnel');
					}
				}
				else {
					$output = __('Please enter Product ID first.','mwb-sale-funnel');
				}
			}
			else {
				$output = __('You do not have any orders associated with Upsell.','mwb-sale-funnel');
			}
		}
		else {
			$output = __('This Funnel Step is not associated with any Funnels.','mwb-sale-funnel');
		}
	}
	else {
		$output = __('This product component will work only on Funnel Post Types.','mwb-sale-funnel');
	}
	return $output;
}
/**
* Simple Add to cart
*
* @since     1.0.0
*/
function mwb_sf_simple_add_to_cart() {
	$current_pid = $GLOBALS['upsell_pid'];
	$product = wc_get_product($current_pid);
	$product_type = $product->get_type();
	if( $product_type == 'simple' ) {
		$order_key = isset($_GET['okey']) ? $_GET['okey']:'';
		?>
		<form class="cart" method="post" enctype='multipart/form-data'>
			<div class="woocommerce-variation-add-to-cart variations_button">
				<button type="submit" class="single_add_to_cart_button button alt"><?php echo __('Buy Now', 'mwb-sale-funnel'); ?></button>
				<a href="<?php echo $GLOBALS['skip_url']; ?>" class="button alt"><?php _e('Skip', 'mwb-sale-funnel'); ?></a>
				<input type="hidden" name="mwb_upsell_buy_now" value="<?php echo absint( $product->get_id() ); ?>" />
				<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
				<input type="hidden" name="sf-order-key" value="<?php echo $order_key; ?>">
				<input type="hidden" name="variation_id" class="variation_id" value="0" />
			</div>
		</form>
		<?php
	}
}

/**
* Upsell Component
*
* @since     1.0.0
* @param     array 		$data 		Component Array    
*/
add_filter('forge_elements', 'mwb_sf_upsell_template');
function mwb_sf_upsell_template($data){
	$data['upsell_felement'] = array(
		'title' => __('One Click Upsell Element', 'mwb-sale-funnel'),
		'description' => __('One Click Upsell Elements', 'mwb-sale-funnel'),
		'group' => 'layout',
		'callback' => 'mwb_sf_forge_element_upsell',
		'fields' => array(
			array(
			'name' => 'mwb_search_fupsell',
			'label' => __('Upsell Product', 'mwb-sale-funnel'),
			'type' => 'number',
			'placeholder' => 'Enter the Product ID',
			'class' => 'mwb-sf-search-element-text',
			'min'	=> '',
			'max'	=> '9999999999999'
			),
			array(
			'name' => 'mwb_ftitle_upsell',
			'caption' => __('Display Product Title', 'mwb-sale-funnel'),
			'type' => 'checkbox',
			'default' => '1'
			),
			array(
			'name' => 'mwb_fprice_upsell',
			'caption' => __('Display Product Price', 'mwb-sale-funnel'),
			'type' => 'checkbox',
			'default' => '1'
			),
			array(
			'name' => 'mwb_fsku_upsell',
			'caption' => __('Display SKU & Category', 'mwb-sale-funnel'),
			'type' => 'checkbox',
			'default' => '1'
			),	
			array(
			'name' => 'mwb_frating_upsell',
			'caption' => __('Display Ratings', 'mwb-sale-funnel'),
			'type' => 'checkbox',
			'default' => '1'
			),
			array(
			'name' => 'mwb_fshort_upsell',
			'caption' => __('Enable Short Description', 'mwb-sale-funnel'),
			'type' => 'checkbox',
			'default' => '1'
			),
			array(
			'name' => 'mwb_fdatatabs_upsell',
			'caption' => __('Display Data Tabs', 'mwb-sale-funnel'),
			'type' => 'checkbox',
			'default' => '1'
			)
		)
	);
	return $data;
}
