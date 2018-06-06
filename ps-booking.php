<?php
/*
 * @link              http://www.ozsvar.com/_converts/ps-booking
 * @since             1.0
 * @package           PS_Booking

@ps-booking
Plugin Name: PS Booking
Plugin URI:  http://www.ozsvar.com/_converts/ps-booking/README.md
Description: Booking engine for the small hotels
Version:     1.0
Author:      István Ozsvár
Author URI:  http://ozsvar.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Copyright 2018 István Ozsvár (email : istvan@ozsvar.com)
(PS Booking) is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
(PS Booking) is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with (PS Booking). If not, see (http://link to your plugin license).
*/
	require_once __DIR__."/admin/functions.php";

	define("PSBOOKING_PRESPAN",    '<span class="psSpan">');
	define("PSBOOKING_SEPARATOR",  'psempty-first');
	define("PSBOOKING_SMALLFIELD", 'psempty-more');
	define("PSBOOKING_EMPTYLABEL", '<label class="psempty psempty-more">x</label>');
	define("PSBOOKING_MAX_AGREES", 5);
	
	$psBooking_languages = get_available_languages();
	
	// Initialize the plugin and add the menu
	add_action('admin_menu', 'psBooking_adminCreateSetupMenu');
	add_action('admin_menu', 'psBooking_adminCreatePluginMenu');
	
	$active_tab = ""; // set as global!
	add_action('admin_init', 'psBookingInit_settings');
	
	register_activation_hook( __FILE__, 'psBooking_install' );
	add_shortcode('psBooking', 'psBooking');
	
	add_filter('plugin_install_action_links', 'psBooking_pluginInformation', 10, 2 );

	// language pack
	
	add_action('plugins_loaded', 'psBooking_load_plugin_textdomain');

//--------------------------------------------------------------------------------------------------
	function psBooking_load_plugin_textdomain() {
		load_plugin_textdomain('ps-booking', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}
//--------------------------------------------------------------------------------------------------
	function psBooking($atts, $content = null) {
		require_once __DIR__."/public/functions.php";
		return psBooking_form($atts, $content);
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_encodeEmail($address) {
		if(!($address=trim($address))) return "";
		$a = explode(",", $address);
		$result = "";
		foreach($a as $mailblock) {
			if(!($mailblock=trim($mailblock))) continue;
			if(preg_match("/^([^<]*)<([^>]*)>$/", $mailblock, $match)) {
				$x = psBooking_encode(trim($match[1]),true)." <".strtolower($match[2]).">";
			}
			else $x = $mailblock;
			$result .= ($result?", ":"").$x; 
		}
		return $result;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_encode($str, $is_in_address=false) {
		
		if(!$str) return "";
		
		if(stripos($str,"=?iso-8859-2?Q?")!==false) return $str;
		else if(stripos($str,"=?UTF-8?Q?")!==false) return $str;
		
		if(function_exists("iconv_mime_encode")) {
			$preferences = array(
				"input-charset"			=> "UTF-8",
				"output-charset"		=> "UTF-8",
				"line-length"				=> $is_in_address? 999 : 76,
				"line-break-chars"	=> "\n"
			);
			$preferences["scheme"] = "Q";
			$a = preg_split("/^DATA\:/", $x=iconv_mime_encode("DATA", $str, $preferences));
			return trim($a[1]);
		}
		
		$i = 0;
		if(!($maxi=strlen($str))) return "";
		$maxi--;
		$newstr = "";
		while($i<=$maxi) {
			$ch1 = substr($str, $i, 1);
			$ch2 = substr($str, $i+1, 1);
			if(!isset($ch2) || !ord($ch2)) {
				$newstr .= $ch1;
				break;
			}
			else {
				$asc1 = ord($ch1);
				$asc2 = ord($ch2);
				if($asc1>=192) {
					$newstr .= "=".dechex($asc1)."=".dechex($asc2);
					$i += 2;
				}
				else {
					$newstr .= $ch1;
					$i++;
				}
			}
		}
		return "=?utf-8?Q?".$newstr."?=";
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_GetFormJScript($place) {
		// ***************************************
		// Some kinds of plugins (or wp theme) can load some of the scripts listed here so I read them so very carefully.
		//  *  https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js
		//  *  https://code.jquery.com/ui/1.12.1/jquery-ui.min.js
		//  *  https://cdn.rawgit.com/dubrox/Multiple-Dates-Picker-for-jQuery-UI/master/jquery-ui.multidatespicker.js
		//  *  if($page=='admin') /wp-content/plugins/ps-booking/admin/inc/dialog.js
		//  *  /wp-content/plugins/ps-booking/'.$place.'/inc/scripts.js
		// ***************************************
		return '
<script>
	document.addEventListener("DOMContentLoaded", function(event) {
    if(typeof($)=="undefined") {
			var tag = document.createElement("script");
			tag.src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js";
			document.getElementsByTagName("head")[0].appendChild(tag);
		}
		setTimeout("psbAfterLoadJQuery()",100);
  });
  function psbAfterLoadJQuery() {
  	if(typeof($)=="undefined") {
  		setTimeout("psbAfterLoadJQuery()",100);
  		return;
  	}
  	psbCheckJQueryUI();
  }
  function psbCheckJQueryUI() {
  	$("<div id=\'psb-test\' style=\'display:none\'></div>").appendTo($("body"));
		if(typeof($("#psb-test").datepicker)=="undefined") {
			$.getScript("https://code.jquery.com/ui/1.12.1/jquery-ui.min.js");
		}
		setTimeout("psbAfterLoadJQueryUI()",100);
  }
  function psbAfterLoadJQueryUI() {
  	if(typeof($("#psb-test").datepicker)=="undefined") {
  		setTimeout("psbAfterLoadJQueryUI()",100);
  		return;
  	}
  	$("#psb-test").remove();
  	$.getScript("https://cdn.rawgit.com/dubrox/Multiple-Dates-Picker-for-jQuery-UI/master/jquery-ui.multidatespicker.js");
  	'.($place=='admin' ? '$.getScript("/wp-content/plugins/ps-booking/admin/inc/dialog.js");'.PHP_EOL : '').'
  	$.getScript("/wp-content/plugins/ps-booking/'.$place.'/inc/scripts.js");
  }
</script>';
		/*
		return '
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdn.rawgit.com/dubrox/Multiple-Dates-Picker-for-jQuery-UI/master/jquery-ui.multidatespicker.js"></script>
'.($place=='admin' ? '<script src="/wp-content/plugins/ps-booking/admin/inc/dialog.js"></script>'.PHP_EOL : '')
.'<script src="/wp-content/plugins/ps-booking/'.$place.'/inc/scripts.js"></script>
';
		*/
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_GetFormCss($place) {
		return '
<link href="https://cdn.rawgit.com/dubrox/Multiple-Dates-Picker-for-jQuery-UI/master/jquery-ui.multidatespicker.css" rel="stylesheet"/>
<link href="https://code.jquery.com/ui/1.12.1/themes/pepper-grinder/jquery-ui.css" rel="stylesheet"/>
<link href="/wp-content/plugins/ps-booking/'.$place.'/inc/styles.css" rel="stylesheet"/>
';
	}
//--------------------------------------------------------------------------------------------------



// Database


//--------------------------------------------------------------------------------------------------
	function psBooking_tblRequires() {
	global $wpdb;
	
		return $wpdb->prefix . "spBooking_requires";
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_tblAccommUnits() {
	global $wpdb;
	
		return $wpdb->prefix . "spBooking_accomm_units";
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_install() {
	global $wpdb;
	
		/*
		$wpdb->query("DROP TABLE IF EXISTS ".psBooking_tblRequires());
		$wpdb->query("DROP TABLE IF EXISTS ".psBooking_tblAccommUnits());
		delete_option("PS_Booking_db_version");
		*/

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		
		$tbl = psBooking_tblRequires();
		$wpdb->query("DROP TABLE IF EXISTS $tbl");
		$sql = "CREATE TABLE IF NOT EXISTS $tbl (
			id             INT NOT NULL AUTO_INCREMENT,
			usr_name       VARCHAR(100) NOT NULL DEFAULT '',
			usr_email      VARCHAR(100) NOT NULL DEFAULT '',
			roomtype_id    INT NOT NULL DEFAULT 0,
			date_start     DATE NOT NULL DEFAULT '0000-00-00',
			date_end       DATE NOT NULL DEFAULT '0000-00-00',
			structuredinfo TEXT NULL,
			date_created   DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			INDEX ix1(usr_name(40),usr_email(40),roomtype_id,date_start,date_end,date_created)
		) ".$wpdb->get_charset_collate().";";
		dbDelta($sql);
		
		
		$tbl = psBooking_tblAccommUnits();
		$wpdb->query("DROP TABLE IF EXISTS $tbl");
		$sql = "CREATE TABLE IF NOT EXISTS $tbl (
			id             INT NOT NULL AUTO_INCREMENT,
			ord_index      TINYINT NOT NULL DEFAULT 1,
			structuredinfo TEXT NULL,
			date_created   DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			INDEX ix1(date_created),
			INDEX ix2(ord_index)
		) ".$wpdb->get_charset_collate().";";
		dbDelta($sql);
		
		//$wpdb->query($sql);
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_getConvertedDatepickerFormat() {
	
		$format = __('DATE-FORMAT', 'ps-booking');
		
		// days
		$format = str_replace("dd", "d", $format); // Numeric representation of a day of month: two digit
		$format = str_replace("d", "j", $format); // Numeric representation of a day of month: no leading zero
		$format = str_replace("o", "z", $format); // Numeric representation of a day of the year: no leading zeros
		/*
		$format = str_replace("DD", "l", $format); // A textual representation of a day: long
		$format = str_replace("D", "D", $format); // A textual representation of a day: short
		*/
		$format = str_replace("DD", "", $format); // A textual representation of a day: long
		$format = str_replace("D", "", $format); // A textual representation of a day: short
		
		// months
		$format = str_replace("mm", "m", $format); // Numeric representation of a month: two digit
		$format = str_replace("m", "n", $format); // Numeric representation of a month: no leading zero
		$format = str_replace("MM", "F", $format); // month name long
		$format = str_replace("M", "M", $format); // month name short
		
		// years
		$format = str_replace("yy", "Y", $format); // year: four digit
		$format = str_replace("y", "y", $format); // year: two digit
		
		// timestamp
		$format = str_replace("@", "U", $format); // Unix timestamp
		
		
		$format = trim(preg_replace("/\([^\)]*\)/", "", $format));
		$format = trim(preg_replace("/\[[^\]]*\]/", "", $format));
		
		return $format;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_datepickerDateToISO($date_string) {
		
		$format = psBooking_getConvertedDatepickerFormat();
		
		for($m=1; $m<=12; $m++) {
			switch($m) {
				case 1: $e = "January"; break;
				case 2: $e = "February"; break;
				case 3: $e = "March"; break;
				case 4: $e = "April"; break;
				case 5: $e = "May"; break;
				case 6: $e = "June"; break;
				case 7: $e = "July"; break;
				case 8: $e = "August"; break;
				case 9: $e = "September"; break;
				case 10: $e = "October"; break;
				case 11: $e = "November"; break;
				case 12: $e = "December"; break;
			}
			$date_string = str_replace(__('MONTH-'.$m,'ps-booking'), $e, $date_string);
			$date_string = str_replace(mb_substr(__('MONTH-'.$m,'ps-booking'),0,3), mb_substr($e,0,3), $date_string);
		}
		
		$date_string = trim(preg_replace("/\([^\)]*\)/", "", $date_string));
		$date_string = trim(preg_replace("/\[[^\]]*\]/", "", $date_string));
		
		$DateTime = date_create_from_format($format, $date_string);
		
		return $DateTime->format("Y-m-d");
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_isoDateToDatepicker($iso_date) {
		
		$timestamp = strtotime($iso_date);
		
		$date_string = __('DATE-FORMAT', 'ps-booking');
		
		// days
		$date_string = str_replace("dd", date("d", $timestamp), $date_string); // Numeric representation of a day of month: two digit
		$date_string = str_replace("d", date("j", $timestamp), $date_string); // Numeric representation of a day of month: no leading zero
		$date_string = str_replace("o", date("z", $timestamp), $date_string); // Numeric representation of a day of the year: no leading zeros
		
		// months
		$date_string = str_replace("mm", date("m", $timestamp), $date_string); // Numeric representation of a month: two digit
		$date_string = str_replace("m", date("n", $timestamp), $date_string); // Numeric representation of a month: no leading zero
		$date_string = str_replace("MM", date("F", $timestamp), $date_string); // month name long
		$date_string = str_replace("M", date("M", $timestamp), $date_string); // month name short
		
		// years
		$date_string = str_replace("yy", date("Y", $timestamp), $date_string); // year: four digit
		$date_string = str_replace("y", date("y", $timestamp), $date_string); // year: two digit
		
		// textual days
		$date_string = str_replace("DD", date("l", $timestamp), $date_string); // A textual representation of a day: long
		$date_string = str_replace("D", date("D", $timestamp), $date_string); // A textual representation of a day: short
		
		// timestamp
		$date_string = str_replace("@", date("U", $timestamp), $date_string); // Unix timestamp
		
		for($m=1; $m<=12; $m++) {
			switch($m) {
				case 1: $e = "January"; break;
				case 2: $e = "February"; break;
				case 3: $e = "March"; break;
				case 4: $e = "April"; break;
				case 5: $e = "May"; break;
				case 6: $e = "June"; break;
				case 7: $e = "July"; break;
				case 8: $e = "August"; break;
				case 9: $e = "September"; break;
				case 10: $e = "October"; break;
				case 11: $e = "November"; break;
				case 12: $e = "December"; break;
			}
			$date_string = str_replace($e, __('MONTH-'.$m,'ps-booking'), $date_string);
			$date_string = str_replace(mb_substr($e,0,3), mb_substr(__('MONTH-'.$m,'ps-booking'),0,3), $date_string);
		}
		
		for($d=0; $d<=6; $d++) {
			switch($d) {
				case 0: $e = "Sunday"; break;
				case 1: $e = "Monday"; break;
				case 2: $e = "Tuesday"; break;
				case 3: $e = "Wednesday"; break;
				case 4: $e = "Thursday"; break;
				case 5: $e = "Friday"; break;
				case 6: $e = "Saturday"; break;
			}
			$date_string = str_replace($e, __('DAY-'.$d,'ps-booking'), $date_string);
			$date_string = str_replace(mb_substr($e,0,3), mb_substr(__('DAY-'.$d,'ps-booking'),0,3), $date_string);
		}
		
		return $date_string;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_getLangindex() {
	global $psBooking_languages;
	
		$actlangcode = ICL_LANGUAGE_CODE."_".strtoupper(ICL_LANGUAGE_CODE);
		$langindex = 0;
		foreach($psBooking_languages as $i => $langcode) {
			if($langcode==$actlangcode) {
				$langindex = $i;
				break;
			}
		}
		
		return $langindex;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_redirect($url) {
		echo '<script>
self.location.href = "'.$url.'";
</script>';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_getUnit($id) {
	global $wpdb;
	
		$langindex = psBooking_getLangindex();
	
		$units = $wpdb->get_results("SELECT * FROM ".psBooking_tblAccommUnits()." ORDER BY ord_index ASC");
		$unit = array();
		foreach($units as $row) if($row->id==$id) {
			$unit = isset($row->structuredinfo) ? unserialize($row->structuredinfo) : array();
			$unit["name"] = $unit["name"][$langindex];
			$unit["intro"] = $unit["intro"][$langindex];
			$unit["price"] = $unit["price"][$langindex];
		}
		
		return $unit;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_pluginInformation($links, $plugin) {
		if(isset($_GET['tab'])) {
			switch($_GET['tab']) {
				case 'featured':                                         
					$links['my-action'] = "Tested up to <a href='#'>{$plugin['tested']}</a>";
					break;                                                   
				case 'popular':                                          
					$links['my-action'] = "Requires <a href='#'>{$plugin['requires']}</a>";
					break;                                                   
				case 'new':                                              
					$links['my-action'] = "Slug <a href='#'>{$plugin['slug']}</a>";
					break;                                                   
			}
		}
		
		return $links;
	}
//--------------------------------------------------------------------------------------------------
?>