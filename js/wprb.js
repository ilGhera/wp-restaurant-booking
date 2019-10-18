/**
 * JS
 * 
 * @author ilGhera
 * @package wc-restaurant-booking/js
 * @since 0.9.0
 */

var wprbController = function() {

	var self = this;

	self.onLoad = function() {

		self.people_check();

	}


	self.people_check = function() {

		jQuery(function($){

			var datepicker = $('.datepicker-here').datepicker().data('datepicker');

			$('.datepicker').on('click', function(){

				datepicker.clear();

				alert('Please select people first');

			})

			// $('#wprb-booking-modal').modal({
			// 	// fadeDuration: 100			
			// });

		})

	}

}

/**
 * Class starter with onLoad method
 */
jQuery(document).ready(function($) {
	
	var Controller = new wprbController;
	Controller.onLoad();

});
