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




#	This class will hold functions for agileMantis task management
class gadiv_tasks extends gadiv_commonlib {
	var $developer;
	var $name;
	var $description;
	var $status;
	var $planned_capacity;
	var $performed_capacity;
	var $rest_capacity;
	var $us_id;
	var $id;
	var $user_id;
	
	# get sprint capacity of one developer in a team
	function getDeveloperSprintCapacity( $taskUnit ) {
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		# Fetch User Story Information
		$userstory = $this->checkForUserStory( $this->us_id );
		$this->getAdditionalProjectFields();
		
		$t_params = array( $this->spr, $userstory['sprint'], $this->developer );
		
		# Add Condition to WHERE-Clause
		if( $this->id > 0 ) {
			$addSql = " AND id != " . db_param( 3 );
			$t_params[] = $this->id;
		}
		
		# Fetch all Tasks and sum up Rest Capacity
		$t_sql = "SELECT sum(rest_capacity) AS rest_capacity 
					FROM $t_mantis_custom_field_string_table 
					LEFT JOIN gadiv_tasks ON bug_id = us_id 
					WHERE field_id = " . db_param( 0 ) . " 
					AND value = " . db_param( 1 ) . " 
					AND us_id IS NOT NULL 
					AND status < 4 
					AND developer_id = " . db_param( 2 ) . " " . $addSql . "
					GROUP BY field_id";
		
		$sprint = $this->executeQuery( $t_sql, $t_params );
		
		# Fetch Sprint Start and End
		$t_sql = "SELECT start, enddate as " . AGILEMANTIS_END_FIELD . ", team_id, status 
					FROM gadiv_sprints 
					WHERE name = " . db_param( 0 );
		$t_params = array( $userstory['sprint'] );
		$sprintinfo = $this->executeQuery( $t_sql, $t_params );
		
		# Set correct Start date for Capacity
		if( $sprintinfo[0]['status'] == 0 ) {
			$date_start = $sprintinfo[0]['start'];
		}
		
		if( $sprintinfo[0]['status'] == 1 ) {
			$date_start = $this->getNormalDateFormat(date( 'Y-m-d' ));
		}
		
		if( $sprintinfo[0]['status'] == 2 ) {
			return true;
		}
		
		# Fetch Developer Capacity in a Sprint
		$t_sql = "SELECT sum(capacity) AS capacity 
					FROM gadiv_rel_user_team_capacity 
					WHERE user_id = " . db_param( 0 ) . " 
					AND team_id = " . db_param( 1 ) . " 
					AND date >= " . db_param( 2 ) . " 
					AND date <= " . db_param( 3 ) . "
					GROUP BY user_id";
		$t_params = array( $this->developer, $sprintinfo[0]['team_id'], $date_start, 
			$sprintinfo[0]['end'] );
		$developer = $this->executeQuery( $t_sql, $t_params );
		
		# If Unit is "T", calculate result with workday hours
		if( $taskUnit == 'T' ) {
			$multiplier = str_replace( ',', '.', plugin_config_get( 'gadiv_workday_in_hours' ) );
		} else {
			$multiplier = 1;
		}
		
		# Check if Sprint Rest Capacity + New Planned Capacity is larger than Developer Capacity
		if( ($sprint[0]['rest_capacity'] + $this->rest_capacity) * $multiplier >
			 $developer[0]['capacity'] ) {
			return false;
		}
		
		return true;
	}
	
	# sets userstory status
	function setUserStoryStatus( $id, $status, $user_id = "" ) {
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		$t_mantis_bug_history_table = db_get_table( 'mantis_bug_history_table' );
		
		$t_sql = "UPDATE $t_mantis_bug_table 
					SET status = " . db_param( 0 ) . " 
					WHERE id = " . db_param( 1 );
		$t_params = array( $status, $id );
		db_query_bound( $t_sql, $t_params );
		
		$t_sql = "INSERT INTO $t_mantis_bug_history_table 
		         (user_id, bug_id, field_name, old_value, new_value, type, date_modified) VALUES (" . 
		          db_param( 0 ) . "," . 
		          db_param( 1 ) . ",'status'," . 
		          db_param( 2 ) . ",80,0," . 
		          db_param( 3 ) . ") ";
		$t_params = array( $user_id, $id, $status, 
			mktime( date( 'H' ), date( 'i' ), date( 's' ), date( 'm' ), date( 'd' ), date( 'Y' ) ) );
		db_query_bound( $t_sql, $t_params );
	}
	
	# get all logging entries from predefined task events
	function getTaskEvent( $id, $event ) {
		$t_sql = "SELECT * FROM 
					gadiv_task_log 
					WHERE event = " . db_param( 0 ) . " 
					AND task_id = " . db_param( 1 );
		$t_params = array( $event, $id );
		$result_array = $this->executeQuery( $t_sql, $t_params );
		return $result_array[0];
	}
	
	# function bugnote_add( 
	# $p_bug_id, 
	# $p_bugnote_text, 
	# $p_time_tracking = '0:00', 
	# $p_private = false, 
	# $p_type = 0, 
	# $p_attr = '', 
	# $p_user_id = null, 
	# $p_send_email = TRUE )
	function createBugNote( $p_story_id, $p_user_id, $p_note ) {
		$id = bugnote_add( $p_story_id, $p_note, null, false, 0, "", $p_user_id, null );
		
		return $id;
	}
	
	# adds a status note to an existing userstory or tracker
	function addStatusNote( $story_id, $task_id, $user_id, $status_text ) {
		$task = $this->getSelectedTask( $task_id );
		$user_name = $this->getUserName( $task['developer_id'] );
		$t_note = "Task <b>\"" . $task['name'] . "\"</b>, ";
		$t_note .= plugin_lang_get( 'edit_task_developer' , 'agileMantis' );
		$t_note .= " \"" . $user_name . "\", ";
		$t_note .= $status_text;
		$this->createBugNote( $story_id, $user_id, $t_note );
	}

	function addFinishedNote( $story_id, $task_id, $user_id ) {
		$status_text = plugin_lang_get( 'status_resolved', 'agileMantis'  );
		$this->addStatusNote( $story_id, $task_id, $user_id, $status_text );
	}

	function addReopenNote( $story_id, $task_id, $user_id ) {
		$status_text = plugin_lang_get( 'status_reopened' , 'agileMantis' );
		$this->addStatusNote( $story_id, $task_id, $user_id, $status_text );
	}
	
	# replace planned capacity
	function replacePlannedCapacity( $task_id ) {
		$t_sql = "UPDATE gadiv_daily_task_performance 
					SET performed = '0.00', 
					rest = " . db_param( 0 ) . " 
					WHERE task_id = " . db_param( 1 );
		$t_params = array( $this->planned_capacity, $task_id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# set task into daily scrum mode
	function setDailyScrum( $task_id, $daily_scrum ) {
		$t_sql = "UPDATE gadiv_tasks 
					SET daily_scrum = " . db_param( 0 ) . " 
					WHERE id = " . db_param( 1 );
		$t_params = array( ( int ) $daily_scrum, $task_id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# get performed capacity of one tasks
	function getPerformedCapacity( $task_id ) {
		$t_sql = "SELECT sum(performed) AS performed_capacity 
					FROM gadiv_daily_task_performance 
					WHERE task_id = " . db_param( 0 ) . " 
					AND date LIKE " . db_param( 1 ) . "
					GROUP BY task_id";
		$t_params = array( $task_id, "%" . $this->getNormalDateFormat(date( 'Y-m-d' )) . "%" );
		$task = $this->executeQuery( $t_sql, $t_params );
		return $task[0]['performed_capacity'];
	}
	
	# get all assumed userstories in a predefined period
	function getAssumedUserStories( $bugList, $dayStart, $dayEnd ) {
		$t_mantis_bug_history_table = db_get_table( 'mantis_bug_history_table' );

		$t_sql = "SELECT * FROM $t_mantis_bug_history_table 
					WHERE bug_id IN ( " . $bugList . " ) 
					AND field_name = 'Sprint' 
					AND date_modified BETWEEN " . db_param( 0 ) . " 
					AND " . db_param( 1 );
		$t_params = array( $dayStart, $dayEnd );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get all notices of a user story
	function getNotices( $id ) {
		$t_mantis_bugnote_table = db_get_table( 'mantis_bugnote_table' );
		$t_mantis_bugnote_text_table = db_get_table( 'mantis_bugnote_text_table' );
		
		$t_sql = "SELECT * 
					FROM $t_mantis_bugnote_table AS mbnt 
					LEFT JOIN $t_mantis_bugnote_text_table AS mbntt ON mbntt.id = mbnt.id 
					WHERE bug_id =" . db_param( 0 ) . " ";
		$t_params = array( $id );
		return $this->executeQuery( $t_sql, $t_params );
	}

	function getUserDayCapacity( $user_id, $team_id ) {
		$t_sql = "SELECT capacity 
					FROM gadiv_rel_user_team_capacity 
					WHERE team_id = " . db_param( 0 ) . " 
					AND date = " . db_param( 1 ) . " 
					AND user_id = " . db_param( 2 );
		$t_params = array( $team_id, $this->getNormalDateFormat(date( 'Y-m-d' )), $user_id );
		$user = $this->executeQuery( $t_sql, $t_params );
		return $user[0]['capacity'];
	}

	/**
		*	Gets daily perfomance for one task
		*
		*	@Version: 1.0.0
		*	@Author: Jan Koch
		*	@Param: task_id - id of a task
		*	@Return: return all saved task performances
		*/
	function getDailyPerformance( $task_id ) {
		$t_sql = "SELECT * FROM gadiv_daily_task_performance 
					WHERE task_id = " . db_param( 0 );
		$t_params = array( $task_id );
		return $this->executeQuery( $t_sql, $t_params );
	}

	function getSession( $user_id ) {
		$t_sql = "SELECT expert FROM gadiv_additional_user_fields 
					WHERE user_id = " . db_param( 0 );
		$t_params = array( $user_id );
		$session = $this->executeQuery( $t_sql, $t_params );
		return $session[0]['expert'];
	}
	
	function tasksExist() {
		$t_sql = "SELECT COUNT(*) as anz FROM gadiv_tasks";
		$rs = $this->executeQuery( $t_sql, null );
		$count = $rs[0][anz];
		return $count > 0;
	}
}
?>