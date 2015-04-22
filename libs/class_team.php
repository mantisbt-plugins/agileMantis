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



#	this class will hold functions for agileMantis team management
class gadiv_team extends gadiv_commonlib {
	var $capacity;
	var $role_id;
	var $sprint_id;
	var $total_sum;
	var $name;
	var $description;
	var $id;
	var $product_backlog;
	var $team_id;
	var $us_id;
	var $start;
	var $end;
	var $status;
	var $planned_capacity;
	var $performed_capacity;
	var $rest_capacity;
	var $daily_scrum;
	
	# adds a new Team
	function newTeam() {
		$t_sql = "INSERT INTO gadiv_teams ( name, description, pb_id, daily_scrum )
					VALUES ( " . db_param( 0 ) . "," . db_param( 1 ) . "," . db_param( 2 ) . "," . db_param( 3 ) . " )";
		$t_params = array(htmlspecialchars( $this->name ),htmlspecialchars( $this->description ),$this->product_backlog,( int ) $this->daily_scrum );
		db_query_bound( $t_sql, $t_params );
		$this->id = db_insert_id("gadiv_teams");
		return $this->id;
	}
	
	# edits a Team and if there is not $this->id, 
	# create a new one and edit additional information for the new team
	function editTeam() {
		if( $this->id == 0 ) {
			$this->id = $this->newTeam();
		}
		$t_sql = "UPDATE gadiv_teams 
				SET name=" . db_param( 0 ) . ", 
				description=" . db_param( 1 ) . ", 
				pb_id=" . db_param( 2 ) . ", 
				daily_scrum=" . db_param( 3 ) . " 
				WHERE id=" . db_param( 4 );
		$t_params = array(htmlspecialchars( $this->name ),htmlspecialchars( $this->description ),$this->product_backlog,(( int ) $this->daily_scrum),$this->id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# deletes a team from the database
	function deleteTeam( $id ) {
		$t_sql = "DELETE FROM gadiv_teams WHERE id=" . db_param( 0 );
		$t_params = array($id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# get all Teams with sorting options
	function getTeams() {
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		
		if( $_GET['sort_by'] ) {
			if( $_SESSION['order'] == 0 ) {
				$_SESSION['order'] = 1;
				$direction = 'ASC';
			} else {
				$_SESSION['order'] = 0;
				$direction = 'DESC';
			}
			switch( $_GET['sort_by'] ) {
				case 'product_backlog':
					$orderby = "LEFT JOIN gadiv_productbacklogs AS pb ON pb.id = t.pb_id 
							ORDER BY pb.name " . $direction;
					break;
				case 'product_owner':
					$addsql = ', ut.username';
					$orderby = "LEFT JOIN gadiv_rel_team_user AS utc ON utc.team_id = t.id 
								LEFT JOIN $t_mantis_user_table AS ut ON ut.id = utc.user_id 
								WHERE role LIKE '%1%' 
								ORDER BY ut.username " . $direction;
					break;
				case 'scrum_master':
					$addsql = ', ut.username';
					$orderby = "LEFT JOIN gadiv_rel_team_user AS utc ON utc.team_id = t.id 
								LEFT JOIN $t_mantis_user_table AS ut ON ut.id = utc.user_id 
								WHERE role LIKE '%2%'
								ORDER BY ut.username " . $direction;
					break;
				case 'description':
					$orderby = "ORDER BY description " . $direction;
					break;
				case 'name':
				default:
					$orderby = "ORDER BY name " . $direction;
			}
		}
		
		if( !$_GET['sort_by'] ) {
			$orderby = "ORDER BY name ASC";
			$_SESSION['order'] = 1;
		}
		
		$t_sql = "SELECT t.id AS id, t.pb_id AS product_backlog, 
						 t.name AS name, t.description AS description " . $addsql . " 
					FROM gadiv_teams AS t " . $orderby;
		return $this->executeQuery( $t_sql );
	}
	
	# checks wether the entered team name is unique or not
	function isTeamNameUnique() {
		$t_sql = "SELECT count(*) AS tnz 
				FROM gadiv_teams 
				WHERE name LIKE " . db_param( 0 ) . " 
				AND id!=" . db_param( 1 ) . "
				GROUP BY name";
		$t_params = array($this->name,$this->id );
		$isTeam = $this->executeQuery( $t_sql, $t_params );
		if( $isTeam[0]['tnz'] > 0 ) {
			return false;
		} else {
			return true;
		}
	}
	
	#	with the help of this function only complete Teams will be returned. Firstly all Team are loaded from the database and secondly
	#	all team members are loaded from those teams. Every Team will be checked, if it has a Product Owner, a Scrum Master and at least
	#	one developer. If the team is "complete" the function will return a filled array and if the team is not complete it will
	#	return an empty one.
	function getCompleteTeams() {
		$teamdata = $this->getTeams();
		foreach( $teamdata as $num => $row ) {
			$t_sql = "SELECT count(role) AS product_owner 
					FROM gadiv_rel_team_user 
					WHERE team_id=" . db_param( 0 ) . " 
					AND role LIKE '%1%'" . "
					GROUP BY team_id";
			$t_params = array($row['id'] );
			$prowner = $this->executeQuery( $t_sql, $t_params );
			
			$t_sql = "SELECT count(role) AS scrum_master 
					FROM gadiv_rel_team_user 
					WHERE team_id=" . db_param( 0 ) . " 
					AND role LIKE '%2%'" . "
					GROUP BY team_id";
			$t_params = array($row['id'] );
			$scmaster = $this->executeQuery( $t_sql, $t_params );
			
			$t_sql = "SELECT count(role) AS developer 
					FROM gadiv_rel_team_user 
					WHERE team_id=" . db_param( 0 ) . " 
					AND role LIKE '%3%'" . "
					GROUP BY team_id";
			$t_params = array($row['id'] );
			$developer = $this->executeQuery( $t_sql, $t_params );
			
			if( $scmaster[0]['scrum_master'] > 0 && $prowner[0]['product_owner'] > 0 && $developer[0]['developer'] > 0 && $row['product_backlog'] > 0 ) {
				$teams[$num]['id'] = $row['id'];
				$teams[$num]['name'] = $row['name'];
				$teams[$num]['product_backlog'] = $row['product_backlog'];
			}
		}
		return $teams;
	}
	
	# get all team users
	function allTeamsByUser( $user_id ) {
		$t_sql = "SELECT DISTINCT(team_id) 
				FROM gadiv_rel_team_user 
				WHERE user_id=" . db_param( 0 );
		$t_params = array($user_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get the current Product Backlog name which is processed by a team and return it
	function getTeamBacklog( $id ) {
		$t_sql = "SELECT pb.name, pb.id, t.pb_id 
				FROM gadiv_teams AS t 
				LEFT JOIN gadiv_productbacklogs AS pb ON t.pb_id = pb.id 
				WHERE pb.id=" . db_param( 0 );
		$t_params = array($id );
		$pbName = $this->executeQuery( $t_sql, $t_params );
		return $pbName[0]['name'];
	}
	
	# looks for the current Team-User of a team and returns the name
	function getTeamUserByBacklogName( $name ) {
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		
		$t_sql = "SELECT username 
				FROM $t_mantis_user_table 
				WHERE realname LIKE " . db_param( 0 );
		$t_params = array("Team-User-" . $name );
		$result = $this->executeQuery( $t_sql, $t_params );
		return $result[0]['username'];
	}
	
	# get the current Product Owner of one team
	function getTeamProductOwner() {
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		
		$t_sql = "SELECT ut.id AS user_id 
				FROM gadiv_rel_team_user AS tu 
				LEFT JOIN $t_mantis_user_table AS ut ON tu.user_id = ut.id 
				WHERE role = 1 
				AND team_id=" . db_param( 0 ) . " 
				ORDER BY username ASC";
		$t_params = array($this->id );
		$result = $this->executeQuery( $t_sql, $t_params );
		return $result[0]['user_id'];
	}
	
	# get the current Scrum Master of one team
	function getTeamScrumMaster() {
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		
		$t_sql = "SELECT ut.id AS user_id 
				FROM gadiv_rel_team_user AS tu 
				LEFT JOIN $t_mantis_user_table AS ut ON tu.user_id = ut.id 
				WHERE role = 2 
				AND team_id=" . db_param( 0 ) . " 
				ORDER BY username ASC";
		$t_params = array($this->id );
		$result = $this->executeQuery( $t_sql, $t_params );
		return $result[0]['user_id'];
	}
	
	# get all developer of one team
	function getTeamDeveloper() {
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		$t_sql = "SELECT * 
					FROM gadiv_rel_team_user AS tu 
					LEFT JOIN $t_mantis_user_table AS ut ON tu.user_id = ut.id 
					WHERE role = 3 
					AND team_id=" . db_param( 0 ) . " 
					AND id IS NOT NULL 
					ORDER BY username ASC";
		$t_params = array($this->id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get all customers of one team
	function getTeamCustomer() {
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		
		$t_sql = "SELECT * 
					FROM gadiv_rel_team_user AS tu 
					LEFT JOIN $t_mantis_user_table AS ut ON tu.user_id = ut.id 
					WHERE role = 4 
					AND team_id=" . db_param( 0 ) . " 
					ORDER BY username ASC";
		$t_params = array($this->id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get all product user of one team
	function getTeamProductUser() {
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		
		$t_sql = "SELECT * 
					FROM gadiv_rel_team_user AS tu 
					LEFT JOIN $t_mantis_user_table AS ut ON tu.user_id = ut.id 
					WHERE role = 5 
					AND team_id = " . db_param( 0 ) . " 
					ORDER BY username ASC";
		$t_params = array($this->id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get all manager of one team
	function getTeamManager() {
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		
		$t_sql = "SELECT * 
					FROM gadiv_rel_team_user AS tu 
					LEFT JOIN $t_mantis_user_table AS ut ON tu.user_id = ut.id 
					WHERE role = 6 
					AND team_id = " . db_param( 0 ) . " 
					ORDER BY username ASC";
		$t_params = array($this->id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# calculate capacity from one team member
	function getTeamMemberCapacity( $user_id, $date_start, $date_end ) {
		$t_sql = "SELECT SUM( capacity ) AS total_cap 
				FROM gadiv_rel_user_availability 
				WHERE user_id=" . db_param( 0 ) . " 
				AND date>=" . db_param( 1 ) . " 
				AND date<=" . db_param( 2 ) . "
				GROUP BY user_id";
		$t_params = array($user_id,$this->getNormalDateFormat($date_start),$this->getNormalDateFormat($date_end));
		$result = $this->executeQuery( $t_sql, $t_params );
		if( $result[0]['total_cap'] != "" ) {
			return $result;
		}
	}
	
	# deletes all team member from a team
	function deleteTeamMember( $team_id ) {
		$t_sql = "DELETE FROM gadiv_rel_team_user 
				WHERE team_id=" . db_param( 0 );
		$t_params = array($team_id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# deletes team member by role_id from a team
	function deleteTeamRoleMember( $team_id, $role_id ) {
		$t_sql = "DELETE FROM gadiv_rel_team_user 
				WHERE team_id=" . db_param( 0 ) . " 
				AND role=" . db_param( 1 );
		$t_params = array($team_id,$role_id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# adds a new user to a team with its user role
	function addTeamMember( $user_id, $team_id, $role_id ) {
		$table = "gadiv_rel_team_user";
		$t_sql = "SELECT * 
					FROM " . $table . " 
					WHERE user_id=" . db_param( 0 ) . " 
					AND team_id=" . db_param( 1 ) . " 
					AND role=" . db_param( 2 );
		$t_params = array($user_id,$team_id,$role_id );
		$result = db_query_bound( $t_sql, $t_params );
		if( db_num_rows( $result ) == 0 ) {
			$t_sql = "INSERT INTO " . $table . " (user_id, team_id, role) VALUES ( " 
			         . db_param( 0 ) . "," . db_param( 1 ) . "," . db_param( 2 ) . " )";
		    $t_params = array($user_id,$team_id,$role_id );
			db_query_bound( $t_sql, $t_params );
		}
	}
	
	# deletes a user from a team by role
	function deleteSelectedTeamMember( $team_id, $user_id, $role_id ) {
		$t_sql = "DELETE FROM gadiv_rel_team_user 
				WHERE user_id=" . db_param( 0 ) . " 
				AND team_id=" . db_param( 1 ) . " 
				AND role=" . db_param( 2 );
		$t_params = array($user_id,$team_id,$role_id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# deletes a member from all teams where he is developer
	function deleteScrumDeveloperFromTeams( $p_user_id ) {
		# first delete the user' capacities, then remove from the teams
		$date = $this->getNormalDateFormat(date("Y-m-d"));
		$this->deleteDeveloperCapacities( $p_user_id, $date );
		
		# remove from teams
		$t_sql = "DELETE FROM gadiv_rel_team_user 
				WHERE user_id=" . db_param( 0 ) . " 
				AND role=3";
		$t_params = array($p_user_id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# deletes a member from all teams where he is stakeholder
	function deleteStakeholderFromTeams( $p_user_id ) {
		$t_sql = "DELETE FROM gadiv_rel_team_user 
				WHERE user_id=" . db_param( 0 ) . " 
				AND role IN (4, 5, 6, 7)";
		$t_params = array($p_user_id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# deletes all capacities of a developer from all teams for the future after date
	function deleteDeveloperCapacities( $p_user_id, $p_date ) {
		$t_sql = "SELECT * 
				FROM gadiv_rel_team_user 
				WHERE user_id=" . db_param( 0 ) . " 
				AND role=3";
		$t_params = array($p_user_id );
		$rsteams = $this->executeQuery( $t_sql, $t_params );
		for( $i = 0; $i < count( $rsteams ); $i++ ) {
			$t_sql = "DELETE from gadiv_rel_user_team_capacity 
					WHERE user_id=" . db_param( 0 ) . " 
					AND team_id=" . db_param( 1 ) . " 
					AND date >= " . db_param( 2 );
			$t_params = array($p_user_id,$rsteams[$i]['team_id'],$p_date );
			db_query_bound( $t_sql, $t_params );
		}
	}
	# inserts capacity values for users of one team with a defined date
	function insertTeamUserCapacity( $team_id, $user_id, $date, $capacity ) {
		
		$db_date = $this->getNormalDateFormat($date);
		
		$t_sql = "DELETE FROM gadiv_rel_user_team_capacity 
				WHERE team_id=" . db_param( 0 ) . " 
				AND user_id=" . db_param( 1 ) . " 
				AND date=" . db_param( 2 );
		$t_params = array($team_id,$user_id,$db_date );
		db_query_bound( $t_sql, $t_params );
		$t_sql = "INSERT INTO gadiv_rel_user_team_capacity (team_id, user_id, date, capacity) VALUES (" 
		         . db_param( 0 ) . "," . db_param( 1 ) . "," . db_param( 2 ) . "," . db_param( 3 ) . " )";
		$t_params = array($team_id,$user_id,$db_date,$capacity );
		db_query_bound( $t_sql, $t_params );
	}
	
	# checks wether a user has open tasks left in a team or not.
	function memberHasOpenTasks( $team_id, $user_id ) {
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		$this->getAdditionalProjectFields();
		
		$t_sql = "SELECT min( id ) AS currentsprint, name 
				FROM gadiv_sprints 
				WHERE team_id=" . db_param( 0 ) . " 
				AND status != 2
				GROUP BY name";
		$t_params = array($team_id );
		$get = $this->executeQuery( $t_sql, $t_params );
		if( $get[0]['currentsprint'] > 0 && !is_null( $get[0]['currentsprint'] ) ) {
			$t_sql = "SELECT * 
						FROM $t_mantis_custom_field_string_table 
						WHERE value=" . db_param( 0 ) . " 
						AND field_id=" . db_param( 1 );
			$t_params = array($get[0]['name'],$this->spr );
			$userstories = $this->executeQuery( $t_sql, $t_params );
			if( !empty( $userstories[0]['bug_id'] ) ) {
				for( $i = 0; $i < count( $userstories ); $i++ ) {
					$t_sql = "SELECT * 
								FROM gadiv_tasks 
								WHERE us_id=" . db_param( 0 ) . " 
								AND developer_id=" . db_param( 1 );
					$t_params = array($userstories[$i]['bug_id'],$user_id );
					$tasks = $this->executeQuery( $t_sql, $t_params );
					if( !empty( $tasks ) ) {
						foreach( $tasks as $num => $row ) {
							if( $row['status'] != 5 ) {
								return true;
							}
						}
					}
				}
			}
			return false;
		} else {
			return false;
		}
	}
	
	# count all team members of one team
	function countMemberTeams( $user_id ) {
		$t_sql = "SELECT count(DISTINCT team_id) AS teams 
				FROM gadiv_rel_team_user 
				WHERE user_id=" . db_param( 0 ) . "
				GROUP BY user_id";
		$t_params = array($user_id );
		$user = $this->executeQuery( $t_sql, $t_params );
		return $user[0]['teams'];
	}
	
	# checks if user is scrum master
	function isScrumMaster( $team_id, $user_id ) {
		$t_sql = "SELECT * 
				FROM gadiv_rel_team_user 
				WHERE team_id=" . db_param( 0 ) . " 
				AND user_id=" . db_param( 1 ) . " 
				AND role = 2";
		$t_params = array($team_id,$user_id );
		$result = $this->executeQuery( $t_sql, $t_params );
		if( count( $result ) == 1 ) {
			return true;
		}
		return false;
	}
	
	# checks if user is developer
	function isDeveloper( $team_id, $user_id ) {
		$t_sql = "SELECT * 
				FROM gadiv_rel_team_user 
				WHERE team_id=" . db_param( 0 ) . " 
				AND user_id=" . db_param( 1 ) . " 
				AND role = 3";
		$t_params = array($team_id,$user_id );
		$result = $this->executeQuery( $t_sql, $t_params );
		if( count( $result ) == 1 ) {
			return true;
		}
		return false;
	}
	
	# get product backlog information by team id
	function getBacklogByTeam( $team_id ) {
		if( !empty( $team_id ) ) {
			$t_sql = "SELECT DISTINCT(pb_id) 
					FROM gadiv_teams 
					WHERE id IN(" . $team_id . ")";
			return $this->executeQuery( $t_sql );
		}
	}
	
	# get user, product backlog and team role information 
	function getProductBacklogTeamRole( $product_backlog, $user_id, $role ) {
		$t_sql = "SELECT t.id as team_id, pb.id as pb_id 
				FROM gadiv_rel_team_user tu 
				LEFT JOIN gadiv_teams t ON tu.team_id = t.id 
				LEFT JOIN gadiv_productbacklogs pb ON t.pb_id = pb.id 
				WHERE tu.user_id=" . db_param( 0 ) . " 
				AND pb.name=" . db_param( 1 ) . " 
				AND role=" . db_param( 2 );
		$t_params = array($user_id,$product_backlog,$role );
		return $this->executeQuery( $t_sql, $t_params );
	}
	function getTotalTeamMemberCapacityBySprint( $user_id, $sprint_name ) {
		$t_sql = "SELECT start, status, enddate as " . AGILEMANTIS_END_FIELD . ", team_id 
				FROM gadiv_sprints 
				WHERE name=" . db_param( 0 );
		$t_params = array($sprint_name );
		$result = $this->executeQuery( $t_sql, $t_params );
		
		if( $result[0]['status'] == 2 ) {
			return 0;
		}
		
		if( $result[0]['status'] == 1 ) {
			$date_start = $this->getNormalDateFormat(date( 'Y-m-d' ));
		}
		
		if( $result[0]['status'] == 0 ) {
			$date_start = $result[0]['start'];
		}
		
		$t_sql = "SELECT sum(capacity) AS capacity 
				FROM  gadiv_rel_user_team_capacity  
				WHERE user_id=" . db_param( 0 ) . " 
				AND team_id=" . db_param( 1 ) . " 
				AND date>=" . db_param( 2 ) . " 
				AND date<=" . db_param( 3 ) . "
				GROUP BY user_id";
		$t_params = array($user_id,$result[0]['team_id'],$date_start,$result[0]['end'] );
		$result = $this->executeQuery( $t_sql, $t_params );
		
		return $result[0]['capacity'];
	}
}
?>