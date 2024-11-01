<?php

/**
* Common create funnel 
*
* @since     1.0.0
*/

function create_funnel_template($funnel_id, $first_step_id = 0){

	$funnel_steps_details = get_post_meta($funnel_id,'mwb_funnel_steps',true);
	$res = "";
	if(isset($funnel_steps_details) && $funnel_steps_details != null) {

	
		$res.="<div class='mwb-funnel-steps'>
				<h3>".__('Funnel Creation','mwb-sale-funnel')."</h3>
				<div class='mwb-sf-step-perma-wrap'>
				<div class='mwb-funnel-step'> ";

		foreach ($funnel_steps_details as $key => $value) { 
			
			$class = ( !$key ) ? 'sf-step-active': '';
			
			$res.="<div class='mwb-sf-step-wrapper' id='mwb-sf-step-".$value[0]."'>
					<input type='hidden' name='mwb_sf_step_id[]' value='".$value[0]."'>
					<span class='mwb_funnel_names ".$class."'>
						<a class='mwb-sf-step-name' data-id='".$value[0]."'>".$value[1]."</a>
						<span class='mwb-step-actions'>
						<a href='javascript:void(0)' class='mwb_step_edit' data-id='".$value[0]."' data-name='".$value[1]."'><img src='".MWB_PLUGIN_URL."/admin/images/edit.png'></a>
						<a href='javascript:void(0)' class='mwb_step_remove' data-id='".$value[0]."'><img src='".MWB_PLUGIN_URL."/admin/images/cross.png'></a>
						</spam>
					</span>
					</div>";
		}
		$res.="</div>";
		$res.="<a href='javascript:void(0)' class='mwb-sf-add-more'>".__('Add more steps', 'mwb-sale-funnel')."</a></div>";
		$res.= "<div class='mwb_template_container'>";
		$res.="<strong><span class='mwb-perma-title'>".__( 'Permalink: ', 'mwb-sale-funnel' )."</span></strong>";
		foreach ($funnel_steps_details as $key => $value) { 
			$class = ($first_step_id == $value[0])? 'mwb-sf-slug-active' : '';
			list($sf_permalink, $sf_post_name) = get_sample_permalink($value[0]);

			$sf_permalink = str_replace( array( '%pagename%/', '%postname%/' ), "", $sf_permalink );

			$res.= "<span class='mwb-sf-perma-span ".$class."' id='mwb-perma-step-each-".$value[0]."'>".$sf_permalink."<input type='text' value='".$sf_post_name."' name='mwb_sf_step_permalink[".$value[0]."]' id='mwb-step-slug-".$value[0]."'></span>";
		}
		$res.= "<div class='mwb-funnel-step-template'>";
		if($first_step_id) {
			$res.= fetch_template($first_step_id);
		}

		$res.="</div><div class='mwb_loader_wrapper'>";
		$res.= '<div class="mwb_bar_preparing_loader">
			        <div class="loader"></div>
			        <div class="loader1"></div>
			        <div class="loader2"></div>
			    </div></div></div></div>';
		$res.="<div class='mwb-sf-pop-wrap' style='display:none;'><div class='mwb-sf-add-more-pop'></div></div>";
		
	}
	return $res;
}

/**
* Common fetch template
*
* @since     1.0.0
*/
function fetch_template($funnel_step_id){
	$get_selected_template = get_post_meta($funnel_step_id,'mwb_selected_template',true);
	$args = array(
		'posts_per_page'   => -1,
		'orderby'          => 'ID',
		'order'            => 'ASC',
		'post_type'		   => 'forge_template'
	);
	
	
	
	$temp = '<input type="hidden" data-permalink="'.get_permalink($funnel_step_id).'" id="mwb_step_perma_'.$funnel_step_id.'">';
	$template = get_posts($args);
	if(isset($get_selected_template) && $get_selected_template != null){
		foreach ($template as $key => $value) {
			if($value->ID == $get_selected_template){
				$temp.="<div class='mwb-funnel-template'><span class='mwb-funnel-template-name'>".$value->post_name."</span><input type='hidden' class='mwb-funnel-template-name' value='".$value->post_name."'><input type='button' class='mwb-funnel-edit-template button' data-template_id='".$value->ID."' data-step_id='".$funnel_step_id."' value='".__('Edit Page', 'mwb-sale-funnel')."'></div>";
			}
			else {
				$temp.="<div class='mwb-funnel-template'><span class='mwb-funnel-template-name'>".$value->post_name."</span><input type='button' class='mwb-funnel-template-selected button' data-template_id='".$value->ID."' data-step_id='".$funnel_step_id."' value='Select'><input type='button' class='mwb-funnel-template-preview button' data-template_id='".$value->ID."' data-step_id='".$funnel_step_id."' value='".__('Preview', 'mwb-sale-funnel')."'></div>";
			}
		}
	}
	else{
		foreach ( $template as $key => $value ) {
			
			$temp.="<div class='mwb-funnel-template'><span class='mwb-funnel-template-name'>".$value->post_name."</span><input type='button' class='mwb-funnel-template-selected button' data-template_id='".$value->ID."' data-step_id='".$funnel_step_id."' value='Select'><input type='button' class='mwb-funnel-template-preview button' data-template_id='".$value->ID."' data-step_id='".$funnel_step_id."' value='Preview'></div>";
			
		}

	}
	return $temp;
}