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




#	This class will hold functions for agileMantis sprint management
class gadiv_sprint extends gadiv_commonlib {
	var $sprint_id;
	var $pb_id;
	
	# get all sprints with sorting functions
	function getSprints( $chron = "", $show_closed_sprints = "" ) {

		if (db_is_mssql()) {
			$endedatum = '[end]';
		} else {
			$endedatum = 'end';
		}
		
		
		$addsql = "";
		$orderby = "";
		$startjoin = "";
		$removeStatus = ' WHERE status != 2';
		$klickStatus = $_GET['klickStatus'];
		$disable_click = $_POST['disable_click'];
		$clicked = $klickStatus == 1 && $disable_click != 1;
		
		if( isset( $_POST['show_all_sprints'] ) || $clicked || $show_closed_sprints == 1 ) {
			if( $_POST['show_all_sprints'] == 1 || $clicked || $show_closed_sprints == 1 ) {
				$removeStatus = '';
			} else {
				$removeStatus = ' WHERE status != 2';
			}
		}
		if( isset( $_GET['sort_by'] ) ) {
			if( $_GET['sort_by'] ) {
				switch( $_GET['sort_by'] ) {
					case 'id':
						if( $_SESSION['order_id'] == 0 ) {
							$orderby = " ORDER BY sname ASC";
							$_SESSION['order_id'] = 1;
						} else {
							$orderby = " ORDER BY sname DESC";
							$_SESSION['order_id'] = 0;
						}
						$_SESSION['order_rest'] = 0;
						$_SESSION['order_team'] = 0;
						$_SESSION['order_start'] = 0;
						$_SESSION['order_end'] = 0;
						break;
					case 'team':
						if( $_SESSION['order_team'] == 0 ) {
							$orderby = " ORDER BY t.name ASC";
							$_SESSION['order_team'] = 1;
						} else {
							$orderby = " ORDER BY t.name DESC";
							$_SESSION['order_team'] = 0;
						}
						$_SESSION['order_rest'] = 0;
						$_SESSION['order_id'] = 0;
						$_SESSION['order_start'] = 1;
						$_SESSION['order_end'] = 0;
						break;
					case 'rest':
						if( $_SESSION['order_rest'] == 0 && $klickStatus == 0 ) {
							$orderby = " ORDER BY  restaufwand  ASC";
						}
						if( $_SESSION['order_rest'] == 0 ) {
							$orderby = " ORDER BY  restaufwand  DESC";
							$_SESSION['order_rest'] = 1;
							$_SESSION['order_end'] = 1;
							$_SESSION['order_id'] = 0;
						} else {
							$orderby = " ORDER BY  restaufwand  ASC";
							$_SESSION['order_rest'] = 0;
							$_SESSION['order_end'] = 0;
							$_SESSION['order_id'] = 1;
						}
// 						$diff_now_end = "CEIL((UNIX_TIMESTAMP(end) - " . time() . ") / 86400)";
// 						$diff_start_end = "CEIL((UNIX_TIMESTAMP(end) - UNIX_TIMESTAMP(start)) / 86400)";
						
// 						$addsql = ",
// 							IF(($diff_now_end) < ($diff_start_end),
// 								($diff_now_end),
// 								($diff_start_end)) AS restaufwand";

						if (db_is_mssql()) {
							$addsql = ", (CASE WHEN (DATEDIFF(day,GETDATE(),enddate) > DATEDIFF(day,start,enddate)) THEN DATEDIFF(day,start,enddate) ELSE  DATEDIFF(day,GETDATE(), enddate) END) as restaufwand";
						} else {
							$addsql = ", if (DATEDIFF(end,curdate())>DATEDIFF(end,start), DATEDIFF(end,start),  DATEDIFF(end,curdate()))";  
						}
						
						$_SESSION['order_team'] = 0;
						$_SESSION['order_start'] = 0;
						break;
					case 'start':
						if( $_SESSION['order_start'] == 0 ) {
							$orderby = " ORDER BY start ASC";
							$_SESSION['order_start'] = 1;
						} else {
							$orderby = " ORDER BY start DESC";
							$_SESSION['order_start'] = 0;
						}
						$_SESSION['order_rest'] = 0;
						$_SESSION['order_id'] = 0;
						$_SESSION['order_team'] = 0;
						$_SESSION['order_end'] = 0;
						break;
					case 'pb':
						if( $_SESSION['oder_pb'] == 0 ) {
							$orderby = " ORDER BY pname ASC";
							$_SESSION['oder_pb'] = 1;
						} else {
							$orderby = " ORDER BY pname DESC";
							$_SESSION['oder_pb'] = 0;
						}
						break;
					case 'end':
					default:
						if( $_SESSION['order_end'] == 0 ) {
							$orderby = " ORDER BY " . $endedatum . " DESC";
							$_SESSION['order_end'] = 1;
							$_SESSION['order_rest'] = 1;
						} else {
							$orderby = " ORDER BY " . $endedatum . " ASC";
							$_SESSION['order_end'] = 0;
							$_SESSION['order_rest'] = 0;
						}
						$_SESSION['order_id'] = 0;
						$_SESSION['order_team'] = 0;
						$_SESSION['order_start'] = 0;
						break;
				}
			}
		} else {
			$startjoin = "";
			$orderby = " ORDER BY " . $endedatum . " DESC, t.name ASC";
			$_SESSION['order'] = 0;
		}
		
		if( $chron != "" ) {
			$orderby = " ORDER BY " . $endedatum . " DESC";
		}
		
		$t_sql = "SELECT s.id AS sid, s.name AS sname, team_id, s.status,
				         s.enddate as " . $endedatum . ", s.start, p.name AS pname " . $addsql . " 
								FROM gadiv_sprints AS s
				LEFT JOIN gadiv_teams AS t ON t.id = s.team_id
				LEFT JOIN gadiv_productbacklogs AS p ON p.id = t.pb_id  " . $removeStatus . " " . $orderby;

		return $this->executeQuery( $t_sql );
	}
	
	# get team information by id
	function getTeamById( $id ) {
		$t_sql = "SELECT * FROM gadiv_teams WHERE id=" . db_param( 0 );
		$t_params = array( $id );
		$team = $this->executeQuery( $t_sql, $t_params );
		if( !empty( $team ) ) {
			return $team[0]['name'];
		}
	}
	
	# get all stories which are in a sprint 
	function getSprintStories( $name, $show_only_open_userstories = false ) {
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		$t_mantis_project_table = db_get_table( 'mantis_project_table' );
		
		$this->getAdditionalProjectFields();
		
		if( $show_only_open_userstories ) {
			$addsql = ' AND bt.status < 80';
		}
		
		$request = array_merge( $_GET, $_POST );
		
		$t_sql = "SELECT sprint.*, bt.*, pt.id AS pid, pt.name AS name 
					FROM $t_mantis_custom_field_string_table AS sprint 
					LEFT JOIN $t_mantis_bug_table AS bt ON sprint.bug_id = bt.id
					LEFT JOIN $t_mantis_project_table AS pt ON bt.project_id = pt.id 
					WHERE sprint.field_id=" . db_param( 0 ) . " 
					AND sprint.value=" . db_param( 1 ) . " " . $addsql;
		$t_params = array( $this->spr, $name );
		
		$bug_list = $this->executeQuery( $t_sql, $t_params );
		
		if( !$bug_list || sizeof( $bug_list ) == 0 ) {
			return array();
		}
		
		foreach( $bug_list as $row ) {
			$row['storyPoints'] = $this->getCustomFieldValueById( $row['id'], $this->sp );
			$row['rankingOrder'] = $this->getCustomFieldValueById( $row['id'], $this->ro );
			$user_stories[] = $row;
		}
		
		$sort_by = config_get( 'current_user_sprint_backlog_filter', null, auth_get_current_user_id() );
		if( !empty( $_GET['sort_by'] ) && isset( $_GET['sort_by'] ) ) {
			config_set( 'current_user_sprint_backlog_filter', $_GET['sort_by'], 
				auth_get_current_user_id() );
			$sort_by = $_GET['sort_by'];
		}
	
		$direction = config_get( 'current_user_sprint_backlog_filter_direction', null, auth_get_current_user_id() );
		if( !empty( $_GET['direction'] ) && isset( $_GET['direction'] ) ) {
			config_set( 'current_user_sprint_backlog_filter_direction', $_GET['direction'], 
				auth_get_current_user_id() );
			$direction = $_GET['direction'];
		}
		
		return $this->sortUserStories( $sort_by, $direction, $user_stories );
	}
	
	# get the list of all stories of the sprint.
	function getSprintStoriesSimple( $sprintName ) {
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		
		$this->getAdditionalProjectFields();
		$t_sql = "SELECT * 
					FROM $t_mantis_custom_field_string_table AS sprint 
					LEFT JOIN $t_mantis_bug_table AS bt ON bt.id = sprint.bug_id 
					WHERE sprint.field_id=" . db_param( 0 ) . " 
					AND sprint.value=" . db_param( 1 );
		$t_params = array( $this->spr, $sprintName );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# count sprint stories
	function countSprintStories( $sprint ) {
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		
		$this->getAdditionalProjectFields();
		$t_sql = "SELECT * 
					FROM $t_mantis_custom_field_string_table AS sprint 
					LEFT JOIN $t_mantis_bug_table AS bt ON bt.id = sprint.bug_id 
					WHERE sprint.field_id=" . db_param( 0 ) . " 
					AND sprint.value=" . db_param( 1 );
		$t_params = array( $this->spr, $sprint );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get all tasks to a given user story 
	function getSprintTasks( $us_id, $user_id = "" ) {
		$t_params = array( $us_id );
		
		$addsql = "";
		if( $user_id > 0 ) {
			$addsql = " AND developer_id = " . db_param( 1 );
			$t_params[] = $user_id;
		}
		$t_sql = "SELECT * 
				FROM gadiv_tasks 
				WHERE us_id = " . db_param( 0 ) . " " . $addsql . " 
				ORDER BY id ASC";
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get sprint information by name
	function getSprintById() {
		if( $this->sprint_id != "" ) {
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
				
			$t_params = array( $this->sprint_id );
			$sprint = $this->executeQuery( $t_sql, $t_params );
			
			$sprint[0]['start'] = substr($sprint[0]['start'], 0, 10);
			$sprint[0]['end'] = substr($sprint[0]['end'], 0, 10);
			return $sprint[0];
		}
	}
	
	# get sprint information by id
	function getSprintByName() {
		if( $this->sprint_id != "" ) {
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
		 FROM gadiv_sprints WHERE id=" . db_param( 0 );
			$t_params = array( $this->sprint_id );
			$sprint = $this->executeQuery( $t_sql, $t_params );
			$sprint[0]['start'] = substr($sprint[0]['start'], 0, 10);
			$sprint[0]['end'] = substr($sprint[0]['end'], 0, 10);
			return $sprint[0];
		}
	}
	
	# add new sprint
	function newSprint() {
		
		if (db_is_mssql()) {
			
			$t_sql = "INSERT INTO gadiv_sprints (team_id,pb_id,name,description,status,daily_scrum,
		                 start,dispose,enddate,closed,unit_storypoints,unit_planned_work,unit_planned_task,workday_length
			          ) VALUES ("
					     . db_param( 0 )
					     . ","
						 . db_param( 1 )
						 . ","
						 . db_param( 2 )
						 . ","
						 . db_param( 3 )
						 . ",0,"
						 . db_param( 4 )
						 . ","
						 . db_param( 5 )
						 . ",'" . $this->getDateFormat(1900,1,1) . "',"
			             . db_param( 6 )
			             . ",'" . $this->getDateFormat(1900,1,1) . "',"
			             . "0,0,0,0.00)";
		} else {
			$t_sql = "INSERT INTO gadiv_sprints (team_id,pb_id,name,description,status,daily_scrum,
		                 start,dispose,enddate,closed,unit_storypoints,unit_planned_work,unit_planned_task,workday_length
			          ) VALUES ("
					     . db_param( 0 )
					     . ","
						 . db_param( 1 )
						 . ","
						 . db_param( 2 )
						 . ","
						 . db_param( 3 )
						 . ",0,"
						 . db_param( 4 )
						 . ","
						 . db_param( 5 )
						 . ",'1900-01-01 00:00:00',"
			             . db_param( 6 )
					     . ",'1900-01-01 00:00:00',"
			             . "0,0,0,0.00)";
		}
				
		$t_params = array( $this->team_id, $this->pb_id, $this->name, $this->description, 
			$this->daily_scrum, $this->getNormalDateFormat($this->start), $this->getNormalDateFormat($this->end ));
		db_query_bound( $t_sql, $t_params );
		$this->sprint_id = db_insert_id("gadiv_sprints");
		
		$result = $this->executeQuery( "SELECT id,
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
		 FROM gadiv_sprints ORDER BY name ASC" );
		
		foreach( $result as $num => $row ) {
			if( $row['status'] != 2 ) {
				$spr .= $row['name'] . '|';
			}
		}
		
		$spr = substr( $spr, 0, -1 );
		
		$this->getAdditionalProjectFields();
		custom_field_update( $this->spr, array( 'name' => 'Sprint', 'possible_values' => $spr ) );
		
		return $this->sprint_id;
	}
	
	# save / update sprint information
	function editSprint() {
		if( $this->sprint_id == 0 ) {
			$this->sprint_id = $this->newSprint();
		}
		$t_sql = "UPDATE gadiv_sprints 
				SET name=" . db_param( 0 ) . ", 
				description=" . db_param( 1 ) . ", 
				daily_scrum=" . db_param( 2 ) . ", 
				team_id=" . db_param( 3 ) . ", 
				start=" . db_param( 4 ) . ", 
				enddate=" . db_param( 5 ) . ", 
				pb_id=" . db_param( 6 ) . " 
				WHERE id=" . db_param( 7 );
		
		$t_params = array( $this->name, $this->description, $this->daily_scrum, $this->team_id, 
							$this->getNormalDateFormat($this->start), $this->getNormalDateFormat($this->end), $this->pb_id, $this->sprint_id );
		
		db_query_bound( $t_sql, $t_params );
		
		$result = $this->executeQuery( "SELECT id,
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
		 FROM gadiv_sprints ORDER BY name ASC" );
		if( !empty( $result ) ) {
			foreach( $result as $num => $row ) {
				$t_sprints .= $row['name'] . '|';
			}
		}
		$t_sprints = substr( $t_sprints, 0, -1 );
		
		$this->getAdditionalProjectFields();
		custom_field_update( $this->spr, 
				array( 'name' => 'Sprint', 'possible_values' => $t_sprints ) );
	}
	
	# delete selected sprint information
	function deleteSprint() {
		$t_sql = "DELETE FROM gadiv_sprints WHERE id=" . db_param( 0 );
		$t_params = array( $this->id );
		db_query_bound( $t_sql, $t_params );
		
		$result = $this->executeQuery( "SELECT id,
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
		 FROM gadiv_sprints ORDER BY name ASC" );
		
		foreach( $result as $num => $row ) {
			if( $row['status'] != 2 ) {
				$spr .= $row['name'] . '|';
			}
		}
		
		$spr = substr( $spr, 0, -1 );
		
		$this->getAdditionalProjectFields();
		custom_field_update( $this->spr, array( 'name' => 'Sprint', 'possible_values' => $spr ) );
	}
	
	# change sprint status by sprint id
	function setSprintStatus( $status, $sprint_id ) {
		$t_sql = "UPDATE gadiv_sprints SET status=" . db_param( 0 ) . " WHERE id=" . db_param( 1 );
		$t_params = array( $status, $sprint_id );
		$ergebnis = db_query_bound( $t_sql, $t_params );
		
		if( $status == 2 ) {
			$result = $this->executeQuery( "SELECT id,
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
			 FROM gadiv_sprints ORDER BY name ASC" );
			
			foreach( $result as $num => $row ) {
				if( $row['status'] != 2 ) {
					$spr .= $row['name'] . '|';
				}
			}
			
			$spr = substr( $spr, 0, -1 );
			
			$this->getAdditionalProjectFields();
			custom_field_update( $this->spr, 
				array( 'name' => 'Sprint', 'possible_values' => $spr ) );
		}
		
		if( $ergebnis == true ) {
			return 1;
		} else {
			return 0;
		}
	}
	
	# checks if sprint name is unique or not
	function sprintnameisunique() {
		$t_params = array( $_POST['name'] );
		
		if( $this->sprint_id > 0 ) {
			$addsql = " AND id != " . db_param( 1 );
			$t_params[] = $this->sprint_id;
		}
		
		$t_sql = "SELECT count(*) AS sprints 
				FROM gadiv_sprints 
				WHERE name LIKE " . db_param( 0 ) . $addsql ."
					GROUP BY name";
		$result = $this->executeQuery( $t_sql, $t_params );
		
		if( $result[0]['sprints'] > 0 ) {
			return false;
		}
		return true;
	}
		
	# checks if this sprint overlaps with another sprint of this team
	function isSprintOverlapping() {
		
		$retVal = false;
		
		$t_sql = "SELECT name
				FROM gadiv_sprints
				WHERE team_id = " . db_param() . " 
				AND start <= " . db_param() . " 
				AND enddate >= " . db_param() . " 
				AND id <> " . db_param();
		
		$t_params = array();
		$t_params[] = $this->team_id;
		$t_params[] = $this->getNormalDateFormat($this->end);
		$t_params[] = $this->getNormalDateFormat($this->start);
		$t_params[] = $this->sprint_id;

		$result = $this->executeQuery( $t_sql, $t_params );
		if( $result[0]['name'] ) {
			$retVal = true;
		}
		
		return $retVal;
	}
	
	# check if previous sprint is already closed before committing a new one
	function previousSprintIsClosed( $team_id, $sprint_id ) {
		$t_sql = "SELECT count(*) AS amount 
				FROM gadiv_sprints 
				WHERE team_id = " . db_param( 0 ) . " 
				AND status != 2 
				AND status != 0 
				AND id != " . db_param( 1 ) . "
				GROUP BY team_id";
		$t_params = array( $team_id, $sprint_id );
		$result = $this->executeQuery( $t_sql, $t_params );
		
		if( $result[0]['amount'] > 0 ) {
			return false;
		} else {
			return true;
		}
	}
	
	# get product backlog name by team information
	function getProductBacklogByTeam( $team_id ) {
		$t_sql = "SELECT pb.name AS pname 
				FROM gadiv_sprints AS s 
				LEFT JOIN gadiv_teams AS t ON t.id = s.team_id 
				LEFT JOIN gadiv_productbacklogs AS pb ON pb.id = t.pb_id 
				WHERE team_id=" . db_param( 0 );
		$t_params = array( $team_id );
		$result = $this->executeQuery( $t_sql, $t_params );
		return $result[0]['pname'];
	}
	
	# checks if the current sprint has still user stories to do
	function sprintHasUserStories( $name ) {
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		$this->getAdditionalProjectFields();
		
		$t_sql = "SELECT count(*) AS count_userstories 
				FROM $t_mantis_custom_field_string_table 
				WHERE value = " . db_param( 0 ) . " 
				AND field_id=" . db_param( 1 ) . "
				GROUP BY field_id";
		$t_params = array( $name, $this->spr );
		$result = $this->executeQuery( $t_sql, $t_params );
		if( $result[0]['count_userstories'] > 0 ) {
			return true;
		} else {
			return false;
		}
	}
	
	# get the current sprint for the current logged in user
	function getCurrentUserSprint( $user_id ) {
		$top = $limit = "";
		if (db_is_mssql()) {
			$top = " top 1";
		} else {
			$limit = " limit 1";
		}
		$t_sql = "SELECT " . $top . "rtu.team_id, rtu.user_id, rtu.role, s.id,
							s.team_id,
							s.pb_id,
							s.name,
							s.description,
							s.status,
							s.daily_scrum,
							s.start,
							s.dispose as " . AGILEMANTIS_COMMIT_FIELD . ",
							s.enddate as " . AGILEMANTIS_END_FIELD . ",
							s.closed,
							s.unit_storypoints,
							s.unit_planned_work,
							s.unit_planned_task,
							s.workday_length
				FROM gadiv_rel_team_user AS rtu 
				LEFT JOIN gadiv_sprints AS s ON s.team_id = rtu.team_id 
				WHERE user_id=" . db_param( 0 ) . " 
				AND STATUS != 2 
				ORDER BY start" . $limit;
		$t_params = array( $user_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# count all sprints belonging to one user
	function countUserSprints( $user_id ) {
		$t_sql = "SELECT COUNT(DISTINCT s.team_id ) AS sprints 
				FROM gadiv_rel_team_user AS tu 
				LEFT JOIN gadiv_sprints AS s ON s.team_id = tu.team_id 
				WHERE user_id=" . db_param( 0 ) . " 
				AND s.name IS NOT NULL
				GROUP BY user_id";
		$t_params = array( $user_id );
		$user = $this->executeQuery( $t_sql, $t_params );
		return $user[0]['sprints'];
	}
	
	# check if all tasks and user stories are resolved or closed in a sprint
	function allTasksAndStoriesAreClosed( $name ) {
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		
		$this->getAdditionalProjectFields();
		$t_sql = "SELECT mbt.status AS userstory_status, t.status AS task_status 
				FROM $t_mantis_custom_field_string_table AS mfst
				LEFT JOIN gadiv_tasks AS t ON mfst.bug_id = t.us_id
				LEFT JOIN $t_mantis_bug_table AS mbt ON mfst.bug_id = mbt.id
				WHERE mfst.value=" . db_param( 0 ) . " 
				AND mfst.field_id=" . db_param( 1 );
		$t_params = array( $name, $this->spr );
		$result = $this->executeQuery( $t_sql, $t_params );
		if( !empty( $result ) ) {
			foreach( $result as $num => $row ) {
				if( $row['userstory_status'] < 80 ||
					 ($row['task_status'] < 4 && $row['task_status'] != NULL) ) {
					return false;
				}
			}
		}
		return true;
	}
	
	# get all sprints which are not resolved or closed by the selected team
	function getUndoneSprintsByTeam( $team_id, $sprint_id ) {
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
				FROM gadiv_sprints 
				WHERE team_id = " . db_param( 0 ) . " 
				AND status < 2 
				AND id != " . db_param( 1 );
		$t_params = array( $team_id, $sprint_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# collect all information when sprint is committed and save to database
	function confirmInformation( $id, $unit_sp, $unit_wu, $unit_wt, $ld ) {
		$t_sql = "UPDATE gadiv_sprints 
				SET unit_storypoints=" . db_param( 0 ) . ", 
				unit_planned_work=" . db_param( 1 ) . ", 
				unit_planned_task=" . db_param( 2 ) . ", 
				workday_length=" . db_param( 3 ) . ", 
				dispose=" . db_param( 4 ) . " 
				WHERE id=" . db_param( 5 );
		$t_params = array( $unit_sp, $this->getUnitId( $unit_wu ), $this->getUnitId( $unit_wt ), 
			$ld, $this->getDateFormat(date( 'Y' ), date( 'm' ), date( 'd' ), true), $id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# count all team members who participate in a sprint
	function getCountSprintTeamMember( $team_id ) {
		$t_sql = "SELECT COUNT(*) AS developer 
				FROM gadiv_rel_team_user 
				WHERE team_id=" . db_param( 0 ) . " 
				AND role='3'" . "
				GROUP BY role";
		$t_params = array( $team_id );
		$team = $this->executeQuery( $t_sql, $t_params );
		return $team[0]['developer'];
	}
	
	# collect all information when a sprint is closed and save to database
	function closeInformation( $id, $usps, $uwus, $uwts, $lds ) {
		$cus = 0;
		$cts = 0;
		$csus = 0;
		$sps = 0;
		$spus = 0;
		$wps = 0;
		$wes = 0;
		$wms = 0;
		$spm = 0;
		$cdv = 0;
		$kds = 0;
		$cdvt = 0;
		$ks = 0;
		
		$developer = array();
		
		# get sprint information
		$this->sprint_id = $id;
		$sprint = $this->getSprintByName();
		
		# Sprint User Stories
		$userstories = $this->countSprintStories( $sprint['name'] );
		
		if( !empty( $userstories ) ) {
			
			# amount of user stories in a sprint CU(S)
			$cus = count( $userstories );
			
			foreach( $userstories as $num => $row ) {
				
				# User Story information
				$story = $this->checkForUserStory( $row['id'] );
				
				# amount of task storypoints SP(S)
				$sps += $this->getStoryPoints( $row['id'] );
				
				# Task to user stories
				$tasks = $this->getSprintTasks( $row['id'] );
				if( !empty( $tasks ) ) {
					
					# amount of tasks in a sprint CT(S)
					$cts += count( $tasks );
					foreach( $tasks as $key => $value ) {
						if( !in_array( $value['developer_id'], $developer ) ) {
							$developer[] = $value['developer_id'];
						}
						
						$wps += $value['planned_capacity'];
						$wes += $value['performed_capacity'];
					}
				}
				
				# amount of splitted user stories CSUS(S)
				if( $this->isSplittedStory( $row['id'] ) ) {
					
					$new_story = $this->getSplittedStory( $row['id'] );
					$new_userstory = $this->checkForUserStory( $row['id'] );
					$wps += $new_story['wmu'];
					
					# amount of splitted user stories CSU(S)
					$csus++;
					
					# amount of story points in splitted user stories CSPUS(S)
					$spus += $story['storypoints'];
					$tasks = $this->getSprintTasks( $new_story['new_userstory_id'] );
					foreach( $tasks as $key => $value ) {
						
						# work moved in splitted user stories WM(S)
						$wms += $value['rest_capacity'];
					}
				}
			}
		}
		if( $sprint['unit_planned_task'] == '3' ) {
			$spm = $wms;
		} else {
			$spm = 0;
		}
		
		# amount of developers who worked at least on a task in this sprint CDV(TD(S))
		$cdv = count( $developer );
		
		# amount of developers in a team CDVT(S)
		$cdvt = $this->getCountSprintTeamMember( $sprint['team_id'] );
		
		# developer capacities of developers who worked at least on a task in this sprint K(S)
		if( !empty( $developer ) ) {
			foreach( $developer as $key => $value ) {
				$t_sql = "SELECT sum( capacity ) AS developer 
						FROM gadiv_rel_user_team_capacity 
						WHERE user_id=" . db_param( 0 ) . " 
						AND team_id=" . db_param( 1 ) . " 
						AND date>=" . db_param( 2 ) . " 
						AND date<=" . db_param( 3 ) . "
						GROUP BY user_id";
				$t_params = array( $value, $sprint['team_id'], $sprint['start'], $sprint['end'] );
				$capacity = $this->executeQuery( $t_sql, $t_params );
				$ks += $capacity[0]['developer'];
			}
		}
		
		# total developer capacity of all team developers K(TD, DV, Z)
		$t_sql = "SELECT sum( capacity ) AS total_cap 
				FROM gadiv_rel_user_team_capacity 
				WHERE team_id=" . db_param( 0 ) . " 
				AND date>=" . db_param( 1 ) . " 
				AND date<=" . db_param( 2 ) . "
				GROUP BY team_id";
		$t_params = array( $sprint['team_id'], $sprint['start'], $sprint['end'] );
		$team = $this->executeQuery( $t_sql, $t_params );
		$kds = $team[0]['total_cap'];
		
		if ( is_null($kds) ) {
			$kds = 0;
		}
		
		$t_sql = "INSERT INTO gadiv_rel_sprint_closed_information 
				VALUES (" . db_param( 0 ) . "," . db_param( 1 ) . "," . db_param( 2 ) . "," 
				          . db_param( 3 ) . "," . db_param( 4 ) . "," . db_param( 5 ) . "," 
				          . db_param( 6 ) . "," . db_param( 7 ) . "," . db_param( 8 ) . "," 
				          . db_param( 9 ) . "," . db_param( 10) . "," . db_param( 11) . "," 
				          . db_param( 12) . "," . db_param( 13) . ") ";
		$t_params = array( $id, $cus, $cts, $csus, $sps, $spus, $wps, $wes, $wms, $spm, $cdvt, $kds, 
			$cdv, $ks );
		db_query_bound( $t_sql, $t_params );
		
		$t_sql = "UPDATE gadiv_sprints 
				SET closed=" . db_param( 0 ) . " 
				WHERE id=" . db_param( 1 );
		$t_params = array( 
			$this->getDateFormat(date( 'Y' ), date( 'm' ), date( 'd' ), true), $id );
		db_query_bound( $t_sql, $t_params );
	}

	function getLatestSprints( $team_id, $amountOfSprints = 0, $sprintName = "", $previous = "" ) {
		
		$top="";
		if (db_is_mysql()) {
			if( $amountOfSprints != "" ) {
				$addSql = " ORDER BY id DESC LIMIT 0, " . ( int ) $amountOfSprints;
			}
			
			if( $previous == true ) {
				$addSql = " ORDER BY id DESC LIMIT 1, " . ( int ) $amountOfSprints;
			}
		} else {
			//mssql
			$addSql = " ORDER BY id DESC ";
			if($amountOfSprints > 0 ){
				$top = " top "  . ( int ) $amountOfSprints . " ";
			}
		}
		
		$t_params[0] =  $team_id;
		
		if( $sprintName != "" ) {
			$addSql = " AND name=" . db_param( 1 );
			$t_params[1] = $sprintName;
		}
		
		$t_sql = "SELECT " . $top . " id,
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
							workday_length,
							sprint_id,
							count_user_stories,
							count_task_sprint,
							count_splitted_user_stories_sprint,
							storypoints_sprint,
							storypoints_in_splitted_user_stories,
							work_planned_sprint,
							work_performed,
							work_moved,
							storypoints_moved,
							count_developer_team,
							total_developer_capacity,
							count_developer_team_task,
							total_developer_capacity_task
				FROM gadiv_sprints 
				LEFT JOIN gadiv_rel_sprint_closed_information ON sprint_id = id 
				WHERE team_id=" . db_param( 0 ) . $addSql;
				
		return $this->executeQuery( $t_sql, $t_params );
	}

	function getPreviousSprint( $sprint_id, $team_id ) {
		$t_sql = "SELECT enddate as " . AGILEMANTIS_END_FIELD . " FROM gadiv_sprints WHERE id=" . db_param( 0 );
		$t_params = array( $sprint_id );
		$sprint = $this->executeQuery( $t_sql, $t_params );
		
		$top="";
		$limit="";
		if (db_is_mssql()) {
			$top = " top 1 ";
		} else {
			$limit = " LIMIT 0, 1 ";
		}
		
		$t_sql = "SELECT " . $top . "id,
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
							workday_length,
							sprint_id,
							count_user_stories,
							count_task_sprint,
							count_splitted_user_stories_sprint,
							storypoints_sprint,
							storypoints_in_splitted_user_stories,
							work_planned_sprint,
							work_performed,
							work_moved,
							storypoints_moved,
							count_developer_team,
							total_developer_capacity,
							count_developer_team_task,
							total_developer_capacity_task
				FROM gadiv_sprints 
				LEFT JOIN gadiv_rel_sprint_closed_information ON sprint_id = id 
				WHERE enddate < " . db_param( 0 ) . " 
				AND team_id=" . db_param( 1 ) . " 
				ORDER BY id DESC" . $limit;
		$t_params = array( $sprint[0]['end'], $team_id );		
		return $this->executeQuery( $t_sql, $t_params );
	}

	function getTeamCapacityInSprint( $team_id, $sprint_start, $sprint_end) {
		
		$t_sql = "SELECT sum( capacity ) AS total_cap 
				FROM gadiv_rel_user_team_capacity 
				WHERE team_id=" . db_param( 0 ) . " 
				AND date>=" . db_param( 1 ) . " 
				AND date<=" . db_param( 2 ) . "
				GROUP BY team_id";
		$t_params = array( $team_id, $sprint_start, $sprint_end );
		$team = $this->executeQuery( $t_sql, $t_params );
		return $team[0]['total_cap'];
	}

	function getVelocityDataFromSprint( $userstories ) {
		if( !empty( $userstories ) ) {
			
			$reststorypoints = 0;
			$storypoints = 0;
			$workmoved = 0;
			$storypointsmoved = 0;
			$performed = 0;
			
			foreach( $userstories as $num => $row ) {
				
				// Prüfe, ob Status erledigt oder geschlossen und ermittle Anzahl Story Points
				$userstory = $this->checkForUserStory( $row['bug_id'] );
				if( $row['status'] >= 80 ) {
					$reststorypoints += $userstory['storypoints'];
				}
				
				$storypoints = $storypoints + ( int ) $userstory['storypoints'];
				// Story Points in aufgesplitteten User Stories ermitteln
				$splittedStory = $this->getSplittedStory( $row['id'] );
				
				if( !empty( $splittedStory ) ) {
					$workmoved += $userstory['storypoints'];
					$storypointsmoved += $splittedStory['storypoints_moved'];
				}
				
				$tasked = $this->getSprintTasks( $row['id'], 0 );
				if( !empty( $tasked ) ) {
					foreach( $tasked as $key => $value ) {
						$performed += $value['performed_capacity'];
					}
				}
			}
			
			$sprint['reststorypoints'] = $reststorypoints;
			$sprint['storypoints'] = $storypoints;
			$sprint['workmoved'] = $workmoved;
			$sprint['storypointsmoved'] = $storypointsmoved;
			$sprint['performed'] = $performed;
			
			return $sprint;
		}
	}
	
	function updateSprintCustomFieldStrings( $p_sprint_name_old, $p_sprint_name_new ) {
		if( empty( $p_sprint_name_old ) 
				|| empty( $p_sprint_name_new ) 
				|| ($p_sprint_name_old == $p_sprint_name_new) ) {
			
			return;
		}
		
		$this->getAdditionalProjectFields();
	
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
	
		$t_sql = "UPDATE $t_mantis_custom_field_string_table ";
		$t_sql .= "SET value=" . db_param( 0 ) . " ";
		$t_sql .= "WHERE field_id=" . db_param( 1 ) . " AND value=" . db_param( 2 );
		$t_params = array( $p_sprint_name_new, $this->spr, $p_sprint_name_old );
		
		db_query_bound( $t_sql, $t_params );
	}
}
?>