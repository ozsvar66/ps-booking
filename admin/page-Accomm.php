<?php
	
	if(isset($_GET["tab"]) && $_GET["tab"]) $active_tab = $_GET["tab"];
	else {
		register_setting('psBooking_OptionGroup_Accomm', 'psBooking_settingsAccomm');
		$options = get_option('psBooking_settingsAccomm');
		if(isset($options["psBooking_psTab"]) && $options["psBooking_psTab"]) $active_tab = $options["psBooking_psTab"];
	}
			
//--------------------------------------------------------------------------------------------------
	function psBookingInit_settingsAccomm() {
		
		register_setting('psBooking_OptionGroup_Accomm', 'psBooking_settingsAccomm');
		
		add_settings_section(
			'psBookingSection_Accomm', // section ID
			__('Accommodation setup', 'ps-booking'), // section title
			'psBookingCallback_sectionAccomm', // function
			'psBookingPage_Accomm'
		);
		
		psBooking_addField("hotel", "Name [object]", "Accomm");
		psBooking_addField("maxchildage", "Max. age of child", "Accomm");
		psBooking_addField("logodimx", "LOGODIM", "Accomm");
		psBooking_addField("logodimy", PSBOOKING_SMALLFIELD, "Accomm");
		
		psBooking_addField("psTab", PSBOOKING_SMALLFIELD, "Accomm");
	}
//--------------------------------------------------------------------------------------------------
	function psBookingCallback_sectionAccomm() { /* do nothing */ }
//--------------------------------------------------------------------------------------------------
	function psBooking_hotel_render() {
		$options = get_option( 'psBooking_settingsAccomm' );
		$value = isset($options["psBooking_hotel"]) ? $options["psBooking_hotel"] : "";
		echo '<input name="psBooking_settingsAccomm[psBooking_hotel]" value="'.$value.'" type="text" style="width:520px; max-width:90%;" />';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_maxchildage_render() {
		$options = get_option( 'psBooking_settingsAccomm' );
		$value = isset($options["psBooking_maxchildage"]) ? (int)$options["psBooking_maxchildage"] : 18;
		echo '<select name="psBooking_settingsAccomm[psBooking_maxchildage]">';
		for($i=1; $i<=20; $i++) echo '<option value="'.$i.'"'.($value==$i ? ' selected' : '').'>'.$i.'</option>';
		echo '</select> '.__('years','ps-booking');
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_logodimx_render() {
		$options = get_option('psBooking_settingsAccomm');
		$width = isset($options["psBooking_logodimx"]) ? $options["psBooking_logodimx"] : 140;
		$height = isset($options["psBooking_logodimy"]) ? $options["psBooking_logodimy"] : 140;
		echo ''
			.__('WIDTH','ps-booking')
			.': <input name="psBooking_settingsAccomm[psBooking_logodimx]" value="'.$width.'" type="text" style="width:50px; text-align:right;" />'
			.'px'
			.'&nbsp; &nbsp; &nbsp; '
			.__('HEIGHT','ps-booking')
			.': <input name="psBooking_settingsAccomm[psBooking_logodimy]" value="'.$height.'" type="text" style="width:50px; text-align:right;" />'
			.'px';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_logodimy_render() { echo PSBOOKING_EMPTYLABEL; }
//--------------------------------------------------------------------------------------------------






?>