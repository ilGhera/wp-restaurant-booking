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

		self.change_reservation_status();
		self.modal_change_status();

	}


	/**
	 * Auto-change the status when a table is assigned to the reservation
	 */
	self.change_reservation_status = function() {

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
	 * Change the status of a single reservation from the modal window
	 */
	self.modal_change_status = function() {

		jQuery(function($){

			$('.wprb-status-label').on('click', function(){

				var current_status = $(this).data('status');

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
