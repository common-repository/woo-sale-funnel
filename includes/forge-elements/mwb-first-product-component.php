<?php 

/**
* Product Return Function
*
* @since     1.0.0
* @param    array    $atts    Attributes Array
*/
function mwb_sf_forge_element_product($atts, $content = null) {
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
				'mwb_search_fproduct' 	=> '',
				'mwb_fsku'				=> '1',
				'mwb_frating'			=> '1',
				'mwb_fshort'			=> '1',
				'mwb_flong'				=> '1',
				'mwb_ftitle'			=> '1',
				'mwb_fprice'			=> '1',
				'mwb_fdatatabs'			=> '1',
				'mwb_frelated'			=> '1',
				'mwb_fupsell'			=> '1'
				),
			$atts));
			
			if(isset($mwb_search_fproduct) && $mwb_search_fproduct != null && $mwb_search_fproduct ) {

				$product = wc_get_product($mwb_search_fproduct);
				if( isset($product) && $product != null ) {
					$product_type = $product->get_type();
					if( $product_type == 'simple' ) {
						if ( $mwb_frating != '1' ) {
							remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
						}
						if ( $mwb_fshort != '1' ) {
							remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
						}
						if ( $mwb_fsku != '1' ) {
							remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
						}
						if ( $mwb_ftitle != '1' ) {
							remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
						}
						if ( $mwb_fprice != '1' ) {
							remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
						}
						if ( $mwb_fdatatabs != '1' ) {
							remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
						}
						if ( $mwb_frelated != '1' ) {
							remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
						}
						if ( $mwb_fupsell != '1' ) {
							remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
						}
					
						$output .=do_shortcode('[product_page id="'.$mwb_search_fproduct.'"]');
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
			$output = __('This Funnel Step is not associated with any Funnels.','mwb-sale-funnel');
		}
	}
	else {
		$output = __('This product component will work only on Funnel Post Types.','mwb-sale-funnel');
	}

	return $output;
}
/**
* Product Component
*
* @since     1.0.0
* @param     array 		$data 		Component Array    
*/
add_filter('forge_elements', 'mwb_sf_product_first_template');
function mwb_sf_product_first_template($data){
	$data['product_felement'] = array(
		'title' => __('Product Element', 'mwb-sale-funnel'),
		'description' => __('One of the product elements', 'mwb-sale-funnel'),
		'group' => 'layout',
		'callback' => 'mwb_sf_forge_element_product',
		'fields' => array(
			array(
			'name' => 'mwb_search_fproduct',
			'label' => __('Product', 'mwb-sale-funnel'),
			'type' => 'number',
			'placeholder' => 'Enter the Product ID',
			'class' => 'mwb-sf-search-element-text',
			'min'	=> '',
			'max'	=> '9999999999999'
			),
			array(
			'name' => 'mwb_ftitle',
			'caption' => __('Display Product Title', 'mwb-sale-funnel'),
			'type' => 'checkbox',
			'default' => '1'
			),
			array(
			'name' => 'mwb_fprice',
			'caption' => __('Display Product Price', 'mwb-sale-funnel'),
			'type' => 'checkbox',
			'default' => '1'
			),
			array(
			'name' => 'mwb_fsku',
			'caption' => __('Display SKU & Category', 'mwb-sale-funnel'),
			'type' => 'checkbox',
			'default' => '1'
			),	
			array(
			'name' => 'mwb_frating',
			'caption' => __('Display Ratings', 'mwb-sale-funnel'),
			'type' => 'checkbox',
			'default' => '1'
			),
			array(
			'name' => 'mwb_fshort',
			'caption' => __('Enable Short Description', 'mwb-sale-funnel'),
			'type' => 'checkbox',
			'default' => '1'
			),
			array(
			'name' => 'mwb_fdatatabs',
			'caption' => __('Display Data Tabs', 'mwb-sale-funnel'),
			'type' => 'checkbox',
			'default' => '1'
			),
			array(
			'name' => 'mwb_frelated',
			'caption' => __('Display Related Products', 'mwb-sale-funnel'),
			'type' => 'checkbox',
			'default' => '1'
			),
			array(
			'name' => 'mwb_fupsell',
			'caption' => __('Display Upsell Products', 'mwb-sale-funnel'),
			'type' => 'checkbox',
			'default' => '1'
			),
		)
	);
	return $data;
}
