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
	
	
	#	This class will hold functions for agileMantis user management
	class gadiv_agileuser extends gadiv_commonlib {

		# get all agileMantis users with filter options
		function getAgileUser($only_developer = false){
			if($_GET['filter']){
				$addsql = "AND username LIKE '".$_GET['filter']."%'";
			}

			if($only_developer == false){
				$condition = '(participant = 1 OR developer = 1 OR administrator = 1)';
			} else {
				$condition = 'developer = 1';
			}

			$this->sql = "SELECT * FROM mantis_user_table AS ut LEFT JOIN gadiv_additional_user_fields AS auf ON ut.id = auf.user_id WHERE ".$condition." ".$addsql." ORDER by username ASC";
			return $this->executeQuery();
		}

		# load all mantis user with filter and sorting options
		function getAllUser(){
			if($_GET['filter'] != ""){
				$addsql = "AND username LIKE '".mysql_real_escape_string($_GET['filter'])."%' ";
			}
			if($_GET['sort_by']){
				if($_SESSION['order'] == 0){
					$_SESSION['order'] = 1;
					$direction = 'ASC';
				} else {
					$_SESSION['order'] = 0;
					$direction = 'DESC';
				}
				switch($_GET['sort_by']){
					case 'realname' :
						$orderby = "ORDER BY realname ".$direction;
					break;
					case 'email':
						$orderby = "ORDER BY email ".$direction;
					break;
					case 'username':
					default:
						$orderby = "ORDER BY username ".$direction;
				}
			}

			if(!$_GET['sort_by']){
				$orderby = "ORDER BY username ASC";
				$_SESSION['order'] = 1;
			}

			$this->sql = "SELECT id,username,realname,email FROM mantis_user_table WHERE 1 ".$addsql.$orderby;
			return $this->executeQuery();
		}

		# authenticate one agileMantis user and configure user rights
		function authUser(){
			$this->sql = "SELECT * FROM gadiv_additional_user_fields WHERE user_id = ".auth_get_current_user_id();
			$agilemantis_rights = $this->executeQuery();
			$user_right = 0;
			if($agilemantis_rights[0]['participant']){
				$user_right = 1;
			}
			if($agilemantis_rights[0]['administrator']){
				$user_right = 2;
			}
			if($agilemantis_rights[0]['developer']){
				$user_right = 3;
			}

			return $user_right;
		}

		
		# this function looks for the highest user id from  mantis_user_table and returns it
		function getHighestUserId(){
			$this->sql = "SELECT max(id) AS mid FROM mantis_user_table";
			$max = $this->executeQuery();
			return $max[0]['mid'];
		}

		# set agileMantis User Rights to Mantis User
		function setAgileMantisUserRights($user_id, $participant, $developer, $administrator){
			$user = $this->getAdditionalUserFields($user_id);
			if($user[0]['user_id'] > 0){
				$sql = "UPDATE gadiv_additional_user_fields SET participant = ".$participant.", developer = ".$developer.", administrator = ".$administrator."  WHERE user_id = '".$user_id."'";
			} else {
				$sql = "INSERT INTO gadiv_additional_user_fields SET user_id = '".$user_id."', participant = ".$participant.", developer = ".$developer.", administrator = ".$administrator;
			}
			mysql_query($sql);
		}
	}
?>
