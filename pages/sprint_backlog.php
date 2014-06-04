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

	# include additional sprint functionality
	include(PLUGIN_URI.'pages/sprint_backlog_functions.php');

	# delete a task
	if($_POST['delete']){
		$user_id = auth_get_current_user_id();
		$tasks->id = $_POST['task_id'];
		$tasks->deleteTask();
		$tasks->updateTaskLog($tasks->id , $user_id, "delete", time());
		$tasks->deleteTaskLog($tasks->id);

		if($tasks->hasTasksLeft($_POST['us_id']) != ""){
			$tasks->closeUserStory($_POST['us_id'],80,$user_id);
			email_resolved($_POST['us_id']);
			email_relationship_child_resolved($_POST['us_id']);
		}
	}

	# submit performed work
	if(($_POST['submit_performed'] != "" || $_POST['resolved'] == plugin_lang_get( 'button_resolve' )) && $_SESSION['uniqformid'] != $_POST['uniqformid']){
		$_SESSION['uniqformid'] 	= $_POST['uniqformid'];
 		$tasks->rest_flag 			= 0;
		$tasks->user_id 			= auth_get_current_user_id();
		$tasks->id 					= (int) $_POST['task_id'];
		$tasks->us_id				= (int) $_POST['us_id'];
		$tasks->developer			= (int) $_POST['developer_id'];
		$tasks->name 				= $_POST['task_name'];
		$tasks->description			= $_POST['task_description'];
		$tasks->status				= $_POST['task_status'];
		$tasks->planned_capacity	= $_POST['planned_capacity'];
		$tasks->performed_capacity	= $_POST['performed_capacity'];
		$tasks->performed			= $_POST['performed'];
		$tasks->performed 			= str_replace(',','.',$tasks->performed);
		$tasks->rest_capacity		= $_POST['rest_capacity'] - $tasks->performed;
		$tasks->capacity			= $tasks->performed;

		# change status to confirmed
		if($tasks->performed_capacity == 0 && $_POST['performed'] != ""){$tasks->status = 3;}
		if($tasks->rest_capacity > 0 && $_POST['performed'] != ""){$tasks->status = 3;}

		# change status to resolved
		if($_POST['resolved'] == plugin_lang_get( 'button_resolve' )) {
			$tasks->status = 4;
			$tasks->rest_capacity = 0;
			$tasks->capacity = 0;
		}

		# check wether developer has enough capacity or not
		if($tasks->developer > 0 && $tasks->status < 4 && !$_POST['copy_tasks'] && ($_POST['currentUnit'] == "h" || $_POST['currentUnit'] == 'T')){
			if(!$tasks->getDeveloperSprintCapacity($_POST['currentUnit'])){
				$hinweis = plugin_lang_get( 'sprint_backlog_error_108701' );
			}
		}

		if($tasks->performed_capacity + str_replace(',','.',$_POST['performed']) < 0 && $_POST['performed'] != 0 && $system == ""){
			$tasks->capacity = 0;
			$tasks->capacity -= $tasks->performed_capacity;
			$tasks->performed_capacity = 0;
			$system = plugin_lang_get( 'sprint_backlog_error_980700' );
			$tasks->rest_capacity = sprintf("%.2f",str_replace(',','.',$_POST['rest_capacity']));
		}

		if($tasks->rest_capacity <= 0 && $system == ""){
			$tasks->status = 4;
			$hinweis = plugin_lang_get( 'sprint_backlog_error_107701' );
			$date = date('Y').'-'.date('m').'-'.date('d');
			$tasks->rest_capacity = 0;
			$tasks->updateTaskLog($tasks->id , $user_id, "resolved", $date);
			$tasks->addStatusNote($tasks->us_id,$tasks->id,$user_id);
		}

		if($system == "") {
			$tasks->saveDailyPerformance(0);
			$tasks->setDailyScrum($tasks->id, 1);
			$tasks->editTask();

			$tasks->setConfirmationStatus($tasks->us_id);

			if($tasks->hasTasksLeft($tasks->us_id) !=""){
				$tasks->closeUserStory($tasks->us_id,80,$user_id);
				email_resolved( $tasks->us_id );
				email_relationship_child_resolved( $tasks->us_id );
			}
		}
	}
	# show chose sprint page or open chosen sprint directly

	if($show_all_sprints == true){
		include(PLUGIN_URI.'pages/chose_sprint.php');
	} else {
		include(PLUGIN_URI.'pages/sprint_backlog_header.php');
?>

<?php if($no_sprints == false){?>
	<br>
	<?php include(PLUGIN_URI.'pages/sprint_backlog_actions.php');?>
	<br>
	<?php
	if(config_get('current_user_sprint_backlog_filter_direction',null,auth_get_current_user_id()) == 'ASC'){
		$direction = 'DESC';
	} else {
		$direction = 'ASC';
	}

	# calculate amount of table columns
	$tableColums = 10;
	$tableColums += plugin_config_get('gadiv_show_rankingorder');
	$tableColums += plugin_config_get('gadiv_show_storypoints');
	?>
	<table align="center" class="width100" cellspacing="1">
		<tr>
			<td colspan="<?php echo $tableColums?>">
				<div style="float:left;">
					<b>User Stories & Tasks</b>
				</div>
				<form action="<?php echo plugin_page("sprint_backlog.php")?>" method="post" style="float:right;margin:0; padding:0;">
					<input type="hidden" name="id" value="<?php echo $s['id']?>">
					<input type="hidden" name="sprintName" value="<?php echo $s['name']?>">
					<input type="hidden" name="action" value="save_sprint_options">
					<input type="checkbox" name="show_project_target_version" <?php if(config_get('show_project_target_version',null,auth_get_current_user_id()) == 1){?>checked<?php }?> value="1" onClick="this.form.submit();"> <?php echo plugin_lang_get( 'sprint_backlog_project' )?>
					<input type="checkbox" name="show_only_open_userstories" <?php if(config_get('show_only_open_userstories',null,auth_get_current_user_id()) == 1){?>checked<?php }?> value="1" onClick="this.form.submit();"> <?php echo plugin_lang_get( 'sprint_backlog_only_undone' )?>
					<input type="checkbox" name="show_only_own_userstories" <?php if(config_get('show_only_own_userstories',null,auth_get_current_user_id()) == 1){?>checked<?php }?> value="1" onClick="this.form.submit();"> <?php echo plugin_lang_get( 'sprint_backlog_only_own' )?>
				</form>
			</td>
		</tr>
		<tr>
			<td class="category" width="20"></td>
			<td class="category"><a href="<?php echo plugin_page("sprint_backlog.php")?>&sprintName=<?php echo urlencode($s['name'])?>&sort_by=id&direction=<?php echo $direction?>">ID</a></td>
			<td class="category" width="20"></td>
			<td class="category"><a href="<?php echo plugin_page("sprint_backlog.php")?>&sprintName=<?php echo urlencode($s['name'])?>&sort_by=summary&direction=<?php echo $direction?>"><?php echo plugin_lang_get( 'sprint_backlog_summary' )?></a></td>
			<td class="category"><?php echo plugin_lang_get( 'sprint_backlog_developer' )?></td>
			<td class="category"><?php echo plugin_lang_get( 'sprint_backlog_planned' )?> <?php echo $unit?></td>
			<td class="category"><?php echo plugin_lang_get( 'sprint_backlog_performed' )?> <?php echo $unit?></td>
			<td class="category"><?php echo plugin_lang_get( 'sprint_backlog_enter_work' )?> <?php echo $unit?></td>
			<td class="category">Rest <?php echo $unit?></td>
			<?php if(plugin_config_get('gadiv_show_storypoints')=='1'){?>
			<td class="category"><a href="<?php echo plugin_page("sprint_backlog.php")?>&sprintName=<?php echo urlencode($s['name'])?>&sort_by=storypoints&direction=<?php echo $direction?>">SP</a></td>
			<?php }?>
			<?php if(plugin_config_get('gadiv_show_rankingorder')=='1'){?>
			<td class="category"><a href="<?php echo plugin_page("sprint_backlog.php")?>&sprintName=<?php echo urlencode($s['name'])?>&sort_by=rankingOrder&direction=<?php echo $direction?>">R</a></td></td>
			<?php }?>
			<?php if(config_get('show_project_target_version',null,auth_get_current_user_id()) == 1){?>
			<td class="category"><a href="<?php echo plugin_page("sprint_backlog.php")?>&sprintName=<?php echo urlencode($s['name'])?>&sort_by=target_version&direction=<?php echo $direction?>"><?php echo plugin_lang_get( 'sprint_backlog_target_version' )?></a></td>
			<?php }?>
			<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
		</tr>
		<?php
			# show each user story which is in a specific sprint in a table
			if(!empty($us)){
				foreach($us AS $num => $row){
					$t_buglist .= $row['id'].',';
					if(config_get('show_only_own_userstories',null,auth_get_current_user_id()) == 1){$user_id = auth_get_current_user_id();} else {$user_id = 0;}

					# get all tasks of a user story
					$t = $sprint->getSprintTasks($row['id'],0);

					# change background color of a user story row
					switch ($row['status']){
						case '40':
							$uscolor = '#FFF494';
						break;
						case '50':
							$uscolor = '#C2DFFF';
						break;
						case '80':
							$uscolor = '#D2F5B0';
						break;
						case '90':
							$uscolor = '#c9ccc4';
						break;
					}
					if(config_get('show_only_own_userstories',null,auth_get_current_user_id()) == 1 && $sprint->isUserTask($row['id'],auth_get_current_user_id())){
						include(PLUGIN_URI.'pages/sprint_backlog_task_row.php');
					} elseif(!config_get('show_only_own_userstories',null,auth_get_current_user_id())) {
						include(PLUGIN_URI.'pages/sprint_backlog_task_row.php');
					}
				}
			}
		?>
		<?php
			# set new bug list cookie
			gpc_set_cookie( config_get( 'bug_list_cookie' ), substr($t_buglist,0,-1) );
		?>
	</table>
	<br>
	<?php html_status_legend();?>
<?php } else {?>
<br>
<center>
	<span style="color:red; font-size:16px; font-weight:bold;"><?php echo $system?></span>
</center>
<?php }}?>
<?php html_page_bottom() ?>
<?php include(PLUGIN_URI.'pages/agileMantisActions.js.php');?>