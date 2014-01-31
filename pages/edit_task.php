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

	html_page_top(plugin_lang_get( 'edit_task_title' )); 
?>
<br>
<?php
	
	# get task and user story id
	if($_GET['us_id']){$tasks->us_id = (int) $_GET['us_id'];}
	if($_POST['us_id']){$tasks->us_id = $_POST['us_id'];}

	# get all task and user story information
	$usData 	= $tasks->checkForUserStory($tasks->us_id);
	$usSumText 	= $tasks->getUserStoryById();
	$sprint->sprint_id = $usData['sprint'];
	$getSprint = $sprint->getSprintById();

	# calculate current date
	$date = date('Y').'-'.date('m').'-'.date('d');

	# get current user id
	$user_id = auth_get_current_user_id();

	# delete task action
	if($_POST['delete']){
		$user_id = auth_get_current_user_id();
		$tasks->id = $_POST['id'];
		$tasks->deleteTask();
		$tasks->updateTaskLog($tasks->id , $user_id, "delete", time());
		$tasks->deleteTaskLog($tasks->id);

		if($tasks->hasTasksLeft($_POST['us_id']) != ""){
			$tasks->closeUserStory($_POST['us_id'],80,$user_id);
			email_resolved($_POST['us_id']);
			email_relationship_child_resolved($_POST['us_id']);
		}

		if($_POST['fromSprintBacklog'] == 1 && $_POST['fromTaskPage'] == 1){
			$additional = '&fromSprintBacklog=1';
		}
		header($sprint->forwardReturnToPage("task_page.php&us_id=".$_POST['us_id']).$additional."");
	} else {

		# divide task action
		if($_POST['divide_task'] == plugin_lang_get( 'button_assume' )){
			
			# common
			$tasks->us_id 				= 	$_POST['us_id'];
			$tasks->name 				=	$_POST['name'];
			$tasks->description 		= 	$_POST['description'];

			# old task information
			$tasks->id 					= 	$_POST['id'];
			$tasks->developer			= 	$_POST['developer'];
			$tasks->status				= 	4;
			$tasks->planned_capacity 	= 	$_POST['planned_capacity'];
			$tasks->rest_capacity 		= 	0;
			$tasks->addStatusNote($tasks->us_id,$tasks->id,$user_id);
			$tasks->editTask();

			# new task information
			$tasks->id 					= 	0;
			$tasks->us_id 				= 	$_POST['us_id'];
			$tasks->description 		= 	$_POST['description'];
			$tasks->developer			= 	0;
			$tasks->status				= 	1;
			$tasks->unit				=	$tasks->getUnitId(plugin_config_get('gadiv_task_unit_mode'));
			if($getSprint['status'] ==  0){
				$tasks->planned_capacity 	= 	$_POST['rest_capacity'];
			} else {
				$tasks->planned_capacity 	= 	0;
			}
			$tasks->rest_capacity		= 	$_POST['rest_capacity'];
			$tasks->capacity 		   -= $tasks->planned_capacity;
			$tasks->editTask();

			if($_POST['fromSprintBacklog'] == 1 && $_POST['fromTaskPage'] == 1){
				$additional = '&fromSprintBacklog=1';
			}
			header($sprint->forwardReturnToPage("task_page.php&us_id=".$_POST['us_id']).$additional);
		} else {
		
			# back button action
			if($_POST['back_button']){
				if($_POST['fromSprintBacklog'] == 1 && $_POST['fromTaskPage'] == 1){
					$additional = '&fromSprintBacklog=1';
				}
				header($sprint->forwardReturnToPage("task_page.php&us_id=".$_POST['us_id']).$additional);
			} else {
			
				# save / update task information
				if($_POST['action']=="editTask"){
						
					# make different capacity checks
					if($_POST['rest_capacity'] < 0){
						$system = plugin_lang_get( 'edit_task_error_127905' );
					}

					if(!empty($_POST['rest_capacity']) && !is_numeric(str_replace(',','.',$_POST['rest_capacity'])) && $system == ''){
						$system = plugin_lang_get( 'edit_task_error_985902' );
					}

					if($getSprint['status'] == 0){
						$tasks->planned_capacity = str_replace(',','.',$_POST['planned_capacity']);
					}

					if($getSprint['status'] == 1 && $_POST['planned_capacity'] > 0){
						$tasks->planned_capacity = str_replace(',','.',$_POST['planned_capacity']);
					}

					# collect all task information
					$tasks->developer 			= (int) $_POST['developer'];
					$tasks->us_id				= (int) $_POST['us_id'];
					$tasks->id 					= (int) $_POST['id'];
					$tasks->user_id 			= (int) auth_get_current_user_id();
					$tasks->name 				= $_POST['name'];
					$tasks->description 		= $_POST['description'];
					$tasks->performed_capacity	= sprintf("%.2f",str_replace(',','.',$_POST['performed_capacity']));
					$tasks->rest_capacity 		= sprintf("%.2f",str_replace(',','.',$_POST['rest_capacity']) - str_replace(',','.',$_POST['performed_capacity_today']));
					$tasks->status				= $_POST['status'];
					$tasks->daily_scrum			= 0;

					# if the task is new set current task unit
					if($_POST['id'] == 0){
						$tasks->unit			=	$tasks->getUnitId(plugin_config_get('gadiv_task_unit_mode'));
					}
					
					# change capacity information
					$tasks->capacity = str_replace(',','.',$_POST['performed_capacity_today']);

					if(str_replace(',','.',$_POST['rest_capacity']) == str_replace(',','.',$_POST['old_rest_capacity']) && str_replace(',','.',$_POST['performed_capacity_today']) == 0){
						$tasks->capacity = 0;
					}

					if(str_replace(',','.',$_POST['rest_capacity']) > str_replace(',','.',$_POST['old_rest_capacity']) && str_replace(',','.',$_POST['performed_capacity_today']) == 0) {
						$tasks->capacity = 0;
					}

					if(str_replace(',','.',$_POST['rest_capacity']) < str_replace(',','.',$_POST['old_rest_capacity'])) {
						$tasks->capacity = str_replace(',','.',$_POST['old_rest_capacity']) - str_replace(',','.',$_POST['rest_capacity']);
						if($_POST['rest_capacity'] <= 0){
							$tasks->capacity = 0;
							$tasks->rest_capacity = 0;
						}
					}

					if(str_replace(',','.',$_POST['performed_capacity_today']) != 0 && str_replace(',','.',$_POST['rest_capacity']) != str_replace(',','.',$_POST['old_rest_capacity'])){
						$tasks->capacity = str_replace(',','.',$_POST['performed_capacity_today']);
						$tasks->rest_capacity = str_replace(',','.',$_POST['rest_capacity']);
					}

					if(str_replace(',','.',$_POST['rest_capacity']) - str_replace(',','.',$_POST['performed_capacity_today']) <= 0 && str_replace(',','.',$_POST['rest_capacity']) == str_replace(',','.',$_POST['old_rest_capacity'])){
						$tasks->rest_capacity = 0;
					}

					if($_POST['oldstatus'] != $_POST['status']){
						$tasks->capacity = str_replace(',','.',$_POST['performed_capacity_today']);
					}

					# calculate developer performed work
					if($tasks->performed_capacity + str_replace(',','.',$_POST['performed_capacity_today']) < 0 && $_POST['performed_capacity_today'] != 0){
						$tasks->capacity = 0;
						$tasks->capacity -= $tasks->performed_capacity;
						$tasks->performed_capacity = 0;
						$system = plugin_lang_get( 'edit_task_error_127906' );
						$tasks->rest_capacity = sprintf("%.2f",str_replace(',','.',$_POST['rest_capacity']));
					}
		
					# if task is new, rest capacity is equal to planned capacity
					if($_POST['id'] == 0){
						$tasks->rest_capacity  = str_replace(',','.',$_POST['planned_capacity']);
					}

					if($getSprint['status'] == 0){
						$tasks->rest_capacity  = $tasks->planned_capacity;
						if($_POST['id'] > 0){
							$tasks->replacePlannedCapacity($_POST['id']);
							$tasks->capacity = $tasks->rest_capacity;
						}
					}

					# check wether work is already performed on the current day
					if($_POST['action']=="editTask" && ($tasks->getPerformedCapacity($tasks->id) == 0 || $tasks->getPerformedCapacity($tasks->id) == '') && str_replace(',','.',$_POST['performed_capacity_today']) == '' && $_POST['status'] == 4 && $_POST['oldstatus'] < 4 && !isset($_POST['resolved']) && $tasks->developer > 0){
						$system = plugin_lang_get( 'edit_task_error_127900' );
					} elseif($_POST['status'] == 4 && $_POST['oldstatus'] < 4) {
						$tasks->status = 4;
						$tasks->rest_capacity = 0;
						$tasks->daily_scrum	= 1;
					}

					if($_POST['resolved'] == plugin_lang_get( 'button_resolve' ) && ($tasks->getPerformedCapacity($tasks->id) == 0 || $tasks->getPerformedCapacity($tasks->id) == '') && str_replace(',','.',$_POST['performed_capacity_today']) == '' && $tasks->developer > 0){
						$system = plugin_lang_get( 'edit_task_error_127901' );
					} elseif($_POST['resolved'] == plugin_lang_get( 'button_resolve' )) {
						$tasks->status = 4;
						$tasks->rest_capacity = 0;
						$tasks->daily_scrum	= 1;
					}

					# if task has no name -> error
					if($_POST['name'] == ""){
						$system = plugin_lang_get( 'edit_task_error_922900' );
					}

					# if planned capacity is not a number -> error
					if(!empty($tasks->planned_capacity) && (!is_numeric($tasks->planned_capacity) || $tasks->planned_capacity < 0) && $system == ''){
						$system = plugin_lang_get( 'edit_task_error_985901' );
					}
					
					# if performed capacity today is not a number -> error
					if(!empty($_POST['performed_capacity_today']) && !is_numeric(str_replace(',','.',$_POST['performed_capacity_today'])) && $system == ''){
						$system = plugin_lang_get( 'edit_task_error_985900' );
					}
					
					if($_POST['oldstatus'] == 5 && $_POST['status'] == 4){
						$tasks->deleteTaskLog($tasks->id,"closed");
					}
					
					# tasks status cannot be changed from resolved or closed if there is no rest capacity 
					if($_POST['oldstatus'] > 3 && $tasks->status == 1 && $tasks->developer > 0 && $tasks->performed_capacity > 0 && $_POST['rest_capacity'] == 0 && $system == ''){
						$system = plugin_lang_get( 'edit_task_error_127902' );
					}
					
					if($_POST['oldstatus'] > 3 && $tasks->status == 2 && $tasks->developer > 0 && $tasks->performed_capacity > 0 && $_POST['rest_capacity'] == 0 && $system == ''){
						$system = plugin_lang_get( 'edit_task_error_127903' );
					}

					if($_POST['oldstatus'] > 3 && $tasks->status == 3 && $tasks->developer > 0 && $_POST['rest_capacity'] == 0 && $system == ''){
						$system = plugin_lang_get( 'edit_task_error_127904' );
					}
					
					# check capacity of a the developer who works on this task 
					$noCapacity = false;
					if(($_POST['currentUnit'] == 'h' || $_POST['currentUnit'] == 'T') && $tasks->developer > 0 && $system == ""){
						if(!$tasks->getDeveloperSprintCapacity($_POST['currentUnit'])){
							$noCapacity = true;
						}
					}

					if($system == ""){
						# change status to new if previous status was resolved or closed
						if($_POST['oldstatus'] > 3 && $tasks->status == 1 && $tasks->performed_capacity == 0){$tasks->status = 1;}

						# change status to assigned if previous status was resolved or closed
						if($_POST['oldstatus'] > 3 && $tasks->status == 1 && $tasks->performed_capacity == 0 && $tasks->developer > 0){$tasks->status = 2;}

						# change status to assigned if previous status was resolved or closed
						if($_POST['oldstatus'] > 3 && $tasks->status == 2 && $tasks->performed_capacity == 0 && $tasks->developer > 0){$tasks->status = 2; $tasks->rest_capacity = $tasks->planned_capacity;}

						# change status to confirmed if previous status was resolved or closed
						if($_POST['oldstatus'] > 3 && $tasks->status == 3 && $tasks->performed_capacity > 0 && $tasks->developer > 0 && $tasks->rest_capacity == 0){$tasks->status = 4;}

						# change status to confirmed if previous status was resolved or closed
						if($_POST['oldstatus'] > 3 && $tasks->performed_capacity > 0 && $tasks->developer == 0 && $tasks->rest_capacity == 0){$tasks->status = 3;}
						
						if($_POST['oldstatus'] == $_POST['status']){

							# change to status new
							if($tasks->status == 0){
								$tasks->status = 1;
							}

							if($tasks->status == 2 && $tasks->developer == 0 && $task->perfomed_capacity == 0){
								$tasks->status = 1;
							}

							# change to status assigned
							if($tasks->status < 3 && $tasks->developer > 0 && $tasks->performed_capacity == 0){
								$tasks->status = 2;
							}

							if($tasks->status < 3 && $tasks->developer > 0 && $tasks->rest_capacity > 0){
								$tasks->status = 2;
							}

							if($tasks->status > 3 && $tasks->developer > 0 && $tasks->rest_capacity > 0){
								$tasks->status = 2;
							}

							# change to status confirmed
							if($tasks->status == 2 && isset($_POST['performed_capacity_today'])){
								$tasks->status = 3;
							}

							if(str_replace(',','.',$_POST['rest_capacity']) < str_replace(',','.',$_POST['old_rest_capacity']) && $tasks->rest_capacity > 0){
								$tasks->status = 3;
							}
							
							if(str_replace(',','.',$_POST['rest_capacity']) != str_replace(',','.',$_POST['old_rest_capacity']) && $tasks->rest_capacity > 0){
								$tasks->status = 3;
								$tasks->rest_capacity = str_replace(',','.',$_POST['rest_capacity']);
							}
							
							if(str_replace(',','.',$_POST['rest_capacity']) != str_replace(',','.',$_POST['old_rest_capacity']) && $tasks->rest_capacity == 0){
								$tasks->status = 4;
								$task_resolved = true;
							}

							# change to status resolved
							if($tasks->status != 5 && $tasks->performed_capacity > 0 && $tasks->planned_capacity > 0 && $tasks->rest_capacity <= 0){
								$tasks->rest_capacity = 0;
								$tasks->status = 4;
								$task_resolved = true;
							}

							if(($tasks->status == 3 || $tasks->status == 2) && str_replace(',','.',$_POST['performed_capacity_today']) > 0 && $tasks->rest_capacity <= 0 ){
								$tasks->status = 4;
								$task_resolved = true;
							}
							
							# change to status closed
							if($tasks->status == 5 && $tasks->rest_capacity == 0 && $tasks->planned_capacity > 0 && $tasks->performed_capacity > 0){
								$tasks->status = 5;
								$task_closed = true;
							}

						}
					
						# make different status changes 
						if($_POST['oldstatus'] > 3 && $tasks->status == 1 && $tasks->developer > 0){
							$tasks->status = 2;
						}

						if($_POST['oldstatus'] > 3 && $tasks->status == 1 && $tasks->developer > 0 && $tasks->performed_capacity > 0){
							$tasks->status = 3;
						}

						if($_POST['oldstatus'] > 3 && $tasks->status == 2 && $tasks->developer > 0 && $tasks->performed_capacity > 0){
							$tasks->status = 3;
						}
						
						if($tasks->performed_capacity > 0 && $tasks->rest_capacity > 0 && $tasks->status < 4){
							$tasks->status = 3;
						}
						
						if($tasks->performed_capacity + $_POST['performed_capacity_today'] <= 0 && $tasks->rest_capacity > 0 && $_POST['status'] == 2){
							$tasks->status = 2;	
						}

						if($tasks->status == 2){
							$userstory->addBugMonitor($tasks->developer,$tasks->us_id);
						}
						
						if($tasks->developer == 0 && $_POST['status'] < 4){
							$tasks->status = 1;
						}

						# update task log
						if(($_POST['oldstatus'] >= 4) && $tasks->status <= 3){
							$tasks->updateTaskLog($tasks->id , $user_id, "reopened", $date);
							$tasks->addReopenNote($tasks->us_id,$tasks->id,$user_id);
						}

						if($tasks->status == 3){
							$tasks->updateTaskLog($tasks->id , $user_id, "confirmed", $date);
						}

						# if task is resolved or close set rest capacity = 0
						if($tasks->status == 4 || $tasks->status == 5){
							$tasks->rest_capacity = 0;
						}

						# only save daily performance if current sprint is running
						if($getSprint['status'] == 1){
							$tasks->saveDailyPerformance(0);
						}

						if($tasks->status == 4 && $_POST['oldstatus'] != $_POST['status']){
							$tasks->updateTaskLog($tasks->id , $user_id, "resolved", $date);
							if($_POST['oldstatus'] != 5){
								$tasks->addStatusNote($tasks->us_id,$tasks->id,$user_id);
							}
							$task_resolved = true;
						}

						if($tasks->status == 5 && $_POST['oldstatus'] != $_POST['status']){
							$tasks->updateTaskLog($tasks->id , $user_id, "closed", $date);
							if($_POST['oldstatus'] != 4){
								$tasks->addStatusNote($tasks->us_id,$tasks->id,$user_id);
							}
							$task_closed = true;
						}
						
						if($tasks->id == 0){$tasks->capacity -= $tasks->planned_capacity;}
						$tasks->editTask();

						if($_POST['oldstatus'] != $tasks->status){
							$tasks->daily_scrum = 1;
						}

						$tasks->setDailyScrum($tasks->id, $tasks->daily_scrum);

						$tasks->setConfirmationStatus($tasks->us_id);
						if($tasks->hasTasksLeft($tasks->us_id) != ""){
							$tasks->closeUserStory($tasks->us_id,80,$user_id);
							email_resolved( $tasks->us_id );
							email_relationship_child_resolved( $tasks->us_id );
						}
						
						if(empty($_POST['id'])){$tasks->setConfirmationStatus($tasks->us_id);}

						# add warning flag if current developer has no capacity
						if($noCapacity == true){
							$addlink = '&warning=2';
						}

						# add warning flag if task is resolved
						if($task_resolved == true){
							$addlink = '&warning=3';
							email_bugnote_add( $tasks->us_id );
						}

						# add warning flag if task is closed
						if($task_closed == true){
							$addlink = '&warning=4';
							email_bugnote_add( $tasks->us_id );
						}

						# return directly to Taskboard, Task Page or Sprint Backlog
						if($_POST['fromSprintBacklog'] == 1 && $_POST['fromTaskPage'] == 1){
							$additional = '&fromSprintBacklog=1';
							header("Location: ".plugin_page('task_page.php&us_id='.$tasks->us_id.$addlink.$additional));
						}

						if($_POST['fromSprintBacklog'] && $_POST['fromTaskPage'] != 1) {
							header("Location: ".plugin_page('sprint_backlog.php')."&sprintName=".urlencode($_POST['sprintName']).$addlink);
						}

						if($_POST['fromTaskboard']) {
							header("Location: ".plugin_page('taskboard.php')."&sprintName=".urlencode($_POST['sprintName']).$addlink);
						}

						if(empty($_POST['fromSprintBacklog']) && empty($_POST['fromTaskboard'])){
							header("Location: ".plugin_page('task_page.php&us_id='.$tasks->us_id.$addlink));
						}
					}
				}
			}
		}
	}

	# get task id
	if( $_POST['id'] ){
		$task_id = $_POST['id'];
	} else {
		$task_id = $tasks->id;
	}

	# load task information
	$task = $tasks->getSelectedTask($task_id);
	$tasks->us_id = $task['us_id'];
	$sprint_end_date = strtotime($getSprint['end']);
	$userstory = $tasks->getAssumedUserStories($task['us_id'], strtotime($getSprint['commit']), mktime(23,59,59,date('m',$sprint_end_date), date('d',$sprint_end_date), date('Y',$sprint_end_date)));
	if($sprint->getUnitId(plugin_config_get('gadiv_task_unit_mode')) != $getSprint['unit_planned_task'] && !isset($_POST['action']) && $getSprint['status'] == 1 && $task['rest_capacity'] == 0.00 && $task['status'] < 3 && date('dmy',$userstory[0]['date_modified']) == date('dmy')){
		$system = plugin_lang_get( 'edit_task_error_106900' );
	}

	if($system){
?>
		<center><span style="color:red; font-size:16px; font-weight:bold;"><?php echo $system?></span></center>
	<br>
<?php	
	}
	
	# make task unit changes if sprint is not running
	if($getSprint['status'] > 0){
		if($getSprint['unit_planned_task'] > 0 ){
			$unit = '('.$tasks->getUnitById($getSprint['unit_planned_task']).')';
			$currentUnit = $tasks->getUnitById($getSprint['unit_planned_task']);
		}
	} else {
		if(plugin_config_get('gadiv_task_unit_mode') != ""){
			$unit = '('.plugin_config_get('gadiv_task_unit_mode').')';
			$currentUnit = plugin_config_get('gadiv_task_unit_mode');
		}
	}
?>
<form action="" method="post">
<input type="hidden" name="action" value="editTask">
<input type="hidden" name="id" value="<?php echo $task['id']?>">
<input type="hidden" name="us_id" value="<?php echo $tasks->us_id?>">
<input type="hidden" name="sprintName" value="<?php echo $sprint->sprint_id?>">
<input type="hidden" name="planned_capacity" value="<?php echo $task['planned_capacity']?>">
<input type="hidden" name="performed_capacity" value="<?php echo $task['performed_capacity']?>">
<input type="hidden" name="rest_capacity" value="<?php echo $task['rest_capacity']?>">
<input type="hidden" name="old_rest_capacity" value="<?php echo $task['rest_capacity']?>">
<input type="hidden" name="fromSprintBacklog" value="<?php echo $_POST['fromSprintBacklog']?>">
<input type="hidden" name="fromTaskboard" value="<?php echo $_POST['fromTaskboard']?>">
<input type="hidden" name="fromTaskPage" value="<?php echo $_POST['fromTaskPage']?>">
<input type="hidden" name="currentUnit" value="<?php echo $currentUnit?>">
<?php if($getSprint['status'] == 0){ ?>
	<input type="hidden" name="status" value="<?php echo $task['status']?>">
<?php } ?>
<?php if($getSprint['status'] == 1){ ?>
	<input type="hidden" name="planned_capacity" value="<?php echo $task['planned_capacity'] ?>">
<?php } ?>
	<table align="center" class="width75" cellspacing="1">
	<tr>
		<td class="form-title" colspan="3">
			<?php echo plugin_lang_get( 'edit_task_title' )?>
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo plugin_lang_get( 'edit_task_name' )?>
		</td>
		<td>
			<input type="text" style="width:400px;" name="name" value="<?php if($_POST['name']){?><?php echo $_POST['name']?><?php } else {?><?php echo $task['name']?><?php }?>">
		</td>
	</tr>

	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
		  <?php echo plugin_lang_get( 'common_description' )?>
		</td>
		<td>
			<textarea name="description" style="height:200px; width:400px;"><?php echo $task['description']?></textarea>
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo plugin_lang_get( 'edit_task_developer' )?>
		</td>
		<td>
			<select name="developer" style="width:400px;" <?php if($task['performed_capacity'] > 0 || $task['status'] >= 3 || $task['status'] == 5){?>disabled<?php }?>>
				<option value="0" <?php if($task['developer_id']==0){?>selected<?php }?>><?php echo plugin_lang_get( 'common_chose' )?></option>
				<?php
					# get all team developers
					$team->id = $getSprint['team_id'];
					$user = $team->getTeamDeveloper();
					foreach($user AS $num => $row){?>
						<?php if($row['id'] != 0){?>
							<option value="<?php echo $row['id']?>" <?php if($task['developer_id'] == $row['id']){?>selected<?php }?>><?php echo $row['username']?></option>
						<?php }?>
				<?php } ?>
			</select>
			<?php if($task['performed_capacity'] > 0 || ($task['status'] >= 3 && $task['developer_id'] > 0)){?>
				<input type="hidden" name="developer" value="<?php echo $task['developer_id']?>">
			<?php }?>
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
		   <?php echo plugin_lang_get( 'edit_task_planned' )?> <?php echo $unit?>
		</td>
		<td>
			<?php if($getSprint['status'] == 0){?>
				<input style="width:400px;" type="text" name="planned_capacity"  value="<?php echo $task['planned_capacity'] ?>">
			<?php } else {?>
				<?php echo $task['planned_capacity'] ?>
			<?php }?>
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo plugin_lang_get( 'edit_task_rest' )?> <?php echo $unit?>
		</td>
		<td>
			<?php if($getSprint['status'] == 1){ ?>
				<input style="width:400px;" type="text" name="rest_capacity"  value="<?php echo $task['rest_capacity'] ?>">
			<?php } ?>
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo plugin_lang_get( 'edit_task_performed' )?> <?php echo $unit?>
		</td>
		<td>
			<?php if($getSprint['status'] == 1 && $task['status'] < 4 && $task['developer_id'] > 0){ ?>
				<input style="width:400px;" type="text" name="performed_capacity_today" value="">
			<?php } ?>
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			Status
		</td>
		<td>
			<?
				# status
				if($task['status'] == 0){$oldStatus = plugin_lang_get( 'status_new' );}
				if($task['status'] == 1){$oldStatus = plugin_lang_get( 'status_new' );}
				if($task['status'] == 2){$oldStatus = plugin_lang_get( 'status_assigned' );}
				if($task['status'] == 3){$oldStatus = plugin_lang_get( 'status_confirmed' );}
				if($task['status'] == 4){$oldStatus = plugin_lang_get( 'status_resolved' );}
				if($task['status'] == 5){$oldStatus = plugin_lang_get( 'status_closed' );}
			?>
			<input type="hidden" name="oldstatus" value="<?php echo $task['status']?>">
			<select name="status" style="width:400px;" <?php if($getSprint['status'] == 0 || $task['id'] == 0){?>disabled<?php }?>>
				<?php if($task['status'] != 0){?>
					<option value="<?php echo $task['status']?>" selected><?php echo $oldStatus ?></option>
				<?php }?>
				<?php if($_POST['resolved'] || $_POST['status'] == 4){?>
					<option value="4" selected><?php echo plugin_lang_get( 'status_resolved' ); ?></option>
				<?php }?>
				<?php if($task['id'] == 0 || $task['status'] == 4){?>
				<option value="1"><?php echo plugin_lang_get( 'status_new' ) ?></option>
				<?php }?>
				<?php if($task['status'] != 2 && $task['status'] > 1 && $task['status'] != 5){?>
					<option value="2"><?php echo plugin_lang_get( 'status_assigned' ) ?></option>
				<?php }?>
				<?php if($task['status'] != 3 && $task['status'] > 1 && $task['status'] != 5){?>
					<option value="3"><?php echo plugin_lang_get( 'status_confirmed' ) ?></option>
				<?php }?>
				<?php if($task['status'] != 4){?>
					<option value="4"><?php echo plugin_lang_get( 'status_resolved' ) ?></option>
				<?php }?>
				<?php if($task['status'] != 5){?>
					<option value="5"><?php echo plugin_lang_get( 'status_closed' ) ?></option>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="center" colspan="2">
			<input type="submit" name="submit" value="<?php echo plugin_lang_get( 'button_save' )?>">
			<?php if(($getSprint['status'] == 0 || ($task['developer_id'] == 0 && $task['planned_capacity'] == '0.00')) && $task['id'] > 0 && $task['status'] == 1){?>
				<input type="submit" name="delete" value="<?php echo 'Löschen' ?>">
			<?php } elseif($getSprint['status'] == 1 && $task['status'] < 4) {?>
				<input type="submit" name="resolved" value="<?php echo plugin_lang_get( 'button_resolve' )?>">
			<?php }?>
			<?php if($task['id'] > 0 && $task['status'] < 4 && $task['performed_capacity'] > 0 && $getSprint['status'] == 1){?>
				<input type="submit" name="divide_task" value="<?php echo plugin_lang_get( 'button_assume' )?>">
			<?php }?>
			<input type="submit" name="back_button" value="<?php echo plugin_lang_get( 'button_back' )?>">
		</td>
	</tr>
	</table>
	<?php if($task['id'] > 0){?>
	<br>
	<table align="center" class="width75" cellspacing="1">
		<tr>
			<td colspan="2">
				<b><?php echo plugin_lang_get( 'edit_task_log' )?></b>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo plugin_lang_get( 'edit_task_created' )?>
			</td>
			<td>
				<?php $taskEvents = $tasks->getTaskEvent($task['id'],'created');$date = strtotime($taskEvents['date']);$userData = $tasks->getUserById($taskEvents['user_id']);if(!empty($userData)){echo $userData;?> / <?php echo date('d.m.Y',$date);}?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo plugin_lang_get( 'edit_task_assigned' )?>
			</td>
			<td>
				<?php $taskEvents = $tasks->getTaskEvent($task['id'],'confirmed');$date = strtotime($taskEvents['date']);$userData = $tasks->getUserById($taskEvents['user_id']);if(!empty($userData)){echo $userData;?> / <?php echo date('d.m.Y',$date);}?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo plugin_lang_get( 'edit_task_resolved' )?>
			</td>
			<td>
				<?php $taskEvents = $tasks->getTaskEvent($task['id'],'resolved');$date = strtotime($taskEvents['date']);$userData = $tasks->getUserById($taskEvents['user_id']);if(!empty($userData)){echo $userData;?> / <?php echo date('d.m.Y',$date);}?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo plugin_lang_get( 'edit_task_reopened' )?>
			</td>
			<td>
				<?php $taskEvents = $tasks->getTaskEvent($task['id'],'reopened');$date = strtotime($taskEvents['date']);$userData = $tasks->getUserById($taskEvents['user_id']);if(!empty($userData)){echo $userData;?> / <?php echo date('d.m.Y',$date);}?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo plugin_lang_get( 'edit_task_closed' )?>
			</td>
			<td>
				<?php $taskEvents = $tasks->getTaskEvent($task['id'],'closed');$date = strtotime($taskEvents['date']);$userData = $tasks->getUserById($taskEvents['user_id']);if(!empty($userData)){echo $userData;?> / <?php echo date('d.m.Y',$date);}?>
			</td>
		</tr>
	</table>
	<?php }?>
</form>
<?php html_page_bottom() ?>