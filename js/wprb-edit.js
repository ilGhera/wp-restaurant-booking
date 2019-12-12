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

		self.people_element();
		self.date_element();
		self.hours_element();
		self.external_element();
		self.auto_change_reservation_status();
		self.reservation_id_to_modal();
		self.modal_status_label_activate();
		self.modal_change_status();

	}


	/**
	 * Get the hours element updated based on the admin selections
	 * 
	 * @param  {int}     people   the reservation people.
	 * @param  {string}  date     the reservation date.
	 * @param  {Boolean} back_end true if called from edit reservation.
	 */
	self.hours_element_update = function(people = null, date = null, back_end = false) {

		jQuery(function($){

			var hours_tr    = $('.wprb-hours');
			var last_minute = 0;
			var reservation_time;

			/*Delete until value*/
			$('.until-field').val('');

			/*Editing a last minute reservation*/
			if( $('table.wprb-reservation').hasClass('last-minute') ) {
				
				last_minute = 1;
			
			}

			/*Get current booking time if editing a reservation*/
			if (back_end) {

				reservation_time = $('table.wprb-reservation').data('time');

			}

			var data = {
				'action': 'wprb-hours-available',
				'wprb-change-date-nonce': wprbSettings.changeDateNonce,
				'people': people,
				'date': date,
				'back-end': back_end,
				'time': reservation_time,
				'last-minute': last_minute
			}

			$.post(ajaxurl, data, function(response) {

				$('.booking-hours').html(response);

				if ( ! $(hours_tr).hasClass('active') ) {

					$(hours_tr).addClass('active').show('slow');

				}

			})

		})

	}

	/**
	 * Handles the people setting in the single reservation
	 */
	self.people_element = function() {

		jQuery(function($){

			$('.wprb-people').on('change', function(){

				var date = $('.wprb-date').val();

				if ( date ) {

					self.hours_element_update($(this).val(), date, true);

				}

			})

		})

	}


	/**
	 * Activate the hours element if a date is set
	 */
	self.date_element = function() {

		jQuery(function($){

			var hours_tr = $('.wprb-hours');
			var people;
			var data;

			if('' != $('.wprb-date').val()) {
				$(hours_tr).show();
			}

			$('.wprb-date').on('change', function(){

				/*Not in the reservations page */
				if ( $(this).hasClass('list') ) {

					return;

				}
				
				people = $('.wprb-people').val();

				self.hours_element_update(people, $(this).val(), true);

			})

		})

	}


	/**
	 * Handles the hour setting in the single reservation
	 */
	self.hours_element = function() {

		jQuery(function($){

			var hours_el    = $('.booking-hours');
			var time_el     = $('li.wprb-hour.regular input');
			var input       = $('input.wprb-time');
			var until       = $('input.wprb-until');
			var current_val = $(input).val();
			var date        = $('.wprb-date').val();;
				
			if ( '' != $(until).val() ) {

				time_el = $('li.wprb-hour input.last-minute');

			}

			$(time_el).each(function(){

				if( current_val === $(this).val() ) {
						
					$(this).addClass('active');

				}

			})

			$(document).on('click', 'li.wprb-hour input', function(){

				$('li.wprb-hour input').removeClass('active')
				
				$(this).addClass('active');

				$(input).val( $(this).val() );
				
				/*Last minute*/
				if ($(this).hasClass('last-minute')) {

					$(until).val( $(this).data('until') );

					/*No external*/
					$('.wprb-external-container').slideUp();

				} else {

					$(until).val('');
					
					date = $('.wprb-date').val();

					self.external_element_update( date, $(this).val() );

				}

			})

		})

	}


	/**
	 * Display the external element on edit page load
	 */
	self.external_element = function() {

		jQuery(function($){

			var is_external = false;
			var date        = $('.wprb-date').val();
			var time        = $('.wprb-time').val();

			self.external_element_update( date, time, true );
		
		})

	}


	/**
	 * Check if an external reservation is available based on the admin selections
	 * 
	 * @param  {string}  date     the reservation date.
	 * @param  {string}  time     the reservation time.
	 * @param  {Boolean} on_load  true if called loading an edit reservation.
	 */
	self.external_element_update = function( date, time, on_load ) {

		jQuery(function($){

			var is_external      = 0;
			var people           = $('.wprb-people').val();
			var input            = $('input.wprb-external');
			var hour_selected    = $('.wprb-hour input.active');
			var internals 	     = $(hour_selected).closest('li').data('internal');
			var interested;

			if ( on_load && $('table.wprb-reservation').hasClass('external') ) {

				is_external = 1;

			} else {

				/*Deactivate buttons*/
				$('.wprb-external-container a.yes').removeClass('active');
				$('.wprb-external-container a.no').addClass('active');

				/*Delete the hidden field value*/
				$(input).val('');

			}

			var data = {
				'action': 'wprb-check-for-external-seats',
				'wprb-external-nonce': wprbSettings.externalNonce,
				'date': date,
				'time': time,
				'people': people,
				'back-end': 1,
				'is_external': is_external
			}

			$.post(ajaxurl, data, function(response){
				
				if ( response ) {

					if ( people <= parseInt(response) && people > parseInt(internals) ) {

						$('.wprb-external-container.choise').slideUp();
						$('.wprb-external-container.only').slideDown();

					} else if (  people <= parseInt(response) && people <= parseInt(internals) ) {

						$('.wprb-external-container.only').slideUp();
						$('.wprb-external-container.choise').slideDown();

					} else {

						$('.wprb-external-container.only').slideUp();
						$('.wprb-external-container.choise').slideUp();

					}

					/*Activate if current reservation is external */
					if ( is_external ) {

						$('.yes').addClass('active');

					}

					$('.wprb-external-container a').on('click', function(){

						$('.wprb-external-container a').removeClass('active');
						
						$(this).addClass('active');
						
						interested = $(this).hasClass('yes') ? 1 : 0;

						/*Add data*/
						$(input).val(interested);

					})					


				} else {

					$('.wprb-external-container').slideUp();
					$('.wprb-external-container a').removeClass('active');

				}

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
