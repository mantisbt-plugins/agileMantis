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


class gadiv_commonlib {
	var $id;
	var $pbid;
	var $us_id;
	var $businessvalue;
	var $bv;
	var $storypoints;
	var $sp;
	var $pb;
	var $ro;
	var $pw;
	var $pr;
	var $un;
	var $unitfield;
	var $spr;
	var $rld;
	var $user_id;
	var $capacity;
	var $availability;
	var $monday;
	var $tuesday;
	var $wednesday;
	var $thursday;
	var $friday;
	var $saturday;
	var $sunday;
	var $total_capacity;
	var $lastresult;
	var $rnorder;
	var $userorder;
	var $tech;
	var $bug_arr;
	var $unit;
	var $custom_field_arr;
	
	#This function mask an internal CDATA TAG for all description tags
	function safeCData($str)
	{
		//CDATA im Text in XYZ... wandeln
		$str = str_replace('<![CDATA[', 'XYZAuf', $str);
		$str = str_replace(']]>', 'XYZZu', $str);
		//Das eigene CDATA um den Text setzen
		$str = '<![CDATA[' . $str . ']]>';
		return $str;
	}
	

	function executeQuery( $p_sql, $p_params = null ) {
		if( $t_result = db_query_bound( $p_sql, $p_params ) ) {
			$t_result_set = array();
			if( $t_result === TRUE ) {
				$t_result_set = true;
			} else {
				while( $t_rs = db_fetch_array( $t_result ) ) {
					if( !empty( $t_rs ) ) {
						$t_result_set[] = $t_rs;
					}
				}
			}
			return $t_result_set;
		}
	}
	
	function createAgManWarning( $field_name ) {
		$str = '<p style="color:red">AGILEMANTIS WARNING: '.plugin_lang_get( 'warning_1', 'agileMantis' ).$field_name;
		$str .= plugin_lang_get( 'warning_2', 'agileMantis' ).'</p>';
		return $str;
	}
	
	# Für die DEMO-Version soll hier ein Hinweis ausgegeben werden,
	# dass die Expert-Komponenten nicht Teil des OpenSource Teils sind.
	function pointOutDemoVersionIfNeeded( $component_name ) {
 		if( $_SERVER["SERVER_NAME"] == AGILEMANTIS_DEMO_VERSION_HOST ) {
			// ACHTUNG: Hier KEINE Zeilenumbrüche in den HTML-Code einbauen. Wird in JavaScript verwendet.
			$noticeText = '<center>';
			$noticeText .= '<span style="background: yellow; color: black; font-size: 16px; font-weight: bold;">';
			$noticeText .= 'Please note!<br />';
			$noticeText .= 'Below you have access to the Expert components ' . $component_name . '.<br />';
			$noticeText .= 'It is not part of the Open Source package.<br />';
			$noticeText .= 'For your own installation you can get a free trial license <a href="'.AGILEMANTIS_ORDER_PAGE_URL.'">here</a><br />';
			$noticeText .= '<br/></span>';
			$noticeText .= '</center>';
			return $noticeText;
		}
		return '';
	}
	
	# count current user sessions
	function countSessions() {
		$t_sql = "SELECT count(expert) AS sessions " .
			 "FROM gadiv_additional_user_fields WHERE expert = 1 GROUP BY expert";
		$t_amountOf = $this->executeQuery( $t_sql );
		return $t_amountOf[0]['sessions'];
	}
	
	# redirects a user to a specific page, sprint backlog or taskboard
	function forwardReturnToPage( $page_name ) {
		if( $_POST['fromDailyScrum'] == 1 ) {
			$header = "Location: " . plugin_page( 'daily_scrum_meeting.php' ) . "&sprintName=" .
				 urlencode( $_POST['sprintName'] );
		} else {
			
			if( $_POST['fromSprintBacklog'] == 1 && $_POST['fromTaskPage'] != 1 ) {
				$header = "Location: " . plugin_page( 'sprint_backlog.php' ) . "&sprintName=" .
					 urlencode( $_POST['sprintName'] );
			}
			
			if( $_POST['fromTaskboard'] == 1 ) {
				$header = "Location: " . plugin_page( 'taskboard.php' ) . "&sprintName=" .
					 urlencode( $_POST['sprintName'] );
			}
			
			if( $_POST['fromStatistics'] == 1 ) {
				$header = "Location: " . plugin_page( 'statistics.php' ) . "&sprintName=" .
					 urlencode( $_POST['sprintName'] );
			}
			
			if( $_POST['fromProductBacklog'] == 1 ) {
				$header = "Location: " . plugin_page( 'product_backlog.php' ) .
					 "&productBacklogName=" . $_POST['productBacklogName'];
			}
			
			if( $_POST['fromSprintBacklog'] == 0 && $_POST['fromTaskboard'] == 0 &&
				 $_POST['fromProductBacklog'] == 0 && $_POST['fromStatistics'] == 0 ) {
				$header = "Location: " . plugin_page( $page_name );
			}
			
			if( $_POST['fromSprintBacklog'] == 1 && $_POST['fromTaskPage'] == 1 ) {
				$header = "Location: " . plugin_page( $page_name );
			}
		}
		
		return $header;
	}

	function getConfigValueNoCache( $p_config_id ) {
		$t_mantis_config_table = db_get_table( 'mantis_config_table' );
		
		$t_sql = "SELECT * FROM $t_mantis_config_table WHERE config_id=" . db_param( 0 );
		$t_params = array( $p_config_id );
		$t_config = $this->executeQuery( $t_sql, $t_params );
		return $t_config[0]['value'];
	}
	
	# get config value from database
	function getConfigValue( $p_config_id, $p_default = null, $p_user = null ) {
		$value = config_get( $p_config_id, $p_default, $p_user );
		return $value;
	}
	
	# get config value by user id from database
	function getConfigUserValue( $p_config_id, $p_user_id ) {
		return $this->getConfigValue( $p_config_id, null, $p_user_id );
	}
	
	# set config value especially for one user into database
	function setConfigValue( $p_config_id, $p_user_id, $p_value ) {
		config_set( $p_config_id, $p_value, $p_user_id );
	}
	
	# user gets marked when capacity is exceeded
	function setUserAsMarkType( $p_id, $p_marking ) {
		$t_sql = "UPDATE gadiv_rel_user_availability_week 
					SET marked = " . db_param( 0 ) . " 
					WHERE user_id = " . db_param( 1 );
		$t_params = array( $p_marking, $p_id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# get user data
	function getUser( $p_user_id ) {
		return user_get_row( $p_user_id );
	}
	
	# get password from a specific user
	function getUserPassword( $p_user_id ) {
		$t_user = user_get_row( $p_user_id );
		return $t_user['password'];
	}
	
	# get user information by id
	function getUserName( $p_user_id ) {
		if( user_exists( $p_user_id ) ) {
			$t_user = user_get_row( $p_user_id );
			return $t_user['username'];
		} else {
			return '';
		}
	}
	
	# get user information by name
	function getUserIdByName( $p_username ) {
		return user_get_id_by_name( $p_username );
	}
	
	# get user real name by user id
	function getUserRealName( $p_user_id ) {
		$t_user = user_get_row( $p_user_id );
		return $t_user['realname'];
	}
	
	# get team user email by user id
	function getUserEmail( $p_user_id ) {
		$t_user = user_get_row( $p_user_id );
		return $t_user['email'];
	}
	
	# get all additional user fields from mantis database
	function getAdditionalUserFields( $p_user_id ) {
		$t_sql = "SELECT * FROM gadiv_additional_user_fields WHERE user_id=" . db_param( 0 );
		$t_params = array( $p_user_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get all additional agileMantis custom field ids from database and return as array
	function getAdditionalProjectFields() {
		$this->bv = $this->getCustomFieldIdByName( "BusinessValue" );
		$this->pb = $this->getCustomFieldIdByName( "ProductBacklog" );
		$this->sp = $this->getCustomFieldIdByName( "Storypoints" );
		$this->spr = $this->getCustomFieldIdByName( "Sprint" );
		$this->ro = $this->getCustomFieldIdByName( "RankingOrder" );
		$this->pr = $this->getCustomFieldIdByName( "Presentable" );
		$this->tech = $this->getCustomFieldIdByName( "Technical" );
		$this->rld = $this->getCustomFieldIdByName( "InReleaseDocu" );
		$this->pw = $this->getCustomFieldIdByName( "PlannedWork" );
		$this->un = $this->getCustomFieldIdByName( "PlannedWorkUnit" );
	}

	function getCustomFieldIdByName( $p_field_name ) {
		$field_id = custom_field_get_id_from_name( $p_field_name );
		return $field_id;
	}
	
	# check if project is in a product backlog
	function projectHasBacklogs( $p_project_id ) {
		$t_sql = "SELECT count(*) AS projects FROM gadiv_rel_productbacklog_projects " .
			 "WHERE project_id = " . db_param( 0 );
			  //GROUP BY project_id";
		$t_params = array( $p_project_id );
		$t_result = $this->executeQuery( $t_sql, $t_params );
		
		if( $t_result[0]['projects'] > 0 ) {
			return true;
		}
		return false;
	}
	
	# set user story confirmation status
	function setConfirmationStatus( $p_bug_id ) {
		$this->setTrackerStatus( $p_bug_id, 50 );
	}
	
	# set a defined tracker / user story status
	function setTrackerStatus( $p_bug_id, $p_status ) {
		bug_set_field( $p_bug_id, 'status', $p_status );
	}
	
	# get all product backlogs with filter and sorting options
	function getProductBacklogs( $p_id = "" ) {
		if( $p_id != "" ) {
			$t_pb_id_filter = " WHERE id = " . db_param( 0 ) . " ";
			$t_params[] = $p_id;
		}
		
		if( $_GET['sort_by'] ) {
			if( $_SESSION['order'] == 0 ) {
				$_SESSION['order'] = 1;
				$t_direction = 'ASC';
			} else {
				$_SESSION['order'] = 0;
				$t_direction = 'DESC';
			}
			switch( $_GET['sort_by'] ) {
				case 'description':
					$t_orderby = " ORDER BY description " . $t_direction;
					break;
				case 'name':
				default:
					$t_orderby = " ORDER BY name " . $t_direction;
			}
		}
		
		if( !$_GET['sort_by'] ) {
			$t_orderby = " ORDER BY name ASC";
			$_SESSION['order'] = 1;
		}
		
		$t_sql = "SELECT * 
				FROM gadiv_productbacklogs " . $t_pb_id_filter . $t_orderby;
		
		
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get all product backlogs of a project
	function getProjectProductBacklogs( $p_project_id ) {
		$t_sql = "SELECT * 
					FROM gadiv_rel_productbacklog_projects AS rpp 
					LEFT JOIN gadiv_productbacklogs AS p ON p.id = rpp.pb_id 
					WHERE project_id=" . db_param( 0 ) . " 
					ORDER BY name ASC";
		$t_params = array( $p_project_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get product backlog information by product backlog id
	function getSelectedProductBacklog() {
		$t_sql = "SELECT * 
					FROM gadiv_productbacklogs 
					WHERE id=" . db_param( 0 );
		$t_params = array( $this->id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get product backlog information by product backlog name
	function getProductBacklogByName( $p_pb_name ) {
		$t_sql = "SELECT * 
				FROM gadiv_productbacklogs 
				WHERE name LIKE " . db_param( 0 );
		$t_params = array( $p_pb_name );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# check if one or more teams work on the same product backlog
	function checkProductBacklogMoreOneTeam( $p_pb_name ) {
		$t_pb_info = $this->getProductBacklogByName( $p_pb_name );
		$t_sql = "SELECT count(*) AS number_of_teams 
					FROM gadiv_teams 
					WHERE pb_id=" . db_param( 0 );
					//GROUP BY pb_id";
		$t_params = array( $t_pb_info[0]['id'] );
		$t_result = $this->executeQuery( $t_sql, $t_params );
		if( $t_result[0]['number_of_teams'] === 1 ) {
			return true;
		}
		return false;
	}
	
	# get team id by product backlog id
	function getTeamIdByBacklog( $p_pb_id ) {
		$t_sql = "SELECT id FROM gadiv_teams WHERE pb_id=" . db_param( 0 );
		$t_params = array( $p_pb_id );
		$t_team = $this->executeQuery( $t_sql, $t_params );
		return $t_team[0]['id'];
	}
	
	# generate team user function and returns team user name
	function generateTeamUser( $p_username ) {
		$t_mutated_vowel = array( ' ', 'Ö', 'ö', 'Ä', 'ä', 'Ü', 'ü', 'ß', '/', '(', ')', '@', '>', 
			'<', '#', '+', '*', '&' );
		$t_normal_vowels = array( '-', 'Oe', 'oe', 'Ae', 'ae', 'Ue', 'ue', 'ss', '_', '_', '_', '_', 
			'_', '_', '_', '_', '_', '_' );
		$p_username = str_replace( $t_mutated_vowel, $t_normal_vowels, $p_username );
		$p_username = 'Team-User-' . $p_username;
		return $p_username;
	}
	
	# check if mantis tracker is user story and return user story values
	function checkForUserStory( $p_bug_id ) {
		$this->getAdditionalProjectFields();
		$t_userstory['name'] = $this->getCustomFieldValueById( $p_bug_id, $this->pb );
		$t_userstory['storypoints'] = $this->getCustomFieldValueById( $p_bug_id, $this->sp );
		$t_userstory['businessValue'] = $this->getCustomFieldValueById( $p_bug_id, $this->bv );
		$t_userstory['sprint'] = $this->getCustomFieldValueById( $p_bug_id, $this->spr );
		$t_userstory['rankingorder'] = $this->getCustomFieldValueById( $p_bug_id, $this->ro );
		$t_userstory['presentable'] = $this->getCustomFieldValueById( $p_bug_id, $this->pr );
		$t_userstory['technical'] = $this->getCustomFieldValueById( $p_bug_id, $this->tech );
		$t_userstory['inReleaseDocu'] = $this->getCustomFieldValueById( $p_bug_id, $this->rld );
		$t_userstory['plannedWork'] = $this->getCustomFieldValueById( $p_bug_id, $this->pw );
		$t_userstory['unit'] = $this->getCustomFieldValueById( $p_bug_id, $this->un );
		return $t_userstory;
	}
	
	# evtl. durch API-Methode ersetzen
	function getUserStoryChanges( $p_bug_id ) {
		$t_mantis_bug_history_table = db_get_table( 'mantis_bug_history_table' );
		
		if (db_is_mssql()) {
			$t_sql = "SELECT top 1 new_value,date_modified
			FROM $t_mantis_bug_history_table
			WHERE bug_id=" . db_param( 0 ) . "
			AND field_name='status'
					AND new_value >= 80
					ORDER BY date_modified ASC";
		} else {
			$t_sql = "SELECT new_value,date_modified
			FROM $t_mantis_bug_history_table
			WHERE bug_id=" . db_param( 0 ) . "
			AND field_name='status'
					AND new_value >= 80
					ORDER BY date_modified ASC LIMIT 1";
		}
		
		$t_params = array( $p_bug_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# set task status by task id
	function setTaskStatus( $p_id, $p_status ) {
		$t_sql = "UPDATE gadiv_tasks 
					SET status=" . db_param( 0 ) . ", 
					rest_capacity=0 
					WHERE id=" . db_param( 1 );
		$t_params = array( $p_status, $p_id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# save task progress in database
	function saveDailyPerformance( $p_rest_flag ) {
		$t_sql = "INSERT INTO gadiv_daily_task_performance" . 
				 " (task_id, performed, rest, date, user_id, rest_flag) VALUES ( " .
				 db_param() . ", " .
				 db_param() . ", " . 
				 db_param() . ", " .
				 db_param() . ", " .  
				 db_param() . ", " .  
				 db_param() . ")";
		
		$t_params = array(
				$this->id,
				$this->capacity,
				$this->rest_capacity,
				$this->getDateFormat(date( 'Y' ), date( 'm' ), date( 'd' ), true),
				$this->user_id,
				$p_rest_flag
		);
		
		db_query_bound( $t_sql, $t_params );
	}
	
	# get task information by task id
	function getSelectedTask( $p_task_id ) {
		$t_sql = "SELECT * FROM gadiv_tasks WHERE id=" . db_param( 0 );
		$t_params = array( $p_task_id );
		$t_task = $this->executeQuery( $t_sql, $t_params );
		return $t_task[0];
	}
	
	# add new task
	function newTask() {
		
		if( $_POST['user'] ) {
			$this->user_id = $_POST['user'];
		} else {
			$this->user_id = auth_get_current_user_id();
		}
		
		$t_sql = "INSERT INTO gadiv_tasks ";
		$t_sql .= "( us_id,developer_id,name,description,status,planned_capacity,performed_capacity,rest_capacity,unit,daily_scrum) VALUES (";
		$t_sql .= db_param( 0 ) . ", ";
		$t_sql .= db_param( 1 ) . ", ";
		$t_sql .= db_param( 2 ) . ", ";
		$t_sql .= db_param( 3 ) . ", ";
		$t_sql .= db_param( 4 ) . ", ";
		$t_sql .= db_param( 5 ) . ", ";
		$t_sql .= db_param( 6 ) . ", ";
		$t_sql .= db_param( 7 ) . ", ";
		$t_sql .= db_param( 8 ) . ", ";
		$t_sql .= db_param( 9 ) . ")" ;
		
		$t_unit = 0;
		if ( !is_null( $this->unit ) ) {
			$t_unit = $this->getUnitId( $this->unit );
		}
		
		$t_params = array( $this->us_id, 
							$this->developer, 
							$this->name, 
							$this->description, 
							$this->status, 
							( int )$this->planned_capacity, 
							0, 
							( int )$this->rest_capacity, 
							$t_unit, 
							0 );
		db_query_bound( $t_sql, $t_params );
		$id = db_insert_id("gadiv_tasks");
		
		$this->setConfirmationStatus( $this->us_id );
		
		$t_sql = "INSERT INTO gadiv_task_log (task_id, user_id, event, date) VALUES ( ";
		$t_sql .= db_param( 0 ) . ", ";
		$t_sql .= db_param( 1 ) . ", ";
		$t_sql .= db_param( 2 ) . ", ";
		$t_sql .= db_param( 3 ) . ") ";
		$t_params = array( $id, $this->user_id, 'created', 
				$this->getDateFormat(date( 'Y' ), date( 'm' ), date( 'd' ), true));
		db_query_bound( $t_sql, $t_params );
		
		$this->id = $id;
		$this->saveDailyPerformance( 1 );
		$this->id = 0;
		
		return $id;
	}
	
	# save / update task information
	function editTask() {
		
		if( $this->id == 0 ) {
			$this->id = $this->newTask();
		}
		
		$mantis_bug_table = db_get_table( 'mantis_bug_table' );
		
		$t_sql = "UPDATE $mantis_bug_table SET ";
		$t_sql .= "last_updated=" . db_param( 0 ) . " ";
		$t_sql .= "WHERE id=" . db_param( 1 );
		$t_params = array( time(), $this->us_id );
		db_query_bound( $t_sql, $t_params );
		
		$performed_capacity = $this->getTotalPerformedCapacity();
		if( !$performed_capacity ) {
			$performed_capacity = 0.00;
		}
		
		$t_unit_id = $this->getUnitId( $this->unit );
		
		if ( !$this->planned_capacity){
			$this->planned_capacity = 0.00;
		}
		
		$t_sql = "UPDATE gadiv_tasks SET ";
		$t_sql .= "us_id=" . db_param( 0 ) . ", ";
		$t_sql .= "developer_id=" . db_param( 1 ) . ", ";
		$t_sql .= "name=" . db_param( 2 ) . ", ";
		$t_sql .= "description=" . db_param( 3 ) . ", ";
		$t_sql .= "status=" . db_param( 4 ) . ", ";
		$t_sql .= "planned_capacity=" . db_param( 5 ) . ", ";
		$t_sql .= "performed_capacity=" . db_param( 6 ) . ", ";
		$t_sql .= "rest_capacity=" . db_param( 7 ) . ", ";
		$t_sql .= "unit=" . db_param( 8 ) . " ";
		$t_sql .= "WHERE id=" . db_param( 9 );
		$t_params = array( $this->us_id, $this->developer, $this->name, $this->description, 
			$this->status, $this->planned_capacity, $performed_capacity, $this->rest_capacity, 
			$t_unit_id, $this->id );
		db_query_bound( $t_sql, $t_params );
		
		return $this->id;
	}
	
	# reset planned capacity of one task
	function resetPlanned( $task_id ) {
		$t_sql = "UPDATE gadiv_tasks SET planned_capacity='0.00', performed_capacity='0.00', " .
			 "rest_capacity='0.00' WHERE id=" . db_param( 0 );
		$t_params = array( $task_id );
		db_query_bound( $t_sql, $t_params );
		
		if (db_is_mssql()) {
			$t_sql = "SELECT top 1 date FROM gadiv_daily_task_performance WHERE task_id=" . db_param( 0 ) .
			         " ORDER BY date DESC";
		} else {
			$t_sql = "SELECT date FROM gadiv_daily_task_performance WHERE task_id=" . db_param( 0 ) .
			         " ORDER BY date DESC LIMIT 0,1";
						}
		$t_params = array( $task_id );
		$task = $this->executeQuery( $t_sql, $t_params );
		
		$t_sql = "UPDATE gadiv_daily_task_performance SET rest='0.00' AND performed='0.00' " .
			 "WHERE task_id=" . db_param( 0 ) . " AND date=" . db_param( 1 );
		$t_params = array( $task_id, $task[0]['date'] );
		db_query_bound( $t_sql, $t_params );
	}
	
	# delete task
	function deleteTask() {
		$t_sql = "DELETE FROM gadiv_tasks WHERE id=" . db_param( 0 );
		$t_params = array( $this->id );
		$t_ergebnis = db_query_bound( $t_sql, $t_params );
		
		$t_sql = "DELETE FROM gadiv_task_log WHERE task_id=" . db_param( 0 );
		$t_params = array( $this->id );
		$t_ergebnis2 = db_query_bound( $t_sql, $t_params );
		
		$t_sql = "DELETE FROM gadiv_daily_task_performance WHERE task_id=" . db_param( 0 );
		$t_params = array( $this->id );
		$t_ergebnis3 = db_query_bound( $t_sql, $t_params );
		
		if( $t_ergebnis == true && $t_ergebnis2 == true && $t_ergebnis3 == true ) {
			return 1;
		} else {
			return 0;
		}
	}
	
	# get sprint information by product backlog name
	function getBacklogSprints( $p_backlog_name ) {
		$t_sql = "SELECT * FROM gadiv_productbacklogs WHERE name=" . db_param( 0 );
		$t_params = array( $p_backlog_name );
		$t_backlog = $this->executeQuery( $t_sql, $t_params );
		$t_sql = "SELECT gs.name AS sname, gs.status AS status FROM gadiv_teams AS gt " .
			 "LEFT JOIN gadiv_sprints AS gs ON gs.team_id=gt.id WHERE gt.pb_id=" . db_param( 0 );
		$t_params = array( $t_backlog[0]['id'] );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get team information by team id
	function getSelectedTeam() {
		$t_sql = "SELECT * FROM gadiv_teams WHERE id=" . db_param( 0 );
		$t_params = array( $this->id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get product owner username
	function getProductOwner( $p_id ) {
		
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		
		$t_sql = "SELECT * FROM gadiv_rel_team_user AS tu LEFT JOIN $t_mantis_user_table AS ut " .
			 "ON tu.user_id=ut.id WHERE role LIKE '%1%' AND team_id=" . db_param( 0 );
		$t_params = array( $p_id );
		$t_name = $this->executeQuery( $t_sql, $t_params );
		return $t_name[0]['username'];
	}
	
	# get scrum master username
	function getScrumMaster( $p_id ) {
		
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		
		$t_sql = "SELECT * FROM gadiv_rel_team_user AS tu LEFT JOIN $t_mantis_user_table AS ut " .
			 "ON tu.user_id=ut.id WHERE role LIKE '%2%' AND team_id=" . db_param( 0 );
		$t_params = array( $p_id );
		$t_name = $this->executeQuery( $t_sql, $t_params );
		return $t_name[0]['username'];
	}
	
	//check the dateformat for only mssqlserver
	function getDateFormat($p_year, $p_month, $p_day, $p_withTime = false) {
		
		if (db_is_mssql()) {
			$t_sql = "SELECT dateformat FROM master..syslanguages WHERE name = @@LANGUAGE";
			$t_result = $this->executeQuery($t_sql);
			
			if ($t_result[0]['dateformat']=='dmy') {
				//german mssqlserver
				$german_date_format = str_pad($p_day, 2 ,'0', STR_PAD_LEFT) . "-" . str_pad($p_month, 2 ,'0', STR_PAD_LEFT) . "-" . $p_year;
				if ($p_withTime) {
					return $german_date_format . " " . date( 'H' ) . ":" .	date( 'i' ) . ":" . date( 's' ) . ".000";
				}
				return $german_date_format;
			}
		
		} 
		//or every other dbs
		$other_date_format = $p_year . "-" . str_pad($p_month, 2 ,'0', STR_PAD_LEFT) . "-" . str_pad($p_day, 2 ,'0', STR_PAD_LEFT);
		if ($p_withTime) {
			return $other_date_format  . " " . date( 'H' ) . ":" .	date( 'i' ) . ":" . date( 's' );
		} 
		return $other_date_format; 
	}
	
	#get the normalized date
	#input     jjjj-mm-dd or jjjj-m-d or jjjj-mm-d or jjjj-m-dd
	#output:   jjjj-mm-dd or dd-mm-jjjj possibel with timestamp
	function getNormalDateFormat($p_date, $p_withTime = false) {
		
		$p_year  = substr($p_date, 0, 4);
		
		$pos = strrpos($p_date, "-");
		
		if ($pos == 7) {
			$p_month = substr($p_date, 5, 2);
			$p_day   = substr($p_date, 8);
			return $this->getDateFormat($p_year, $p_month, $p_day, $p_withTime);
			
		} else {
			#only pos 6
			$p_month = substr($p_date, 5, 1);
			$p_day   = substr($p_date, 7);
			return $this->getDateFormat($p_year, $p_month, $p_day, $p_withTime);
		}
	}
	
	# check if developer has enough capacity or if it is exceeded
	function compareAvailabilityWithCapacity( $p_user_id, $p_year, $p_month, $p_day ) {
		
		$t_date = $this->getDateFormat($p_year, $p_month, $p_day);      // $p_year . "-" . $p_month . "-" . $p_day;
		
		$t_sql = "SELECT capacity FROM gadiv_rel_user_availability WHERE date=" . db_param( 0 ) .
			 " AND user_id=" . db_param( 1 );
		$t_params = array( $t_date, $p_user_id );
		$t_user = $this->executeQuery( $t_sql, $t_params );
		if( empty( $t_user[0]['capacity'] ) ) {
			$t_user[0]['capacity'] = 0;
		}
		
		$t_sql = "SELECT sum(capacity) AS total_capacity FROM gadiv_rel_user_team_capacity " .
			 "WHERE date=" . db_param( 0 ) . " AND user_id=" . db_param( 1 ); //. " GROUP BY user_id";
		$t_params = array( $t_date, $p_user_id );
		$t_total = $this->executeQuery( $t_sql, $t_params );
		if( $t_total[0]['total_capacity'] == NULL ) {
			return true;
		}
		
		if( $t_user[0]['capacity'] < $t_total[0]['total_capacity'] ) {
			return false;
		} else {
			return true;
		}
	}
	
	# get saved availbilities of a user from database
	function getAvailabilityToSavedCapacity( $p_user, $p_date ) {
		
		$db_date = $this->getNormalDateFormat($p_date);
		
		$t_sql = "SELECT capacity FROM gadiv_rel_user_availability WHERE user_id=" . db_param( 0 ) .
			 " AND date=" . db_param( 1 );
		$t_params = array( $p_user, $db_date );
		$t_user = $this->executeQuery( $t_sql, $t_params );
		if( !empty( $t_user[0]['capacity'] ) ) {
			return $t_user[0]['capacity'];
		} else {
			return 0;
		}
	}
	
	# get saved capacities of a user from database
	function getCapacityToSavedAvailability( $p_user, $p_date ) {
				
		$db_date = $this->getNormalDateFormat(substr($p_date, 0, 10));
		
		$t_sql = "SELECT sum(capacity) AS capacity 
					FROM gadiv_rel_user_team_capacity 
					WHERE user_id=" . db_param( 0 ) . " 
					AND date=" . db_param( 1 );
			
		$t_params = array( $p_user, $db_date );
		$t_user = $this->executeQuery( $t_sql, $t_params );
		if( !empty( $t_user[0]['capacity'] ) ) {
			return $t_user[0]['capacity'];
		} else {
			return 0;
		}
	}
	
	# get total performed capacity for one task
	function getTotalPerformedCapacity() {
		$t_sql = "SELECT sum(performed) AS capacity ";
		$t_sql .= "FROM gadiv_daily_task_performance ";
		$t_sql .= "WHERE task_id=" . db_param( 0 ) . " ";
		$t_sql .= "AND rest_flag = 0 ";
		//$t_sql .= "GROUP BY rest_flag";
		$t_params = array( $this->id );
		$t_task = $this->executeQuery( $t_sql, $t_params );
		return $t_task[0]['capacity'];
	}
	
	# check if a team has open or running sprints
	function hasSprints( $p_team_id ) {
		if( $p_team_id > 0 ) {
			$t_sql = "SELECT count(*) AS team FROM gadiv_sprints WHERE status < 2 " . "AND team_id=" .
				 db_param( 0 ); // . " GROUP BY team_id";
			$t_params = array( $p_team_id );
			$t_team = $this->executeQuery( $t_sql, $t_params );
		}
		return 0;
	}
	
	# get user story by id
	function getUserStoryById() {
		$t_mantis_bug_text_table = db_get_table( 'mantis_bug_text_table' );
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		
		$t_sql = "SELECT * 
					FROM $t_mantis_bug_text_table AS btt 
					LEFT JOIN $t_mantis_bug_table AS bt 
						ON btt.id=bt.bug_text_id 
					WHERE bt.id=" . db_param( 0 );
		$t_params = array( $this->us_id );
		$t_result_set = $this->executeQuery( $t_sql, $t_params );
		return $t_result_set;
	}
	
	# update task log
	function updateTaskLog( $id, $user_id, $event ) {
		$t_sql = "SELECT * 
					FROM gadiv_task_log 
					WHERE task_id=" . db_param( 0 ) . " 
					AND event=" . db_param( 1 );
		$t_params = array( $id, $event );
		$t_result = db_query_bound( $t_sql, $t_params );
		if( db_num_rows( $t_result ) == 0 ) {
			$t_sql = "INSERT INTO gadiv_task_log (task_id, user_id, event, date)
						VALUES ( " . db_param( 0 ) . "," . db_param( 1 ) . "," . db_param( 2 ) . "," . db_param( 3 ) . ") ";
			$t_params = array( $id, $user_id, $event, 
				$this->getDateFormat(date( 'Y' ), date( 'm' ), date( 'd' ), true) );
			db_query_bound( $t_sql, $t_params );
		} else {
			$t_sql = "UPDATE gadiv_task_log 
					SET user_id=" . db_param( 0 ) . ", 
					date=" . db_param( 1 ) . " 
					WHERE task_id=" . db_param( 2 ) . " 
					AND event=" . db_param( 3 );
			$t_params = array( $user_id, 
				$this->getDateFormat(date( 'Y' ), date( 'm' ), date( 'd' ), true), $id, $event );
			db_query_bound( $t_sql, $t_params );
		}
	}
	
	# delete task log, optionally delete single event entries
	function deleteTaskLog( $id, $event = "" ) {
		if( $event != "" ) {
			$t_sql = "DELETE FROM gadiv_task_log 
					WHERE task_id=" . db_param( 0 ) . " 
					AND event=" . db_param( 1 );
			$t_params = array( $id, $event );
		} else {
			$t_sql = "DELETE FROM gadiv_task_log WHERE task_id=" . db_param( 0 );
			$t_params = array( $id );
		}
		db_query_bound( $t_sql, $t_params );
	}
	
	# get all task log information
	function getTaskLog( $id ) {
		$t_sql = "SELECT * FROM gadiv_task_log WHERE task_id=" . db_param( 0 );
		$t_params = array( $id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get user story status
	function getUserStoryStatus( $p_bug_id ) {
		return bug_get_field( $p_bug_id, 'status' );
	}
	
	# check if user story is closable and close it
	function closeUserStory( $us_id, $status, $user_id ) {
		$t_sql = "SELECT count(*) AS openedTasks 
					FROM gadiv_tasks 
					WHERE us_id=" . db_param( 0 ) . " 
					AND status < 4" . "
					GROUP BY us_id";
		$t_params = array( $us_id );
		$ot = $this->executeQuery( $t_sql, $t_params );
		
		if( $ot[0]['openedTasks'] == 0 ) {
			$bug = bug_get( $us_id );
			
			bug_set_field( $us_id, 'status', $status );
			
			history_log_event_direct( $us_id, 'status', $bug->status, '80', $user_id, 0 );
		}
		
		bug_set_field( $us_id, 'last_updated', time() );
	}
	
	# move user story into a new or runnning sprint
	function doUserStoryToSprint( $bug_id, $sprint, $sprint_old = "" ) {
		$this->getAdditionalProjectFields();
		
		if( empty( $sprint ) ) {
			$sprint = '';
		}
		
		$this->upsertCustomField( $this->spr, $bug_id, $sprint );
		$this->spr = "";
		if( $sprint != '' ) {
			history_log_event_direct( $bug_id, 'Sprint', $sprint_old, $sprint, 
				auth_get_current_user_id(), $p_type = 0 );
		}
	}
	
	# add business value data to a user story
	function addBusinessValue( $bug_id, $businessValue, $businessValue_old = "" ) {
		$this->getAdditionalProjectFields();
		$this->upsertCustomField( $this->bv, $bug_id, $businessValue );
		$this->bv = "";
		history_log_event_direct( $bug_id, 'Business Value', $businessValue_old, $businessValue, 
			auth_get_current_user_id(), $p_type = 0 );
	}
	
	# add storypoints data to a user story
	function addStoryPoints( $bug_id, $storypoints, $storypoints_old = "" ) {
		$this->getAdditionalProjectFields();
		$this->upsertCustomField( $this->sp, $bug_id, $storypoints );
		$this->sp = "";
		if( $storypoints != '' ) {
			history_log_event_direct( $bug_id, 'Story Points', $storypoints_old, $storypoints, 
				auth_get_current_user_id(), $p_type = 0 );
		}
	}
	
	# add ranking order data to a user story
	function addRankingOrder( $bug_id, $rankingorder, $rankingorder_old = "" ) {
		$this->getAdditionalProjectFields();
		$this->upsertCustomField( $this->ro, $bug_id, $rankingorder );
		$this->ro = "";
		if( $rankingorder != '' ) {
			history_log_event_direct( $bug_id, plugin_lang_get( 'RankingOrder' ), 
				$rankingorder_old, $rankingorder, auth_get_current_user_id(), $p_type = 0 );
		}
	}
	
	# add presentable data to a user story
	function addPresentable( $bug_id, $presentable, $prensentable_old = "" ) {
		$this->getAdditionalProjectFields();
		$this->upsertCustomField( $this->pr, $bug_id, $presentable );
		$this->pr = "";
		history_log_event_direct( $bug_id, plugin_lang_get( 'Presentable' ), $prensentable_old, 
			$presentable, auth_get_current_user_id(), $p_type = 0 );
	}
	
	# mark user story as technical
	function addTechnical( $bug_id, $technical, $technical_old = "" ) {
		$this->getAdditionalProjectFields();
		
		if( $technical === '1' ) {
			$technical = 'Ja';
		} else {
			$technical = '';
		}
		
		$this->upsertCustomField( $this->tech, $bug_id, $technical );
		$this->tech = "";
		history_log_event_direct( $bug_id, plugin_lang_get( 'Technical' ), $technical_old, 
			$technical, auth_get_current_user_id(), $p_type = 0 );
	}
	
	# mark user story in order to appear in the release documentation
	function addInReleaseDocu( $bug_id, $inReleaseDocu, $inReleaseDocu_old = "" ) {
		$this->getAdditionalProjectFields();
		
		if( $inReleaseDocu === '1' ) {
			$inReleaseDocu = 'Ja';
		} else {
			$inReleaseDocu = '';
		}
		
		$this->upsertCustomField( $this->rld, $bug_id, $inReleaseDocu );
		$this->rld = "";
		history_log_event_direct( $bug_id, plugin_lang_get( 'InReleaseDocu' ), $inReleaseDocu_old, 
			$inReleaseDocu, auth_get_current_user_id(), $p_type = 0 );
	}
	
	# calculate planned work for a user story
	function addPlannedWork( $bug_id, $plannedWork, $plannedWork_old = "" ) {
		$this->getAdditionalProjectFields();
		$this->upsertCustomField( $this->pw, $bug_id, $plannedWork );
		$this->pw = "";
		history_log_event_direct( $bug_id, plugin_lang_get( 'PlannedWork' ), $plannedWork_old, 
			$plannedWork, auth_get_current_user_id(), $p_type = 0 );
	}
	
	# set configured user story unit to a user story
	function setUserStoryUnit( $bug_id, $unit ) {
		$this->getAdditionalProjectFields();
		$this->upsertCustomField( $this->un, $bug_id, $unit );
	}
	
	# update custom field value of bug or insert if it does not exist 
	function upsertCustomField( $p_field_id, $p_bug_id, $p_value ) {
		return custom_field_set_value( $p_field_id, $p_bug_id, $p_value );
	}
	
	# transforms a mantis tracker into a user story
	function addUserStory( $bug_id, $backlog, $backlog_old = "" ) {
		$this->getAdditionalProjectFields();
		$this->upsertCustomField( $this->pb, $bug_id, $backlog );
		
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		
		if( $backlog != "" ) {
			$t_sql = "SELECT ut.id AS id 
					FROM gadiv_productbacklogs pb 
					LEFT JOIN $t_mantis_user_table ut ON pb.user_id=ut.id 
					WHERE pb.name=" . db_param( 0 );
			$t_params = array( $backlog );
			$result = $this->executeQuery( $t_sql, $t_params );
			if( $this->hasTasks( $bug_id ) == false ) {
				if( !empty( $result[0]['id'] ) ) {
					bug_set_field( $bug_id, 'handler_id', $result[0]['id'] );
					bug_set_field( $bug_id, 'status', '50' );
				}
			}
		}
		
		$_SESSION['tracker_handler'] = $result[0]['id'];
		$_SESSION['tracker_id'] = $bug_id;
		$_SESSION['backlog'] = $_POST['backlog'];
		$_SESSION['old_product_backlog'] = $_POST['old_product_backlog'];
		
		if( $_POST['backlog'] != $_POST['old_product_backlog'] ) {
			$this->updateTrackerHandler( $bug_id, $result[0]['id'], 
				$this->get_product_backlog_id( $_POST['old_product_backlog'] ) );
		}
		
		$this->pb = "";
		if( $backlog != '' ) {
			history_log_event_direct( $bug_id, 'Product Backlog', $backlog_old, $backlog, 
				auth_get_current_user_id(), $p_type = 0 );
		}
	}
	
	# get product backlog by id
	function get_product_backlog_id( $productbacklog_name ) {
		$t_sql = "SELECT id FROM gadiv_productbacklogs WHERE name LIKE " . db_param( 0 );
		$t_params = array( "%" . $productbacklog_name . "%" );
		$pb = $this->executeQuery( $t_sql, $t_params );
		return $pb[0]['id'];
	}
	
	# update the bug handler when product backlog is added or changed
	function updateTrackerHandler( $bug_id, $handler_id, $productbacklog_id = "" ) {
		$bug = bug_get( $bug_id, true );
		
		if( $bug->status == 50 ) {
			if( $bug->status < 80 ) {
				bug_set_field( $bug_id, 'status', '50' );
			}
			
			bug_set_field( $bug_id, 'handler_id', $handler_id );
			
			if( $handler_id == 0 || empty( $handler_id ) ) {
				
				bug_set_field( $bug_id, 'status', '10' );
				
				if( $this->count_productbacklog_teams( $productbacklog_id ) > 0 ) {
					$team_id = $this->getTeamIdByBacklog( $productbacklog_id );
					$product_owner = $this->getProductOwner( $team_id );
					if( !empty( $product_owner ) ) {
						$handler_id = $this->getUserIdByName( $product_owner );
						
						bug_set_field( $bug_id, 'handler_id', $handler_id );
						bug_set_field( $bug_id, 'status', '50' );
					}
				}
			}
		}
	}
	
	# count the number of teams which are working on a product backlog
	function count_productbacklog_teams( $productbacklog_id ) {
		$t_sql = "SELECT COUNT(*) AS teams FROM gadiv_teams WHERE pb_id=" . db_param( 0 ) . " GROUP BY pb_id";
		$t_params = array( $productbacklog_id );
		$amount = $this->executeQuery( $t_sql, $t_params );
		return $amount[0]['teams'];
	}
	
	# check if a user story has tasks
	function hasTasks( $id ) {
		$t_sql = "SELECT count(*) AS userstories FROM gadiv_tasks WHERE us_id=" . db_param( 0 ) . " GROUP BY us_id";
		$t_params = array( $id );
		$story = $this->executeQuery( $t_sql, $t_params );
		if( $story[0]['userstories'] > 0 ) {
			return true;
		} else {
			return false;
		}
	}
	
	# checks if user has access right administrator
	function is_admin_user( $p_user_id ) {
		return user_is_administrator( $p_user_id );
	}
	
	# check if a user story has tasks left to do
	function hasTasksLeft( $us_id ) {
		$t_sql = "SELECT status FROM gadiv_tasks WHERE us_id=" . db_param( 0 );
		$t_params = array( $us_id );
		$results = $this->executeQuery( $t_sql, $t_params );
		if( !empty( $results ) ) {
			$resolve = "erledigen";
			foreach( $results as $num => $row ) {
				if( $row['status'] < 4 ) {
					$resolve = "";
				}
			}
		} else {
			$resolve = "";
		}
		return $resolve;
	}
	
	# get agileMantis custom field value by user story id and custom field id
	function getCustomFieldValueById( $p_bug_id, $p_field_id ) {
		$field_value = custom_field_get_value( $p_field_id, $p_bug_id );
		return $field_value;
	}
	
	# check if custom field is in a project by name
	function customFieldIsInProject( $p_field_name ) {
		$t_field_id = custom_field_get_id_from_name( $p_field_name );
		$t_result = custom_field_get_project_ids( $t_field_id );
		return !empty( $t_result );
	}
	
	# restores agileMantis custom field value if user tries to enter wrong value
	function restoreCustomFieldValue( $p_bug_id, $p_field_id, $p_value ) {
		custom_field_set_value( $p_field_id, $p_bug_id, $p_value );
	}
	
	# get product backlog id by product backlog name
	function getProductBacklogIDByBugId( $p_bug_id ) {
		if( bug_exists( $p_bug_id ) ) {
			$this->getAdditionalProjectFields();
			$t_value = custom_field_get_value( $this->pb, $p_bug_id );
			
			if( !empty( $t_value ) ) {
				$t_sql = "SELECT * FROM gadiv_productbacklogs WHERE name=" . db_param( 0 );
				$t_params = array( $t_value );
				$pb = $this->executeQuery( $t_sql, $t_params );
				return $pb[0]['id'];
			}
		}
		return null;
	}
	
	# get agileMantis custom field value Sprint
	function getSprintByBugId( $p_bug_id ) {
		if( bug_exists( $p_bug_id ) ) {
			$this->getAdditionalProjectFields();
			$t_value = custom_field_get_value( $this->spr, $p_bug_id );
			
			if( !empty( $t_value ) ) {
				$t_sql = "SELECT id,
									team_id,
									pb_id,
									name,
									description,
									status,
									daily_scrum,
									start,
									dispose as " . AGILEMANTIS_COMMIT_FIELD . ",
									enddate as " . AGILEMANTIS_END_FIELD . ",
									closed,
									unit_storypoints,
									unit_planned_work,
									unit_planned_task,
									workday_length
			 FROM gadiv_sprints WHERE name=" . db_param( 0 );
				return $this->executeQuery( $t_sql, array( $t_value ) );
			}
		}
		return null;
	}
	
	# get agileMantis custom field value Story Points
	function getStoryPoints( $p_bug_id ) {
		return custom_field_get_value( custom_field_get_id_from_name( 'Storypoints' ), $p_bug_id );
	}
	
	# get agileMantis custom field value Product Backlog
	function getProductBacklogByBugId( $p_bug_id ) {
		if( bug_exists( $p_bug_id ) ) {
			$this->getAdditionalProjectFields();
			$t_value = custom_field_get_value( $this->pb, $p_bug_id );
			
			if( !empty( $t_value ) ) {
				$t_sql = "SELECT * FROM gadiv_productbacklogs WHERE name=" . db_param( 0 );
				$t_result = $this->executeQuery( $t_sql, array( $t_value ) );
				if( count( $t_result ) > 0 ) {
					return $t_result[0];
				}
			}
		}
		return null;
	}
	
	# get all sprints which are connected to a product backlog by product backlog id
	function getSprintsByBacklogId( $backlog_id ) {
		$t_sql = "SELECT gs.name AS sname, gs.status AS status, gs.pb_id AS pbid 
				FROM gadiv_teams AS gt LEFT JOIN gadiv_sprints AS gs ON gs.team_id = gt.id 
				WHERE gs.name IS NOT NULL 
				AND status IS NOT NULL 
				AND gt.pb_id=" . db_param( 0 );
		$t_params = array( $backlog_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# check if task belongs to a developer or not
	function isUserTask( $us_id, $developer_id ) {
		$t_sql = "SELECT count(*) AS tasks 
				FROM gadiv_tasks 
				WHERE us_id=" . db_param( 0 ) . " 
				AND developer_id=" . db_param( 1 ) . "
				GROUP BY us_id";
		$t_params = array( $us_id, $developer_id );
		$user = $this->executeQuery( $t_sql, $t_params );
		if( $user[0]['tasks'] >= 1 ) {
			return true;
		}
		return false;
	}
	
	# get the name of a category
	function getCategoryById( $p_category_id ) {
		return category_get_name( $p_category_id );
	}
	
	# get the parent project id, returns 0 when it is a main project
	function getParentProjectId( $project_id ) {
		$t_mantis_project_hierarchy_table = db_get_table( 'mantis_project_hierarchy_table' );
		
		$t_sql = "SELECT parent_id 
				FROM $t_mantis_project_hierarchy_table 
				WHERE child_id=" . db_param( 0 );
		$t_params = array( $project_id );
		$project = $this->executeQuery( $t_sql, $t_params );
		return $project[0]['parent_id'];
	}
	
	# select unit id by unit name
	function getUnitId( $unit ) {
		switch( $unit ) {
			default:
			case 'keine':
				$unit_id = 0;
				break;
			case 'h':
				$unit_id = 1;
				break;
			case 'T':
				$unit_id = 2;
				break;
			case 'SP':
				$unit_id = 3;
				break;
		}
		return $unit_id;
	}
	
	# select unit name by unit id
	function getUnitById( $id ) {
		switch( $id ) {
			default:
			case '0':
				$unit = "";
				break;
			case '1':
				$unit = "h";
				break;
			case '2':
				$unit = "T";
				break;
			case '3':
				$unit = "SP";
				break;
		}
		return $unit;
	}
	
	# collect all splitting information and save to database
	function setSplittingInformation( $us_id, $new_bug_id, $wmu, $spmu ) {
		if( !$this->isSplittedStory( $us_id ) ) {
			$t_sql = "INSERT INTO gadiv_rel_userstory_splitting_table VALUES ( ";
			$t_sql .= db_param( 0 ) . ", ";
			$t_sql .= db_param( 1 ) . ", ";
			$t_sql .= db_param( 2 ) . ", ";
			$t_sql .= db_param( 3 ) . ", ";
			$t_sql .= db_param( 4 ) . ") ";
			$t_params = array( $us_id, $new_bug_id, $wmu, $spmu, $this->getDateFormat(date( 'Y' ), date( 'm' ), date( 'd' ), true) );
			db_query_bound( $t_sql, $t_params );
		}
	}
	
	# check if user story is a splitted one
	function isSplittedStory( $us_id ) {
		$t_sql = "SELECT count(*) AS splitted 
				FROM gadiv_rel_userstory_splitting_table 
				WHERE old_userstory_id=" . db_param( 0 ) . "
				GROUP BY old_userstory_id";
		$t_params = array( $us_id );
		$story = $this->executeQuery( $t_sql, $t_params );
		return $story[0]['splitted'] == 1;
	}
	
	# check if user story is splitted and return splitting information
	function getSplittedStory( $us_id ) {
		$t_sql = "SELECT * 
				FROM gadiv_rel_userstory_splitting_table 
				WHERE old_userstory_id=" . db_param( 0 );
		$t_params = array( $us_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get user story from a specific sprint by sprint name
	function getUserStoriesWithSpecificSprint( $sprint_name = "" ) {
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		$this->getAdditionalProjectFields();
		$t_sql = "SELECT * 
				FROM $t_mantis_custom_field_string_table 
				WHERE field_id=" . db_param( 0 ) . " 
				AND value=" . db_param( 1 );
		$t_params = array( $this->spr, $sprint_name );
		$t_result = $this->executeQuery( $t_sql, $t_params );

		return $t_result;
	}
	
	# get all new sprints
	function getNewSprints() {
		return $this->executeQuery( "SELECT id,
											team_id,
											pb_id,
											name,
											description,
											status,
											daily_scrum,
											start,
											dispose as " . AGILEMANTIS_COMMIT_FIELD . ",
											enddate as " . AGILEMANTIS_END_FIELD . ",
											closed,
											unit_storypoints,
											unit_planned_work,
											unit_planned_task,
											workday_length
		 FROM gadiv_sprints WHERE status = 0" );
	}
	
	# changes the visibilty at the filter options on the view issues page
	function changeCustomFieldFilter( $p_field_name, $p_status ) {
		$t_field_id = custom_field_get_id_from_name( $p_field_name );
		if( $t_field_id ) {
			custom_field_update( $t_field_id, 
				array( 'name' => $p_field_name, 'filter_by' => $p_status ) );
		}
	}
	
	# remove custom field by name
	function removeCustomField( $p_field_name ) {

		$t_field_id = custom_field_get_id_from_name( $p_field_name );
		
		$t_projects = project_get_all_rows();
		
		foreach ( $t_projects as $t_row ) {
			custom_field_unlink( $t_field_id, $t_row['id'] );
		}
		
	}

	function sortUserStories( $sort_by, $direction, $user_stories ) {
		if( empty( $user_stories ) ) {
			return $user_stories;
		}
		
		foreach( $user_stories as $key => $row ) {
			$sort_id[$key] = $row['id'];
			$sort_project_id[$key] = $row['project_id'];
			$sort_summary[$key] = $row['summary'];
			$sort_status[$key] = $row['status'];
			$sort_target_version[$key] = $row['target_version'];
			$sort_b_category_id[$key] = $row['b_category_id'];
			$sort_category_name[$key] = $row['category_name'];
			$sort_c_project_id[$key] = $row['c_project_id'];
			$sort_project_name[$key] = $row['project_name'];
			$sort_productBacklog[$key] = $row['productBacklog'];
			$sort_business_value[$key] = $row['businessValue'];
			$sort_story_points[$key] = $row['storyPoints'];
			$sort_sprint[$key] = $row['sprint'];
			$sort_ranking_order[$key] = $row['rankingOrder'];
			$sort_planned_work[$key] = $row['plannedWork'];
		}
		
		if( $direction == 'DESC' ) {
			$direction = SORT_DESC;
		} else {
			$direction = SORT_ASC;
		}
		
		switch( $sort_by ) {
			case 'plannedWork':
				array_multisort( $sort_planned_work, $direction, $sort_project_name, $direction, 
					$sort_target_version, $direction, $sort_sprint, $direction, $sort_id, $direction, 
					$user_stories );
				break;
			case 'rankingOrder':
				array_multisort( $sort_ranking_order, $direction, $sort_project_name, $direction, 
					$sort_target_version, $direction, $sort_sprint, $direction, $sort_id, $direction, 
					$user_stories );
				break;
			case 'storyPoints':
				array_multisort( $sort_story_points, $direction, $sort_project_name, $direction, 
					$sort_target_version, $direction, $sort_sprint, $direction, $sort_id, $direction, 
					$user_stories );
				break;
			case 'businessValue':
				array_multisort( $sort_business_value, $direction, $sort_project_name, $direction, 
					$sort_target_version, $direction, $sort_sprint, $direction, $sort_id, $direction, 
					$user_stories );
				break;
			case 'sprint':
				array_multisort( $sort_sprint, $direction, $sort_project_name, $direction, 
					$sort_target_version, $direction, $sort_id, $direction, $user_stories );
				break;
			case 'summary':
				array_multisort( $sort_summary, $direction, $sort_project_name, $direction, 
					$sort_target_version, $direction, $sort_sprint, $direction, $sort_id, $direction, 
					$user_stories );
				break;
			case 'version':
				array_multisort( $sort_project_name, $direction, $sort_target_version, $direction, 
					$sort_sprint, $direction, $sort_id, $direction, $user_stories );
				break;
			case 'category':
				array_multisort( $sort_category_name, $direction, $sort_project_name, $direction, 
					$sort_target_version, $direction, $sort_sprint, $direction, $sort_id, $direction, 
					$user_stories );
				break;
			case 'id':
				array_multisort( $sort_id, $direction, $user_stories );
				break;
			default:
				array_multisort( $sort_project_name, $direction, $sort_target_version, $direction, 
					$sort_sprint, $direction, $sort_id, $direction, $user_stories );
				break;
		}
		
		return $user_stories;
	}
}
?>