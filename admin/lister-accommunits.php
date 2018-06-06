<?php

	define("psDATASEP", '##°°##');

	class psBookingAccomUnits extends WP_List_Table {
	
		//**********************************************************************************************
		function __construct() {
			global $status, $page;
			parent::__construct( array(
				'singular'  => 'request',
				'plural'    => 'requests',
				'ajax'      => false
			));
		}
		//**********************************************************************************************
		function column_default($item, $column_name) {
		
			if($column_name=="cb") return $item[$column_name];
			else {
				return '<a href="?page=ps-booking&tab=Unit&unitid='.$item['id'].'">'.$item[$column_name].'</a>';
			}
			
		}
		//**********************************************************************************************
		function column_cb($item){
			return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" />',
				/*$1%s*/ $this->_args['singular'],
				/*$2%s*/ $item['id']
			);
		}
		//**********************************************************************************************
		function get_columns(){
			$columns = array(
				"cb"						=> '<input type="checkbox" />', //Render a checkbox instead of text
				"id"						=> "ID",
				"name"					=> __("Name of unit", 'ps-booking'),
				"min_nights"		=> __("Min. nights", 'ps-booking'),
				"max_nights"		=> __("Max. nights", 'ps-booking'),
				"adults"				=> __("Unit capacity", 'ps-booking')." - ".__("adults", 'ps-booking'),
				"children"			=> __("Unit capacity", 'ps-booking')." - ".__("children", 'ps-booking'),
				"date_created"	=> __("DateCreated", 'ps-booking'),
			);
			return $columns;
		}
		//**********************************************************************************************
		function get_sortable_columns() {
			$sortable_columns = array(
				'id'						=> array('id',false),
				'name'					=> array('name',false),
				'date_created'	=> array('date_created',false),
			);
			return $sortable_columns;
		}
		//**********************************************************************************************
		function get_bulk_actions() {
			$actions = array(
				'delete'    => 'Delete'
			);
			return $actions;
    }
		//**********************************************************************************************
		function process_bulk_action() {
		global $wpdb;
		
			if('delete'===$this->current_action()) {
				if(isset($_POST["request"])) {
					$tbl = psBooking_tblAccommUnits();
					foreach($_POST["request"] as $id) $wpdb->delete($tbl, array('id'=>$id));
				}
			}
		}
		//**********************************************************************************************
		function prepare_items() {
		global $wpdb;
		
			$per_page = 5;
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array($columns, $hidden, $sortable);
			$this->process_bulk_action();
			
			$options = get_option('psBooking_settings');
			
			$data = array();
			$units = $wpdb->get_results("SELECT * FROM ".psBooking_tblAccommUnits()." ORDER BY ord_index ASC");
			
			$langindex = psBooking_getLangindex();
			
			foreach($units as $row) {
				$unit = isset($row->structuredinfo) ? unserialize($row->structuredinfo) : array();
				$data[] = array(
					'id'						=> $row->id,
					'name'					=> $unit["name"][$langindex],
					'min_nights'		=> $unit["nightsmin"],
					'max_nights'		=> $unit["nightsmax"],
					'adults'				=> $unit["adults"],
					'children'			=> $unit["children"],
					'date_created'	=> isset($row->date_created) ? "'".date("y/M/j H:i", strtotime($row->date_created)) : "",
				);
			}
			
			function usort_reorder($a,$b){
				$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'name'; //If no sort, default to name
				$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
				$result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
				return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
			}
			usort($data, 'usort_reorder');
		
		
			$current_page = $this->get_pagenum();
		
			$total_items = count($data);
		
			$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
			
			$this->items = $data;
		
			$this->set_pagination_args( array(
				'total_items' => $total_items,                  //WE have to calculate the total number of items
				'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
				'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
			));
		}
		//**********************************************************************************************
		
	}
	
?>