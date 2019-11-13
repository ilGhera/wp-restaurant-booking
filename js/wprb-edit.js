/**
 * Admin JS
 * 
 * @author ilGhera
 * @package wc-restaurant-booking/js
 * @since 0.9.0
 */

var wprbEditController = function() {

	var self = this;

	self.onLoad = function() {

		self.hours_element();
		self.auto_change_reservation_status();
		self.reservation_id_to_modal();
		self.modal_status_label_activate();
		self.modal_change_status();

	}


	/**
	 * Handles the hour setting in the single reservation
	 */
	self.hours_element = function() {

		jQuery(function($){

			var time_el     = $('li.wprb-hour input');
			var input       = $('input.wprb-time');
			var current_val = $(input).val();

			$(time_el).each(function(){

				if( current_val === $(this).val() ) {

					$(this).addClass('active');

				}

			})

			$(time_el).on('click', function(){

				$(time_el).removeClass('active')
				
				$(this).addClass('active');

				$(input).val( $(this).val() );

			})

		})

	}


	/**
	 * Auto-change the status when a table is assigned to the reservation
	 */
	self.auto_change_reservation_status = function() {

		jQuery(function($){

			var status_field = $('.wprb-status')

			$('.wprb-table').on('change', function(){

				if( '' == $(this).val() ) {

					$('.wprb-status').val('received');

				} else {

					$('.wprb-status').val('managed');

				}

			})

		})

	}

	
	/**
	 * Add class active to the status toggle clicked
	 */
	self.reservation_id_to_modal = function() {

		jQuery(function($){

			$(document).on('click', '.column-status .wprb-status-label', function(){

				$('.column-status .wprb-status-label').removeClass('active');

				$(this).addClass('active');

			});

		})

	}

	
	/**
	 * Activate the label of the current reservation status in the modal window
	 */
	self.modal_status_label_activate = function() {

		jQuery(function($){

			$('.column-status .wprb-status-label').on('click', function(){

				var current_status = $(this).data('status');

				$('#wprb-status-modal li').each(function(){

					$('a', this).removeClass('active');

					if( current_status == $(this).data('status') ) {
					
						$('a', this).addClass('active');

					}

				})

			})

		})

	}


	/**
	 * Change the status of a single reservation from the modal window
	 */
	self.modal_change_status = function() {

		jQuery(function($){

			$(document).on('click', '#wprb-status-modal li a', function(){

				var bouble_container = $('.wprb.update-plugins');
				var bouble           = $('.wprb.update-plugins span.update-count');
				var bouble_val       = $(bouble).html() ? $(bouble).html() : 0;
				var status_column    = $('.wprb-status-label.active').closest('td'); 
				var post_id          = $('.wprb-status-label.active').data('post-id');
				var status           = $(this).data('status');

				if( $(this).hasClass('active') ) {

					return;

				} else  {

					$('#wprb-status-modal li a').removeClass('active');
					$(this).addClass('active');

					var data = {
						'action': 'wprb-change-status',
						'wprb-change-status-nonce': wprbSettings.changeStatusNonce,
						'reservation-id': post_id,
						'status': status
					}
						
					$.post(ajaxurl, data, function(response){

						$(status_column).html(response);

						bouble_val = 'received' === status ? parseInt(bouble_val) + 1 : parseInt(bouble_val) - 1;


						if(1 <= bouble_val) {

							$(bouble).html(bouble_val);
							$(bouble_container).show();
						
						} else {

							$(bouble).html('');
							$(bouble_container).hide();

						}

					})

				}

			
			})

		})

	}

}

/**
 * Class starter with onLoad method
 */
jQuery(document).ready(function($) {
	
	var Controller = new wprbEditController;
	Controller.onLoad();

});
