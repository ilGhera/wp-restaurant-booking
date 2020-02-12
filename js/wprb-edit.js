/**
 * Admin JS
 * 
 * @author ilGhera
 * @package wc-restaurant-booking/js
 * @since 1.0.0
 */

var wprbEditController = function() {

	var self = this;

	self.onLoad = function() {

		self.tables_element_update();
		self.chosen();
		self.wprb_tooltipser();
		self.people_element();
		self.date_element();
		self.hours_element();
		self.last_minute_select();
		self.external_element();
		self.auto_change_reservation_status();
		self.reservation_id_to_modal();
		self.modal_status_label_activate();
		self.modal_change_status();


	}

	
	/**
	 * Fires Chosen
	 * @param  {bool} destroy method distroy
	 */
	self.chosen = function(destroy = false) {

		jQuery(function($){

			var select = $('.wprb-select');

			if (destroy) {

				$(select).chosen('destroy');

			} else {
			
				$(select).chosen({
			
					disable_search_threshold: 10
					// width: '200px'
				
				});

			}

		})

	}


	/**
	 * Tooltips
	 */
	self.wprb_tooltipser = function() {

		jQuery(function($){

            var targets = ['.wprb-hour.regular', 'ul.last-minute .wprb-hour'];
            var action; 

            /*Generic*/
            $('.tooltip').tooltipster({

        	   trigger: 'click'

            });

            for (var i = 0; i < targets.length; i++) {

            	$('body').on('mouseenter', targets[i] + ':not(.tooltipstered)', function(){

            		/*Using click or hover where required*/
            		action = 'click';

            		if ( ! $(this).hasClass('not-available') && $(this).hasClass('regular') ) {

	            		action = 'hover';

            		}

		            $(this).tooltipster({

		        	   trigger: action,
					   interactive: true

		            });

	            });
            	
            }

		})

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
			var reservation_people;

			/*Delete until value*/
			$('.until-field').val('');

			/*Editing a last minute reservation*/
			if( $('table.wprb-reservation').hasClass('last-minute') ) {
				
				last_minute = 1;
			
			}

			/*Get current booking time if editing a reservation*/
			if (back_end) {

				reservation_time   = $('table.wprb-reservation').data('time');
				reservation_people = $('table.wprb-reservation').data('people');

			}

			var data = {
				'action': 'wprb-hours-available',
				'wprb-change-date-nonce': wprbSettings.changeDateNonce,
				'people': people,
				'res_people': reservation_people,
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

			var hours_tr   = $('.wprb-hours');
			var dateOnLoad = $('.wprb-date').val();
			var people;
			var date;
			var date_selected;
			var display_date;
			var dateOptions = {
				day: '2-digit',
				month: '2-digit', 
				year: 'numeric'
			}


			/*Editing an existing reservation*/
			if('' != dateOnLoad) {

				/*Display hours*/
				$(hours_tr).show();

				/*Get reservation date*/
				display_date = new Date( dateOnLoad );

				/*Display formatted resevation date in the field*/
				setTimeout( function(){
					
					$('.datepicker-here').attr( 'value', display_date.toLocaleString(wprbSettings.locale, dateOptions) );

				}, 400)

			}

			$('.datepicker').on('click', function(){

				if ( $('.datepicker--cell.-focus-', this).hasClass( '-disabled-' ) ) {

					alert(wprbSettings.dateNotAvailableMessage);

				}

				date_selected = $('.datepicker-here').data('datepicker').selectedDates[0];

				if ( date_selected ) {

					date          = date_selected.toLocaleString('en-EN', dateOptions);
					display_date  = date_selected.toLocaleString(wprbSettings.locale, dateOptions);

					$('.wprb-date').attr( 'value', date );
					$('.datepicker-here').attr( 'value', display_date );
					
					people = $('.wprb-people').val();

					/*Update hours available*/
					self.hours_element_update(people, date, true);

					/*Update tables available*/
					self.tables_element_update( date, $(this).val() );

				}

			})

		})

	}


	/**
	 * Confirm the last minute hour selection from the tooltip
	 */
	self.last_minute_select = function() {

		jQuery(function($){

			var last_minute;
			var input = $('input.wprb-time');
			var until = $('input.wprb-until');


			$('body').on('click', '#until-message span', function(){

				last_minute = $('ul.last-minute .wprb-hour');
				
				if ($(this).hasClass('cancel')) {

					$('input', last_minute).removeClass('active');
					$(input).val('');
					$(until).val('');

				}

				$(last_minute).tooltipster('close');

			})

		})

	}


	/**
	 * Display the tables field only if resDate and resTime are set.
	 * Get tables available in a specified date and time.
	 *
	 * @param  {string} date the date
	 * @param  {string} time the time
	 * @return {string}      the field element
	 */
	self.tables_element_update = function( date = null, time = null ) {

		jQuery(function($){

			var tables_element = $('tr.wprb-tables');
			var resDate        = $('.wprb-date').val()
			var resTime        = $('.wprb-time').val();

			if ( date && time ) {

				var data = {
					'action': 'wprb-available-tables',
					'wprb-tables-nonce': wprbSettings.tablesNonce,
					'date': date,
					'time': time
				}

				$.post(ajaxurl, data, function(response){

					$('td', tables_element).html(response);
					tables_element.show('slow');
					self.chosen();

				})

			} else {

				if ( ! resDate || ! resTime ) {

					tables_element.hide();

				}

			}

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
			var date        = $('.wprb-date').val();

			if ( '' != $(until).val() ) {

				time_el = $('li.wprb-hour input.last-minute');

			}

			$(time_el).each(function(){

				if( current_val === $(this).val() ) {
						
					$(this).addClass('active');

				}

			})

			$(document).on('click', 'li.wprb-hour input', function(){

				if ( ! $(this).parent('li').hasClass( 'not-available' ) ) {

					$('li.wprb-hour input').removeClass('active')
					
					$(this).addClass('active');

					$(input).val( $(this).val() );

				}
				
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

				/*Update tables available*/
				self.tables_element_update( date, $(this).val() );

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
			var reservation_people = $('table.wprb-reservation').data('people');
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
				'res_people': reservation_people,
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

			$('.wprb-tables').on('change', function(){

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
