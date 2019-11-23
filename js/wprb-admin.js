/**
 * Admin JS
 * 
 * @author ilGhera
 * @package wc-restaurant-booking/js
 * @since 0.9.0
 */

var wprbAdminController = function() {

	var self = this;

	self.onLoad = function() {

		self.wprbPagination();
		self.tzCheckbox();
		self.externalSeats();
		self.addHours();
		self.removeHours();
		self.addLastMinute();
		self.removeLastMinute();
		self.autoCompleteFields();
		self.autoCompleteFields(true);
		self.lastMinuteElement();

	}

	/**
	 * Tab navigation
	 */
	self.wprbPagination = function() {

		jQuery(function($){

			console.log( wprbSettings );

			var contents = $('.wprb-admin')
			var url = window.location.href.split("#")[0];
			var hash = window.location.href.split("#")[1];

			if(hash) {
		        contents.hide();		    
			    $('#' + hash).fadeIn(200);		
		        $('h2#wprb-admin-menu a.nav-tab-active').removeClass("nav-tab-active");
		        $('h2#wprb-admin-menu a').each(function(){
		        	if($(this).data('link') == hash) {
		        		$(this).addClass('nav-tab-active');
		        	}
		        })
		        
		        $('html, body').animate({
		        	scrollTop: 0
		        }, 'slow');
			}

			$("h2#wprb-admin-menu a").click(function () {
		        var $this = $(this);
		        
		        contents.hide();
		        $("#" + $this.data("link")).fadeIn(200);

		        self.chosen(true);
		        self.chosen();

		        $('h2#wprb-admin-menu a.nav-tab-active').removeClass("nav-tab-active");
		        $this.addClass('nav-tab-active');

		        window.location = url + '#' + $this.data('link');

		        $('html, body').scrollTop(0);

		    })

		})
	        	
	}


	/**
	 * Checkboxes
	 */
	self.tzCheckbox = function() {

		jQuery(function($){
			$('input[type=checkbox]').tzCheckbox({labels:['On','Off']});
		});

	}


	/**
	 * Fires Chosen
	 * @param  {bool} destroy method distroy
	 */
	self.chosen = function(destroy = false) {

		jQuery(function($){

			$('.wprb-select').chosen({
		
				disable_search_threshold: 10,
				width: '200px'
			
			});

			$('.wprb-select-large').chosen({
		
				disable_search_threshold: 10,
				width: '290px'
			
			});

		})

	}


	/**
	 *External seats option
	 */
	self.externalSeats = function() {

		jQuery(function($){

			var col             = $('.wprb-col.external');
			var fieldContainer  = $('.wprb-activate-external-seats-field');
			var field           = $('input', fieldContainer);

			var fieldCheck = function() {

				if ( 'checked' == field.attr('checked') ) {

					col.show();

				} else {

					col.hide();

				}
			
			}
			fieldCheck();

			$('span.tzCheckBox', fieldContainer).on('click', function(){

				fieldCheck();

			})	

		})

	}


	/**
	 * Add a new hours element
	 */
	self.addHours = function() {

		jQuery(function($){

			$(document).on('click', '.add-hours-hover', function(){


				var count = $('.hours-element').length;
				var prev  = $('.wprb-hours-element-' + count);
				var next  = count + 1;
				var data  = {
					'action': 'wprb-add-hours',
					'wprb-add-hours-nonce': wprbSettings.addHoursNonce,
					'number': next
				}

				console.log(prev);
				$.post(ajaxurl, data, function(response){

					$(prev).after(response);

				})

			})

		})

	}

	
	/**
	 * Remove an hour element
	 */
	self.removeHours = function() {

		jQuery(function($){

			$(document).on('click', '.remove-hours-hover', function(){

				var element = $(this).closest('.hours-element');

				$(element).remove();

			})

		})

	}


	/**
	 * Auto-complete the bookable and external fields of every day, copying the values from the firsts setted
	 *
	 * @param  {Boolean} external true for external field, default for bookable.
	 */
	self.autoCompleteFields = function( external = false ) {

		jQuery(function($){

			var element = true == external ? '#wprb-external-seats' : '#wprb-bookable-seats';
			var value;

			$(element + '.mon').on('change', function(){

				value = $(this).val();


				$('.wprb-set-reservations-day').each(function(){

					// console.log( element );
					console.log( 'TEST: ' + $(element, this).val() );

					if( ! $(element, this).val()) {

						$(element, this).val(value);

					}

				})

			})

		})

	}


	self.lastMinuteElement = function() {

		jQuery(function($){

			var toggle      = $('.wprb-activate-last-minute-field .tzCheckBox');
			var lastMinute  = $('.wprb-add-last-minute-field');

			if ($(toggle).hasClass('checked')) {

				lastMinute.show();

			}

			$(toggle).on('click', function(){
				
				if ($(this).hasClass('checked')) {

					lastMinute.show('slow');

				} else {

					lastMinute.hide();

				}
			
			})

		})

	}


	/**
	 * Add a new last minute
	 */
	self.addLastMinute = function() {

		jQuery(function($){

			$(document).on('click', '.add-last-minute-hover', function(){


				var count = $('.last-minute-element').length;
				var prev  = $('.wprb-last-minute-element-' + count);
				var next  = count + 1;
				var data  = {
					'action': 'wprb-add-last-minute',
					'wprb-add-last-minute-nonce': wprbSettings.addLastMinuteNonce,
					'number': next
				}

				$.post(ajaxurl, data, function(response){

					console.log(response);
					$(prev).after(response);

				})

			})

		})

	}

	
	/**
	 * Remove a last minute
	 */
	self.removeLastMinute = function() {

		jQuery(function($){

			$(document).on('click', '.remove-last-minute-hover', function(){

				var element = $(this).closest('.last-minute-element');

				$(element).remove();

			})

		})

	}

}

/**
 * Class starter with onLoad method
 */
jQuery(document).ready(function($) {
	
	var Controller = new wprbAdminController;
	Controller.onLoad();

});
