/**
 * Datepicker options
 * 
 * @author ilGhera
 * @package wc-restaurant-booking/js
 * @since 1.0.0
 */

var wprbDatepickerOptions = function() {

	jQuery(function($){

		var disabledDays    = wprbSettings.closingDays;
		var disabledPeriods = wprbSettings.closingPeriods;
		var day;
	    var isDisabled;
		var time;
		var period;
		var from;
		var to;
		var from_time;
		var to_time;

		$('.datepicker-here').datepicker({

			minDate: new Date(),
			autoClose: true,

			onRenderCell: function (date, cellType) {
	            
	            /*Useful to compare dates*/
	            date.setHours(0,0,0,0);

	            time = date.getTime();
		        
		        if (cellType == 'day') {
		            
		            day        = date.getDay();
	                isDisabled = disabledDays.indexOf(day) != -1;

	                for (var i = 0; i < disabledPeriods.length; i++) {

						period = JSON.parse( disabledPeriods[i] );
						from   = new Date(period.from);
						to     = new Date(period.to);

						/*Useful to compare dates*/
						from.setHours(0,0,0,0);
						to.setHours(0,0,0,0);

						from_time = from.getTime();
						to_time   = to.getTime();

						if ( time >= from_time && time <= to_time ) {

							isDisabled = true;

						}

					}

		            return {

		                disabled: isDisabled
		            
		            }
		        }
		    }

		})

	})

}

jQuery(document).ready(function($){

	wprbDatepickerOptions();	

})
