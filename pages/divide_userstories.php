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
	
	html_page_top(plugin_lang_get( 'divide_userstories_title' )); 

	# merge global $_GET / $_POST Array
	$request = array_merge($_GET, $_POST);
	
	# copy user story function
	function copyUserStory($us_id,$status,$sprintname){

		$pb = new gadiv_product_backlog();
		$tasks = new gadiv_tasks();
		$sprint = new gadiv_sprint();

		$new_bug_id = bug_copy( $us_id, $p_target_project_id = null, $p_copy_custom_fields = true, $p_copy_relationships = true, $p_copy_history = false, $p_copy_attachments = true, $p_copy_bugnotes = true, $p_copy_monitoring_users = true );
		$pb->doUserStoryToSprint($new_bug_id,$sprintname);
		relationship_add($new_bug_id, $us_id, 0);
		$task = $sprint->getSprintTasks($us_id);
		$sprint->sprint_id = $sprintname;
		$sprintinfo = $sprint->getSprintById();
		$old_userstory = $pb->checkForUserStory($us_id);

		$pb->addStoryPoints($new_bug_id,$old_userstory['storypoints']);
		$pb->addBusinessValue($new_bug_id,$old_userstory['businessValue']);
		$pb->addRankingOrder($new_bug_id,$old_userstory['rankingorder']);
		$pb->addTechnical($new_bug_id,$old_userstory['technical']);
		$pb->addPresentable($new_bug_id,$old_userstory['presentable']);
		$pb->AddInReleaseDocu($new_bug_id,$old_userstory['inReleaseDocu']);
		$pb->AddPlannedWork($new_bug_id,$old_userstory['plannedWork']);

		$bugnote_text_new = plugin_lang_get( 'divide_userstories_from' ).$pb->getUserById(auth_get_current_user_id()).plugin_lang_get( 'divide_userstories_of' ).' #'.$us_id.plugin_lang_get( 'divide_userstories_splitted' );
		$bugnote_text_old = plugin_lang_get( 'divide_userstories_from' ).$pb->getUserById(auth_get_current_user_id()).plugin_lang_get( 'divide_userstories_from' ).', #'.$new_bug_id.plugin_lang_get( 'divide_userstories_splitted' );

		$sprint->sprint_id = $old_userstory['sprint'];
		$sprintinfo = $sprint->getSprintById();
		
		$userstory_performed = false;
		$wmu = 0;
		$spmu = 0;
		if(!empty($task)){
			foreach($task AS $key => $value){
				if($value['performed_capacity'] > 0 || $value['status'] >= 4){
					$userstory_performed = true;
				}
				if($value['status'] < 4){

					$tasks->name 				= 	$value['name'];
					$tasks->us_id 				= 	$value['us_id'];
					$tasks->description 		= 	$value['description'];
					$tasks->developer			= 	$value['developer_id'];
					$tasks->status				= 	5;
					$tasks->planned_capacity 	= 	$value['planned_capacity'];
					$tasks->rest_capacity 		= 	0;
					$tasks->id 					= 	$value['id'];
					$tasks->editTask();
					$tasks->saveDailyPerformance(0);
					
					$tasks->id = 0;
					$tasks->name 				= 	$value['name'];
					$tasks->us_id 				= 	$new_bug_id;
					$tasks->description 		= 	$value['description'];
					$tasks->status 				= 	$value['status'];
					
					if($value['status'] == 3){
						$tasks->status 			= 	2;
					}

					$tasks->developer 			= 	$value['developer_id'];
					
					if($sprint->getUnitId(plugin_config_get('gadiv_task_unit_mode')) != $sprintinfo['unit_planned_task']){
						$tasks->planned_capacity 	= 	0;
						$tasks->rest_capacity 		= 	0;
					} else {
						$tasks->planned_capacity 	= 	$value['rest_capacity'];
						$tasks->rest_capacity 		= 	$value['rest_capacity'];
					}
					
					$tasks->addStatusNote($value['us_id'],$value['id'],auth_get_current_user_id());
					$tasks->editTask();
					$tasks->id = 0;
					$date = date('Y').'-'.date('m').'-'.date('d');
					$tasks->updateTaskLog($value['id'] , auth_get_current_user_id(), "closed", $date);
					$tasks->setTaskStatus($value['id'],5);

					$wmu += $value['rest_capacity'];
					$new_storypoints += $value['performed_capacity'];
				}
			}
		}

		if($sprintinfo['unit_planned_task'] == 3){
			$spmu = $wmu;
		} else {
			$spmu = 0;
		}
		
		# collect all user story splitting information and write these into database
		$sprint->setSplittingInformation($us_id,$new_bug_id, $wmu, $spmu);

		if($userstory_performed === true){
			if($sprintinfo['unit_planned_task'] < 3){
				$pb->addStoryPoints($new_bug_id,'');
			} elseif($sprintinfo['unit_planned_task'] == 3) {
				$pb->addStoryPoints($new_bug_id,$old_userstory['storypoints'] - $new_storypoints);
			}
			$bugnote_text_new .= plugin_lang_get( 'divide_userstories_old_estimation' )." #".$us_id.plugin_lang_get( 'divide_userstories_with' ).$old_userstory['storypoints']." SP.";
			bugnote_add( $new_bug_id, $bugnote_text_new);
		}

		# add bug note
		bugnote_add( $us_id, $bugnote_text_old );

		$tasks->setUserStoryStatus($us_id,$status, auth_get_current_user_id());
		$tasks->closeUserStory($us_id,$status, auth_get_current_user_id());
		bug_update_date($us_id);
	}

	# divide user story action
	if($request['action'] == 'edit'){
		if($request['divide_userstory']){
			if($request['status'] < 80){
				copyUserStory($request['us_id'],$request['userstory_status'],$request['userstory_sprint']);
			}
		} elseif($request['divide_userstories']) {
			$userstories = $sprint->getSprintStories($request['sprintName']);
			foreach($userstories AS $num => $row){
				if($row['status'] < 80){
					copyUserStory($row['id'],$request['userstory_status'],$request['userstory_sprint']);
				}
			}
		}

		if($request['fromPage'] == 'sprint_backlog'){
			$header = "Location: ".plugin_page('sprint_backlog.php')."&sprintName=".urlencode($request['sprintName']);
		}

		if($request['fromPage'] == 'taskboard') {
			$header = "Location: ".plugin_page('taskboard.php')."&sprintName=".urlencode($request['sprintName']);
		}

		# return to taskboard or sprint backlog
		header($header);
	} else {
	
	$sprint->sprint_id = $request['sprintName'];
	$sprintinfo = $sprint->getSprintById();
	
	if($sprint->getUnitId(plugin_config_get('gadiv_task_unit_mode')) != $sprintinfo['unit_planned_task']){
		echo '<br><center><span style="color:red; font-size:16px; font-weight:bold;">'.plugin_lang_get( 'divide_userstories_error_106D00' ) .'</span></center>';
	}
?>
	<br>
	<form action="<?php echo plugin_page("divide_userstories.php")?>" method="post">
	<input type="hidden" name="action" value="edit">
	<input type="hidden" name="us_id" value="<?php echo $request['us_id']?>">
	<input type="hidden" name="sprintName" value="<?php echo $request['sprintName']?>">
	<input type="hidden" name="status" value="<?php echo $request['status']?>">
	<input type="hidden" name="fromPage" value="<?php echo $request['fromPage']?>">
	<input type="hidden" name="fromDailyScrum" value="<?php echo $_POST['fromDailyScrum']?>">
	<table align="center" class="width75" cellspacing="1">
		<tr>
			<td colspan="2"><b><?php echo plugin_lang_get( 'divide_userstories_subtitle' ) ?></b></td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'divide_userstories_chose_sprint' ) ?></td>
			<td>
				<?php
					$sprint->sprint_id = $request['sprintName'];
					$current_sprint = $sprint->getSprintById();
					$sprint_id = $current_sprint['id'];
					$team_id = $current_sprint['team_id'];
					$sprints = $sprint->getUndoneSprintsByTeam($team_id,$sprint_id);
				?>
				<select name="userstory_sprint" style="width:255px;">
					<option value=""></option>
					<?php if(!empty($sprints)){?>
					<?php foreach($sprints AS $num => $row){?>
					<option value="<?php echo $row['name']?>"><?php echo $row['name']?></option>
					<?php }?>
					<?php }?>
				</select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'divide_userstories_chose_status' ) ?></td>
			<td>
				<select name="userstory_status" style="width:255px;">
					<option value="80"><?php echo plugin_lang_get( 'status_resolved' ) ?></option>
					<option value="90"><?php echo plugin_lang_get( 'status_closed' ) ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="center">
				<?php if($request['us_id']){?>
					<input type="submit" name="divide_userstory" value="<?php echo plugin_lang_get( 'divide_userstories_single_title' )?>">
				<?php } else {?>
					<input type="submit" name="divide_userstories" value="<?php echo plugin_lang_get( 'divide_userstories_title' )?>">
				<?php }?>
				</form>
				<form action="<?php echo plugin_page($request['fromPage'])?>.php&sprintName=<?php echo urlencode($request['sprintName']);?>" method="post">
					<input type="submit" name="back_button" value="<?php echo plugin_lang_get( 'button_back' )?>">
				</form>
			</td>
		</tr>
	</table>
	<?php }?>
<?php html_page_bottom() ?>