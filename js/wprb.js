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
				minDate: new Date()
			})

		})

	}

	/**
	 * Update bookable based on people and date selections
	 *
	 * @param  {int}    people the number of people of the reservation
	 * @param  {string} date   the reservation date
	 */
	self.hours_available_update = function(people = null, date = null) {

		jQuery(function($){

			var data;

			/*Delete until value*/
			$('.until-field').val('');

			data = {
				'action': 'wprb-hours-available',
				'wprb-change-date-nonce': wprbSettings.changeDateNonce,
				'people': people,
				'date': date
			}

			$.post(wprbSettings.ajaxURL, data, function(response){

				$('.booking-step.booking-hours').html(response);

			})

		})

	}


	/**
	 * Widget date selection
	 */
	self.date_select = function() {

		jQuery(function($){

			var datepicker    = $('.datepicker-here').datepicker().data('datepicker');
			var people        = $('.booking-people .people-container');
			var people_select = $('.booking-people_numbers__number select');
			var date_selected;
			var options;
			var date;
			var data;

			$('.datepicker').on('click', function(){

				date_selected = $('.datepicker-here').data('datepicker').selectedDates[0];

				options = {
					day: '2-digit',
					month: 'short', 
					year: 'numeric'
				}

				date         = date_selected.toLocaleString('en-EN', options);
				display_date = date_selected.toLocaleString(wprbSettings.locale, options);

				/*Get the max bookable number*/
				data = {
					'action': 'wprb-get-max-bookable',
					'wprb-max-bookable-nonce': wprbSettings.maxBookableNonce,
					'date': date,
				}

				$.post(wprbSettings.ajaxURL, data, function(response){

					$(people_select).html(response);

				})

				people_val = $('.people-field').val();

				if ( people_val ) {

					/*Update available hours*/
					self.hours_available_update( people_val, date );

				}

				/*Add data*/
				$('li.date .value').html(display_date); // temp.
				$('.date-field').val(date);

				/*Activate people element*/
				$('.people').addClass('active');
				$(people).addClass('active');
				$(people_select).removeAttr('disabled');
					
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
			var date;

			$(number).on('click', function(){

				people = $('.people-field').attr('value');
				
				/*The date must be set before*/
				if( '' == $('.date-field').val() ) {
					
					alert(wprbSettings.dateFirstMessage);

				} else {
				
					/*Activate only the number selected*/
					$(number).removeClass('active');
					$(select).closest('li').removeClass('active');
					$(select).val('');
					$(this).addClass('active');

					/*Activate time element*/
					$('.time').addClass('active');
					
					/*Add data*/
					$('li.people .value').html($(this).val());
					$('.people-field').val($(this).val());

					date = $('.date-field').val();

					if (date) {

						/*Get bookables*/
						self.hours_available_update( $(this).val(), date );

					}

					/*Activate the hours step*/
					$('.booking-step').removeClass('active');
					$('.booking-hours').addClass('active');


				}


			})
			

			$(select).on('change', function(){

				/*Activate only the number selected*/
				$(number).removeClass('active');
				$(this).closest('li').addClass('active');

				/*Activate date element*/
				$('.date').addClass('active');

				/*Activate the calendar*/
				$(calendar).addClass('active');

				/*Add data*/
				$('li.people .value').html($(this).val());
				$('.people-field').val($(this).val());

				date = $('.date-field').val();

				if (date) {					
					/*Get bookables*/
					self.hours_available_update( $(this).val(), date );

				}

				/*Activate the hours step*/
				$('.booking-step').removeClass('active');
				$('.booking-hours').addClass('active');

			})

		})

	}


	/*Move to last booking step*/
	self.go_to_last_step = function() {

		jQuery(function($){

			/*Activate complete element*/
			$('.complete').addClass('active');

			/*Activate the complete step*/
			$('.booking-step').removeClass('active');
			$('.booking-complete').addClass('active');	

		})

	}


	/**
	 * Display external seats option if available
	 *
	 * @param  {int} internals the internal seats available
	 * @param  {int} externals the external seats available
	 */
	self.external_seats = function( internals, externals ) {

		jQuery(function($){

			var date   = $('.date-field').val();
			var people = $('.people-field').val();
			var interested;

			if ( people <= parseInt(externals) && people > parseInt(internals) ) {

				$('.wprb-external-container.choise').slideUp();
				$('.wprb-external-container.only').slideDown();

			} else if (  people <= parseInt(externals) && people <= parseInt(internals) ) {

				$('.wprb-external-container.only').slideUp();
				$('.wprb-external-container.choise').slideDown();

			} else {

				$('.wprb-external-container.only').slideUp();
				$('.wprb-external-container.choise').slideUp();

				self.go_to_last_step();

			}

			$('.wprb-external-container a').on('click', function(){

				$('.wprb-external-container a').removeClass('active');
				
				$(this).addClass('active');
				
				interested = $(this).hasClass('yes') ? 1 : 0;

				/*Add data*/
				$('.external-field').val(interested);

				self.go_to_last_step();

			})					

		})

	}


	/**
	 * Widget time selection
	 */
	self.time_select = function() {

		jQuery(function($){

			$(document).on('click', '.booking-hours ul li input', function(){

				var parent_li = $(this).closest('li');
				var internals = $(parent_li).data('internal');
				var externals = $(parent_li).data('external');
				var until;

				if( $(parent_li).hasClass('not-available') ) {
					return;
				}

				/*Change active element*/
				$('.booking-hours ul li input').removeClass('active');
				$(this).addClass('active');

				/*Add data*/
				$('li.time .value').html($(this).val());
				$('.time-field').val($(this).val());
				
				/*Lat minute*/
				if ($(this).hasClass('last-minute')) {

					until = $(this).data('until');
			
					/*Add data*/
					$('.until-field').val(until);

					/*External not available*/
					$('.wprb-external-container').slideUp();

					self.go_to_last_step();
				
				} else if ( externals ) {

					/*temp*/
					self.external_seats( internals, externals );

				} else {

					self.go_to_last_step();

				}

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

			var title     = $('.wprb-widget-title');
			var container = ('#wprb-booking-modal .padding-2');
			var get_title;

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

				var data = {
					'action': 'wprb-reservation',
					'wprb-save-reservation-nonce': wprbSettings.saveReservationNonce,
					'values': values
				}

				$.post(wprbSettings.ajaxURL, data, function(response){

					$(container).html(response);

					get_title = $('.booking-end').data('title');
					
					$(title).text(get_title);

					$('.header-bar_steps li span').css('cursor', 'default');

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
