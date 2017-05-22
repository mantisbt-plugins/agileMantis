<?php
# This file is part of agileMantis.
#
# Developed by: 
# gadiv GmbH
# Bövingen 148
# 53804 Much
# Germany
#
# Email: agilemantis@gadiv.de
#
# Copyright (C) 2012-2014 gadiv GmbH 
#
# agileMantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU Lesser General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public License
# along with agileMantis. If not, see <http://www.gnu.org/licenses/>.


# delete custom field
if( $_POST['deleteField'] != "" ) {
	$removeField = $_POST['deleteField'];
	$agilemantis_userstory->removeCustomField( $removeField );
}

# mantis form securiy token
form_security_validate( 'plugin_format_config_edit' );

# format different double variables
$_POST['gadiv_sprint_length'] = str_replace( ',', '.', $_POST['gadiv_sprint_length'] );
$_POST['gadiv_workday_in_hours'] = str_replace( '.', ',', $_POST['gadiv_workday_in_hours'] );

if( $_POST['gadiv_sprint_length'] == floor( $_POST['gadiv_sprint_length'] ) &&
	 is_numeric( $_POST['gadiv_sprint_length'] ) && $_POST['gadiv_sprint_length'] > 0 ) {
	
	# make work day length checks
	$f_gadiv_workday_in_hours = gpc_get_string( 'gadiv_workday_in_hours', 8 );
	if( $_POST['gadiv_workday_in_hours'] >= 1 && $_POST['gadiv_workday_in_hours'] <= 24 ) {
		if( plugin_config_get( 'gadiv_workday_in_hours' ) != $f_gadiv_workday_in_hours ) {
			plugin_config_set( 'gadiv_workday_in_hours', $f_gadiv_workday_in_hours );
		}
	} else {
		$throw_error_4 = true;
	}
	
	if( !$throw_error_4 ) {
		
		# make sprint length checks
		$f_gadiv_sprint_length = $_POST['gadiv_sprint_length'];
		if( $_POST['gadiv_sprint_length'] > 0 ) {
			if( plugin_config_get( 'gadiv_sprint_length' ) != $f_gadiv_sprint_length ) {
				plugin_config_set( 'gadiv_sprint_length', $f_gadiv_sprint_length );
			}
			
			# make storypoint mode checks
			$f_gadiv_storypoint_mode = gpc_get_int( 'gadiv_storypoint_mode', 0 );
			if( plugin_config_get( 'gadiv_storypoint_mode' ) != $f_gadiv_storypoint_mode ) {
				plugin_config_set( 'gadiv_storypoint_mode', $f_gadiv_storypoint_mode );
			}
			
			if( plugin_config_get( 'gadiv_storypoint_mode' ) == 0 ) {
				$f_gadiv_fibonacci_length = gpc_get_int( 'gadiv_fibonacci_length', 0 );
				
				if( plugin_config_get( 'gadiv_fibonacci_length' ) != $f_gadiv_fibonacci_length ) {
					plugin_config_set( 'gadiv_fibonacci_length', $f_gadiv_fibonacci_length );
				}
			}
			
			$f_gadiv_show_storypoints = gpc_get_int( 'gadiv_show_storypoints', 0 );
			if( plugin_config_get( 'gadiv_show_storypoints' ) != $f_gadiv_show_storypoints ) {
				plugin_config_set( 'gadiv_show_storypoints', $f_gadiv_show_storypoints );
			}
			
			# make task unit checks
			$f_gadiv_task_unit_mode = gpc_get_string( 'gadiv_task_unit_mode', 'h' );
			if( plugin_config_get( 'gadiv_task_unit_mode' ) != $f_gadiv_task_unit_mode &&
				 $_POST['changeUnit'] ) {
				plugin_config_set( 'gadiv_task_unit_mode', $f_gadiv_task_unit_mode );
			}
			
			# make user story unit checks
			$f_gadiv_userstory_unit_mode = gpc_get_string( 'gadiv_userstory_unit_mode', 
				'h' );
			if( plugin_config_get( 'gadiv_userstory_unit_mode' ) != $f_gadiv_userstory_unit_mode ) {
				plugin_config_set( 'gadiv_userstory_unit_mode', $f_gadiv_userstory_unit_mode );
			}
			
			$f_gadiv_daily_scrum = gpc_get_int( 'gadiv_daily_scrum', 0 );
			
			if( plugin_config_get( 'gadiv_daily_scrum' ) != $f_gadiv_daily_scrum ) {
				plugin_config_set( 'gadiv_daily_scrum', $f_gadiv_daily_scrum );
			}
			
			# activate scrum for mantis
			$f_gadiv_scrum = gpc_get_int( 'gadiv_scrum', 0 );
			if( $f_gadiv_scrum == 0 ) {
				$f_gadiv_scrum = 1;
			}
			
			if( plugin_config_get( 'gadiv_scrum' ) != $f_gadiv_scrum ) {
				plugin_config_set( 'gadiv_scrum', $f_gadiv_scrum );
			}
			
			# make ranking order checks
			$f_gadiv_show_rankingorder = gpc_get_int( 'gadiv_show_rankingorder', 0 );
			if( plugin_config_get( 'gadiv_show_rankingorder' ) != $f_gadiv_show_rankingorder ) {
				plugin_config_set( 'gadiv_show_rankingorder', $f_gadiv_show_rankingorder );
			}
			
			$f_gadiv_ranking_order = gpc_get_int( 'gadiv_ranking_order', 0 );
			if( plugin_config_get( 'gadiv_ranking_order' ) != $f_gadiv_ranking_order ) {
				plugin_config_set( 'gadiv_ranking_order', $f_gadiv_ranking_order );
			}
			
			if( plugin_config_get( 'gadiv_ranking_order' ) == 0 ) {
				plugin_config_set( 'gadiv_show_rankingorder', 0 );
			}
			
			$agilemantis_team->changeCustomFieldFilter( "RankingOrder", 
				plugin_config_get( 'gadiv_ranking_order' ) );
			
			# make custom field presentable checks
			$f_gadiv_presentable = gpc_get_int( 'gadiv_presentable', 0 );
			if( plugin_config_get( 'gadiv_presentable' ) != $f_gadiv_presentable ) {
				plugin_config_set( 'gadiv_presentable', $f_gadiv_presentable );
			}
			
			$agilemantis_team->changeCustomFieldFilter( "Presentable", 
				plugin_config_get( 'gadiv_presentable' ) );
			
			# make custom field technical checks
			$f_gadiv_technical = gpc_get_int( 'gadiv_technical', 0 );
			if( plugin_config_get( 'gadiv_technical' ) != $f_gadiv_technical ) {
				plugin_config_set( 'gadiv_technical', $f_gadiv_technical );
			}
			
			$agilemantis_team->changeCustomFieldFilter( "Technical", 
				plugin_config_get( 'gadiv_technical' ) );
			
			# make custom field release documentation checks
			$f_gadiv_release_documentation = gpc_get_int( 
				'gadiv_release_documentation', 0 );
			if( plugin_config_get( 'gadiv_release_documentation' ) != $f_gadiv_release_documentation ) {
				plugin_config_set( 'gadiv_release_documentation', $f_gadiv_release_documentation );
			}
			
			$agilemantis_team->changeCustomFieldFilter( "InReleaseDocu", 
				plugin_config_get( 'gadiv_release_documentation' ) );
			
			# make custom fields tracker_planned_costs checks
			$f_gadiv_tracker_planned_costs = gpc_get_int( 
				'gadiv_tracker_planned_costs', 0 );
			if( plugin_config_get( 'gadiv_tracker_planned_costs' ) != $f_gadiv_tracker_planned_costs ) {
				plugin_config_set( 'gadiv_tracker_planned_costs', $f_gadiv_tracker_planned_costs );
			}
			
			$agilemantis_team->changeCustomFieldFilter( "PlannedWork", 
				plugin_config_get( 'gadiv_tracker_planned_costs' ) );
			
			$backlogProjects = $agilemantis_project->getProjectsInBacklogs();
			if( !empty( $backlogProjects ) ) {
				foreach( $backlogProjects as $num => $row ) {
					$agilemantis_project->addAdditionalProjectFields( $row['project_id'] );
				}
			}
		}
	}
} else {
	$throw_error_7 = true;
}

if( plugin_is_loaded( 'agileMantisExpert' ) && $_FILES['license']['tmp_name'] != "" ) {
	if( $_FILES['license']['name'] == 'license.txt' ) {
		$uploadfile = config_get_global( 'plugin_path' ) . 'agileMantisExpert' . DIRECTORY_SEPARATOR .
			 'license' . DIRECTORY_SEPARATOR . basename( $_FILES['license']['name'] );
		if( !move_uploaded_file( $_FILES['license']['tmp_name'], $uploadfile ) ) {
			$throw_error_5 = true;
			$file_upload = true;
		}
	} else {
		$throw_error_5 = true;
	}
}

# change task unit action
if( $_POST['changeUnit'] == 'deleteUnit' ) {

	function getTasksWithWrontUnit( $task_list, $sprint_name = "" ) {
		global $agilemantis_userstory;
		global $agilemantis_sprint;
		$unit = plugin_config_get( 'gadiv_task_unit_mode' );

		$userstories = $agilemantis_userstory->getUserStoriesWithSpecificSprint( $sprint_name );
		
		// Tasks zu User Stories, die zu keinem Sprint gehören
		if( !empty( $userstories ) ) {

			foreach( $userstories as $num => $row ) {
				$task = $agilemantis_sprint->getSprintTasks( $row['bug_id'], 0 );
				if( !empty( $task ) ) {
					foreach( $task as $key => $value ) {
						if( $unit != $value['unit'] && $value['status'] < 4 ) {
							$task_list[$value['id']] = true;
						}
					}
				}
			}
		}
		return $task_list;
	}
	
	$task_list = array();
	$task_list = getTasksWithWrontUnit( $task_list );
	
	// Ermittle neue Sprints
	$new_sprints = $agilemantis_sprint->getNewSprints();
	if( !empty( $new_sprints ) ) {
		foreach( $new_sprints as $num => $row ) {
			$task_list = getTasksWithWrontUnit( $task_list, $row['name'] );
		}
	}
	if( !empty( $task_list ) ) {
		foreach( $task_list as $num => $row ) {
			$agilemantis_sprint->resetPlanned( $num );
		}
	}
}

form_security_purge( 'plugin_format_config_edit' );

# redirect back to config page with errors


if( $throw_error_5 ) {
	print_successful_redirect( plugin_page( 'config.php&error=file_upload_error', true ) );
}

if( !$throw_error_7 ) {
	if( $throw_error_4 ) {
		print_successful_redirect( plugin_page( 'config.php&error=workday_error', true ) );
	}
}

if( $throw_error_7 ) {
	print_successful_redirect( plugin_page( 'config.php&error=sprint_length_error', true ) );
}

if( $throw_error_1 ) {
	print_successful_redirect( plugin_page( 'config.php&error=no_license_error', true ) );
}

if( $throw_error_2 && !$file_upload ) {
	print_successful_redirect( plugin_page( 'config.php&error=could_not_find_error', true ) );
}

if( $throw_error_3 ) {
	print_successful_redirect( plugin_page( 'config.php&error=empty_license_error', true ) );
}

print_successful_redirect( plugin_page( 'config.php&save=success', true ) );
?>