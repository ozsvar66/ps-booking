<?php

	$psBooking_pictures = array();
	$psBooking_phpmailerInitAction = null;
	
//--------------------------------------------------------------------------------------------------
	function psBooking_sendMail($options, $require) {
	
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$options, \$require);"); if($_ret) return $_ret; }
		
		$langindex = psBooking_getLangindex();
		
		/*
		$require sql record:
			usr_name
			usr_email
			roomtype_id
			date_start
			date_end
			structuredinfo
			date_created
		*/
		
		$subject = psBooking_encode(psBooking_replaceVariables($options["Email"]["emailsubject_".$langindex],$options,$require));
		$from = psBooking_encodeEmail($options["Email"]["emailaddrtxt_".$langindex].' <'.trim($options["Email"]["emailaddrurl"]).'>');
		
		$headers = 'From: '.$from."\r\n".'Reply-To: '.$from."\r\nContent-Type: text/html;\r\n\tcharset=UTF-8\r\n";
		
		$to = psBooking_encodeEmail($require->usr_name.' <'.trim($require->usr_email).'>');
		
		$message = psBooking_emailHtmlHeader();
		
		$message .= $options["Email"]["emailintro_".$langindex];
		
		$message .= psBooking_insertRequire($options, $require);
		
		$message .= $options["Email"]["emailfooter_".$langindex];
		
		$message .= psBooking_emailHtmlFooter();
		
		$message = psBooking_replaceVariables($message, $options, $require);
		
		// attach pictures from message (if they exist)
		global $psBooking_pictures, $psBooking_phpmailerInitAction;
		$psBooking_pictures = array();
		preg_match_all("/(<img[^>]+src=[\"\']+)([^\"\']+)([\"\']+[^>]*>)/", $message, $m);
		foreach($m[2] as $i => $source) {
			$a = explode("/", $source);
			$b = explode(".", $a[sizeof($a)-1]);
			$message = str_replace($m[0][$i], $m[1][$i].'cid:'.$b[0].$m[3][$i], $message);
			$psBooking_pictures[$source] = $b[0];
		}
		add_action('phpmailer_init', $psBooking_phpmailerInitAction=function(&$phpmailer)use($psBooking_pictures){
			foreach($psBooking_pictures as $source => $cid) {
				$phpmailer->SMTPKeepAlive = true;
	    	$phpmailer->AddEmbeddedImage(ABSPATH.$source, $cid);
	    }
		});
		
		wp_mail($to, $subject, $message, $headers);
		
		remove_action('phpmailer_init', $psBooking_phpmailerInitAction);
		
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_replaceVariables($str, $options, $require) {
	
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$str, \$options, \$require);"); if($_ret) return $_ret; }
	
		$str = str_replace("##VISITOREMAIL##", $require->usr_email, $str);
		$str = str_replace("##VISITORNAME##", $require->usr_name, $str);
		$str = str_replace("##ORDERNUMBER##", $require->id, $str);
		$str = str_replace("##ORDERNUMBER1000##", $require->id+1000, $str);
		$str = str_replace("##UNITNAME##", psBooking_getUnit((int)$require->roomtype_id)["name"], $str);
		$str = str_replace("##DATE##", psBooking_isoDateToDatepicker($require->date_created)." ".substr($require->date_created,11), $str);
		
		return $str;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_insertRequire($options, $require) {
	
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$options, \$require);"); if($_ret) return $_ret; }
		
		$html = '';
		
		$unit = psBooking_getUnit($require->roomtype_id);
		
		$info = unserialize($require->structuredinfo);
		
		$html .= '<table class="psb-email-table">'.PHP_EOL;
		$html .= psBooking_insertRequireRow('Your full name', $require->usr_name);
		$html .= psBooking_insertRequireRow('Your Email address', $require->usr_email);
		$html .= psBooking_insertRequireRow('Selected unit', $unit["name"]);
		$html .= psBooking_insertRequireRow('Check-in', psBooking_isoDateToDatepicker($require->date_start));
		$html .= psBooking_insertRequireRow('Check-out', psBooking_isoDateToDatepicker($require->date_end));
		$html .= psBooking_insertRequireRow('Num. of adults', $info["adults"]);
		
		$children = $info["children"];
		if($options["Form"]["if_ages"]) $children .= ' ('.__('Ages of children','ps-booking').': '.implode(', ',$info["children_ages"]).')';
		$html .= psBooking_insertRequireRow('Num. of children', $children);
		
		if($options["Form"]["if_comments"]) {
			$html .= psBooking_insertRequireRow('Other comments', $info["comments"]);
		}
		
		$html .= '</table>'.PHP_EOL;
		
		return $html;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_insertRequireRow($label, $value) {
	global $psb_email_row_index;
	
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$label, \$value);"); if($_ret) return $_ret; }
	
		if(!isset($psb_email_row_index)) $psb_email_row_index = 0;
		$psb_email_row_index++;
		$plus = ' style="background-color:'.($psb_email_row_index%2 ? '#f4f4f6;' : '#ebecf2').'"';
		return '<tr><th'.$plus.'>'.__($label,'ps-booking').'</th><td'.$plus.'>'.$value.'</td></tr>'.PHP_EOL;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_emailHtmlHeader() {
	
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."();"); if($_ret) return $_ret; }
		
		return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"> 
<html>
	<head>
		<META http-equiv="content-type" content="text/html; charset=UTF-8">
   	<style>'.file_get_contents(ABSPATH.'wp-content/plugins/ps-booking/public/inc/styles-email.css').'</style>
	</head>
<body>
<table class="psb-email-content" width="600" border="0" cellspacing="0" cellpadding="0"><tr><td>';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_emailHtmlFooter() {
	
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."();"); if($_ret) return $_ret; }
		
		return '</td></tr></table>
</body>
</html>';
	}
//--------------------------------------------------------------------------------------------------
?>