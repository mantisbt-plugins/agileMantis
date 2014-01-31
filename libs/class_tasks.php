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
		function getDeveloperSprintCapacity($taskUnit){
			
			# Fetch User Story Information
			$userstory = $this->checkForUserStory($this->us_id);
			$this->getAdditionalProjectFields();

			# Add Condition to WHERE-Clause
			if($this->id > 0){
				$addSql = " AND id != '".$this->id."'";
			}
			
			# Fetch all Tasks and sum up Rest Capacity
			$sql = "SELECT sum(rest_capacity) AS rest_capacity FROM mantis_custom_field_string_table LEFT JOIN gadiv_tasks ON bug_id = us_id WHERE field_id = '".$this->spr."' AND value = '".$userstory['sprint']."' AND us_id IS NOT NULL AND status < 4 AND developer_id = '".$this->developer."' ".$addSql."";
			$result = mysql_query($sql);
			$sprint = mysql_fetch_assoc($result);
			
			# Fetch Sprint Start and End
			$sql = "SELECT start, end, team_id, status FROM gadiv_sprints WHERE name = '".$userstory['sprint']."'";
			$result = mysql_query($sql);
			$sprintinfo = mysql_fetch_assoc($result);
			
			# Set correct Start date for Capacity
			if($sprintinfo['status'] == 0){
				$date_start = $sprintinfo['start'];
			}

			if($sprintinfo['status'] == 1){
				$date_start = date('Y-m-d');
			}

			if($sprintinfo['status'] == 2){
				return true;
			}
			
			# Fetch Developer Capacity in a Sprint
			$sql = "SELECT sum(capacity) AS capacity FROM gadiv_rel_user_team_capacity WHERE user_id = '".$this->developer."' AND team_id = '".$sprintinfo['team_id']."' AND date >= '".$date_start."' AND date <= '".$sprintinfo['end']."'";
			$result = mysql_query($sql);
			$developer = mysql_fetch_assoc($result);

			# If Unit is "T", calculate result with workday hours
			if($taskUnit == 'T'){
				$multiplier = str_replace(',','.',plugin_config_get('gadiv_workday_in_hours'));
			} else {
				$multiplier = 1;
			}
				
			# Check if Sprint Rest Capacity + New Planned Capacity is larger than Developer Capacity
			if(($sprint['rest_capacity'] + $this->rest_capacity) * $multiplier > $developer['capacity']){
				return false;
			}
			
			return true;
		}

		
		# sets userstory status
		function setUserStoryStatus($id, $status, $user_id=""){
			$sql = "UPDATE mantis_bug_table SET status = '".$status."' WHERE id = ".$id;
			mysql_query($sql);
			$sql = "SELECT * FROM mantis_bug_table WHERE id = '".$id."'";
			$result = mysql_query($sql);
			$userstory = mysql_fetch_assoc($result);
			$sql = "UPDATE mantis_bug_table SET status = '".$status."' WHERE id = ".$id;
			mysql_query($sql);
			$sql = "INSERT INTO mantis_bug_history_table SET user_id = '".$user_id."', bug_id = '".$id."', field_name = 'status', old_value = '".$userstory['status']."', new_value = 80, type = 0, date_modified = '".mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('Y'))."'";
			mysql_query($sql);
		}

		
		# get all logging entries from predefined task events
		function getTaskEvent($id, $event){
			$sql = "SELECT * FROM gadiv_task_log WHERE event = '".$event."' AND task_id = '".$id."'";
			$result = mysql_query($sql);
			return mysql_fetch_assoc($result);
		}


		# adds a status note to an existing userstory or tracker
		function addStatusNote($usid,$tid,$user_id){
			$sql = "INSERT INTO mantis_bugnote_table SET bug_id = '".$usid."', reporter_id = '".$user_id."', view_state = '10', note_type = '0', time_tracking = '0', last_modified = '".time()."', date_submitted = '".time()."'";
			mysql_query($sql);
			$id = mysql_insert_id();

			$task = $this->getSelectedTask($tid);
			$sql = "INSERT INTO mantis_bugnote_text_table SET id = '".$id."', note = 'Task <b>\"".$task['name']."\"</b>, Entwickler \"".$this->getDeveloperById($task['developer_id'])."\", erledigt'";
			mysql_query($sql);
			$bugnote_text_id = mysql_insert_id();
			$sql = "UPDATE mantis_bugnote_table SET bugnote_text_id = '".$bugnote_text_id."' WHERE id = '".$id."'";
			mysql_query($sql);
		}


		# adds a status note to an existing userstory or tracker
		function addReopenNote($usid,$tid,$user_id){
			$sql = "INSERT INTO mantis_bugnote_table SET bug_id = '".$usid."', reporter_id = '".$user_id."', view_state = '10', note_type = '0', time_tracking = '0', last_modified = '".time()."', date_submitted = '".time()."'";
			mysql_query($sql);
			$id = mysql_insert_id();

			$task = $this->getSelectedTask($tid);
			$sql = "INSERT INTO mantis_bugnote_text_table SET id = '".$id."', note = 'Task <b>\"".$task['name']."\"</b>, Entwickler \"".$this->getDeveloperById($task['developer_id'])."\", wurde wieder eröffnet'";
			mysql_query($sql);

			$bugnote_text_id = mysql_insert_id();
			$sql = "UPDATE mantis_bugnote_table SET bugnote_text_id = '".$bugnote_text_id."' WHERE id = '".$id."'";
			mysql_query($sql);
		}

		# replace planned capacity
		function replacePlannedCapacity($task_id){
			$sql = "UPDATE gadiv_daily_task_performance SET performed = '0.00', rest = '".$this->planned_capacity."' WHERE task_id = '".$task_id."'";
			mysql_query($sql);
		}
		
		# set task into daily scrum mode
		function setDailyScrum($task_id, $daily_scrum){
			$sql = "UPDATE gadiv_tasks SET daily_scrum = ".(int) $daily_scrum." WHERE id = '".$task_id."'";
			mysql_query($sql);
		}

		# get performed capacity of one tasks
		function getPerformedCapacity($task_id){
			$sql = "SELECT sum(performed) AS performed_capacity FROM gadiv_daily_task_performance WHERE task_id = '".$task_id."' AND date LIKE '%".date('Y-m-d')."%'";
			$result = mysql_query($sql);
			$task = mysql_fetch_assoc($result);
			return $task['performed_capacity'];
		}

		# get all assumed userstories in a predefined period
		function getAssumedUserStories($bugList, $dayStart, $dayEnd){
			$this->sql = "SELECT * FROM  `mantis_bug_history_table` WHERE  `bug_id` IN ( ".$bugList." ) AND field_name = 'Sprint' AND date_modified BETWEEN '".$dayStart."' AND '".$dayEnd."'";
			return $this->executeQuery();
		}
	}
?>