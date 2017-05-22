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


$_GET['page'] = str_replace( 'agileMantis/', '', $_GET['page'] );
$page_name = str_replace( '.php', '', $_GET['page'] );

if( $_POST['sprintName'] != "" || $sprInfo[0]['name'] ) {
	
	if( $_POST['sprintName'] ) {
		$agilemantis_sprint->sprint_id = $_POST['sprintName'];
	} else {
		$agilemantis_sprint->sprint_id = $sprInfo[0]['name'];
	}
	
	$sprint_id = '<input type="hidden" name="sprintName" value="' . $agilemantis_sprint->sprint_id .
		 '">';
	$s = $agilemantis_sprint->getSprintById();
	$userIsScrumMaster = $agilemantis_team->isScrumMaster( $s['team_id'], $user_id );
	$userIsDeveloper = $agilemantis_team->isDeveloper( $s['team_id'], $user_id );
	$convertedDateStart = substr($s['start'], 0, 10);
	$convertedDateEnd = substr($s['end'], 0, 10);
	$temp_start_date = explode('-',$convertedDateStart);
	$temp_end_date = explode('-',$convertedDateEnd);
	$s['start'] = mktime( 0, 0, 0, $temp_start_date[1], $temp_start_date[2], $temp_start_date[0] );
	$s['end'] = mktime( 0, 0, 0, $temp_end_date[1], $temp_end_date[2], $temp_end_date[0] );
	
	if( $s['status'] > 0 ) {
		if( $s['unit_planned_task'] > 0 ) {
			$unit = '(' . $agilemantis_tasks->getUnitById( $s['unit_planned_task'] ) . ')';
			$currentUnit = $agilemantis_tasks->getUnitById( $s['unit_planned_task'] );
		}
	} else {
		if( plugin_config_get( 'gadiv_task_unit_mode' ) != "keine" ) {
			$unit = '(' . plugin_config_get( 'gadiv_task_unit_mode' ) . ')';
			$currentUnit = plugin_config_get( 'gadiv_task_unit_mode' );
		}
	}
	
	if( $currentUnit == 'T' ) {
		$multiplier = str_replace( ',', '.', plugin_config_get( 'gadiv_workday_in_hours' ) );
	} else {
		$multiplier = 1;
	}
	
	$end_date = $s['end'];
	if( time() >= $s['start'] ) {
		$start_date = time();
	} else {
		$start_date = $s['start'];
	}
	
	if( $s['status'] == 0 ) {
		$start_date = $s['start'];
	}
	$diff = $end_date - $start_date;
	$anzahl_tage = ceil( $diff / 86400 );
	
	if( $anzahl_tage == 0 && $end_date > time() ) {
		$anzahl_tage = 1;
	} elseif( $anzahl_tage <= 0 ) {
		$anzahl_tage = 0;
	}
	
	$today_date = $start_date;
	$date_start = date( 'Y', $today_date ) . '-' . date( 'm', $today_date ) . '-' .
		 date( 'd', $today_date );
	$date_end = date( 'Y', $end_date ) . '-' . date( 'm', $end_date ) . '-' . date( 'd', $end_date );
	$capacity = $agilemantis_av->getTeamCapacity( $s['team_id'], $date_start, $date_end );
	
	if( $capacity == "" ) {
		$capacity = 0;
	}
	
	$calculate_storypoints = $agilemantis_sprint->countSprintStories( $s['name'] );
	if( !empty( $calculate_storypoints ) ) {
		foreach( $calculate_storypoints AS $num => $row ) {
			$gesamt_storypoints += $agilemantis_pb->getStoryPoints( $row['id'] );
		}
	}
	
	$onlyOpenStories = false;
	if( config_get( 'show_only_open_userstories', null, auth_get_current_user_id() ) == 1 ) {
		$onlyOpenStories = true;
	}
	
	$stories_without_tasks_exist = false;
	$tasks_with_planned_capacity_exist = false;
	$tasks_without_planned_capacity_exist = false;
	
	$us = $agilemantis_sprint->getSprintStories( $s['name'], $onlyOpenStories );
	if( !empty( $us ) ) {
		$added = false;
		foreach( $us as $num => $row ) {
			$tasks = $agilemantis_sprint->getSprintTasks( $row['id'], 0 );
			if( empty( $tasks ) ) {
				$stories_without_tasks_exist = true;
			} else {
				foreach( $tasks as $key => $value ) {
					
					$planned_capacity += $value['rest_capacity'];
					
					if( $value['planned_capacity'] == '0.00' ) {
						$tasks_without_planned_capacity_exist = true;
					} else {
						$tasks_with_planned_capacity_exist = true;
					}
					
					if( $s['status'] == 0 ) {
						$agilemantis_tasks->setDailyScrum( $value['id'], 0 );
					}
				}
			}
		}
	}
	
	if( $planned_capacity == "" ) {
		$planned_capacity = 0;
	}
	
	if( $userIsScrumMaster == true && $userIsDeveloper == false ) {
		$disable_button = '';
	} else {
		$disable_button = 'disabled';
	}
	
	if( $userIsScrumMaster == false && $userIsDeveloper == true ) {
		$disable_button = 'disabled';
	} else {
		$disable_button = '';
	}
	
	if( $userIsScrumMaster == false && $userIsDeveloper == false &&
		 !$_SESSION['AGILEMANTIS_ISMANTISADMIN'] ) {
		$disable_button = 'disabled';
	} else {
		$disable_button = '';
	}
	
	if( $s['status'] == 2 ) {
		$disable = 'disabled';
		$disable_button = 'disabled';
	} else {
		$disable = '';
	}
	
	if( $planned_capacity * $multiplier > $capacity && ($currentUnit == 'h' || $currentUnit == 'T') ) {
		$hinweis_rest_capacity = plugin_lang_get( 'sprint_backlog_error_108702' );
		$span_left = '<span style="color:red; font-weight:bold;">';
		$span_right = '</span>';
	}
}
if( $_SESSION['AGILEMANTIS_ISMANTISUSER'] && !$_SESSION['AGILEMANTIS_ISMANTISADMIN'] && $teams == 0 ) {
	$hinweis = plugin_lang_get( 'sprint_backlog_hint' );
	$no_sprints = true;
}

if( $page_name == 'sprint_backlog' ) {
	$header_title = 'Sprint Backlog';
}
if( $page_name == 'taskboard' ) {
	$header_title = 'Taskboard';
}
if( $page_name == 'daily_scrum_meeting' ) {
	$header_title = 'Daily Scrum Board';
}
if( $page_name == 'statistics' ) {
	$header_title = plugin_lang_get( 'statistics_title' );
}
?>
<?php html_page_top($header_title);?>
<?php print_recently_visited();?>
<?php if( $_GET['warning'] == 1 ) {
	$warning = plugin_lang_get( 'sprint_backlog_error_100700' ).'<br>';?>
<?php }?>
<?php if( $_GET['warning'] == 2 ) {
	$warning =  plugin_lang_get( 'sprint_backlog_error_108700' ).'<br>';?>
<?php }?>
<?php if( $_GET['warning'] == 3 ) {
	$warning = plugin_lang_get( 'sprint_backlog_error_107700' );?>
<?php }?>
<?php if( $_GET['warning'] == 4 ) {
	$warning = plugin_lang_get( 'sprint_backlog_error_107702' );?>
<?php }?>
<?php if( $warning != '' && $system == "" ) {?>
<br>
<center>
	<span style="color: red; font-size: 16px; font-weight: bold;"><?php 
		echo $warning?></span>
</center>
<?php }?>
<?php if($hinweis_rest_capacity != '' && $system == ""){?>
<br>
<center>
	<span style="color: red; font-size: 16px; font-weight: bold;"><?php 
		echo $hinweis_rest_capacity?></span>
</center>
<?php }?>
<?php if($hinweis != "" && $system == ""){?>
<br>
<center>
	<span style="color: red; font-size: 16px; font-weight: bold;"><?php 
		echo $hinweis?></span>
</center>
<?php }?>
<?php if($system != "" && $s['status'] == 1){?>
<br>
<center>
	<span style="color: red; font-size: 16px; font-weight: bold;"><?php 
		echo $system?></span>
</center>
<?php }?>