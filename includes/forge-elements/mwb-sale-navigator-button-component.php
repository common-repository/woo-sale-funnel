<?php

/**
* Navigator Return Function
*
* @since     1.0.0
* @param    array    $atts    Attributes Array
*/
function mwb_sf_forge_element_navigate( $atts, $content = null ) {
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
				'sf_button_text' => 'Next'
				),
			$atts));
			$funnel_steps = get_post_meta( $funnel_id,'mwb_funnel_steps', true);
							
			foreach ($funnel_steps as $key => $value) {
				if( $value[0] == $fstep_id ) {
					break;
				}
			}
			if( isset($funnel_steps[$key+1]) ) {
				$order_key = isset($_GET['okey']) ? '/?okey='.$_GET['okey']:'';
				$redirect = get_permalink($funnel_steps[$key+1][0]).$order_key;
			}
			else {
				$redirect = wc_get_page_permalink('shop');
			}
			$output .= '<a href="'.$redirect.'" class="button alt">'.$sf_button_text.'</a>';
		}
		else {
			$output = __('This Funnel Step is not associated with any Funnels.','mwb-sale-funnel');
		}
	}
	else {
		$output = __('This navigator component will work only on Funnel Post Types.','mwb-sale-funnel');
	}

	return $output;
}
/**
* Navigator Component
*
* @since     1.0.0
* @param     array 		$data 		Component Array    
*/
add_filter('forge_elements', 'mwb_sf_forge_navigate_metadata');
function mwb_sf_forge_navigate_metadata($data){
	$data['navigator_felement'] = array(
		'title' => __('Funnel Navigator', 'mwb-sale-funnel'),
		'description' => __('Navigation between Funnels', 'mwb-sale-funnel'),
		'group' => 'layout',
		'callback' => 'mwb_sf_forge_element_navigate',
		'fields' => array(
			array(
				'name' => 'sf_button_text',
				'label' => __('Button Text', 'mwb-sale-funnel'),
				'type' => 'text',
				'default' => 'Next',
			),
		)
	);
	return $data;
}