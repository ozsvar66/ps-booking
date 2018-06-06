$(function() {
	$(".psempty").parent().css("font-size","0px").css("height","0px").css("padding","0px").css("height","0px");
	$(".psempty-first").parent().css("border-top","1px solid silver");
	$(".psempty-first").parent().last().css("border-top","0px");
	$(".psempty-more").parent().hide();
	
	eval("$('#pscalendar').multiDatesPicker({ " + psbCalendarSetup + "});"); // psbCalendarSetup defined in page-Unit.php
});
