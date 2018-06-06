<?php

	if(file_exists($_sf=get_stylesheet_directory()."/ps-booking-public.php")) include $_sf;
	
//--------------------------------------------------------------------------------------------------
	function psBooking_form($atts, $content) {

		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$atts, \$content);"); if($_ret) return $_ret; }
	
		if( is_array( $atts ) ) extract($atts);
		$options = get_option( 'psBooking_settings' );
		
		$form = '';
		
		$options = psBooking_getOptions();
		
		$form .= psBooking_form_getHeader($options);
		
		$form .= psBooking_form_getInputs($options);
		
		$form .= psBooking_form_getFooter($options);
		
		
		if(isset($_POST["psb_roomtype"])) {
			$form .= '<h2>You chose the room <b>'.psBooking_getUnit((int)$_POST["psb_roomtype"])["name"].'</b></h2>'
				.'<p>A letter was sent to your address...</p>';
			
			$require = psBooking_insert();
			
			require_once(ABSPATH.'wp-content/plugins/ps-booking/public/send-mail.php');
			psBooking_sendMail($options, $require);
			
			$langindex = psBooking_getLangindex();
			
			if(isset($options["Form"]["thankyoupage_".$langindex]) && $options["Form"]["thankyoupage_".$langindex]) {
//echo "redirect: ".get_site_url().$options["Form"]["thankyoupage_".$langindex];
//echo "f exists: ".(function_exists("wp_redirect") ? "true" : "false")."<br>\n";
				psBooking_redirect(get_site_url().$options["Form"]["thankyoupage_".$langindex]);
				//exit;
			}
			
		}
	
		return $content.$form;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_getOptions() {
	
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."();"); if($_ret) return $_ret; }
	
		$options = array();
		
		foreach(array("Accomm","Form","Email") as $type) {
			$options_selected = get_option('psBooking_settings'.$type);
			$options[$type] = array();
			foreach($options_selected as $key1 => $value1) {
				$keymod1 = str_replace("psBooking_", "", $key1);
				if(is_array($value1)) {
					foreach($value1 as $key2 => $value2) {
						$keymod2 = str_replace("psBooking_", "", $key2);
						if(is_array($value2)) {
							foreach($value2 as $key3 => $value3) {
								$keymod3 = str_replace("psBooking_", "", $key3);
								$options[$type][$keymod1][$keymod2][$keymod3] = $value3;
							}
						}
						else $options[$type][$keymod1][$keymod2] = $value2;
					}
				}
				else $options[$type][$keymod1] = $value1;
			}
		}
		
		return $options;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_getInput($type, $name, $label, $is_obligatory=false, $input="", $units="") {
	
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$type, \$name, \$label, \$is_obligatory, \$input, \$units);"); if($_ret) return $_ret; }
		
		$html = '
		<div class="psb-form-row">
			<div class="psb-form-label'.($is_obligatory ? ' psb-obly' : '').'">'.__($label, 'ps-booking').'</div>
			<div class="psb-form-value">
		';
		if(isset($input) && $input) $html .= $input;
		else switch($type) {
			case 'text': case 'email':
				$html .= '<input type="text" name="'.$name.'" id="'.$name.'" />';
				break;
			case 'textarea':
				$html .= '<textarea name="'.$name.'" id="'.$name.'" /></textarea>';
				break;
			case "calendar":
				$html .= '<input type="text" name="'.$name.'" id="'.$name.'" class="psb-calendar" />';
				if($name=="psb_end") {
					$html .= PHP_EOL.psBooking_GetFormCss('public');
					$html .= PHP_EOL.psBooking_GetFormJScript('public');
					$months = array();
					for($i=1; $i<=12; $i++) $months[] = __('MONTH-'.$i, 'ps-booking');
					$months_short = array();
					for($i=1; $i<=12; $i++) $months_short[] = mb_substr(__('MONTH-'.$i, 'ps-booking'),0,3);
					$days = array();
					for($i=0; $i<=6; $i++) $days[] = __('DAY-'.$i, 'ps-booking');
					$html .= PHP_EOL.'<script>
var psBooking_textMinNights = "'.__('Min. nights', 'ps-booking').'";
var psBooking_textMaxNights = "'.__('Max. nights', 'ps-booking').'";
var psMonths = ["'.implode('","',$months).'"];
var psMonthsShort = ["'.implode('","',$months_short).'"];
var psDays = ["'.implode('","',$days).'"];
var psDateFormat = "'.__('DATE-FORMAT', 'ps-booking').'";
</script>'.PHP_EOL;
				}
				break;
		}
		return $html.'</div></div>';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getHeader($options) {
		
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$options);"); if($_ret) return $_ret; }
		
		return '<script> var psbOnEmptyInput = "'.__('Please fill in the fields below.','ps-booking').'"; </script>'
			.'<form method="post" class="psb-publicform" onsubmit="return psCheckForm(this)">';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getInputs($options) {
		
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$options);"); if($_ret) return $_ret; }
		
		$inputs = '';
		
		$inputs .= psBooking_form_getInputEmail($options);
		
		$inputs .= psBooking_form_getInputName($options);
		
		$inputs .= psBooking_form_getInputRoomtypes($options);
		
		$inputs .= psBooking_form_getComments($options);
		
		$inputs .= psBooking_form_getAgrees($options);
		
		return $inputs;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getFooter($options) {
		
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$options);"); if($_ret) return $_ret; }
		
		return '<input class="psb-submit" type="submit" value="'.$options["Form"]["submittext_".psBooking_getLangindex()].'" ></form>';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getInputEmail($options) {
		
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$options);"); if($_ret) return $_ret; }
		
		return psBooking_getInput("email", "psb_email", "Your Email address", true);
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getInputName($options) {
		
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$options);"); if($_ret) return $_ret; }
		
		return psBooking_getInput("text", "psb_name", "Your full name", true);
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getInputRoomtypes($options) {
	global $wpdb;
		
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$options);"); if($_ret) return $_ret; }
		
		$inputs = '';
		
		$langindex = psBooking_getLangindex();
		
		$units = $wpdb->get_results("SELECT * FROM ".psBooking_tblAccommUnits()." ORDER BY ord_index ASC");
		
		$radio = array();
		$inputs .= '<script> var psMaxPersons = []; var psRoomAvail = []; var psRoomNightsMin = []; var psRoomNightsMax = []; </script>';
		foreach($units as $row) {
			$unit = isset($row->structuredinfo) ? unserialize($row->structuredinfo) : array();
			$value = isset($unit["calendar"]) ? $unit["calendar"] : "";
			$i = $row->id;
			$min = isset($unit["nightsmin"]) ? $unit["nightsmin"] : 1;
			$max = isset($unit["nightsmax"]) ? $unit["nightsmax"] : 21;
			$option = '
				<input type="radio" name="psb_roomtype" value="'.$i.'" id="psb_roomtype_'.$i.'" onclick="psOnRoom('.$i.')" />
				<label for="psb_roomtype_'.$i.'">'.$unit["name"][$langindex].'</label>
				<script> psMaxPersons['.$i.'] = '.$unit["adults"].'; psRoomAvail['.$i.'] = "'.$value.'"; psRoomNightsMin['.$i.'] = '.$min.'; psRoomNightsMax['.$i.'] = '.$max.'; </script>
			';
			$radio[] = $option;
		}
		
		$inputs .= psBooking_getInput("", "", "Accommodation unit", true, implode('<br>',$radio));
		
		$inputs .= '<div id="psb-check-dates" style="display:none;">';
		$inputs .= psBooking_form_getRoomDetails_Begin($options);
		$inputs .= psBooking_form_getPersons($units, $options);
		$inputs .= psBooking_form_getInputDates($units);
		$inputs .= psBooking_form_getRoomDetails_End($units);
		$inputs .= '</div>';
		
		return $inputs;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getRoomDetails_Begin($options) {
	
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$options);"); if($_ret) return $_ret; }
		
		return '<div class="psb-roominputs">';
		
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getRoomDetails_End($units) {
	
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$units);"); if($_ret) return $_ret; }
		
		$langindex = psBooking_getLangindex();
		
		$inputs = '</div><div class="psb-roomdetails">';
		
		foreach($units as $row) {
			$unit = isset($row->structuredinfo) ? unserialize($row->structuredinfo) : array();
			$i = $row->id;
			$inputs .= '<div id="psb-roomdetail-'.$i.'" class="psb-roomdetail">';
			$inputs .= '<div class="psb-roomtitle">'.$unit["name"][$langindex].'</div>';
			$inputs .= '<div class="psb-roomdescript">'.$unit["intro"][$langindex].'</div>';
			$inputs .= '<div class="psb-pricedescript"><div class="psb-pricedescript-title">'.__('Price descript','ps-booking').'</div>'.$unit["price"][$langindex].'</div>';
			$inputs .= '</div>';
		}
		$inputs .= '</div>';
		
		
		return $inputs;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getPersons($units, $options) {
	
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$units, \$options);"); if($_ret) return $_ret; }
		
		return '
<div class="psb-capacity">
	<div class="psb-capacity-info">
		<span class="psb-ci-title">'.__('Unit capacity','ps-booking').'</span>
		<span class="psb-ci-value"></span>
		<span class="psb-ci-suffix">'.__('head(s)2','ps-booking').'</span>
	</div>
	<div class="psb-capacity-selects">
		<div class="psb-persons">
			'.psBooking_form_getInputAdults($units).'
			'.psBooking_form_getInputChildren($units, $options).'
		</div>
		'.(isset($options["Form"]["if_ages"]) && $options["Form"]["if_ages"] ? psBooking_form_getInputAges($options) : '').'
	</div>
</div>';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getInputDates($units) {
		
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."($units);"); if($_ret) return $_ret; }
		
		$inputs = '';
		
		$inputs .= psBooking_getInput("calendar", "psb_start", "Check-in", true);
		$inputs .= psBooking_getInput("calendar", "psb_end", "Check-out", true, "", $units);
		
		return $inputs;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getInputAdults($units) {
		
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$units);"); if($_ret) return $_ret; }
		
		$input = '<select name="psb_adults" id="psb_adults" class="psb-numof"></select>';
		$inputs .= psBooking_getInput("", "", "Num. of adults", false, $input);
		
		$inputs .= '<script> var psBooking_byRoomMax_adults = []; ';
		foreach($units as $row) {
			$i = $row->id;
			$unit = isset($row->structuredinfo) ? unserialize($row->structuredinfo) : array();
			$value = isset($unit["adults"]) ? $unit["adults"] : 2;
			$inputs .= 'psBooking_byRoomMax_adults['.$i.'] = '.$value.'; ';
		}
		$inputs .= '</script>';
		
		return $inputs;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getInputChildren($units, $options) {
		
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$units, \$options);"); if($_ret) return $_ret; }
		
		$inputs = '';
		
		$input = '<select name="psb_children" id="psb_children" class="psb-numof" onchange="psOnChild()"></select>';
		$inputs .= psBooking_getInput("", "", "Num. of children", false, $input);
		
		$inputs .= '<script> var psBooking_byRoomMax_children = []; ';
		foreach($units as $row) {
			$i = $row->id;
			$unit = isset($row->structuredinfo) ? unserialize($row->structuredinfo) : array();
			$value = isset($unit["children"]) ? $unit["children"] : 1;
			$inputs .= 'psBooking_byRoomMax_children['.$i.'] = '.$value.'; ';
		}
		$inputs .= '</script>';
		
		return $inputs;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getInputAges($options) {
		
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$options);"); if($_ret) return $_ret; }
		
		$inputs = '';
		
		$maxchildage = isset($options["Accomm"]["maxchildage"]) ? (int)$options["Accomm"]["maxchildage"] : 18;
		
		$inputs .= '<div id="psbAges" style="display:'.(isset($GLOBALS["psb_children"]) && $GLOBALS["psb_children"] ? "block" : "none").'">'.PHP_EOL;
		$inputs .= '  <span>'.__('Ages of children','ps-booking').'</span>'.PHP_EOL;
		$inputs .= '  <div class="psb-ages-holder">'.PHP_EOL;
		for($i=1; $i<=20; $i++) {
			$inputs .= '    <div id="psbAges-'.$i.'" class="psb-age">'.PHP_EOL;
			$inputs .= '      <select name="psb_ages['.$i.']" id="psb_ages_'.$i.'" class="psb-numof">'.PHP_EOL;
			for($j=1; $j<=$maxchildage; $j++) $inputs .= '        <option value="'.$j.'">'.$j.'</option>'.PHP_EOL;
			$inputs .= '      </select>'.PHP_EOL;
			$inputs .= '    </div>';
		}
		$inputs .= '  </div>';
		$inputs .= '</div>';
		
		return $inputs;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getComments($options) {
		
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."(\$options);"); if($_ret) return $_ret; }
		
		$inputs = '';
		
		if(isset($options["Form"]["if_comments"]) && $options["Form"]["if_comments"]) {
			$inputs .= psBooking_getInput("textarea", "psb_comments", "Other comments");
		}
		
		return $inputs;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_insert($data=array()) {
	global $wpdb;

		$tbl = psBooking_tblRequires();
		$dtnow = current_time('mysql');
		$wpdb->insert($tbl, array(
			'usr_name'				=> isset($data["name"]) ? $data["name"] : $_POST["psb_name"],
			'usr_email'				=> isset($data["email"]) ? $data["email"] : $_POST["psb_email"],
			'roomtype_id'			=> isset($data["roomtype"]) ? $data["roomtype"] : (int)$_POST["psb_roomtype"],
			'date_start'			=> isset($data["start"]) ? $data["start"] : psBooking_datepickerDateToISO($_POST["psb_start"]),
			'date_end'				=> isset($data["end"]) ? $data["end"] : psBooking_datepickerDateToISO($_POST["psb_end"]),
			'structuredinfo'	=> isset($data["structuredinfo"]) ? $data["structuredinfo"] : psBooking_getStructuredInfo(),
			'date_created'		=> $dtnow,
		));
		
		$requires = $wpdb->get_results("SELECT * FROM $tbl WHERE date_created='$dtnow'");
		if(sizeof($requires)) $require = current($requires);
		if($require) return $require;
		else return array();
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_getStructuredInfo() {
	
		if(function_exists($_sf=__FUNCTION__."_MODIFIED")) { eval("\$_ret=".$_sf."();"); if($_ret) return $_ret; }
	
		$data = array();
		
		$data["adults"] = isset($_POST["psb_adults"]) && (int)$_POST["psb_adults"] ? (int)$_POST["psb_adults"] : 1;
		$data["children"] = isset($_POST["psb_children"]) && (int)$_POST["psb_children"] ? (int)$_POST["psb_children"] : 0;
		for($i=1; $i<=$data["children"]; $i++) {
			$data["children_ages"][$i] = isset($_POST["psb_ages"][$i]) && $_POST["psb_ages"][$i] ? (int)$_POST["psb_ages"][$i] : 0;
		}
		$data["comments"] = isset($_POST["psb_comments"]) ? $_POST["psb_comments"] : "";
		
		return serialize($data);
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_form_getAgrees($options) {
	
		$langindex = psBooking_getLangindex();
		
		$inputs = '';
			
		for($j=1; $j<=PSBOOKING_MAX_AGREES; $j++) if(isset($options["Form"]["agree".$j]) && $options["Form"]["agree".$j]) {
			if(isset($options["Form"]["agree".$j."text".$langindex]) && $options["Form"]["agree".$j."text".$langindex]) {
				$input = '<input type="checkbox" name="psb_agree'.$j.'" value="1" id="psb_agree'.$j.'" />'
					.'<label for="psb_agree'.$j.'" class="psb-obly">'.$options["Form"]["agree".$j."text".$langindex].'</label>';
				$inputs .= psBooking_getInput("", "", "", false, $input);
			}
		} 
		
		return $inputs;
	}
//--------------------------------------------------------------------------------------------------
?>
