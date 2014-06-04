<?php
	
	# agileMantis - makes Mantis ready for Scrum

	# agileMantis is free software: you can redistribute it and/or modify
	# it under the terms of the GNU General Public License as published by
	# the Free Software Foundation, either version 2 of the License, or
	# (at your option) any later version.
	#
	# agileMantis is distributed in the hope that it will be useful,
	# but WITHOUT ANY WARRANTY; without even the implied warranty of
	# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	# GNU General Public License for more details.
	#
	# You should have received a copy of the GNU General Public License
	# along with agileMantis. If not, see <http://www.gnu.org/licenses/>.

	$uri = explode('/',$_SERVER['REQUEST_URI']);
	if(!empty($uri[1])){
		$subdir = '/'.$uri[1].'/';
	}

	$tasks->us_id = 2108;
	$_POST['language'] = 'german';
	
	$t_normal_date_format 		= 'Y-m-d H:i';
	$t_complete_date_format 	= 'Y-m-d H:i T';
	
	$t_email_separator1 		= str_pad('', 70, '=');
	$t_email_separator2 		= str_pad('', 70, '-');
	
	$userstory = $tasks->getUserStoryById();
	
	// Fetch all Recipients
	$t_recipients = array();
	
	// add Reporter
	$t_recipients[$userstory[0]['reporter_id']] = true;
	
	// add Handler
	$t_recipients[$userstory[0]['handler_id']] = true;

	// add Monitoring User
	$bug_monitor_user = $tasks->getBugmonitor();
	if(!empty($bug_monitor_user)){
		foreach($bug_monitor_user AS $num => $row){
			$t_recipients[$row['user_id']] = true;
		}
	}
	
	$t_project_id = $userstory[0]['project_id'];
	
	if(!empty($t_recipients)){
		foreach($t_recipients AS $num => $row){
			$t_recipients[$row['user_id']] = true;
		}
	}
	
	// Sammle Bugdaten
	$t_bug_data = array();
	
	$t_bug_data['email_bug']			 		= $userstory[0]['id'];
	$t_bug_data['email_handler'] 				= $tasks->getUserById($userstory[0]['handler_id']);
	$t_bug_data['email_reporter'] 				= $tasks->getUserById($userstory[0]['reporter_id']);
	$t_bug_data['email_project_id'] 			= $t_project_id;
	$t_bug_data['email_project'] 				= $project->getProjectById($t_project_id);
	$t_bug_data['email_category'] 				= $tasks->getCategoryById($userstory[0]['category_id']);
	$t_bug_data['email_date_submitted'] 		= date($t_complete_date_format, $userstory[0]['date_submitted']);
	$t_bug_data['email_last_modified'] 			= date($t_complete_date_format, $userstory[0]['last_updated']);
	$t_bug_data['email_status'] 				= $userstory[0]['status'];
	$t_bug_data['email_severity'] 				= $userstory[0]['severity'];
	$t_bug_data['email_priority'] 				= $userstory[0]['priority'];
	$t_bug_data['email_reproducibility'] 		= $userstory[0]['reproducibility'];
	$t_bug_data['email_resolution'] 			= $userstory[0]['resolution'];
	$t_bug_data['email_fixed_in_version'] 		= $userstory[0]['fixed_in_version'];
	$t_bug_data['email_target_version']			= $userstory[0]['target_version'];
	$t_bug_data['email_summary']				= $userstory[0]['summary'];
	$t_bug_data['email_description'] 			= $userstory[0]['description'];
	$t_bug_data['email_additional_information'] = $userstory[0]['additional_information'];
	$t_bug_data['email_steps_to_reproduce'] 	= $userstory[0]['steps_to_reproduce'];+
	$t_bug_data['email_bug_view_url']			= "http://".$_SERVER['HTTP_HOST'].$subdir.'view.php?id='.$userstory[0]['id'];
	$t_bug_data['set_category'] 				= '[' . $t_bug_data['email_project'] . '] ' . $t_bug_data['email_category'];
	$t_bug_data['custom_fields']				= $tasks->checkForUserStory($userstory[0]['id']);
	$t_bug_data['bugnotes']						= $tasks->getNotices($userstory[0]['id']);
	$t_bug_data['history'] 						= $tasks->getBugHistory();
	
	$t_subject = '[' . $t_bug_data['email_project'] . ' ' . utf8_str_pad( $t_bug_data['email_bug'], 7, 0, STR_PAD_LEFT ) . ']: ' . $t_bug_data['email_summary'];
	
	include( $_SERVER['DOCUMENT_ROOT'].$subdir.'/lang/strings_'.$_POST['language'].'.txt' );
	
	$severity_array = explode(',',$s_severity_enum_string);
	foreach($severity_array AS $num => $row){
		$temp = explode(':',$row);
		$severity[$temp[0]] = $temp[1];
	}
	
	$priority_array = explode(',',$s_priority_enum_string);
	foreach($priority_array AS $num => $row){
		$temp = explode(':',$row);
		$priority[$temp[0]] = $temp[1];
	}

	$status_array = explode(',',$s_status_enum_string);
	foreach($status_array AS $num => $row){
		$temp = explode(':',$row);
		$status[$temp[0]] = $temp[1];
	}
	
	$reproducibility_array = explode(',',$s_reproducibility_enum_string);
	foreach($reproducibility_array AS $num => $row){
		$temp = explode(':',$row);
		$reproducibility[$temp[0]] = $temp[1];
	}
	
	function formatAttribute($attribute_value,$attribute_name){
		return utf8_str_pad( $attribute_name . ': ', 28, ' ', STR_PAD_RIGHT ) . $attribute_value . "\n";
	}

	$t_message = "Eine Notiz wurde zu diesem Eintrag hinzugefügt.". " \n";
	$t_message .= $t_email_separator1 . " \n";
	$t_message .= $t_bug_data['email_bug_view_url'] . " \n";
	$t_message .= $t_email_separator1 . " \n";
	$t_message .= formatAttribute($t_bug_data['email_reporter'],$s_email_reporter);
	$t_message .= formatAttribute($t_bug_data['email_handler'],$s_email_handler);
	$t_message .= $t_email_separator1 . " \n";
	$t_message .= formatAttribute($t_bug_data['email_project'],$s_email_project); 
	$t_message .= formatAttribute($t_bug_data['email_bug'],$s_email_bug);
	$t_message .= formatAttribute($t_bug_data['email_category'],$s_email_category);
	$t_message .= formatAttribute($reproducibility[$t_bug_data['email_reproducibility']],$s_email_reproducibility);
	$t_message .= formatAttribute($severity[$t_bug_data['email_severity']],$s_email_severity);
	$t_message .= formatAttribute($priority[$t_bug_data['email_priority']],$s_email_priority);
	$t_message .= formatAttribute($status[$t_bug_data['email_status']],$s_email_status);
	$t_message .= $t_email_separator1 . " \n";
	$t_message .= formatAttribute($t_bug_data['email_date_submitted'], $s_email_date_submitted);
	$t_message .= formatAttribute($t_bug_data['email_last_modified'], $s_email_last_modified);
	$t_message .= $t_email_separator1 . " \n";
	$t_message .= formatAttribute($t_bug_data['email_summary'], $s_email_summary);
	$t_message .= $s_email_description . ": \n" . $t_bug_data['email_description'];
	
	if($t_bug_data['email_steps_to_reproduce'] != ""){
		$t_message .= formatAttribute($t_bug_data['email_steps_to_reproduce'], $s_email_steps_to_reproduce);
	}
	
	if($t_bug_data['email_additional_information'] != ""){
		$t_message .= formatAttribute($t_bug_data['email_additional_information'], $s_email_additional_information);
	}
	
	$t_message .= "\n";
	$t_message .= $t_email_separator1 . " \n\n";
	
	if(!empty($t_bug_data['bugnotes'])){
		foreach($t_bug_data['bugnotes'] AS $num => $row){
			$t_bugnote_link = $t_bug_data['email_bug_view_url'].'#c'.$row['id'];
			$t_string = ' (' . utf8_str_pad( $row['id'], 7, 0, STR_PAD_LEFT ) . ') ' . $tasks->getUserById( $row['reporter_id'] ) . ' - ' . date($t_normal_date_format, $row['last_modified']) . "\n " . $t_bugnote_link;

			$t_message .= $t_email_separator2 . " \n";
			$t_message .= $t_string . " \n";
			$t_message .= $t_email_separator2 . " \n";
			$t_message .= $row['note'] . " \n\n";
		}
	}

?>