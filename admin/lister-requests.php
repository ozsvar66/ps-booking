<?php

	define("psDATASEP", '##°°##');

	class psBookingLister extends WP_List_Table {
	
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
				if($column_name=="info") {
					$a = explode(psDATASEP, $item["info"]);
					$value = $a[0];
					$name = $a[1];
					$email = $a[2];
					$roomtype_id = $a[3];
					$date_start = $a[4];
					$date_end = $a[5];
					$structuredinfo = $a[6];
					
					$nights = round( (strtotime($date_end) - strtotime($date_start)) / 86400 );
					$date_start = psBooking_isoDateToDatepicker($date_start);
					$date_end = psBooking_isoDateToDatepicker($date_end);
					
					if($structuredinfo) {
						$info = unserialize($structuredinfo);
						$adults = isset($info["adults"]) && $info["adults"] ? $info["adults"] : 1;
						$children = isset($info["children"]) && $info["children"] ? $info["children"] : 0;
						$ages = array();
						for($i=1; $i<=$children; $i++) {
							$ages[$i] = isset($info["children_ages"][$i]) && $info["children_ages"][$i] ? $info["children_ages"][$i] : 0;
						}
						$children_ages = $ages ? implode(", ", $ages) : "";
						$comments = isset($info["comments"]) && $info["comments"] ? nl2br(str_replace("\\","",$info["comments"])) : "";
					}
					else {
						$adults = 1; // default (obligatory)
						$children = 0;
						$children_ages = "";
						$comments = "";
					}
					
					$item[$column_name] = 'i<div id="ps-info-'.$item["id"].'" class="ps-info">
						<div><label>ID</label><div>'.$item["id"].'</div></div>
						<div><label>'.__("Requester", 'ps-booking').'</label><div>'.$name.'</div></div>
						<div><label>E-mail</label><div>'.$email.'</div></div>
						<div><label>'.__("Check-in", 'ps-booking').'</label><div>'.$date_start.'</div></div>
						<div><label>'.__("Check-out", 'ps-booking').'</label><div>'.$date_end.'</div></div>
						<div><label>'.__("nights", 'ps-booking').'</label><div>'.$nights.'</div></div>
						<div><label>'.__("Roomtype", 'ps-booking').'</label><div>'.$item["roomtype"].'</div></div>
						<div><label>'.__("Num. of adults", 'ps-booking').'</label><div>'.$adults.'</div></div>
						<div><label>'.__("Num. of children", 'ps-booking').'</label><div>'.$children.'</div></div>
						<div><label>'.__("Ages of children", 'ps-booking').'</label><div>'.$children_ages.'</div></div>
						<div><label>'.__("Other comments", 'ps-booking').'</label><div>'.$comments.'</div></div>
					</div>';
				}
				return '<span onclick="Dialog_ShowNode(\'ps-info-'.$item["id"].'\', \''.__("Detailed information about the Request", 'ps-booking').'\')" style="cursor:pointer;" title="Overview">'.$item[$column_name].'</span>';
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
				"name"					=> __("Requester", 'ps-booking')." / ".__("adults", 'ps-booking'),
				"roomtype"			=> __("Roomtype", 'ps-booking'),
				"date_start"		=> __("Check-in", 'ps-booking')." / ".__("nights", 'ps-booking'),
				"date_created"	=> __("Reg. date", 'ps-booking'),
				"info"					=> "i"
			);
			return $columns;
		}
		//**********************************************************************************************
		function get_sortable_columns() {
			$sortable_columns = array(
				'id'						=> array('id',false),
				'name'					=> array('name',false),
				'roomtype'			=> array('roomtype',false),
				'date_start'		=> array('date_start',false),
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
					$tbl = psBooking_tblRequires();
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
			$requests = $wpdb->get_results("SELECT * FROM ".psBooking_tblRequires()." ORDER BY id DESC");
			foreach($requests as $request) {
				if($request->structuredinfo) {
					$info = unserialize($request->structuredinfo);
					$adults = isset($info["adults"]) && $info["adults"] ? $info["adults"] : 1;
				}
				else $adults = 1; // default (obligatory; min. num. of adults)
				$data[] = array(
					'id'						=> $request->id,
					'name'					=> $request->usr_name." / ".$adults,
					'roomtype'			=> psBooking_getUnit((int)$request->roomtype_id)["name"],
					'date_start'		=> $request->date_start." / ".round( (strtotime($request->date_end) - strtotime($request->date_start)) / 86400 ),
					'date_created'	=> "'".date("y/M/j H:i", strtotime($request->date_created)),
					"info"					=> 'i'
															.psDATASEP.$request->usr_name
															.psDATASEP.$request->usr_email
															.psDATASEP.$request->roomtype_id
															.psDATASEP.$request->date_start
															.psDATASEP.$request->date_end
															.psDATASEP.$request->structuredinfo
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