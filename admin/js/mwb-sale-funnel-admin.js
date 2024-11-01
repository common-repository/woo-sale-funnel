(function( $ ) {
	'use strict';

	var ajaxURL = mwb_sf_localized.ajaxURL;

	$(document).ready(function(){
	 	
	 	$(document).find('.mwb-funnel-step').sortable();
	 	
	 	$(".mwb-products-type-select").on('click', function(){

	 		var selected_funnel = jQuery(this).attr('ftype');
	 		var funnel_id = jQuery("#mwb_fs_funnel_id").val();
	 		jQuery.ajax({
	 			url : ajaxURL,
	 			type : 'post',
	 			data : {
	 				action : 'mwb_sf_funnel_seletion',
	 				selected_funnel : selected_funnel,
	 				funnel_id : funnel_id
	 			},
	 			success : function(response){
	 				
	 				jQuery(".mwb-sf-type2").hide();
	 				jQuery(".mwb-sf-type3").html(response);
	 				jQuery(".mwb-sf-type3").show();
	 			}
	 		});
	 	});
	 	jQuery(document).on('click','.mwb-sf-step-name',function(){
	 		var funnel_step_id = jQuery(this).data('id');
	 		var ths = this;
	 		jQuery('.mwb_funnel_names').each(function(){
	 			jQuery(this).removeClass('sf-step-active');
	 		});
	 		jQuery('.mwb_loader_wrapper').show();
	 		jQuery(ths).parent().addClass('sf-step-active');
	 		jQuery.ajax({
	 			url : ajaxURL,
	 			type : 'post',
	 			data : {
	 				action : 'mwb_sf_funnel_template',
	 				funnel_step_id : funnel_step_id
	 			},
	 			success : function(response){
	 				// alert(response);
	 				$('.mwb-sf-perma-span').each(function(){
	 					jQuery(this).removeClass('mwb-sf-slug-active');
	 				});
	 				jQuery('#mwb-perma-step-each-'+funnel_step_id).addClass('mwb-sf-slug-active');
	 				jQuery('.mwb-funnel-step-template').html(response);
	 				jQuery('.mwb_loader_wrapper').hide();
	 			}
	 		});
	 	});
	 	jQuery(document).on('click','.mwb-funnel-template-selected',function(){
	 		var selected_template = jQuery(this).data('template_id');
	 		var selected_funnel_step = jQuery(this).data('step_id');
	 		var slug = jQuery('#mwb-step-slug-'+selected_funnel_step).val();
	 		jQuery('.mwb_loader_wrapper').show();
	 		jQuery.ajax({
	 			url : ajaxURL,
	 			type : 'post',
	 			data : {
	 				action : 'mwb_sf_seleted_template',
	 				selected_template : selected_template,
	 				selected_funnel_step : selected_funnel_step,
	 				slug:slug
	 			},
	 			success : function(response){
	 				
	 				jQuery('.mwb-funnel-step-template').html(response);
	 				jQuery('.mwb_loader_wrapper').hide();
	 			}
	 		});
	 	});
	 	jQuery(document).on('click','.mwb-funnel-template-preview',function(){
	 		var selected_template = jQuery(this).data('template_id');
	 		var selected_funnel_step = jQuery(this).data('step_id');
	 		var baseurl = jQuery('#mwb_step_perma_'+selected_funnel_step).data('permalink')+"?&preview=true&tid="+selected_template;
	 		window.open(baseurl,'_blank');
	 	});
	 	jQuery(document).on('click','.mwb-funnel-edit-template',function(){
	 		var selected_template = jQuery(this).data('template_id');
	 		var selected_funnel_step = jQuery(this).data('step_id');
	 		var baseurl = jQuery('#mwb_step_perma_'+selected_funnel_step).data('permalink')+"/?&forge_builder";
	 		window.open(baseurl,'_blank');
	 		
	 	});

	 	$(document).on('click','.mwb-sf-add-more', function(){
	 		var html = "<p>"+mwb_sf_localized.new_step+"</p><span>"+mwb_sf_localized.funnel_name+"</span><input type='text' id='mwb-sf-add-step-txt'><a href='javascript:void(0)' class='mwb-sf-add-more-submit'>"+mwb_sf_localized.create_step+"</a><a href='javascript:void(0)' class='mwb_sf-cancel-popup'>"+mwb_sf_localized.cancel+"</a>";
	 		$('.mwb-sf-add-more-pop').html(html);
	 		$('.mwb-sf-pop-wrap').show();
	 	});
	 	$(document).on('click', '.mwb-sf-add-more-submit',function(){
	 		var txt = $('#mwb-sf-add-step-txt').val();
	 		if(txt.length) {
	 			var funnel_id = $("#mwb_fs_funnel_id").val();
	 			var data = {
		 			action : 'mwb_sf_add_new_step',
		 			funnel_id : funnel_id,
		 			step_name : txt
		 		};
		 		$('.mwb_sf_ajax_loader').show();
		 		jQuery.ajax({
		 			url : ajaxURL,
		 			type : 'post',
		 			dataType: 'json',
		 			data : data,
		 			success : function(response){
		 				if( response.status ) {
		 					$('.mwb-funnel-step').append(response.message);
		 				}
		 				$('.mwb_sf_ajax_loader').hide();
		 				$('.mwb-sf-pop-wrap').hide();
		 			}
		 		});
	 		}
	 	});
	 	$(document).on('click', '.mwb_step_remove',function(){
	 		var step_id = $(this).data('id');
	 		var funnel_id = jQuery("#mwb_fs_funnel_id").val();
	 		var html = '<p>'+mwb_sf_localized.delete_step+'</p><span>'+mwb_sf_localized.delete_confirm+'</span><a href="javascript:void(0)" class="mwb_delete_yes" data-id="'+step_id+'" data-funnelid="'+funnel_id+'">Delete</a><a href="javascript:void(0)" class="mwb_sf-cancel-popup">'+mwb_sf_localized.cancel+'</a>';
	 		$('.mwb-sf-add-more-pop').html(html);
	 		$('.mwb-sf-pop-wrap').show();
	 	});
	 	$(document).on('click', '.mwb_delete_yes',function(){
	 		var step_id = $(this).data('id');
	 		var funnel_id = $(this).data('funnelid');
	 		var data = {
	 			action : 'mwb_sf_remove_step',
	 			step_id : step_id,
	 			funnel_id:funnel_id
	 		};
	 		$('.mwb_sf_ajax_loader').show();
	 		jQuery.ajax({
	 			url : ajaxURL,
	 			type : 'post',
	 			data : data,
	 			success : function(response){
	 				if ( response ) {
	 					
		 				if( $('#mwb-sf-step-'+step_id).find('span.mwb_funnel_names').hasClass('sf-step-active')) {
		 					$('.mwb-sf-perma-span').each(function(){
			 					jQuery(this).removeClass('mwb-sf-slug-active');
			 				});
			 				jQuery('#mwb-perma-step-each-'+step_id).remove();
			 				jQuery('.mwb-perma-title').remove();
		 					var html = '<span>'+mwb_sf_localized.selectfunnel+'<span>';
		 					$('.mwb-funnel-step-template').html(html);
		 				}
	 					$('#mwb-sf-step-'+step_id).remove();
	 					$('.mwb-sf-pop-wrap').hide();
	 				}
	 				$('.mwb_sf_ajax_loader').hide();
	 			}
	 		});
	 	});
	 	$(document).on('click', '.mwb_sf-cancel-popup',function(){
	 		$('.mwb-sf-pop-wrap').hide();
	 	});
	 	var name = '';
	 	var edit_id = 0;
	 	$(document).on('click', '.mwb_step_edit',function(){
	 		
	 		var step_id = $(this).data('id');
	 		if(edit_id == step_id){
	 			if(name == ''){
		 			name = $(this).data("name");
		 		}
	 		}
	 		else {
	 			name = $(this).data("name");
	 		}
	 		edit_id = step_id;
	 		
	 		var funnel_id = jQuery("#mwb_fs_funnel_id").val();
	 		var step_name = name;
	 		var html = '<p>'+mwb_sf_localized.edit_step+'</p><span>'+mwb_sf_localized.step_name+'</span><input type="text" id="mwb-sf-update-step-txt" value="'+step_name+'"><a href="javascript:void(0)" class="mwb_update_yes" data-id="'+step_id+'" data-funnelid="'+funnel_id+'">'+mwb_sf_localized.update+'</a><a href="javascript:void(0)" class="mwb_sf-cancel-popup">'+mwb_sf_localized.cancel+'</a>';
	 		$('.mwb-sf-add-more-pop').html(html);
	 		$('.mwb-sf-pop-wrap').show();
	 	});
	 	$(document).on('click', '.mwb_update_yes',function(){
	 		var step_id = $(this).data('id');
	 		var funnel_id = $(this).data('funnelid');
	 		var update_txt = $('#mwb-sf-update-step-txt').val();

	 		if ( update_txt.length ) {
	 			name = update_txt;
		 		var data = {
		 			action : 'mwb_sf_update_step_name',
		 			step_id : step_id,
		 			funnel_id:funnel_id,
		 			new_txt:update_txt
		 		};
		 		$('.mwb_sf_ajax_loader').show();
		 		jQuery.ajax({
		 			url : ajaxURL,
		 			type : 'post',
		 			data : data,
		 			success : function(response){
		 				if ( response ) {
		 					$('#mwb-sf-step-'+step_id).find('span.mwb_funnel_names a.mwb-sf-step-name').html(update_txt);
		 					$('#mwb-sf-step-'+step_id).find('span.mwb_funnel_names span.mwb-step-actions a.mwb_step_edit').attr('data-name', update_txt);
		 					$('.mwb-sf-pop-wrap').hide();
		 				}
		 				$('.mwb_sf_ajax_loader').hide();
		 			}
		 		});
		 	}
	 	});
	});

})( jQuery );
