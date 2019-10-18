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

		self.wprb_pagination();
		self.tzCheckbox();
		self.external_seats();
		self.add_hours();
		self.remove_hours();
		self.auto_complete_fields();
		self.auto_complete_fields(true);
	}

	/**
	 * Tab navigation
	 */
	self.wprb_pagination = function() {

		jQuery(function($){

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
	self.external_seats = function() {

		jQuery(function($){

			var col             = $('.wprb-col.external');
			var field_container = $('.wprb-activate-external-seats-field');
			var field           = $('input', field_container);

			var field_check = function() {

				if ( 'checked' == field.attr('checked') ) {

					col.show();

				} else {

					col.hide();

				}
			
			}
			field_check();

			$('span.tzCheckBox', field_container).on('click', function(){

				field_check();

			})	

		})

	}


	/**
	 * Add a new hours element
	 */
	self.add_hours = function() {

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
	self.remove_hours = function() {

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
	self.auto_complete_fields = function( external = false ) {

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

}

/**
 * Class starter with onLoad method
 */
jQuery(document).ready(function($) {
	
	var Controller = new wprbAdminController;
	Controller.onLoad();

});
