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

	
	$userstory = $sprint->getUserStoryById();
	$version_info = $version->getVersionInformation($userstory[0]['project_id'],$userstory[0]['target_version']);
	$versiondate = "";
	if($version_info['date_order'] != ""){
		$versiondate = 	date('d.m.Y',$version_info['date_order']);
	}
	echo '<userstory>';
	echo '<us_id>'.$userstory[0]['id'].'</us_id>';
	echo '<us_summary>'.htmlspecialchars($userstory[0]['summary']).'</us_summary>';
	echo '<us_status>'.$userstory[0]['status'].'</us_status>';
	echo '<us_description>'.htmlspecialchars($userstory[0]['description']).'</us_description>';
	echo '<us_category>'.htmlspecialchars($tasks->getCategoryById($userstory[0]['category_id'])).'</us_category>';
	echo '<us_project_version>'.htmlspecialchars($project->getProjectById($userstory[0]['project_id'])) . ' ' . $userstory[0]['target_version'].'</us_project_version>';
	echo '<us_severity>'.htmlspecialchars(utf8_decode($severity[$userstory[0]['severity']])).'</us_severity>';
	echo '<us_status_str>'.htmlspecialchars(utf8_decode($status[$userstory[0]['status']])).'</us_status_str>';
	echo '<version>';
		echo '<us_version_project>'.htmlspecialchars($project->getProjectById($userstory[0]['project_id'])).'</us_version_project>';
		echo '<us_version_target>'.$userstory[0]['target_version'].'</us_version_target>';
		echo '<us_version_date>'.$versiondate.'</us_version_date>';
		echo '<us_version_trackerGesamt>'.$version->getVersionTracker($userstory[0]['project_id'],$userstory[0]['target_version'], '10,20,30,40,50,60,70,80,90').'</us_version_trackerGesamt>';
		echo '<us_version_trackerOpen>'.$version->getVersionTracker($userstory[0]['project_id'],$userstory[0]['target_version'], '10,20,30,40,50,60,70').'</us_version_trackerOpen>';
		echo '<us_version_userStoriesGesamt>'.$version->getVersionUserStories($userstory[0]['project_id'],$userstory[0]['target_version']).'</us_version_userStoriesGesamt>';
		echo '<us_version_userStoriesOpen>'.$version->getNumberOfUserStories($userstory[0]['project_id'],$userstory[0]['target_version']).'</us_version_userStoriesOpen>';
		echo '<us_version_beschreibung>'.$version_info['description'].'</us_version_beschreibung>';
	echo '</version>';
	$id	= $sprint->us_id;
	$addFields = $sprint->checkForUserStory($id);
	if(!empty($addFields)){
		echo '<additionalFields>';
			echo '<field>';
				echo '<field_name>Product Backlog</field_name>';
				echo '<field_value>'.$addFields['name'].'</field_value>';
				echo '<field_datatype>String</field_datatype>';
			echo '</field>';
			echo '<field>';
				echo '<field_name>Story Points</field_name>';
				echo '<field_value>'.$addFields['storypoints'].'</field_value>';
				echo '<field_datatype>Double</field_datatype>';
			echo '</field>';
			echo '<field>';
				echo '<field_name>Business Value</field_name>';
				echo '<field_value>'.$addFields['businessValue'].'</field_value>';
				echo '<field_datatype>String</field_datatype>';
			echo '</field>';
			echo '<field>';
				echo '<field_name>Sprint</field_name>';
				echo '<field_value>'.$addFields['sprint'].'</field_value>';
				echo '<field_datatype>String</field_datatype>';
			echo '</field>';
			if($tasks->getConfigValue('plugin_AgileMantis_gadiv_tracker_planned_costs')=='1'){
				echo '<field>';
					echo '<field_name>'.$s_plugin_agileMantis_PlannedWork.' ('.$_POST['userstorycostunit'].')</field_name>';
					echo '<field_value>'.$addFields['plannedWork'].'</field_value>';
					echo '<field_datatype>Double</field_datatype>';
				echo '</field>';
			}
			if($tasks->getConfigValue('plugin_AgileMantis_gadiv_ranking_order')=='1'){
				echo '<field>';
					echo '<field_name>'.$s_plugin_agileMantis_RankingOrder.'</field_name>';
					echo '<field_value>'.$addFields['rankingorder'].'</field_value>';
					echo '<field_datatype>String</field_datatype>';
				echo '</field>';
			}
			if($tasks->getConfigValue('plugin_AgileMantis_gadiv_presentable')=='1'){
				if($addFields['presentable'] == 0){$string = $s_plugin_agileMantis_view_issue_non_presentable;}
				if($addFields['presentable'] == 1){$string = $s_plugin_agileMantis_view_issue_technical_presentable;}
				if($addFields['presentable'] == 2){$string = $s_plugin_agileMantis_view_issue_functional_presentable;}
				echo '<field>';
					echo '<field_name>'.$s_plugin_agileMantis_Presentable.'</field_name>';
					echo '<field_value>'.utf8_decode($string).'</field_value>';
					echo '<field_datatype>String</field_datatype>';
				echo '</field>';
			}
			if($tasks->getConfigValue('plugin_AgileMantis_gadiv_technical')=='1'){
				if($addFields['technical']==0){$string = 'false';};
				if($addFields['technical']==1){$string = 'true';};
				echo '<field>';
					echo '<field_name>'.$s_plugin_agileMantis_Technical.'</field_name>';
					echo '<field_value>'.$string.'</field_value>';
					echo '<field_datatype>Boolean</field_datatype>';
				echo '</field>';
			}
			if($tasks->getConfigValue('plugin_AgileMantis_gadiv_release_documentation')=='1'){
				if($addFields['inReleaseDocu']==0){$string = 'false';};
				if($addFields['inReleaseDocu']==1){$string = 'true';};
				echo '<field>';
					echo '<field_name>'.$s_plugin_agileMantis_InReleaseDocu.'</field_name>';
					echo '<field_value>'.$string.'</field_value>';
					echo '<field_datatype>Boolean</field_datatype>';
				echo '</field>';
			}
			echo '</additionalFields>';
	}
	$temp_tasks = $sprint->getSprintTasks($id);
	if(!empty($temp_tasks)){
		echo '<tasks>';
		foreach($temp_tasks AS $num => $row){
			$created = $tasks->getTaskEvent($row['id'],'created');
			$confirmed = $tasks->getTaskEvent($row['id'],'confirmed');
			$resolved = $tasks->getTaskEvent($row['id'],'resolved');
			$reopened = $tasks->getTaskEvent($row['id'],'reopened');
			$closed = $tasks->getTaskEvent($row['id'],'closed');
			echo '<task>';
				echo '<task_id>'.$row['id'].'</task_id>';
				echo '<task_name>'.htmlspecialchars($row['name']).'</task_name>';
				echo '<task_description>'.htmlspecialchars($row['description']).'</task_description>';
				echo '<task_daily_scrum>'.(isset($row['daily_scrum']) ? '1' : '0').'</task_daily_scrum>';
				if($row['developer_id'] > 0){
				echo '<developer>';
					echo '<dev_id>'.$row['developer_id'].'</dev_id>';
					echo '<dev_username>'.$tasks->getUserById($row['developer_id']).'</dev_username>';
					echo '<dev_realname>'.$tasks->getUserRealName($row['developer_id']).'</dev_realname>';
				echo '</developer>';
				}
				echo '<task_status>'.$row['status'].'</task_status>';
				echo '<task_planned_capacity>'.$row['planned_capacity'].'</task_planned_capacity>';
				echo '<task_performed_capacity>'.$row['performed_capacity'].'</task_performed_capacity>';
				echo '<task_rest_capacity>'.$row['rest_capacity'].'</task_rest_capacity>';
				$create_date = strtotime($created['date']);
				$confirm_date = strtotime($confirmed['date']);
				$resolve_date = strtotime($resolved['date']);
				$close_date = strtotime($closed['date']);
				$reopen_date = strtotime($reopened['date']);
				if($created['user_id'] > 0){
				echo '<task_created>'.$tasks->getUserById($created['user_id']).' / '.date('d.m.Y',$create_date).'</task_created>';
				}
				if($confirmed['user_id'] > 0){
				echo '<task_confirmed>'.$tasks->getUserById($confirmed['user_id']).' / '.date('d.m.Y',$confirm_date).'</task_confirmed>';
				}
				if($resolved['user_id'] > 0){
				echo '<task_resolved>'.$tasks->getUserById($resolved['user_id']).' / '.date('d.m.Y',$resolve_date).'</task_resolved>';
				}
				if($closed['user_id'] > 0){
					echo '<task_closed>'.$tasks->getUserById($closed['user_id']).' / '.date('d.m.Y',$close_date).'</task_closed>';
				}
				if($reopened['user_id'] > 0){
					echo '<task_reopened>'.$tasks->getUserById($reopened['user_id']).' / '.date('d.m.Y',$reopen_date).'</task_reopened>';
				}
			echo '</task>';
		}
		echo '</tasks>';
	}
	$notices = $tasks->getNotices($id);
	if(!empty($notices)){
		echo '<notices>';
		foreach($notices AS $num => $row){
			echo '<notice>';
				echo '<note_id>'.$row['id'].'</note_id>';
				echo '<note_description>'.htmlspecialchars($row['note']).'</note_description>';
				echo '<note_reporter>'.htmlspecialchars($tasks->getUserById($row['reporter_id'])).'</note_reporter>';
				echo '<note_date>'.date('d.m.Y',$row['date_submitted']).'</note_date>';
			echo '</notice>';
		}
		echo '</notices>';
	}
	echo '</userstory>';
?>