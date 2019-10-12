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

		self.wprb_pagination();
		self.tzCheckbox();
		self.external_seats();
		self.add_hours();
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

	self.add_hours = function() {

		jQuery(function($){

			$(document).on('click', '.add-hours-hover', function(){


				var count = $('#wprb-bookable-hours').length;
				var prev  = $('wprb-hours-element-' + count);
				var next  = count + 1;
				var data  = {
					'action': 'wprb-add-hours',
					'wprb-add-hours-nonce': wprbSettings.addHoursNonce,
					'number': next
				}

				$.post(ajaxurl, data, function(response){

					$(prev).next(response);

					console.log(response);

				})


				// var images_f  = wprbSettings.imagesFolderURL;
				// var container = '<div class="wprb-hours-element-' + next + '"></div>';
				// var field     = '<input type="time" name="wprb-bookable-hours-' + next + '" id="wprb-bookable-hours" class="wprb-bookable-hours" min="12:00" max="23:00">';
				// var del_el    = '<div class="wprb-remove-hours-container">';
	   //  			del_el   += '<img class="remove-hours" src="' + images_f + '/remove-icon.png">';
	   //  			del_el   += '<img class="remove-hours-hover" src="' + images_f + '/remove-icon-hover.png">';
	   //  			del_el   += '</div>';

				// $('.wprb-hours-element-' + count).after(container);
				// $(field).appendTo('.wprb-hours-element-' + next);
				// $(del_el).appendTo('.wprb-hours-element-' + next);

				// console.log( next );

			})

		})

	}

}

/**
 * Class starter with onLoad method+
 */
jQuery(document).ready(function($) {
	
	var Controller = new wprbController;
	Controller.onLoad();

});
