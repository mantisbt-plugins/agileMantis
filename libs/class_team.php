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
		function newTeam(){
			$sql = "INSERT INTO gadiv_teams SET name = '".htmlspecialchars($this->name)."'";
			mysql_query($sql);
			$this->id = mysql_insert_id();
			return mysql_insert_id();
		}

		# edits a Team and if there is not $this->id, create a new one and edit additional information for the new team
		function editTeam(){
			if($this->id == 0) {
				$this->id = $this->newTeam();
			}
			$sql = "UPDATE gadiv_teams SET name = '".htmlspecialchars($this->name)."', description = '".htmlspecialchars($this->description)."', pb_id = '".$this->product_backlog."', daily_scrum = ".(int) $this->daily_scrum." WHERE id = ".$this->id;
			mysql_query($sql);
		}

		
		# deletes a team from the database
		function deleteTeam($id){
			$sql = "DELETE FROM gadiv_teams WHERE id = ".$id;
			mysql_query($sql);
		}

		# get all Teams with sorting options
		function getTeams(){

			if($_GET['sort_by']){
				if($_SESSION['order'] == 0){
					$_SESSION['order'] = 1;
					$direction = 'ASC';
				} else {
					$_SESSION['order'] = 0;
					$direction = 'DESC';
				}
				switch($_GET['sort_by']){
					case 'product_backlog' :
						$orderby = "LEFT JOIN gadiv_productbacklogs AS pb ON pb.id = t.pb_id ORDER BY pb.name ".$direction;
					break;
					case 'product_owner' :
						$addsql = ', ut.username';
						$orderby = "LEFT JOIN gadiv_rel_team_user AS utc ON utc.team_id = t.id LEFT JOIN mantis_user_table AS ut ON ut.id = utc.user_id WHERE role LIKE '%1%' ORDER BY ut.username ".$direction;
					break;
					case 'scrum_master' :
						$addsql = ', ut.username';
						$orderby = "LEFT JOIN gadiv_rel_team_user AS utc ON utc.team_id = t.id LEFT JOIN mantis_user_table AS ut ON ut.id = utc.user_id WHERE role LIKE '%2%' ORDER BY ut.username ".$direction;
					break;
					case 'description':
						$orderby = "ORDER BY description ".$direction;
					break;
					case 'name':
					default:
						$orderby = "ORDER BY name ".$direction;
				}
			}

			if(!$_GET['sort_by']){
				$orderby = "ORDER BY name ASC";
				$_SESSION['order'] = 1;
			}

			$this->sql = "SELECT t.id AS id, t.pb_id AS product_backlog, t.name AS name, t.description AS description ".$addsql." FROM gadiv_teams AS t ".$orderby;
			return $this->executeQuery();
		}

		
		# checks wether the entered team name is unique or not
		function isTeamNameUnique(){
			$this->sql = "SELECT count(*) AS tnz FROM gadiv_teams WHERE name LIKE '".$this->name."' AND id != '".$this->id."'";
			$isTeam = $this->executeQuery();
			if($isTeam[0]['tnz'] > 0 ){
				return false;
			} else {
				return true;
			}
		}

		#	with the help of this function only complete Teams will be returned. Firstly all Team are loaded from the database and secondly
		#	all team members are loaded from those teams. Every Team will be checked, if it has a Product Owner, a Scrum Master and at least
		#	one developer. If the team is "complete" the function will return a filled array and if the team is not complete it will
		#	return an empty one.
		function getCompleteTeams(){
			$teamdata = $this->getTeams();
			foreach($teamdata AS $num => $row){
				$this->sql = "SELECT count(role) AS product_owner FROM gadiv_rel_team_user WHERE team_id = '".$row['id']."' AND role LIKE '%1%'";
				$prowner = $this->executeQuery();

				$this->sql = "SELECT count(role) AS scrum_master FROM gadiv_rel_team_user WHERE team_id = '".$row['id']."' AND role LIKE '%2%'";
				$scmaster = $this->executeQuery();

				$this->sql = "SELECT count(role) AS developer FROM gadiv_rel_team_user WHERE team_id = '".$row['id']."' AND role LIKE '%3%'";
				$developer = $this->executeQuery();

				if($scmaster[0]['scrum_master'] > 0 && $prowner[0]['product_owner'] > 0 && $developer[0]['developer'] > 0 && $row['product_backlog'] > 0){
					$teams[$num]['id'] 				= $row['id'];
					$teams[$num]['name'] 			= $row['name'];
					$teams[$num]['product_backlog'] = $row['product_backlog'];
				}
			}
			return $teams;
		}

		# get all team users
		function allTeamsByUser($user_id){
			$this->sql = "SELECT  DISTINCT(team_id) FROM gadiv_rel_team_user WHERE user_id = '".$user_id."'";
			return $this->executeQuery();
		}


		# get the current Product Backlog name which is processed by a team and return it
		function getTeamBacklog($id){
			$this->sql = "SELECT pb.name, pb.id, t.pb_id FROM gadiv_teams AS t LEFT JOIN gadiv_productbacklogs AS pb ON t.pb_id = pb.id WHERE pb.id = ".$id;
			$pbName = $this->executeQuery();
			return $pbName[0]['name'];
		}

		# looks for the current Team-User of a team and returns the name
		function getTeamUserByBacklogName($name){
			$this->sql = "SELECT username FROM mantis_user_table WHERE realname LIKE '%".$name."%'";
			$result = $this->executeQuery();
			return $result[0]['username'];
		}


		# get the current Product Owner of one team
		function getTeamProductOwner(){
			$this->sql = "SELECT ut.id AS user_id FROM gadiv_rel_team_user AS tu LEFT JOIN mantis_user_table AS ut ON tu.user_id = ut.id WHERE role = 1 AND team_id = ".$this->id." ORDER BY username ASC";
			$result = $this->executeQuery();
			return $result[0]['user_id'];
		}


		
		# get the current Scrum Master of one team
		function getTeamScrumMaster(){
			$this->sql = "SELECT ut.id AS user_id FROM gadiv_rel_team_user AS tu LEFT JOIN mantis_user_table AS ut ON tu.user_id = ut.id WHERE role = 2 AND team_id = ".$this->id." ORDER BY username ASC";
			$result = $this->executeQuery();
			return $result[0]['user_id'];
		}


		# get all developer of one team
		function getTeamDeveloper(){
			$this->sql = "SELECT * FROM gadiv_rel_team_user AS tu LEFT JOIN mantis_user_table AS ut ON tu.user_id = ut.id WHERE role = 3 AND team_id = ".$this->id." AND id IS NOT NULL ORDER BY username ASC";
			return $this->executeQuery();
		}

		
		# get all customers of one team
		function getTeamCustomer(){
			$this->sql = "SELECT * FROM gadiv_rel_team_user AS tu LEFT JOIN mantis_user_table AS ut ON tu.user_id = ut.id WHERE role = 4 AND team_id = ".$this->id." ORDER BY username ASC";
			return $this->executeQuery();
		}

		
		# get all product user of one team
		function getTeamProductUser(){
			$this->sql = "SELECT * FROM gadiv_rel_team_user AS tu LEFT JOIN mantis_user_table AS ut ON tu.user_id = ut.id WHERE role = 5 AND team_id = ".$this->id." ORDER BY username ASC";
			return $this->executeQuery();
		}

		# get all manager of one team
		function getTeamManager(){
			$this->sql = "SELECT * FROM gadiv_rel_team_user AS tu LEFT JOIN mantis_user_table AS ut ON tu.user_id = ut.id WHERE role = 6 AND team_id = ".$this->id." ORDER BY username ASC";
			return $this->executeQuery();
		}

		# calculate capacity from one team member
		function getTeamMemberCapacity($user_id,$date_start,$date_end){
			$this->sql = "SELECT sum( capacity ) AS total_cap FROM `gadiv_rel_user_availability` WHERE user_id = '".$user_id."' AND date >= '".$date_start."' AND date <= '".$date_end."'";
			$result = $this->executeQuery();
			if($result[0]['total_cap'] != ""){
				return $this->executeQuery();
			}
		}

		
		# deletes all team member from a team
		function deleteTeamMember($team_id){
			$sql = "DELETE FROM gadiv_rel_team_user WHERE team_id = ".$team_id;
			mysql_query($sql);
		}


		# deletes team member by role_id from a team
		function deleteTeamRoleMember($team_id,$role_id){
			$sql = "DELETE FROM gadiv_rel_team_user WHERE team_id = '".$team_id."' AND role = '".$role_id."'";
			mysql_query($sql);
		}

		
		# adds a new user to a team with its user role
		function addTeamMember($user_id, $team_id,$role_id){
			$sql = "DELETE FROM gadiv_rel_team_user WHERE user_id = '".$user_id."' AND team_id = '".$team_id."' AND role = '".$role_id."'";
			mysql_query($sql);

			$sql = "INSERT INTO gadiv_rel_team_user SET user_id = '".$user_id."', team_id = '".$team_id."', role = '".$role_id."'";
			mysql_query($sql);
		}

		# deletes a user from a team by role
		function deleteSelectedTeamMember($team_id, $user_id, $role_id){
			$sql = "DELETE FROM gadiv_rel_team_user WHERE user_id = '".$user_id."' AND team_id = '".$team_id."' AND role = '".$role_id."'";
			mysql_query($sql);
		}

		# deletes a user from a team by role
		function deleteScrumDeveloperFromTeams($user_id){
			$sql = "DELETE FROM gadiv_rel_team_user WHERE user_id = '".$user_id."' AND role = '3'";
			mysql_query($sql);
		}

		# Gets the Team-User id and add this user to one team
		function insertProductBacklogTeamMember($team_id,$backlog_name){
			$sql 		= 	"SELECT * FROM mantis_user_table WHERE username LIKE '%".$backlog_name."%'";
			$result 	= 	mysql_query($sql);
			$user 		=	mysql_fetch_assoc($result);
			$user_id 	= 	$user['id'];
			$this->addTeamMember($user_id, $team_id, 7);
		}

		# inserts capacity values for users of one team with a defined date
		function insertTeamUserCapacity($team_id, $user_id, $date, $capacity){
			$sql = "DELETE FROM gadiv_rel_user_team_capacity WHERE team_id = '".$team_id."' AND user_id = '".$user_id."' AND date = '".$date."'";
			mysql_query($sql);
			$sql = "INSERT INTO gadiv_rel_user_team_capacity SET team_id = '".$team_id."', user_id = '".$user_id."',date = '".$date."', capacity = '".$capacity."'";
			mysql_query($sql);
		}

		# checks wether a user has open tasks left in a team or not.
		function memberHasOpenTasks($team_id,$user_id){
			$this->getAdditionalProjectFields();
			$this->sql = "SELECT min( id ) AS currentsprint,name FROM gadiv_sprints WHERE team_id = '".$team_id."' AND status != 2";
			$get = $this->executeQuery();
			if($get[0]['currentsprint'] > 0 && !is_null($get[0]['currentsprint'])){
				$this->sql = "SELECT * FROM mantis_custom_field_string_table WHERE value = '".$get[0]['name']."' AND field_id = '".$this->spr."'";
				$userstories = $this->executeQuery();
				if(!empty($userstories[0]['bug_id'])){
					for($i=0; $i < count($userstories); $i++){
						$this->sql = "SELECT * FROM gadiv_tasks WHERE us_id = '".$userstories[$i]['bug_id']."' AND developer_id = '".$user_id."'";
						$tasks = $this->executeQuery();
						if(!empty($tasks)){
							foreach($tasks AS $num =>$row){
								if($row['status'] != 5){
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
		function countMemberTeams($user_id){
			$sql = "SELECT count(DISTINCT team_id) AS teams FROM gadiv_rel_team_user WHERE user_id = '".$user_id."'";
			$result = mysql_query($sql);
			$user = mysql_fetch_assoc($result);
			return $user['teams'];
		}

		# checks if user is scrum master
		function isScrumMaster($team_id, $user_id){
			$sql = "SELECT * FROM gadiv_rel_team_user WHERE team_id = '".$team_id."' AND user_id = '".$user_id."' AND role = 2";
			$result = mysql_query($sql);
			if(mysql_num_rows($result) == 1){
				return true;
			}
			return false;
		}

		# checks if user is developer
		function isDeveloper($team_id, $user_id){
			$sql = "SELECT * FROM gadiv_rel_team_user WHERE team_id = '".$team_id."' AND user_id = '".$user_id."' AND role = 3";
			$result = mysql_query($sql);
			if(mysql_num_rows($result) == 1){
				return true;
			}
			return false;
		}

		# get product backlog information by team id
		function getBacklogByTeam($team_id){
			$this->sql = "SELECT DISTINCT(pb_id) FROM gadiv_teams WHERE id IN(".$team_id.")";
			return $this->executeQuery();
		}

		# get user, product backlog and team role information 
		function getProductBacklogTeamRole($product_backlog, $user_id, $role){
			$this->sql = "SELECT * FROM gadiv_rel_team_user tu LEFT JOIN gadiv_teams t ON tu.team_id = t.id LEFT JOIN gadiv_productbacklogs pb ON t.pb_id = pb.id WHERE tu.user_id = '".$user_id."' AND pb.name = '".$product_backlog."' AND role = '".$role."'";
			return $this->executeQuery();
		}
		
		function getTotalTeamMemberCapacityBySprint($user_id,$sprint_name){
			$this->sql = "SELECT start,status, end, team_id FROM gadiv_sprints WHERE name = '".$sprint_name."'";
			$result = $this->executeQuery();

			if($result[0]['status'] == 2){
				return 0;
			}

			if($result[0]['status'] == 1){
				$date_start = date('Y-m-d');
			}

			if($result[0]['status'] == 0){
				$date_start = $result[0]['start'];
			}

			$this->sql = "SELECT sum(capacity) AS capacity FROM `gadiv_rel_user_team_capacity` WHERE user_id = '".$user_id."' AND team_id = '".$result[0]['team_id']."' AND date >= '".$date_start."' AND date <= '".$result[0]['end']."'";
			$result = $this->executeQuery();

			return $result[0]['capacity'];
		}
	}
?>