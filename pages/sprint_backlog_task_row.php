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
?>
<tr style="background-color:<?php echo $uscolor;?>">
	<td>
		<?php if(!bug_is_readonly( $row['id'] )){?><a href="bug_update_page.php?bug_id=<?php echo $row['id']?>" height="16" width="16"><img src="images/update.png" alt="Detailinformation zur User Story bearbeiten" height="16" width="16"></a><?php }?>
	</td>
	<td>
		<a href="view.php?id=<?php echo $row['id']?>">#<?php echo $row['id']?></a>
	</td>
	<td colspan="6">
		<div style="float:left;">
			<?php echo $row['summary']?>
		</div>
	</td>
	<?php if(plugin_config_get('gadiv_show_storypoints')=='1'){?>
	<td><?php echo $row['sp']?></td>
	<?php }?>
	<?php if(plugin_config_get('gadiv_show_rankingorder')=='1'){?>
	<td><?php echo $row['ro']?></td>
	<?php }?>
	<?php if(config_get('show_project_target_version',null,auth_get_current_user_id()) == 1){
		# get user story version information
		$version_info = $version->getVersionInformation($row['project_id'],$row['target_version']);

		# include version dialogue
		include(PLUGIN_URI.'pages/sprint_backlog_version.php');
	?>
	<?php }?>
	<td>
		<form action="<?php echo plugin_page("task_page.php")?>&us_id=<?php echo $row['id']?>" method="post">
			<input type="hidden" name="us_id" value="<?php echo $row['id']?>">
			<input type="hidden" name="sprintName" value="<?php echo $s['name']?>">
			<input type="hidden" name="fromSprintBacklog" value="1">
			<input type="submit" name="add_task" value="<?php echo plugin_lang_get( 'sprint_backlog_add_task' )?>" <?php echo $disable_button?> <?php echo $sprint_end_disable?>>
		</form>
		<?php if($s['status'] == 0){?>
		<form action="<?php echo plugin_page("sprint_backlog.php")?>" method="post">
			<input type="hidden" name="us_id" value="<?php echo $row['id']?>">
			<input type="hidden" name="sprintName" value="<?php echo $s['name']?>">
			<input type="hidden" name="sprint_id" value="<?php echo $s['id']?>">
			<input type="submit" name="revoke_userstory" value="<?php echo plugin_lang_get( 'sprint_backlog_remove_userstory' )?>" <?php echo $disable_button?> <?php if($row['status'] >= 80){?>disabled<?php }?> <?php echo $sprint_end_disable?>>
		</form>
		<?php } else {?>
		<form action="<?php echo plugin_page("divide_userstories.php")?>" method="post">
			<input type="hidden" name="us_id" value="<?php echo $row['id']?>">
			<input type="hidden" name="status" value="<?php echo $row['status']?>">
			<input type="hidden" name="sprintName" value="<?php echo $s['name']?>">
			<input type="hidden" name="fromPage" value="<?php echo $page_name?>">
			<input type="submit" name="copy_userstory" value="<?php echo plugin_lang_get( 'sprint_backlog_divide_userstory' )?>" <?php echo $disable_button?> <?php if($row['status'] >= 80){?>disabled<?php }?> <?php echo $sprint_end_disable?>>
		</form>
		<?php }?>
	</td>
</tr>
<?php
# get tasks to one user story
if(!empty($t)){
	foreach($t AS $key => $value){
		# change task row colors according to task status
		$changed = false;
		$status = $agm->agileMantisStatusColorsAndNames($value['status']);
		$bgcolor = $status['color'];
		?>
		<tr style="<?php echo $style?>">
			<td style="background-color:<?php echo $uscolor;?>;"></td>
			<td style="background-color:<?php echo $uscolor;?>;"></td>
			<td style="background-color:<?php echo $bgcolor;?>;"><?php echo $value['name']?></td>
			<td style="background-color:<?php echo $bgcolor;?>;"><?php if($value['developer_id']>0){echo $team->getDeveloperById($value['developer_id']);}?></td>
			<td style="background-color:<?php echo $bgcolor;?>;"><?php echo $value['planned_capacity']?></td>
			<td style="background-color:<?php echo $bgcolor;?>;"><?php echo $value['performed_capacity']?></td>
			<td style="background-color:<?php echo $bgcolor;?>;width:190px;">
				<form action="<?php echo plugin_page("sprint_backlog.php")?>" method="post">
					<input type="hidden" name="uniqformid" value="<?php echo md5(uniqid(microtime(),1)) ?>"/>
					<input type="hidden" name="sprintName" value="<?php echo $s['name']?>">
					<input type="hidden" name="task_id" value="<?php echo $value['id']?>">
					<input type="hidden" name="us_id" value="<?php echo $row['id']?>">
					<input type="hidden" name="developer_id" value="<?php echo $value['developer_id']?>">
					<input type="hidden" name="task_name" value="<?php echo $value['name']?>">
					<input type="hidden" name="task_description" value="<?php echo $value['description']?>">
					<input type="hidden" name="task_status" value="<?php echo $value['status']?>">
					<input type="hidden" name="rest_capacity" value="<?php echo $value['rest_capacity']?>">
					<input type="hidden" name="planned_capacity" value="<?php echo $value['planned_capacity']?>">
					<input type="hidden" name="performed_capacity" value="<?php echo $value['performed_capacity']?>">
					<input type="hidden" name="currentUnit" value="<?php echo $currentUnit?>">
					<input type="text" name="performed" value="" maxlength="7" style="width:60px;" <?php if($value['status'] > 3 || $value['status'] == 1 || $s['status'] == 0 || $value['rest_capacity'] == 0.00){?>disabled<?php }?>>
					<input type="submit" name="submit_performed" value="<?php echo plugin_lang_get( 'sprint_backlog_enter' )?>" <?php if($value['status'] > 3 || $value['status'] == 1 || $s['status'] == 0 || $value['rest_capacity'] == 0.00){?>disabled<?php }?> <?php echo $sprint_end_disable?>>
				</form>
			</td>
			<td style="background-color:<?php echo $bgcolor;?>;"><?php echo $value['rest_capacity']?></td>
			<?php if(plugin_config_get('gadiv_show_storypoints')=='1'){?>
			<td style="background-color:<?php echo $bgcolor;?>;"></td>
			<?php }?>
			<?php if(plugin_config_get('gadiv_show_rankingorder')=='1'){?>
			<td style="background-color:<?php echo $bgcolor;?>;"></td>
			<?php }?>
			<?php if(config_get('show_project_target_version',null,auth_get_current_user_id()) == 1){?>
			<td style="background-color:<?php echo $bgcolor;?>;"></td>
			<?php }?>
			<td style="background-color:<?php echo $bgcolor;?>;">
				<form action="<?php echo plugin_page("edit_task.php")?>" method="post">
					<input type="hidden" name="us_id" value="<?php echo $row['id']?>">
					<input type="hidden" name="id" value="<?php echo $value['id'] ?>">
					<input type="hidden" name="sprintName" value="<?php echo $s['name']?>">
					<input type="hidden" name="fromSprintBacklog" value="1">
					<input type="submit" name="edit" value="<?php echo plugin_lang_get( 'button_edit' )?>" <?php echo $disable?> <?php echo $disable_button?> <?php echo $sprint_end_disable?>>
				</form>
				<form action="<?php echo plugin_page("sprint_backlog.php")?>" method="post">
					<input type="hidden" name="sprintName" value="<?php echo $s['name']?>">
					<input type="hidden" name="task_id" value="<?php echo $value['id']?>">
					<input type="hidden" name="us_id" value="<?php echo $row['id']?>">
					<input type="hidden" name="developer_id" value="<?php echo $value['developer_id']?>">
					<input type="hidden" name="task_name" value="<?php echo $value['name']?>">
					<input type="hidden" name="task_description" value="<?php echo $value['description']?>">
					<input type="hidden" name="task_status" value="<?php echo $value['status']?>">
					<input type="hidden" name="rest_capacity" value="<?php echo $value['rest_capacity']?>">
					<input type="hidden" name="planned_capacity" value="<?php echo $value['planned_capacity']?>">
					<input type="hidden" name="performed_capacity" value="<?php echo $value['performed_capacity']?>">
					<input type="submit" name="divide_task" value="<?php echo plugin_lang_get( 'button_assume' )?>" <?php if($value['status'] != 3 || $value['performed_capacity'] <= 0 || $s['status'] != 1){?>disabled<?php }?>>
				</form>
				 <?php if($s['status'] == 0 || ($value['developer_id'] == 0 && $value['planned_capacity'] == '0.00' && $value['status'] == 1)){?>
					<form action="<?php echo plugin_page("sprint_backlog.php")?>" method="post">
						<input type="hidden" name="sprintName" value="<?php echo $s['name']?>">
						<input type="hidden" name="task_id" value="<?php echo $value['id']?>">
						<input type="hidden" name="fromSprintBacklog" value="1">
						<input type="submit" name="deleteTask" value="<?php echo plugin_lang_get( 'button_remove' )?>" <?php echo $disable?> <?php echo $disable_button?> <?php echo $sprint_end_disable?>>
						<input type="hidden" name="delete" value="true">
					</form>
				<?php } else {?>
					<?php if($s['status'] == 1 && $value['planned_capacity'] == 0.00 && $value['rest_capacity'] == 0.00){?>
					<form action="<?php echo plugin_page("sprint_backlog.php")?>" method="post">
						<input type="hidden" name="uniqformid" value="<?php echo md5(uniqid(microtime(),1)) ?>"/>
						<input type="hidden" name="sprintName" value="<?php echo $s['name']?>">
						<input type="hidden" name="task_id" value="<?php echo $value['id']?>">
						<input type="hidden" name="us_id" value="<?php echo $row['id']?>">
						<input type="hidden" name="developer_id" value="<?php echo $value['developer_id']?>">
						<input type="hidden" name="action" value="editTask">
						<input type="hidden" name="task_name" value="<?php echo $value['name']?>">
						<input type="hidden" name="task_description" value="<?php echo $value['description']?>">
						<input type="hidden" name="task_status" value="<?php echo $value['status']?>">
						<input type="hidden" name="rest_capacity" value="<?php echo $value['rest_capacity']?>">
						<input type="hidden" name="planned_capacity" value="<?php echo $value['planned_capacity']?>">
						<input type="hidden" name="performed_capacity" value="<?php echo $value['performed_capacity']?>">
						<input type="hidden" name="fromSprintBacklog" value="1">
						<input type="submit" name="resolved" value="<?php echo plugin_lang_get( 'button_resolve' )?>" <?php if($value['status'] >= 4){?>disabled<?php }?><?php echo $sprint_end_disable?>>
					</form>
					<?php } else {?>
					<form action="<?php echo plugin_page("edit_task.php")?>" method="post">
						<input type="hidden" name="sprintName" value="<?php echo $s['name']?>">
						<input type="hidden" name="id" value="<?php echo $value['id']?>">
						<input type="hidden" name="us_id" value="<?php echo $row['id']?>">
						<input type="hidden" name="developer" value="<?php echo $value['developer_id']?>">
						<input type="hidden" name="action" value="editTask">
						<input type="hidden" name="name" value="<?php echo $value['name']?>">
						<input type="hidden" name="description" value="<?php echo $value['description']?>">
						<input type="hidden" name="status" value="4">
						<input type="hidden" name="rest_capacity" value="<?php echo $value['rest_capacity']?>">
						<input type="hidden" name="planned_capacity" value="<?php echo $value['planned_capacity']?>">
						<input type="hidden" name="performed_capacity" value="<?php echo $value['performed_capacity']?>">
						<input type="hidden" name="fromSprintBacklog" value="1">
						<input type="submit" name="resolved" value="<?php echo plugin_lang_get( 'button_resolve' )?>" <?php if($value['status'] >= 4){?>disabled<?php }?><?php echo $sprint_end_disable?>>
					</form>
					<?php } ?>
				<?php }?>
			</td>
		</tr>
	<?php
	}
}
?>
<tr>
	<td colspan="9"></td>
</tr>