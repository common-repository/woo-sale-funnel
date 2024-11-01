<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://makewebbetter.com
 * @since      1.0.0
 *
 * @package    Woo_Sale_Funnel
 * @subpackage Woo_Sale_Funnel/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Sale_Funnel
 * @subpackage Woo_Sale_Funnel/public
 * @author     Make Web Better <webmaster@makewebbetter.com>
 */
class Mwb_Sale_Funnel_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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
		global $post;
		
		if( isset($post) && $post->post_type == 'mwbfunnelstep') {
			$fstep_id = $post->ID;

			$funnel_id = get_post_meta( $fstep_id, 'mwb_funnel_step_active', true);
			if( isset($funnel_id) && $funnel_id != null ) {
				wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );
				wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );
			}
		}
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mwb-sale-funnel-public.css', array(), $this->version, 'all' );
		wp_enqueue_style($this->plugin_name.'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css',array() );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mwb-sale-funnel-public.js', array( 'jquery' ), $this->version, false );

	}
	/**
	 * For adding upsell products to orders.
	 *
	 * @since      1.0.0
	 */
	public function mwb_sf_add_product_to_cart() {
		
		global $post;
		if( isset($post) && $post->post_type == 'mwbfunnelstep' ) {
			$fstep_id = $post->ID;

			$funnel_id = get_post_meta( $fstep_id, 'mwb_funnel_step_active', true);
			if( isset($funnel_id) && $funnel_id != null ) {
				$funnel_steps = get_post_meta( $funnel_id, 'mwb_funnel_steps', true);
							
				foreach ($funnel_steps as $key => $value) {
					if( $value[0] == $fstep_id ) {
						break;
					}
				}
				if( isset($_POST['add-to-cart']) && $_POST['add-to-cart'] != null ) {
					if( isset($funnel_steps[$key+1]) ) {
						$redirect = get_permalink($funnel_steps[$key+1][0]);
						wp_redirect( $redirect );
					}
				}
				if( isset( $_POST['mwb_upsell_buy_now'] ) && isset( $_POST['sf-order-key'] ) ) {
					if ( isset( $_POST['sf-order-key'] ) && $_POST['sf-order-key'] != null ) {
						$order_key = $_POST['sf-order-key'];
						$order_id = wc_get_order_id_by_order_key( $order_key );
						$order = wc_get_order($order_id);
						$product_id = $_POST['product_id'];
						$product = wc_get_product( $product_id );
						$product_type = $product->get_type();
						$upsell_id = $_POST['product_id'];
						if( $product_type == 'variable' ) {
							$upsell_id = $_POST['variation_id'];
						}
						$result = $this->mwb_sf_add_upsell_order( $order_key, $order->get_payment_method(), $upsell_id );

						if( $result ) {
							if( isset( $funnel_steps[$key+1] ) ) {
								$redirect = get_permalink( $funnel_steps[$key+1][0] ).'/?okey='.$order_key;
								wp_redirect( $redirect );
							}
							else {
								$url = $order->get_checkout_order_received_url();

								wp_redirect($url);
							}
						}
						else {
							$redirect = wc_get_page_permalink( 'shop' );
							wp_redirect( $redirect );
						}
					}
				}
			}
		}
	}
	/**
	 * Processing Checkout Function gor Upsell.
	 *
	 * @since      1.0.0
	 * @param      string    $order_key       Order key.
	 * @param      string    $pay_method    Payment Method.
	 * @param      product_id    $product_id    Product ID.
	 * @return    boolean    	  Order Payment Process result.
	 */
	public function mwb_sf_add_upsell_order( $order_key, $pay_method, $product_id ) {
		global $woocommerce;
		$result = false;
		$gateways = $woocommerce->payment_gateways->get_available_payment_gateways();
		if( !empty($gateways[$pay_method]) && ($pay_method=='cod'))
			{
			$product = wc_get_product( $product_id );
			if($product->is_purchasable()) {
				$sparent_order_id = wc_get_order_id_by_order_key( $order_key );
				$sparent_order = wc_get_order( $sparent_order_id );
				if ( !empty( $sparent_order )) {
					$parent_order_billing = $sparent_order->get_address('billing');
					if(!empty($parent_order_billing['email']))
					{
						$current_user_id = get_current_user_id();

						$original_price = $product->get_price();

						$upsell_order = wc_create_order( array(
							'customer_id' => $current_user_id,
						));
						$upsell_order->add_product($product, 1);

						$upsell_order->set_address($sparent_order->get_address('billing'),'billing');

						$upsell_order->set_address($sparent_order->get_address('shipping'), 'shipping' );

						$upsell_order->set_payment_method($gateways[$pay_method]);

						$upsell_order->calculate_totals();

						update_post_meta($upsell_order->get_id(),'mwb_sf_upsell_parent_order',$sparent_order->get_id());

						if($pay_method =='cod')
						{
							$result = $gateways[$pay_method]->process_payment($upsell_order->get_id());
						}
					}
				}
			}
		}
		return $result;
	}
	/**
	 * Updating Order URL.
	 *
	 * @since      1.0.0
	 * @param      string        $order_received_url       Order Receive URL.
	 * @param      Object        $order    				   Order.
	 * @return    String    	  Order Receive URL.
	 */
	public function mwb_sf_get_checkout_order_received_url( $order_received_url, $order) {
		$order_key = $order->get_order_key();
		$order_id = wc_get_order_id_by_order_key( $order_key );
		if( isset($_POST['mwb_sf_current_page_id']) && $_POST['mwb_sf_current_page_id'] != null ) {
			$post = get_post($_POST['mwb_sf_current_page_id']);
		
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
					$mwb_sf_payment_method = get_post_meta($order_id,'_payment_method',true);

			        if($mwb_sf_payment_method !=="cod")
			        {
			            return $order_received_url;
			        }
					if( isset($funnel_steps[$key+1]) ) {
						$order_received_url = get_permalink($funnel_steps[$key+1][0]).'/?okey='.$order_key;
					}
				}
			}
		}
		return $order_received_url;
	}
	/**
	 * Adding Hidden field for posting Funnel Step ID
	 *
	 * @since      1.0.0
	 * @param      Array        $checkout    	   Checkout Form Array.
	 */
	public function mwb_sf_woocommerce_before_checkout_billing_form( $checkout ) {
		global $post;
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
					?><input type="hidden" name="mwb_sf_current_page_id" value="<?php echo $fstep_id; ?>"><?php
				}
			}
		}
	}
	/**
	 * Adding Upsell Order Item To Order Table
	 * @since      1.0.0
	 * @param      Object        $parent_order    		Parent Order.
	 */
	public function mwb_sf_pro_order_items_table( $parent_order ) {
		
		
		if( !empty( $parent_order ) ) {

			$parent_order_id = $parent_order->get_id();

			$mwb_sf_order = get_posts(array(
				'posts_per_page' =>  -1,
				'post_type'      =>  'shop_order',
				'post_status'    =>  'any',
				'meta_key'       =>  'mwb_sf_upsell_parent_order',
				'meta_value'     =>   $parent_order_id,
				'orderby'        =>  'ID',
				'order'          =>  'ASC'
			));

			if( !empty( $mwb_sf_order )) {
				$show_purchase_note    = $parent_order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
				
				foreach ($mwb_sf_order as $key => $value) {
					$suborder = wc_get_order( $value->ID );
					$order_items           = $suborder->get_items();
					foreach ( $order_items as $item_id => $item ) {
						$product = apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );

						wc_get_template( 'order/order-details-item.php', array(
							'order'			     => $suborder,
							'item_id'		     => $item_id,
							'item'			     => $item,
							'show_purchase_note' => $show_purchase_note,
							'purchase_note'	     => $product ? $product->get_purchase_note() : '',
							'product'	         => $product,
						) );
					}	
				}
			}
		}
	}
	/**
	 * Adding Upsell Order Item To Order Table
	 * @since      1.0.0
	 * @param      Array        $total_rows    		Total Rows.
	 * @param      Object        $parent_order    	Parent Order
	 * @return    Array    	  Order Details
	 */
	public function mwb_sf_get_order_item_totals( $total_rows,$parent_order ) {
		// calculating new total for all purchased items

		if(!empty($parent_order))
		{
			$parent_id = $parent_order->get_id();
			$tax_display_item = get_option( 'woocommerce_tax_total_display' );
			$tax_display = get_option( 'woocommerce_tax_display_cart' );
			
			$suborder = get_posts(array(
				'posts_per_page' =>  -1,
				'post_type'      =>  'shop_order',
				'post_status'    =>  'any',
				'meta_key'       =>  'mwb_sf_upsell_parent_order',
				'meta_value'     =>   $parent_id,
				'orderby'        =>  'ID',
				'order'          =>  'ASC'
			));

			if(!empty($suborder))
			{
				$mwb_sf_parent_order_items 		= array();
				$mwb_sf_new_order_total 			= 0;
				$mwb_sf_new_order_subtotal 		= 0;
				$mwb_sf_new_order_shipping 		= 0;
				$mwb_sf_new_order_tax 			= 0;
				$mwb_sf_new_order_discount 		= 0;

				$mwb_sf_parent_order_items=$parent_order->get_items();

				foreach($mwb_sf_parent_order_items as $mwb_sf_single_item)
				{
					if( $tax_display == 'excl') {
						$mwb_sf_new_order_subtotal += $parent_order->get_line_subtotal( $mwb_sf_single_item);
					}
					else {
						$mwb_sf_new_order_subtotal += $parent_order->get_line_subtotal( $mwb_sf_single_item,true);
					}
					
					

					$mwb_sf_new_order_total += $parent_order->get_line_total($mwb_sf_single_item);
				}
				if( $tax_display == 'excl') {
					$mwb_sf_new_order_shipping += $parent_order->get_total_shipping();
				}
				else {
					$mwb_sf_new_order_shipping += $parent_order->get_total_shipping()+$parent_order->get_shipping_tax();
				}

				$mwb_sf_new_order_tax += $parent_order->get_total_tax();

				$mwb_sf_new_order_discount += $parent_order->get_total_discount();

				$mwb_sf_new_order_total += $parent_order->get_total_shipping() + $parent_order->get_total_tax();

				$suborder_factory = new WC_Order_Factory();

				foreach($suborder as $mwb_sf_single_order):

					$mwb_sf_upsell_order = $suborder_factory->get_order($mwb_sf_single_order->ID);

					$mwb_sf_upsell_order_items = $mwb_sf_upsell_order->get_items();
					
					if(!empty($mwb_sf_upsell_order_items))
					{
						
						foreach($mwb_sf_upsell_order_items as $mwb_sf_single_upsell_order_item)
						{
							if( $tax_display == 'excl') {
								$mwb_sf_new_order_subtotal += $mwb_sf_upsell_order->get_line_subtotal( $mwb_sf_single_upsell_order_item);
							}else {
								$mwb_sf_new_order_subtotal += $mwb_sf_upsell_order->get_line_subtotal( $mwb_sf_single_upsell_order_item, true);
							}

							$mwb_sf_new_order_total += $mwb_sf_upsell_order->get_line_total($mwb_sf_single_upsell_order_item);

						}
						
						$mwb_sf_new_order_shipping += $mwb_sf_upsell_order->get_total_shipping()+$mwb_sf_upsell_order->get_shipping_tax();

						$mwb_sf_new_order_tax += $mwb_sf_upsell_order->get_total_tax();

						$mwb_sf_new_order_discount += $mwb_sf_upsell_order->get_total_discount();
						
						$mwb_sf_new_order_total += $mwb_sf_upsell_order->get_total_shipping() + $mwb_sf_upsell_order->get_total_tax();
						
					}
				endforeach;

				if( !empty( $mwb_sf_new_order_subtotal ) )
				{
					$total_rows['cart_subtotal']['value'] = wc_price( $mwb_sf_new_order_subtotal );
				}

				if( !empty( $mwb_sf_new_order_discount ) )
				{
					$total_rows['discount']['value'] = '-' . wc_price( $mwb_sf_new_order_discount );
				}

				if( !empty( $mwb_sf_new_order_shipping ) )
				{
					$total_rows['shipping']['value'] = wc_price( $mwb_sf_new_order_shipping ) . '&nbsp;<small class="shipped_via">' . sprintf( __( 'via %s', 'woocommerce' ), $parent_order->get_shipping_method() ) . '</small>';
				}

				if( !empty( $mwb_sf_new_order_total ) )
				{
					$total_rows['order_total']['value'] = wc_price( $mwb_sf_new_order_total );
				}
				if(!empty( $mwb_sf_new_order_tax ))
                {
                    if($tax_display_item!=='itemized')
                    {
                        $total_rows['tax']['value'] = wc_price( $mwb_sf_new_order_tax );
                    }
                    else
                    {
                        $tax_totals = $parent_order->get_tax_totals();
                        
                        if(!empty($tax_totals))
                        {
                            foreach ($tax_totals as $code => $single_row){
                                $total_rows[sanitize_title($code)]['value'] = wc_price( $mwb_sf_new_order_tax );
                            }
                        }
                    }
                }

			}
		}

		return $total_rows;	
	}

	/**
	 * New Order Total Including Upsell Order
	 * @since      1.0.0
	 * @param      Integer        $total    		Order Total.
	 * @param      Object        $parent_order    	Parent Order
	 * @return    Integer    	  Order Total
	 */
	public function mwb_sf_get_new_total( $total,$parent_order ) {
		// updating total after upsell purchases

		$mwb_sf_new_total = 0;

		$mwb_sf_new_total = $mwb_sf_new_total + $parent_order->get_total('other');

		if(!empty($parent_order))
		{
			$mwb_sf_order_id = $parent_order->get_id();

			$mwb_sf_order = get_posts(array(
				'posts_per_page' =>  -1,
				'post_type'      =>  'shop_order',
				'post_status'    =>  'any',
				'meta_key'       =>  'mwb_sf_upsell_parent_order',
				'meta_value'     =>   $mwb_sf_order_id,
				'orderby'        =>  'ID',
				'order'          =>  'ASC'
			));
		}

		if(!empty( $parent_order))
		{
			if(!empty($mwb_sf_order))
			{
				$mwb_sf_order_factory = new WC_Order_Factory();

				foreach($mwb_sf_order as $mwb_sf_single_order):

					$mwb_sf_upsell_order = $mwb_sf_order_factory->get_order($mwb_sf_single_order->ID);

					$mwb_sf_new_total += $mwb_sf_upsell_order->get_total();

				endforeach;

				$total = $mwb_sf_new_total;
			}
		}

		return $total;
	}

	/**
	 * Fetching All orders except upsell orders
	 * @since      1.0.0
	 * @param      Object        $order    	Parent Order
	 * @return    Array    	  Order Array
	 */
	public function mwb_sf_my_account_my_orders_query( $orders ) {
		$orders['meta_key']="mwb_sf_upsell_parent_order";
        $orders['meta_compare']='NOT EXISTS';
        return $orders;
	}

	/**
	 * Increasing Item Count
	 * @since      1.0.0
	 * @param      Integer        $count    	 	Count
	 * @param      Integer        $item_type    	Item Type
	 * @param      Object         $parent_order     Parent Order
	 * @return    Integer    	  Total Item Counts
	 */
	public function mwb_sf_get_item_count( $count, $item_type, $parent_order ) {
		if(!empty($parent_order))
        {
            $mwb_sf_order_id = $parent_order->get_id();

            $mwb_sf_order = get_posts(array(
                'posts_per_page' =>  -1,
                'post_type'      =>  'shop_order',
                'post_status'    =>  'any',
                'meta_key'       =>  'mwb_sf_upsell_parent_order',
                'meta_value'     =>   $mwb_sf_order_id,
                'orderby'        =>  'ID',
                'order'          =>  'ASC'
            ));
            if(!empty($mwb_sf_order))
            {
                $count += count($mwb_sf_order);
                return $count;
            }
        }
        return $count;
	}
}