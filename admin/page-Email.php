<?php
	
	if(isset($_GET["tab"]) && $_GET["tab"]) $active_tab = $_GET["tab"];
	else {
		register_setting('psBooking_OptionGroup_Email', 'psBooking_settingsEmail');
		$options = get_option('psBooking_OptionGroup_Email');
		if(isset($options["psBooking_psTab"]) && $options["psBooking_psTab"]) $active_tab = $options["psBooking_psTab"];
	}
	
//--------------------------------------------------------------------------------------------------
	function psBookingInit_settingsEmail( ) {
	global $psBooking_languages;
	
		register_setting('psBooking_OptionGroup_Email', 'psBooking_settingsEmail');
		
		add_settings_section(
			'psBookingSection_Email', // section ID
			__('After-Email setup', 'ps-booking').'<span style="font-weight:normal !important;"> - '.__('Email after request', 'ps-booking').'</span>', // section title
			'psBookingCallback_sectionEmail',
			'psBookingPage_Email'
		);
		
		foreach($psBooking_languages as $i => $langcode) {
			if($i) eval('function psBooking_emailsubject_'.$i.'_render() { echo PSBOOKING_EMPTYLABEL; }');
			eval('function psBooking_emailaddrtxt_'.$i.'_render() { echo PSBOOKING_EMPTYLABEL; }');
			if($i) eval('function psBooking_emailintro_'.$i.'_render() { echo PSBOOKING_EMPTYLABEL; }');
			eval('function psBooking_emailfooter_'.$i.'_render() { echo PSBOOKING_EMPTYLABEL; }');
		}
		function psBooking_emailaddrurl_render($i) { echo PSBOOKING_EMPTYLABEL; }
		
		psBooking_addFieldMultilang("emailsubject", "Email header", "Email");
		psBooking_addField("emailaddrurl", PSBOOKING_SMALLFIELD, "Email");
		psBooking_addFieldMultilang("emailaddrtxt", PSBOOKING_SMALLFIELD, "Email");
		psBooking_addField("dummy1", PSBOOKING_SEPARATOR, "Email");
		psBooking_addFieldMultilang("emailintro", "Email body", "Email");
		psBooking_addFieldMultilang("emailfooter", PSBOOKING_SMALLFIELD, "Email");
		
		psBooking_addField("psTab", PSBOOKING_SMALLFIELD, "Email");
	}
//--------------------------------------------------------------------------------------------------
	function psBookingCallback_sectionEmail() {
		echo '<div class="psb-notes">'.__('Insertable Variables','ps-booking').'</div>';
		echo '<hr>';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_emailsubject_0_render() {
	global $psBooking_languages;
	
		$options = get_option( 'psBooking_settingsEmail' );
		
		echo PSBOOKING_PRESPAN.__('E-mail Subject', 'ps-booking').':</span><br />';
		echo '<div class="psb-bordered">';
		foreach($psBooking_languages as $i => $langcode) {
			echo PSBOOKING_PRESPAN.__('IN-'.$langcode, 'ps-booking').':</span>';
			$value = isset($options["psBooking_emailsubject_".$i]) ? $options["psBooking_emailsubject_".$i] : ""; 
			echo '<input type="text" name="psBooking_settingsEmail[psBooking_emailsubject_'.$i.']" value="'.htmlspecialchars($value).'" class="psb-text" />';
		}
		echo '</div>';
		echo '<br />';
		
		echo PSBOOKING_PRESPAN.__('Sender\'s E-mail', 'ps-booking').'</span><br>';
		echo '<div class="psb-bordered">';
		echo PSBOOKING_PRESPAN.__('Address part', 'ps-booking').':</span>';
		$value = isset($options["psBooking_emailaddrurl"]) ? $options["psBooking_emailaddrurl"] : ""; 
		echo '<input type="text" name="psBooking_settingsEmail[psBooking_emailaddrurl]" value="'.htmlspecialchars($value).'" class="psb-text" />';
		echo '<br />';
		echo PSBOOKING_PRESPAN.__('Name part', 'ps-booking').':</span><br>';
		echo '<div class="psb-bordered" style="margin:10px 10px 10px 130px !important;">';
		foreach($psBooking_languages as $i => $langcode) {
			echo PSBOOKING_PRESPAN.'&nbsp; '.__('IN-'.$langcode, 'ps-booking').':</span>';
			$value = isset($options["psBooking_emailaddrtxt_".$i]) ? $options["psBooking_emailaddrtxt_".$i] : ""; 
			echo '<input type="text" name="psBooking_settingsEmail[psBooking_emailaddrtxt_'.$i.']" value="'.htmlspecialchars($value).'" class="psb-text" />';
		}
		echo '</div>';
		echo '</div>';
		echo '<br />';
		
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_emailintro_0_render() {
	global $psBooking_languages;
	
		$options = get_option( 'psBooking_settingsEmail' );
		
		echo PSBOOKING_PRESPAN.__('Lead-In Text', 'ps-booking').':</span><br />';
		echo '<div class="psb-bordered">';
		$languages = get_available_languages();
		foreach($languages as $i => $langcode) {
			echo PSBOOKING_PRESPAN.__('IN-'.$langcode, 'ps-booking').':</span>';
			$value = isset($options["psBooking_emailintro_".$i]) ? $options["psBooking_emailintro_".$i] : ""; 
			echo '<textarea name="psBooking_settingsEmail[psBooking_emailintro_'.$i.']" class="psb-textarea">'.htmlspecialchars($value).'</textarea>';
		}
		echo '</div>';
		echo '<br />';
		
		echo '<div class="psb-bordered psb-notes" style="margin-top: 20px;">'.__('Email body descript','ps-booking').'</div>';
		
		echo PSBOOKING_PRESPAN.__('Footer', 'ps-booking').':</span><br />';
		echo '<div class="psb-bordered">';
		$languages = get_available_languages();
		foreach($languages as $i => $langcode) {
			echo PSBOOKING_PRESPAN.__('IN-'.$langcode, 'ps-booking').':</span>';
			$value = isset($options["psBooking_emailfooter_".$i]) ? $options["psBooking_emailfooter_".$i] : ""; 
			echo '<textarea name="psBooking_settingsEmail[psBooking_emailfooter_'.$i.']" class="psb-textarea">'.htmlspecialchars($value).'</textarea>';
		}
		echo '</div>';
		echo '<br />';
	}
//--------------------------------------------------------------------------------------------------
?>