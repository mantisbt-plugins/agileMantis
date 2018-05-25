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


class gadiv_productBacklog extends gadiv_commonlib {
	var $id;
	var $name;
	var $team;
	var $description;
	var $system;
	var $user_id;
	var $project_id;
	var $project_ids;
	var $email;
	var $sp;
	var $bv;
	var $pb;
	
	# adds a new product backlog
	function newProductBacklog() {
		global $agilemantis_au;
		
		// Check if team-user name fits into MantisBT regulations
		if ( ! ( utf8_strlen( $this->name ) <  22 // field size minus length of prefix  
				&& user_is_name_valid( $this->name ) 
				&& user_is_name_unique( $this->name )  ) ) {
			
			return null;
		}
		
		$p_username = $this->generateTeamUser( $this->name );
		$p_email = $this->email;
		$p_email = trim( $p_email );
		$t_seed = $p_email . $p_username;
		$t_password = auth_generate_random_password( $t_seed );
		
		if( user_is_name_unique( $p_username ) === true ) {
			user_create( $p_username, $t_password, $p_email, 55, false, true, 
				'Team-User-' . $_POST['pbl_name'] );
		} else {
			$t_user_id = $this->getUserIdByName( $p_username );
			user_set_field( $t_user_id, 'email', $p_email );
		}
		
		$user_id = $this->getLatestUser();
		
		$agilemantis_au->setAgileMantisUserRights( $user_id, 1, 0, 0 );
		
		if( $this->team == 0 ) {
			$this->team = $this->getLatestUser();
		}
		$t_sql = "INSERT INTO gadiv_productbacklogs (name, description, user_id) VALUES ( " . 
		         db_param( 0 ) . ", " . db_param( 1 ) . ", " . db_param( 2 ) . ") ";
		$t_params = array( $this->name, $this->description, $user_id );
		db_query_bound( $t_sql, $t_params );
		$this->id = db_insert_id("gadiv_productbacklogs");
		
		$this->user_id = $user_id;
		return $this->id;
	}
	
	# save / update product backlog information
	function editProductBacklog() {
		if( $this->id == 0 ) {
			$this->id = $this->newProductBacklog();
			if ( is_null($this->id ) ) {
				return false;
			}
		}
		
		$t_sql = "UPDATE gadiv_productbacklogs 
					SET name=" . db_param( 0 ) . ", 
					description=" . db_param( 1 ) . ", 
					user_id=" . db_param( 2 ) . " 
					WHERE id=" . db_param( 3 );
		$t_params = array( $this->name, $this->description, $this->user_id, (( int ) $this->id) );
		db_query_bound( $t_sql, $t_params );
		
		$result = $this->executeQuery( "SELECT * FROM gadiv_productbacklogs ORDER BY name ASC" );
		if( !empty( $result ) ) {
			foreach( $result as $num => $row ) {
				$pbs .= $row['name'] . '|';
			}
		}
		$pbs = substr( $pbs, 0, -1 );
		
		$this->getAdditionalProjectFields();
		custom_field_update( $this->pb, 
			array( 'name' => 'ProductBacklog', 'possible_values' => $pbs ) );
		
		return true;
	}

	/**
		 * Updates the custom field strings of all user stories that belong to
		 * a certain product backlog.
		 *
		 * @param String $pb_name_old	Old name of the product backlog
		 * @param String $pb_name_new	New name of the product backlog
		 */
	function updatePBCustomFieldStrings( $pb_name_old, $pb_name_new ) {
		if( empty( $pb_name_old ) || empty( $pb_name_new ) || ($pb_name_old == $pb_name_new) ) {
			return;
		}
		
		$this->getAdditionalProjectFields();
		
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		
		$t_sql = "UPDATE $t_mantis_custom_field_string_table ";
		$t_sql .= "SET value=" . db_param( 0 ) . " ";
		$t_sql .= "WHERE field_id=" . db_param( 1 ) . " AND value=" . db_param( 2 );
		$t_params = array( $pb_name_new, $this->pb, $pb_name_old );
		
		db_query_bound( $t_sql, $t_params );
	}
	
	# get all projects from a product backlog
	function getBacklogProjects( $backlog_id ) {
		$t_mantis_project_table = db_get_table( 'mantis_project_table' );
		
		$t_sql = "SELECT * 
					FROM gadiv_rel_productbacklog_projects AS rpp 
					LEFT JOIN $t_mantis_project_table AS mpt ON rpp.project_id = mpt.id 
					WHERE pb_id=" . db_param( 0 ) . " 
					ORDER BY mpt.name ASC";
		$t_params = array( $backlog_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	#  delete and insert projects according to its product backlog
	function editProjects( $backlog_id, $project_id ) {
		$t_sql = "DELETE FROM gadiv_rel_productbacklog_projects 
					WHERE pb_id=" . db_param( 0 ) . " 
					AND project_id=" . db_param( 1 );
		$t_params = array( $backlog_id, $project_id );
		db_query_bound( $t_sql, $t_params );
		
		$t_sql = "INSERT INTO gadiv_rel_productbacklog_projects 
					VALUES (" . db_param( 0 ) . "," . db_param( 1 ) . ")";
		$t_params = array( $backlog_id, $project_id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# checks if product backlog name is unique or not
	function isNameUnique() {
		$t_sql = "SELECT count(*) AS uniqueName 
					FROM gadiv_productbacklogs 
					WHERE name=" . db_param( 0 ) . "
					GROUP BY name";
		$t_params = array( $this->name );
		$uniqueName = $this->executeQuery( $t_sql, $t_params );
		if( $uniqueName[0]['uniqueName'] > 0 ) {
			return false;
		} else {
			return true;
		}
	}
	
	# delete product backlog information
	function deleteProductBacklog() {
		$t_sql = "DELETE FROM gadiv_productbacklogs WHERE id=" . db_param( 0 );
		$t_params = array( $this->id );
		db_query_bound( $t_sql, $t_params );
		
		$t_sql = "DELETE FROM gadiv_rel_productbacklog_projects WHERE pb_id=" . db_param( 0 );
		$t_params = array( $this->id );
		db_query_bound( $t_sql, $t_params );
		
		$result = $this->executeQuery( "SELECT * FROM gadiv_productbacklogs ORDER BY name ASC" );
		
		foreach( $result as $num => $row ) {
			$pbs .= $row['name'] . '|';
		}
		
		$pbs = substr( $pbs, 0, -1 );
		
		$this->getAdditionalProjectFields();
		custom_field_update( $this->pb, 
			array( 'name' => 'ProductBacklog', 'possible_values' => $pbs ) );
	}
	
	# check if product backlog has still user stories in it
	function productBacklogHasStoriesLeft( $name ) {
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		
		$this->getAdditionalProjectFields();
		$t_sql = "SELECT * 
					FROM $t_mantis_custom_field_string_table AS ufst 
					LEFT JOIN $t_mantis_bug_table AS bt ON bt.id=ufst.bug_id 
					WHERE field_id=" . db_param( 0 ) . " 
					AND value=" . db_param( 1 );
		$t_params = array( $this->pb, $name );
		$result = $this->executeQuery( $t_sql, $t_params );
		foreach( $result as $num => $row ) {
			if( $row['status'] < 90 ) {
				return false;
			}
		}
		return true;
	}
	
	# check amount of teams working with the selected product backlog
	function checkProductBacklogTeam( $id ) {
		$t_sql = "SELECT count(*) AS pbTeams FROM gadiv_teams WHERE pb_id=" . db_param( 0 ) . " GROUP BY pb_id";
		$t_params = array( (( int ) $id) );
		$pbTeams = $this->executeQuery( $t_sql, $t_params );
		if( $pbTeams[0]['pbTeams'] > 0 ) {
			return true;
		}
		return false;
	}
	
	# get all user stories which have not been finished yet
	function getAllUndoneUserStories( $product_backlog ) {
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		$t_mantis_project_table = db_get_table( 'mantis_project_table' );
		
		$this->getAdditionalProjectFields();
		
		$t_params = array( $this->pb, $product_backlog );
		
		$t_sql = "SELECT
				a.id AS id, a.summary AS summary, a.status AS status, a.target_version AS version,
				b.value AS productBacklog, c.name AS projectname
				FROM $t_mantis_bug_table AS a
				LEFT JOIN $t_mantis_custom_field_string_table AS b ON a.id = b.bug_id
				LEFT JOIN $t_mantis_project_table AS c ON c.id = a.project_id
				WHERE a.status=50
				AND b.field_id=" . db_param( 0 ) . "
				AND b.value=" . db_param( 1 ) . "
				AND b.value!=''";
		
		$bug_list = $this->executeQuery( $t_sql, $t_params );
		
		if( !$bug_list || sizeof( $bug_list ) == 0 ) {
			return array();
		}
		
		foreach( $bug_list as $row ) {
			$sprint = $this->getCustomFieldValueById( $row['id'], $this->spr );
			if( !empty( $sprint ) ) {
				continue;
			}
			
			$row['businessValue'] = $this->getCustomFieldValueById( $row['id'], $this->bv );
			
			$row['storyPoints'] = $this->getCustomFieldValueById( $row['id'], $this->sp );
			
			if( config_get( 'plugin_agileMantis_gadiv_ranking_order' ) == '1' ) {
				$row['rankingOrder'] = $this->getCustomFieldValueById( $row['id'], $this->ro );
			}
			
			if( config_get( 'plugin_agileMantis_gadiv_ranking_order' ) == '1' ) {
				$row['plannedWork'] = $this->getCustomFieldValueById( $row['id'], $this->pw );
			}
			
			$user_stories[] = $row;
		}
		
		$sort_by = config_get( 'current_user_assume_userstories_filter', null, 
			auth_get_current_user_id() );
		if( !empty( $_GET['sort_by'] ) && isset( $_GET['sort_by'] ) ) {
			config_set( 'current_user_assume_userstories_filter', $_GET['sort_by'], 
				auth_get_current_user_id() );
			$sort_by = $_GET['sort_by'];
		}
		
		$direction = config_get( 'current_user_assume_userstories_filter_direction', null, 
			auth_get_current_user_id() );
		if( !empty( $_GET['direction'] ) && isset( $_GET['direction'] ) ) {
			config_set( 'current_user_assume_userstories_filter_direction', $_GET['direction'], 
				auth_get_current_user_id() );
			$direction = $_GET['direction'];
		}
		
		return $this->sortUserStories( $sort_by, $direction, $user_stories );
	}
	
	# get value from agileMantis custom field Sprint
	function getSprintValue( $p_bug_id ) {
		return custom_field_get_value( $this->spr, $p_bug_id );
	}
	
	# check if product backlog is locked by running sprints
	function productBacklogHasRunningSprint( $product_backlog ) {
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
	 FROM gadiv_sprints WHERE pb_id=" . db_param( 0 ) . " AND status=1";
		$t_params = array( $product_backlog );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# check which user stories are in closed sprints
	function userStoriesInClosedSprints( $bug_id ) {
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		
		$this->getAdditionalProjectFields();
		$t_sql = "SELECT field_id, bug_id, value, id,
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
					FROM $t_mantis_custom_field_string_table 
					LEFT JOIN gadiv_sprints ON value LIKE name 
					WHERE field_id=" . db_param( 0 ) . " 
					AND value != '' 
					AND id IS NOT NULL 
					AND bug_id=" . db_param( 1 ) . " 
					AND status='2'";
		$t_params = array( $this->spr, $bug_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# check which user stories are in running sprints
	function userStoriesInRunningSprints( $bug_id ) {
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		$this->getAdditionalProjectFields();
		$t_sql = "SELECT field_id, bug_id, value, id,
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
					FROM $t_mantis_custom_field_string_table 
					LEFT JOIN gadiv_sprints ON value LIKE name 
					WHERE field_id=" . db_param( 0 ) . " 
					AND value != '' 
					AND id IS NOT NULL 
					AND bug_id=" . db_param( 1 ) . " 
					AND status > 0";
		$t_params = array( $this->spr, $bug_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get all user stories by a project
	function getUserStoriesByProject( $project_id, $product_backlog, $status = 0 ) {
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		$this->getAdditionalProjectFields();
		
		$t_sql = "SELECT * FROM $t_mantis_custom_field_string_table 
					LEFT JOIN $t_mantis_bug_table 
					ON id = bug_id WHERE field_id=" . db_param( 0 ) . " 
					AND value=" . db_param( 1 ) . " 
					AND project_id=" . db_param( 2 );
		$t_params = array( $this->pb, $product_backlog, $project_id );
		if( $status > 0 ) {
			$t_sql .= " AND status<" . db_param( 3 );
			$t_params[] = $status;
		}
		
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get one page of user stories by product backlog name and page number
	function getUserStoriesByProductBacklogNameAndPageNumber( $product_backlog, $page_number ) {
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		$t_mantis_project_table = db_get_table( 'mantis_project_table' );
		$t_mantis_category_table = db_get_table( 'mantis_category_table' );
		
		$this->getAdditionalProjectFields();
		
		$t_sql = "SELECT
				a.id AS id, a.project_id AS project_id, a.summary AS summary, a.status AS status,
				a.target_version AS target_version, b.id AS b_category_id, b.name AS category_name,
				c.id AS c_project_id, c.name AS project_name, d.value AS productBacklog
			FROM $t_mantis_bug_table a
			LEFT JOIN $t_mantis_category_table b ON a.category_id = b.id
			LEFT JOIN $t_mantis_project_table c ON a.project_id = c.id
			LEFT JOIN $t_mantis_custom_field_string_table d ON a.id = d.bug_id
			WHERE d.field_id=" . db_param( 0 ) . " AND d.value=" . db_param( 1 );
		
		$t_params = array( $this->pb, $product_backlog );
		
		$show_resolved_userstories = $this->getConfigValue( 'show_resolved_userstories' );
		$show_closed_userstories = $this->getConfigValue( 'show_closed_userstories' );
		if( $show_resolved_userstories == 1 && $show_closed_userstories == 0 ) {
			$t_sql .= " AND a.status <= 80";
		}
		
		if( $show_closed_userstories == 1 && $show_resolved_userstories == 0 ) {
			$t_sql .= " AND a.status != 80";
		}
		
		if( $show_closed_userstories == 1 && $show_resolved_userstories == 1 ) {
			$t_sql .= " AND a.status <= 90";
		}
		
		if( $show_closed_userstories == 0 && $show_resolved_userstories == 0 ) {
			$t_sql .= " AND a.status < 80";
		}
		
		$show_only_project_userstories = $this->getConfigValue( 'show_only_project_userstories' );
		if( $show_only_project_userstories == 1 && helper_get_current_project() > 0 ) {
			$t_sql .= " AND a.project_id=" . db_param( sizeof( $t_params ) );
			$t_params[] = helper_get_current_project();
		}
		
		$t_sql .= $orderby;
		
		$bug_list = $this->executeQuery( $t_sql, $t_params );
		
		if( !$bug_list || sizeof( $bug_list ) == 0 ) {
			return array();
		}
		
		foreach( $bug_list as $row ) {
			$row['businessValue'] = $this->getCustomFieldValueById( $row['id'], $this->bv );
			
			$row['storyPoints'] = $this->getCustomFieldValueById( $row['id'], $this->sp );
			if( config_get( 'show_only_us_without_storypoints', 0, auth_get_current_user_id() ) ==
				 1 && $row['storyPoints'] <> "" ) {
				continue;
			}
			
			$row['sprint'] = $this->getCustomFieldValueById( $row['id'], $this->spr );
			if( config_get( 'show_only_userstories_without_sprint', 0, 
				auth_get_current_user_id() ) == 1 && !empty( $row['sprint'] ) ) {
				continue;
			}
			
			if( config_get( 'plugin_agileMantis_gadiv_ranking_order' ) == '1' ) {
				$row['rankingOrder'] = $this->getCustomFieldValueById( $row['id'], $this->ro );
			}
			
			if( config_get( 'plugin_agileMantis_gadiv_tracker_planned_costs' ) == '1' ) {
				$row['plannedWork'] = $this->getCustomFieldValueById( $row['id'], $this->pw );
			}
			
			$user_stories[] = $row;
		}
		
		
		
		$sort_by = config_get( 'current_user_product_backlog_filter', null, 
			auth_get_current_user_id() );
		if( !empty( $_GET['sort_by'] ) && isset( $_GET['sort_by'] ) ) {
			config_set( 'current_user_product_backlog_filter', $_GET['sort_by'], 
				auth_get_current_user_id() );
			$sort_by = $_GET['sort_by'];
		}
		
		$direction = config_get( 'current_user_product_backlog_filter_direction', null, 
			auth_get_current_user_id() );
		if( !empty( $_GET['direction'] ) && isset( $_GET['direction'] ) ) {
			config_set( 'current_user_product_backlog_filter_direction', $_GET['direction'], 
				auth_get_current_user_id() );
			$direction = $_GET['direction'];
		}
		$user_stories = $this->sortUserStories( $sort_by, $direction, $user_stories );
		
		
		$t_filter = current_user_get_bug_filter();
		$t_filter = filter_ensure_valid_filter( $t_filter );
		if($t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] > 0 && $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] < count($user_stories)){
			$pagesize = $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE];
		} else {
			$pagesize = 50;
		}

		$size = (int) $pagesize;
		for($i = ($page_number-1)*$size; $i < (($page_number-1)*$size)+$size; $i++ ){
			if(isset($user_stories[$i]) && !empty($user_stories[$i])){
				$page_of_stories[] = $user_stories[$i];
			}
		}
		
		
		return $page_of_stories;
	}
	
	
	# get all user stories by product backlog name
	function getUserStoriesByProductBacklogName( $product_backlog ) {
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		$t_mantis_project_table = db_get_table( 'mantis_project_table' );
		$t_mantis_category_table = db_get_table( 'mantis_category_table' );
		
		$this->getAdditionalProjectFields();
		
		$t_sql = "SELECT
				a.id AS id, a.project_id AS project_id, a.summary AS summary, a.status AS status,
				a.target_version AS target_version, b.id AS b_category_id, b.name AS category_name,
				c.id AS c_project_id, c.name AS project_name, d.value AS productBacklog
			FROM $t_mantis_bug_table a
			LEFT JOIN $t_mantis_category_table b ON a.category_id = b.id
			LEFT JOIN $t_mantis_project_table c ON a.project_id = c.id
			LEFT JOIN $t_mantis_custom_field_string_table d ON a.id = d.bug_id
			WHERE d.field_id=" . db_param( 0 ) . " AND d.value=" . db_param( 1 );
		
		$t_params = array( $this->pb, $product_backlog );
		
		$show_resolved_userstories = $this->getConfigValue( 'show_resolved_userstories' );
		$show_closed_userstories = $this->getConfigValue( 'show_closed_userstories' );
		if( $show_resolved_userstories == 1 && $show_closed_userstories == 0 ) {
			$t_sql .= " AND a.status <= 80";
		}
		
		if( $show_closed_userstories == 1 && $show_resolved_userstories == 0 ) {
			$t_sql .= " AND a.status != 80";
		}
		
		if( $show_closed_userstories == 1 && $show_resolved_userstories == 1 ) {
			$t_sql .= " AND a.status <= 90";
		}
		
		if( $show_closed_userstories == 0 && $show_resolved_userstories == 0 ) {
			$t_sql .= " AND a.status < 80";
		}
		
		$show_only_project_userstories = $this->getConfigValue( 'show_only_project_userstories' );
		if( $show_only_project_userstories == 1 && helper_get_current_project() > 0 ) {
			$t_sql .= " AND a.project_id=" . db_param( sizeof( $t_params ) );
			$t_params[] = helper_get_current_project();
		}
		
		$t_sql .= $orderby;
		
		$bug_list = $this->executeQuery( $t_sql, $t_params );
		
		if( !$bug_list || sizeof( $bug_list ) == 0 ) {
			return array();
		}
		
		foreach( $bug_list as $row ) {
			$row['businessValue'] = $this->getCustomFieldValueById( $row['id'], $this->bv );
			
			$row['storyPoints'] = $this->getCustomFieldValueById( $row['id'], $this->sp );
			if( config_get( 'show_only_us_without_storypoints', 0, auth_get_current_user_id() ) ==
				 1 && $row['storyPoints'] <> "" ) {
				continue;
			}
			
			$row['sprint'] = $this->getCustomFieldValueById( $row['id'], $this->spr );
			if( config_get( 'show_only_userstories_without_sprint', 0, 
				auth_get_current_user_id() ) == 1 && !empty( $row['sprint'] ) ) {
				continue;
			}
			
			if( config_get( 'plugin_agileMantis_gadiv_ranking_order' ) == '1' ) {
				$row['rankingOrder'] = $this->getCustomFieldValueById( $row['id'], $this->ro );
			}
			
			if( config_get( 'plugin_agileMantis_gadiv_tracker_planned_costs' ) == '1' ) {
				$row['plannedWork'] = $this->getCustomFieldValueById( $row['id'], $this->pw );
			}
			
			$user_stories[] = $row;
		}
		
		$sort_by = config_get( 'current_user_product_backlog_filter', null, 
			auth_get_current_user_id() );
		if( !empty( $_GET['sort_by'] ) && isset( $_GET['sort_by'] ) ) {
			config_set( 'current_user_product_backlog_filter', $_GET['sort_by'], 
				auth_get_current_user_id() );
			$sort_by = $_GET['sort_by'];
		}
		
		$direction = config_get( 'current_user_product_backlog_filter_direction', null, 
			auth_get_current_user_id() );
		if( !empty( $_GET['direction'] ) && isset( $_GET['direction'] ) ) {
			config_set( 'current_user_product_backlog_filter_direction', $_GET['direction'], 
				auth_get_current_user_id() );
			$direction = $_GET['direction'];
		}
		
		return $this->sortUserStories( $sort_by, $direction, $user_stories );
	}
	
	# set all agileMantis custom field values
	function setCustomFieldValues( $bug_id ) {
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		
		$t_sql = "SELECT * FROM $t_mantis_bug_table WHERE id=" . db_param( 0 );
		$t_params = array( $bug_id );
		$bug = $this->executeQuery( $t_sql, $t_params );
		
		$story = $this->checkForUserStory( $bug_id );
		
		if( $bug[0]['status'] < 80 ) {
			$this->addUserStory( $bug_id, $_POST['backlog'], $story['name'] );
		}
		
		if( $bug[0]['status'] < 80 ) {
			$this->addStoryPoints( $bug_id, str_replace( ',', '.', $_POST['storypoints'] ), 
				str_replace( ',', '.', $story['storypoints'] ) );
		}
		
		$t_business_value = '';
		if( isset( $_POST['businessValue'] ) ) {
			$t_business_value = $_POST['businessValue'];
		}
		$this->addBusinessValue( $bug_id, $t_business_value, $story['businessValue'] );
		
		$t_ranking_order = '';
		if( isset( $_POST['rankingorder'] ) ) {
			$t_ranking_order = $_POST['rankingorder'];
		}
		$this->addRankingOrder( $bug_id, $t_ranking_order, $story['rankingorder'] );
		
		$t_technical = '2';
		if( isset( $_POST['technical'] ) ) {
			$t_technical = $_POST['technical'];
		}
		
		$this->addTechnical( $bug_id, $t_technical, $story['technical'] );
		
		$t_presentable = '3';
		if( isset( $_POST['presentable'] ) ) {
			$t_presentable = $_POST['presentable'];
		}
		$this->addPresentable( $bug_id, $t_presentable, $story['presentable'] );
		
		$t_in_release_doku = '2';
		if( isset( $_POST['inReleaseDocu'] ) ) {
			$t_in_release_doku = $_POST['inReleaseDocu'];
		}
		
		$this->AddInReleaseDocu( $bug_id, $t_in_release_doku, $story['inReleaseDocu'] );
		
		if( $bug[0]['status'] < 80 ) {
			$this->doUserStoryToSprint( $bug_id, $_POST['sprint'], $story['sprint'] );
		}
		
		$_POST['plannedWork'] = str_replace( ',', '.', $_POST['plannedWork'] );
		if( is_numeric( $_POST['plannedWork'] ) || empty( $_POST['plannedWork'] ) ) {
			
			$t_planned_work = '';
			if( isset( $_POST['plannedWork'] ) ) {
				$t_planned_work = $_POST['plannedWork'];
			}
			
			$this->AddPlannedWork( $bug_id, sprintf( "%.2f", $t_planned_work ), 
				$story['plannedWork'] );
		}
	}
	
	# get the latest mantis user 
	function getLatestUser() {
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		$team = $this->executeQuery( "SELECT max(id) AS id FROM $t_mantis_user_table" );
		return $team[0]['id'];
	}

	function getTeamUserId( $pb_id ) {
		$t_sql = "SELECT user_id
					FROM gadiv_productbacklogs
					WHERE id=" . db_param( 0 );
		$t_params = array( $pb_id );
		$result = $this->executeQuery( $t_sql, $t_params );
		
		if( array_key_exists( 'user_id', $result[0] ) ) {
			return $result[0]['user_id'];
		} else {
			return -1;
		}
	}

	function giveDeveloperRightsToTeamUser( $user_id_team_user, $project_id ) {
		$t_mantis_project_user_list_table = db_get_table( 'mantis_project_user_list_table' );
		
		$t_sql = "SELECT user_id
					FROM $t_mantis_project_user_list_table
					WHERE project_id=" . db_param( 0 ) . " 
					AND user_id=" . db_param( 1 ) . " 
					AND access_level=55";
		$t_params = array( $project_id, $user_id_team_user );
		$result = $this->executeQuery( $t_sql, $t_params );
		if( empty( $result ) ) {
			$t_sql = "INSERT INTO $t_mantis_project_user_list_table
						(project_id, user_id, access_level)
						VALUES (" . db_param( 0 ) . ", " . db_param( 1 ) . ", 55)";
			$t_params = array( $project_id, $user_id_team_user );
			db_query_bound( $t_sql, $t_params );
		}
	}
	
	# calculates all fibonacci numbers according to the amount of story points
	function getFibonacciNumbers( $storypoints ) {
		$a = 0;
		$b = 0;
		$c = '';
		$end = plugin_config_get( 'gadiv_fibonacci_length' );
		for( $i = 0; $i < $end; $i++ ) {
			$sum = $a + $b;
			if( $storypoints != '' ) {
				if( $storypoints == $sum && $storypoints >= 0 ) {
					$selected = 'selected';
					$c = $sum;
				} else {
					$selected = '';
					$additional_storypoints = true;
				}
			}
			$a = $b;
			$b = $sum;
			if( $a == 0 ) {
				$a = 1;
			}
			echo '<option value="' . $sum . '" ' . $selected . '>' . $sum . '</option>';
		}
		
		if( $additional_storypoints = true && $c == '' && $storypoints != '' ) {
			echo '<option value="' . $storypoints . '" selected>' . $storypoints . '</option>';
		}
	}

	function getProductBacklogNameById( $pbId ) {
		$t_sql = "SELECT name FROM gadiv_productbacklogs WHERE id=" . db_param( 0 );
		$t_params = array( $pbId );
		$name = $this->executeQuery( $t_sql, $t_params );
		
		if( array_key_exists( 'name', $name[0] ) ) {
			return $name[0]['name'];
		} else {
			return '';
		}
	}
	
	function getUserIdOfPoByPbId($pb_id) {
		$ROLE_PRODUCT_OWNER = 1;
		$t_sql = "SELECT user_id 
					FROM gadiv_rel_team_user tu
					JOIN gadiv_teams t ON t.id = tu.team_id
					WHERE t.pb_id = " . db_param() . "
					AND tu.role = " . db_param();
		
		$t_params = array( $pb_id, $ROLE_PRODUCT_OWNER );
		$t_result = $this->executeQuery( $t_sql, $t_params );
		return $t_result[0]['user_id'];
	}
}
?>