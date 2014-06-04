<?
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
	
	$_GET['page'] = str_replace('agileMantis/','',$_GET['page']);
	$page_name = str_replace('.php','',$_GET['page']);

	if($_POST['sprintName'] != "" || $sprInfo[0]['name']){

		if($_POST['sprintName']){
			$sprint->sprint_id = $_POST['sprintName'];
		} else {
			$sprint->sprint_id =  $sprInfo[0]['name'];
		}

		$sprint_id = '<input type="hidden" name="sprintName" value="'.$sprint->sprint_id.'">';
		$s = $sprint->getSprintById();
		$userIsScrumMaster 	= $team->isScrumMaster($s['team_id'],$user_id);
		$userIsDeveloper 	= $team->isDeveloper($s['team_id'],$user_id);
		$temp_start_date 	= 	explode('-',$s['start']);
		$temp_end_date 		= 	explode('-',$s['end']);
		$s['start']	=	mktime(0,0,0,$temp_start_date[1],$temp_start_date[2],$temp_start_date[0]);
		$s['end']	=	mktime(0,0,0,$temp_end_date[1],$temp_end_date[2],$temp_end_date[0]);

		if($s['status'] > 0){
			if($s['unit_planned_task'] > 0 ){
				$unit = '('.$tasks->getUnitById($s['unit_planned_task']).')';
				$currentUnit = $tasks->getUnitById($s['unit_planned_task']);
			}
		} else {
			if(plugin_config_get('gadiv_task_unit_mode') != "keine"){
				$unit = '('.plugin_config_get('gadiv_task_unit_mode').')';
				$currentUnit = plugin_config_get('gadiv_task_unit_mode');
			}
		}
		
		if($currentUnit == 'T'){
			$multiplier = str_replace(',','.',plugin_config_get('gadiv_workday_in_hours'));
		} else {
			$multiplier = 1;
		}
		
		$end_date = $s['end'];
		if(time()>=$s['start']){
			$start_date = time();
		} else {
			$start_date = $s['start'];
		}

		if($s['status']==0){$start_date = $s['start'];}
		$diff = $end_date - $start_date;
		$anzahl_tage = ceil ($diff / 86400);

		if($anzahl_tage == 0 && $end_date > time()){
			$anzahl_tage = 1;
		} elseif($anzahl_tage <= 0){
			$anzahl_tage = 0;
		}

		$today_date = $start_date;
		$date_start = date('Y',$today_date).'-'.date('m',$today_date).'-'.date('d',$today_date);
		$date_end = date('Y',$end_date).'-'.date('m',$end_date).'-'.date('d',$end_date);
		$capacity = $av->getTeamCapacity($s['team_id'],$date_start,$date_end);

		if($capacity == ""){
			$capacity = 0;
		}

		$calculate_storypoints = $sprint->countSprintStories($s['name']);
		if(!empty($calculate_storypoints)){
			foreach($calculate_storypoints AS $num => $row){
				$gesamt_storypoints += $pb->getStoryPoints($row['id']);
			}
		}
		$us = $sprint->getSprintStories($s['name'], $user_id, config_get('show_only_open_userstories',null,auth_get_current_user_id()));
		$tasks_have_been_planned = false;
		$still_tasks_without_planning = false;
		if(!empty($us)){
			$added = false;
			foreach($us AS $num => $row){
				$tasked = $sprint->getSprintTasks($row['id'],0);
				if(!empty($tasked)){
					foreach($tasked AS $key => $value){
					
						$planned_capacity += $value['rest_capacity'];

						if($value['planned_capacity'] > '0.00'){
							$tasks_have_been_planned = true;
						}
						if($value['planned_capacity'] == '0.00'){
							$still_tasks_without_planning = true;
						}
						if($s['status'] == 0){
							$tasks->setDailyScrum($value['id'], 0);
						}
					}
				} else {
					$still_tasks_without_planning = true;
				}
			}
		} else {
			$still_tasks_without_planning = true;
		}
	
		if($planned_capacity  == ""){
			$planned_capacity = 0;
		}

		if($userIsScrumMaster == true && $userIsDeveloper == false){
			$disable_button = '';
		} else {
			$disable_button = 'disabled';
		}

		if($userIsScrumMaster == false && $userIsDeveloper == true){
			$disable_button = 'disabled';
		} else {
			$disable_button = '';
		}

		if($userIsScrumMaster == false && $userIsDeveloper == false && !$_SESSION['ISMANTISADMIN']){
			$disable_button = 'disabled';
		} else {
			$disable_button = '';
		}

		if($s['status'] == 2){
			$disable = 'disabled';
			$disable_button = 'disabled';
		} else {
			$disable = '';
		}

		if($planned_capacity * $multiplier > $capacity && ($currentUnit == 'h' || $currentUnit == 'T')){
			$hinweis_rest_capacity = plugin_lang_get( 'sprint_backlog_error_108702' );
			$span_left = '<span style="color:red; font-weight:bold;">';
			$span_right = '</span>';
		}
	}
	if($_SESSION['ISMANTISUSER'] && !$_SESSION['ISMANTISADMIN'] && $teams == 0){$hinweis = plugin_lang_get( 'sprint_backlog_hint' );$no_sprints = true;}
	
	if($page_name == 'sprint_backlog'){$header_title = 'Sprint Backlog';}
	if($page_name == 'taskboard'){$header_title = 'Taskboard';}
	if($page_name == 'daily_scrum_meeting'){$header_title = 'Daily Scrum Board';}
	if($page_name == 'statistics'){$header_title = plugin_lang_get( 'statistics_title' );}
?>
<style type="text/css">
.version_tooltip {
	color				: #000;
	position			: relative;
	text-decoration 	: none;
	border-bottom		: 2px dotted #000;
	padding-bottom		: 2px;
	cursor				: default;
}
.version_tooltip span {
	background-color	: #FFF;
	border				: 1px solid #000;
	margin-left			: -999em;
	position			: absolute;
	text-decoration		: none;
	padding				: 0.8em 1em;
}
.version_tooltip:hover span {
	display				: block;
	font-family			: Arial;
	left				: 10em;
	margin-left			: 0;
	position			: absolute;
	top					: -5em;
	width				: 250px;
	z-index				: 99;
}
</style>
<?php html_page_top($header_title);?>
<?php print_recently_visited();?>
<?php if($_GET['warning'] == 1){
	$warning = plugin_lang_get( 'sprint_backlog_error_100700' ).'<br>';?>
<?php }?>
<?php if($_GET['warning'] == 2){
	$warning =  plugin_lang_get( 'sprint_backlog_error_108700' ).'<br>';?>
<?php }?>
<?php if($_GET['warning'] == 3){
	$warning = plugin_lang_get( 'sprint_backlog_error_107700' );?>
<?php }?>
<?php if($_GET['warning'] == 4){
	$warning = plugin_lang_get( 'sprint_backlog_error_107702' );?>
<?php }?>
<?php if($warning != '' && $system == ""){?>
	<br>
	<center><span style="color:red; font-size:16px; font-weight:bold;"><?php echo $warning?></span></center>
<?php }?>
<?php if($hinweis_rest_capacity != '' && $system == ""){?>
	<br>
	<center><span style="color:red; font-size:16px; font-weight:bold;"><?php echo $hinweis_rest_capacity?></span></center>
<?php }?>
<?php if($hinweis != "" && $system == ""){?>
	<br>
	<center><span style="color:red; font-size:16px; font-weight:bold;"><?php echo $hinweis?></span></center>
<?php }?>
<?php if($system != "" && $s['status'] == 1){?>
	<br>
	<center><span style="color:red; font-size:16px; font-weight:bold;"><?php echo $system?></span></center>
<?php }?>