<?php
	
	if(isset($_GET["tab"]) && $_GET["tab"]) $active_tab = $_GET["tab"];
	else {
		register_setting('psBooking_OptionGroup_Form', 'psBooking_settingsForm');
		$options = get_option('psBooking_settingsForm');
		if(isset($options["psBooking_psTab"]) && $options["psBooking_psTab"]) $active_tab = $options["psBooking_psTab"];
	}
	
//--------------------------------------------------------------------------------------------------
	function psBookingInit_settingsForm() {
	global $psBooking_languages;
	
		register_setting('psBooking_OptionGroup_Form', 'psBooking_settingsForm');
				
		add_settings_section(
			'psBookingSection_Form', // section ID
			__('Form setup', 'ps-booking'), // section title
			'psBookingCallback_sectionForm',
			'psBookingPage_Form'
		);
		
		foreach($psBooking_languages as $i => $langcode) {
			if($i) eval('function psBooking_thankyoupage_'.$i.'_render() { echo PSBOOKING_EMPTYLABEL; }');
			if($i) eval('function psBooking_submittext_'.$i.'_render() { echo PSBOOKING_EMPTYLABEL; }');
		}
		psBooking_addFieldMultilang("thankyoupage", "After submit", "Form");
		psBooking_addField("dummy1", PSBOOKING_SEPARATOR, "Form");
		psBooking_addFieldMultilang("submittext", "Text on Submit button", "Form");
		psBooking_addField("dummy2", PSBOOKING_SEPARATOR, "Form");
		psBooking_addField("if_ages", "Optional fields", "Form");
		psBooking_addField("if_comments", PSBOOKING_SMALLFIELD, "Form");
		psBooking_addField("agree", __('Agree to checkboxes', 'ps-booking'), "Form");
		
		for($j=1; $j<=PSBOOKING_MAX_AGREES; $j++) {
			eval('function psBooking_agree'.$j.'_render() { echo PSBOOKING_EMPTYLABEL; }');
			psBooking_addField("agree".$j, PSBOOKING_SMALLFIELD, "Form");
			foreach($psBooking_languages as $i => $langcode) {
				eval('function psBooking_agree'.$j.'text'.$i.'_render() { echo PSBOOKING_EMPTYLABEL; }');
				psBooking_addField("agree".$j."text".$i, PSBOOKING_SMALLFIELD, "Form");
			}
		}
		
		psBooking_addField("psTab", PSBOOKING_SMALLFIELD, "Form");
	}
//--------------------------------------------------------------------------------------------------
	function psBookingCallback_sectionForm() { /* do nothing */ }
//--------------------------------------------------------------------------------------------------
	function psBooking_thankyoupage_0_render() {
	global $psBooking_languages;
	
		$options = get_option( 'psBooking_settingsForm' );
		
		echo PSBOOKING_PRESPAN.__('Link of the Thankyou page', 'ps-booking').':</span><br />';
		echo '<div class="psb-bordered">';
		echo '<div class="psb-notes psb-notes-top">'.__('If no Thankyou page','ps-booking').'</div>';
		foreach($psBooking_languages as $i => $langcode) {
			echo PSBOOKING_PRESPAN.__('IN-'.$langcode, 'ps-booking').':</span>';
			$value = isset($options["psBooking_thankyoupage_".$i]) ? $options["psBooking_thankyoupage_".$i] : ""; 
			echo '<input type="text" name="psBooking_settingsForm[psBooking_thankyoupage_'.$i.']" value="'.htmlspecialchars($value).'" class="psb-text" />';
		}
		echo '</div>';
		echo '<br />';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_submittext_0_render() {
	global $psBooking_languages;
	
		$options = get_option( 'psBooking_settingsForm' );
		
		echo '<div class="psb-bordered">';
		echo '<div class="psb-notes psb-notes-top">'.__('Submit text can be','ps-booking').'</div>';
		foreach($psBooking_languages as $i => $langcode) {
			echo PSBOOKING_PRESPAN.__('IN-'.$langcode, 'ps-booking').':</span>';
			$value = isset($options["psBooking_submittext_".$i]) ? $options["psBooking_submittext_".$i] : ""; 
			echo '<input type="text" name="psBooking_settingsForm[psBooking_submittext_'.$i.']" value="'.htmlspecialchars($value).'" class="psb-text" style="max-width:calc(100% - 200px); width:300px !important;" /><br>';
		}
		echo '</div>';
		echo '<br />';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_if_ages_render() {
	
		$options = get_option( 'psBooking_settingsForm' );
		
		echo '<div class="psb-bordered">';
		
		$if_ages = isset($options["psBooking_if_ages"]) ? (int)$options["psBooking_if_ages"] : 0;
		echo '<input id="ifages" type="checkbox" name="psBooking_settingsForm[psBooking_if_ages]" value="1" '.($if_ages==1 ? 'checked ' : '').' class="psCheckbox" />';
		echo '<label for="ifages">'.__('Ages of children', 'ps-booking').'</label>';
		echo '<br />';
		
		$if_comments = isset($options["psBooking_if_comments"]) ? (int)$options["psBooking_if_comments"] : 0;
		echo '<input id="ifcomm" type="checkbox" name="psBooking_settingsForm[psBooking_if_comments]" value="1" '.($if_comments==1 ? 'checked ' : '').' class="psCheckbox" />';
		echo '<label for="ifcomm">'.__('Other comments', 'ps-booking').'</label>';
		echo '<br />';
		
		echo '</div>';
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_agree_render() {
	global $psBooking_languages;
	
		$options = get_option( 'psBooking_settingsForm' );
		
		echo '<input type="hidden" name="psBooking_settingsForm[psBooking_agree]" value="" />';
		
		echo '<div class="psb-bordered">';
		for($j=1; $j<=PSBOOKING_MAX_AGREES; $j++) {
			$value = isset($options["psBooking_agree".$j]) ? $options["psBooking_agree".$j] : 0; 
			echo '<input id="agree'.$j.'" type="checkbox" name="psBooking_settingsForm[psBooking_agree'.$j.']" value="1" '.($value==1 ? 'checked ' : '').' class="psCheckbox" onclick="$(\'#agree'.$j.'texts\').css(\'display\',$(this).is(\':checked\')?\'block\':\'none\')" />';
			echo '<label for="agree'.$j.'">'.__('Agree'.$j, 'ps-booking').'</label>';
			echo '<div id="agree'.$j.'texts" style="display:'.($value==1 ? "block" : "none").'" class="psb-bordered psb-bordered2">';
			foreach($psBooking_languages as $i => $langcode) {
				echo PSBOOKING_PRESPAN.'&nbsp; '.__('IN-'.$langcode, 'ps-booking').':</span>';
				$value = isset($options["psBooking_agree".$j."text".$i]) ? $options["psBooking_agree".$j."text".$i] : ""; 
				echo '<textarea name="psBooking_settingsForm[psBooking_agree'.$j.'text'.$i.']" class="psb-textarea" />'.htmlspecialchars($value).'</textarea>';
			}
			echo '</div>';
			echo '<br />';
		}
		echo '</div>';
		
	}
//--------------------------------------------------------------------------------------------------
	function psBooking_if_comments_render($i) { echo PSBOOKING_EMPTYLABEL; }
	
//--------------------------------------------------------------------------------------------------

?>