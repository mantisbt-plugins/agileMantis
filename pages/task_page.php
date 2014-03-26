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

	if($_GET['us_id'] > 0){
	
	html_page_top(plugin_lang_get( 'edit_tasks_title' )); 
?>
<br>
<?php
	# collect task, user story and sprint information
	$tasks->us_id = (int) $_GET['us_id'];
	$usData = $tasks->checkForUserStory($tasks->us_id);
	
	# get further sprint information
	$sprint->sprint_id = $usData['sprint'];
	$getSprint = $sprint->getSprintById();

	#save story points and planned work from chosen user story
	if($_POST['action'] == 'save'){
		$sprint->addStoryPoints($tasks->us_id, $_POST['storypoints'],$usData['storypoints']);
		$sprint->addPlannedWork($tasks->us_id, $_POST['plannedWork'],$usData['plannedWork']);
		header("Location: " . plugin_page('task_page.php&us_id='.$tasks->us_id));
	}

	# divide task action
	if($_POST['divide_task'] == plugin_lang_get( 'button_assume' )){
		
		# common
		$tasks->us_id 					= 	$_POST['us_id'];
		$tasks->name 					=	$_POST['task_name'];
		$tasks->description 			= 	$_POST['task_description'];
		$tasks->daily_scrum				= 	1;

		# old task information
		$tasks->id 						= 	$_POST['task_id'];
		$tasks->developer				= 	$_POST['developer_id'];
		$tasks->status					= 	4;
		$tasks->planned_capacity 		= 	$_POST['planned_capacity'];
		$tasks->rest_capacity 			= 	0;
		$tasks->addStatusNote($tasks->us_id,$tasks->id,$user_id);
		$tasks->editTask();
		$tasks->setDailyScrum($tasks->id, $tasks->daily_scrum);

		# new task information
		$tasks->id 						= 	0;
		$tasks->us_id 					= 	$_POST['us_id'];
		$tasks->developer				= 	0;
		$tasks->status					= 	1;
		if($getSprint['status'] ==  0){
			$tasks->planned_capacity 	= 	$_POST['rest_capacity'];
		} else {
			$tasks->planned_capacity 	= 	0;
		}
		$tasks->rest_capacity			= 	$_POST['rest_capacity'];
		$tasks->capacity 		   		= 	0;
		$tasks->editTask();
		$tasks->setDailyScrum($tasks->id, $tasks->daily_scrum);
	}

	# add new task
	if($_POST['action'] == 'addTask' && $_SESSION['uniqformid'] != $_POST['uniqformid']){
		$_SESSION['uniqformid'] = $_POST['uniqformid'];
		if($_POST['name'] == ""){
			$system = plugin_lang_get( 'edit_tasks_error_922800' );
		}

		$date 						= date('Y').'-'.date('m').'-'.date('d');
		$tasks->developer 			= (int) $_POST['developer'];
		$tasks->us_id				= (int) $_POST['us_id'];
		$tasks->id 					= (int) $_POST['id'];
		$tasks->user_id 			= (int) auth_get_current_user_id();
		$tasks->name 				= $_POST['name'];
		$tasks->description 		= $_POST['description'];

		if($_POST['id'] == 0){
			$tasks->unit			=	$tasks->getUnitId(plugin_config_get('gadiv_task_unit_mode'));
		}
		$_POST['planned_capacity'] = str_replace(',','.',$_POST['planned_capacity']);
		
		if($getSprint['status'] == 0){
			$tasks->planned_capacity = str_replace(',','.',$_POST['planned_capacity']);
		}

		$tasks->rest_capacity  	 	= str_replace(',','.',$_POST['planned_capacity']);
		$tasks->performed_capacity	= str_replace(',','.',$_POST['performed_capacity']);
		$tasks->capacity 			-= $tasks->planned_capacity;
		$tasks->status				= $_POST['status'];

		if($tasks->developer > 0 && $tasks->status < 4 && $tasks->getDeveloperSprintCapacity($_POST['currentUnit']) == 0 && ($_POST['currentUnit'] == 'h' || $_POST['currentUnit'] == 'T')){
			$_GET['warning'] = 2;
		}
		
		if($_POST['sprintName'] != '' && $_POST['id'] == 0 && $system == "" && $tasks->rest_capacity == 0.00){
			$userstories = $sprint->getSprintStories($_POST['sprintName'], null, 0);
			$tasks_have_been_planned = false;
			if(!empty($userstories)){
				foreach($userstories AS $num => $row){
					$sprintTasks = $sprint->getSprintTasks($row['id'],0);
					if(!empty($sprintTasks)){
						foreach($sprintTasks AS $key => $value){
							if($value['planned_capacity'] > 0.00){
								$tasks_have_been_planned = true;
							}
						}
					}
				}
			}
		}
		
		# add warning flag if others tasks have been planned already
		if($tasks_have_been_planned == true){
			$_GET['warning'] = 1;
		}
		
		if(!empty($_POST['planned_capacity']) && ($_POST['planned_capacity'] < 0 || !is_numeric($_POST['planned_capacity']))){
			$system = plugin_lang_get( 'edit_tasks_error_985800' );
		}
		
		if($system == ""){
			if($tasks->developer > 0){
				$tasks->status = 2;
				$userstory->addBugMonitor($tasks->developer,$tasks->us_id);
			}
			$tasks->editTask();
			if($getSprint['status'] == 1){
				$tasks->setDailyScrum($tasks->id, 1);
			}
			$tasks->developer 			= "";
		}
	}

	$usSumText = $tasks->getUserStoryById();

	$array = explode(',',lang_get( 'status_enum_string' ));
	foreach($array AS $num => $row){
		$temp = explode(':',$row);
		$status[$temp[0]] = $temp[1];
	}

	$array = explode(',',$g_status_enum_string);
	foreach($array AS $num => $row){
		$temp = explode(':',$row);
		$userstory_status[$temp[0]] = $temp[1];
	}

	if($sprint->getUnitId(plugin_config_get('gadiv_task_unit_mode')) != $getSprint['unit_planned_task'] && isset($_POST['add_task']) && $getSprint['status'] == 1){
		$system = plugin_lang_get( 'edit_tasks_error_106800' );
	}
	
	$request = array_merge($_POST, $_GET);
?>
<?php if($_GET['warning'] == 1){
	$warning = plugin_lang_get( 'edit_tasks_error_120800' );?>
<?php }?>
<?php if($_GET['warning'] == 2){
	$warning = plugin_lang_get( 'edit_tasks_error_108800' );?>
<?php }?>
<?php if($_GET['warning'] == 3){
	$warning = plugin_lang_get( 'edit_tasks_error_107800' );?>
<?php }?>
<?php if($_GET['warning'] == 4){
	$warning = plugin_lang_get( 'edit_tasks_error_107801' );?>
<?php }?>
<?php if($system){?>
<center><span style="color:red; font-size:16px; font-weight:bold;"><?php echo $system?></span></center>
<br>
<?php }?>
<?php if($warning){?>
<center><span style="color:red; font-size:16px; font-weight:bold;"><?php echo $warning?></span></center>
<br>
<?php }?>
<table align="center" class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="6">
				User Story - <span style="font-weight:bold;color:grey;">"<?php echo $usSumText[0]['summary']?>"</span>
			</td>
		</tr>
		</tr>
			<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				ID
			</td>
			<td>
			  <a href="http://<?php echo $_SERVER['HTTP_HOST'].SUBFOLDER?>view.php?id=<?php echo $usSumText[0]['id']?>"><?php echo $usSumText[0]['id']?></a>
			</td>
			<td class="category">
				Status
			</td>
			<td style="background-color:<?php echo $g_status_colors[$userstory_status[$usSumText[0]['status']]]?>">
				<?php echo $status[$usSumText[0]['status']];?>
			</td>
			<td class="category">
				<?php echo plugin_lang_get( 'edit_tasks_category' )?>
			</td>
			<td>
				<?php echo $tasks->getCategoryById($usSumText[0]['category_id'])?>
			</td>
			<td class="category">
				<?php echo plugin_lang_get( 'edit_tasks_severity' )?>
			</td>
			<td colspan="3">
				<?php
					$array = explode(',',lang_get( 'severity_enum_string' ));
					foreach($array AS $num => $row){
						$temp = explode(':',$row);
						$severity[$temp[0]] = $temp[1];
					}
					echo $severity[$usSumText[0]['severity']];
				?>
			</td>
		</tr>
		<form action="<?php echo plugin_page("task_page.php&us_id=".$tasks->us_id)?>" method="post">
		<input type="hidden" name="action" value="save">
		<?php $col_rd = 0;$col_pc = 0;$col_tech = 0;$col_pr = 0;$col_ro = 0;?>
		<?php if(plugin_config_get('gadiv_release_documentation')=='1'){$show_rd = true;$col_rd -= 2;$minus -= 2;}?>
		<?php if(plugin_config_get('gadiv_tracker_planned_costs')=='1'){$show_pc = true;$col_pc -= 2;}?>
		<?php if(plugin_config_get('gadiv_technical')=='1'){$show_tech = true;$col_tech -= 2;}?>
		<?php if(plugin_config_get('gadiv_presentable')=='1'){$show_pr = true;$col_pr -= 2;}?>
		<?php if(plugin_config_get('gadiv_ranking_order')=='1'){$show_ro = true;$col_ro -= 3;}?>
		<?php if($show_rd == true || $show_pr == true || $show_tech == true){$minus -= 2;}?>
		<?php
			if($getSprint['status'] > 0 || $usSumText[0]['status'] >= 80){
				$disable_storypoints = 'disabled';
				echo '<input type="hidden" name="storypoints" value="'.$usData['storypoints'].'">';
			} else {
				$disable_storypoints = '';
			}
		?>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">Storypoints</td>
			<td>
				<?php if(plugin_config_get('gadiv_storypoint_mode') == 1){?>
					<input type="text" name="storypoints" style="width:30px;" maxlength="3" value="<?php echo $usData['storypoints']?>" <?php echo $disable_storypoints?>>
				<?php } else {?>
					<select name="storypoints" style="width:40px;" <?php echo $disable_storypoints?>>
					<option value=""></option>
						<?php $pb->getFibonacciNumbers($usData['storypoints']);?>
					</select>
				<?php }?>
			</td>
			<?php if($show_pc == true){?>
				<td class="category"><?php echo lang_get( 'PlannedWork' ) ?></td>
				<td><input type="text" name="plannedWork" style="width:40px;" maxlength="4" value="<?php if(!empty($usData['plannedWork'])){?><?php echo $usData['plannedWork']?><?php }?>"> <?php echo plugin_config_get('gadiv_userstory_unit_mode')?></td>
			<?php }?>
			<td class="category">Business Value</td>
			<td colspan="<?php echo (4+ $col_ro + $col_pc)?>"><?php echo $usData['businessValue']?></td>
			<?php if($show_ro == true){?>
				<td class="category"><?php echo lang_get( 'RankingOrder' ) ?></td>
				<td colspan="2"><?php echo $usData['rankingorder']?></td>
			<?php }?>
			<td colspan="3">
				<input type="submit" name="submit" value="<?php echo plugin_lang_get( 'edit_tasks_save_changes' )?>">
			</td>
		</tr>
		</form>
		<?php
			$sprint = $tasks->getCustomFieldSprint($usSumText[0]['id']);
			$productBacklog = $tasks->getCustomFieldProductBacklog($usSumText[0]['id']);

			if($usData['presentable']==0){$presentable = plugin_lang_get( 'view_issue_non_presentable' ) ;}
			if($usData['presentable']==1){$presentable = plugin_lang_get( 'view_issue_technical_presentable' ) ;}
			if($usData['presentable']==2){$presentable = plugin_lang_get( 'view_issue_functional_presentable' ) ;}

			if($usData['inReleaseDocu'] == 1){
				$inReleaseDocu = 'checked';
			} else {
				$inReleaseDocu = '';
			}

			if($usData['technical'] == 1){
				$technical = 'checked';
			} else {
				$technical = '';
			}
		?>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">Produkt Backlog</td>
			<td><?php echo $productBacklog['name']?></td>
			<?php if($show_pr == true && $show_tech == true && $show_rd == true){?>
				<td class="category">Sprint</td>
				<td><?php echo $sprint['name']?></td>
				<td class="category"><?php echo lang_get( 'Presentable' )?></td>
				<td><?php echo $presentable?></td>
				<td class="category"><?php echo lang_get( 'Technical' )?></td>
				<td><input type="checkbox" name="technical" <?php echo $technical?> value="1" disabled></td>
				<td class="category"><?php echo lang_get( 'InReleaseDocu' )?></td>
				<td><input type="checkbox" name="inReleaseDocu" <?php echo $inReleaseDocu?> value="1" disabled></td>
			<?php }?>
			<?php if($show_pr == true && $show_tech == false && $show_rd == false){?>
				<td class="category">Sprint</td>
				<td><?php echo $sprint['name']?></td>
				<td class="category"><?php echo lang_get( 'Presentable' )?></td>
				<td colspan="5"><?php echo $presentable?></td>
			<?php }?>
			<?php if($show_pr == false && $show_tech == true && $show_rd == false){?>
				<td class="category">Sprint</td>
				<td><?php echo $sprint['name']?></td>
				<td class="category"><?php echo lang_get( 'Technical' )?></td>
				<td colspan="5"><input type="checkbox" name="technical" <?php echo $technical?> value="1" disabled></td>
			<?php }?>
			<?php if($show_pr == false && $show_tech == false && $show_rd == true){?>
				<td class="category">Sprint</td>
				<td><?php echo $sprint['name']?></td>
				<td class="category"><?php echo lang_get( 'InReleaseDocu' )?></td>
				<td colspan="5"><input type="checkbox" name="inReleaseDocu" <?php echo $inReleaseDocu?> value="1" disabled></td>
			<?php }?>
			<?php if($show_pr == true && $show_tech == true && $show_rd == false){?>
				<td class="category">Sprint</td>
				<td><?php echo $sprint['name']?></td>
				<td class="category"><?php echo lang_get( 'Presentable' )?></td>
				<td><?php echo $presentable?></td>
				<td class="category"><?php echo lang_get( 'Technical' )?></td>
				<td colspan="3"><input type="checkbox" name="technical" <?php echo $technical?> value="1" disabled></td>
			<?php }?>
			<?php if($show_pr == true && $show_tech == false && $show_rd == true){?>
				<td class="category">Sprint</td>
				<td><?php echo $sprint['name']?></td>
				<td class="category"><?php echo lang_get( 'Presentable' )?></td>
				<td><?php echo $presentable?></td>
				<td class="category"><?php echo lang_get( 'InReleaseDocu' )?></td>
				<td colspan="3"><input type="checkbox" name="inReleaseDocu" <?php echo $inReleaseDocu?> value="1" disabled></td>
			<?php }?>
			<?php if($show_pr == false && $show_tech == true && $show_rd == true){?>
				<td class="category">Sprint</td>
				<td><?php echo $sprint['name']?></td>
				<td class="category"><?php echo lang_get( 'Technical' )?></td>
				<td><input type="checkbox" name="technical" <?php echo $technical?> value="1" disabled></td>
				<td class="category"><?php echo lang_get( 'InReleaseDocu' )?></td>
				<td colspan="3"><input type="checkbox" name="inReleaseDocu" <?php echo $inReleaseDocu?> value="1" disabled></td>
			<?php }?>
			<?php if($show_pr == false && $show_tech == false && $show_rd == false){?>
				<td class="category">Sprint</td>
				<td colspan="7"><?php echo $sprint['name']?></td>
			<?php } ?>
		</tr>
		</tr>
			<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'summary' )?>
			</td>
			<td colspan="9">
			  <?php echo $usSumText[0]['summary']?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo plugin_lang_get( 'common_description' )?>
			</td>
			<td colspan="9">
			  <?php echo nl2br($usSumText[0]['description'])?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'steps_to_reproduce' )?>
			</td>
			<td colspan="9">
			  <?php echo nl2br($usSumText[0]['steps_to_reproduce'])?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'additional_information' )?>
			</td>
			<td colspan="9">
			  <?php echo nl2br($usSumText[0]['additional_information'])?>
			</td>
		</tr>
</table>
<br>
<table align="center" class="width100" cellspacing="1">
	<tr>
		<td class="form-title" colspan="6">
			Tasks - <span style="font-weight:bold;color:grey;">"<?php echo $usSumText[0]['summary']?>"</span>
		</td>
	</tr>
	<?php 
		# change task unit if sprint is not running
		if($getSprint['status'] > 0){
			if($getSprint['unit_planned_task'] > 0 ){
				$unit = '('.$userstory->getUnitById($getSprint['unit_planned_task']).')';
				$currentUnit = $userstory->getUnitById($getSprint['unit_planned_task']);
			}
		} else {
			if(plugin_config_get('gadiv_task_unit_mode') != ""){
				$unit = '('.plugin_config_get('gadiv_task_unit_mode').')';
				$currentUnit = plugin_config_get('gadiv_task_unit_mode');
			}
		}
	?>
	<tr>
		<td class="category"><?php echo plugin_lang_get( 'edit_tasks_name' )?></td>
		<td class="category">Status</td>
		<td class="category"><?php echo plugin_lang_get( 'edit_tasks_assigned_developer' )?></td>
		<td class="category"><?php echo plugin_lang_get( 'edit_tasks_planned' )?> <?php echo $unit?></td>
		<td class="category"><?php echo plugin_lang_get( 'edit_tasks_rest' )?><?php echo $unit?></td>
		<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
	</tr>
	<?php
		# get all task from a user story
		$userStoryTasks = $userstory->getUserStoryTasks($tasks->us_id);
		if(!empty($userStoryTasks)){
			foreach($userStoryTasks AS $num => $row){
				# change background color and set status text for each task
				$status = $agm->agileMantisStatusColorsAndNames($row['status']);
				
				if($row['status'] < 4){
					$tasks->setConfirmationStatus($tasks->us_id);
				}
		?>
			<tr style="background-color:<?php echo $status['color']?>" >
				<td><?php echo $row['name']?></td>
				<td><?php echo $status['name']?></td>
				<td><?php echo $tasks->getDeveloperById($row['developer_id'])?></td>
				<td><?php echo $row['planned_capacity']?></td>
				<td><?php echo $row['rest_capacity']?></td>
				<td>
					<form action="<?php echo  plugin_page('edit_task.php') ?>" method="post">
						<input type="hidden" name="id" value="<?php echo $row['id'] ?>">
						<input type="hidden" name="us_id" value="<?php echo $tasks->us_id ?>">
						<input type="hidden" name="sprintName" value="<?php echo $getSprint['name'] ?>">
						<input type="hidden" name="fromSprintBacklog" value="<?php echo $request['fromSprintBacklog']?>">
						<input type="hidden" name="fromTaskPage" value="1">
						<input type="submit" name="submit" value="<?php echo plugin_lang_get( 'edit_tasks_edit' )?>">
					</form>
					<?php if($row['id'] > 0 && $row['status'] < 4 && $row['status'] != 1 && $row['performed_capacity'] > 0 && $getSprint['status'] == 1){?>
					<form action="<?php echo plugin_page('task_page.php')."&us_id=".$tasks->us_id?>" method="post">
						<input type="hidden" name="task_id" value="<?php echo $row['id']?>">
						<input type="hidden" name="us_id" value="<?php echo $tasks->us_id?>">
						<input type="hidden" name="developer_id" value="<?php echo $row['developer_id']?>">
						<input type="hidden" name="task_name" value="<?php echo $row['name']?>">
						<input type="hidden" name="task_description" value="<?php echo $row['description']?>">
						<input type="hidden" name="task_status" value="<?php echo $row['status']?>">
						<input type="hidden" name="rest_capacity" value="<?php echo $row['rest_capacity']?>">
						<input type="hidden" name="planned_capacity" value="<?php echo $row['planned_capacity']?>">
						<input type="hidden" name="performed_capacity" value="<?php echo $row['performed_capacity']?>">
						<input type="hidden" name="fromSprintBacklog" value="<?php echo $request['fromSprintBacklog']?>">
						<input type="submit" name="divide_task" value="<?php echo plugin_lang_get( 'button_assume' )?>">
					</form>
					<?php }?>
				</td>
			</tr>
		<?php }
	}?>
</table>
<br>
<form action="" method="post">
	<input type="hidden" name="action" value="addTask">
	<input type="hidden" name="id" value="0">
	<input type="hidden" name="us_id" value="<?php echo $tasks->us_id?>">
	<input type="hidden" name="status" value="1">
	<input type="hidden" name="fromSprintBacklog" value="<?php echo $_POST['fromSprintBacklog']?>">
	<input type="hidden" name="sprintName" value="<?php echo $_POST['sprintName']?>">
	<input type="hidden" name="uniqformid" value="<?php echo md5(uniqid(microtime(),1)) ?>"/>
	<input type="hidden" name="currentUnit" value="<?php echo $currentUnit?>">
	<table align="center" class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="4">
				<?php echo plugin_lang_get( 'edit_tasks_add' )?>
			</td>
		</tr>
		<tr>
			<td class="category"><?php echo plugin_lang_get( 'edit_tasks_name' )?></td>
			<td class="category"><?php echo plugin_lang_get( 'common_description' )?></td>
			<td class="category"><?php echo plugin_lang_get( 'edit_tasks_developer' )?></td>
			<td class="category"><?php echo plugin_lang_get( 'edit_tasks_planned' )?></td>
			<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
		</tr>
		<tr style="background-color:#fcbdbd">
			<td style="width:20%;"><input type="text" name="name" style="width:98%;" value=""></td>
			<td style="width:20%;"><textarea name="description" style="width:98%; height:50px;"><?php if($_POST['description'] && $system != ""){ echo $_POST['description'];}?></textarea></td>
			<td style="width:20%;">
				<select name="developer" style="width:98%;">
					<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
					<?php
						# get all developers by team id
						$team->id = $getSprint['team_id'];
						$user = $team->getTeamDeveloper();
						foreach($user AS $num => $row){?>
							<?php if($row['id'] != 0){?>
								<option value="<?php echo $row['id']?>" <?php if($row['id'] == $_POST['developer'] && $system != ""){ echo 'selected';}?>><?php echo $row['username']?></option>
							<?php }?>
					<?php	}
					?>
				</select>
			</td>
			<td style="width:20%;"><input type="text" name="planned_capacity" value="<?php if($system != ""){ echo $_POST['planned_capacity'];}?>"></td>
			<td style="width:20%;"><input type="submit" name="submit" value="<?php echo plugin_lang_get( 'edit_tasks_add' )?>"></td>
		</tr>
	</table>
</form>
<br>
<center>
	<form action="<?php echo plugin_page("edit_task.php")?>" method="post">
		<input type="hidden" name="us_id" value="<?php echo $tasks->us_id?>">
	</form>
	<?php
	# redirect back to sprint backlog or view issue page
	if($request['fromSprintBacklog'] == 1){
		$redirect = plugin_page('sprint_backlog.php')."&sprintName=".urlencode($getSprint['name']);
	} else {
		$redirect = 'view.php?id='.$tasks->us_id;
	}
	?>
	<form action="<?php echo $redirect?>" method="post">
		<input type="submit" name="back_button" value="<?php echo plugin_lang_get( 'button_back' )?>">
	</form>
</center>
<?php
	# add bug note form
	$f_bug_id = $tasks->us_id;
	include( $tpl_mantis_dir . 'plugins/agileMantis/pages/bugnote_add_inc.php' );
?>
<?php html_status_legend();?>
<?php html_page_bottom() ?>
<?php }?>