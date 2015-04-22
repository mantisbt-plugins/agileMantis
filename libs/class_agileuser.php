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


#	This class will hold functions for agileMantis user management
class gadiv_agileuser extends gadiv_commonlib {
	
	# get all agileMantis users with filter options
	function getAgileUser( $p_only_developer = false ) {
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		
		if( $_GET['filter'] ) {
			$t_username_filter = "AND username LIKE " . db_param( 0 ) . " ";
			$t_params[] = $_GET['filter'] . '%';
		}
		
		if( $p_only_developer == false ) {
			$t_condition = '(participant = 1 OR developer = 1 OR administrator = 1)';
		} else {
			$t_condition = 'developer = 1';
		}
		
		$t_sql = "SELECT * 
					FROM $t_mantis_user_table AS ut 
					LEFT JOIN gadiv_additional_user_fields AS auf 
						ON ut.id = auf.user_id 
					WHERE $t_condition $t_username_filter  
					ORDER by username ASC";
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# load all mantis user with filter and sorting options
	function getAllUser() {
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		
		if( $_GET['filter'] != "" ) {
			$t_username_filter = " WHERE username LIKE " . db_param( 0 ) . " ";
			$t_params[] = $_GET['filter'] . '%';
		}
		if( $_GET['sort_by'] ) {
			if( $_SESSION['order'] == 0 ) {
				$_SESSION['order'] = 1;
				$direction = 'ASC';
			} else {
				$_SESSION['order'] = 0;
				$direction = 'DESC';
			}
			switch( $_GET['sort_by'] ) {
				case 'realname':
					$t_orderby = " ORDER BY realname " . $direction;
					break;
				case 'email':
					$t_orderby = " ORDER BY email " . $direction;
					break;
				case 'username':
				default:
					$t_orderby = " ORDER BY username " . $direction;
			}
		}
		
		if( !$_GET['sort_by'] ) {
			$t_orderby = " ORDER BY username ASC";
			$_SESSION['order'] = 1;
		}
		
		$t_sql = "SELECT id, username, realname, email 
					FROM " . $t_mantis_user_table . $t_username_filter . $t_orderby;
		
		return $this->executeQuery( $t_sql, $t_params );
	}

	/**
		 * Return the user right of one agileMantis user:
		 * 	1 = Participant
		 * 	2 = Developer
		 * 	3 = Administrator
		 */
	function authUser() {
		$t_sql = "SELECT * 
					FROM gadiv_additional_user_fields 
					WHERE user_id=" . db_param( 0 );
		$t_params = array( auth_get_current_user_id() );
		$t_agilemantis_rights = $this->executeQuery( $t_sql, $t_params );

		if( $t_agilemantis_rights[0]['administrator'] ) {
			return 3;
		}
		if( $t_agilemantis_rights[0]['developer'] ) {
			return 2;
		}
		if( $t_agilemantis_rights[0]['participant'] ) {
			return 1;
		}
	}
	
	# this function looks for the highest user id from  mantis_user_table and returns it
	function getHighestUserId() {
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		$t_max = $this->executeQuery( "SELECT max(id) AS mid FROM $t_mantis_user_table GROUP BY enabled" );
		return $t_max[0]['mid'];
	}
	
	# set agileMantis User Rights to Mantis User
	function setAgileMantisUserRights( $p_user_id, $p_participant, $p_developer, 
										$p_administrator ) {
		$t_user = $this->getAdditionalUserFields( $p_user_id );
		
		if ( empty($p_participant) ) {
			$p_participant = 0;
		}
		if ( empty ($p_developer) ) {
			$p_developer = 0;
		}
		if ( empty ($p_administrator) ) {
			$p_administrator = 0;
		}
		
		// Den User gibt es schon -> update, falls nichts dagegen spricht
		if( $t_user[0]['user_id'] > 0 ) {
			$t_sql = "UPDATE gadiv_additional_user_fields 
							SET participant=" . db_param( 0 ) . ", 
							developer=" . db_param( 1 ) . ", 
							administrator=" . db_param( 2 ) . "  
							WHERE user_id=" . db_param( 3 );
			$t_params = array( $p_participant, $p_developer, $p_administrator, $p_user_id );
			# Der User ist neu -> insert
		} else {
			$t_sql = "INSERT INTO gadiv_additional_user_fields 
							SET user_id=" . db_param( 0 ) . ", 
							participant=" . db_param( 1 ) . ", 
							developer=" . db_param( 2 ) . ", 
							administrator=" . db_param( 3 ) . ", 
							expert=0";
			
			if (db_is_mssql()) {
				$t_sql = "INSERT INTO gadiv_additional_user_fields
							VALUES (" . db_param( 0 ) . "," 
							          . db_param( 1 ) . "," 
							          . db_param( 2 ) . "," 
							          . db_param( 3 ) . ",0)";
			}
			
			$t_params = array( $p_user_id, $p_participant, $p_developer, $p_administrator );
		}
		db_query_bound( $t_sql, $t_params );
	}

	function setExpert( $p_user_id, $p_expert ) {
		$t_sql = "UPDATE gadiv_additional_user_fields 
						SET expert=" . db_param( 0 ) . " 
						WHERE user_id=" . db_param( 1 );
		$t_params = array( $p_expert, $p_user_id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# Hilfsfunktionen zur Aenderung bereits erteilter Rechte
	# For each running Sprint we check for the roles of the user in the associated team.
	function getActiveRoles( $user_id, &$isOwner, &$isMaster, &$isDeveloper, &$isStakeholder ) {
		$isOwner = FALSE;
		$isMaster = FALSE;
		$isDeveloper = FALSE;
		$isStakeholder = FALSE;
		$t_sql="SELECT id,
				team_id,
				pb_id,
				name,
				description,
				status,
				daily_scrum,
				start,
				dispose as " . AGILEMANTIS_COMMIT_FIELD . ",enddate as " . AGILEMANTIS_END_FIELD . ",
				closed,
				unit_storypoints,
				unit_planned_work,
				unit_planned_task,
				workday_length
 	    FROM gadiv_sprints WHERE status <= 1";
		$rsSprints = $this->executeQuery( $t_sql );
		
		if( !empty( $rsSprints ) ) {
			foreach( $rsSprints as $num => $row1 ) {
				# Get all roles of the user in running Sprints
				$t_sql = "SELECT * 
							FROM gadiv_rel_team_user 
							WHERE team_id=" . db_param( 0 ) . " 
							AND user_id=" . db_param( 1 );
				$t_params = array( $row1['team_id'], $user_id );
				$rsRoles = $this->executeQuery( $t_sql, $t_params );
				
				if( !empty( $rsRoles ) ) {
					foreach( $rsRoles as $num => $row2 ) {
						$isOwner = $isOwner || ($row2['role'] == 1);
						$isMaster = $isMaster || ($row2['role'] == 2);
						$isDeveloper = $isDeveloper || ($row2['role'] == 3);
						$isStakeholder = $isStakeholder || ($row2['role'] == 4);
						$isStakeholder = $isStakeholder || ($row2['role'] == 5);
						$isStakeholder = $isStakeholder || ($row2['role'] == 6);
						$isStakeholder = $isStakeholder || ($row2['role'] == 7);
					}
				}
			}
		}
	}
	
	# The new rights participant, developer, admin can be 0 or 1.
	function checkChangeRightsAllowed( $user_id, $participant, $developer, $administrator ) {
		$isOwner = FALSE;
		$isMaster = FALSE;
		$isDeveloper = FALSE;
		$isStakeholder = FALSE;
		$this->getActiveRoles( $user_id, $isOwner, $isMaster, $isDeveloper, $isStakeholder );
		
		$participant_db = 0;
		$developer_db = 0;
		$administrator_db = 0;
		
		$user = $this->getAdditionalUserFields( $user_id );
		
		# if($user[0]['user_id'] > 0){	# if user exists..
		if( !empty( $user ) ) {
			$participant_db = ($user[0]['participant'] == true ? 1 : 0);
			$developer_db = ($user[0]['developer'] == true ? 1 : 0);
			$administrator_db = ($user[0]['administrator'] == true ? 1 : 0);
		}
		
		$hasOwnerMasterRight_db = (($participant_db == 1) || ($developer_db == 1));
		$hasDeveloperRight_db = ($developer_db == 1);
		
		$hasOwnerMasterRight = (($participant == 1) || ($developer == 1));
		$hasDeveloperRight = ($developer == 1);
		
		$loosesOwnerMasterRight = ($hasOwnerMasterRight_db && !$hasOwnerMasterRight);
		$loosesDeveloperRight = ($hasDeveloperRight_db && !$hasDeveloperRight);
		$loosesStakeholderRight = $loosesOwnerMasterRight;
		
		# At loss of Owner/Master rights
		if( ($isOwner || $isMaster) && $loosesOwnerMasterRight ) {
			return 5; # Hint: please first remove MasterOwner from team
		}
		
		# At loss of developer rights
		if( $isDeveloper && $loosesDeveloperRight ) {
			$hasTasks = $this->userHasRunningTasks( $user_id );
			if( $hasTasks == 1 )
				return 4; # Hint: please first remove Developer from tasks
			else
				return 3; # Ask: remove Developer from team?
		}
		
		# At loss of stakeholder rights
		if( $isStakeholder && $loosesStakeholderRight ) {
			return 2; # Ask: remove Stakeholer from team?
		}
		
		$changesRight = !(($participant_db == $participant) && ($developer_db == $developer) &&
			 ($administrator_db == $administrator));
		if( $changesRight ) {
			return 1; # A change occurs without restrictions
		}
		
		return 0; # nothing to do; no change of rights has occurred.
	} // end checkChangeRightsAllowed

	
	# check, if a user is assigned to any task in any non-closed sprint
	function userHasRunningTasks( $p_user_id ) {
		# open Sprints
		$rsSprints = $this->executeQuery( "SELECT id,
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
		 FROM gadiv_sprints WHERE status <= 1" );
		global $agilemantis_sprint;
		
		if( !empty( $rsSprints ) ) {
			foreach( $rsSprints as $num => $row1 ) {
				# User-Stories of sprint
				$rsStories = $agilemantis_sprint->getSprintStoriesSimple( 
					$row1['name'] );
				if( !empty( $rsStories ) ) {
					foreach( $rsStories as $num => $row2 ) {
						$t_sql = "SELECT COUNT(*) as cnt 
										FROM gadiv_tasks 
										WHERE us_id=" . db_param( 0 ) . " 
										AND developer_id=" . db_param( 1 ) . "
										GROUP BY us_id";
						$t_params = array( $row2['id'], $p_user_id );
						$rsTasks = $this->executeQuery( $t_sql, $t_params );
						if( $rsTasks[0]['cnt'] > 0 )
							return 1;
					}
				}
			}
		}
		return 0;
	}

	function getUserRights( $p_user_id, &$isParticipant, &$isDeveloper, &$isAdmin ) {
		$t_sql = "SELECT participant, developer, administrator 
						FROM gadiv_additional_user_fields 
						WHERE user_id=" . db_param( 0 );
		$t_params = array( $p_user_id );
		$t_rs = $this->executeQuery( $t_sql, $t_params );
		
		$isParticipant = 0;
		$isDeveloper = 0;
		$isAdmin = 0;
		if( !empty( $t_rs ) ) {
			$isParticipant = ($t_rs[0]['participant'] == true ? 1 : 0);
			$isDeveloper = ($t_rs[0]['developer'] == true ? 1 : 0);
			$isAdmin = ($t_rs[0]['administrator'] == true ? 1 : 0);
		}
	}
} // end class_agileuser


?>
