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
		var $sql;
		var $lastresult;
		var $rnorder;
		var $userorder;
		var $tech;
		var $bug_arr;
		var $unit;
		var $custom_field_arr;

		# execute query function which returns a multi-dimensional array as result
		function executeQuery(){
			if($result = mysql_query($this->sql)){
				$resultSet = array();
				if ($result === TRUE) {
					$resultSet = true;
				} else {
				 while ($rs = mysql_fetch_assoc($result)) {
					if (!empty($rs)) {$resultSet[]=$rs;};
				 };
				}
				return $resultSet;
			}
		}
		
		# count current user sessions
		function countSessions(){
			$sql = "SELECT count(session_id) AS sessions FROm gadiv_additional_user_fields WHERE session_id != ''";
			$result = mysql_query($sql);
			$amountOf = mysql_fetch_assoc($result);
			return $amountOf['sessions'];
		}

		# redirects a user to a specific page, sprint backlog or taskboard
		function forwardReturnToPage($page_name){

			if($_POST['fromDailyScrum'] == 1) {
				$header = "Location: ".plugin_page('daily_scrum_meeting.php')."&sprintName=".urlencode($_POST['sprintName']);
			} else {

				if($_POST['fromSprintBacklog'] == 1 && $_POST['fromTaskPage'] != 1){
					$header = "Location: ".plugin_page('sprint_backlog.php')."&sprintName=".urlencode($_POST['sprintName']);
				}

				if($_POST['fromTaskboard'] == 1) {
					$header = "Location: ".plugin_page('taskboard.php')."&sprintName=".urlencode($_POST['sprintName']);
				}

				if($_POST['fromProductBacklog'] == 1) {
					$header = "Location: ".plugin_page('product_backlog.php')."&productBacklogName=".$_POST['productBacklogName'];
				}

				if($_POST['fromSprintBacklog'] == 0 && $_POST['fromTaskboard'] == 0 && $_POST['fromProductBacklog'] == 0){
					$header = "Location: ".plugin_page($page_name);
				}

				if($_POST['fromSprintBacklog'] == 1 && $_POST['fromTaskPage'] == 1){
					$header = "Location: ".plugin_page($page_name);
				}

			}

			return $header;
		}

		# get config value from database
		function getConfigValue($config_id){
			$sql = "SELECT * FROM mantis_config_table WHERE config_id = '".$config_id."'";
			$result = mysql_query($sql);
			$config = mysql_fetch_assoc($result);
			return $config['value'];
		}

		# get config value by user id from database
		function getConfigUserValue($config_id, $user_id){
			$sql = "SELECT * FROM mantis_config_table WHERE config_id = '".$config_id."' AND user_id = '".$user_id."'";
			$result = mysql_query($sql);
			$config = mysql_fetch_assoc($result);
			return $config['value'];
		}

		# set config value especially for one user into database
		function setConfigValue($config_id,$user_id,$value){
			$sql = "UPDATE mantis_config_table SET value = '".$value."' WHERE config_id = '".$config_id."' AND user_id = '".$user_id."'";
			mysql_query($sql);
		}

		# user gets marked when capacity is exceeded
		function setUserAsMarkType($id,$marking){
			$sql = "UPDATE `gadiv_rel_user_availability_week` SET marked = ".$marking." WHERE user_id = '".$id."'";
			mysql_query($sql);
		}

		# get password from a specific user
		function getUserPassword($user_id){
			$sql = "SELECT * FROM mantis_user_table WHERE id = '".$user_id."'";
			$result = mysql_query($sql);
			$user = mysql_fetch_assoc($result);
			return $user['password'];
		}

		# get user information by id
		function getUserById($user_id){
			$sql = "SELECT username FROM mantis_user_table WHERE id = '".$user_id."'";
			$result = mysql_query($sql);
			$user = mysql_fetch_assoc($result);
			return $user['username'];
		}

		# get user information by name
		function getUserIdByName($username){
			$sql = "SELECT id FROM mantis_user_table WHERE username = '".$username."'";
			$result = mysql_query($sql);
			$user = mysql_fetch_assoc($result);
			return $user['id'];
		}
		
		# get user real name by user id
		function getUserRealName($user_id){
			$sql = "SELECT realname FROM mantis_user_table WHERE id = '".$user_id."'";
			$result = mysql_query($sql);
			$user = mysql_fetch_assoc($result);
			return $user['realname'];
		}

		# get all additional user fields from mantis database
		function getAdditionalUserFields($user_id){
			$this->sql = "SELECT * FROM gadiv_additional_user_fields WHERE user_id = ".$user_id;
			return $this->executeQuery();
		}

		# get all additional agileMantis custom field ids from database
		# and return as array
		function getAdditionalProjectFields(){
			$this->sql = "SELECT id FROM mantis_custom_field_table WHERE name = 'BusinessValue'";
			$result = $this->executeQuery();
			$this->bv = $result[0]['id'];

			$this->sql = "SELECT id FROM mantis_custom_field_table WHERE name = 'ProductBacklog'";
			$result = $this->executeQuery();
			$this->pb = $result[0]['id'];

			$this->sql = "SELECT id FROM mantis_custom_field_table WHERE name = 'Storypoints'";
			$result = $this->executeQuery();
			$this->sp = $result[0]['id'];

			$this->sql = "SELECT id FROM mantis_custom_field_table WHERE name = 'Sprint'";
			$result = $this->executeQuery();
			$this->spr = $result[0]['id'];

			$this->sql = "SELECT id FROM mantis_custom_field_table WHERE name = 'RankingOrder'";
			$result = $this->executeQuery();
			$this->ro = $result[0]['id'];

			$this->sql = "SELECT id FROM mantis_custom_field_table WHERE name = 'Presentable'";
			$result = $this->executeQuery();
			$this->pr = $result[0]['id'];

			$this->sql = "SELECT id FROM mantis_custom_field_table WHERE name = 'Technical'";
			$result = $this->executeQuery();
			$this->tech = $result[0]['id'];

			$this->sql = "SELECT id FROM mantis_custom_field_table WHERE name = 'InReleaseDocu'";
			$result = $this->executeQuery();
			$this->rld = $result[0]['id'];

			$this->sql = "SELECT id FROM mantis_custom_field_table WHERE name = 'PlannedWork'";
			$result = $this->executeQuery();
			$this->pw = $result[0]['id'];

			$this->sql = "SELECT id FROM mantis_custom_field_table WHERE name = 'PlannedWorkUnit'";
			$result = $this->executeQuery();
			$this->un = $result[0]['id'];
		}

		# check if project is in a product backlog
		function projectHasBacklogs($project_id){
			$this->sql = "SELECT count(*) AS projects FROM `gadiv_rel_productbacklog_projects` WHERE project_id = '".$project_id."'";
			$result = $this->executeQuery();
			if($result[0]['projects'] > 0){
				return false;
			}
			return true;
		}

		# set user story confirmation status
		function setConfirmationStatus($us_id){
			$sql = "UPDATE mantis_bug_table SET status = 50 WHERE id = ".$us_id;
			mysql_query($sql);
		}

		# set a defined tracker / user story status
		function setTrackerStatus($id, $status){
			$sql = "UPDATE mantis_bug_table SET status = '".$status."' WHERE id = '".$id."'";
			mysql_query($sql);
		}

		# get all product backlogs with filter and sorting options
		function getProductBacklogs($id=""){
			if($id !=""){$addsql = " AND id = ".$id;}

			if($_GET['sort_by']){
				if($_SESSION['order'] == 0){
					$_SESSION['order'] = 1;
					$direction = 'ASC';
				} else {
					$_SESSION['order'] = 0;
					$direction = 'DESC';
				}
				switch($_GET['sort_by']){
					case 'description':
						$orderby = "ORDER BY description ".$direction;
					break;
					case 'name' :
					default:
						$orderby = "ORDER BY name ".$direction;
				}
			}

			if(!$_GET['sort_by']){
				$orderby = "ORDER BY name ASC";
				$_SESSION['order'] = 1;
			}

			$this->sql = "SELECT * FROM gadiv_productbacklogs WHERE 1 ".$addsql.$orderby;
			return $this->executeQuery();
		}

		# get all product backlogs of a project
		function getProjectProductBacklogs($project_id){
			$this->sql = "SELECT * FROM gadiv_rel_productbacklog_projects AS rpp LEFT JOIN gadiv_productbacklogs AS p ON p.id = rpp.pb_id WHERE project_id = '".$project_id."' ORDER BY name ASC";
			return $this->executeQuery();
		}

		# get product backlog information by product backlog id
		function getSelectedProductBacklog (){
			$this->sql = "SELECT * FROM gadiv_productbacklogs WHERE id = ".$this->id;
			return $this->executeQuery();
		}

		# get product backlog information by product backlog name
		function getProductBacklogByName($product_backlog){
			$this->sql = "SELECT * FROM gadiv_productbacklogs WHERE name LIKE '".$product_backlog."'";
			return $this->executeQuery();
		}

		# check if one or more teams work on the same product backlog
		function checkProductBacklogMoreOneTeam($product_backlog){
			$pb_info = $this->getProductBacklogByName($product_backlog);
			$this->sql = "SELECT count(*) AS number_of_teams FROM gadiv_teams WHERE pb_id = '".$pb_info[0]['id']."'";
			$result = $this->executeQuery();
			if($result[0]['number_of_teams'] > 1){
				return false;
			}
			if($result[0]['number_of_teams'] == 0){
				return false;
			}
			return true;
		}

		# get team id by product backlog id
		function getTeamIdByBacklog($pb_id){
			$sql = "SELECT id FROM gadiv_teams WHERE pb_id = '".$pb_id."'";
			$result = mysql_query($sql);
			$team = mysql_fetch_assoc($result);
			return $team['id'];
		}

		# generate team user function and returns team user name
		function generateTeamUser($p_username){
			$mutated_vowel = array(' ', 'ö', 'ä', 'ü', 'ß', '/','(', ')', '@', '>', '<', '#', '+', '*', '&');
			$normal_vowels = array('-', 'oe', 'ae', 'ue', 'ss', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_');
			$p_username = strtolower($p_username);
			$p_username = str_replace($mutated_vowel,$normal_vowels,$p_username);
			$p_username = 'Team-User-'.$p_username;
			return $p_username;
		}

		# check if mantis tracker is user story and return user story values
		function checkForUserStory($bug_id){

			$this->getAdditionalProjectFields();

			$this->sql = "SELECT * FROM mantis_custom_field_string_table WHERE bug_id = '".$bug_id."' AND field_id = '".$this->pb."'";
			$result = $this->executeQuery();
			$userstory['name'] = $result[0]['value'];

			$this->sql = "SELECT * FROM mantis_custom_field_string_table WHERE bug_id = '".$bug_id."' AND field_id = '".$this->sp."'";
			$result = $this->executeQuery();
			$userstory['storypoints'] = $result[0]['value'];

			$this->sql = "SELECT * FROM mantis_custom_field_string_table WHERE bug_id = '".$bug_id."' AND field_id = '".$this->bv."'";
			$result = $this->executeQuery();
			$userstory['businessValue'] = $result[0]['value'];

			$this->sql = "SELECT * FROM mantis_custom_field_string_table WHERE bug_id = '".$bug_id."' AND field_id = '".$this->spr."'";
			$result = $this->executeQuery();
			$userstory['sprint'] = $result[0]['value'];

			$this->sql = "SELECT * FROM mantis_custom_field_string_table WHERE bug_id = '".$bug_id."' AND field_id = '".$this->ro."'";
			$result = $this->executeQuery();
			$userstory['rankingorder'] = $result[0]['value'];

			$this->sql = "SELECT * FROM mantis_custom_field_string_table WHERE bug_id = '".$bug_id."' AND field_id = '".$this->pr."'";
			$result = $this->executeQuery();
			$userstory['presentable'] = $result[0]['value'];

			$this->sql = "SELECT * FROM mantis_custom_field_string_table WHERE bug_id = '".$bug_id."' AND field_id = '".$this->tech."'";
			$result = $this->executeQuery();
			$userstory['technical'] = $result[0]['value'];

			$this->sql = "SELECT * FROM mantis_custom_field_string_table WHERE bug_id = '".$bug_id."' AND field_id = '".$this->rld."'";
			$result = $this->executeQuery();
			if(!empty($result[0]['value'])){
				$userstory['inReleaseDocu'] = $result[0]['value'];
			}

			$this->sql = "SELECT * FROM mantis_custom_field_string_table WHERE bug_id = '".$bug_id."' AND field_id = '".$this->pw."'";
			$result = $this->executeQuery();
			$userstory['plannedWork'] = $result[0]['value'];

			$this->sql = "SELECT * FROM mantis_custom_field_string_table WHERE bug_id = '".$bug_id."' AND field_id = '".$this->un."'";
			$result = $this->executeQuery();
			$userstory['unit'] = $result[0]['value'];

			return $userstory;

		}
		
		function getUserStoryChanges($id){
			$this->sql = "SELECT new_value,date_modified FROM mantis_bug_history_table WHERE bug_id = '".$id."' AND field_name = 'status' AND new_value >= 80 ORDER BY date_modified ASC LIMIT 1";
			return $this->executeQuery();
		}

		# copy user story
		function copyUserStory($bug_id){
			$sql = "SELECT * FROM `mantis_bug_table` WHERE id = '".$bug_id."'";
			$result = mysql_query($sql);
			$old_bug_data = mysql_fetch_assoc($result);

			$sql = "SELECT * FROM `mantis_bug_text_table` WHERE id = '".$bug_id."'";
			$result = mysql_query($sql);
			$old_bug_text_data = mysql_fetch_assoc($result);

			$this->sql = "SELECT * FROM `mantis_bug_tag_table` WHERE bug_id = '".$bug_id."'";
			$old_bug_tag_data = $this->executeQuery();

			$this->sql = "SELECT * FROM `mantis_bug_revision_table` WHERE bug_id = '".$bug_id."'";
			$old_bug_revision_data = $this->executeQuery();

			$this->sql = "SELECT * FROM `mantis_bug_monitor_table` WHERE bug_id = '".$bug_id."'";
			$old_bug_monitor_data = $this->executeQuery();

			$this->sql = "SELECT * FROM `mantis_bug_file_table` WHERE bug_id = '".$bug_id."'";
			$old_bug_file_data = $this->executeQuery();

			$this->sql = "SELECT * FROM `mantis_custom_field_string_table` WHERE bug_id = '".$bug_id."'";
			$old_custom_field_string_data = $this->executeQuery();

			$this->sql = "SELECT * FROM `mantis_bug_relationship_table` WHERE source_bug_id  = '".$bug_id."' OR destination_bug_id = '".bug_id."'";
			$old_bug_relationship_data = $this->executeQuery();

			$this->sql = "SELECT * FROM `mantis_bugnote_table` AS mbt LEFT JOIN `mantis_bugnote_text_table` AS mbtt ON mbtt.id = mbt.id WHERE bug_id = '".$bug_id."'";
			$old_bugnote_data = $this->executeQuery();

			if(!empty($old_bug_data)){
				$sql = "INSERT INTO mantis_bug_table SET
							project_id 			=	'".$old_bug_data['project_id']."',
							reporter_id 		=	'".$old_bug_data['reporter_id']."',
							handler_id 			=	'".$old_bug_data['handler_id']."',
							duplicate_id		=	'".$old_bug_data['duplicate_id']."',
							priority			=	'".$old_bug_data['priority']."',
							severity			=	'".$old_bug_data['severity']."',
							reproducibility 	=	'".$old_bug_data['reproducibility']."',
							status				= 	'50',
							resolution			=	'".$old_bug_data['resolution']."',
							projection			=	'".$old_bug_data['projection']."',
							eta					=	'".$old_bug_data['eta']."',
							bug_text_id			=	'".$old_bug_data['bug_text_id']."',
							os					=	'".$old_bug_data['os']."',
							os_build			=	'".$old_bug_data['os_build']."',
							platform			=	'".$old_bug_data['platform']."',
							version				=	'".$old_bug_data['version']."',
							fixed_in_version	=	'".$old_bug_data['fixed_in_version']."',
							build				=	'".$old_bug_data['build']."',
							profile_id			=	'".$old_bug_data['profile_id']."',
							view_state			=	'".$old_bug_data['view_state']."',
							summary				=	'".$old_bug_data['summary']."',
							sponsorship_total	=	'".$old_bug_data['sponsorship_total']."',
							sticky				=	'".$old_bug_data['sticky']."',
							target_version		=	'".$old_bug_data['target_version']."',
							category_id			=	'".$old_bug_data['category_id']."',
							date_submitted		=	'".$old_bug_data['date_submitted']."',
							due_date			=	'".time()."',
							last_updated		=	'".time()."'";
				mysql_query($sql);
				$new_bug_id = mysql_insert_id();
			}

			if($new_bug_id > 0){

				$sql = "INSERT INTO `mantis_bug_text_table` SET		id						=	'".$new_bug_id."',
																	description				=	'".$old_bug_text_data['description']."',
																	steps_to_reproduce		=	'".$old_bug_text_data['steps_to_reproduce']."',
																	additional_information	=	'".$old_bug_text_data['additional_information']."'";
				mysql_query($sql);

				foreach($old_bug_tag_data AS $num => $row){
					$sql = "INSERT INTO `mantis_bug_tag_table` SET		bug_id			=	'".$new_bug_id."',
																		tag_id			=	'".$row['tag_id']."',
																		user_id			=	'".$row['user_id']."',
																		date_attached	=	'".$row['additional_information']."'";
					mysql_query($sql);
				}

				foreach($old_bug_revision_data AS $num => $row){
					$sql = "INSERT INTO `mantis_bug_revision_table` SET		bug_id					=	'".$new_bug_id."',
																			bugnote_id				=	'".$row['bugnote_id']."',
																			user_id					=	'".$row['user_id']."',
																			value					=	'".$row['value']."',
																			timestamp				=	'".$row['timestamp']."',
																			type					=	'".$row['type']."'";
					mysql_query($sql);
				}

				foreach($old_bug_monitor_data AS $num => $row){
					$sql = "INSERT INTO `mantis_bug_monitor_table` SET		bug_id				=	'".$new_bug_id."',
																			user_id				=	'".$row['user_id']."'";
					mysql_query($sql);
				}

				foreach($old_bug_file_data AS $num => $row){
					$sql = "INSERT INTO `mantis_bug_file_table` SET		bug_id				=	'".$new_bug_id."',
																			user_id			=	'".$row['user_id']."',
																			description		=	'".$row['description']."',
																			diskfile		=	'".$row['diskfile']."',
																			filename		=	'".$row['filename']."',
																			folder			=	'".$row['folder']."',
																			filesize		=	'".$row['filesize']."',
																			file_type		=	'".$row['file_type']."',
																			content			=	'".$row['content']."',
																			date_added		=	'".$row['date_added']."',
																			user_id			=	'".$row['bugnote_id']."'";
					mysql_query($sql);
				}

				foreach($old_bug_relationship_data AS $num => $row){
					if($row['source_bug_id'] == $bug_id){
					$sql = "INSERT INTO `mantis_bug_relationship_table` 	SET		source_bug_id				=	'".$new_bug_id."',
																					destination_bug_id			=	'".$row['destination_bug_id']."',
																					relationship_type			=	'".$row['relationship_type']."'";
					}
					if($row['destination_bug_id'] == $bug_id){
					$sql = "INSERT INTO `mantis_bug_relationship_table` 	SET		source_bug_id				=	'".$row['source_bug_id']."',
																					destination_bug_id			=	'".$new_bug_id."',
																					relationship_type			=	'".$row['relationship_type']."'";
					}
					mysql_query($sql);

					$sql = "INSERT INTO `mantis_bug_relationship_table` 	SET		source_bug_id				=	'".$new_bug_id."',
																					destination_bug_id			=	'".$bug_id."',
																					relationship_type			=	'0'";
					mysql_query($sql);
				}

				foreach($old_custom_field_string_data AS $num => $row){
					$sql = "INSERT INTO `mantis_custom_field_string_table` 	SET			bug_id				=	'".$new_bug_id."',
																						field_id			=	'".$row['field_id']."',
																						value				=	'".$row['value']."'";
					mysql_query($sql);
				}

				foreach($old_bugnote_data AS $num => $row){
					$sql = "INSERT INTO `mantis_bugnote_text_table` SET	note				=	'".$row['note']."'";
					mysql_query($sql);
					$new_bugnote_text_id = mysql_insert_id();

					$sql = "INSERT INTO `mantis_bugnote_table` 	SET		bug_id				=	'".$new_bug_id."',
																		reporter_id			=	'".$row['reporter_id']."',
																		view_state			=	'".$row['view_state']."',
																		note_type			=	'".$row['note_type']."',
																		note_attr			=	'".$row['note_attr']."',
																		bugnote_text_id		=	'".$new_bugnote_text_id."',
																		time_tracking		=	'".$row['time_tracking']."',
																		last_modified		=	'".$row['last_modified']."',
																		date_submitted		=	'".$row['date_submitted']."'";
					mysql_query($sql);
				}

				return $new_bug_id;
			}
		}

		# set task status by task id
		function setTaskStatus($id,$status){
			$sql = "UPDATE gadiv_tasks SET status = ".$status.", rest_capacity = 0 WHERE id = ".$id;
			mysql_query($sql);
		}

		# save task progress in database
		function saveDailyPerformance($rest_flag){
			$sql = "INSERT INTO gadiv_daily_task_performance SET task_id = ".$this->id.", performed = '".$this->capacity."', rest = '".$this->rest_capacity."', date = '". date('Y').'-'.date('m').'-'.date('d') ." ".date('H').":".date('i').":".date('s')."', user_id = '".$this->user_id."', rest_flag = ".$rest_flag."";
			mysql_query($sql);
		}

		# get task information by task id
		function getSelectedTask($tid){
			$sql = "SELECT * FROM gadiv_tasks WHERE id = ".$tid;
			$result = mysql_query($sql);
			return mysql_fetch_assoc($result);
		}

		# add new task
		function newTask(){
			if($_POST['user']){
				$user_id = $_POST['user'];
			} else {
				$user_id = auth_get_current_user_id();
			}

			$sql = "INSERT INTO gadiv_tasks SET name = '".$this->name."',description = '".$this->description ."', status = '".$this->status."', developer_id = '".$this->developer."', planned_capacity = '".$this->planned_capacity."', performed_capacity = 0, rest_capacity = '".$this->rest_capacity."', unit = '".$this->unit."'";
			mysql_query($sql);
			$id = mysql_insert_id();

			$this->setConfirmationStatus($this->us_id);

			$sql = "INSERT INTO gadiv_task_log SET task_id = '".$id."', user_id = '".$user_id."', event = 'created', date = '".date('Y').'-'.date('m').'-'.date('d')." ".date('H').":".date('i').":".date('s')."'";
			mysql_query($sql);

			$this->id = $id;
			$this->saveDailyPerformance(1);
			$this->id = 0;

			return $id;
		}

		# save / update task information
		function editTask(){
			if($this->id == 0){
				$this->id = $this->newTask();
			}

			$sql = "UPDATE mantis_bug_table SET last_updated = '".time()."' WHERE id = '".$this->us_id."'";
			mysql_query($sql);

			$sql = "UPDATE gadiv_tasks SET us_id = '".$this->us_id."', name = '".$this->name."',description = '".$this->description ."', status = '".$this->status."', developer_id = '".$this->developer."',planned_capacity = '".$this->planned_capacity."', performed_capacity = '".$this->getTotalPerformedCapacity()."', rest_capacity = '".$this->rest_capacity."' WHERE id = ".$this->id;
			mysql_query($sql);

			return $this->id;
		}
		
		# reset planned capacity of one task
		function resetPlanned($task_id){
			$sql = "UPDATE gadiv_tasks SET planned_capacity = '0.00', performed_capacity = '0.00', rest_capacity = '0.00' WHERE id = '".$task_id."'";
			mysql_query($sql);
			
			$sql = "SELECT date FROM gadiv_daily_task_performance WHERE task_id = '".$task_id."' ORDER BY date DESC LIMIT 0,1";
			$result = mysql_query($sql);
			$task = mysql_fetch_assoc($result);
			
			$sql = "UPDATE gadiv_daily_task_performance SET rest = '0.00' AND performed = '0.00' WHERE task_id = '".$task_id."' AND date = '".$task['date']."'";
			mysql_query($sql);
		}

		# delete task
		function deleteTask(){
			$sql = "DELETE FROM gadiv_tasks WHERE id = ".$this->id;
			$ergebnis = mysql_query($sql);

			$sql = "DELETE FROM gadiv_task_log WHERE task_id = ".$this->id;
			$ergebnis2 = mysql_query($sql);

			$sql = "DELETE FROM gadiv_daily_task_performance WHERE task_id = ".$this->id;
			$ergebnis3 = mysql_query($sql);

			if($ergebnis == true && $ergebnis2 == true && $ergebnis3 == true){
				return 1;
			} else {
				return 0;
			}
		}

		# get sprint information by product backlog name
		function getBacklogSprints($backlog_name){
			$sql = "SELECT * FROM gadiv_productbacklogs WHERE name = '".$backlog_name."'";
			$result = mysql_query($sql);
			$backlog = mysql_fetch_assoc($result);
			$sql = "SELECT gs.name AS sname, gs.status AS status FROM gadiv_teams AS gt LEFT JOIN gadiv_sprints AS gs ON gs.team_id = gt.id WHERE gt.pb_id = '".$backlog['id']."'";
			return mysql_query($sql);
		}

		# get team information by team id
		function getSelectedTeam(){
			$this->sql = "SELECT * FROM gadiv_teams WHERE id = ".$this->id;
			return $this->executeQuery();
		}

		# get product owner username
		function getProductOwner($id){
			$this->sql = "SELECT * FROM gadiv_rel_team_user AS tu LEFT JOIN mantis_user_table AS ut ON tu.user_id = ut.id WHERE role LIKE '%1%' AND team_id = ".$id;
			$smName = $this->executeQuery();
			return $smName[0]['username'];
		}

		# get scrum master username
		function getScrumMaster($id){
			$this->sql = "SELECT * FROM gadiv_rel_team_user AS tu LEFT JOIN mantis_user_table AS ut ON tu.user_id = ut.id WHERE role LIKE '%2%' AND team_id = ".$id;
			$smName = $this->executeQuery();
			return $smName[0]['username'];
		}

		# get developer username
		function getDeveloperById($id){
			$sql = "SELECT * FROM mantis_user_table WHERE id = ".$id;
			$result = mysql_query($sql);
			$username = mysql_fetch_assoc($result);
			return $username['username'];
		}

		# check if developer has enough capacity or if it is exceeded
		function compareAvailabilityWithCapacity($user_id, $year, $month, $day){
			$date = $year."-".$month."-".$day;
			$this->sql = "SELECT capacity FROM gadiv_rel_user_availability WHERE date = '".$date."' AND user_id = '".$user_id."'";
			$user = $this->executeQuery();

			$this->sql = "SELECT sum(capacity) AS total_capacity FROM gadiv_rel_user_team_capacity WHERE date = '".$date."' AND user_id = '".$user_id."'";
			$total = $this->executeQuery();
			if($total[0]['total_capacity'] == NULL){
				return true;
			}

			if(empty($user[0]['capacity'])){
				$user[0]['capacity'] = 0;
			}

			if($user[0]['capacity'] < $total[0]['total_capacity']){
				return false;
			} else {

				return true;
			}
		}

		# get saved availbilities of a user from database
		function getAvailabilityToSavedCapacity($user,$date){
			$this->sql = "SELECT capacity FROM gadiv_rel_user_availability WHERE user_id = '".$user."' AND date = '".$date."'";
			$user = $this->executeQuery();
			if(!empty($user[0]['capacity'])){
				return $user[0]['capacity'];
			} else {
				return $user[0]['capacity'] = 0;
			}
		}

		# get saved capacities of a user from database
		function getCapacityToSavedAvailability($user,$date){
			$this->sql = "SELECT sum(capacity) AS capacity FROM gadiv_rel_user_team_capacity WHERE date = '".$date."' AND user_id = '".$user."'";
			$user = $this->executeQuery();
			if(!empty($user[0]['capacity'])){
				return $user[0]['capacity'];
			} else {
				return 0;
			}
		}

		# get total performed capacity for one task
		function getTotalPerformedCapacity(){
			$sql = "SELECT sum(performed) AS capacity FROM gadiv_daily_task_performance WHERE task_id = ".$this->id." AND rest_flag = 0";
			$result = mysql_query($sql);
			$task = mysql_fetch_assoc($result);
			$capacity = $task['capacity'];
			return $capacity;
		}

		# check if a team has open or running sprints
		function hasSprints($team_id){
			if($team_id > 0){
				$sql = "SELECT count(*) AS team FROM gadiv_sprints WHERE status < 2 AND team_id = ".$team_id;
				$result = mysql_query($sql);
				$oS = mysql_fetch_assoc($result);
				return $oS['team'];
			} 
			return 0;
		}

		# get user story by id
		function getUserStoryById(){
			$this->sql = "SELECT * FROM mantis_bug_text_table AS btt LEFT JOIN mantis_bug_table AS bt ON btt.id = bt.id WHERE bt.id =  ".$this->us_id;
			return $this->executeQuery();
		}

		# update task log
		function updateTaskLog($id, $user_id, $event, $date){
			$sql = "SELECT * FROM gadiv_task_log WHERE task_id = '".$id."' AND event = '".$event."'";
			mysql_query($sql);
			if(mysql_affected_rows() == 0 || $event == "resolved" || $event == "closed" || $event == "reopened"){
				$sql = "INSERT INTO gadiv_task_log SET task_id = '".$id."', user_id = '".$user_id."', event = '".$event."', date = '".date('Y').'-'.date('m').'-'.date('d')." ".date('H').":".date('i').":".date('s')."'";
				mysql_query($sql);
			} else {
				$sql = "UPDATE gadiv_task_log SET user_id = '".$user_id."', date = '".date('Y').'-'.date('m').'-'.date('d')." ".date('H').":".date('i').":".date('s')."' WHERE task_id = '".$id."' AND event = '".$event."'";
				mysql_query($sql);
			}
		}

		# delete task log, optionally delete single event entries
		function deleteTaskLog($id,$event=""){
			if($event!=""){
				$addsql = "AND event = '".$event."'";
			}
			$sql = "DELETE FROM gadiv_task_log WHERE task_id = '".$id."'".$addsql;
			mysql_query($sql);
		}

		# get all task log information
		function getTaskLog($id){
			$this->sql = "SELECT * FROM gadiv_task_log WHERE task_id = ".$id;
			return $this->executeQuery();
		}

		# get user story status
		function getUserStoryStatus($us_id){
			$sql = "SELECT status FROM mantis_bug_table WHERE id = '".$us_id."'";
			$result = mysql_query($sql);
			$us = mysql_fetch_assoc($result);
			return $us['status'];
		}

		# check if user story is closable and close it
		function closeUserStory($us_id,$status, $user_id){
			$sql = "SELECT count(*) AS openedTasks FROM gadiv_tasks WHERE us_id = ".$us_id." AND status < 4";
			$result = mysql_query($sql);
			$ot = mysql_fetch_assoc($result);
			if($ot['openedTasks'] == 0 ){
				$sql = "SELECT * FROM mantis_bug_table WHERE id = '".$us_id."'";
				$result = mysql_query($sql);
				$userstory = mysql_fetch_assoc($result);
				$sql = "UPDATE mantis_bug_table SET status = '".$status."' WHERE id = ".$us_id;
				mysql_query($sql);
				$sql = "INSERT INTO mantis_bug_history_table SET user_id = '".$user_id."', bug_id = '".$us_id."', field_name = 'status', old_value = '".$userstory['status']."', new_value = 80, type = 0, date_modified = '".mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('Y'))."'";
				mysql_query($sql);
			}
			$sql = "UPDATE mantis_bug_table SET last_updated = '".time()."' WHERE id = '".$us_id."'";
			mysql_query($sql);
		}

		# move user story into a new or runnning sprint
		function doUserStoryToSprint($bug_id,$sprint, $sprint_old=""){

			$this->getAdditionalProjectFields();

			$sql = "INSERT INTO mantis_custom_field_string_table SET value = '".$sprint."' , bug_id = '".$bug_id."' , field_id = '".$this->spr."'";
			mysql_query($sql);

			$sql = "UPDATE mantis_custom_field_string_table SET value = '".$sprint."' WHERE bug_id = '".$bug_id."' AND field_id = '".$this->spr."'";
			mysql_query($sql);

			$this->spr = "";
			if($sprint != ''){
				history_log_event_direct( $bug_id, 'Sprint', $sprint_old, $sprint, auth_get_current_user_id(), $p_type = 0 );
			}
		}

		# add business value data to a user story
		function addBusinessValue($bug_id,$businessValue, $businessValue_old=""){
			$this->getAdditionalProjectFields();

			$sql = "INSERT INTO mantis_custom_field_string_table SET value = '".$businessValue."' , bug_id = '".$bug_id."' ,field_id = '".$this->bv."'";
			mysql_query($sql);

			$sql = "UPDATE mantis_custom_field_string_table SET value = '".$businessValue."' WHERE bug_id = '".$bug_id."' AND field_id = '".$this->bv."'";
			mysql_query($sql);

			$this->bv = "";

			history_log_event_direct( $bug_id, 'Business Value', $businessValue_old, $businessValue, auth_get_current_user_id(), $p_type = 0 );

		}

		# add storypoints data to a user story
		function addStoryPoints($bug_id,$storypoints, $storypoints_old=""){
			$this->getAdditionalProjectFields();

			$sql = "INSERT INTO mantis_custom_field_string_table SET value = '".$storypoints."' , bug_id = '".$bug_id."', field_id = '".$this->sp."'";
			mysql_query($sql);

			$sql = "UPDATE mantis_custom_field_string_table SET value = '".$storypoints."' WHERE bug_id = '".$bug_id."' AND field_id = '".$this->sp."'";
			mysql_query($sql);

			$this->sp = "";
			if($storypoints != ''){
				history_log_event_direct( $bug_id, 'Story Points', $storypoints_old, $storypoints, auth_get_current_user_id(), $p_type = 0 );
			}
		}

		# add ranking order data to a user story
		function addRankingOrder($bug_id,$rankingorder, $rankingorder_old=""){
			$this->getAdditionalProjectFields();

			$sql = "INSERT INTO mantis_custom_field_string_table SET value = '".$rankingorder."' , bug_id = '".$bug_id."', field_id = '".$this->ro."'";
			mysql_query($sql);

			$sql = "UPDATE mantis_custom_field_string_table SET value = '".$rankingorder."' WHERE bug_id = '".$bug_id."' AND field_id = '".$this->ro."'";
			mysql_query($sql);

			$this->ro = "";
			if($rankingorder != ''){
				history_log_event_direct( $bug_id, lang_get( 'RankingOrder' ), $rankingorder_old, $rankingorder, auth_get_current_user_id(), $p_type = 0 );
			}
		}

		# add presentable data to a user story
		function addPresentable($bug_id,$presentable,$prensentable_old=""){
			$this->getAdditionalProjectFields();

			$sql = "INSERT INTO mantis_custom_field_string_table SET value = '".$presentable."' , bug_id = '".$bug_id."', field_id = '".$this->pr."'";
			mysql_query($sql);

			$sql = "UPDATE mantis_custom_field_string_table SET value = '".$presentable."' WHERE bug_id = '".$bug_id."' AND field_id = '".$this->pr."'";
			mysql_query($sql);

			$this->pr = "";
			history_log_event_direct( $bug_id, lang_get( 'Presentable' ), $prensentable_old, $presentable, auth_get_current_user_id(), $p_type = 0 );
		}

		# mark user story as technical
		function addTechnical($bug_id,$technical,$technical_old=""){
			$this->getAdditionalProjectFields();

			if(empty($technical)){
				$technical = 2;
			}
			
			$sql = "INSERT INTO mantis_custom_field_string_table SET value = '".$technical."' , bug_id = '".$bug_id."', field_id = '".$this->tech."'";
			mysql_query($sql);

			$sql = "UPDATE mantis_custom_field_string_table SET value = '".$technical."' WHERE bug_id = '".$bug_id."' AND field_id = '".$this->tech."'";
			mysql_query($sql);

			$this->tech = "";
			history_log_event_direct( $bug_id, lang_get( 'Technical' ), $technical_old, $technical, auth_get_current_user_id(), $p_type = 0 );

		}

		# mark user story in order to appear in the release documentation
		function addInReleaseDocu($bug_id,$inReleaseDocu,$inReleaseDocu_old=""){
			$this->getAdditionalProjectFields();
			
			if(empty($inReleaseDocu)){
				$inReleaseDocu = 2;
			}

			$sql = "INSERT INTO mantis_custom_field_string_table SET value = '".$inReleaseDocu."' , bug_id = '".$bug_id."', field_id = '".$this->rld."'";
			mysql_query($sql);

			$sql = "UPDATE mantis_custom_field_string_table SET value = '".$inReleaseDocu."' WHERE bug_id = '".$bug_id."' AND field_id = '".$this->rld."'";
			mysql_query($sql);

			$this->rld = "";
			
			history_log_event_direct( $bug_id, lang_get( 'InReleaseDocu' ), $inReleaseDocu_old, $inReleaseDocu, auth_get_current_user_id(), $p_type = 0 );
		}

		# calculate planned work for a user story
		function addPlannedWork($bug_id,$plannedWork,$plannedWork_old=""){
			$this->getAdditionalProjectFields();

			$sql = "INSERT INTO mantis_custom_field_string_table SET value = '".$plannedWork."' , bug_id = '".$bug_id."', field_id = '".$this->pw."'";
			mysql_query($sql);

			$sql = "UPDATE mantis_custom_field_string_table SET value = '".$plannedWork."' WHERE bug_id = '".$bug_id."' AND field_id = '".$this->pw."'";
			mysql_query($sql);

			$this->pw = "";

			history_log_event_direct( $bug_id, lang_get( 'PlannedWork' ), $plannedWork_old, $plannedWork, auth_get_current_user_id(), $p_type = 0 );

		}

		# set configured user story unit to a user story
		function setUserStoryUnit($bug_id, $unit){
			$this->getAdditionalProjectFields();
			$sql = "INSERT INTO mantis_custom_field_string_table SET value = '".$unit."' , bug_id = '".$bug_id."' , field_id = '".$this->un."'";
			mysql_query($sql);
		}

		# transforms a mantis tracker into a user story
		function addUserStory($bug_id,$backlog, $backlog_old=""){

			$this->getAdditionalProjectFields();

			$sql = "INSERT INTO mantis_custom_field_string_table SET value = '".$backlog."' , bug_id = '".$bug_id."' , field_id = '".$this->pb."'";
			mysql_query($sql);

			$sql = "UPDATE mantis_custom_field_string_table SET value = '".$backlog."' WHERE bug_id = '".$bug_id."' AND field_id = '".$this->pb."'";
			mysql_query($sql);

			if($backlog != ""){
				$this->sql = "SELECT ut.id AS id FROM gadiv_productbacklogs pb LEFT JOIN mantis_user_table ut ON pb.user_id = ut.id WHERE pb.name = '".$backlog."'";
				$result = $this->executeQuery();
				if($this->hasTasks($bug_id) == false){
					if(!empty($result[0]['id'])){
						$sql = "UPDATE mantis_bug_table SET handler_id = '".$result[0]['id']."', status = '50' WHERE id = '".$bug_id."'";
						mysql_query($sql);
					}
				}
			}

			$_SESSION['tracker_handler'] = $result[0]['id'];
			$_SESSION['tracker_id'] = $bug_id;
			$_SESSION['backlog'] = $_POST['backlog'];
			$_SESSION['old_product_backlog'] = $_POST['old_product_backlog'];


			if($_POST['backlog'] != $_POST['old_product_backlog']){
				$this->updateTrackerHandler($bug_id,$result[0]['id'],$this->get_product_backlog_id($_POST['old_product_backlog']));
			}

			$this->pb = "";
			if($backlog != ''){
				history_log_event_direct( $bug_id, 'Product Backlog', $backlog_old, $backlog, auth_get_current_user_id(), $p_type = 0 );
			}
		}

		# get product backlog by id
		function get_product_backlog_id($productbacklog_name){
			$sql = "SELECT id FROM gadiv_productbacklogs WHERE name LIKE '%".$productbacklog_name."%'";
			$result = mysql_query($sql);
			$pb = mysql_fetch_assoc($result);
			return $pb['id'];
		}

		# update the bug handler when product backlog is added or changed
		function updateTrackerHandler($bug_id, $handler_id, $productbacklog_id=""){
			$sql = "SELECT * FROM mantis_bug_table WHERE id = '".$bug_id."'";
			$result = mysql_query($sql);
			$bug = mysql_fetch_assoc($result);
			if($bug['status'] == 50){
				if($bug['status'] >= 80){$status = '';} else {$status = ',status = 50';}
				$sql = "UPDATE mantis_bug_table SET handler_id = '".$handler_id."' ".$status." WHERE id = '".$bug_id."'";
				mysql_query($sql);

				if($handler_id == 0 || empty($handler_id)){
					$sql = "UPDATE mantis_bug_table SET status = '10' WHERE id = '".$bug_id."'";
					mysql_query($sql);
					if($this->count_productbacklog_teams($productbacklog_id) > 0){
						$team_id = $this->getTeamIdByBacklog($productbacklog_id);
						$product_owner = $this->getProductOwner($team_id);
						if(!empty($product_owner)){
							$handler_id = $this->getUserIdByName($product_owner);
							$sql = "UPDATE mantis_bug_table SET handler_id = '".$handler_id."', status = '50' WHERE id = '".$bug_id."'";
							mysql_query($sql);
						}
					}
				}
			}
		}

		# count the number of teams which are working on a product backlog
		function count_productbacklog_teams($productbacklog_id){
			$sql = "SELECT COUNT(*) AS teams FROM gadiv_teams WHERE pb_id = '".$productbacklog_id."'";
			$result = mysql_query($sql);
			$amount = mysql_fetch_assoc($result);
			return $amount['teams'];
		}

		# check if a user story has tasks
		function hasTasks($id){
			$sql = "SELECT count(*) AS userstories FROM gadiv_tasks WHERE us_id = '".$id."'";
			$result = mysql_query($sql);
			$story = mysql_fetch_assoc($result);
			if($story['userstories'] > 0){
				return true;
			} else {
				return false;
			}
		}

		# check if a user story has tasks left to do
		function hasTasksLeft($us_id){
			$this->sql = "SELECT status FROM gadiv_tasks WHERE us_id = '".$us_id."'";
			$results = $this->executeQuery();
			if(!empty($results)){
				$resolve = "erledigen";
				foreach($results AS $num => $row){
					if($row['status'] < 4){
						$resolve = "";
					}
				}
			} else {
				$resolve = "";
			}
			return $resolve;
		}

		# get agileMantis custom field value by user story id and custom field id
		function getCustomFieldValueById($bug_id, $custom_field_id){
			$sql = "SELECT value FROM mantis_custom_field_string_table WHERE field_id = '".$custom_field_id."' AND bug_id = '".$bug_id."'";
			$result = mysql_query($sql);
			$custom_field = mysql_fetch_assoc($result);
			return $custom_field['value'];
		}

		# check if custom field is in a project by name
		function customFieldIsInProject($name){
			$sql = "SELECT id FROM mantis_custom_field_table WHERE name = '".$name."'";
			$result = mysql_query($sql);
			$field = mysql_fetch_assoc($result);

			$sql = "SELECT count(*) AS inTable FROM mantis_custom_field_project_table WHERE field_id = '".$field['id']."' AND project_id = '".helper_get_current_project()."'";
			$result = mysql_query($sql);
			$custom = mysql_fetch_assoc($result);

			if($custom['inTable'] == 1){
				return true;
			} else {
				return false;
			}
		}

		# restores agileMantis custom field value if user tries to enter wrong value
		function restoreCustomFieldValue($bug_id, $field_id, $value){
			$sql = "UPDATE mantis_custom_field_string_table SET value = '".$value."' WHERE bug_id = '".$bug_id."' AND field_id = '".$field_id."'";
			mysql_query($sql);
			$sql = "SELECT max(id) AS maxid FROM mantis_bug_history_table WHERE bug_id = '".$bug_id."'";
			$result = mysql_query($sql);
			$history = mysql_fetch_assoc($result);
			$sql = "DELETE FROM mantis_bug_history_table WHERE id = '".$history['maxid']."'";
			mysql_query($sql);
		}

		# get product backlog id by product backlog name
		function getProductBacklogIDByName($bug_id){
			$this->getAdditionalProjectFields();
			$sql = "SELECT * FROM mantis_custom_field_string_table LEFT JOIN gadiv_productbacklogs ON value = name WHERE field_id = '".$this->pb."' AND bug_id = '".$bug_id."'";
			$result = mysql_query($sql);
			$pb = mysql_fetch_assoc($result);
			return $pb['id'];
		}

		# get agileMantis custom field value Sprint
		function getCustomFieldSprint($bug_id){
			$this->getAdditionalProjectFields();
			$sql = "SELECT * FROM mantis_custom_field_string_table LEFT JOIN gadiv_sprints ON value = name WHERE field_id = '".$this->spr."' AND bug_id = '".$bug_id."' AND status IS NOT NULL";
			$result = mysql_query($sql);
			return mysql_fetch_assoc($result);
		}

		# get agileMantis custom field value Story Points
		function getStoryPoints($bug_id){
			$this->getAdditionalProjectFields();
			$sql = "SELECT * FROM mantis_custom_field_string_table WHERE field_id = '".$this->sp."' AND bug_id = '".$bug_id."'";
			$result = mysql_query($sql);
			$storyPoints = mysql_fetch_assoc($result);
			return $storyPoints['value'];
		}

		# get agileMantis custom field value Product Backlog
		function getCustomFieldProductBacklog($bug_id){
			$this->getAdditionalProjectFields();
			$sql = "SELECT * FROM mantis_custom_field_string_table LEFT JOIN gadiv_productbacklogs ON value = name WHERE field_id = '".$this->pb."' AND bug_id = '".$bug_id."'";
			$result = mysql_query($sql);
			return mysql_fetch_assoc($result);
		}

		# get all sprints which are connected to a product backlog by product backlog id
		function getSprintsByBacklogId($backlog_id){
			$this->sql = "SELECT gs.name AS sname, gs.status AS status, gs.pb_id AS pbid FROM gadiv_teams AS gt LEFT JOIN gadiv_sprints AS gs ON gs.team_id = gt.id WHERE gs.name IS NOT NULL AND status IS NOT NULL AND gt.pb_id = '".$backlog_id."'";
			return $this->executeQuery();
		}

		# check if task belongs to a developer or not
		function isUserTask($us_id, $developer_id){
			$sql = "SELECT count(*) AS tasks FROM gadiv_tasks WHERE us_id = '".$us_id."' AND developer_id = '".$developer_id."'";
			$result = mysql_query($sql);
			$user = mysql_fetch_assoc($result);
			if($user['tasks'] >= 1){
				return true;
			}
			return false;
		}

		# get the name of a category
		function getCategoryById($category_id){
			$sql = "SELECT * FROM mantis_category_table WHERE id = '".$category_id."'";
			$result = mysql_query($sql);
			$category = mysql_fetch_assoc($result);
			return $category['name'];
		}

		# get the parent project id, returns 0 when it is a main project
		function getParentProjectId($project_id){
			$sql = "SELECT parent_id FROM mantis_project_hierarchy_table WHERE child_id = '".$project_id."'";
			$result = mysql_query($sql);
			$project = mysql_fetch_assoc($result);
			return $project['parent_id'];
		}

		# select unit id by unit name
		function getUnitId($unit){
			switch($unit){
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
		function getUnitById($id){
			switch($id){
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
		function setSplittingInformation($us_id,$new_bug_id, $wmu, $spmu){
			$sql = "INSERT INTO gadiv_rel_userstory_splitting_table SET old_userstory_id = '".$us_id."', new_userstory_id = '".$new_bug_id."', work_moved = '".$wmu."', storypoints_moved = '".$spmu."', date = '".date('Y-m-d H:i:s')."'";
			mysql_query($sql);
		}

		# check if user story is a splitted one
		function isSplittedStory($us_id){
			$sql = "SELECT count(*) AS splitted FROM gadiv_rel_userstory_splitting_table WHERE old_userstory_id = '".$us_id."'";
			$result = mysql_query($sql);
			$story = mysql_fetch_assoc($result);
			return $story['splitted'] == 1;
		}

		# check if user story is splitted and return splitting information
		function getSplittedStory($us_id){
			$sql = "SELECT * FROM gadiv_rel_userstory_splitting_table WHERE old_userstory_id = '".$us_id."'";
			$result = mysql_query($sql);
			return mysql_fetch_assoc($result);
		}

		# get user story from a specific sprint by sprint name
		function getUserStoriesWithSpecificSprint($sprint_name=""){
			$this->getAdditionalProjectFields();
			$this->sql = "SELECT * FROM mantis_custom_field_string_table WHERE field_id = ".$this->spr." AND value = '".$sprint_name."'";
			return $this->executeQuery();
		}

		# get all new sprints
		function getNewSprints(){
			$this->sql = "SELECT * FROM gadiv_sprints WHERE status = 0";
			return $this->executeQuery();
		}
		
		# changes the visibilty at the filter options on the view issues page
		function changeCustomFieldFilter($customField, $status){
			$sql = "UPDATE mantis_custom_field_table SET filter_by = '".$status."' WHERE name = '".$customField."'";
			mysql_query($sql);
		}
	}
?>