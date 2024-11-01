<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://makewebbetter.com
 * @since      1.0.0
 *
 * @package    woo_Sale_Funnel
 * @subpackage woo_Sale_Funnel/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    woo_Sale_Funnel
 * @subpackage woo_Sale_Funnel/admin
 * @author     Make Web Better <webmaster@makewebbetter.com>
 */
class Mwb_Sale_Funnel_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		//load and initializes admin related classes.
		$this->mwb_sf_load_admin_classes();
	}
	/**
	 * Loading Admin Classes
	 *
	 * @since    1.0.0
	 */
	public function mwb_sf_load_admin_classes(){

		require_once plugin_dir_path( __FILE__ ).'partials/class-mwb-sale-funnel-post-type-register.php';

		$funnel_post_type_manager = new Mwb_Sale_Funnel_Post_Manager();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mwb_Sale_Funnel_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mwb_Sale_Funnel_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mwb-sale-funnel-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mwb_Sale_Funnel_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mwb_Sale_Funnel_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( 'jquery-ui-sortable');
		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mwb-sale-funnel-admin.js', array( 'jquery','jquery-ui-sortable' ), $this->version, false );
		$localize_arr = array(
			'ajaxURL' => admin_url( 'admin-ajax.php' ),
			'new_step'=>__('New Step In Funnel', 'mwb-sale-funnel'),
			'funnel_name'=>__('Enter Funnel Step name: ', 'mwb-sale-funnel'),
			'create_step'=>__('Create Funnel Step', 'mwb-sale-funnel'),
			'cancel'=>__('Cancel', 'mwb-sale-funnel'),
			'delete_step'=>__('Delete Funnel Step', 'mwb-sale-funnel'),
			'delete_confirm'=>__('Do you want to delete this funnel?', 'mwb-sale-funnel'),
			'edit_step'=>__('Edit Funnel Step Name', 'mwb-sale-funnel'),
			'step_name'=>__('Funnel Step Name', 'mwb-sale-funnel'),
			'update'=>__('Update', 'mwb-sale-funnel'),
			'selectfunnel'=>__('Please select any Funnel Steps', 'mwb-sale-funnel'),
		) ;
		wp_localize_script( $this->plugin_name, 'mwb_sf_localized', $localize_arr);

		wp_enqueue_script( $this->plugin_name );

		if(isset($_GET["section"]) && $_GET["section"]=="mwb-sf-paypal-gateway")
		{
			wp_enqueue_script('mwb-sf-paypal-script', plugin_dir_url( __FILE__ ) . 'js/mwb-sf-admin-paypal.js', array('jquery'), $this->version,false);
		}
		elseif(isset($_GET["section"]) && $_GET["section"]=="mwb-sf-stripe-gateway")
		{
			wp_enqueue_script('mwb-sf-stripe-script', plugin_dir_url( __FILE__ ) . 'js/mwb-sf-admin-stripe.js', array('jquery'), $this->version,false);
		}
	}
	
	/**
	* Adding Upsell Order Columns on order section
	*
	* @since     1.0.0
	* @param     array 		$columns 		    All Cloumns
	*/
	public function mwb_sf_add_columns_to_admin_orders( $columns ) {
		$columns['upsell-orders'] = __('Upsell Orders','mwb-sale-funnel');

    	return $columns;
	}
	/**
	* Adding Upsell Order data to parent orders columns
	*
	* @since     1.0.0
	* @param     string 		$columns 		    All Cloumns
	* @param     int 			$post_id 		    Post ID
	*/
	public function mwb_sf_add_upsell_orders_to_parent( $column, $post_id ) {

		$suborder = get_posts(array(
				'posts_per_page' =>  -1,
				'post_type'      =>  'shop_order',
				'post_status'    =>  'any',
				'meta_key'       =>  'mwb_sf_upsell_parent_order',
				'meta_value'     =>   $post_id,
				'orderby'        =>  'ID',
				'order'          =>  'ASC'
			));

		$is_upsell_order = get_post_meta($post_id,"mwb_sf_upsell_parent_order",true);

		switch($column)
		{
			case 'upsell-orders':

			$data = "";

			if(!empty($suborder))
			{
				foreach($suborder as $mwb_sf_single_order)
				{
					$mwb_sf_upsell_order = wc_get_order($mwb_sf_single_order->ID);

					$data .= '<p><a href="'.get_edit_post_link($mwb_sf_upsell_order->get_id()).'">'.__('Upsell order #','mwb-sale-funnel').$mwb_sf_upsell_order->get_order_number().'</a></p>';
				}	
			}
			elseif( $is_upsell_order == null )
			{
				$data .= __("Single Order","mwb-sale-funnel");
			}
			else
			{
				$data = '<p style="">_</p>';
			}

			echo $data;
			
			break;	
		}	
	}
	/**
	* Adding filters to order page
	*
	* @since     1.0.0
	*/
	public function mwb_sf_restrict_manage_posts() {

		if(isset($_GET["post_type"]) && $_GET["post_type"]=="shop_order")
		{
			if(isset($_GET["mwb_sf_upsell_parent_order"])):?>
				<select name="mwb_sf_upsell_parent_order">
					<option value="select" <?php echo $_GET["mwb_sf_upsell_parent_order"]=="select"?"selected=selected":""?>><?php _e('All Orders','mwb-sale-funnel')?></option>
					<option value="all_single" <?php echo $_GET["mwb_sf_upsell_parent_order"]=="all_single"?"selected=selected":""?>><?php _e('All excluding Upsell Orders','mwb-sale-funnel')?></option>
					<option value="all_upsells" <?php echo $_GET["mwb_sf_upsell_parent_order"]=="all_upsells"?"selected=selected":""?>><?php _e('Only Upsell Orders','mwb-sale-funnel')?></option>
				</select>
			<?php endif;

			if(!isset($_GET["mwb_sf_upsell_parent_order"])):?>
				<select name="mwb_sf_upsell_parent_order">
					<option value="select"><?php _e('All Orders','mwb-sale-funnel')?></option>
					<option value="all_single"><?php _e('All excluding Upsell Orders','mwb-sale-funnel')?></option>
					<option value="all_upsells"><?php _e('Only Upsell Orders','mwb-sale-funnel')?></option>
				</select>
			<?php endif;
		}
	}
	/**
	* Order Request Query
	*
	* @since     1.0.0
	* @param     array 		$vars 		    Query
	*/
	public function mwb_sf_request_query ( $vars ) {
		//modifying query vars for filtering orders

		if(isset($_GET["mwb_sf_upsell_parent_order"]) && $_GET["mwb_sf_upsell_parent_order"]=="all_upsells")
		{
			$vars = array_merge($vars,array('meta_key'=>'mwb_sf_upsell_parent_order'));
		}
		elseif(isset($_GET["mwb_sf_upsell_parent_order"]) && $_GET["mwb_sf_upsell_parent_order"]=="all_single")
		{
			$vars = array_merge($vars,array('meta_key'=>'mwb_sf_upsell_parent_order','meta_compare'=>'NOT EXISTS'));
		}

		return $vars;
	}
	/**
	* Preview Post Link for Funnel Post type
	*
	* @since     1.0.0
	* @param     string 		$prevlink 		    Prev post
	* @param     object 			$post 		    Post 
	*/
	public function mwb_sf_preview_post_link( $prevlink, $post ) {
		
		if( isset($post) && $post->post_type == 'mwbfunnel') {
			$funnel_id = $post->ID;
			$funnel_steps = get_post_meta( $funnel_id,'mwb_funnel_steps', true);
			if( isset($funnel_steps) && $funnel_steps != null ) {
				if ( isset($funnel_steps[0][0])) {
					$prevlink = get_permalink($funnel_steps[0][0]);
				}

			}
			else {
				$prevlink = "";	
			}			
		}
		return $prevlink;
	}
	/**
	* Funnel Post actions
	*
	* @since     1.0.0
	* @param     string 		$prevlink 		    Prev post
	* @param     object 			$post 		    Post 
	*/
	public function mwb_sf_post_row_actions( $actions, $post ) {
		if( isset($post) && $post->post_type == 'mwbfunnel') {
			$funnel_id = $post->ID;
			$title = $post->post_title;
			$funnel_steps = get_post_meta( $funnel_id,'mwb_funnel_steps', true);
			//unset($actions['view'] );
			if( isset($funnel_steps) && $funnel_steps != null ) {
				if ( isset($funnel_steps[0][0])) {
					if ( isset( $actions['view'] )) {
						$actions['view'] =  sprintf(
							'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
							get_permalink( $funnel_steps[0][0] ),
							/* translators: %s: post title */
							esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $title ) ),
							__( 'View' )
						);
					}
				}
			}
		}

		return $actions;
	}
	/**
	* Funnel Post Type Columns
	*
	* @since     1.0.0
	* @param     array 		$columns 		    Columns
	*/
	public function mwb_sf_mwbfunnel_columns( $columns ) {

		$new_colum  = array();
        $new_colum['cb'] = $columns['cb'];
        $new_colum['title'] = $columns['title'];
        $new_colum['sf_status'] = __("Funnel Status", 'mwb-sale-funnel');
        $new_colum['sf_view'] = __("View", 'mwb-sale-funnel');
        $new_colum['date'] = $columns['date'];
         

        return  $new_colum;
	}
	/**
	* Funnel Post Type Columns Data
	*
	* @since     1.0.0
	* @param     array 		$columns 		    Columns
	* @param     array 		$post_id 		    Post ID
	*/
	public function mwb_sf_mwbfunnel_columns_data( $column, $post_id ) {
		switch($column)
		{
			case 'sf_status':
				$funnel_status = get_post_meta( $post_id, 'mwb_sf_funnel_status', true);
				if(  $funnel_status ) {
					echo __( 'Enabled', 'mwb-sale-funnel' );
				}
				else {
					echo __( 'Disabled', 'mwb-sale-funnel' );
				}
			break;
			case 'sf_view':
				$funnel_steps = get_post_meta( $post_id,'mwb_funnel_steps', true);
				
				if( isset($funnel_steps) && $funnel_steps != null ) {
					if ( isset($funnel_steps[0][0])) {
						echo '<a class="button" href="'.get_permalink( $funnel_steps[0][0] ).'">'.__('View Funnel', 'mwb-sale-funnel').'</a>';
					}
				}
				else {
					echo __('Please publish this funnel first', 'mwb-sale-funnel');
				}	
			break;
		}
	}

	/**
	* Funnel Post Type Bulk Actions
	*
	* @since     1.0.0
	* @param     array 		$columns 		    Columns
	*/
	public function mwb_sf_mwbfunnel_bulk_actions( $actions ) {
		$actions['enable']  = __( 'Enable', 'mwb-sale-funnel' );
		$actions['disable']  = __( 'Disable', 'mwb-sale-funnel' );
		return $actions;
	}

	/**
	* Funnel Post Type Bulk Actions
	*
	* @since     1.0.0
	* @param     string 		$redirect 		    redirect
	* @param     string 		$action 		    action
	* @param     array 			$post_ids 		    Post IDS
	*/
	public function mwb_sf_handle_mwbfunnel_bulk_actions( $redirect, $action, $post_ids ) {
		if( $action != 'enable' && $action != 'disable' ) {
			return $redirect;
		}
		foreach ($post_ids as $key => $value) {
			if( $action == 'enable' ) {
				 update_post_meta( $value, 'mwb_sf_funnel_status', true);
			}
			elseif( $action == 'disable' ) {
				 update_post_meta( $value, 'mwb_sf_funnel_status', false);
			}
		}
		return $redirect;
	}
}
