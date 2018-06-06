<?php
	
	if(isset($_GET["tab"]) && $_GET["tab"]) $active_tab = $_GET["tab"];
	else {
		register_setting('psBooking_OptionGroup_Units', 'psBooking_settingsUnits');
		$options = get_option('psBooking_settingsUnits');
		if(isset($options["psBooking_psTab"]) && $options["psBooking_psTab"]) $active_tab = $options["psBooking_psTab"];
	}
	
//--------------------------------------------------------------------------------------------------
	function psBookingInit_settingsUnits() {
	
		register_setting('psBooking_OptionGroup_Units', 'psBooking_settingsUnits');
		
		add_settings_section(
			'psBookingSection_Units',
			__('Units setup', 'ps-booking'),
			'psBookingCallback_sectionUnits',
			'psBookingPage_Units'
		);
		
		psBooking_addField("psTab", PSBOOKING_SMALLFIELD, "Units");
	}
//--------------------------------------------------------------------------------------------------
	function psBookingCallback_sectionUnits() { /* do nothing */ }
//--------------------------------------------------------------------------------------------------
	function psBooking_showUnits() {
	
		require_once(ABSPATH.'wp-content/plugins/ps-booking/admin/lister-accommunits.php');
		
		$linknew = '<div style="text-align:right;"><a href="?page=ps-booking&tab=Unit&unitid=0">'.__('Create new unit', 'ps-booking').' &raquo;</a></div>';
		
		echo '<h2>'.__('Accommodation units', 'ps-booking').'</h2>';
		echo '<p style="font-style:italic;">'.__('Accommodation units descript', 'ps-booking').'</p>';
		echo '<form method="post">';
		$lister = new psBookingAccomUnits();
		$lister->prepare_items();
		echo $linknew;
		$lister->display();
		echo $linknew;
		echo '</form>';
		
	}
//--------------------------------------------------------------------------------------------------

?>