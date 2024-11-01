jQuery(document).ready( function($){
	jQuery('#woocommerce_mwb-sf-stripe-gateway_testmode').on("change",function(e){
		e.preventDefault();
		var live = jQuery('#woocommerce_mwb-sf-stripe-gateway_live_secret_key,#woocommerce_mwb-sf-stripe-gateway_live_publishable_key').closest('tr');
		var	test_sandbox = jQuery('#woocommerce_mwb-sf-stripe-gateway_test_secret_key,#woocommerce_mwb-sf-stripe-gateway_test_publishable_key').closest('tr');
		if($(this).is(':checked'))
		{
			test_sandbox.show();
			live.hide();
		}
		else
		{
			test_sandbox.hide();
			live.show();
		}
	}).change();
});