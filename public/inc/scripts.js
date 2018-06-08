var psRoomSelected = 1;
var psBooking_occupiedDayFound = false;
$(function() {
	
	$('.psb-publicform').find(':input').click(function() {
		$(this).parents('.psb-form-row').find('.psb-input-error').removeClass('psb-input-error');
	});
	
	$("#psb_adults").click(function() {
		psOnRoomReinitNumof(psRoomSelected, 'children');
		psOnChild();
	});
	
	setTimeout('psbCheckRoomRadios()',500);
	
	if($('#psb_start').length) {
		$('#psb_start').datepicker({
			minDate: '0',
			numberOfMonths: 2,
			monthNames: psMonths,
			monthNamesShort: psMonthsShort,
			dayNames: psDays,
			dateFormat: psDateFormat,
			onSelect: function(dateStr) {
				var min = $(this).datepicker('getDate'); // Get selected checkin date
				if(min) {
					min.setDate(min.getDate() + psRoomNightsMin[psRoomSelected]);
				}
				$('#psb_end').datepicker('option', 'minDate', min || '0'); // Set other min, default to today
				$('#psb_end').val(''); // reset check-out
				psBooking_occupiedDayFound = false;
			},
	   	beforeShowDay: function(date){
				//var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
				var className = 'psb-enabled-day';
				var string = jQuery.datepicker.formatDate('mm/dd/yy', date);
				var valid = psRoomAvail[psRoomSelected].indexOf(string) == -1;
				if(!valid) className = 'psb-disabled-day';
				return [valid, className];
			},
			beforeShow : function() {
				if(!$('.psb-datepicker').length){
					$('#ui-datepicker-div').wrap('<span class="psb-datepicker"></span>');
				}
				setTimeout('psAfterInitCalendarGlobal()',100);
			}
		});
		
		// set max date for checkin when user select checkout date
		$('#psb_end').datepicker({
			minDate: '0',
			numberOfMonths: 2,
			monthNames: psMonths,
			monthNamesShort: psMonthsShort,
			dayNames: psDays,
			dateFormat: psDateFormat,
			/*
			onSelect: function(dateStr) {
				var max = $(this).datepicker('getDate'); // Get selected checkout date
				if (max) {
					max.setDate(max.getDate() - psRoomNightsMin[psRoomSelected]);
				}
				$('#psb_start').datepicker('option', 'maxDate', max || '+1Y+6M'); // Set other max, default to +18 months
			},
			*/
			beforeShowDay: function(date){
				var startDay = $('#psb_start').datepicker('getDate'); // Get selected checkin date
				var className = 'psb-enabled-day';
				var string = jQuery.datepicker.formatDate('mm/dd/yy', date);
				var valid = psRoomAvail[psRoomSelected].indexOf(string) == -1;
				if(valid) {
					if(startDay) {
						if(date.getTime()>startDay.getTime() + psRoomNightsMax[psRoomSelected]*24*60*60*1000) valid = false;
					}
				}
				else className = 'psb-disabled-day';
				
				if(startDay) {
					var d = $('#psb_start').datepicker('getDate'); // check-in date
					var startLast = new Date();
					startLast.setTime( d.getTime() + psRoomNightsMin[psRoomSelected]*24*60*60*1000);
					var startLastTime = startLast.getTime();
					var lastPossibleDay = new Date();
					lastPossibleDay.setTime( d.getTime() + psRoomNightsMax[psRoomSelected]*24*60*60*1000);
					var lastPossibleTime = lastPossibleDay.getTime();
					var sec = 1; // security counter
					do {
						if(date.getTime()==d.getTime()) {
							className += " psb-start-day";
							break;
						}
						d.setTime(d.getTime() + 24*60*60*1000);
						if(d.getTime()>startLastTime) break;
						sec++;
						if(sec>psRoomNightsMin[psRoomSelected]) break;
					}while(true);
				}
				
				if(date.getTime()>startLastTime) {
					if(className=='psb-disabled-day') psBooking_occupiedDayFound = true;
					else if(psBooking_occupiedDayFound) valid = false;
				}
				
				return [valid, className];
			},
			beforeShow : function() {
				if(!$('.psb-datepicker').length){
					$('#ui-datepicker-div').wrap('<span class="psb-datepicker"></span>');
				}
				setTimeout('psAfterInitCalendar()',100);
			}
		});
	}
});

function psTdViaDate(date) {
	var jqTD = null;
	$('TD[data-month="'+date.getMonth()+'"]').each(function() {
		if($(this).data('year')==date.getFullYear() && $(this).find('a').text()==date.getDate()) {
			jqTD = $(this);
			return;
		}
	});
	return jqTD;
}

function psDateViaTD(jqTD) {
	var date = new Date()
	date.setFullYear(jqTD.data('year'));
	date.setMonth(jqTD.data('month'));
	date.setDate(jqTD.find('a').text());
	return date;
}

function psAfterInitCalendarGlobal() {
	if(!$('.psb-datepicker .ui-datepicker').length) {
		setTimeout('psAfterInitCalendarGlobal()',100);
		return;
	}
	$('.psb-datepicker .ui-datepicker').prepend($(''
		+ '<div class="ui-datepicker-header ui-widget-header ui-corner-all psb-calendar-title">'
		+   '<div class="psb-cal-room">' + $('label[for="psb_roomtype_' + $('input[name="psb_roomtype"]:checked').val() + '"]').text() + '<div>'
		+   '<div class="psb-cal-nights">'
		+     psBooking_textMinNights + ': ' + psRoomNightsMin[psRoomSelected] + ' / '
		+     psBooking_textMaxNights + ': ' + psRoomNightsMax[psRoomSelected]
		+   '</div>'
		+ '</div>'
	));
}

function psAfterInitCalendar() {
	if(!$('.psb-datepicker').length || !$('.psb-datepicker .ui-datepicker').length) {
		setTimeout('psAfterInitCalendar()',100);
		return;
	}
	
	psAfterInitCalendarGlobal();
	
	var x = $('.psb-datepicker').find('.psb-start-day');
	if(x.length) $(x[0]).addClass('psb-start-day-first');
	
	$('.psb-enabled-day').hover(
		function(e) { // hover in
			psSetInterval($(e.currentTarget));
		},
		function(e) { // hover out
		}
	);
}

function psSetInterval(jqTD) {
	$('.psb-datepicker .psb-day-last').removeClass('psb-day-last'); // reset all
	$('.psb-datepicker .psb-interday').removeClass('psb-interday'); // reset all
	jqTD.addClass('psb-day-last');
	var dLast = psDateViaTD(jqTD);
	var d = $('#psb_start').datepicker('getDate'); // check-in date
	var dx = new Date();
	dx.setTime( d.getTime() + psRoomNightsMin[psRoomSelected]*24*60*60*1000);
	while(dx.getTime()<dLast.getTime()) {
		if((x=psTdViaDate(dx))!=null) x.addClass('psb-interday');
		dx.setTime(dx.getTime() + 24*60*60*1000);
	}
}

function psOnRoom(roomtypeIndex) {

	if(typeof(psOnRoom_MODIFIED)!='undefined') { var result = psOnRoom_MODIFIED(roomtypeIndex); if(result!="continue") return; }
	
	$("#psb-check-dates").css('display','flex');
	psRoomSelected = roomtypeIndex;
	$('#psb_start').val(''); // reset check-in
	$('#psb_end').val(''); // reset check-out
	psBooking_occupiedDayFound = false;
	
	$('.psb-ci-value').html( psMaxPersons[roomtypeIndex] );
	
	psOnRoomReinitNumof(roomtypeIndex, 'adults');
	psOnRoomReinitNumof(roomtypeIndex, 'children');
	psOnChild();
	psOnRoomShowRoomDetails(roomtypeIndex);
	
	if(typeof(psOnRoom_FINISHER)!='undefined') psOnRoom_FINISHER(roomtypeIndex);
	
}

function psOnRoomShowRoomDetails(roomtypeIndex) {

	if(typeof(psOnRoomShowRoomDetails_MODIFIED)!='undefined') { var result = psOnRoomShowRoomDetails_MODIFIED(roomtypeIndex); if(result!="continue") return; }
	
	$('.psb-roomdetail').hide();
	$('#psb-roomdetail-'+roomtypeIndex).show();
	
	if(typeof(psOnRoomShowRoomDetails_FINISHER)!='undefined') psOnRoomShowRoomDetails_FINISHER(roomtypeIndex);
}

function psOnRoomReinitNumof(roomtypeIndex, selType) {

	if(typeof(psOnRoomReinitNumof_MODIFIED)!='undefined') { var result = psOnRoomReinitNumof_MODIFIED(roomtypeIndex, selType); if(result!="continue") return; }
	
	eval('var maxi = psBooking_byRoomMax_' + selType + '[' + roomtypeIndex + '];');
	if(selType=="children") maxi = psMaxPersons[psRoomSelected] - $('#psb_adults').val();
	
	var sel = $('#psb_' + selType);
	if(!sel.length) return;
	sel.children().remove();
	
	if(selType=="children") sel.append($('<option/>', { value:0, text:'0' } ));
	for(var i=1; i<=maxi; i++) sel.append($('<option/>', { value:i, text:i } ));
	
	if(typeof(psOnRoomReinitNumof_FINISHER)!='undefined') psOnRoomReinitNumof_FINISHER(roomtypeIndex, selType);
}

function psOnChild() {
	
	if(typeof(psOnChild_MODIFIED)!='undefined') { var result = psOnChild_MODIFIED(); if(result!="continue") return; }
	
	var numOfChildren = $('#psb_children').val();
	$('#psbAges').css('display',(numOfChildren>0 ? 'block' : 'none'));
	$('#psbAges .psb-age').hide();
	for(var i=1; i<=numOfChildren; i++) $('#psbAges-'+i).show();
	
	if(typeof(psOnChild_FINISHER)!='undefined') psOnChild_FINISHER();
}

function psCheckForm(oForm) {
	
	if(typeof(psCheckForm_MODIFIED)!='undefined') { var result = psCheckForm_MODIFIED(); if(result!="continue") return; }
	
	var failedLabels = [];
	var failedInputs = [];
	$('.psb-obly').each(function() {
		var inp = $(this).parent().find(':input');
		if(inp.length) {
			var value = null;
			if(inp[0].tagName=='TEXTAREA') value = inp.val() ? inp.val() : null;
			else switch(inp.attr('type')) {
				case 'text':
					value = inp.val() ? inp.val() : null;
					break;
				case 'radio':
					$('input[name="'+inp.attr('name')+'"]').each(function() {
						if($(this).is(':checked')) value = $(this).val();
					});
					break;
				case 'checkbox':
					value = inp.is(':checked') ? 1 : null;
					break;
			}
			if(value==null) {
				failedLabels[failedLabels.length] = $(this);
				failedInputs[failedInputs.length] = inp;
				$(this).addClass('psb-input-error');
			}
		}
	});
	
	if(failedLabels.length) {
		psCheckFormAlert(failedLabels, failedInputs, psbOnEmptyInput);
		return false;
	}
	
	if(typeof(psCheckForm_FINISHER)!='undefined') return psCheckForm_FINISHER();
	else return true;
}

function psCheckFormAlert(failedLabels, failedInputs, psbOnEmptyInput) {

	if(typeof(psCheckFormAlert_MODIFIED)!='undefined') { var result = psCheckFormAlert_MODIFIED(failedLabels, failedInputs, psbOnEmptyInput); if(result!="continue") return; }
	
	var msg = psbOnEmptyInput;
	for(var i in failedLabels) msg += "\n - " + failedLabels[i].text();
	alert(msg);
	
	if(typeof(psCheckFormAlert_FINISHER)!='undefined') psCheckFormAlert_FINISHER(failedLabels, failedInputs, psbOnEmptyInput);
}

function psbCheckRoomRadios() {
	$('input[name="psb_roomtype"]').each(function() {
		if($(this).is(':checked')) psOnRoom( $(this).attr('id').replace(/psb_roomtype_/,'') );
	});
}









