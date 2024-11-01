<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://makewebbetter.com
 * @since      1.0.0
 *
 * @package    Mwb_Sale_Funnel
 * @subpackage Mwb_Sale_Funnel/admin/partials
 */

/**
 * The admin-specific funnel post type functionality of the plugin.
 *
 *
 * @package    Mwb_Sale_Funnel
 * @subpackage Mwb_Sale_Funnel/admin/partials
 * @author     Make Web Better <webmaster@makewebbetter.com>
 */
class Mwb_Sale_Funnel_Post_Manager {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( ) {
		
		//register funnel post type.
		add_action( 'init', array($this, 'mwb_funnel_post_type') );
		add_action( 'init', array($this, 'mwb_sf_funnel_step_post_type') );
		//customizing messages of funnel post type
		add_filter( 'post_updated_messages', array($this,'mwb_funnel_updated_messages') );
		//adding contextual help topics.
		add_action( 'contextual_help', array($this,'mwb_funnel_contextual_help'), 10, 3 );
		//custom help tab 
		add_action('admin_head', array($this,'mwb_funnel_custom_help_tab'));
		//add meta boxes for funnel post type.
		add_action( 'add_meta_boxes', array($this, 'mwb_funnnel_meta_boxes') );
		//register the funnel types and response the templates.
		add_action('wp_ajax_mwb_sf_funnel_seletion', array($this,'mwb_fs_update_funnel_selection'));
		add_action('wp_ajax_mwb_sf_funnel_template', array($this,'mwb_sf_get_template'));
		add_action('wp_ajax_mwb_sf_seleted_template', array($this,'mwb_sf_save_template'));
		add_action('wp_ajax_mwb_sf_add_new_step', array($this,'mwb_sf_add_new_step'));
		add_action('wp_ajax_mwb_sf_remove_step', array($this,'mwb_sf_remove_step'));
		add_action('wp_ajax_mwb_sf_update_step_name', array($this,'mwb_sf_update_step_name'));
		add_action( 'wp_before_admin_bar_render', array($this,'mwb_funnel_steps_admin_bar') ); 
		add_action( 'the_content', array($this,'mwb_funnel_steps_preview_content') );
		add_action ('save_post', array($this,'mwb_sf_save_final_funnel'));
	}

	/**
	* Funnel Post update step name
	*
	* @since     1.0.0
	*/
	public function mwb_sf_update_step_name() {
		$response = false;
		if ( isset($_POST['funnel_id']) && $_POST['funnel_id'] != null  ) {
			$funnel_id = $_POST['funnel_id'];
			$step_id = $_POST['step_id'];
			$new_txt = $_POST['new_txt'];
			$funnelsteps = get_post_meta( $funnel_id, 'mwb_funnel_steps',true);

			if ( isset($funnelsteps) && $funnelsteps != null ) {
				foreach ($funnelsteps as $key => $value) {
					if ( $value[0] == $step_id ) {
						$funnelsteps[$key][1] = $new_txt;
                        $step_post = array(
                            'ID'           => $step_id,
                            'post_title'     => $new_txt,
                        );
                        wp_update_post( $step_post );
						break;
					}
				}
				$response = true;
				update_post_meta( $funnel_id, 'mwb_funnel_steps',$funnelsteps);
			}
		}
		echo $response;
		wp_die();
	}

	/**
	* Funnel Post remove step
	*
	* @since     1.0.0
	*/
	public function mwb_sf_remove_step() {
		$response = false;
		if ( isset($_POST['funnel_id']) && $_POST['funnel_id'] != null  ) {

			$funnel_id = $_POST['funnel_id'];
			$step_id = $_POST['step_id'];
			$funnelsteps = get_post_meta( $funnel_id, 'mwb_funnel_steps',true);

			if ( isset($funnelsteps) && $funnelsteps != null ) {
				foreach ($funnelsteps as $key => $value) {
					if ( $value[0] == $step_id ) {
						unset($funnelsteps[$key]);
					}
				}
				wp_delete_post($step_id, true);
				$funnelsteps = array_values($funnelsteps);
				update_post_meta( $funnel_id, 'mwb_funnel_steps',$funnelsteps);

				$get_meta_fields=get_post_meta($step_id);
			    foreach ($get_meta_fields as $key => $value) {
			    	
				    delete_post_meta($step_id,$key);
			    }
			}
			$response = true;
		}
		echo $response;
		wp_die();
	}

	/**
	* Funnel Post add new step
	*
	* @since     1.0.0
	*/
	public function mwb_sf_add_new_step() {
		$response = array('status' => false, 'message'=>null);
		if ( isset($_POST['funnel_id']) && $_POST['funnel_id'] != null  ) {

			$funnel_id = $_POST['funnel_id'];
			$step_name = $_POST['step_name'];
			$funnel_step_id = array();
			$user = wp_get_current_user();
			$postarr = array(
		        'post_author' => $user->ID,
		        'post_title' => $step_name,
		        'post_status' => 'draft',
		        'post_type' => 'mwbfunnelstep',
		    );
		    $postid = wp_insert_post( $postarr, $wp_error = false );
			$funnel_step_id[] = $postid;
			$funnel_step_id[] = $step_name;
			update_post_meta( $postid, 'mwb_funnel_step_active', $funnel_id);
			$funnelsteps = get_post_meta( $funnel_id, 'mwb_funnel_steps',true);
			$funnelsteps[] = $funnel_step_id;
			update_post_meta($funnel_id, 'mwb_funnel_steps', $funnelsteps);
			$response['status'] = true;
			$html = "<div class='mwb-sf-step-wrapper' id='mwb-sf-step-".$postid."'>
					<input type='hidden' name='mwb_sf_step_id[]' value='".$postid."'>
					<span class='mwb_funnel_names ".$class."'>
						<a class='mwb-sf-step-name' data-id='".$postid."'>".$step_name."</a>
						<span class='mwb-step-actions'>
						<a href='javascript:void(0)' class='mwb_step_edit' data-id='".$postid."' data-name='".$step_name."'><img src='".MWB_PLUGIN_URL."/admin/images/edit.png'></a>
						<a href='javascript:void(0)' class='mwb_step_remove' data-id='".$postid."'><img src='".MWB_PLUGIN_URL."/admin/images/cross.png'></a>
						</spam>
					</span>
					</div>";
			$response['message'] = $html;
		}
		echo wp_json_encode($response);
		wp_die();
	}

	/**
	* Funnel Post save funnel
	*
	* @since     1.0.0
	*/
	public function mwb_sf_save_final_funnel( $post_ID ) {

		$post = get_post($post_ID);
		if ( isset($post) && $post->post_type == 'mwbfunnel' ) {
			$post_id = $post->ID;
			$post_type = $post->post_type;
			$newfunnel = array();
			$flag = false;
			if( $post_type == 'mwbfunnel') {

				if( isset($_POST['mwb_sf_step_id']) && $_POST['mwb_sf_step_id'] != null) {
                    $step_slugs = $_POST['mwb_sf_step_permalink'];
					$step_ids = $_POST['mwb_sf_step_id'];

					$funnelsteps = get_post_meta( $post_id,'mwb_funnel_steps',true);
					
					if( isset( $funnelsteps ) && $funnelsteps != null ) {
						
						foreach ($step_ids as $key => $value) {
							foreach ( $funnelsteps as $key1 => $value1 ) {
								
								if ( $value ==  $value1[0]) {
									$newfunnel[] = $value1;
									$flag = true;
								}
							}
                            $new_name = '';
                            $changed = false;
                            if ( isset($step_slugs[$value]) && $step_slugs[$value] != null ) {
                                $new_name = trim( $step_slugs[$value] );
                                if ( !empty( $new_name ) ) {
                                    $changed = true;
                                }
                            }
                            $template = get_post($value);
                            $step_slug = $template->post_title;
                            if( $changed ){
                                $step_slug = $new_name;
                            }
                            $step_post = array(
                                'ID'           => $value,
                                'post_name'     => $step_slug,
                            );
                           
                            wp_update_post( $step_post );
                           
                            $get_selected_template = get_post_meta($value,'mwb_selected_template',true);
                            if ( isset($get_selected_template) && $get_selected_template != null ) {
                                wp_publish_post( $value );
                            }
						}
					}
				}
				if( $flag ) {
					update_post_meta($post_id, 'mwb_funnel_steps', $newfunnel);
					update_post_meta($post_id, 'mwb_sf_funnel_status', true);
				}
			}
		}
	}

	/**
	* Funnel Post Preview
	*
	* @since     1.0.0
	*/
	public function mwb_funnel_steps_preview_content($content) {
		global $post;
		$post_id = $post->ID;
		$post_type = $post->post_type;
		if($post_type == 'mwbfunnelstep') {
			if(isset($_GET['preview']) && $_GET['preview'] == true) {
				if(isset($_GET['post_type']) && $_GET['post_type'] == $post_type && isset($_GET['p']) && $_GET['p'] == $post_id ) {
					if(isset($_GET['tid']) && $_GET['tid'] != null) {
						$content = '<code>[forge_template id="'.$_GET['tid'].'"]</code>';
					}
				}
				elseif(isset($_GET['tid']) && $_GET['tid'] != null) {
					$content = '<code>[forge_template id="'.$_GET['tid'].'"]</code>';
				}
			}			
		}
		return $content;
	}

	/**
	* Funnel Post step admin bar
	*
	* @since     1.0.0
	*/
	function mwb_funnel_steps_admin_bar() {
		global $wp_admin_bar;
		global $post;
		if(isset($post)) {
			$post_id = $post->ID;
			$post_type = $post->post_type;
			if($post_type == 'mwbfunnelstep') {
				if(isset($_GET['preview']) && $_GET['preview'] == true) {
					if(isset($_GET['post_type']) && $_GET['post_type'] == 'mwbfunnelstep' && isset($_GET['p']) && $_GET['p'] == $post_id ) {
						$wp_admin_bar->remove_menu('forge-edit');
					}
				}
				$wp_admin_bar->remove_menu('edit');
			}
		}
	}

	/**
	* Funnel Post Save Template
	*
	* @since     1.0.0
	*/
	public function mwb_sf_save_template(){
		$funnel_step_id = $_POST['selected_funnel_step'];
		$selected_template = $_POST['selected_template'];
		$content = '<code>[forge_template id="'.$selected_template.'"]</code>';

		$template = get_post($funnel_step_id);
		$step_slug = $template->post_title;
		if( isset($_POST['slug']) && $_POST['slug'] != null ) {
			$slug = trim($_POST['slug']);
			if ( !empty($slug) ) {
				$step_slug = $slug;
			}
		}
		
		// Update post 
		$step_post = array(
			'ID'           => $funnel_step_id,
			'post_content' => $content,
			'post_name'		=> $step_slug,
		);
		$get_meta_fields = get_post_meta($selected_template);
	    foreach ($get_meta_fields as $key => $value) {
	    	if($key!='forge_builder_settings'){
		    	if(is_serialized($value[0])){
		    		$value=unserialize($value[0]);
		    	}
		    	update_post_meta($funnel_step_id,$key,$value);
		    }
	    }
		// Update the post into the database
		wp_update_post( $step_post );
	    update_post_meta($funnel_step_id,'mwb_selected_template',$selected_template);
		require_once 'mwb-funnel-steps-view.php';
		$fetch_temp = fetch_template($funnel_step_id);
		
		echo $fetch_temp;
		wp_die();
	}

	/**
	* Funnel Post step Fetch Template
	*
	* @since     1.0.0
	*/
	public function mwb_sf_get_template(){
		$funnel_step_id = $_POST['funnel_step_id'];
		require_once 'mwb-funnel-steps-view.php';
		$fetch_temp = fetch_template($funnel_step_id);
		
		echo $fetch_temp;
		wp_die();
		
	}
	/**
	* Funnel Post step creation
	*
	* @since     1.0.0
	*/
	public function mwb_fs_update_funnel_selection(){

		$funnel_id = isset($_POST['funnel_id']) ? intval($_POST['funnel_id']) : 0;

		$sell_product_steps = array(
			__('Email Landing Page','mwb-sale-funnel'),
			__('Sales Page','mwb-sale-funnel'),
			__('Order Confirmation','mwb-sale-funnel'),
			__('Thank You/Download Page','mwb-sale-funnel')
		);
		$sell_product_steps = apply_filters('mwb_sell_products_filter',$sell_product_steps,$funnel_id);
		$product_launch_steps = array(
			__('Email Landing Page','mwb-sale-funnel'),
			__('Free Video #1-#4','mwb-sale-funnel'),
			__('Order Page','mwb-sale-funnel'),
			__('Thank You/Download Page','mwb-sale-funnel')
		);
		$product_launch_steps = apply_filters('mwb_launch_products_filter',$product_launch_steps,$funnel_id);
		
		if($funnel_id){
			$selected_type = isset($_POST['selected_funnel']) ? $_POST['selected_funnel'] : 'salefunnel';
			
			$funnel_steps_details=array();
			if ($selected_type == 'salefunnel') {
				$funnel_detail = $sell_product_steps;
			}
			else if($selected_type == 'prolaunch'){
				$funnel_detail = $product_launch_steps;
			}
			foreach ($funnel_detail as $key => $value) {
				$funnel_step_id = array();
				$user = wp_get_current_user();
				$postarr = array(
			        'post_author' => $user->ID,
			        'post_title' => $value,
			        'post_status' => 'draft',
			        'post_type' => 'mwbfunnelstep',
			    );
			    $postid = wp_insert_post( $postarr, $wp_error = false );
				$funnel_step_id[] = $postid;
				$funnel_step_id[] = $value;
				$funnel_steps_details[] = $funnel_step_id;
                update_post_meta( $postid, 'mwb_funnel_step_active', $funnel_id);
			} 
			$first_step_id = 0;
			if(isset($funnel_steps_details[0][0]) && $funnel_steps_details[0][0] != null) {
				$first_step_id = $funnel_steps_details[0][0];
			}
			update_post_meta( $funnel_id,'mwb_funnel_steps', $funnel_steps_details );

			update_post_meta( $funnel_id, 'mwb_sf_funnel_type', $selected_type );
			
		
			require_once 'mwb-funnel-steps-view.php';
			$response = create_funnel_template($funnel_id, $first_step_id); 
			
			echo $response;
			// echo $funnel_templates;
			wp_die();
		}
		
	}

	/**
	* Register Funnel Post Type
	*
	* @since     1.0.0
	*/
	public function mwb_sf_funnel_step_post_type(){

		$args = array(
			'label'             => __('Funnel Step', 'mwb-sale-funnel'),
	        'description'        => __( 'Default templates for creating funnel steps ', 'mwb-sale-funnel' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => false,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'funnel_step' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'supports'           => array( 'title','editor' )
		);

		register_post_type( 'mwbfunnelstep', $args );
	}
	/**
	* Funnel Post Type Contexual Help
	*
	* @since     1.0.0
	*/
	public function mwb_funnel_post_type(){

		$labels = array(
			'name'               => __( 'Funnels', 'mwb-sale-funnel' ),
			'singular_name'      => __( 'Funnel', 'mwb-sale-funnel' ),
			'menu_name'          => __( 'Funnels', 'mwb-sale-funnel' ),
			'name_admin_bar'     => __( 'Funnel', 'mwb-sale-funnel' ),
			'add_new'            => __( 'Add New', 'funnel', 'mwb-sale-funnel' ),
			'add_new_item'       => __( 'Add New Funnel', 'mwb-sale-funnel' ),
			'new_item'           => __( 'New Funnel', 'mwb-sale-funnel' ),
			'edit_item'          => __( 'Edit Funnel', 'mwb-sale-funnel' ),
			'view_item'          => __( 'View Funnel', 'mwb-sale-funnel' ),
			'all_items'          => __( 'All Funnels', 'mwb-sale-funnel' ),
			'search_items'       => __( 'Search Funnels', 'mwb-sale-funnel' ),
			'parent_item_colon'  => __( 'Parent Funnels:', 'mwb-sale-funnel' ),
			'not_found'          => __( 'No funnels found.', 'mwb-sale-funnel' ),
			'not_found_in_trash' => __( 'No funnels found in Trash.', 'mwb-sale-funnel' )
		);

		$args = array(
			'labels'             => $labels,
	        'description'        => __( 'An easy and effective way for implementing sale funnels in WooCommerce.', 'mwb-sale-funnel' ),
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'funnel' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 5,
			'supports'           => array( 'title' ),
		);

		$funnel_posts = get_posts(array(
			'posts_per_page' =>  -1,
			'post_type'      =>  'mwbfunnel',
			'post_status' => array('publish', 'pending', 'draft', 'future', 'private', 'inherit', 'trash') 
		));
		
		if( isset($funnel_posts) && $funnel_posts != null ) {
			if(count($funnel_posts) > 0 || count($funnel_posts) == 1 ) {
				$args['capabilities'] = array(
					'create_posts' => 'do_not_allow',
				);
				$args['map_meta_cap'] = true;
			}
		}
		register_post_type( 'mwbfunnel', $args );
	}

	/**
	 * Funnel update messages.
	 *
	 * See /wp-admin/edit-form-advanced.php
	 *
	 * @param array $messages Existing post update messages.
	 *
	 * @return array Amended post update messages with new Funnel update messages.
	 */
	public function mwb_funnel_updated_messages( $messages ){

		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages['mwbfunnel'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Funnel updated.', 'mwb-sale-funnel' ),
			2  => __( 'Custom field updated.', 'mwb-sale-funnel' ),
			3  => __( 'Custom field deleted.', 'mwb-sale-funnel' ),
			4  => __( 'Funnel updated.', 'mwb-sale-funnel' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Funnel restored to revision from %s', 'mwb-sale-funnel' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Funnel published.', 'mwb-sale-funnel' ),
			7  => __( 'Funnel saved.', 'mwb-sale-funnel' ),
			8  => __( 'Funnel submitted.', 'mwb-sale-funnel' ),
			9  => sprintf(
				__( 'Funnel scheduled for: <strong>%1$s</strong>.', 'mwb-sale-funnel' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'mwb-sale-funnel' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Funnel draft updated.', 'mwb-sale-funnel' )
		);

		if ( $post_type_object->publicly_queryable && 'Funnel' === $post_type ) {
			$permalink = get_permalink( $post->ID );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View Funnel', 'mwb-sale-funnel' ) );
			$messages[ $post_type ][1] .= $view_link;
			$messages[ $post_type ][6] .= $view_link;
			$messages[ $post_type ][9] .= $view_link;

			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview Funnel', 'mwb-sale-funnel' ) );
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}
	/**
	* Funnel Post Type Contexual Help
	*
	* @since     1.0.0
	*/
	public function mwb_funnel_contextual_help( $contextual_help, $screen_id, $screen ){

		if ( 'mwbfunnel' == $screen->id ) {
		    $contextual_help =
		      '<p>' . __('Things to remember when adding or editing a funnel:', 'mwb-sale-funnel') . '</p>' .
		      '<ul>' .
		      '<li>' . __('Specify the steps, that will be there in the funnel', 'mwb-sale-funnel') . '</li>' .
		      '</ul>' .
		      '<ul>' .
		      '<li>' . __('Under the Publish module, click on the Edit link next to Publish.', 'mwb-sale-funnel') . '</li>' .
		      '<li>' . __('Change the date to the date to actual publish this article, then click on Ok.', 'mwb-sale-funnel') . '</li>' .
		      '</ul>' .
		      '<p><strong>' . __('For more information:', 'mwb-sale-funnel') . '</strong></p>' .
		      '<p>' . __('<a href="http://codex.wordpress.org/Posts_Edit_SubPanel" target="_blank">Edit Posts Documentation</a>', 'mwb-sale-funnel') . '</p>' .
		      '<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>', 'mwb-sale-funnel') . '</p>' ;
		  } elseif ( 'edit-mwbfunnel' == $screen->id ) {
		    $contextual_help =
		      '<p>' . __('This is the help screen displaying the table of funnels.', 'mwb-sale-funnel') . '</p>' ;
		  }
	  return $contextual_help;
	}
	/**
	* Funnel Post Type Help Tab
	*
	* @since     1.0.0
	*/
	public function mwb_funnel_custom_help_tab(){
		$screen = get_current_screen();

		  // Return early if we're not on the book post type.
		  if ( 'mwbfunnel' != $screen->post_type )
		    return;

		  // Setup help tab args.
		  $args = array(
		    'id'      => 'mwb_funnel_custom_help_tag', //unique id for the tab
		    'title'   => __('Funnel Help','mwb-sale-funnel'), //unique visible title for the tab
		    'content' => '',  //actual help text
		  );
		  
		  // Add the help tab.
		  $screen->add_help_tab( $args );
	}

	/**
	* Funnel Post Type Meta Box
	*
	* @since     1.0.0
	*/
	public function mwb_funnnel_meta_boxes(){
		remove_meta_box( 'forge_layout_mwbfunnel' , 'mwbfunnel' , 'side' ); 
		add_meta_box( 'mwb-funnel-add-steps', __( 'Funnel Steps', 'mwb-sale-funnel' ), array($this,'mwb_funnel_add_steps_view'), 'mwbfunnel' );
	}

	/**
	* Steps View html
	*
	* @since     1.0.0
	*/
	public function mwb_funnel_add_steps_view(){
		
		$id=get_the_id();
		$funnel_steps=get_post_meta($id,'mwb_funnel_steps',true);
		$base=get_home_url();
		
		global $post;
		?>
		<div class="mwb-sf-new-funnel-steps">
			<div class="mwb-sf-steps-wrapper">
				<input type="hidden" id="baseurl" name="baseurl" value="<?php echo $base; ?>" />
				<!-- funnel options -->
				
				<!-- End of initial funnel options -->

				<!-- selected option funnel types -->
				<div class="mwb-sf-selected-type mwb-sf-type2" <?php if(!empty($funnel_steps)){ echo 'style="display:none;"';}?>>
					<div class="mwb-sf-options-wrapper">
						<div class="mwb-sf-sell-products-type mwb-sf-second-products">
							<div class="mwb-sf-second-options-wrapper">
								<!-- sell funnel -->
								<div class="mwb-sf-sell-products-options mwb-products-action mwb-sf-wrapper mwb-sf-sell-funnel">
									
									<h2><span class="mwb-sf-products-type"><?php _e('Sell Funnel', 'mwb-sale-funnel') ?></span></h2>
									<img src="<?php echo MWB_PLUGIN_URL.'admin/images/oldpronav.png'; ?>">
									<button type="button" class="mwb-sf-sell-products-options mwb-products-type-select button" ftype="salefunnel"> <?php _e('Choose', 'mwb-sale-funnel') ?></button>
								</div>
								<!-- product launch -->
								<div class="mwb-sf-sell-products-options mwb-products-action mwb-sf-wrapper mwb-sf-product-funnel">
									<h2><span class="mwb-sf-products-type "><?php _e('Product Launch', 'mwb-sale-funnel') ?></span></h2>
									<img src="<?php echo MWB_PLUGIN_URL.'admin/images/newpronav.png'; ?>">
									<button type="button" class="mwb-sf-sell-products-options mwb-products-type-select button" ftype="prolaunch"> <?php _e('Choose', 'mwb-sale-funnel') ?></button>
								</div>
								<!-- membership -->
							</div>
						</div>
					</div>
				</div>
				<!-- End of selected option funnel types -->

				<!-- Steps of funnnel sale -->
				<div class="mwb-sf-funnel-steps mwb-sf-type3"  <?php if(!empty($funnel_steps)){ echo 'style="display:block;"';}?> >
					<?php
						if($funnel_steps != null) {
							require_once 'mwb-funnel-steps-view.php';
							$firstid = 0;
							if(isset($funnel_steps[0][0]) && $funnel_steps[0][0] != null ) {
								$firstid = $funnel_steps[0][0];
							}
							$res = create_funnel_template($id, $firstid); 
							echo $res;
						}
						
					?>				
				</div>
				<!-- End of steps of funnel sale -->

				<input type="hidden" id="mwb_fs_initial_selection" name="mwb_fs_initial_selection">
				<input type="hidden" id="mwb_fs_second_selection" name="mwb_fs_second_selection">
				<input type="hidden" id="mwb_fs_funnel_id" name="mwb_fs_funnel_id" value="<?php echo $post->ID; ?>">
				<div class="mwb_sf_ajax_loader" style="display: none;">
					<img src="<?php echo MWB_PLUGIN_URL.'admin/images/loader.svg'; ?>">
				</div>
			</div>
		</div>
		<?php
	}
}