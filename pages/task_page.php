<?php
# This file is part of agileMantis.
#
# Developed by: 
# gadiv GmbH
# Bövingen 148
# 53804 Much
# Germany
#
# Email: agilemantis@gadiv.de
#
# Copyright (C) 2012-2014 gadiv GmbH 
#
# agileMantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU Lesser General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public License
# along with agileMantis. If not, see <http://www.gnu.org/licenses/>.


if( $_GET['us_id'] > 0 ) {
	
	html_page_top( plugin_lang_get( 'edit_tasks_title' ) );
	?>
<br>
<?php
	# collect task, user story and sprint information
	$agilemantis_tasks->us_id = ( int ) $_GET['us_id'];
	$usData = $agilemantis_tasks->checkForUserStory( $agilemantis_tasks->us_id );
	
	# get further sprint information
	$agilemantis_sprint->sprint_id = $usData['sprint'];
	$getSprint = $agilemantis_sprint->getSprintById();
	
	#save story points and planned work from chosen user story
	if( $_POST['action'] == 'save' ) {
		$agilemantis_sprint->addStoryPoints( $agilemantis_tasks->us_id, $_POST['storypoints'], 
			$usData['storypoints'] );
		$agilemantis_sprint->addPlannedWork( $agilemantis_tasks->us_id, $_POST['plannedWork'], 
			$usData['plannedWork'] );
		header( "Location: " . plugin_page( 'task_page.php&us_id=' . $agilemantis_tasks->us_id ) );
	}
	
	# divide task action
	if( $_POST['divide_task'] == plugin_lang_get( 'button_assume' ) ) {
		
		# common
		$agilemantis_tasks->us_id = $_POST['us_id'];
		$agilemantis_tasks->name = $_POST['task_name'];
		$agilemantis_tasks->description = $_POST['task_description'];
		$agilemantis_tasks->daily_scrum = 1;
		
		# old task information
		$agilemantis_tasks->id = $_POST['task_id'];
		$agilemantis_tasks->developer = $_POST['developer_id'];
		$agilemantis_tasks->status = 4;
		$agilemantis_tasks->planned_capacity = $_POST['planned_capacity'];
		$agilemantis_tasks->rest_capacity = 0;
		$agilemantis_tasks->addFinishedNote( $agilemantis_tasks->us_id, $agilemantis_tasks->id, 
			$user_id );
		$agilemantis_tasks->editTask();
		$agilemantis_tasks->setDailyScrum( $agilemantis_tasks->id, $agilemantis_tasks->daily_scrum );
		
		# new task information
		$agilemantis_tasks->id = 0;
		$agilemantis_tasks->us_id = $_POST['us_id'];
		$agilemantis_tasks->developer = 0;
		$agilemantis_tasks->status = 1;
		if( $getSprint['status'] == 0 ) {
			$agilemantis_tasks->planned_capacity = $_POST['rest_capacity'];
		} else {
			$agilemantis_tasks->planned_capacity = 0;
		}
		$agilemantis_tasks->rest_capacity = $_POST['rest_capacity'];
		$agilemantis_tasks->capacity = 0;
		$agilemantis_tasks->editTask();
		$agilemantis_tasks->setDailyScrum( $agilemantis_tasks->id, $agilemantis_tasks->daily_scrum );
	}
	
	# add new task
	if( $_POST['action'] == 'addTask' && $_SESSION['uniqformid'] != $_POST['uniqformid'] ) {
		$_SESSION['uniqformid'] = $_POST['uniqformid'];
		if( $_POST['name'] == "" ) {
			$system = plugin_lang_get( 'edit_tasks_error_922800' );
		}
		
		$date = date( 'Y' ) . '-' . date( 'm' ) . '-' . date( 'd' );
		$agilemantis_tasks->developer = ( int ) $_POST['developer'];
		$agilemantis_tasks->us_id = ( int ) $_POST['us_id'];
		$agilemantis_tasks->id = ( int ) $_POST['id'];
		$agilemantis_tasks->user_id = ( int ) auth_get_current_user_id();
		$agilemantis_tasks->name = $_POST['name'];
		$agilemantis_tasks->description = $_POST['description'];
		
		if( $_POST['id'] == 0 ) {
			$agilemantis_tasks->unit = $agilemantis_tasks->getUnitId( 
				plugin_config_get( 'gadiv_task_unit_mode' ) );
		}
		
		if( $getSprint['status'] == 0 ) {
			$agilemantis_tasks->planned_capacity = str_replace( ',', '.', 
				$_POST['planned_capacity'] );
		}
		if( !$agilemantis_tasks->planned_capacity ) {
			$agilemantis_tasks->planned_capacity = 0.00;
		}
		
		$agilemantis_tasks->rest_capacity = str_replace( ',', '.', $_POST['planned_capacity'] );
		if( !$agilemantis_tasks->rest_capacity ) {
			$agilemantis_tasks->rest_capacity = 0.00;
		}
		
		$agilemantis_tasks->performed_capacity = str_replace( ',', '.', 
			$_POST['performed_capacity'] );
		if( !$agilemantis_tasks->performed_capacity ) {
			$agilemantis_tasks->performed_capacity = 0.00;
		}
		
		$agilemantis_tasks->capacity -= $agilemantis_tasks->planned_capacity;
		$agilemantis_tasks->status = $_POST['status'];
		
		if( $agilemantis_tasks->developer > 0 && $agilemantis_tasks->status < 4 &&
			 $agilemantis_tasks->getDeveloperSprintCapacity( $_POST['currentUnit'] ) == 0 &&
			 ($_POST['currentUnit'] == 'h' || $_POST['currentUnit'] == 'T') ) {
			$_GET['warning'] = 2;
		}
		
		if( $_POST['sprintName'] != '' && $_POST['id'] == 0 && $system == "" &&
			 $agilemantis_tasks->rest_capacity == 0.00 ) {
			$userstories = $agilemantis_sprint->getSprintStories( $_POST['sprintName'] );
			$tasks_with_planned_capacity_exist = false;
			if( !empty( $userstories ) ) {
				foreach( $userstories as $num => $row ) {
					$sprintTasks = $agilemantis_sprint->getSprintTasks( $row['id'], 0 );
					if( !empty( $sprintTasks ) ) {
						foreach( $sprintTasks as $key => $value ) {
							if( $value['planned_capacity'] > 0.00 ) {
								$tasks_with_planned_capacity_exist = true;
							}
						}
					}
				}
			}
		}
		
		# add warning flag if others tasks have been planned already
		if( $tasks_with_planned_capacity_exist == true ) {
			$_GET['warning'] = 1;
		}
		
		if( !empty( $_POST['planned_capacity'] ) &&
			 ($_POST['planned_capacity'] < 0 || !is_numeric( $_POST['planned_capacity'] )) ) {
			$system = plugin_lang_get( 'edit_tasks_error_985800' );
		}
		
		if( $system == "" ) {
			if( $agilemantis_tasks->developer > 0 ) {
				$agilemantis_tasks->status = 2;
				$agilemantis_userstory->addBugMonitor( $agilemantis_tasks->developer, 
					$agilemantis_tasks->us_id );
			}
			$agilemantis_tasks->editTask();
			if( $getSprint['status'] == 1 ) {
				$agilemantis_tasks->setDailyScrum( $agilemantis_tasks->id, 1 );
			}
			$agilemantis_tasks->developer = "";
		}
	}
	
	$usSumText = $agilemantis_tasks->getUserStoryById();
	
	$array = explode( ',', lang_get( 'status_enum_string' ) );
	foreach( $array as $num => $row ) {
		$temp = explode( ':', $row );
		$status[$temp[0]] = $temp[1];
	}
	
	$array = explode( ',', $g_status_enum_string );
	foreach( $array as $num => $row ) {
		$temp = explode( ':', $row );
		$userstory_status[$temp[0]] = $temp[1];
	}
	
	if( $agilemantis_sprint->getUnitId( plugin_config_get( 'gadiv_task_unit_mode' ) ) !=
		 $getSprint['unit_planned_task'] && isset( $_POST['add_task'] ) 
				&& $getSprint['status'] == 1 ) {
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
<center>
	<span style="color: red; font-size: 16px; font-weight: bold;"><?php echo $system?></span>
</center>
<br>
<?php }?>
<?php if($warning){?>
<center>
	<span style="color: red; font-size: 16px; font-weight: bold;"><?php echo $warning?></span>
</center>
<br>
<?php }?>
<div class="table-container">
	<table align="center" class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="6">User Story - <span
				style="font-weight: bold; color: grey;">"<?php echo string_display_line_links($usSumText[0]['summary'])?>"</span>
			</td>
		</tr>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">ID</td>
			<td><a
				href="<?php echo config_get_global( 'path' ) ?>view.php?id=<?php 
						echo $usSumText[0]['id']?>"><?php echo $usSumText[0]['id']?></a></td>
			<td class="category">Status</td>
			<td style="background-color:<?php 
						echo $g_status_colors[$userstory_status[$usSumText[0]['status']]]?>">
				<?php echo $status[$usSumText[0]['status']];?>
			</td>
			<td class="category">
				<?php echo plugin_lang_get( 'edit_tasks_category' )?>
			</td>
			<td>
				<?php echo $agilemantis_tasks->getCategoryById($usSumText[0]['category_id'])?>
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
		<form
			action="<?php echo plugin_page("task_page.php&us_id=".$agilemantis_tasks->us_id)?>"
			method="post">
			<input type="hidden" name="action" value="save">
		<?php $col_rd = 0;$col_pc = 0;$col_tech = 0;$col_pr = 0;$col_ro = 0;?>
		<?php if( plugin_config_get('gadiv_release_documentation') == '1' ) {
				$show_rd = true;
				$col_rd -= 2;
				$minus -= 2;
				}?>
		<?php if( plugin_config_get('gadiv_tracker_planned_costs') == '1' ) { 
				$show_pc = true;
				$col_pc -= 2;
				}?>
		<?php if( plugin_config_get('gadiv_technical') == '1' ) { 
				$show_tech = true;
				$col_tech -= 2;
				}?>
		<?php if( plugin_config_get('gadiv_presentable') == '1' ) { 
				$show_pr = true;
				$col_pr -= 2;
				}?>
		<?php if( plugin_config_get('gadiv_ranking_order') == '1' ) { 
				$show_ro = true;
				$col_ro -= 3;
				}?>
		<?php if( $show_rd == true || $show_pr == true || $show_tech == true ) { 
				$minus -= 2;
				}?>
		<?php
			if( $getSprint['status'] > 0 || $usSumText[0]['status'] >= 80 ) {
				$disable_storypoints = 'disabled';
				echo '<input type="hidden" name="storypoints" value="'.$usData['storypoints'].'">';
			} else {
				$disable_storypoints = '';
			}
		?>
		<tr <?php echo helper_alternate_class() ?>>
				<td class="category">Storypoints</td>
				<td>
				<?php if( plugin_config_get('gadiv_storypoint_mode') == 1){?>
					<input type="text" name="storypoints" style="width: 30px;"
					maxlength="3" value="<?php echo $usData['storypoints']?>"
					<?php echo $disable_storypoints?>>
				<?php } else {?>
					<select name="storypoints" style="width: 40px;"
					<?php echo $disable_storypoints?>>
						<option value=""></option>
						<?php $agilemantis_pb->getFibonacciNumbers($usData['storypoints']);?>
					</select>
				<?php }?>
			</td>
			<?php if( $show_pc == true ) {?>
				<td class="category"><?php echo plugin_lang_get( 'PlannedWork' ) ?></td>
				<td><input type="text" name="plannedWork" style="width: 40px;"
					value="<?php if( !empty( $usData['plannedWork'] ) ) { 
						echo $usData['plannedWork']; }?>"> <?php 
						echo plugin_config_get('gadiv_userstory_unit_mode')?></td>
			<?php }?>
			<td class="category">Business Value</td>
				<td colspan="<?php echo (4+ $col_ro + $col_pc)?>"><?php echo $usData['businessValue']?></td>
			<?php if( $show_ro == true ) {?>
				<td class="category"><?php echo plugin_lang_get( 'RankingOrder' ) ?></td>
				<td colspan="2"><?php echo $usData['rankingorder']?></td>
			<?php }?>
			<td colspan="3"><input type="submit" name="submit"
					value="<?php echo plugin_lang_get( 'edit_tasks_save_changes' )?>">
				</td>
			</tr>
		</form>
		<?php
		
		$sprint = $agilemantis_tasks->getSprintByBugId( $usSumText[0]['id'] );
		$sprint = $sprint[0];
		$productBacklog = $agilemantis_tasks->getProductBacklogByBugId( $usSumText[0]['id'] );
		
		if( $usData['presentable'] == 0 ) {
			$presentable = plugin_lang_get( 'view_issue_non_presentable' );
		}
		if( $usData['presentable'] == 1 ) {
			$presentable = plugin_lang_get( 'view_issue_technical_presentable' );
		}
		if( $usData['presentable'] == 2 ) {
			$presentable = plugin_lang_get( 'view_issue_functional_presentable' );
		}
		
		if( $usData['inReleaseDocu'] == 'Ja' ) {
			$inReleaseDocu = 'checked';
		} else {
			$inReleaseDocu = '';
		}
		
		if( $usData['technical'] == 'Ja' ) {
			$technical = 'checked';
		} else {
			$technical = '';
		}
		?>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">Produkt Backlog</td>
			<td><?php echo string_display_line_links( $productBacklog['name'] )?></td>
			<?php if( $show_pr == true && $show_tech == true && $show_rd == true ) {?>
				<td class="category">Sprint</td>
			<td><?php echo string_display_line_links( $sprint['name'] )?></td>
			<td class="category"><?php echo plugin_lang_get( 'Presentable' )?></td>
			<td><?php echo $presentable?></td>
			<td class="category"><?php echo plugin_lang_get( 'Technical' )?></td>
			<td><input type="checkbox" name="technical" <?php echo $technical?>
				value="1" disabled></td>
			<td class="category"><?php echo plugin_lang_get( 'InReleaseDocu' )?></td>
			<td><input type="checkbox" name="inReleaseDocu"
				<?php echo $inReleaseDocu?> value="1" disabled></td>
			<?php }?>
			<?php if( $show_pr == true && $show_tech == false && $show_rd == false ) {?>
				<td class="category">Sprint</td>
			<td><?php echo string_display_line_links( $sprint['name'] )?></td>
			<td class="category"><?php echo plugin_lang_get( 'Presentable' )?></td>
			<td colspan="5"><?php echo $presentable?></td>
			<?php }?>
			<?php if( $show_pr == false && $show_tech == true && $show_rd == false ) {?>
				<td class="category">Sprint</td>
			<td><?php echo string_display_line_links( $sprint['name'] )?></td>
			<td class="category"><?php echo plugin_lang_get( 'Technical' )?></td>
			<td colspan="5"><input type="checkbox" name="technical"
				<?php echo $technical?> value="1" disabled></td>
			<?php }?>
			<?php if( $show_pr == false && $show_tech == false && $show_rd == true ) {?>
				<td class="category">Sprint</td>
			<td><?php echo string_display_line_links( $sprint['name'] )?></td>
			<td class="category"><?php echo plugin_lang_get( 'InReleaseDocu' )?></td>
			<td colspan="5"><input type="checkbox" name="inReleaseDocu"
				<?php echo $inReleaseDocu?> value="1" disabled></td>
			<?php }?>
			<?php if( $show_pr == true && $show_tech == true && $show_rd == false ) {?>
				<td class="category">Sprint</td>
			<td><?php echo string_display_line_links( $sprint['name'] )?></td>
			<td class="category"><?php echo plugin_lang_get( 'Presentable' )?></td>
			<td><?php echo $presentable?></td>
			<td class="category"><?php echo plugin_lang_get( 'Technical' )?></td>
			<td colspan="3"><input type="checkbox" name="technical"
				<?php echo $technical?> value="1" disabled></td>
			<?php }?>
			<?php if( $show_pr == true && $show_tech == false && $show_rd == true ) {?>
				<td class="category">Sprint</td>
			<td><?php echo string_display_line_links( $sprint['name'] )?></td>
			<td class="category"><?php echo plugin_lang_get( 'Presentable' )?></td>
			<td><?php echo $presentable?></td>
			<td class="category"><?php echo plugin_lang_get( 'InReleaseDocu' )?></td>
			<td colspan="3"><input type="checkbox" name="inReleaseDocu"
				<?php echo $inReleaseDocu?> value="1" disabled></td>
			<?php }?>
			<?php if( $show_pr == false && $show_tech == true && $show_rd == true ) {?>
				<td class="category">Sprint</td>
			<td><?php echo string_display_line_links( $sprint['name'] )?></td>
			<td class="category"><?php echo plugin_lang_get( 'Technical' )?></td>
			<td><input type="checkbox" name="technical" <?php echo $technical?>
				value="1" disabled></td>
			<td class="category"><?php echo plugin_lang_get( 'InReleaseDocu' )?></td>
			<td colspan="3"><input type="checkbox" name="inReleaseDocu"
				<?php echo $inReleaseDocu?> value="1" disabled></td>
			<?php }?>
			<?php if( $show_pr == false && $show_tech == false && $show_rd == false ) {?>
				<td class="category">Sprint</td>
			<td colspan="7"><?php echo string_display_line_links( $sprint['name'] )?></td>
			<?php } ?>
		</tr>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'summary' )?>
			</td>
			<td colspan="9">
			  <?php echo string_display_line_links( $usSumText[0]['summary'] )?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo plugin_lang_get( 'common_description' )?>
			</td>
			<td colspan="9">
			  <?php echo nl2br( string_display_links( $usSumText[0]['description'] ) )?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'steps_to_reproduce' )?>
			</td>
			<td colspan="9">
			  <?php echo nl2br( string_display_links( $usSumText[0]['steps_to_reproduce'] ) )?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'additional_information' )?>
			</td>
			<td colspan="9">
			  <?php echo nl2br( string_display_links( $usSumText[0]['additional_information'] ) )?>
			</td>
		</tr>
	</table>
</div>
<br>
<div class="table-container">
	<table align="center" class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="6">Tasks - <span
				style="font-weight: bold; color: grey;">"<?php echo string_display_line_links( $usSumText[0]['summary'] )?>"</span>
			</td>
		</tr>
	<?php
		# change task unit if sprint is not running
		if( $getSprint['status'] > 0 ) {
			if( $getSprint['unit_planned_task'] > 0 ) {
				$unit = '(' . $agilemantis_userstory->getUnitById( $getSprint['unit_planned_task'] ) .
					 ')';
				$currentUnit = $agilemantis_userstory->getUnitById( $getSprint['unit_planned_task'] );
			}
		} else {
			if( plugin_config_get( 'gadiv_task_unit_mode' ) != "" ) {
				$unit = '(' . plugin_config_get( 'gadiv_task_unit_mode' ) . ')';
				$currentUnit = plugin_config_get( 'gadiv_task_unit_mode' );
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
		$userStoryTasks = $agilemantis_userstory->getUserStoryTasks( $agilemantis_tasks->us_id );
		if( !empty( $userStoryTasks ) ) {
			foreach( $userStoryTasks AS $num => $row ) {
				# change background color and set status text for each task
				$status = $agilemantis_agm->agileMantisStatusColorsAndNames( $row['status'] );
				
				if( $row['status'] < 4 ) {
					$agilemantis_tasks->setConfirmationStatus( $agilemantis_tasks->us_id );
				}
		?>
			<tr style="background-color:<?php echo $status['color']?>" >
			<td><?php echo string_display_line_links( $row['name'] )?></td>
			<td><?php echo string_display( $status['name'] )?></td>
			<td><?php echo $agilemantis_tasks->getUserName( $row['developer_id'] )?></td>
			<td><?php echo $row['planned_capacity']?></td>
			<td><?php echo $row['rest_capacity']?></td>
			<td>
				<form action="<?php echo  plugin_page( 'edit_task.php' ) ?>"
					method="post">
					<input type="hidden" name="id" value="<?php echo $row['id'] ?>"> <input
						type="hidden" name="us_id"
						value="<?php echo $agilemantis_tasks->us_id ?>"> <input
						type="hidden" name="sprintName"
						value="<?php echo $getSprint['name'] ?>"> <input type="hidden"
						name="fromSprintBacklog"
						value="<?php echo $request['fromSprintBacklog']?>"> <input
						type="hidden" name="fromTaskPage" value="1"> <input type="submit"
						name="submit"
						value="<?php echo plugin_lang_get( 'edit_tasks_edit' )?>">
				</form>
					<?php if( $row['id'] > 0 && $row['status'] < 4 && $row['status'] != 1 
							&& $row['performed_capacity'] > 0 && $getSprint['status'] == 1 ) {?>
					<form
					action="<?php echo plugin_page('task_page.php')."&us_id=".$agilemantis_tasks->us_id?>"
					method="post">
					<input type="hidden" name="task_id" value="<?php echo $row['id']?>">
					<input type="hidden" name="us_id"
						value="<?php echo $agilemantis_tasks->us_id?>"> <input
						type="hidden" name="developer_id"
						value="<?php echo $row['developer_id']?>"> <input type="hidden"
						name="task_name" value="<?php echo $row['name']?>"> <input
						type="hidden" name="task_description"
						value="<?php echo string_display($row['description'])?>"> <input type="hidden"
						name="task_status" value="<?php echo $row['status']?>"> <input
						type="hidden" name="rest_capacity"
						value="<?php echo $row['rest_capacity']?>"> <input type="hidden"
						name="planned_capacity"
						value="<?php echo $row['planned_capacity']?>"> <input
						type="hidden" name="performed_capacity"
						value="<?php echo $row['performed_capacity']?>"> <input
						type="hidden" name="fromSprintBacklog"
						value="<?php echo $request['fromSprintBacklog']?>"> <input
						type="submit" name="divide_task"
						value="<?php echo plugin_lang_get( 'button_assume' )?>">
				</form>
					<?php }?>
				</td>
		</tr>
		<?php }
	}?>
</table>
</div>
<br>
<form action="" method="post">
	<input type="hidden" name="action" value="addTask"> <input
		type="hidden" name="id" value="0"> <input type="hidden" name="us_id"
		value="<?php echo $agilemantis_tasks->us_id?>"> <input type="hidden"
		name="status" value="1"> <input type="hidden" name="fromSprintBacklog"
		value="<?php echo $_POST['fromSprintBacklog']?>"> <input type="hidden"
		name="sprintName" value="<?php echo $_POST['sprintName']?>"> <input
		type="hidden" name="uniqformid"
		value="<?php echo md5( uniqid( microtime(), 1 ) ) ?>" /> <input
		type="hidden" name="currentUnit" value="<?php echo $currentUnit?>">
	<div class="table-container">
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
			<tr style="background-color: #fcbdbd">
				<td style="width: 20%;"><input type="text" name="name"
					style="width: 98%;"
					value="<?php if( $_POST['name'] && $system != "" ){ echo $_POST['name'];}?>"></td>
				<td style="width: 20%;"><textarea name="description"
						style="width: 98%; height: 50px;"><?php 
					if( $_POST['description'] && $system != "" ) { 
						echo $_POST['description'];
					}?></textarea></td>
				<td style="width: 20%;"><select name="developer" style="width: 98%;">
						<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
					<?php
						# get all developers by team id
						$agilemantis_team->id = $getSprint['team_id'];
						if( $agilemantis_team->id ) {
							$user = $agilemantis_team->getTeamDeveloper();
							foreach( $user AS $num => $row ) {
								if( $row['id'] != 0 ) {?>
									<option value="<?php echo $row['id']?>"
							<?php if( $row['id'] == $_POST['developer'] && $system != "" ) { 
									echo 'selected';
									}?>><?php 
									echo $row['username']?></option>
							<?php }
							}
						} ?>
				</select></td>
				<td style="width: 20%;"><input type="text" name="planned_capacity"
					value="<?php 
						if( $system != "" ) { 
							echo $_POST['planned_capacity'];
						}?>"></td>
				<td style="width: 20%;"><input type="submit" name="submit"
					value="<?php echo plugin_lang_get( 'edit_tasks_add' )?>"></td>
			</tr>
		</table>
	</div>
</form>
<br>
<center>
	<form action="<?php echo plugin_page("edit_task.php")?>" method="post">
		<input type="hidden" name="us_id"
			value="<?php echo $agilemantis_tasks->us_id?>">
	</form>
	<?php
		# redirect back to sprint backlog or view issue page
	if( $request['fromSprintBacklog'] == 1 ) {
		$redirect = plugin_page( 'sprint_backlog.php' ) . "&sprintName=" .
			 urlencode( $getSprint['name'] );
	} else {
		$redirect = 'view.php?id=' . $agilemantis_tasks->us_id;
	}
	?>
	<form action="<?php echo $redirect?>" method="post">
		<input type="submit" name="back_button"
			value="<?php echo plugin_lang_get( 'button_back' )?>">
	</form>
</center>
<?php
	# add bug note form
	$f_bug_id = $agilemantis_tasks->us_id;
	require( 'bugnote_add_inc.php' );
?>
<?php html_status_legend();?>
<?php html_page_bottom() ?>
<?php }?>