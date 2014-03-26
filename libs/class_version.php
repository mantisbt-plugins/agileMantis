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
	
	class gadiv_product_version extends gadiv_commonlib {
		
		# get mantis version info by project id
		function getVersionInformation($project_id, $version=""){
			$sql = "SELECT * FROM mantis_project_version_table WHERE project_id = '".$project_id."' AND version = '".$version."'";
			$result = mysql_query($sql);
			if(mysql_num_rows($result) == 0){
				$sql = "SELECT * FROM mantis_project_version_table WHERE project_id = '".$this->getParentProjectId($project_id)."' AND version = '".$version."'";
				$result = mysql_query($sql);
			}
			return mysql_fetch_assoc($result);
		}

		# get all tracker from a certain version and status
		function getVersionTracker($project_id, $version = "",$status){
			$sql = "SELECT count(*) AS tracker FROM mantis_bug_table WHERE project_id = '".$project_id."' AND target_version = '".$version."' AND status IN(".$status.")";
			$result = mysql_query($sql);
			$number_of_tracker = mysql_fetch_assoc($result);
			return $number_of_tracker['tracker'];
		}
	
		# get all user stories from a certain project and version
		function getVersionUserStories($project_id, $version){
			$this->getAdditionalProjectFields();
			$sql = "SELECT count(*) AS userstories FROM mantis_bug_table LEFT JOIN mantis_custom_field_string_table ON id = bug_id WHERE project_id = '".$project_id."' AND target_version = '".$version."' AND field_id = '".$this->pb."' AND value != ''";
			$result = mysql_query($sql);
			$number_of_tracker = mysql_fetch_assoc($result);
			return $number_of_tracker['userstories'];
		}

		# count number of user stories from a certain project and version
		function getNumberOfUserStories($project_id, $version){
			$this->getAdditionalProjectFields();
			$sql = "SELECT count(*) AS userstories FROM mantis_bug_table LEFT JOIN mantis_custom_field_string_table ON id = bug_id WHERE project_id = '".$project_id."' AND target_version = '".$version."' AND status < 80 AND field_id = '".$this->pb."' AND value != ''";
			$result = mysql_query($sql);
			$total = mysql_fetch_assoc($result);
			return $total['userstories'];
		}
	}

?>