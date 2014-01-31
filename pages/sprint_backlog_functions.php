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

# get current user information
$user_id =  auth_get_current_user_id();
$teams = $team->countMemberTeams($user_id);

# check if chose sprint page or a sprint directly appears on screen
$show_all_sprints = false;
if($_SESSION['ISMANTISUSER'] && $teams > 1){$show_all_sprints = true;}
if($_SESSION['ISMANTISUSER'] && $teams >= 1 && $sprint->countUserSprints($user_id) == 1){$show_all_sprints = false;$teams = 1;}
if($_SESSION['ISMANTISADMIN'] && $teams > 0 && $teams != 1){$show_all_sprints = true;}
if($_SESSION['ISMANTISADMIN'] && $teams == 0){$show_all_sprints = true;}
if($_POST['chose_sprint'] && $_POST['submit'] != "backlog" ){$show_all_sprints = true;}
if($_POST['do_not_enter_sprint']){$show_all_sprints = true;}
if($teams == 1 && $show_all_sprints == false && $_POST['sprintName'] == ""){$sprInfo = $sprint->getCurrentUserSprint($user_id);$_POST['sprintName'] = $sprInfo[0]['name'];}
if($_SESSION['ISMANTISADMIN'] && $teams == 0 && $_POST['sprintName'] != ""){$show_all_sprints = false;}
if($_SESSION['ISMANTISADMIN'] && $sprint->countUserSprints($user_id) == 0 && $teams >= 1){$show_all_sprints = true;}
if($_POST['sprintName'] != ""){$show_all_sprints = false;}
if($_GET['sprintName'] != ""){$show_all_sprints = false;$_POST['sprintName'] = urldecode($_GET['sprintName']);}

# call revoke user story function
if($_POST['revoke_userstory']){
	$pb->doUserStoryToSprint($_POST['us_id'],'');
}

# confirm sprint when all tasks are planned
if($_POST['confirm_sprint'] != "" && $_POST['confirmSprint'] == 1){
	$sprint->sprint_id = $_POST['id'];
	$sprintInfo = $sprint->getSprintByName();
	$sprint->setSprintStatus(1,$sprintInfo['id']);
	$sprint->confirmInformation($sprintInfo['id'], plugin_config_get('gadiv_storypoint_mode'), plugin_config_get('gadiv_userstory_unit_mode'), plugin_config_get('gadiv_task_unit_mode'), str_replace(',','.', plugin_config_get('gadiv_workday_in_hours')));
}

# confirm sprint when not all tasks are planned
if($_POST['preconfirm_sprint'] != "" && $_POST['preConfirmSprint'] == 1){
	$sprint->sprint_id = $_POST['id'];
	$sprintInfo = $sprint->getSprintByName();
	$sprint->setSprintStatus(1,$sprintInfo['id']);
	$sprint->confirmInformation($sprintInfo['id'], plugin_config_get('gadiv_storypoint_mode'), plugin_config_get('gadiv_userstory_unit_mode'), plugin_config_get('gadiv_task_unit_mode'), str_replace(',','.', plugin_config_get('gadiv_workday_in_hours')));
}

# close sprint operations
if($_POST['close_sprint']){
	$sprint->closeInformation($_POST['id'],plugin_config_get('gadiv_storypoint_mode'), plugin_config_get('gadiv_userstory_unit_mode') ,plugin_config_get('gadiv_task_unit_mode'),str_replace(',','.', plugin_config_get('gadiv_workday_in_hours')));
	$userstories = $sprint->getSprintStories($_POST['name'], $user_id ,null);
	foreach($userstories AS $num => $row){
		$task = $sprint->getSprintTasks($row['id']);
		foreach($task AS $key => $value){
			$tasks->updateTaskLog($value['id'] , auth_get_current_user_id(), "closed", $date);
			$tasks->setTaskStatus($value['id'],5);
		}
		if($_POST['closeUserStories'] == 1){
			$sprint->closeUserStory($row['id'],90,$user_id);
		}
	}
	$sprint->setSprintStatus(2,$_POST['id']);
	$system = "Der Sprint wurde erfolgreich geschlossen!";
}

# set different sprint backlog config values
if(!config_is_set('show_project_target_version',auth_get_current_user_id())){
	config_set('show_project_target_version', 0, auth_get_current_user_id());
}

if(!config_is_set('show_only_own_userstories',auth_get_current_user_id())){
	config_set('show_only_own_userstories', 0, auth_get_current_user_id());
}

if(!config_is_set('show_only_open_userstories',auth_get_current_user_id())){
	config_set('show_only_open_userstories', 0, auth_get_current_user_id());
}

if(!config_is_set('current_user_sprint_backlog_filter',auth_get_current_user_id())){
	config_set('current_user_sprint_backlog_filter', '', auth_get_current_user_id());
}

if(!config_is_set('current_user_sprint_backlog_filter_direction',auth_get_current_user_id())){
	config_set('current_user_sprint_backlog_filter_direction', 'ASC', auth_get_current_user_id());
}

# set sorting direction of the sprint backlog / user story table
if(!empty($_GET['sort_by']) && isset($_GET['sort_by'])){
	$direction = $_GET['direction'];
	config_set('current_user_sprint_backlog_filter_direction', $direction, auth_get_current_user_id());
	$sort_by = $_GET['sort_by'];
	config_set('current_user_sprint_backlog_filter', $sort_by , auth_get_current_user_id());
}

# set / update checkbox values 
if($_POST['action'] == 'save_sprint_options'){
	config_set('show_project_target_version', $_POST['show_project_target_version'], auth_get_current_user_id());
	config_set('show_only_own_userstories', $_POST['show_only_own_userstories'], auth_get_current_user_id());
	config_set('show_only_open_userstories', $_POST['show_only_open_userstories'], auth_get_current_user_id());
}

# divide task action
if($_POST['divide_task'] == plugin_lang_get( 'button_assume' )){
	$sprint->sprint_id = $_POST['sprintName'];
	$sprintInfo = $sprint->getSprintById();

	$tasks->daily_scrum			= 	1;
	
	# common
	$tasks->us_id 				= 	$_POST['us_id'];
	$tasks->name 				=	$_POST['task_name'];
	$tasks->description 		= 	$_POST['task_description'];

	# old task
	$tasks->id 					= 	$_POST['task_id'];
	$tasks->developer			= 	$_POST['developer_id'];
	$tasks->status				= 	4;
	$tasks->planned_capacity 	= 	$_POST['planned_capacity'];
	$tasks->rest_capacity 		= 	0;
	$tasks->addStatusNote($tasks->us_id,$tasks->id,auth_get_current_user_id());
	$tasks->editTask();
	$tasks->setDailyScrum($tasks->id, $tasks->daily_scrum);

	# new task
	$tasks->id 					= 	0;
	$tasks->us_id 				= 	$_POST['us_id'];
	$tasks->description 		= 	$_POST['task_description'];
	$tasks->developer			= 	0;
	$tasks->status				= 	1;
	if($sprintInfo['status'] ==  0){
		$tasks->planned_capacity 	= 	$_POST['rest_capacity'];
	} else {
		$tasks->planned_capacity 	= 	0;
	}

	$tasks->unit				=	$tasks->getUnitId(plugin_config_get('gadiv_task_unit_mode'));

	$tasks->rest_capacity		= 	$_POST['rest_capacity'];
	$tasks->capacity 		   -= $tasks->planned_capacity;
	$tasks->editTask();
	$tasks->setDailyScrum($tasks->id, $tasks->daily_scrum);
}
?>