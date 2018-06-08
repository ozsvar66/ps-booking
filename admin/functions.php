<?php

//--------------------------------------------------------------------------------------------------
	function psBooking_adminCreateSetupMenu( ) {
		add_options_page(
			'PS Booking',
			'PS Booking',
			'manage_options',
			'ps-booking',
			'psBooking_adminOptionsPage'
		);
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_adminCreatePluginMenu() {
		add_action('admin_head', 'psBooking_createPluginFont');
		add_menu_page(
			'PS Booking - Requests',
			__('Requests', 'ps-booking'),
			'manage_options', // access
			'ps-booking2', // kvcodes
			'psBooking_adminShowPluginPage',
			'dashicons-shield',
			4
		);
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_createPluginFont() {
  	echo '<style type="text/css" media="screen"> #adminmenu .toplevel_page_ps-booking2 div.wp-menu-image:before {
	content: "\f310"; 
} </style>';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_adminShowPluginPage() {
		
		echo psBooking_GetFormJScript('admin');
		
		echo psBooking_GetFormCss('admin');
		
		echo '<h1 class="wp-heading-inline">'.__('Requests','ps-booking').'</h1>';
		require_once(ABSPATH.'wp-content/plugins/ps-booking/admin/lister-requests.php');
		echo '<form method="post">';
		$lister = new psBookingLister();
		$options = get_option('psBooking_settingsAccomm');
		$lister->prepare_items();
		$lister->display();
		echo '</form>';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_addField($field, $label, $type) {
		add_settings_field(
			'psBooking_'.$field,
			strpos($label,"psempty")!==false ? '<label class="psempty '.$label.'">x</label>' : __($label, 'ps-booking'),
			'psBooking_'.$field.'_render',
			"psBookingPage_".$type,
			"psBookingSection_".$type
		);
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_addFieldMultilang($field, $label, $type) {
	global $psBooking_languages;
	
		foreach($psBooking_languages as $i => $langcode) {
			psBooking_addField($field.'_'.$i, $i==0 ? $label : PSBOOKING_SMALLFIELD, $type);
		}
		
	}
//--------------------------------------------------------------------------------------------------
	function psBookingInit_settings() {
	global $active_tab;
	
		$types = array("Accomm","Units","Unit","Form","Email","Shortcode","Requests");
		
		foreach($types as $type) {
			require_once(ABSPATH.'wp-content/plugins/ps-booking/admin/page-'.$type.'.php');
		}
		
		if(!$active_tab) $active_tab = "Accomm";
		if($_SERVER["QUERY_STRING"]=="page=ps-booking") $active_tab = "Accomm";
		if(!in_array($active_tab,$types)) $active_tab = "Accomm";
		
		eval("psBookingInit_settings".$active_tab."();");
	
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_adminOptionsPage() {
	global $active_tab;
	
		echo psBooking_GetFormJScript('admin');
		echo psBooking_GetFormCss('admin');
		
		echo '<div class="wrap">'.PHP_EOL;
		echo '<h2>'.__('psBooking Settings', 'ps-booking').'</h2>'.PHP_EOL;
    //echo '<div class="description">This is description of the page.</div>'.PHP_EOL;
    settings_errors();
    
    echo '<h2 class="nav-tab-wrapper">'.PHP_EOL;
    echo psBooking_adminAddTabSelect("Accomm", "Accommodation setup");
    echo psBooking_adminAddTabSelect("Units", "Accommodation units");
    if($active_tab=="Unit") echo psBooking_adminAddTabSelect("Unit", "Unit setup");
    echo psBooking_adminAddTabSelect("Form", "Form setup");
    echo psBooking_adminAddTabSelect("Email", "After-Email setup");
    echo psBooking_adminAddTabSelect("Shortcode", "Shortcode");
    echo psBooking_adminAddTabSelect("Requests", "Requests");
    echo '</h2>'.PHP_EOL;
    
    if($active_tab=="Units") psBooking_showUnits();
    else {
	    echo '<form method="post" action="options.php">'.PHP_EOL;
			echo '<input type="hidden" name="psBooking_settings'.$active_tab.'[psBooking_psTab]" value="'.$active_tab.'" />'.PHP_EOL;
	    switch($active_tab) {
	    	case "Accomm":
	    	case "Unit":
	    	case "Form":
	    	case "Email":
	    		settings_fields('psBooking_OptionGroup_'.$active_tab);
	    		do_settings_sections('psBookingPage_'.$active_tab);
	    		submit_button();
					break;
				case "Shortcode":
					echo '
				<h3>'.__('Shortcode', 'ps-booking').'</h3>
				<p>'.__('SHORTCODE-DESC', 'ps-booking').'</p>
				<div style="margin:8px; padding:12px 36px; border:1px solid silver; display:inline-block; background:#fff none;">
					[psBooking]
				</div>';
					break;
				case "Requests":
					echo '<h3>'.__('Requests', 'ps-booking').'</h3>'.__('REQUEST_SETUP', 'ps-booking');
					break;
	    }
	    
			echo '</form>'.PHP_EOL.'</div>'.PHP_EOL;
		}
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_adminAddTabSelect($tab_id, $tab_name) {
	global $active_tab;
	
		return '<a href="?page=ps-booking&tab='.$tab_id.'" class="nav-tab'.($active_tab==$tab_id ? ' nav-tab-active' : '').'">'.__($tab_name, 'ps-booking').'</a>'.PHP_EOL;
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_settings_section_callback() { /* do nothing */ }
//--------------------------------------------------------------------------------------------------
	function psBooking_psTab_render() { echo '<label class="psempty psempty-first">x</label>'; }
	function psBooking_dummy1_render() { echo '<label class="psempty psempty-first">x</label>'; }
	function psBooking_dummy2_render() { echo '<label class="psempty psempty-first">x</label>'; }
//--------------------------------------------------------------------------------------------------













?>