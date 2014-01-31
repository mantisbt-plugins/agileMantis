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

	class gadiv_projects extends gadiv_commonlib {

		
		# get project name by id and returns it
		function getProjectById($id){
			$this->sql = "SELECT * FROM mantis_project_table WHERE id = ".$id;
			$project = $this->executeQuery();
			return $project[0]['name'];
		}

		# delete a project from a product backlog
		function deleteProject($backlog_id,$project_id){
			$sql = "DELETE FROM`gadiv_rel_productbacklog_projects` WHERE pb_id = '".$backlog_id."' AND project_id = '".$project_id."'";
			mysql_query($sql);
		}

		# fetch all mantis projects and put them in the right order.
		function getProjectWithHierachy(){
			$this->sql = "SELECT DISTINCT p.id, ph.parent_id, p.name, p.inherit_global, ph.inherit_parent FROM mantis_project_table p LEFT JOIN mantis_project_hierarchy_table ph ON ph.child_id = p.id WHERE p.enabled = 1 ORDER BY p.name";
			$result = $this->executeQuery();

			foreach($result AS $num => $row){
				if($row['parent_id'] == NULL){
					$id = 0;
				} else {
					$id = $row['parent_id'];
				}
				$project[$id][$row['id']] = $row['name'];
			}

			return $project;
		}


		# adds agileMantis custom fields to all projects where a product backlog is assigned
		function addAdditionalProjectFields($project_id){
			$this->deleteAdditionalProjectFields($project_id);
			$this->getAdditionalProjectFields();

			if($project_id != ""){
				$sql = "INSERT INTO mantis_custom_field_project_table SET field_id = '".$this->bv."', project_id = '".$project_id."'";
				mysql_query($sql);

				$sql = "INSERT INTO mantis_custom_field_project_table SET field_id = '".$this->pb."', project_id = '".$project_id."'";
				mysql_query($sql);

				$sql = "INSERT INTO mantis_custom_field_project_table SET field_id = '".$this->sp."', project_id = '".$project_id."'";
				mysql_query($sql);

				$sql = "INSERT INTO mantis_custom_field_project_table SET field_id = '".$this->spr."', project_id = '".$project_id."'";
				mysql_query($sql);

				if(plugin_config_get('gadiv_presentable')=='1'){
					$sql = "INSERT INTO mantis_custom_field_project_table SET field_id = '".$this->pr."', project_id = '".$project_id."'";
					mysql_query($sql);
				}

				if(plugin_config_get('gadiv_ranking_order')=='1'){
					$sql = "INSERT INTO mantis_custom_field_project_table SET field_id = '".$this->ro."', project_id = '".$project_id."'";
					mysql_query($sql);
				}

				if(plugin_config_get('gadiv_technical')=='1'){
					$sql = "INSERT INTO mantis_custom_field_project_table SET field_id = '".$this->tech."', project_id = '".$project_id."'";
					mysql_query($sql);
				}

				if(plugin_config_get('gadiv_release_documentation')=='1'){
					$sql = "INSERT INTO mantis_custom_field_project_table SET field_id = '".$this->rld."', project_id = '".$project_id."'";
					mysql_query($sql);
				}

				if(plugin_config_get('gadiv_tracker_planned_costs')=='1'){
					$sql = "INSERT INTO mantis_custom_field_project_table SET field_id = '".$this->pw."', project_id = '".$project_id."'";
					mysql_query($sql);
				}

				$sql = "INSERT INTO mantis_custom_field_project_table SET field_id = '".$this->un."', project_id = '".$project_id."'";
				mysql_query($sql);
			}

			$this->bv = "";
			$this->pb = "";
			$this->sp = "";
			$this->ro = "";
			$this->pr = "";
			$this->spr = "";
			$this->tech = "";
			$this->rld = "";
			$this->pw = "";
			$this->un = "";
		}

		# when a project is deleted from a product backlog, all additional agileMantis custom fields will be deleted too.
		function deleteAdditionalProjectFields($project_id){
			$this->project_id = $project_id;
			if($this->backlog_project_is_unique($project_id)==true){
				$this->getAdditionalProjectFields();

				$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$this->bv."' AND project_id = '".$project_id."'";
				mysql_query($sql);

				$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$this->pb."' AND project_id = '".$project_id."'";
				mysql_query($sql);

				$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$this->sp."' AND project_id = '".$project_id."'";
				mysql_query($sql);

				$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$this->spr."' AND project_id = '".$project_id."'";
				mysql_query($sql);

				$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$this->ro."' AND project_id = '".$project_id."'";
				mysql_query($sql);

				$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$this->pr."' AND project_id = '".$project_id."'";
				mysql_query($sql);

				$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$this->tech."' AND project_id = '".$project_id."'";
				mysql_query($sql);

				$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$this->rld."' AND project_id = '".$project_id."'";
				mysql_query($sql);


				$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$this->pw."' AND project_id = '".$project_id."'";
				mysql_query($sql);

				$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$this->un."' AND project_id = '".$project_id."'";
				mysql_query($sql);

				$this->bv = "";
				$this->pb = "";
				$this->sp = "";
				$this->ro = "";
				$this->pr = "";
				$this->spr = "";
				$this->tech = "";
				$this->rld = "";
				$this->pw = "";
				$this->un = "";
			}
		}

		# get all projects in all product backlogs
		function getProjectsInBacklogs(){
			$this->sql = "SELECT DISTINCT(project_id) FROM `gadiv_rel_productbacklog_projects` LEFT JOIN mantis_project_table ON project_id = id";
			return $this->executeQuery();
		}

		# check if a project is only in one product backlog
		function backlog_project_is_unique($project_id){
			$this->sql = "SELECT count(*) AS projects FROM `gadiv_rel_productbacklog_projects` WHERE project_id = '".$project_id."'";
			$result = $this->executeQuery();
			if($result[0]['projects'] > 1){
				return false;
			}
			return true;
		}

	}
?>