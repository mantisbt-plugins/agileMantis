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


html_page_top( plugin_lang_get( 'divide_userstories_title' ) );

# merge global $_GET / $_POST Array
$request = array_merge( $_GET, $_POST );

# copy user story function
function copyUserStory( $us_id, $status, $sprintname ) {
	global $agilemantis_pb;
	global $agilemantis_tasks;
	global $agilemantis_sprint;
	
	$new_bug_id = bug_copy( $us_id, null, true, true, false, true, true, true );
	$agilemantis_pb->doUserStoryToSprint( $new_bug_id, $sprintname );
	relationship_add( $new_bug_id, $us_id, 0 );
	$task = $agilemantis_sprint->getSprintTasks( $us_id );
	$agilemantis_sprint->sprint_id = $sprintname;
	$sprintinfo = $agilemantis_sprint->getSprintById();
	$old_userstory = $agilemantis_pb->checkForUserStory( $us_id );
	
	$agilemantis_pb->addStoryPoints( $new_bug_id, $old_userstory['storypoints'] );
	$agilemantis_pb->addBusinessValue( $new_bug_id, $old_userstory['businessValue'] );
	$agilemantis_pb->addRankingOrder( $new_bug_id, $old_userstory['rankingorder'] );
	$agilemantis_pb->addTechnical( $new_bug_id, $old_userstory['technical'] );
	$agilemantis_pb->addPresentable( $new_bug_id, $old_userstory['presentable'] );
	$agilemantis_pb->AddInReleaseDocu( $new_bug_id, $old_userstory['inReleaseDocu'] );
	$agilemantis_pb->AddPlannedWork( $new_bug_id, $old_userstory['plannedWork'] );
	history_delete($new_bug_id);
	
	$bugnote_text_new = plugin_lang_get( 'divide_userstories_from' ) .
		 $agilemantis_pb->getUserName( auth_get_current_user_id() ) .
		 plugin_lang_get( 'divide_userstories_of' ) . ' #' . $us_id .
		 plugin_lang_get( 'divide_userstories_splitted' );
	$bugnote_text_old = plugin_lang_get( 'divide_userstories_from' ) .
		 $agilemantis_pb->getUserName( auth_get_current_user_id() ) .
		 plugin_lang_get( 'divide_userstories_from' ) . ', #' . $new_bug_id .
		 plugin_lang_get( 'divide_userstories_splitted' );
	
	$agilemantis_sprint->sprint_id = $old_userstory['sprint'];
	$sprintinfo = $agilemantis_sprint->getSprintById();
	
	$userstory_performed = false;
	$wmu = 0;
	$spmu = 0;
	if( !empty( $task ) ) {
		foreach( $task as $key => $value ) {
			if( $value['performed_capacity'] > 0 || $value['status'] >= 4 ) {
				$userstory_performed = true;
			}
			if( $value['status'] < 4 ) {
				
				$agilemantis_tasks->user_id = auth_get_current_user_id();
				$agilemantis_tasks->name = $value['name'];
				$agilemantis_tasks->us_id = $value['us_id'];
				$agilemantis_tasks->description = $value['description'];
				$agilemantis_tasks->developer = $value['developer_id'];
				$agilemantis_tasks->status = 5;
				$agilemantis_tasks->capacity = $value['performed_capacity'];
				$agilemantis_tasks->planned_capacity = $value['planned_capacity'];
				$agilemantis_tasks->rest_capacity = 0;
				$agilemantis_tasks->id = $value['id'];
				$agilemantis_tasks->unit = $value['unit'];
				$agilemantis_tasks->editTask();
				$agilemantis_tasks->saveDailyPerformance( 0 );
				
				$agilemantis_tasks->id = 0;
				$agilemantis_tasks->name = $value['name'];
				$agilemantis_tasks->us_id = $new_bug_id;
				$agilemantis_tasks->description = $value['description'];
				$agilemantis_tasks->status = $value['status'];
				
				if( $value['status'] == 3 ) {
					$agilemantis_tasks->status = 2;
				}
				
				$agilemantis_tasks->developer = $value['developer_id'];
				
				if( $agilemantis_sprint->getUnitId( plugin_config_get( 'gadiv_task_unit_mode' ) ) !=
					 $sprintinfo['unit_planned_task'] ) {
					$agilemantis_tasks->planned_capacity = 0;
					$agilemantis_tasks->rest_capacity = 0;
				} else {
					$agilemantis_tasks->planned_capacity = $value['rest_capacity'];
					$agilemantis_tasks->rest_capacity = $value['rest_capacity'];
				}
				
				$agilemantis_tasks->addFinishedNote( $value['us_id'], $value['id'], 
					auth_get_current_user_id() );
				$agilemantis_tasks->editTask();
				$agilemantis_tasks->id = 0;
				$agilemantis_tasks->updateTaskLog( $value['id'], auth_get_current_user_id(), 
					"closed" );
				$agilemantis_tasks->setTaskStatus( $value['id'], 5 );
				
				$wmu += $value['rest_capacity'];
				$new_storypoints += $value['performed_capacity'];
			}
		}
	}
	
	if( $sprintinfo['unit_planned_task'] == 3 ) {
		$spmu = $wmu;
	} else {
		$spmu = 0;
	}
	
	# collect all user story splitting information and write these into database
	$agilemantis_sprint->setSplittingInformation( $us_id, $new_bug_id, $wmu, $spmu );
	
	if( $userstory_performed === true ) {
		if( $sprintinfo['unit_planned_task'] < 3 ) {
			$agilemantis_pb->addStoryPoints( $new_bug_id, '' );
		} elseif( $sprintinfo['unit_planned_task'] == 3 ) {
			$agilemantis_pb->addStoryPoints( $new_bug_id, 
				$old_userstory['storypoints'] - $new_storypoints );
		}
		$bugnote_text_new .= plugin_lang_get( 'divide_userstories_old_estimation' ) . " #" . $us_id .
			 plugin_lang_get( 'divide_userstories_with' ) . $old_userstory['storypoints'] . " SP.";
			 bugnote_add( $new_bug_id, $bugnote_text_new, $p_time_tracking = '0:00', $p_private = false, $p_type = BUGNOTE, $p_attr = '', $p_user_id = null, $p_send_email = FALSE, $p_log_history = TRUE);
	}
	
	# add bug note
	bugnote_add( $us_id, $bugnote_text_old, $p_time_tracking = '0:00', $p_private = false, $p_type = BUGNOTE, $p_attr = '', $p_user_id = null, $p_send_email = FALSE, $p_log_history = TRUE);
	
	$agilemantis_tasks->setUserStoryStatus( $us_id, $status, auth_get_current_user_id() );
	$agilemantis_tasks->closeUserStory( $us_id, $status, auth_get_current_user_id() );
	bug_update_date( $us_id );
}

# divide user story action
if( $request['action'] == 'edit' ) {
	if( $request['divide_userstory'] ) {
		if( $request['status'] < 80 ) {
			copyUserStory( $request['us_id'], $request['userstory_status'], 
				$request['userstory_sprint'] );
		}
	} elseif( $request['divide_userstories'] ) {
		$userstories = $agilemantis_sprint->getSprintStories( $request['sprintName'] );
		foreach( $userstories as $num => $row ) {
			if( $row['status'] < 80 ) {
				copyUserStory( $row['id'], $request['userstory_status'], 
					$request['userstory_sprint'] );
			}
		}
	}
	
	if( $request['fromPage'] == 'sprint_backlog' ) {
		$header = "Location: " . plugin_page( 'sprint_backlog.php' ) . "&sprintName=" .
			 urlencode( $request['sprintName'] );
	}
	
	# return to taskboard or sprint backlog
	header( $header );
} else {
	
	$agilemantis_sprint->sprint_id = $request['sprintName'];
	$sprintinfo = $agilemantis_sprint->getSprintById();
	
	if( $agilemantis_sprint->getUnitId( plugin_config_get( 'gadiv_task_unit_mode' ) ) !=
		 $sprintinfo['unit_planned_task'] ) {
		echo '<br><center><span class="message_error">' .
		 plugin_lang_get( 'divide_userstories_error_106D00' ) . '</span></center>';
	}
?>
<br>
<form action="<?php echo plugin_page("divide_userstories.php")?>"
	method="post">
	<input type="hidden" name="action" value="edit"> <input type="hidden"
		name="us_id" value="<?php echo $request['us_id']?>"> <input
		type="hidden" name="sprintName"
		value="<?php echo $request['sprintName']?>"> <input type="hidden"
		name="status" value="<?php echo $request['status']?>"> <input
		type="hidden" name="fromPage"
		value="<?php echo $request['fromPage']?>"> <input type="hidden"
		name="fromDailyScrum" value="<?php echo $_POST['fromDailyScrum']?>">
	<div class="table-container">
		<table align="center" class="width75" cellspacing="1">
			<tr>
				<td colspan="2"><b><?php echo plugin_lang_get( 'divide_userstories_subtitle' ) ?></b></td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category"><?php echo plugin_lang_get( 'divide_userstories_chose_sprint' ) ?></td>
				<td>
				<?php
					$agilemantis_sprint->sprint_id = $request['sprintName'];
					$current_sprint = $agilemantis_sprint->getSprintById();
					$sprint_id = $current_sprint['id'];
					$team_id = $current_sprint['team_id'];
					$sprints = $agilemantis_sprint->getUndoneSprintsByTeam( $team_id, $sprint_id );
				?>
				<select name="userstory_sprint" style="width: 255px;">
						<option value=""></option>
					<?php if( !empty( $sprints ) ) { ?>
					<?php foreach( $sprints AS $num => $row ) { ?>
					<option value="<?php echo $row['name']?>"><?php echo string_display($row['name'])?></option>
					<?php }?>
					<?php }?>
				</select>
				</td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category"><?php echo plugin_lang_get( 'divide_userstories_chose_status' ) ?></td>
				<td><select name="userstory_status" style="width: 255px;">
						<option value="80"><?php echo plugin_lang_get( 'status_resolved' ) ?></option>
						<option value="90"><?php echo plugin_lang_get( 'status_closed' ) ?></option>
				</select></td>
			</tr>
			<tr>
				<td colspan="2" class="center">
				<?php if( $request['us_id'] ) { ?>
					<input type="submit" name="divide_userstory"
					value="<?php echo plugin_lang_get( 'divide_userstories_single_title' )?>">
				<?php } else {?>
					<input type="submit" name="divide_userstories"
					value="<?php echo plugin_lang_get( 'divide_userstories_title' )?>">
				<?php }?>
				</form>
					<form
						action="<?php echo plugin_page( $request['fromPage'] )?>.php&sprintName=<?php 
										echo urlencode( $request['sprintName'] );?>"
						method="post">
						<input type="submit" name="back_button"
							value="<?php echo plugin_lang_get( 'button_back' )?>">
					</form>
				</td>
			</tr>
		</table>
	</div>
	<?php }?>
<?php html_page_bottom() ?>