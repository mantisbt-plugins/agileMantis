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

	$userstory = $agilemantis_sprint->getUserStoryById();
	$project_id = $userstory[0]['project_id'];
	$target_version = $userstory[0]['target_version'];
	$version_info = $agilemantis_version->getVersionInformation( $project_id, $target_version );
	$versiondate = "";
	if( isset( $version_info['date_order'] ) && $version_info['date_order'] != "" ){
		$versiondate = 	date( 'd.m.Y',$version_info['date_order'] );
	}
	$versiondescription = "";
	if( isset( $version_info['description'] ) && $version_info['description'] != "" ){
		$versiondescription = 	$version_info['description'];
	}
	echo '<userstory>';
	echo '<us_id>' . $userstory[0]['id'] . '</us_id>';
	echo '<us_summary>' . $agilemantis_commonlib->safeCData( string_display_line_links( $userstory[0]['summary'] ) ) . '</us_summary>';
	echo '<us_status>' . $userstory[0]['status'] . '</us_status>';
	echo '<us_description>' . $agilemantis_commonlib->safeCData( string_display_links( $userstory[0]['description'] ) ) . 
			'</us_description>';
	
	echo '<us_category><![CDATA[' . $agilemantis_tasks->getCategoryById( $userstory[0]['category_id'] ) . ']]></us_category>';
	echo '<us_project_version><![CDATA[' . $agilemantis_project->getProjectName( $userstory[0]['project_id'] ) . 
			' ' . $userstory[0]['target_version'].']]></us_project_version>';
	echo '<us_severity><![CDATA[' . utf8_decode( $severity[$userstory[0]['severity']] ) . ']]></us_severity>';
	echo '<us_status_str><![CDATA[' . utf8_decode( $status[$userstory[0]['status']] ) . ']]></us_status_str>';
	
	echo '<version>';
		echo '<us_version_project>' . 
			$agilemantis_commonlib->safeCData( $agilemantis_project->getProjectName( $userstory[0]['project_id'] ) ) . 
				'</us_version_project>';
		echo '<us_version_project_description>' . 
			$agilemantis_commonlib->safeCData( $agilemantis_project->getProjectDescription( $userstory[0]['project_id'] ) ) .
				'</us_version_project_description>';
		echo '<us_version_target><![CDATA[' . $userstory[0]['target_version'] . ']]></us_version_target>';
		echo '<us_version_date>' . $versiondate . '</us_version_date>';
		echo '<us_version_trackerGesamt>' . 
			$agilemantis_version->getVersionTracker( $userstory[0]['project_id'], $userstory[0]['target_version'], '10,20,30,40,50,60,70,80,90' ) .
				'</us_version_trackerGesamt>';
		echo '<us_version_trackerOpen>' .
			$agilemantis_version->getVersionTracker( $userstory[0]['project_id'], $userstory[0]['target_version'], '10,20,30,40,50,60,70' ) . 
				'</us_version_trackerOpen>';
		echo '<us_version_userStoriesGesamt>' . 
			$agilemantis_version->getVersionUserStories( $userstory[0]['project_id'], $userstory[0]['target_version'] ) . 
				'</us_version_userStoriesGesamt>';
		echo '<us_version_userStoriesOpen>' . 
			$agilemantis_version->getNumberOfUserStories( $userstory[0]['project_id'], $userstory[0]['target_version'] ) . 
				'</us_version_userStoriesOpen>';
		echo '<us_version_beschreibung>' . utf8_decode( $versiondescription ) . '</us_version_beschreibung>';
	echo '</version>';
	
	$id	= $agilemantis_sprint->us_id;
	$addFields = $agilemantis_sprint->checkForUserStory( $id );
	if( !empty( $addFields ) ){
		echo '<additionalFields>';
			echo '<field>';
				echo '<field_name>Product Backlog</field_name>';
				echo '<field_value>' . $agilemantis_commonlib->safeCData( string_display_line_links( $addFields['name'] ) ) . '</field_value>';
				echo '<field_datatype>String</field_datatype>';
			echo '</field>';
			echo '<field>';
				echo '<field_name>Story Points</field_name>';
				echo '<field_value>' . $addFields['storypoints'] . '</field_value>';
				echo '<field_datatype>Double</field_datatype>';
			echo '</field>';
			echo '<field>';
				echo '<field_name>Business Value</field_name>';
				echo '<field_value><![CDATA[' . $addFields['businessValue'] . ']]></field_value>';
				echo '<field_datatype>String</field_datatype>';
			echo '</field>';
			echo '<field>';
				echo '<field_name>Sprint</field_name>';
				echo '<field_value>' . $agilemantis_commonlib->safeCData( string_display_line_links( $addFields['sprint'] ) ) . '</field_value>';
				echo '<field_datatype>String</field_datatype>';
			echo '</field>';
			if( $agilemantis_tasks->getConfigValue( 'plugin_agileMantis_gadiv_tracker_planned_costs' ) == '1' ){
				echo '<field>';
					echo '<field_name>' . plugin_lang_get( 'PlannedWork', 'agileMantis' ) . ' ('.$_POST['userstorycostunit'].')</field_name>';
					echo '<field_value>' . $addFields['plannedWork'] . '</field_value>';
					echo '<field_datatype>Double</field_datatype>';
				echo '</field>';
			}
			if( $agilemantis_tasks->getConfigValue( 'plugin_agileMantis_gadiv_ranking_order' ) == '1' ){
				echo '<field>';
					echo '<field_name>' . plugin_lang_get( 'RankingOrder', 'agileMantis' ) . '</field_name>';
					echo '<field_value>' . $addFields['rankingorder'] . '</field_value>';
					echo '<field_datatype>String</field_datatype>';
				echo '</field>';
			}
			if( $agilemantis_tasks->getConfigValue( 'plugin_agileMantis_gadiv_presentable' ) == '1' ){
				if( $addFields['presentable'] == 0 ){
					$string = plugin_lang_get( 'view_issue_non_presentable', 'agileMantis' );
				}else if( $addFields['presentable'] == 1 ){
					$string = plugin_lang_get( 'view_issue_technical_presentable', 'agileMantis' );
				}else if( $addFields['presentable'] == 2 ){
					$string = plugin_lang_get( 'view_issue_functional_presentable', 'agileMantis' );
				}
				echo '<field>';
					echo '<field_name>' . plugin_lang_get( 'Presentable', 'agileMantis' ) . '</field_name>';
					echo '<field_value>' . utf8_decode($string) . '</field_value>';
					echo '<field_datatype>String</field_datatype>';
				echo '</field>';
			}
			if( $agilemantis_tasks->getConfigValue( 'plugin_agileMantis_gadiv_technical' ) == '1' ){
				if( $addFields['technical'] == 0 ){ 
					$string = 'false'; 
				}else if( $addFields['technical'] == 1 ){ 
					$string = 'true'; 
				}
				echo '<field>';
					echo '<field_name>' . plugin_lang_get( 'Technical', 'agileMantis' ) . '</field_name>';
					echo '<field_value>' . $string . '</field_value>';
					echo '<field_datatype>Boolean</field_datatype>';
				echo '</field>';
			}
			if( $agilemantis_tasks->getConfigValue( 'plugin_agileMantis_gadiv_release_documentation' ) == '1' ){
				if( $addFields['inReleaseDocu'] == 0 ){ 
					$string = 'false'; 
				}else if( $addFields['inReleaseDocu'] == 1 ){ 
					$string = 'true'; 
				}
				echo '<field>';
					echo '<field_name>' . plugin_lang_get( 'InReleaseDocu', 'agileMantis' ) . '</field_name>';
					echo '<field_value>' . $string . '</field_value>';
					echo '<field_datatype>Boolean</field_datatype>';
				echo '</field>';
			}
			echo '</additionalFields>';
	}
	
	$t_tasks = $agilemantis_sprint->getSprintTasks( $id );
	if( !empty( $t_tasks ) ){
		echo '<tasks>';
		foreach( $t_tasks AS $num => $row ){
			
			$created = $agilemantis_tasks->getTaskEvent( $row['id'], 'created' );
			$confirmed = $agilemantis_tasks->getTaskEvent( $row['id'], 'confirmed' );
			$resolved = $agilemantis_tasks->getTaskEvent( $row['id'], 'resolved' );
			$reopened = $agilemantis_tasks->getTaskEvent( $row['id'], 'reopened' );
			$closed = $agilemantis_tasks->getTaskEvent( $row['id'], 'closed' );
			
			echo '<task>';
				echo '<task_id>' . $row['id'] . '</task_id>';
				echo '<task_name>' . $agilemantis_commonlib->safeCData( string_display_line_links( $row['name'] ) ) . '</task_name>';
				echo '<task_description>' . $agilemantis_commonlib->safeCData( $row['description'] ) .'</task_description>';
				echo '<task_daily_scrum>' . ( ( int ) $row['daily_scrum'] ) . '</task_daily_scrum>';
				if( $row['developer_id'] > 0 ){
					echo '<developer>';
						echo '<dev_id>' . $row['developer_id'] . '</dev_id>';
						echo '<dev_username>' . $agilemantis_tasks->getUserName( $row['developer_id'] ) . '</dev_username>';
						echo '<dev_realname>' . $agilemantis_tasks->getUserRealName( $row['developer_id'] ) . '</dev_realname>';
					echo '</developer>';
				}
				echo '<task_status>' . $row['status'] . '</task_status>';
				echo '<task_planned_capacity>' . $row['planned_capacity'] . '</task_planned_capacity>';
				echo '<task_performed_capacity>' . $row['performed_capacity'] . '</task_performed_capacity>';
				echo '<task_rest_capacity>' . $row['rest_capacity'] . '</task_rest_capacity>';
				
				
				$create_date = strtotime( $created['date'] );
				$confirm_date = strtotime( $confirmed['date'] );
				$resolve_date = strtotime( $resolved['date'] );
				$close_date = strtotime( $closed['date'] );
				$reopen_date = strtotime( $reopened['date'] );
				if( $created['user_id'] > 0 ){
					echo '<task_created>' . $agilemantis_tasks->getUserName( $created['user_id'] );
					echo ' / ' . date( 'd.m.Y', $create_date ) . '</task_created>';
				}
				if( $confirmed['user_id'] > 0 ){
					echo '<task_confirmed>' . $agilemantis_tasks->getUserName( $confirmed['user_id'] );
					echo ' / ' . date( 'd.m.Y',$confirm_date ) . '</task_confirmed>';
				}
				if( $resolved['user_id'] > 0 ){
				echo '<task_resolved>' . $agilemantis_tasks->getUserName( $resolved['user_id'] );
				echo ' / ' . date( 'd.m.Y', $resolve_date ) . '</task_resolved>';
				}
				if( $closed['user_id'] > 0 ){
					echo '<task_closed>' . $agilemantis_tasks->getUserName( $closed['user_id'] );
					echo ' / ' . date( 'd.m.Y', $close_date ) . '</task_closed>';
				}
				if( $reopened['user_id'] > 0 ){
					echo '<task_reopened>' . $agilemantis_tasks->getUserName( $reopened['user_id'] );
					echo ' / ' . date( 'd.m.Y', $reopen_date ) . '</task_reopened>';
				}
			echo '</task>';
		}
		echo '</tasks>';
	}
	
	$notices = $agilemantis_tasks->getNotices( $id );
	if( !empty( $notices ) ){
		echo '<notices>';
		foreach( $notices AS $num => $row ){
			echo '<notice>';
				echo '<note_id>' . $row['id'] . '</note_id>';
				echo '<note_description>' . $agilemantis_commonlib->safeCData( string_display_links( $row['note'] ) ) . '</note_description>';
				echo '<note_reporter><![CDATA[' . $agilemantis_tasks->getUserName( $row['reporter_id'] ) . ']]></note_reporter>';
				echo '<note_date>' . date( 'd.m.Y', $row['date_submitted'] ) . '</note_date>';
			echo '</notice>';
		}
		echo '</notices>';
	}
	echo '</userstory>';
?>