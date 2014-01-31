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
	
	class gadiv_product_backlog extends gadiv_commonlib {

		var $id;
		var $name;
		var $team;
		var $description;
		var $system;
		var $user_id;
		var $project_id;
		var $project_ids;
		var $email;
		var $sp;
		var $bv;
		var $pb;

		# adds a new product backlog
		function newProductBacklog(){
			$p_username = $this->generateTeamUser($this->name);
			$p_email = $this->email;
			$p_email = trim( $p_email );
			$t_seed = $p_email . $p_username;
			$t_password = auth_generate_random_password( $t_seed );
			if(user_is_name_unique($p_username) === true){
				user_create( $p_username, $t_password, $p_email,55,false,true,'Team-User-'.$_POST['pbl_name']);
			} else {
				$sql = "UPDATE mantis_user_table SET email = '".mysql_real_escape_string ($p_email)."' WHERE id = ".(int) $this->user_id;
				mysql_query($sql);
			}

			$user_id = $this->getLatestUser();
			$au = new gadiv_agileuser();
			$au->setAgileMantisUserRights($user_id, 1 , 0, 0);

			if($this->team==0){$this->team = $this->getLatestUser();}
			$sql = "INSERT INTO `gadiv_productbacklogs` SET `name` = '".mysql_real_escape_string ($this->name)."', description = '".mysql_real_escape_string ($this->description)."'";
			mysql_query($sql);
			$this->id = mysql_insert_id();

			$this->user_id = $user_id;
			return $this->id;
		}

		# save / update product backlog information
		function editProductBacklog(){
			if($this->id == 0 ){$this->id = $this->newProductBacklog();}
			$sql = "UPDATE `gadiv_productbacklogs` SET `name` = '".mysql_real_escape_string ($this->name)."', description = '".mysql_real_escape_string ($this->description)."', user_id = '".$this->user_id."' WHERE `gadiv_productbacklogs`.`id` = ".(int) $this->id;
			mysql_query($sql);

			$this->sql = "SELECT * FROM gadiv_productbacklogs ORDER BY name ASC";
			$result = $this->executeQuery();
			if(!empty($result)){
				foreach($result AS $num => $row){
					$pbs .= $row['name'].'|';
				}
			}
			$pbs = substr($pbs, 0,-1);
			$sql = "UPDATE mantis_custom_field_table SET possible_values = '".mysql_real_escape_string ($pbs)."' WHERE name = 'ProductBacklog'";
			mysql_query($sql);
		}

		# get team user email by user id
		function getEmailByUserId($user_id){
			$sql = "SELECT email FROM mantis_user_table WHERE id = '".$user_id."'";
			$result = mysql_query($sql);
			$user = mysql_fetch_assoc($result);
			return $user['email'];
		}

		# get all projects from a product backlog
		function getBacklogProjects($backlog_id){
			$this->sql = "SELECT * FROM gadiv_rel_productbacklog_projects AS rpp LEFT JOIN mantis_project_table AS mpt ON rpp.project_id = mpt.id WHERE pb_id = '".$backlog_id."' ORDER BY mpt.name ASC ";
			return $this->executeQuery();
		}

		#  delete and insert projects according to its product backlog
		function editProjects($backlog_id, $project_id){
			$sql = "DELETE FROM`gadiv_rel_productbacklog_projects` WHERE pb_id = '".$backlog_id."' AND project_id = '".$project_id."'";
			mysql_query($sql);
			$sql = "INSERT INTO `gadiv_rel_productbacklog_projects` SET pb_id = '".$backlog_id."', project_id = '".$project_id."'";
			mysql_query($sql);
		}

		# checks if product backlog name is unique or not
		function isNameUnique(){
			$sql = "SELECT count(*) AS uniqueName FROM gadiv_productbacklogs WHERE name = '".mysql_real_escape_string ($this->name)."'";
			$result = mysql_query($sql);
			$uniqueName = mysql_fetch_assoc($result);
			if($uniqueName['uniqueName'] > 0 ){
				return false;
			} else {
				return true;
			}
		}

		# delete product backlog information
		function deleteProductBacklog(){
			$sql = "DELETE FROM `gadiv_productbacklogs` WHERE id = ".$this->id;
			mysql_query($sql);

			$sql = "DELETE FROM `gadiv_rel_productbacklog_projects` WHERE pb_id = ".$this->id;
			mysql_query($sql);

			$this->sql = "SELECT * FROM gadiv_productbacklogs ORDER BY name ASC";
			$result = $this->executeQuery();

			foreach($result AS $num => $row){
				$pbs .= $row['name'].'|';
			}

			$pbs = substr($pbs, 0,-1);

			$sql = "UPDATE mantis_custom_field_table SET possible_values = '".mysql_real_escape_string ($pbs)."' WHERE name = 'ProductBacklog'";
			mysql_query($sql);
		}

		# check if product backlog has still user stories in it
		function productBacklogHasStoriesLeft($name){
			$this->getAdditionalProjectFields();
			$sql = "SELECT * FROM mantis_custom_field_string_table AS ufst LEFT JOIN mantis_bug_table AS bt ON bt.id = ufst.bug_id WHERE field_id = '".$this->pb."' AND value = '".mysql_real_escape_string ($name)."'";
			$result = mysql_query($sql);
			if(!empty($result)){
				while($row = mysql_fetch_assoc($result)){
					if($row['status'] < 90){
						return false;
					}
				}
			}
			return true;
		}

		# check amount of teams working with the selected product backlog
		function checkProductBacklogTeam($id){
			$sql = "SELECT count(*) AS pbTeams FROM gadiv_teams WHERE pb_id = ".(int) $id;
			$result = mysql_query($sql);
			if(!empty($result)){
				$pbTeams = mysql_fetch_assoc($result);
				if($pbTeams['pbTeams'] > 0){
					return true;
				} else {
					return false;
				}
			}
			return false;
		}

		# get all user stories which have not been finished yet
		function getAllUndoneUserStories($product_backlog){
			$this->getAdditionalProjectFields();

			$sort_by = $_GET['sort_by'];

			if(!empty($_GET['sort_by'])){
				config_set('current_user_assume_userstories_filter', $_GET['sort_by'], auth_get_current_user_id());
			}

			if(config_get('current_user_assume_userstories_filter',null,auth_get_current_user_id()) && empty($_GET['sort_by']) && !isset($_GET['sort_by'])){
				$sort_by = config_get('current_user_assume_userstories_filter',null,auth_get_current_user_id());
			}

			if(config_get('current_user_assume_userstories_filter_direction',null,auth_get_current_user_id()) && empty($_GET['sort_by']) && !isset($_GET['sort_by'])){
				$direction  = config_get('current_user_assume_userstories_filter_direction',null,auth_get_current_user_id());
			}

			if(!empty($_GET['sort_by']) && isset($_GET['sort_by'])){
				$direction = $_GET['direction'];
				config_set('current_user_assume_userstories_filter_direction',$direction,auth_get_current_user_id());
			}

			switch($sort_by){
				case 'plannedWork':
					$orderby = "ORDER BY ABS (plannedWork) ".$direction.", projectname ".$direction.", version ".$direction.", id ".$direction;
				break;
				case 'rankingOrder':
					$orderby = "ORDER BY (rankingOrder = '') , (rankingOrder IS NULL) ".$direction." , ABS (rankingOrder) ".$direction.", projectname ".$direction.", version ".$direction.", id ".$direction;
				break;
				case 'storyPoints':
					$orderby = "ORDER BY ABS (storyPoints) ".$direction.", projectname ".$direction.", version ".$direction.", id ".$direction;
				break;
				case 'businessValue':
					$orderby = "ORDER BY (businessValue = '') , (businessValue IS NULL) ".$direction." , ABS (businessValue) ".$direction.", projectname ".$direction.", version ".$direction.", id ".$direction;
				break;
				case 'productBacklog':
					$orderby = "ORDER BY productBacklog ".$direction.", projectname ".$direction.", version ".$direction.", id ".$direction;
				break;
				case 'summary':
					$orderby = "ORDER BY summary ".$direction.", projectname ".$direction.", version ".$direction.", id ".$direction;
				break;
				case 'version':
					$orderby = "ORDER BY projectname ".$direction.", version ".$direction.", id ".$direction;
				break;
				case 'id':
					$orderby = "ORDER BY id ".$direction;
				break;
				default:
					$orderby = "ORDER BY projectname ".$direction.", version ".$direction.", id ".$direction;
				break;
			}

			if(plugin_config_get('gadiv_ranking_order')=='1'){
				$addRankingOrderTable 	= 'LEFT JOIN mantis_custom_field_string_table AS f ON a.id = f.bug_id';
				$addRankingOrderField 	= 'f.value AS rankingOrder,';
				$addRankingOrderCond	= "AND f.field_id = '".$this->ro."'";
			}
			if(plugin_config_get('gadiv_tracker_planned_costs')=='1'){
				$addPlannedWorkTable	= 'LEFT JOIN mantis_custom_field_string_table AS g ON a.id = g.bug_id';
				$addPlannedWorkField 	= 'g.value AS plannedWork,';
				$addPlannedWorkCond		= "AND g.field_id = '".$this->pw."'";
			}

		 	$this->sql = "SELECT
				a.id AS id,
				a.summary AS summary,
				a.status AS status,
				a.target_version AS version,
				b.value AS productBacklog,
				d.value AS businessValue,
				".$addRankingOrderField."
				".$addPlannedWorkField."
				e.value AS storyPoints,
				h.name AS projectname
				FROM mantis_bug_table AS a
				LEFT JOIN mantis_custom_field_string_table AS b ON a.id = b.bug_id
				LEFT JOIN mantis_custom_field_string_table AS c ON a.id = c.bug_id
				LEFT JOIN mantis_custom_field_string_table AS d ON a.id = d.bug_id
				LEFT JOIN mantis_custom_field_string_table AS e ON a.id = e.bug_id
				LEFT JOIN mantis_project_table AS h ON h.id = a.project_id
				".$addRankingOrderTable."
				".$addPlannedWorkTable."
				WHERE a.status = 50
				AND b.field_id = '".$this->pb."'
				AND b.value =  '".$product_backlog."'
				AND b.value !=  ''
				AND c.field_id = '".$this->spr."'
				AND c.value =  ''
				AND d.field_id = '".$this->bv."'
				AND e.field_id = '".$this->sp."'
				".$addRankingOrderCond."
				".$addPlannedWorkCond."
				".$orderby;
			
			return $this->executeQuery();
		}

		# get value from agileMantis custom field Sprint
		function getSprintValue($bug_id){
			$this->getAdditionalProjectFields();
			$sql = "SELECT * FROM mantis_custom_field_string_table WHERE field_id = '".$this->spr."' AND bug_id = '".$bug_id."'";
			$result = mysql_query($sql);
			$sprint = mysql_fetch_assoc($result);
			return $sprint['value'];
		}

		# check if product backlog is locked by running sprints
		function productBacklogHasRunningSprint($product_backlog){
			$this->sql = "SELECT * FROM gadiv_sprints WHERE pb_id = '".$product_backlog."' AND status = 1";
			return $this->executeQuery();
		}
		
		# check which user stories are in closed sprints
		function userStoriesInClosedSprints($bug_id){
			$this->getAdditionalProjectFields();
			$this->sql = "SELECT * FROM `mantis_custom_field_string_table` LEFT JOIN gadiv_sprints ON value LIKE name WHERE field_id = '".$this->spr."' AND value != '' AND id IS NOT NULL AND bug_id = '".$bug_id."' AND status = '2'";
			return $this->executeQuery();
		}

		# check which user stories are in running sprints
		function userStoriesInRunningSprints($bug_id){
			$this->getAdditionalProjectFields();
			$this->sql = "SELECT * FROM `mantis_custom_field_string_table` LEFT JOIN gadiv_sprints ON value LIKE name WHERE field_id = '".$this->spr."' AND value != '' AND id IS NOT NULL AND bug_id = '".$bug_id."' AND status > 0";
			return $this->executeQuery();
		}

		# get all user stories by a project
		function getUserStoriesByProject($project_id,$product_backlog, $status = 0){
			$this->getAdditionalProjectFields();
			
			if($status > 0){
				$addSql = " AND status < '".$status."'";
			}
			
			$this->sql = "SELECT * FROM mantis_custom_field_string_table LEFT JOIN mantis_bug_table ON id = bug_id WHERE field_id = '".$this->pb."' AND value = '".$product_backlog."' AND project_id = '".$project_id."' ".$addSql."";
			return $this->executeQuery();
		}

		# get all user stories by product backlog name
		function getUserStoriesByProductBacklogName($product_backlog){
			$this->getAdditionalProjectFields();
			
			if(plugin_config_get('gadiv_ranking_order')=='1'){
				$addRankingOrderTable 	= 'LEFT JOIN mantis_custom_field_string_table AS h ON a.id = h.bug_id';
				$addRankingOrderField 	= ', h.value AS rankingOrder';
				$addRankingOrderCond	= "AND h.field_id = '".$this->ro."'";
			}
			if(plugin_config_get('gadiv_tracker_planned_costs')=='1'){
				$addPlannedWorkTable	= 'LEFT JOIN mantis_custom_field_string_table AS i ON a.id = i.bug_id';
				$addPlannedWorkField 	= ', i.value AS plannedWork';
				$addPlannedWorkCond		= "AND i.field_id = '".$this->pw."'";
			}

			$sort_by = $_GET['sort_by'];

			if(!empty($_GET['sort_by'])){
				config_set('current_user_product_backlog_filter', $_GET['sort_by'], auth_get_current_user_id());
			}

			if(config_get('current_user_product_backlog_filter',null,auth_get_current_user_id()) && empty($_GET['sort_by']) && !isset($_GET['sort_by'])){
				$sort_by = config_get('current_user_product_backlog_filter',null,auth_get_current_user_id());
			}

			if(config_get('current_user_product_backlog_filter_direction',null,auth_get_current_user_id()) && empty($_GET['sort_by']) && !isset($_GET['sort_by'])){
				$direction  = config_get('current_user_product_backlog_filter_direction',null,auth_get_current_user_id());
			}

			if(!empty($_GET['sort_by']) && isset($_GET['sort_by'])){
				$direction = $_GET['direction'];
				config_set('current_user_product_backlog_filter_direction',$direction,auth_get_current_user_id());
			}

			switch($sort_by){
				case 'plannedWork':
					$orderby = "ORDER BY ABS (plannedWork) ".$direction.", project_name ".$direction.", target_version ".$direction.", sprint ".$direction.", id ".$direction;
				break;
				case 'rankingOrder':
					$orderby = " ORDER BY (rankingOrder = ''), (rankingOrder IS NULL) ".$direction.",  ABS (rankingOrder) ".$direction.", project_name ".$direction.", target_version ".$direction.", sprint ".$direction.", id ".$direction;
				break;
				case 'storyPoints':
					$orderby = " ORDER BY ABS (storyPoints) ".$direction.", project_name ".$direction.", target_version ".$direction.", sprint ".$direction.", id ".$direction;
				break;
				case 'businessValue':
					$orderby = " ORDER BY (businessValue = ''), (businessValue IS NULL) ".$direction.", ABS (businessValue) ".$direction.", project_name ".$direction.", target_version ".$direction.", sprint ".$direction.", id ".$direction;
				break;
				case 'sprint':
					$orderby = " ORDER BY sprint ".$direction.", project_name ".$direction.", target_version ".$direction.", id ".$direction;
				break;
				case 'summary':
					$orderby = " ORDER BY summary ".$direction.", project_name ".$direction.", target_version ".$direction.", sprint ".$direction.", id ".$direction;
				break;
				case 'version':
					$orderby = " ORDER BY project_name ".$direction.", target_version ".$direction.", sprint ".$direction.", id ".$direction."";
				break;
				case 'category':
					$orderby = " ORDER BY category_name ".$direction.", project_name ".$direction.", target_version ".$direction.", sprint ".$direction.", id ".$direction;
				break;
				case 'id':
					$orderby = " ORDER BY id ".$direction.", project_name ".$direction.", target_version ".$direction.", sprint ".$direction;
				break;
				default:
					$orderby = " ORDER BY  project_name ".$direction.", target_version ".$direction.", sprint ".$direction.", id ".$direction;
				break;
			}


			$this->sql = "
			SELECT
				a.id AS id,
				a.project_id AS project_id,
				a.summary AS summary,
				a.status AS status,
				a.target_version AS target_version,
				b.id AS b_category_id,
				b.name AS category_name,
				c.id AS c_project_id,
				c.name AS project_name,
				d.value AS productBacklog,
				e.value AS businessValue,
				f.value AS storyPoints,
				g.value AS sprint
				".$addRankingOrderField."
				".$addPlannedWorkField."
			FROM
				mantis_bug_table a
			LEFT JOIN
				mantis_category_table b ON a.category_id = b.id
			LEFT JOIN
				mantis_project_table c ON a.project_id = c.id
			LEFT JOIN
				mantis_custom_field_string_table d ON a.id = d.bug_id
			LEFT JOIN
				mantis_custom_field_string_table e ON a.id = e.bug_id
			LEFT JOIN
				mantis_custom_field_string_table f ON a.id = f.bug_id
			LEFT JOIN
				mantis_custom_field_string_table g ON a.id = g.bug_id
			".$addRankingOrderTable."
			".$addPlannedWorkTable."
			WHERE
				d.field_id = '".$this->pb."'
			AND
				e.field_id = '".$this->bv."'
			AND
				f.field_id = '".$this->sp."'
			AND
				g.field_id = '".$this->spr."'
			".$addRankingOrderCond."
			".$addPlannedWorkCond."
			AND
				d.value = '".$product_backlog."'";

			if(config_get('show_only_us_without_storypoints',null,auth_get_current_user_id()) == 1){
				$this->sql .= " AND (f.value = '' OR f.value IS NULL)";
			}

			if(config_get('show_resolved_userstories',null,auth_get_current_user_id()) == 1 && config_get('show_closed_userstories',null,auth_get_current_user_id()) == 0){
				$this->sql .= " AND a.status <= 80";
			}

			if(config_get('show_closed_userstories',null,auth_get_current_user_id()) == 1 && config_get('show_resolved_userstories',null,auth_get_current_user_id()) == 0){
				$this->sql .= " AND a.status != 80";
			}

			if(config_get('show_closed_userstories',null,auth_get_current_user_id()) == 1 && config_get('show_resolved_userstories',null,auth_get_current_user_id()) == 1){
				$this->sql .= " AND a.status <= 90";
			}

			if(config_get('show_closed_userstories',null,auth_get_current_user_id()) == 0 && config_get('show_resolved_userstories',null,auth_get_current_user_id()) == 0){
				$this->sql .= " AND a.status < 80";
			}

			if(config_get('show_only_userstories_without_sprint',null,auth_get_current_user_id()) == 1){
				$this->sql .= " AND (g.value = '' OR g.value IS NULL)";
			}

			if(config_get('show_only_project_userstories',null,auth_get_current_user_id()) == 1 && helper_get_current_project() > 0){
				$this->sql .= " AND a.project_id = '".helper_get_current_project()."'";
			}

			$this->sql .= $orderby;
			
			return $this->executeQuery();
		}

		# set all agileMantis custom field values
		function setCustomFieldValues($bug_id){

			$sql = "SELECT * FROM mantis_bug_table WHERE id = '".$bug_id."'";
			$result = mysql_query($sql);
			$bug = mysql_fetch_assoc($result);


			$story = $this->checkForUserStory($bug_id);

			if($bug['status'] < 80){
				$this->addUserStory($bug_id,$_POST['backlog'], $story['name']);
			}

			if($bug['status'] < 80){
				$this->addStoryPoints($bug_id, str_replace(',', '.',$_POST['storypoints']), str_replace(',', '.',$story['storypoints']));
			}

			$this->addBusinessValue($bug_id,$_POST['businessValue'],$story['businessValue']);

			$this->addRankingOrder($bug_id,$_POST['rankingorder'],$story['rankingorder']);

			if(empty($_POST['technical'])){$_POST['technical'] = 2;}
			$this->addTechnical($bug_id,$_POST['technical'], $story['technical']);

			$this->addPresentable($bug_id,$_POST['presentable'],$story['presentable']);
			
			if(empty($_POST['inReleaseDocu'])){$_POST['inReleaseDocu'] = 2;}
			$this->AddInReleaseDocu($bug_id,$_POST['inReleaseDocu'],$story['inReleaseDocu']);

			if($bug['status'] < 80){
				$this->doUserStoryToSprint($bug_id,$_POST['sprint'],$story['sprint']);
			}

			$_POST['plannedWork'] = str_replace(',','.',$_POST['plannedWork']);
		if(is_numeric($_POST['plannedWork']) || empty($_POST['plannedWork'])){
				if($_POST['plannedWork'] != ""){
					$this->AddPlannedWork($bug_id,sprintf("%.2f",$_POST['plannedWork']),$story['plannedWork']);
				} else {
					$this->AddPlannedWork($bug_id,"",$story['plannedWork']);
				}
			}
		}

		# get the latest mantis user 
		function getLatestUser(){
			$sql = "SELECT max(id) AS id FROM mantis_user_table";
			$result = mysql_query($sql);
			$team = mysql_fetch_assoc($result);
			return $team['id'];
		}

		# calculates all fibonacci numbers according to the amount of story points
		function getFibonacciNumbers($storypoints){
			$a = 0;
			$b = 0;
			$c = '';
			$end = plugin_config_get('gadiv_fibonacci_length');
			for ($i = 0; $i < $end; $i++){
				$sum = $a+$b;
				if($storypoints != ''){
					if($storypoints == $sum && $storypoints >= 0){$selected = 'selected';$c = $sum;} else {$selected = '';$additional_storypoints = true;}
				}
				$a = $b;
				$b = $sum;
				if($a == 0){$a = 1;}
				echo '<option value="'.$sum.'" '.$selected.'>'.$sum.'</option>';
			}

			if($additional_storypoints = true && $c == '' && $storypoints != ''){
				echo '<option value="'.$storypoints.'" selected>'.$storypoints.'</option>';
			}
		}
	}
?>