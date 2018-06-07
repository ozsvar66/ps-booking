$(function() {
	$(".psempty").parent().css("font-size","0px").css("height","0px").css("padding","0px").css("height","0px");
	$(".psempty-first").parent().css("border-top","1px solid silver");
	$(".psempty-first").parent().last().css("border-top","0px");
	$(".psempty-more").parent().hide();
	
	eval("$('#pscalendar').multiDatesPicker({ " + psbCalendarSetup + "});"); // psbCalendarSetup defined in page-Unit.php
	
	psbPrepareUnitFileUpload();
	
});

function psbPrepareUnitFileUpload() {
	$(".psb-file-upload").click(function() {
		var send_attachment_bkp = wp.media.editor.send.attachment;
		var button = $(this);
		wp.media.editor.send.attachment = function(props, attachment) {
			$(button).parent().prev().attr("src", attachment.url);
			$(button).prev().val(attachment.id);
			wp.media.editor.send.attachment = send_attachment_bkp;
		}
		wp.media.editor.open(button);
		return false;
	});
	
	$(".psb-file-remove").click(function(e) {
		e.stopPropagation();
		Dialog_YesNo(psbTxtIfRemove, psbTxtYES, psbTxtNOT, 'psbPrepareUnitFileRemove()');
		return false;
	});
}

function psbPrepareUnitFileRemove() {
	Dialog_CloseYesNo();
	var button = $(".psb-file-remove");
	var src = button.parent().prev().attr("data-src");
	button.parent().prev().attr("src", src);
	button.prev().prev().val("");
}