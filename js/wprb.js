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

		self.widget_options();
		self.people_select();
		self.date_select();
		self.time_select();
		self.manual_step_navigation();
		self.complete_reservation();
		self.reset_fields();
	}

	
	/**
	 * Set the modal widget options
	 */
	self.widget_options = function() {

		jQuery(function($){

			$('.datepicker-here').datepicker({
				// language: it,
				minDate: new Date()
			})

		})

	}


	/**
	 * Widget numer of people selection
	 */
	self.people_select = function() {

		jQuery(function($){

			var people   = $('.booking-people_numbers');
			var number   = $('.booking-people_numbers__number input');
			var select   = $('.booking-people_numbers__number select');
			var calendar = $('.datepicker-here');

			$(number).on('click', function(){
				
				/*Activate only the number selected*/
				$(number).removeClass('active');
				$(select).closest('li').removeClass('active');
				$(select).val('');
				$(this).addClass('active');

				/*Activate date element*/
				$('.date').addClass('active');

				/*Activate the calendar*/
				$(calendar).addClass('active');
				
				/*Add data*/
				$('li.people .value').html($(this).val());
				$('.people-field').val($(this).val());

			})

			$(select).on('change', function(){

				/*Activate only the number selected*/
				$(number).removeClass('active');
				$(this).closest('li').addClass('active');

				/*Add data*/
				$('li.people .value').html($(this).val());
				$('.people-field').val($(this).val());

				console.log($(this).val());
			})

		})

	}


	/**
	 * Widget date selection
	 */
	self.date_select = function() {

		jQuery(function($){

			var datepicker = $('.datepicker-here').datepicker().data('datepicker');
			var date_selected;
			var options;
			var date;

			$('.datepicker').on('click', function(){

				/*The number of people must be set*/
				if( '' == $('.people-field').val() ) {
					
					datepicker.clear();

					alert('Please select people first'); // temp.

				} else {

					/*Activate time element*/
					$('.time').addClass('active');

					// date_selected = $('.datepicker-here').data('datepicker').selectedDates[0].getDate();
					date_selected = $('.datepicker-here').data('datepicker').selectedDates[0];

					options = {
						day: '2-digit',
						month: 'short', 
						year: 'numeric'
					}

					date = date_selected.toLocaleString('en-EN', options);



					/*Add data*/
					$('li.date .value').html(date); // temp.
					$('.date-field').val(date);

					/*Activate the hours step*/
					$('.booking-step').removeClass('active');
					$('.booking-hours').addClass('active');

					console.log($('.datepicker-here').data('datepicker'));
					console.log(date_selected);
					
				}

			})
			
		})

	}


	/**
	 * Widget time selection
	 */
	self.time_select = function() {

		jQuery(function($){

			$('.booking-hours ul li input').on('click', function(){

				/*Change active element*/
				$('.booking-hours ul li input').removeClass('active');
				$(this).addClass('active');

				/*Add data*/
				$('li.time .value').html($(this).val());
				$('.time-field').val($(this).val());

				/*Activate complete element*/
				$('.complete').addClass('active');

				/*Activate the complete step*/
				$('.booking-step').removeClass('active');
				$('.booking-complete').addClass('active');


			})

		})

	}


	/**
	 * Set the modal widget navigation
	 */
	self.manual_step_navigation = function() {

		jQuery(function($){

			var first_step = ['booking-people', 'booking-date'];
			var step_selected;
			var step;

			$(document).on('click', '.header-bar_steps li.active', function(){

				step_selected = $(this).data('step');

				step = first_step.includes( step_selected ) ? '.booking-people, .booking-date' : '.' + step_selected;

				/*Deactivate other steps*/
				$('.booking-step').removeClass('active');

				/*Activate the selected step*/
				$(step).addClass('active');


			})			

		})

	}


	/**
	 * Check the single form field on mouse leave
	 *
	 * @param  {object} element the form field
	 */
	self.single_field_check = function( element ) {

		jQuery(function($){

			/*Single fields check*/
			$(element).on('focusout', function(){

				if( '' == $(element).val() ) {

					$(element).addClass('error');

				} else {

					$(element).removeClass('error');

				}

			})

		})

	}


	/**
	 * Check the widget form fields before submit 
	 * 
	 * @param  {Boolean} click if true all the empty fields turn red
	 */
	self.fields_check = function(click = false) {

		jQuery(function($){

			var fields = $('#wprb-reservation input');

			$(fields).each(function(n){

				if(!click) {
					
					/*Mouse leave*/
					self.single_field_check(this);

				}

				if( '' == $(this).val() ) {

					if(click) {

						$(this).addClass('error');

					}

				} 

			})

		})

	}


	/**
	 * Delete widget hidden fields values
	 * @return {[type]} [description]
	 */
	self.reset_fields = function() {

		jQuery(function($){
			$('.people-field').val('');
			$('.date-field').val('');
			$('.time-field').val('');
		})

	}


	/**
	 * Complete reservation with customer details
	 */
	self.complete_reservation = function() {

		jQuery(function($){

			/*General fields check*/
			self.fields_check();

			/*Fields check on mouse leave*/
			// self.single_field_check();

			$('.wprb-complete-reservation').on('click', function(){

				/*Fields check on submit*/
				self.fields_check(true);

			})

			$('#wprb-reservation').submit(function(e){

				e.preventDefault();

				var values = $(this).serializeArray();
				console.log(values);

				var data = {
					'action': 'wprb-reservation',
					'wprb-reservation-nonce': wprbSettings.nonce,
					'values': values
				}

				$.post(wprbSettings.ajaxURL, data, function(response){

					console.log(response);

				})

			})

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
