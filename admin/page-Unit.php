<?php

	if(isset($_GET["tab"]) && $_GET["tab"]) $active_tab = $_GET["tab"];
	else {
		register_setting('psBooking_OptionGroup_Unit', 'psBooking_settingsUnit');
		$options = get_option('psBooking_settingsUnit');
		if(isset($options["psBooking_psTab"]) && $options["psBooking_psTab"]) $active_tab = $options["psBooking_psTab"];
	}
	
	add_action('admin_enqueue_scripts', 'psBooking_imageUploader_installer');
	
//--------------------------------------------------------------------------------------------------
	function psBookingInit_settingsUnit() {
	global $psBooking_languages, $wpdb, $psBooking_currentUnit;
	
		register_setting('psBooking_OptionGroup_Unit', 'psBooking_settingsUnit');
		
		add_settings_section(
			'psBookingSection_Unit',
			__('Unit setup', 'ps-booking'),
			'psBookingCallback_sectionUnit',
			'psBookingPage_Unit'
		);
		
		psBooking_addField("unitid", "ID", "Unit");
		psBooking_addField("unitindex", "Ord. index", "Unit");
		psBooking_addFieldMultilang("unitname", __('Name [object]', 'ps-booking'), "Unit");
		psBooking_addField("unitadults", __('Unit capacity', 'ps-booking'), "Unit");
		//psBooking_addField("unitchildren", PSBOOKING_SMALLFIELD, "Unit");
		psBooking_addField("unitnightsmax", __('Enabled num. of nights to book', 'ps-booking'), "Unit");
		psBooking_addField("unitnightsmin", PSBOOKING_SMALLFIELD, "Unit");
		psBooking_addField("unitcalendar", __('Availability', 'ps-booking'), "Unit");
		psBooking_addFieldMultilang("unitintro", __('Short descript', 'ps-booking'), "Unit");
		psBooking_addFieldMultilang("unitprice", __('Price descript', 'ps-booking'), "Unit");
		psBooking_addFieldMultilang("unitlink", __('Web page (url)', 'ps-booking'), "Unit");
		psBooking_addField("unitimg", __('Picture', 'ps-booking').'  <span style="font-weight:normal !important;">('.PSBOOKING_UNITIMG_WIDTH.'x'.PSBOOKING_UNITIMG_HEIGHT.')</span>', "Unit");
		
		$options = get_option( 'psBooking_settingsUnit' );
		
		$tbl = psBooking_tblAccommUnits();
		
		$edited_unitid = isset($_GET["unitid"]) ? (int)$_GET["unitid"] : 0;
		
		if(isset($_REQUEST['settings-updated']) && $_REQUEST['settings-updated']=='true') {
			foreach($psBooking_languages as $i => $langcode) {
				$name[$i] = isset($options["psBooking_unitname_".$i]) ? $options["psBooking_unitname_".$i] : "";
				$intro[$i] = isset($options["psBooking_unitintro_".$i]) ? $options["psBooking_unitintro_".$i] : "";
				$price[$i] = isset($options["psBooking_unitprice_".$i]) ? $options["psBooking_unitprice_".$i] : "";
				$link[$i] = isset($options["psBooking_unitlink_".$i]) ? $options["psBooking_unitlink_".$i] : "";
			}
			$structured = serialize(array(
				"name"			=> $name,
				"adults"		=> isset($options["psBooking_unitadults"]) ? $options["psBooking_unitadults"] : 2,
				//"children"	=> isset($options["psBooking_unitchildren"]) ? $options["psBooking_unitchildren"] : 0,
				"nightsmin"	=> isset($options["psBooking_unitnightsmin"]) ? $options["psBooking_unitnightsmin"] : 1,
				"nightsmax"	=> isset($options["psBooking_unitnightsmax"]) ? $options["psBooking_unitnightsmax"] : 14,
				"calendar"	=> isset($options["psBooking_unitcalendar"]) ? $options["psBooking_unitcalendar"] : "",
				"intro"			=> $intro,
				"price"			=> $price,
				"link"			=> $link,
			));
			
			$psBooking_currentUnit = null;
			
			if(!$edited_unitid) {
				$dtnow = current_time('mysql');
				$wpdb->insert($tbl, array('ord_index'=>psBooking_getNextOrdIndexForAccommUnit(),'structuredinfo'=>$structured,'date_created'=>$dtnow));
				$units = $wpdb->get_results("SELECT * FROM $tbl WHERE date_created='$dtnow'");
				if(sizeof($units)) $unit = current($units);
				if($unit) $edited_unitid = $unit->id;
				if($edited_unitid) {
					wp_redirect('/wp-admin/options-general.php?page=ps-booking&tab=Unit&unitid='.$edited_unitid);
					exit;
				}
			}
			else {
				$units = $wpdb->get_results($sql="SELECT * FROM $tbl WHERE id=".$edited_unitid);
				if(sizeof($units)) {
					$wpdb->update($tbl, array('structuredinfo'=>$structured), array('id'=>$edited_unitid));
				}
				else {
					$wpdb->insert($tbl, array('id'=>$edited_unitid,'ord_index'=>psBooking_getNextOrdIndexForAccommUnit(),'structuredinfo'=>$structured,'date_created'=>current_time('mysql')));
				}
			}
		}
		
		$units = $wpdb->get_results("SELECT * FROM $tbl WHERE id=".$edited_unitid);
		if(sizeof($units)) $unit = current($units);
		if(isset($unit) && $unit) {
			$psBooking_currentUnit = unserialize($unit->structuredinfo);
			$psBooking_currentUnit["ord_index"] = $unit->ord_index;
		}
		else {
			$empty = array();
			foreach($psBooking_languages as $i => $langcode) $empty[$i] = "";
			$psBooking_currentUnit = array(
				"ord_index"	=> psBooking_getNextOrdIndexForAccommUnit(),
				"name"			=> $empty,
				"adults"		=> 2,
				"children"	=> 0,
				"nightsmin"	=> 1,
				"nightsmax"	=> 14,
				"calendar"	=> "",
				"intro"			=> $empty,
				"price"			=> $empty,
				"link"			=> $empty,
			);
		}
		
		psBooking_addField("psTab", PSBOOKING_SMALLFIELD, "Unit");
	}
//--------------------------------------------------------------------------------------------------
	function psBookingCallback_sectionUnit() { /* do nothing */ }
//--------------------------------------------------------------------------------------------------
	function psBooking_getNextOrdIndexForAccommUnit() {
	global $wpdb;
	
		$units = $wpdb->get_results("SELECT MAX(ord_index) AS c FROM ".psBooking_tblAccommUnits());
		if(sizeof($units)) $get = current($units);
		
		return isset($get->c) ? (int)$get->c + 1 : 1;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_unitid_render() {
	global $psBooking_currentUnit;
	
		$options = get_option( 'psBooking_settingsUnit' );
		
		if($psBooking_currentUnit!=null) $value = (int)$_GET['unitid'];
		else $value = isset($options["psBooking_unitid"]) ? $options["psBooking_unitid"] : "0";
		echo '<input readonly type="text" name="psBooking_settingsUnit[psBooking_unitid]" value="'.($value ? htmlspecialchars($value) : __('New unit', 'ps-booking')).'" style="width:100px; text-align:right; font-style:italic;" />';
		echo '<br />';
		
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_unitindex_render() {
	global $psBooking_currentUnit;
	
		$options = get_option( 'psBooking_settingsUnit' );
		
		if($psBooking_currentUnit!=null) $value = $psBooking_currentUnit['ord_index'];
		else $value = isset($options["psBooking_unitindex"]) ? $options["psBooking_unitindex"] : "0";
		echo '<input readonly type="text" name="psBooking_settingsUnit[psBooking_unitindex]" value="'.($value ? htmlspecialchars($value) : __('New unit', 'ps-booking')).'" style="width:100px; text-align:right; font-style:italic;" />';
		
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_unitname_1_render() { psBooking_unitname_render(1); } function psBooking_unitname_2_render() { psBooking_unitname_render(2); } function psBooking_unitname_3_render() { psBooking_unitname_render(3); } function psBooking_unitname_4_render() { psBooking_unitname_render(4); } function psBooking_unitname_5_render() { psBooking_unitname_render(5); } function psBooking_unitname_6_render() { psBooking_unitname_render(6); } function psBooking_unitname_7_render() { psBooking_unitname_render(7); } function psBooking_unitname_8_render() { psBooking_unitname_render(8); } function psBooking_unitname_9_render() { psBooking_unitname_render(9); } function psBooking_unitname_10_render() { psBooking_unitname_render(10); } function psBooking_unitname_11_render() { psBooking_unitname_render(11); } function psBooking_unitname_12_render() { psBooking_unitname_render(12); } function psBooking_unitname_13_render() { psBooking_unitname_render(13); } function psBooking_unitname_14_render() { psBooking_unitname_render(14); } function psBooking_unitname_15_render() { psBooking_unitname_render(15); } function psBooking_unitname_16_render() { psBooking_unitname_render(16); } function psBooking_unitname_17_render() { psBooking_unitname_render(17); } function psBooking_unitname_18_render() { psBooking_unitname_render(18); } function psBooking_unitname_19_render() { psBooking_unitname_render(19); } function psBooking_unitname_20_render() { psBooking_unitname_render(20); }
	function psBooking_unitname_render($i) { echo PSBOOKING_EMPTYLABEL; }
	function psBooking_unitname_0_render() {
	global $psBooking_languages, $psBooking_currentUnit;
	
		$options = get_option( 'psBooking_settingsUnit' );
		
		echo '<div class="psb-bordered">';
		foreach($psBooking_languages as $i => $langcode) {
			echo PSBOOKING_PRESPAN.__('IN-'.$langcode, 'ps-booking').':</span>';
			if($psBooking_currentUnit!=null) $value = $psBooking_currentUnit['name'][$i];
			else $value = isset($options["psBooking_unitname_".$i]) ? $options["psBooking_unitname_".$i] : ""; 
			echo '<input type="text" name="psBooking_settingsUnit[psBooking_unitname_'.$i.']" value="'.htmlspecialchars($value).'" class="psb-text" />';
		}
		echo '</div>';
		
	}
//--------------------------------------------------------------------------------------------------
	// function psBooking_unitchildren_render() { echo PSBOOKING_EMPTYLABEL; }
	function psBooking_unitadults_render() {
	global $psBooking_currentUnit;
	
		$options = get_option( 'psBooking_settingsUnit' );
		
		echo '<div class="psb-bordered">';
		
		echo PSBOOKING_PRESPAN.__('in all', 'ps-booking').':</span>';
		if($psBooking_currentUnit!=null) $value = $psBooking_currentUnit['adults'];
		else $value = isset($options["psBooking_unitadults"]) ? $options["psBooking_unitadults"] : 2;
		echo '<select name="psBooking_settingsUnit[psBooking_unitadults]">';
		for($j=1; $j<=30; $j++) echo '<option value="'.$j.'"'.($value==$j ? ' selected' : '').'>'.$j.'</option>';
		echo '</select> '.__('head(s)2','ps-booking');
		echo '<br />';
		
		/*
		echo PSBOOKING_PRESPAN.__('children', 'ps-booking').':</span>';
		if($psBooking_currentUnit!=null) $value = $psBooking_currentUnit['children'];
		else $value = isset($options["psBooking_unitchildren"]) ? $options["psBooking_unitchildren"] : 0;
		echo '<select name="psBooking_settingsUnit[psBooking_unitchildren]">';
		for($j=0; $j<=30; $j++) echo '<option value="'.$j.'"'.($value==$j ? ' selected' : '').'>'.$j.'</option>';
		echo '</select> '.__('head(s)','ps-booking');
		*/
		
		echo '</div>';
		
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_unitnightsmin_render() { echo PSBOOKING_EMPTYLABEL; }
	function psBooking_unitnightsmax_render() {
	global $psBooking_currentUnit;
	
		$options = get_option( 'psBooking_settingsUnit' );
		
		echo '<div class="psb-bordered">';
		
		echo PSBOOKING_PRESPAN.'Min:</span>';
		if($psBooking_currentUnit!=null) $value = $psBooking_currentUnit['nightsmin'];
		else $value = isset($options["psBooking_unitnightsmin"]) ? $options["psBooking_unitnightsmin"] : 2;
		echo '<select name="psBooking_settingsUnit[psBooking_unitnightsmin]">';
		for($j=1; $j<=30; $j++) echo '<option value="'.$j.'"'.($value==$j ? ' selected' : '').'>'.$j.'</option>';
		echo '</select> '.__('night(s)','ps-booking');
		echo '<br />';
		
		echo PSBOOKING_PRESPAN.'Max:</span>';
		if($psBooking_currentUnit!=null) $value = $psBooking_currentUnit['nightsmax'];
		else $value = isset($options["psBooking_unitnightsmax"]) ? $options["psBooking_unitnightsmax"] : 0;
		echo '<select name="psBooking_settingsUnit[psBooking_unitnightsmax]">';
		for($j=0; $j<=60; $j++) echo '<option value="'.$j.'"'.($value==$j ? ' selected' : '').'>'.$j.'</option>';
		echo '</select> '.__('night(s)','ps-booking');
		
		echo '</div>';
		
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_unitcalendar_render() {
	global $psBooking_currentUnit;
	
		$options = get_option( 'psBooking_settingsUnit' );
		
		echo PSBOOKING_PRESPAN.__('Availability', 'ps-booking').':</span>';
		$setup = array(
			'onSelect: function(dateText, inst) { inst.settings.defaultDate = dateText; }',
			'maxPicks: 5000'			
		);
		if($psBooking_currentUnit!=null) $value = $psBooking_currentUnit['calendar'];
		else $value = isset($options["psBooking_unitcalendar"]) ? $options["psBooking_unitcalendar"] : "";
		if($value) {
			$dates = array();
			$days = explode(",",$value);
			foreach($days as $day) $dates[] = trim($day);
			$setup[] = 'addDates:["'.implode('","',$dates).'"]';
			$value = ""; 
		}
		echo '<input id="pscalendar" type="text" name="psBooking_settingsUnit[psBooking_unitcalendar]" value="'.$value.'" class="ps-calendar" />';
		echo '<script> var psbCalendarSetup = "'.implode(",",$setup).'"; </script>';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_unitintro_1_render() { psBooking_unitintro_render(1); } function psBooking_unitintro_2_render() { psBooking_unitintro_render(2); } function psBooking_unitintro_3_render() { psBooking_unitintro_render(3); } function psBooking_unitintro_4_render() { psBooking_unitintro_render(4); } function psBooking_unitintro_5_render() { psBooking_unitintro_render(5); } function psBooking_unitintro_6_render() { psBooking_unitintro_render(6); } function psBooking_unitintro_7_render() { psBooking_unitintro_render(7); } function psBooking_unitintro_8_render() { psBooking_unitintro_render(8); } function psBooking_unitintro_9_render() { psBooking_unitintro_render(9); } function psBooking_unitintro_10_render() { psBooking_unitintro_render(10); } function psBooking_unitintro_11_render() { psBooking_unitintro_render(11); } function psBooking_unitintro_12_render() { psBooking_unitintro_render(12); } function psBooking_unitintro_13_render() { psBooking_unitintro_render(13); } function psBooking_unitintro_14_render() { psBooking_unitintro_render(14); } function psBooking_unitintro_15_render() { psBooking_unitintro_render(15); } function psBooking_unitintro_16_render() { psBooking_unitintro_render(16); } function psBooking_unitintro_17_render() { psBooking_unitintro_render(17); } function psBooking_unitintro_18_render() { psBooking_unitintro_render(18); } function psBooking_unitintro_19_render() { psBooking_unitintro_render(19); } function psBooking_unitintro_20_render() { psBooking_unitintro_render(20); }
	function psBooking_unitintro_render($i) { echo PSBOOKING_EMPTYLABEL; }
	function psBooking_unitintro_0_render() {
	global $psBooking_languages, $psBooking_currentUnit;
	
		$options = get_option( 'psBooking_settingsUnit' );
		
		echo '<div class="psb-bordered">';
		foreach($psBooking_languages as $i => $langcode) {
			echo PSBOOKING_PRESPAN.__('IN-'.$langcode, 'ps-booking').':</span>';
			if($psBooking_currentUnit!=null) $value = $psBooking_currentUnit['intro'][$i];
			else $value = isset($options["psBooking_unitintro_".$i]) ? $options["psBooking_unitintro_".$i] : ""; 
			echo '<textarea name="psBooking_settingsUnit[psBooking_unitintro_'.$i.']" class="psb-textarea">'.$value.'</textarea>';
		}
		echo '</div>';
		
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_unitprice_1_render() { psBooking_unitprice_render(1); } function psBooking_unitprice_2_render() { psBooking_unitprice_render(2); } function psBooking_unitprice_3_render() { psBooking_unitprice_render(3); } function psBooking_unitprice_4_render() { psBooking_unitprice_render(4); } function psBooking_unitprice_5_render() { psBooking_unitprice_render(5); } function psBooking_unitprice_6_render() { psBooking_unitprice_render(6); } function psBooking_unitprice_7_render() { psBooking_unitprice_render(7); } function psBooking_unitprice_8_render() { psBooking_unitprice_render(8); } function psBooking_unitprice_9_render() { psBooking_unitprice_render(9); } function psBooking_unitprice_10_render() { psBooking_unitprice_render(10); } function psBooking_unitprice_11_render() { psBooking_unitprice_render(11); } function psBooking_unitprice_12_render() { psBooking_unitprice_render(12); } function psBooking_unitprice_13_render() { psBooking_unitprice_render(13); } function psBooking_unitprice_14_render() { psBooking_unitprice_render(14); } function psBooking_unitprice_15_render() { psBooking_unitprice_render(15); } function psBooking_unitprice_16_render() { psBooking_unitprice_render(16); } function psBooking_unitprice_17_render() { psBooking_unitprice_render(17); } function psBooking_unitprice_18_render() { psBooking_unitprice_render(18); } function psBooking_unitprice_19_render() { psBooking_unitprice_render(19); } function psBooking_unitprice_20_render() { psBooking_unitprice_render(20); }
	function psBooking_unitprice_render($i) { echo PSBOOKING_EMPTYLABEL; }
	function psBooking_unitprice_0_render() {
	global $psBooking_languages, $psBooking_currentUnit;
	
		$options = get_option( 'psBooking_settingsUnit' );
		
		echo '<div class="psb-bordered">';
		foreach($psBooking_languages as $i => $langcode) {
			echo PSBOOKING_PRESPAN.__('IN-'.$langcode, 'ps-booking').':</span>';
			if($psBooking_currentUnit!=null) $value = $psBooking_currentUnit['price'][$i];
			else $value = isset($options["psBooking_unitprice_".$i]) ? $options["psBooking_unitprice_".$i] : ""; 
			echo '<textarea name="psBooking_settingsUnit[psBooking_unitprice_'.$i.']" style="width:calc(100% - 170px); height:50px;">'.$value.'</textarea>';
		}
		echo '</div>';
		
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_unitlink_1_render() { psBooking_unitlink_render(1); } function psBooking_unitlink_2_render() { psBooking_unitlink_render(2); } function psBooking_unitlink_3_render() { psBooking_unitlink_render(3); } function psBooking_unitlink_4_render() { psBooking_unitlink_render(4); } function psBooking_unitlink_5_render() { psBooking_unitlink_render(5); } function psBooking_unitlink_6_render() { psBooking_unitlink_render(6); } function psBooking_unitlink_7_render() { psBooking_unitlink_render(7); } function psBooking_unitlink_8_render() { psBooking_unitlink_render(8); } function psBooking_unitlink_9_render() { psBooking_unitlink_render(9); } function psBooking_unitlink_10_render() { psBooking_unitlink_render(10); } function psBooking_unitlink_11_render() { psBooking_unitlink_render(11); } function psBooking_unitlink_12_render() { psBooking_unitlink_render(12); } function psBooking_unitlink_13_render() { psBooking_unitlink_render(13); } function psBooking_unitlink_14_render() { psBooking_unitlink_render(14); } function psBooking_unitlink_15_render() { psBooking_unitlink_render(15); } function psBooking_unitlink_16_render() { psBooking_unitlink_render(16); } function psBooking_unitlink_17_render() { psBooking_unitlink_render(17); } function psBooking_unitlink_18_render() { psBooking_unitlink_render(18); } function psBooking_unitlink_19_render() { psBooking_unitlink_render(19); } function psBooking_unitlink_20_render() { psBooking_unitlink_render(20); }
	function psBooking_unitlink_render($i) { echo PSBOOKING_EMPTYLABEL; }
	function psBooking_unitlink_0_render() {
	global $psBooking_languages, $psBooking_currentUnit;
	
		$options = get_option( 'psBooking_settingsUnit' );
		
		echo '<div class="psb-bordered">';
		foreach($psBooking_languages as $i => $langcode) {
			echo PSBOOKING_PRESPAN.__('IN-'.$langcode, 'ps-booking').':</span>';
			if($psBooking_currentUnit!=null) $value = $psBooking_currentUnit['link'][$i];
			else $value = isset($options["psBooking_unitlink_".$i]) ? $options["psBooking_unitlink_".$i] : ""; 
			echo '<input type="text" name="psBooking_settingsUnit[psBooking_unitlink_'.$i.']" value="'.htmlspecialchars($value).'" class="psb-text" />';
		}
		echo '</div>';
		
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_unitimg_render() {
		
		$name = "unitimg";
    
    $options = get_option('psBooking_settingsUnit');
    $default_image = plugins_url('../imgs/noimage.png', __FILE__);

		if(!empty( $options[$name])) {
			$image_attributes = wp_get_attachment_image_src($options[$name], array(PSBOOKING_UNITIMG_WIDTH, PSBOOKING_UNITIMG_HEIGHT));
			$src = $image_attributes[0];
			$value = $options[$name];
		}
		else {
			$src = $default_image;
			$value = '';
		}
		
		echo '<script>
var psbTxtIfRemove = "'.__('Are you sure to remove it?','ps-booking').'";
var psbTxtYES = "'.__('YES','ps-booking').'";
var psbTxtNOT = "'.__('NOT','ps-booking').'";
</script>
<div class="psb-bordered psb-upload">
	<img data-src="'.$default_image.'" src="'.$src.'" width="'.PSBOOKING_UNITIMG_WIDTH.'px" height="'.PSBOOKING_UNITIMG_HEIGHT.'px" />
	<div>
		<input type="hidden" name="psBooking_settingsUnit['.$name.']" id="psBooking_settingsUnit['.$name.']" value="'.$value.'" />
		<button type="submit" class="psb-file-upload button">'.__('Upload', 'ps-booking').'</button>
		<button type="submit" class="psb-file-remove button">&times;</button>
	</div>
</div>';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_imageUploader_installer() { wp_enqueue_media(); }
//--------------------------------------------------------------------------------------------------
?>