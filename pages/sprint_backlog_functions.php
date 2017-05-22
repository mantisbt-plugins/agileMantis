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



# get current user information
$user_id = auth_get_current_user_id();
$teams = $agilemantis_team->countMemberTeams( $user_id );

# check if chose sprint page or a sprint directly appears on screen
$show_all_sprints = false;
if( $_SESSION['AGILEMANTIS_ISMANTISUSER'] && $teams > 1 ) {
	$show_all_sprints = true;
}
if( $_SESSION['AGILEMANTIS_ISMANTISUSER'] && $teams >= 1 &&
	 $agilemantis_sprint->countUserSprints( $user_id ) == 1 ) {
	$show_all_sprints = false;
	$teams = 1;
}
if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] && $teams > 0 && $teams != 1 ) {
	$show_all_sprints = true;
}
if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] && $teams == 0 ) {
	$show_all_sprints = true;
}
if( $_POST['chose_sprint'] && $_POST['submit'] != "backlog" ) {
	$show_all_sprints = true;
}
if( $_POST['do_not_enter_sprint'] ) {
	$show_all_sprints = true;
}
if( $teams == 1 && $show_all_sprints == false && $_POST['sprintName'] == "" ) {
	$sprInfo = $agilemantis_sprint->getCurrentUserSprint( $user_id );
	if( $sprInfo[0] == "" ) {
		$show_all_sprints = true;
	} else {
		$_POST['sprintName'] = $sprInfo[0]['name'];
	}
}
if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] && $teams == 0 && $_POST['sprintName'] != "" ) {
	$show_all_sprints = false;
}
if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] && $agilemantis_sprint->countUserSprints( $user_id ) == 0 &&
	 $teams >= 1 ) {
	$show_all_sprints = true;
}
if( $_POST['sprintName'] != "" ) {
	$show_all_sprints = false;
}
if( $_GET['sprintName'] != "" ) {
	$show_all_sprints = false;
	$_POST['sprintName'] = urldecode( $_GET['sprintName'] );
}
if( $_GET['chose_sprint'] != "" ) {
	$show_all_sprints = true;
}

# call revoke user story function
if( $_POST['revoke_userstory'] ) {
	$agilemantis_pb->doUserStoryToSprint( $_POST['us_id'], '' );
}

# confirm sprint
if( $_POST['confirmSprint'] == 1 ) {
	$agilemantis_sprint->sprint_id = $_POST['id'];
	$sprintInfo = $agilemantis_sprint->getSprintByName();
	$agilemantis_sprint->setSprintStatus( 1, $sprintInfo['id'] );
	$agilemantis_sprint->confirmInformation( $sprintInfo['id'], 
		plugin_config_get( 'gadiv_storypoint_mode' ), 
		plugin_config_get( 'gadiv_userstory_unit_mode' ), 
		plugin_config_get( 'gadiv_task_unit_mode' ), 
		str_replace( ',', '.', plugin_config_get( 'gadiv_workday_in_hours' ) ) );
}

# close sprint operations
if( $_POST['close_sprint'] ) {
	$agilemantis_sprint->closeInformation( $_POST['id'], 
		plugin_config_get( 'gadiv_storypoint_mode' ), 
		plugin_config_get( 'gadiv_userstory_unit_mode' ), 
		plugin_config_get( 'gadiv_task_unit_mode' ), 
		str_replace( ',', '.', plugin_config_get( 'gadiv_workday_in_hours' ) ) );
	$userstories = $agilemantis_sprint->getSprintStories( $_POST['name'] );
	foreach( $userstories AS $num => $row ) {
		$task = $agilemantis_sprint->getSprintTasks( $row['id'] );
		foreach( $task AS $key => $value ) {
			$agilemantis_tasks->updateTaskLog( $value['id'], auth_get_current_user_id(), "closed" );
			$agilemantis_tasks->setTaskStatus( $value['id'], 5 );
		}
		if( $_POST['closeUserStories'] == 1 ) {
			$agilemantis_sprint->closeUserStory( $row['id'], 90, $user_id );
		}
	}
	$agilemantis_sprint->setSprintStatus( 2, $_POST['id'] );
	$system = "Der Sprint wurde erfolgreich geschlossen!";
}

# set different sprint backlog config values
if( !config_is_set( 'show_project_target_version', auth_get_current_user_id() ) ) {
	config_set( 'show_project_target_version', 0, auth_get_current_user_id() );
}

if( !config_is_set( 'show_only_own_userstories', auth_get_current_user_id() ) ) {
	config_set( 'show_only_own_userstories', 0, auth_get_current_user_id() );
}

if( !config_is_set( 'show_only_open_userstories', auth_get_current_user_id() ) ) {
	config_set( 'show_only_open_userstories', 0, auth_get_current_user_id() );
}

if( !config_is_set( 'current_user_sprint_backlog_filter', auth_get_current_user_id() ) ) {
	config_set( 'current_user_sprint_backlog_filter', '', auth_get_current_user_id() );
}

if( !config_is_set( 'current_user_sprint_backlog_filter_direction', auth_get_current_user_id() ) ) {
	config_set( 'current_user_sprint_backlog_filter_direction', 'ASC', auth_get_current_user_id() );
}

if( !config_is_set( 'plugin_agileMantis_gadiv_show_storypoints' ) ) {
	config_set( 'plugin_agileMantis_gadiv_show_storypoints', 0 );
}

if( !config_is_set( 'plugin_agileMantis_gadiv_show_rankingorder' ) ) {
	config_set( 'plugin_agileMantis_gadiv_show_rankingorder', 0 );
}

if( plugin_is_loaded( 'agileMantisExpert' ) ) {
	
	if( !config_is_set( 'velocity_checkbox_selected', auth_get_current_user_id() ) ) {
		config_set( 'velocity_checkbox_selected', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_sp_gesamt', auth_get_current_user_id() ) ) {
		config_set( 'velocity_sp_gesamt', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_je_entwickler', auth_get_current_user_id() ) ) {
		config_set( 'velocity_je_entwickler', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_je_entwickler_tag', auth_get_current_user_id() ) ) {
		config_set( 'velocity_je_entwickler_tag', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_je_aufwands_tag', auth_get_current_user_id() ) ) {
		config_set( 'velocity_je_aufwands_tag', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_kapazitaet', auth_get_current_user_id() ) ) {
		config_set( 'velocity_kapazitaet', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_referenz_sprint', auth_get_current_user_id() ) ) {
		config_set( 'velocity_referenz_sprint', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_vorgaenger_sprint', auth_get_current_user_id() ) ) {
		config_set( 'velocity_vorgaenger_sprint', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_letzte_x_vorg_sprints', auth_get_current_user_id() ) ) {
		config_set( 'velocity_letzte_x_vorg_sprints', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_hours', auth_get_current_user_id() ) ) {
		config_set( 'burndown_hours', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_hours_capacity', auth_get_current_user_id() ) ) {
		config_set( 'burndown_hours_capacity', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_hours_optimal', auth_get_current_user_id() ) ) {
		config_set( 'burndown_hours_optimal', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_hours_ideal', auth_get_current_user_id() ) ) {
		config_set( 'burndown_hours_ideal', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_hours_actual', auth_get_current_user_id() ) ) {
		config_set( 'burndown_hours_actual', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_hours_trend', auth_get_current_user_id() ) ) {
		config_set( 'burndown_hours_trend', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_sp', auth_get_current_user_id() ) ) {
		config_set( 'burndown_sp', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_sp_ideal', auth_get_current_user_id() ) ) {
		config_set( 'burndown_sp_ideal', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_sp_actual', auth_get_current_user_id() ) ) {
		config_set( 'burndown_sp_actual', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_sp_trend', auth_get_current_user_id() ) ) {
		config_set( 'burndown_sp_trend', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_tasks', auth_get_current_user_id() ) ) {
		config_set( 'burndown_tasks', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_tasks_ideal', auth_get_current_user_id() ) ) {
		config_set( 'burndown_tasks_ideal', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_tasks_actual', auth_get_current_user_id() ) ) {
		config_set( 'burndown_tasks_actual', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_tasks_trend', auth_get_current_user_id() ) ) {
		config_set( 'burndown_tasks_trend', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'utilization_distribution_planned', auth_get_current_user_id() ) ) {
		config_set( 'utilization_distribution_planned', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'utilization_distribution_remains', auth_get_current_user_id() ) ) {
		config_set( 'utilization_distribution_remains', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'utilization_utilizationdetailed', auth_get_current_user_id() ) ) {
		config_set( 'utilization_utilizationdetailed', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'statistic_velocity_amount_of_sprints', auth_get_current_user_id() ) ) {
		config_set( 'statistic_velocity_amount_of_sprints', 5, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'statistic_velocity_referenced_sprint', auth_get_current_user_id() ) ) {
		config_set( 'statistic_velocity_referenced_sprint', "", auth_get_current_user_id() );
	}
}

# set sorting direction of the sprint backlog / user story table
if( !empty( $_GET['sort_by'] ) && isset( $_GET['sort_by'] ) ) {
	$direction = $_GET['direction'];
	config_set( 'current_user_sprint_backlog_filter_direction', $direction, 
		auth_get_current_user_id() );
	$sort_by = $_GET['sort_by'];
	config_set( 'current_user_sprint_backlog_filter', $sort_by, auth_get_current_user_id() );
}

# set / update checkbox values 
if( $_POST['action'] == 'save_sprint_options' ) {
	config_set( 'show_project_target_version', $_POST['show_project_target_version'], 
		auth_get_current_user_id() );
	config_set( 'show_only_own_userstories', $_POST['show_only_own_userstories'], 
		auth_get_current_user_id() );
	config_set( 'show_only_open_userstories', $_POST['show_only_open_userstories'], 
		auth_get_current_user_id() );
}

# divide task action
if( $_POST['divide_task'] == plugin_lang_get( 'button_assume' ) ) {
	$agilemantis_sprint->sprint_id = $_POST['sprintName'];
	$sprintInfo = $agilemantis_sprint->getSprintById();
	
	$agilemantis_tasks->daily_scrum = 1;
	
	# common
	$agilemantis_tasks->us_id = $_POST['us_id'];
	$agilemantis_tasks->name = $_POST['task_name'];
	$agilemantis_tasks->description = $_POST['task_description'];
	
	# old task
	$agilemantis_tasks->id = $_POST['task_id'];
	$agilemantis_tasks->developer = $_POST['developer_id'];
	$agilemantis_tasks->status = 4;
	$agilemantis_tasks->planned_capacity = $_POST['planned_capacity'];
	$agilemantis_tasks->rest_capacity = 0;
	$agilemantis_tasks->addFinishedNote( $agilemantis_tasks->us_id, $agilemantis_tasks->id, 
		auth_get_current_user_id() );
	$agilemantis_tasks->editTask();
	$agilemantis_tasks->setDailyScrum( $agilemantis_tasks->id, $agilemantis_tasks->daily_scrum );
	
	# new task
	$agilemantis_tasks->id = 0;
	$agilemantis_tasks->us_id = $_POST['us_id'];
	$agilemantis_tasks->description = $_POST['task_description'];
	$agilemantis_tasks->developer = 0;
	$agilemantis_tasks->status = 1;
	if( $sprintInfo['status'] == 0 ) {
		$agilemantis_tasks->planned_capacity = $_POST['rest_capacity'];
	} else {
		$agilemantis_tasks->planned_capacity = 0;
	}
	
	$agilemantis_tasks->unit = $agilemantis_tasks->getUnitId( 
		plugin_config_get( 'gadiv_task_unit_mode' ) );
	
	$agilemantis_tasks->rest_capacity = $_POST['rest_capacity'];
	$agilemantis_tasks->capacity -= $agilemantis_tasks->planned_capacity;
	$agilemantis_tasks->editTask();
	$agilemantis_tasks->setDailyScrum( $agilemantis_tasks->id, $agilemantis_tasks->daily_scrum );
}
?>