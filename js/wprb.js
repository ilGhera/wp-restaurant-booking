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

		self.open_modal();

	}


	self.open_modal = function() {

		jQuery(function($){

			$('.booking-steps li span').on('hover', function(){

				// $(this).hide();

				$(this).animate({
					'widht': '55px',
					'heigh': '55px'
				})
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
