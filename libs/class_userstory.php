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

	class gadiv_userstory extends gadiv_commonlib {

		# adds a user who monitors a bug
		function addBugMonitor($user_id, $userstory_id){
			$sql = "INSERT INTO mantis_bug_monitor_table SET user_id = '".$user_id."', bug_id = '".$userstory_id."'";
			mysql_query($sql);
		}
		
		#	adds a bugnote to one tracker/userstory with an email as content
		function addBugNote($userstory_id, $user_id, $email, $privacy = "10"){

			$note  = $email['subject'];
			$note .= '<br>';
			$note .= $email['message'];

			$sql = "INSERT INTO `mantis_bugnote_text_table` SET	note				=	'".$note."'";
			mysql_query($sql);
			$new_bugnote_text_id = mysql_insert_id();

			$sql = "INSERT INTO `mantis_bugnote_table` 	SET		bug_id				=	'".$userstory_id."',
																reporter_id			=	'".$user_id."',
																view_state			=	'".$privacy."',
																note_type			=	'0',
																note_attr			=	'',
																bugnote_text_id		=	'".$new_bugnote_text_id."',
																time_tracking		=	'0',
																last_modified		=	'".time()."',
																date_submitted		=	'".time()."'";
			mysql_query($sql);
		}

		# remove custom field by name
		function removeCustomField($removeField){
			$sql = "SELECT id FROM mantis_custom_field_table WHERE name = '".$removeField."'";
			$result = mysql_query($sql);
			$field = mysql_fetch_assoc($result);

			$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$field['id']."'";
			mysql_query($sql);
			
			$this->changeCustomFieldFilter($removeField,0);
		}

		# get user story tasks
		function getUserStoryTasks($us_id){
			$this->sql = "SELECT * FROM gadiv_tasks WHERE us_id = ".$us_id;
			return $this->executeQuery();
		}
	}
?>