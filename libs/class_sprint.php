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

	#	This class will hold functions for agileMantis sprint management
	class gadiv_sprint extends gadiv_commonlib {

		var $sprint_id;
		var $pb_id;

		# get all sprints with sorting functions
		function getSprints($chron="",$show_closed_sprints=""){
			$addsql = "";
			$orderby = "";
			$startjoin = "";
			$removeStatus = 'WHERE status != 2';

			if(isset($_POST['show_all_sprints']) || ($_GET['klickStatus']==1 && $_POST['disable_click']!=1) || $show_closed_sprints == 1){
				if($_POST['show_all_sprints']==1 || ($_GET['klickStatus']==1 && $_POST['disable_click']!=1) || $show_closed_sprints == 1){
					$removeStatus = '';
				} else {
					$removeStatus = 'WHERE status != 2';
				}
			}
			if(isset($_GET['sort_by'])){
				if($_GET['sort_by']){
					switch($_GET['sort_by']){
						case 'id':
							if($_SESSION['order_id'] == 0){
								$orderby = "ORDER BY sname ASC";
								$_SESSION['order_id'] = 1;
							} else {
								$orderby = "ORDER BY sname DESC";
								$_SESSION['order_id'] = 0;
							}
							$_SESSION['order_rest'] = 0;
							$_SESSION['order_team'] = 0;
							$_SESSION['order_start'] = 0;
							$_SESSION['order_end'] = 0;
						break;
						case 'team':
							if($_SESSION['order_team'] == 0){
								$orderby = "ORDER BY t.name ASC";
								$_SESSION['order_team'] = 1;
							} else {
								$orderby = "ORDER BY t.name DESC";
								$_SESSION['order_team'] = 0;
							}
							$_SESSION['order_rest'] = 0;
							$_SESSION['order_id'] = 0;
							$_SESSION['order_start'] = 1;
							$_SESSION['order_end'] = 0;
						break;
						case 'rest':
							if($_SESSION['order_rest'] == 0 && $_GET['klickStatus'] == 0){
								$orderby = "ORDER BY `restaufwand` ASC";
							}
							if($_SESSION['order_rest'] == 0){
								$orderby = "ORDER BY `restaufwand` DESC";
								$_SESSION['order_rest'] = 1;
								$_SESSION['order_end'] = 1;
								$_SESSION['order_id'] = 0;
							} else {
								$orderby = "ORDER BY `restaufwand` ASC";
								$_SESSION['order_rest'] = 0;
								$_SESSION['order_end'] = 0;
								$_SESSION['order_id'] = 1;
							}
							$addsql = ",IF((ceil((UNIX_TIMESTAMP(end) - ".time().") / 86400)) < (ceil((UNIX_TIMESTAMP(end) - UNIX_TIMESTAMP(start)) / 86400)),(ceil((UNIX_TIMESTAMP(end) - ".time().") / 86400)),(ceil((UNIX_TIMESTAMP(end) - UNIX_TIMESTAMP(start)) / 86400))) AS restaufwand";
							$_SESSION['order_team'] = 0;
							$_SESSION['order_start'] = 0;
						break;
						case 'start':
							if($_SESSION['order_start'] == 0){
								$orderby = "ORDER BY start ASC";
								$_SESSION['order_start'] = 1;
							} else {
								$orderby = "ORDER BY start DESC";
								$_SESSION['order_start'] = 0;
							}
							$_SESSION['order_rest'] = 0;
							$_SESSION['order_id'] = 0;
							$_SESSION['order_team'] = 0;
							$_SESSION['order_end'] = 0;
						break;
						case 'pb':
							if($_SESSION['oder_pb'] == 0){
								$orderby = "ORDER BY pname ASC";
								$_SESSION['oder_pb'] = 1;
							} else {
								$orderby = "ORDER BY pname DESC";
								$_SESSION['oder_pb'] = 0;
							}
						break;
						case 'end':
						default:
							if($_SESSION['order_end'] == 0){
								$orderby = "ORDER BY end DESC";
								$_SESSION['order_end'] = 1;
								$_SESSION['order_rest'] = 1;
							} else {
								$orderby = "ORDER BY end ASC";
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
				$orderby = "ORDER BY end DESC, t.name ASC";
				$_SESSION['order'] = 0;
			}

			if($chron != ""){
				$orderby = "ORDER BY end DESC";
			}

			$sql = "SELECT s.id AS sid, s.name AS sname, team_id, s.status, s.end, s.start, p.name AS pname ".$addsql." FROM gadiv_sprints AS s LEFT JOIN gadiv_teams AS t ON t.id = s.team_id LEFT JOIN gadiv_productbacklogs AS p ON p.id = t.pb_id  ".$removeStatus." ".$orderby;
			return mysql_query($sql);
		}

		# get team information by id
		function getTeamById($id){
			$this->sql = "SELECT * FROM gadiv_teams WHERE id = ".$id;
			$team = $this->executeQuery();
			if(!empty($team)){return $team[0]['name'];}
		}

		# get all stories which are in a sprint 
		function getSprintStories($name,$user_id,$show_only_open_userstories){
			$this->getAdditionalProjectFields();

			if($show_only_open_userstories == 1){
				$addsql = ' AND bt.status < 80';
			}

			$request = array_merge($_GET, $_POST);

			if(!empty($_GET['sort_by']) && isset($_GET['sort_by'])){
				$direction = $_GET['direction'];
				$sort_by = $_GET['sort_by'];
			}

			if($this->getConfigUserValue('current_user_sprint_backlog_filter', $user_id) && empty($_GET['sort_by']) && !isset($_GET['sort_by'])){
				$sort_by = $this->getConfigUserValue('current_user_sprint_backlog_filter', $user_id);
			}

			if($this->getConfigUserValue('current_user_sprint_backlog_filter_direction', $user_id) && empty($_GET['sort_by']) && !isset($_GET['sort_by'])){
				$direction  = $this->getConfigUserValue('current_user_sprint_backlog_filter_direction', $user_id);
			}

			switch($sort_by){
				case 'summary':
					$orderby = " summary ".$direction.", name ".$direction.", target_version ".$direction.", sprint.value ".$direction.", id ".$direction;
				break;
				case 'target_version':
					$orderby = " name ".$direction." , target_version ".$direction.", sprint.value ".$direction.", id ".$direction;
				break;
				case 'rankingOrder':
					$orderby = " (rankingOrder.value = '') , (rankingOrder.value IS NULL) , ABS (rankingOrder.value) ".$direction.", name ".$direction.", sprint.value ".$direction.", id ".$direction."";
				break;
				case 'storypoints':
					$orderby = " (storypoints.value = '') , (storypoints.value IS NULL) , ABS (storypoints.value) ".$direction.", name ".$direction.", target_version ".$direction.", sprint.value ".$direction.", id ".$direction;
				break;
				case  'id':
					$orderby = "id ".$direction;
				break;
				default:
					$orderby = " name ".$direction." , target_version ".$direction.", sprint.value ".$direction.", id ".$direction;
				break;
			}

			$this->sql = "SELECT sprint.*, bt.*, pt.id AS pid, pt.name AS name, storypoints.value AS sp, rankingOrder.value AS ro FROM mantis_custom_field_string_table AS sprint LEFT JOIN mantis_custom_field_string_table AS storypoints ON storypoints.bug_id = sprint.bug_id LEFT JOIN mantis_custom_field_string_table AS rankingOrder ON rankingOrder.bug_id = sprint.bug_id LEFT JOIN mantis_bug_table AS bt ON bt.id = sprint.bug_id LEFT JOIN mantis_project_table AS pt ON bt.project_id = pt.id WHERE sprint.field_id = '".$this->spr."'  AND storypoints.field_id = '".$this->sp."' AND rankingOrder.field_id = '".$this->ro."' AND sprint.value = '".$name."' ".$addsql." ORDER BY  ".$orderby;

			return $this->executeQuery();
		}

		# count sprint stories
		function countSprintStories($sprint){
			$this->getAdditionalProjectFields();
			$this->sql = "SELECT * FROM mantis_custom_field_string_table AS sprint LEFT JOIN mantis_bug_table AS bt ON bt.id = sprint.bug_id WHERE sprint.field_id = '".$this->spr."' AND sprint.value = '".$sprint."'";
			return $this->executeQuery();
		}

		# get all tasks to all user stories in a sprint
		function getSprintTasks($us_id,$user_id=""){
			$addsql = "";
			if($user_id > 0){
				$addsql = " AND developer_id = ".$user_id;
			}
			$this->sql = "SELECT * FROM gadiv_tasks WHERE us_id = ".$us_id ." ".$addsql." ORDER BY id ASC";
			return $this->executeQuery();
		}

		# get sprint information by id
		function getSprintById(){
			if($this->sprint_id != ""){
				$sql = "SELECT * FROM gadiv_sprints WHERE name = '".$this->sprint_id."'";
				$result = mysql_query($sql);
				return mysql_fetch_assoc($result);
			}
		}

		# get sprint information by name
		function getSprintByName(){
			if($this->sprint_id != ""){
				$sql = "SELECT * FROM gadiv_sprints WHERE id = '".$this->sprint_id."'";
				$result = mysql_query($sql);
				return mysql_fetch_assoc($result);
			}
		}

		# add new sprint
		function newSprint(){
			$sql = "INSERT INTO gadiv_sprints SET name = '".$this->name."', pb_id = '".$this->pb_id."'";
			mysql_query($sql);
			$this->sprint_id = mysql_insert_id();

			$this->sql = "SELECT * FROM gadiv_sprints ORDER BY name ASC";
			$result = $this->executeQuery();

			foreach($result AS $num => $row){
				if($row['status'] != 2){
					$spr .= $row['name'].'|';
				}
			}

			$spr = substr($spr, 0,-1);

			$sql = "UPDATE mantis_custom_field_table SET possible_values = '".$spr."' WHERE name = 'Sprint'";
			mysql_query($sql);

			return $this->sprint_id;
		}

		# save / update sprint information
		function editSprint(){
			if($this->sprint_id == 0) {
				$this->sprint_id = $this->newSprint();
			}
			$sql = "UPDATE gadiv_sprints SET name = '".$this->name."',description = '".$this->description."', team_id = '".$this->team_id."',  start = '".$this->start."', end = '".$this->end."', pb_id = '".$this->pb_id."' WHERE id = ".$this->sprint_id;
			mysql_query($sql);
		}

		# delete selected sprint information
		function deleteSprint(){
			$sql = "DELETE FROM gadiv_sprints WHERE id = ".$this->id;
			mysql_query($sql);

			$this->sql = "SELECT * FROM gadiv_sprints ORDER BY name ASC";
			$result = $this->executeQuery();

			foreach($result AS $num => $row){
				if($row['status'] != 2){
					$spr .= $row['name'].'|';
				}
			}

			$spr = substr($spr, 0,-1);

			$sql = "UPDATE mantis_custom_field_table SET possible_values = '".$spr."' WHERE name = 'Sprint'";
			mysql_query($sql);
		}

		# change sprint status by sprint id
		function setSprintStatus($status,$sprint_id){
			$sql = "UPDATE gadiv_sprints SET status = '".$status."' WHERE id = '".$sprint_id."'";
			$ergebnis = mysql_query($sql);

			if($status == 2){
				$this->sql = "SELECT * FROM gadiv_sprints ORDER BY name ASC";
				$result = $this->executeQuery();

				foreach($result AS $num => $row){
					if($row['status'] != 2){
						$spr .= $row['name'].'|';
					}
				}

				$spr = substr($spr, 0,-1);

				$sql = "UPDATE mantis_custom_field_table SET possible_values = '".$spr."' WHERE name = 'Sprint'";
				mysql_query($sql);
			}

			if($ergebnis == true){
				return 1;
			} else {
				return 0;
			}
		}

		# checks if sprint name is unique or not
		function sprintnameisunique(){
			if($this->sprint_id > 0){
				$addsql = " AND id != ".$this->sprint_id;
			}
			$this->sql = 'SELECT count(*) AS sprints FROM gadiv_sprints WHERE name LIKE "'.$_POST['name'].'"'.$addsql;
			$result = $this->executeQuery();
			if($result[0]['sprints'] > 0){
				return false;
			}
			return true;
		}

		# checks if two sprints are crossing at the end or beginning of a sprint
		function crossingSprints($timestamp, $team_id){
			if($this->sprint_id > 0){$addsql = " AND id != ".$this->sprint_id;}
			$this->sql = "SELECT name FROM gadiv_sprints WHERE team_id = '".$team_id."'".$addsql." AND start <= '".$timestamp."' AND end >= '".$timestamp."'";
			$result = $this->executeQuery();
			if($result[0]['name']){return true;}
			return false;
		}

		# check if previous sprint is already closed before committing a new one
		function previousSprintIsClosed($team_id,$sprint_id){
			$this->sql = "SELECT count(*) AS amount FROM gadiv_sprints WHERE team_id = '".$team_id."' AND status != 2 AND status != 0 AND id != '".$sprint_id."'";
			$result = $this->executeQuery();
			if($result[0]['amount'] > 0){
				return false;
			} else {
				return true;
			}
		}

		# get product backlog name by team information
		function getProductBacklogByTeam($team_id){
			$this->sql = "SELECT pb.name AS pname FROM gadiv_sprints AS s LEFT JOIN gadiv_teams AS t ON t.id = s.team_id LEFT JOIN gadiv_productbacklogs AS pb ON pb.id = t.pb_id WHERE team_id = '".$team_id."'";
			$result = $this->executeQuery();
			return $result[0]['pname'];
		}

		# checks if the current sprint has still user stories to do
		function sprintHasUserStories($name){
			$this->getAdditionalProjectFields();
			$this->sql = "SELECT count(*) AS count_userstories FROM mantis_custom_field_string_table WHERE value = '".$name."' AND field_id = '".$this->spr."'";
			$result = $this->executeQuery();
			if($result[0]['count_userstories'] > 0){
				return true;
			} else {
				return false;
			}
		}

		# get the current sprint for the current logged in user
		function getCurrentUserSprint($user_id){
			$this->sql = "SELECT * FROM gadiv_rel_team_user AS rtu LEFT JOIN gadiv_sprints AS s ON s.team_id = rtu.team_id WHERE user_id = '".$user_id."' AND STATUS !=2 ORDER BY start LIMIT 1";
			return $this->executeQuery();
		}

		# count all sprints belonging to one user
		function countUserSprints($user_id){
			$sql = "SELECT count(DISTINCT(s.team_id)) AS sprints FROM gadiv_rel_team_user AS tu LEFT JOIN gadiv_sprints AS s ON s.team_id = tu.team_id WHERE user_id = '".$user_id."' AND s.name IS NOT NULL".$sql_add;
			$result = mysql_query($sql);
			$user = mysql_fetch_assoc($result);
			return $user['sprints'];
		}

		# check if all tasks and user stories are resolved or closed in a sprint
		function allTasksAndStoriesAreClosed($name){

			$this->getAdditionalProjectFields();
			$this->sql = "
				SELECT mbt.status AS userstory_status, t.status AS task_status FROM mantis_custom_field_string_table AS mfst
				LEFT JOIN gadiv_tasks AS t ON mfst.bug_id = t.us_id
				LEFT JOIN mantis_bug_table AS mbt ON mfst.bug_id = mbt.id
				WHERE mfst.value = '".$name."'
				AND mfst.field_id = '".$this->spr."'
			";
			$result = $this->executeQuery();
			if(!empty($result)){
				foreach($result AS $num => $row){
					if($row['userstory_status'] < 80 || ($row['task_status'] < 4 && $row['task_status'] != NULL)){
						return false;
					}
				}
			}
			return true;
		}

		# get all sprints which are not resolved or closed by the selected team
		function getUndoneSprintsByTeam($team_id, $sprint_id){
			$this->sql = "SELECT * FROM gadiv_sprints WHERE team_id = '".$team_id."' AND status < 2 AND id != '".$sprint_id."'";
			return $this->executeQuery();
		}

		# collect all information when sprint is committed and save to database
		function confirmInformation($id, $unit_sp, $unit_wu, $unit_wt, $ld){
			$sql = "UPDATE gadiv_sprints SET unit_storypoints = '".$unit_sp."', unit_planned_work = '".$this->getUnitId($unit_wu)."', unit_planned_task = '".$this->getUnitId($unit_wt)."', workday_length = '".$ld."', commit = '".date("Y-m-d H:i:s")."' WHERE id = '".$id."'";
			mysql_query($sql);
		}

		
		# count all team members who participate in a sprint
		function getCountSprintTeamMember($team_id){
			$sql = "SELECT COUNT(*) AS developer FROM gadiv_rel_team_user WHERE team_id = '".$team_id."' AND role = '3'";
			$result = mysql_query($sql);
			$team = mysql_fetch_assoc($result);
			return $team['developer'];
		}

		# collect all information when a sprint is closed and save to database
		function closeInformation($id, $usps, $uwus, $uwts, $lds){

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
			$userstories = $this->countSprintStories($sprint['name']);

			if(!empty($userstories)){

				# amount of user stories in a sprint CU(S)
				$cus = count($userstories);

				foreach($userstories AS $num => $row){

					# User Story information
					$story = $this->checkForUserStory($row['id']);

					# amount of task storypoints SP(S)
					$sps += $this->getStoryPoints($row['id']);

					# Task to user stories
					$tasks = $this->getSprintTasks($row['id']);
					if(!empty($tasks)){

						# amount of tasks in a sprint CT(S)
						$cts += count($tasks);
						foreach($tasks AS $key => $value){
							if(!in_array($value['developer_id'], $developer)){
								$developer[] = $value['developer_id'];
							}

							$wps += $value['planned_capacity'];
							$wes += $value['performed_capacity'];

						}
					}

					# amount of splitted user stories CSUS(S)
					if($this->isSplittedStory($row['id'])){

						$new_story = $this->getSplittedStory($row['id']);
						$new_userstory = $this->checkForUserStory($row['id']);
						$wps += $new_story['wmu'];

						# amount of splitted user stories CSU(S)
						$csus++;

						# amount of story points in splitted user stories CSPUS(S)
						$spus += $story['storypoints'];
						$tasks = $this->getSprintTasks($new_story['new_userstory_id']);
						foreach($tasks AS $key => $value){

							# work moved in splitted user stories WM(S)
							$wms += $value['rest_capacity'];

						}
					}
				}
			}
			if($sprint['unit_planned_task'] == '3'){
				$spm = $wms;
			} else {
				$spm = 0;
			}

			# amount of developers who worked at least on a task in this sprint CDV(TD(S))
			$cdv = count($developer);

			# amount of developers in a team CDVT(S)
			$cdvt = $this->getCountSprintTeamMember($sprint['team_id']);

			# developer capacities of developers who worked at least on a task in this sprint K(S)
			if(!empty($developer)){
				foreach($developer AS $key => $value){
					$sql = "SELECT sum( capacity ) AS developer FROM gadiv_rel_user_team_capacity WHERE user_id = '".$value."' AND team_id = '".$sprint['team_id']."' AND date >= '".$sprint['start']."' AND date <= '".$sprint['end']."'";
					$result = mysql_query($sql);
					$capacity = mysql_fetch_assoc($result);
					$ks += $capacity['developer'];
				}
			}


			# total developer capacity of all team developers K(TD, DV, Z)
			$sql = "SELECT sum( capacity ) AS total_cap FROM gadiv_rel_user_team_capacity WHERE team_id = '".$sprint['team_id']."' AND date >= '".$sprint['start']."' AND date <= '".$sprint['end']."'";
			$result = mysql_query($sql);
			$team = mysql_fetch_assoc($result);
			$kds = $team['total_cap'];

			$sql = "INSERT INTO gadiv_rel_sprint_closed_information SET sprint_id = '".$id."',
																		count_user_stories = '".$cus."',
																		count_task_sprint = '".$cts."',
																		count_splitted_user_stories_sprint = '".$csus."',
																		storypoints_sprint = '".$sps."',
																		storypoints_in_splitted_user_stories = '".$spus."',
																		work_planned_sprint = '".$wps."',
																		work_performed = '".$wes."',
																		work_moved = '".$wms."',
																		storypoints_moved = '".$spm."',
																		count_developer_team = '".$cdvt."',
																		total_developer_capacity = '".$kds."',
																		count_developer_team_task = '".$cdv."',
																		total_developer_capacity_task = '".$ks."'";
			mysql_query($sql);

			$sql = "UPDATE gadiv_sprints SET closed	= '".date('Y')."-".date('m')."-".date('d')." ".date('H').":".date('i').":".date('s')."' WHERE id = '".$id."'";
			mysql_query($sql);
		}

	}
?>