var Dialog_Index = 0;
var Dialog_widths = new Array();
var Dialog_heights = new Array();
var DIALOG_NOT_MODAL = true;
var Dialog_dictCloseText = "close";
var Dialog_inited = false;
var Dialog_justNowCreated = null;
var Dialog_daccdIconWidth = -1;
var Dialog_defaultPosition = { my: "center", at: "center", of: window };

function Dialog_Init(sCloseText) {
	Dialog_dictCloseText = sCloseText;
	Dialog_inited = true;
}

function Dialog_ShowNode(sNodeID, sTitle, sFunctionOnClose, isNotModal, aPosition, onOpen, sCloseButtonClasses) {
	
	var others = Dialog_findOthers(sNodeID);

	var iWidth = 0;
	if(Dialog_widths[sNodeID]) {
		iWidth = Dialog_widths[sNodeID];
	}
	else {
		iWidth = $("#"+sNodeID).width();
		Dialog_widths[sNodeID] = iWidth;
	}
	var myDialog = $("#" + sNodeID).dialog({
		modal:    typeof(isNotModal)!="undefined" && isNotModal==true ? false : true,
		position: typeof(aPosition)!="undefined" ? aPosition : Dialog_defaultPosition,
		width:    iWidth,
		maxWidth: iWidth,
		title:    sTitle,
		minHeight:0,
		//open: function() { $(".ui-dialog").css("box-shadow","#b4b4b4 5px 5px 20px"); },
		create: function(event, ui) { $('.ui-dialog-titlebar-close').attr('title', Dialog_dictCloseText); if(typeof(sCloseButtonClasses)!='undefined' && sCloseButtonClasses>'') $('.ui-dialog-titlebar-close').addClass(sCloseButtonClasses); },
		close: typeof(sFunctionOnClose)!="undefined" ? function() { eval(sFunctionOnClose); } : "",
		open: typeof(onOpen)!="undefined" ? onOpen : null 
	});

	if(typeof(sFunctionOnClose)=="function") {
		myDialog.on('dialogclose', sFunctionOnClose);
	}
	else if(typeof(sFunctionOnClose)=="string") {
		eval("myDialog.on('dialogclose', function(event,ui ) { " + sFunctionOnClose + " } );");
	}
	if(Dialog_heights[sNodeID]) {
		$("#" + sNodeID).dialog("option", "maxHeight", Dialog_heights[sNodeID]);
	}
	
	Dialog_bringBackOthers(others, sNodeID);
	
	return false;
}

function Dialog_getNextIndex() {
	var i = Dialog_Index;
	return ++i;
}

function Dialog_ShowUrl(sTitle, sUrl, iWidth, iHeight, sFunctionOnClose) {

	Dialog_Index++;
	
	var others = Dialog_findOthers("dialog_" + Dialog_Index);
	
	sUrl += (sUrl.search(/\?/)!=-1 ? "&" : "?") + "di=" + Dialog_Index;
	
	var oDiv = document.createElement("DIV");
	oDiv.id = "dialog_" + Dialog_Index;
	oDiv.style.display = "none";
	
	var sWidth = "" + iWidth;
	var sHeight = "" + iHeight;
	var maxDelta = 40;
	if(sWidth.search(/\%$/)==-1) {
		var ww = $(window).width();
		maxWidth = iWidth;
		maxWidth += maxDelta;
		sWidth = (ww>maxWidth ? iWidth : ww-maxDelta) + "px";
	} 
	if(sHeight.search(/\%$/)==-1) {
		var wh = $(window).height();
		maxHeight = iHeight;
		maxHeight += maxDelta;
		sHeight = (wh>iHeight ? iHeight : wh-maxDelta) + "px";
	}
	oDiv.style.width = sWidth;
	oDiv.style.height = sHeight;
	
	oDiv.style.margin = "0px";
	oDiv.style.padding = "0px";
	var oIframe = document.createElement("IFRAME");
	oIframe.id = "dialogframe_" + Dialog_Index;
	oIframe.style.width = iWidth + "px";
	oIframe.style.height = iHeight + "px";
	oIframe.style.border = "0px";
	oIframe.style.margin = "0px";
	oIframe.style.padding = "0px";
	oDiv.appendChild(oIframe);
	document.body.appendChild(oDiv);
	
	
	$("#dialogframe_" + Dialog_Index).css({
		width: sWidth,
		height: sHeight,
		border: "0px"
	});
	$("#dialogframe_" + Dialog_Index).attr("src", sUrl);
	
	/*
	Dialog_justNowCreated = $("#dialog_" + Dialog_Index).dialog({
		modal:    true,
		width:    iWidth,
		maxWidth: iWidth,
		title:    sTitle,
		open: function() { $(".ui-dialog").css("box-shadow","#b4b4b4 5px 5px 20px"); },
		beforeClose: function( event, ui ) { $("#dialogframe_" + Dialog_Index).attr("src", ""); },
		create: function(event, ui) { $('.ui-dialog-titlebar-close').attr('title', Dialog_dictCloseText); },
		close: typeof(sFunctionOnClose)!="undefined" ? function() { eval(sFunctionOnClose); } : ""
	});
	*/
	
	if(typeof(sFunctionOnClose)!="undefined" && sFunctionOnClose!=null && sFunctionOnClose>"") {
		eval('Dialog_justNowCreated = $("#dialog_" + Dialog_Index).dialog({modal:true,width:iWidth,maxWidth:iWidth,title:sTitle,open: function() { $(".ui-dialog").css("box-shadow","#b4b4b4 5px 5px 20px"); },beforeClose: function( event, ui ) { $("#dialogframe_" + Dialog_Index).attr("src", ""); },create: function(event, ui) { $(".ui-dialog-titlebar-close").attr("title", Dialog_dictCloseText); },close:'+sFunctionOnClose+'});');
	}
	else {
		eval('Dialog_justNowCreated = $("#dialog_" + Dialog_Index).dialog({modal:true,width:iWidth,maxWidth:iWidth,title:sTitle,open: function() { $(".ui-dialog").css("box-shadow","#b4b4b4 5px 5px 20px"); },beforeClose: function( event, ui ) { $("#dialogframe_" + Dialog_Index).attr("src", ""); },create: function(event, ui) { $(".ui-dialog-titlebar-close").attr("title", Dialog_dictCloseText); }});');
	}
	
	Dialog_bringBackOthers(others, "dialog_" + Dialog_Index);
	
	return false;
}

function Dialog_Close(iIndex) {
	if(typeof(iIndex)!="undefined") {
		if(""+parseInt(iIndex)==""+iIndex) {
			if(typeof($("#dialog_" + iIndex))!="undefined") $("#dialog_" + iIndex).dialog("close");
		}
		else if(iIndex=="::LAST::") $("#dialog_" + Dialog_Index).dialog("close");
		else $("#" + iIndex).dialog("close");
	}
	return false;
}

function Dialog_findOthers(id) {
	var others = new Array();
	$(".ui-dialog").each(function() {
		if((other_id=$(this).find(".ui-dialog-content").attr("id"))!=id) {
			if($("#"+other_id+"_spec_dialog_overlay").length==0) {
				if(typeof($("#"+other_id).dialog)!="function" && $("#"+other_id).dialog("isOpen")===true) others[others.length] = other_id;
			}
		}
	});
	return others;
}

function Dialog_bringBackOthers(others, id) {
	var s = "";
	$.each(others, function(i,other_id) {
		s += (s?" ":"") + "Dialog_setToNormal('"+other_id+"');";
		Dialog_moveBack(other_id, id);
	});
	if(s) eval("$('#"+id+"').on('dialogclose', function(event,ui) { " + s + "});");
}

function Dialog_moveBack(backId, topId) {
	div = $("<div>");
	div.attr("id", backId + "_spec_dialog_overlay");
	div.css("position", "absolute");
	div.css($("#"+backId).closest('.ui-dialog').offset());
	div.css("background-color", "silver");
	div.css("height", $("#"+backId).closest('.ui-dialog').height()+10);
	div.css("width", $("#"+backId).closest('.ui-dialog').width()+10);
	div.css("z-index", $("#"+backId).closest('.ui-dialog').zIndex());
	div.css("opacity", 0.6);
	div.append("");
	$("body").append(div);
}

function Dialog_setToNormal(id) {
	Dialog_CloseYesNo();
	$("#"+id+"_spec_dialog_overlay").remove();
}

function Dialog_YesNo(sConfirmText, sTextYes, sTextNo, sFuncYes, sFuncNot) {
	var idYes = "dialogAutoCreatedConfirmYes" + Math.floor((Math.random() * 99999) + 9999);
	var idNot = "dialogAutoCreatedConfirmNot" + Math.floor((Math.random() * 99999) + 9999);
	var sContent = ''
		+ '<div class="daccd-info-holder">'
		+ '  <div class="daccd-icon">&nbsp;</div>'
		+ '  <div class="daccd-text">' + sConfirmText + '</div>'
		+ '</div>'
		+ '<div class="daccd-buttons">'
		+ '  <div class="daccd-yes"><input id="' + idYes + '" type="button" class="button-primary submit" value="' + sTextYes + '" id="ifdelYes" onclick="" /></div>'
		+ '  <div class="daccd-not"><input id="' + idNot + '" type="button" class="button-secondary button" value="' + sTextNo + '" onclick="if(typeof(Dialog_Close)==\'function\') { Dialog_Close(\'dialogAutoCreatedConfirmDiv\'); } else { var o = typeof(opener)!=\'undefined\' ? opener : parent; if(typeof(o)!=\'undefined\' && typeof(o.Dialog_Close)!=\'undefined\') o.Dialog_Close(\'dialogAutoCreatedConfirmDiv\'); }" /></div>'
		+ '</div>';
	$("body").append('<div id="dialogAutoCreatedConfirmDiv"></div>');
	$('#dialogAutoCreatedConfirmDiv').html(sContent);
	Dialog_ShowNode("dialogAutoCreatedConfirmDiv");

	if(typeof(StyleButtons)=='function') {
		StyleButtons(ocmsStyle, idYes);
		StyleButtons(ocmsStyle, idNot);
	}
	if(typeof(sFuncYes)!="undefined") eval("$('#'+idYes).bind('click', function(e) { " + sFuncYes + " });");
	if(typeof(sFuncNot)!="undefined") eval("$('#'+idNot).bind('click', function(e) { " + sFuncNot + " });");
}

function Dialog_CloseYesNo() {
	Dialog_Close("dialogAutoCreatedConfirmDiv");
	return false;
}

function Dialog_Alert(sAlertText, sTextOK, iWidth, aPosition, sFunctionOnClose) {
	var idOK = "dialogAutoCreatedAlertYes" + Math.floor((Math.random() * 99999) + 9999);
	var myWidth = 0;
	var myTextWidth = 0;
	var myDialogWidth = 0;
	var sContent = '';
	
	if(Dialog_daccdIconWidth==-1) {
		sContent = ''
			+ '<div class="daccd-info-holder">'
			+ '  <div class="daccd-icon" id="daccd-icon">&nbsp;</div>'
			+ '  <div class="daccd-text">test text</div>'
			+ '</div>';
		$("body").append('<div id="dialogAutoCreatedAlertDiv" style="position:absolute; left:-5000px; top:-5000px;"></div>');
		$('#dialogAutoCreatedAlertDiv').html(sContent);
		Dialog_daccdIconWidth = parseInt($('#daccd-icon').width());
		if(isNaN(Dialog_daccdIconWidth)) Dialog_daccdIconWidth = 10;
		$('#dialogAutoCreatedAlertDiv').remove();
		Dialog_daccdIconWidth += 10;
	}
	
	if(typeof(iWidth)!='undefined' && (x=parseInt(iWidth))!="NaN" && x>0) {
		myWidth = x;
		myTextWidth = myWidth - Dialog_daccdIconWidth;
		myDialogWidth = myWidth;
		myDialogWidth += 20;
	}
	
	sContent = ''
		+ '<div class="daccd-info-holder"' + (myWidth>0 ? ' style="width:'+myWidth+'px;"' : '') + '>'
		+ '  <div class="daccd-icon" id="daccd-icon">&nbsp;</div>'
		+ '  <div class="daccd-text"' + (myTextWidth>0 ? ' style="width:'+myTextWidth+'px;"' : '') + '>' + sAlertText + '</div>'
		+ '</div>'
		+ '<div class="daccd-buttons"' + (myWidth>0 ? ' style="width:'+myWidth+'px;"' : '') + '>'
		+ '  <div class="daccd-not"><input id="' + idOK + '" type="button" class="button" value="' + sTextOK + '" onclick="if(typeof(Dialog_Close)==\'function\') { Dialog_Close(\'dialogAutoCreatedAlertDiv\'); } else { var o = typeof(opener)!=\'undefined\' ? opener : parent; if(typeof(o)!=\'undefined\' && typeof(o.Dialog_Close)!=\'undefined\') o.Dialog_Close(\'dialogAutoCreatedAlertDiv\'); }" /></div>'
		+ '</div>';
	$("body").append('<div id="dialogAutoCreatedAlertDiv"></div>');
	$('#dialogAutoCreatedAlertDiv').html(sContent);
	if(myDialogWidth>0) Dialog_widths["dialogAutoCreatedAlertDiv"] = myDialogWidth + 'px';
	else Dialog_widths["dialogAutoCreatedAlertDiv"] = 0;
	Dialog_ShowNode("dialogAutoCreatedAlertDiv", "", sFunctionOnClose, false, aPosition);

	if(typeof(StyleButtons)=='function') {
		StyleButtons(ocmsStyle, idOK);
	}
}

function Dialog_CloseAlert() {
	Dialog_Close("dialogAutoCreatedAlertDiv");
	return false;
}

function Dialog_GetTitle(index) {
	return $("#ui-id-" + index).html();
}

function Dialog_ModifyTitle(index, newtext) {
	$("#ui-id-" + index).html( newtext );
}

function Dialog_ConstructErrMsg(sErrorTitle, sMessage) {
	return '<div class="error-head">' + sErrorTitle + '</div>' + sMessage;
}