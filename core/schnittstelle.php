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

ob_start();

//error_reporting( NULL );

$mantisBtPath = realpath(dirname(__FILE__));
$mantisBtPath = str_replace('plugins' . DIRECTORY_SEPARATOR . 
				'agileMantis' . DIRECTORY_SEPARATOR . 'core', '', $mantisBtPath);
$mantisBtPath = str_replace('plugins' . DIRECTORY_SEPARATOR . 
				'agileMantis', '', $mantisBtPath);

require_once ($mantisBtPath . 'core.php');
require_once ($mantisBtPath . 'config_inc.php');

// Load agileMantis configuration
require_once (AGILEMANTIS_CORE_URI . 'config_api.php');

$_COOKIE[ $g_cookie_prefix . '_STRING_COOKIE'] = $_POST['cookie_string'];

// Zugriff auf die Mantis Funktionen
//require_once ($mantisPath . 'core.php');

// Lade die Konfigurationsdatei und stelle die Datenbankverbindung her
// Zugriff auf die agileMantis Funktionen

if( $_POST['timezone'] ) {
	date_default_timezone_set( $_POST['timezone'] );
}
ob_end_clean();

if( $_POST['user'] ) {
	$sitekey = $agilemantis_tasks->getConfigValue( 'plugin_agileMantis_gadiv_sitekey' );
	$heute = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'y' ) );
	$generatedKey = md5( $sitekey . $heute );
	$user_id = $_POST['user'];
	$language = user_pref_get_language( $user_id );
	lang_load( $language, $mantisBtPath . 'plugins' . DIRECTORY_SEPARATOR . 'agileMantis' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR );	
	lang_push( $language );

	// Load language stuff
	if( $language == 'german' ) {
		require_once ($mantisBtPath . 'lang' . DIRECTORY_SEPARATOR . 'strings_german.txt');
		require_once ($mantisBtPath . 'plugins' . DIRECTORY_SEPARATOR . 'agileMantis' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'strings_german.txt');
	} else {
		require_once ($mantisBtPath . 'lang' . DIRECTORY_SEPARATOR . 'strings_english.txt');
		require_once ($mantisBtPath . 'plugins' . DIRECTORY_SEPARATOR . 'agileMantis' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'strings_english.txt');
	}
	
	if( $_POST['event'] == 'checkIdentity' ) {
		if( $generatedKey == $_POST['appletkey'] ) {
			echo true;
		} else {
			echo false;
		}
	}
	
	if( $generatedKey == $_POST['appletkey'] ) {
		// Event-Trigger: Schaue nach ob $_POST['event'] gesetzt worden ist
		// und wenn der gesetzt worden ist, Überprüfe ob ein Event vorliegt.
		// Ansonsten nutze den Standardfall!
	
		if( isset( $_POST['event'] ) ) {
			switch( $_POST['event'] ) {
				case 'loadLicense':
					echo '<licenseData>';
					if( is_file( AGILEMANTIS_LICENSE_PATH ) ) {
						$lines = array();
						$fp = fopen( AGILEMANTIS_LICENSE_PATH, 'r' );
						while( !feof( $fp ) ) {
							
							$line = fgets( $fp );
							
							// process line however you like
							$line = trim( $line );
							
							// add to array
							if( $line != "" ) {
								$lines[] = $line;
							}
						}
						fclose( $fp );
						if( count( $lines ) <= 5 ) {
							echo '<licenseKey>' . $lines[0] . '</licenseKey>';
							echo '<domain><![CDATA[' . $lines[1] . ']]></domain>';
							echo '<organisation><![CDATA[' . $lines[2] . ']]></organisation>';
							echo '<date>' . $lines[3] . '</date>';
							echo '<developer><![CDATA[' . $lines[4] . ']]></developer>';
						} else {
							echo '<licenseKey>corrupted</licenseKey>';
						}
						echo '<amtDeveloper>' . $agilemantis_au->countSessions() . '</amtDeveloper>';
						echo '<session>' . $agilemantis_tasks->getSession( $user_id ) . '</session>';
					} else {
						echo '<licenseKey>FILE NOT FOUND</licenseKey>';
					}
					echo '</licenseData>';
					break;
				// Lädt alle Sprints aus der Datenbank und generiert eine dynamische XML-Ausgabe
				case 'loadSprint':
					$agilemantis_sprint->sprint_id = $_POST['sprintName'];
					$sprintData = $agilemantis_sprint->getSprintById();
				
					# convert dates
					$sprint_start_date = explode( '-', $sprintData['start'] );
					$sprint_end_date = explode( '-', $sprintData['end'] );
					$sprintData['start'] = mktime( 0, 0, 0, $sprint_start_date[1], $sprint_start_date[2], $sprint_start_date[0] );
					$sprintData['end'] = mktime( 0, 0, 0, $sprint_end_date[1], $sprint_end_date[2], $sprint_end_date[0] );
					
					# get product backlog name
					$pb = new gadiv_productBacklog();
					$pbName = $agilemantis_pb->getProductBacklogNameById( $sprintData['pb_id'] );
					
					# Difference between two dates
					$t_end_date = $sprintData['end'];
					if( time() >= $sprintData['start'] ) {
						$t_start_date = time();
					} else {
						$t_start_date = $sprintData['start'] ;
					}
					
					if( $sprintData['status'] == 0 ) {
						$t_start_date = $sprintData['start'] ;
					}
					
					$t_time_difference = $t_end_date - $t_start_date;
					$t_amount_of_days = ceil( $t_time_difference / 86400 );
									
					if( $t_amount_of_days == 0 && $t_end_date > time() ) {
						$t_amount_of_days = 1;
					} elseif( $t_amount_of_days <= 0 ) {
						$t_amount_of_days = 0;
					}
	
					#calcuating rest capacity
					$t_remaining_effort = 0;
					$t_userstories = $agilemantis_sprint->getSprintStories( $sprintData['name'], false );
					if( !empty( $t_userstories ) ) {
						foreach( $t_userstories as $num => $row ) {
							$t_userstory_tasks = $agilemantis_sprint->getSprintTasks( $row['id'], 0 );
							if( !empty( $t_userstory_tasks ) ) {
								foreach( $t_userstory_tasks as $key => $value ) {
									$t_remaining_effort += $value['rest_capacity'];	
								}
							}
						}
					}
					
					if( $t_remaining_effort <= 0 ) {
						$t_remaining_effort = 0;
					}
					
					# calculating rest capacity
					$t_capacity = $agilemantis_av->getTeamCapacity( $sprintData['team_id'], date( 'Y-m-d', $t_start_date ), date( 'Y-m-d', $t_end_date ) );
						
					if( $t_capacity == "" ) {
						$t_capacity = 0;
					}
					
					echo '<sprint>';
					echo '<id>' . $sprintData['id'] . '</id>';
					echo '<name>' . $agilemantis_commonlib->safeCData($sprintData['name']) . '</name>';
					echo '<convertedName>' . $agilemantis_commonlib->safeCData(string_display_line_links(utf8_decode($sprintData['name']))) . '</convertedName>';
					echo '<status>' . $sprintData['status'] . '</status>';
					echo '<timeRemaining>' . $t_amount_of_days . '</timeRemaining>';
					echo '<effortRemaining>' . sprintf( "%.2f", $t_remaining_effort ) . '</effortRemaining>';
					echo '<capacityRemaining>' . sprintf( "%.2f", $t_capacity ) . '</capacityRemaining>';
					echo '<team>' . $agilemantis_sprint->getTeamById( $sprintData['team_id'] ) . '</team>';
					echo '<start>' . date( 'd.m.Y', $sprintData['start'] ) . '</start>';
					echo '<end>' . date( 'd.m.Y', $sprintData['end'] ) . '</end>';
					echo '<description>' . $agilemantis_commonlib->safeCData( string_display_links( $sprintData['description'] ) ) .'</description>';
					echo '<productBacklog>' . $agilemantis_commonlib->safeCData($pbName) . '</productBacklog>';
					echo '</sprint>';
					break;
				// Lädt alle Userstories mit den entsprechenden Tasks, die zu
				// einer Userstory geh�ren und generiert eine dynamische XML-Ausgabe
				case 'loadUserStory':
					$severity_array = explode( ',', $s_severity_enum_string );
					foreach( $severity_array as $num => $row ) {
						$temp = explode( ':', $row );
						$severity[$temp[0]] = $temp[1];
					}
					$status_array = explode( ',', $s_status_enum_string );
					foreach( $status_array as $num => $row ) {
						$temp = explode( ':', $row );
						$status[$temp[0]] = $temp[1];
					}
					$agilemantis_sprint->us_id = $_POST['userstory_id'];
					include (AGILEMANTIS_PLUGIN_URI . 'core/schnittstelle_load_userstory.php');
	
					break;
				case 'loadUserstories':
					
					$severity_array = explode( ',', $s_severity_enum_string );
					foreach( $severity_array as $num => $row ) {
						$temp = explode( ':', $row );
						$severity[$temp[0]] = $temp[1];
					}
					$status_array = explode( ',', $s_status_enum_string );
					foreach( $status_array as $num => $row ) {
						$temp = explode( ':', $row );
						$status[$temp[0]] = $temp[1];
					}
					if( isset( $_POST['sprintName'] ) && !empty( $_POST['sprintName'] ) ) {
						$name = $_POST['sprintName'];
						$userstories = $agilemantis_sprint->getSprintStories( $name );
						if( !empty( $userstories ) ) {
							echo '<sprintstories>';
							foreach( $userstories as $num => $row ) {
								$agilemantis_sprint->us_id = $row['id'];
								include (AGILEMANTIS_PLUGIN_URI . 'core/schnittstelle_load_userstory.php');
							}
							echo '</sprintstories>';
						}
					}
					break;
				//Der Status kann bearbeitet werden
				case 'updateUserstory':
					$id = ( int ) $_POST['id'];
					if( $id > 0 ) {
						$agilemantis_tasks->setConfirmationStatus( $id );
						$status = 50;
						if( $agilemantis_tasks->hasTasksLeft( $id ) != "" ) {
							$agilemantis_tasks->closeUserStory( $id, 80, $user_id );
							$status = 80;
						}
						echo $status;
					}
					break;
				//Ein Task kann anhand seiner id bearbeitet werden, wobei
				//zuerst alle Variablen übergeben werden und dabei der neue
				//Rest-Aufwand gebildet wird und anschließend in die Datenbank
				//geschrieben. Es werden die Änderungen an einer Task in Form
				//von generiertem XML ausgegeben.
				case 'editTask':
					
					// Hole Sprint Informationen
					$usData = $agilemantis_tasks->checkForUserStory( $_POST['us_id'] );
					$agilemantis_sprint->sprint_id = $usData['sprint'];
					$getSprint = $agilemantis_sprint->getSprintById();
					
					if( $getSprint['status'] == 0 ) {
						$agilemantis_tasks->planned_capacity = str_replace( ',', '.', $_POST['planned_capacity'] );
					}
					
					if( $getSprint['status'] == 1 && $_POST['planned_capacity'] > 0 ) {
						$agilemantis_tasks->planned_capacity = str_replace( ',', '.', $_POST['planned_capacity'] );
					}
					
					if( $getSprint['status'] == 1 && $_POST['planned_capacity'] > 0 && $_POST['id'] == 0 ) {
						$agilemantis_tasks->planned_capacity = 0;
					}
					
					// Sammle alle Variablen
					$agilemantis_tasks->developer = ( int ) $_POST['developer'];
					$agilemantis_tasks->us_id = ( int ) $_POST['us_id'];
					$agilemantis_tasks->id = ( int ) $_POST['id'];
					$agilemantis_tasks->user_id = $user_id;
					$agilemantis_tasks->name = $_POST['name'];
					$agilemantis_tasks->description = $_POST['description'];
					$agilemantis_tasks->performed_capacity = sprintf( "%.2f", str_replace( ',', '.', $_POST['performed_capacity'] ) );
					$agilemantis_tasks->rest_capacity = sprintf( "%.2f", str_replace( ',', '.', $_POST['rest_capacity'] ) - str_replace( ',', '.', $_POST['performed_capacity_today'] ) );
					$agilemantis_tasks->status = $_POST['status'];
					$agilemantis_tasks->daily_scrum = 0;
					
					//if( $_POST['id'] == 0 ) {
					$agilemantis_tasks->unit = $_POST['gadiv_task_unit_mode'];
					//}
					
					if( $getSprint['status'] == 1 && str_replace( ',', '.', $_POST['rest_capacity'] ) != str_replace( ',', '.', $_POST['old_rest_capacity'] ) && str_replace( ',', '.', $_POST['rest_capacity'] ) > 0 ) {
						$agilemantis_tasks->daily_scrum = 1;
					}
					
					if( $getSprint['status'] == 1 && str_replace( ',', '.', $_POST['performed_capacity_today'] ) != 0 ) {
						$agilemantis_tasks->daily_scrum = 1;
					}
					
					if( $getSprint['status'] == 1 && str_replace( ',', '.', $_POST['performed_capacity_today'] ) != 0 || $_POST['oldstatus'] == $_POST['status'] ) {
						$agilemantis_tasks->daily_scrum = 1;
					}
					
					if( $getSprint['status'] == 1 && $agilemantis_tasks->id == 0 ) {
						$agilemantis_tasks->daily_scrum = 1;
					}
					
					if( $getSprint['status'] == 1 && $_POST['oldstatus'] != $_POST['status'] ) {
						$agilemantis_tasks->daily_scrum = 1;
					}
					
					$agilemantis_tasks->capacity = str_replace( ',', '.', $_POST['performed_capacity_today'] );
					
					if( str_replace( ',', '.', $_POST['rest_capacity'] ) == str_replace( ',', '.', $_POST['old_rest_capacity'] ) && str_replace( ',', '.', $_POST['performed_capacity_today'] ) == 0 ) {
						$agilemantis_tasks->capacity = 0;
					}
					
					if( str_replace( ',', '.', $_POST['rest_capacity'] ) > str_replace( ',', '.', $_POST['old_rest_capacity'] ) && str_replace( ',', '.', $_POST['performed_capacity_today'] ) == 0 ) {
						$agilemantis_tasks->capacity = 0;
					}
					
					if( str_replace( ',', '.', $_POST['rest_capacity'] ) < str_replace( ',', '.', $_POST['old_rest_capacity'] ) ) {
						$agilemantis_tasks->capacity = 0;
						if( $_POST['rest_capacity'] <= 0 ) {
							$agilemantis_tasks->capacity = 0;
							$agilemantis_tasks->rest_capacity = 0;
						}
					}
					
					if( str_replace( ',', '.', $_POST['performed_capacity_today'] ) != 0 && str_replace( ',', '.', $_POST['rest_capacity'] ) != str_replace( ',', '.', $_POST['old_rest_capacity'] ) ) {
						$agilemantis_tasks->capacity = str_replace( ',', '.', $_POST['performed_capacity_today'] );
						$agilemantis_tasks->rest_capacity = str_replace( ',', '.', $_POST['rest_capacity'] );
					}
					
					if( str_replace( ',', '.', $_POST['rest_capacity'] ) - str_replace( ',', '.', $_POST['performed_capacity_today'] ) <= 0 && str_replace( ',', '.', $_POST['rest_capacity'] ) == str_replace( ',', '.', $_POST['old_rest_capacity'] ) ) {
						$agilemantis_tasks->rest_capacity = 0;
					}
					
					if( $_POST['oldstatus'] != $_POST['status'] ) {
						$agilemantis_tasks->capacity = str_replace( ',', '.', $_POST['performed_capacity_today'] );
					}
					
					if( $agilemantis_tasks->performed_capacity + str_replace( ',', '.', $_POST['performed_capacity_today'] ) < 0 ) {
						$agilemantis_tasks->capacity = 0;
						$agilemantis_tasks->capacity -= $agilemantis_tasks->performed_capacity;
						$agilemantis_tasks->performed_capacity = 0;
						$agilemantis_tasks->rest_capacity = sprintf( "%.2f", str_replace( ',', '.', $_POST['rest_capacity'] ) );
					}
					
					// STATUS NEU, wenn von ERLEDIGT oder GESCHLOSSEN gewechselt wird
					if( $_POST['oldstatus'] > 3 && $agilemantis_tasks->status == 1 && $agilemantis_tasks->performed_capacity == 0 ) {
						$agilemantis_tasks->status = 1;
					}
					
					// STATUS ZUGEWIESEN, wenn von ERLEDIGT oder GESCHLOSSEN gewechselt wird
					if( $_POST['oldstatus'] > 3 && $agilemantis_tasks->status == 1 && $agilemantis_tasks->performed_capacity == 0 && $agilemantis_tasks->developer > 0 ) {
						
						$agilemantis_tasks->status = 2;
					}
					
					// STATUS ZUGEWIESEN, wenn von ERLEDIGT oder GESCHLOSSEN gewechselt wird
					if( $_POST['oldstatus'] > 3 && ($agilemantis_tasks->status == 1 || $agilemantis_tasks->status == 2) && $agilemantis_tasks->performed_capacity > 0 && $agilemantis_tasks->developer > 0 && $agilemantis_tasks->rest_capacity == 0 ) {
						
						$agilemantis_tasks->status = 4;
					}
					
					// STATUS ÜBERNOMMEN, wenn von ERLEDIGT oder GESCHLOSSEN gewechselt wird
					if( $_POST['oldstatus'] > 3 && $agilemantis_tasks->performed_capacity > 0 && $agilemantis_tasks->developer == 0 && $agilemantis_tasks->rest_capacity == 0 ) {
						$agilemantis_tasks->status = 3;
					}
					
					if( $_POST['oldstatus'] == $_POST['status'] ) {
						
						// STATUS NEU
						if( $agilemantis_tasks->status == 0 ) {
							$agilemantis_tasks->status = 1;
						}
						
						if( $agilemantis_tasks->status == 2 && $agilemantis_tasks->developer == 0 && $agilemantis_task->perfomed_capacity == 0 ) {
							$agilemantis_tasks->status = 1;
						}
						
						// STATUS ZUGEWIESEN
						if( $agilemantis_tasks->status < 3 && $agilemantis_tasks->developer > 0 && $agilemantis_tasks->performed_capacity == 0 ) {
							$agilemantis_tasks->status = 2;
						}
						
						if( $agilemantis_tasks->status < 3 && $agilemantis_tasks->developer > 0 && $agilemantis_tasks->rest_capacity > 0 ) {
							$agilemantis_tasks->status = 2;
						}
						
						if( $agilemantis_tasks->status > 3 && $agilemantis_tasks->developer > 0 && $agilemantis_tasks->rest_capacity > 0 ) {
							$agilemantis_tasks->status = 2;
						}
						
						// STATUS ÜBERNOMMEN
						if( $agilemantis_tasks->performed_capacity != 0 && $agilemantis_tasks->planned_capacity > 0 && $agilemantis_tasks->rest_capacity > 0 ) {
							$agilemantis_tasks->status = 3;
						}
						
						if( $agilemantis_tasks->status == 2 && $_POST['performed_capacity_today'] != 0 ) {
							$agilemantis_tasks->status = 3;
						}
						
						if( str_replace( ',', '.', $_POST['rest_capacity'] ) < str_replace( ',', '.', $_POST['old_rest_capacity'] ) && $agilemantis_tasks->rest_capacity > 0 && $getSprint['status'] == 1 ) {
							$agilemantis_tasks->status = 3;
						}
						
						// STATUS ERLEDIGT
						if( $agilemantis_tasks->status != 5 && $agilemantis_tasks->performed_capacity > 0 && $agilemantis_tasks->planned_capacity > 0 && $agilemantis_tasks->rest_capacity <= 0 ) {
							$agilemantis_tasks->status = 4;
						}
						
						if( ($agilemantis_tasks->status == 3 || $agilemantis_tasks->status == 2) && $agilemantis_tasks->performed_capacity > 0 && $agilemantis_tasks->rest_capacity <= 0 ) {
							$agilemantis_tasks->status = 4;
						}
						
						if( ($agilemantis_tasks->status == 3 || $agilemantis_tasks->status == 2) && $agilemantis_tasks->rest_capacity <= 0.00 && str_replace( ',', '.', $_POST['performed_capacity_today'] ) > 0 ) {
							$agilemantis_tasks->status = 4;
						}
						
						// STATUS GESCHLOSSEN
						if( $agilemantis_tasks->status == 5 && $agilemantis_tasks->rest_capacity == 0 && $agilemantis_tasks->planned_capacity > 0 && $agilemantis_tasks->performed_capacity > 0 ) {
							$agilemantis_tasks->status = 5;
						}
					}
					
					if( $_POST['oldstatus'] > 3 && $agilemantis_tasks->status == 1 && $agilemantis_tasks->developer > 0 ) {
						$agilemantis_tasks->status = 2;
					}
					
					if( $_POST['oldstatus'] == 5 && $_POST['status'] == 4 ) {
						$agilemantis_tasks->deleteTaskLog( $agilemantis_tasks->id, "closed" );
					}
					
					if( $_POST['oldstatus'] > 3 && $agilemantis_tasks->status == 1 && $agilemantis_tasks->developer > 0 && $agilemantis_tasks->performed_capacity > 0 ) {
						$agilemantis_tasks->status = 3;
					}
					
					if( $_POST['oldstatus'] > 3 && $agilemantis_tasks->status == 2 && $agilemantis_tasks->developer > 0 && $agilemantis_tasks->performed_capacity > 0 ) {
						$agilemantis_tasks->status = 3;
					}
					
					// Wenn eine Task neu angelegt wird, setze Rest-Aufwand = Plan-Aufwand und Korrigierter Plan = Rest-Aufwand
					if( $_POST['id'] == 0 ) {
						$agilemantis_tasks->rest_capacity = str_replace( ',', '.', $_POST['planned_capacity'] );
					}
					
					if( $getSprint['status'] == 0 ) {
						$agilemantis_tasks->rest_capacity = $agilemantis_tasks->planned_capacity;
						if( $_POST['id'] > 0 ) {
							$agilemantis_tasks->replacePlannedCapacity( $_POST['id'] );
						}
					}
					
					if( $agilemantis_tasks->status == 2 ) {
						$agilemantis_userstory->addBugMonitor( $agilemantis_tasks->developer, $agilemantis_tasks->us_id );
					}
					
					// Wenn von Geschlossen oder Erledigt auf Neu oder Zugewiesen gewechselt wird, ändere Tasklog
					if( ($_POST['oldstatus'] >= 4) && $agilemantis_tasks->status <= 3 ) {
						$agilemantis_tasks->updateTaskLog( $agilemantis_tasks->id, $user_id, "reopened" );
						$agilemantis_tasks->addReopenNote( $agilemantis_tasks->us_id, $agilemantis_tasks->id, $user_id );
					}
					
					// Wenn Task-Status übernommen, dann setze Eintrag "übernommen"
					if( $agilemantis_tasks->status == 3 ) {
						$agilemantis_tasks->updateTaskLog( $agilemantis_tasks->id, $user_id, "confirmed" );
					}
					
					// Wenn Status 4 oder 5, verringere den korrigierten Plan um den Rest-Aufwand und setze Rest-Aufwand auf 0
					if( $agilemantis_tasks->status == 4 || $agilemantis_tasks->status == 5 ) {
						$agilemantis_tasks->rest_capacity = 0;
					}
					
					if( $getSprint['status'] == 1 ) {
						$agilemantis_tasks->saveDailyPerformance( 0 );
					}
					
					if( $agilemantis_tasks->status == 4 && $_POST['oldstatus'] != $_POST['status'] ) {
						$agilemantis_tasks->updateTaskLog( $agilemantis_tasks->id, $user_id, "resolved" );
						if( $_POST['oldstatus'] != 5 ) {
							$agilemantis_tasks->addFinishedNote( $agilemantis_tasks->us_id, $agilemantis_tasks->id, $user_id );
						}
						$task_resolved = true;
					}
					
					if( $agilemantis_tasks->status == 5 && $_POST['oldstatus'] != $_POST['status'] ) {
						$agilemantis_tasks->updateTaskLog( $agilemantis_tasks->id, $user_id, "closed" );
						if( $_POST['oldstatus'] != 4 ) {
							$agilemantis_tasks->addFinishedNote( $agilemantis_tasks->us_id, $agilemantis_tasks->id, $user_id );
						}
						$task_closed = true;
					}
					
					if( $agilemantis_tasks->id == 0 ) {
						$agilemantis_tasks->capacity -= $agilemantis_tasks->planned_capacity;
					}
					$id = $agilemantis_tasks->editTask();
					
					if( $_POST['oldstatus'] != $agilemantis_tasks->status ) {
						$agilemantis_tasks->daily_scrum = 1;
					}
					
					$agilemantis_tasks->setDailyScrum( $agilemantis_tasks->id, $agilemantis_tasks->daily_scrum );
					$agilemantis_tasks->id = $id;
					$taskInfo = $agilemantis_tasks->getSelectedTask( $id );
					$created = $agilemantis_tasks->getTaskEvent( $row['id'], 'created' );
					$confirmed = $agilemantis_tasks->getTaskEvent( $row['id'], 'confirmed' );
					$resolved = $agilemantis_tasks->getTaskEvent( $row['id'], 'resolved' );
					$reopened = $agilemantis_tasks->getTaskEvent( $row['id'], 'reopened' );
					$closed = $agilemantis_tasks->getTaskEvent( $row['id'], 'closed' );
					echo '<task>';
					echo '<id>' . $taskInfo['id'] . '</id>';
					echo '<name>' . $agilemantis_commonlib->safeCData($taskInfo['name']) . '</name>';
					echo '<description>' . $agilemantis_commonlib->safeCData($taskInfo['description']) .'</description>';
					echo '<task_daily_scrum>' . $taskInfo['daily_scrum'] . '</task_daily_scrum>';
					if( $taskInfo['developer_id'] > 0 ) {
						echo '<developer>';
						echo '<dev_id>' . $taskInfo['developer_id'] . '</dev_id>';
						echo '<dev_username>' . $agilemantis_tasks->getUserName( $taskInfo['developer_id'] ) . '</dev_username>';
						echo '<dev_realname>' . $agilemantis_tasks->getUserRealName( $taskInfo['developer_id'] ) . '</dev_realname>';
						echo '</developer>';
					}
					echo '<status>' . $taskInfo['status'] . '</status>';
					echo '<planned_capacity>' . $taskInfo['planned_capacity'] . '</planned_capacity>';
					echo '<performed_capacity>' . $taskInfo['performed_capacity'] . '</performed_capacity>';
					echo '<rest_capacity>' . $taskInfo['rest_capacity'] . '</rest_capacity>';
					$create_date = strtotime( $created['date'] );
					$confirm_date = strtotime( $confirmed['date'] );
					$resolve_date = strtotime( $resolved['date'] );
					$close_date = strtotime( $closed['date'] );
					$reopen_date = strtotime( $reopened['date'] );
					if( $created['user_id'] > 0 ) {
						echo '<task_created>' . $agilemantis_tasks->getUserName( $created['user_id'] ) . ' / ' . $create_date . '</task_created>';
					}
					if( $confirmed['user_id'] > 0 ) {
						echo '<task_confirmed>' . $agilemantis_tasks->getUserName( $confirmed['user_id'] ) . ' / ' . $confirm_date . '</task_confirmed>';
					}
					if( $resolved['user_id'] > 0 ) {
						echo '<task_resolved>' . $agilemantis_tasks->getUserName( $resolved['user_id'] ) . ' / ' . $resolve_date . '</task_resolved>';
					}
					if( $closed['user_id'] > 0 ) {
						echo '<task_closed>' . $agilemantis_tasks->getUserName( $closed['user_id'] ) . ' / ' . $close_date . '</task_closed>';
					}
					if( $reopened['user_id'] > 0 ) {
						echo '<task_reopened>' . $agilemantis_tasks->getUserName( $reopened['user_id'] ) . ' / ' . $reopen_date . '</task_reopened>';
					}
					echo '</task>';
					break;
				// Erhalte alle Team-Benutzer von agileMantis, welche
				// innerhalb eines Teams ein bestimmtes Product Backlog
				// - das im ausgewählten Sprint - bearbeiten sollen.
				// Die ermittelten Entwickler werden als dynamisches XML
				// ausgegeben.
				case 'getDevelopers':
					$agilemantis_tasks->us_id = ( int ) $_POST['userstory_id'];
					$usData = $agilemantis_tasks->checkForUserStory( $agilemantis_tasks->us_id );
					if( !empty( $usData ) ) {
						$agilemantis_sprint->sprint_id = $usData['sprint'];
						$sprintInfo = $agilemantis_sprint->getSprintById();
						$agilemantis_team->id = $sprintInfo['team_id'];
						$user = $agilemantis_team->getTeamDeveloper();
					}
					if( !empty( $user ) ) {
						echo '<developers>';
						foreach( $user as $num => $row ) {
							if( $row['id'] != 0 ) {
								echo '<developer>';
								echo '<id>' . $row['id'] . '</id>';
								echo '<username>' . $row['username'] . '</username>';
								echo '<realname>' . $row['realname'] . '</realname>';
								echo '<capacity>' . $agilemantis_team->getTotalTeamMemberCapacityBySprint( $row['id'], $usData['sprint'] ) . '</capacity>';
								echo '</developer>';
							}
						}
						echo '</developers>';
					}
					break;
 				//Löscht eine Task anhand der Benutzer-id
				case 'deleteTask':
					$agilemantis_tasks->id = ( int ) $_POST['id'];
					echo $agilemantis_tasks->deleteTask();
					break;
				case 'setDailyScrum':
					$agilemantis_tasks->id = ( int ) $_POST['id'];
					$agilemantis_tasks->setDailyScrum( $agilemantis_tasks->id, 0 );
					if( $_POST['developer'] == null || $_POST['developer'] == 0 ) {
						$_POST['developer'] = $_POST['user_id'];
					}
					$agilemantis_tasks->updateTaskLog( $agilemantis_tasks->id, ( int ) $_POST['developer'], "daily_scrum" );
					echo 1;
					break;
				//Loggt alle Vorgänge rund um die Tasks.
				case 'logTaskAction':
					$agilemantis_tasks->updateTaskLog( $_POST['id'], $_POST['user'], $_POST['taskAction'] );
					if( $_POST['taskAction'] == 'confirmed' || $_POST['taskAction'] == 'reopened' ) {
						$agilemantis_tasks->deleteTaskLog( $agilemantis_tasks->id, 'closed' );
						$agilemantis_tasks->deleteTaskLog( $agilemantis_tasks->id, 'resolved' );
					}
					break;
				case 'getStatistics':
					if( isset( $_POST['sprintName'] ) ) {
						
						$name = $_POST['sprintName'];
						$onlyOpenStories = false;
						if( $_POST['only_open_userstories'] == 1 ) {
							$onlyOpenStories = true;
						}
						
						$userstories = $agilemantis_sprint->getSprintStories( $name, $onlyOpenStories );
						
						$agilemantis_sprint->sprint_id = $name;
						$sprintinfo = $agilemantis_sprint->getSprintById();
						
						$convertedDateStart = substr($sprintinfo['start'], 0, 10);
						$convertedDateEnd = substr($sprintinfo['end'], 0, 10);
						$sprint_start_date = explode( '-', $convertedDateStart );
						$sprint_end_date = explode( '-', $convertedDateEnd );
						$sprintinfo['start'] = mktime( 0, 0, 0, $sprint_start_date[1], $sprint_start_date[2], $sprint_start_date[0] );
						$sprintinfo['startdayend'] = mktime( 23, 59, 0, $sprint_start_date[1], $sprint_start_date[2], $sprint_start_date[0] );
						$sprintinfo['end'] = mktime( 23, 59, 0, $sprint_end_date[1], $sprint_end_date[2], $sprint_end_date[0] );
						$sprintinfo['commit'] = strtotime( $sprintinfo['commit'] );
						$sprintinfo['closed'] = strtotime( $sprintinfo['closed'] );
						
						// Startwerte
						$countStories = 0;
						$closedStories = 0;
						$countTasks = 0;
						$closedTasks = 0;
						$countStartTasks = 0;
						$number_of_days = 0;
						$countStorypoints = 0;
						$closedStorypoints = 0;
						$planned_capacity = 0;
						
						if( $sprintinfo['start'] == $sprintinfo['end'] ) {
							$addaday = 86400;
						}
						$first = true;
						if( !empty( $userstories ) ) {
							foreach( $userstories as $num => $row ) {
								
								// Erstelle eine Bugliste
								$bugList .= $row['id'] . ',';
								
								// STORYPOINTS BERECHNUNGEN FÜR DAS BURNDOWN CHART
								$sprint_entry = $agilemantis_userstory->getUserStorySprintHistory( $row['bug_id'] );
								
								if( $row['status'] >= 80 ) {
									$closedStories++;
								}
								$tasked = $agilemantis_sprint->getSprintTasks( $row['id'], 0 );
								
								$countTasks += count( $tasked );
								if( !empty( $tasked ) ) {
									foreach( $tasked as $key => $value ) {
										
										$developer[$value['developer_id']]['planned_capacity'] += $value['planned_capacity'];
										$developer[$value['developer_id']]['performed_capacity'] += $value['performed_capacity'];
										$developer[$value['developer_id']]['rest_capacity'] += $value['rest_capacity'];
										
										if( $value['status'] >= 4 ) {
											$closedTasks++;
										}
										
										$tasklog = $agilemantis_tasks->getTaskLog( $value['id'] );
										if( !empty( $tasklog ) ) {
											foreach( $tasklog as $task => $log ) {
												
												// TASK BERECHNUNGEN FÜR DAS BURNDOWN CHART
												if( $log['event'] == 'created' ) {
													$current_date = strtotime( $log['date'] );
													if( $current_date < $sprintinfo['commit'] && $sprint_entry < $sprintinfo['commit'] ) {
														$countStartTasks++;
													}
													if( $current_date >= $sprintinfo['commit'] ) {
														$current_tasks[$current_date] -= 1;
													}
													if( $current_date < $sprintinfo['commit'] && $sprint_entry >= $sprintinfo['commit'] ) {
														$current_tasks[$sprint_entry] -= 1;
													}
													$taskdate = strtotime( $log['date'] );
												}
												if( $log['event'] == 'closed' ) {
													$current_date = strtotime( $log['date'] );
													$current_tasks[$current_date] += 1;
												} elseif( $log['event'] == 'resolved' ) {
													$current_date = strtotime( $log['date'] );
													$current_tasks[$current_date] += 1;
												}
												if( $log['event'] == 'reopened' ) {
													$current_date = strtotime( $log['date'] );
													$current_tasks[$current_date] -= 1;
												}
											}
										}
										
										if( $sprintinfo['status'] >= 1 ) {
											if( $taskdate <= $sprintinfo['commit'] && $sprint_entry <= $sprintinfo['commit'] ) {
												$planned_capacity += $value['planned_capacity'];
												$planned_capacity_new += $value['planned_capacity'];
											}
										} elseif( $sprintinfo['status'] == 0 ) {
											if( $taskdate <= $sprintinfo['startdayend'] ) {
												$planned_capacity += $value['planned_capacity'];
												$planned_capacity_new += $value['planned_capacity'];
											}
										}
										
										// HOURS BURNDOWN CHART BERECHNUNGEN
										$task_result = $agilemantis_tasks->getDailyPerformance( $value['id'] );
										if( !empty( $task_result ) ) {
											foreach( $task_result as $daily => $capacity ) {
												$date = strtotime( $capacity['date'] );
												if( $sprintinfo['status'] > 0 ) {
													if( $date < $sprintinfo['commit'] && $sprint_entry >= $sprintinfo['commit'] ) {
														$task_array[$value['id']][date( 'd.m.Y', $sprint_entry )] = $capacity['rest'];
													} elseif( $date < $sprintinfo['commit'] && $sprint_entry <= $sprintinfo['commit'] ) {
														$task_array[$value['id']][date( 'd.m.Y', $sprintinfo['start'] )] = $capacity['rest'];
													} else {
														$task_array[$value['id']][date( 'd.m.Y', $date )] = $capacity['rest'];
													}
												} else {
													$task_array[$value['id']][date( 'd.m.Y', $sprintinfo['start'] )] = $capacity['rest'];
												}
											}
										}
										$sprintTask[] = $value['id'];
									}
								}
								
								$addStorypoints = $agilemantis_sprint->checkForUserStory( $row['bug_id'] );
								if( $sprint_entry < $sprintinfo['commit'] ) {
									$storypoints += $addStorypoints['storypoints'];
								}
								
								$changes = $agilemantis_tasks->getUserStoryChanges( $row['bug_id'] );
								if( ($changes[0]['new_value'] == 80 || $changes[0]['new_value'] == 90) && $row['status'] >= 80 && $changes[0]['date_modified'] <= $sprintinfo['end'] ) {
									$storypoints_left[$changes[0]['date_modified']] += $addStorypoints['storypoints'];
								}
								
								if( $sprint_entry > $sprintinfo['commit'] && $sprint_entry <= $sprintinfo['end'] ) {
									$storypoints_left[$sprint_entry] -= $addStorypoints['storypoints'];
								}
							}
						}
						
						for( $i = $sprintinfo['start']; $i <= $sprintinfo['end']; $i += 86400 ) {
							if( $previousDate == date( 'd.m.Y', $i ) ) {
								continue;
							}
							if( isset( $sprintTask ) ) {
								foreach( $sprintTask as $key => $value ) {
									if( $sprintinfo['status'] >= 1 ) {
										if( isset( $task_array[$value][date( 'd.m.Y', $i )] ) ) {
											$last_entry[$value] = $task_array[$value][date( 'd.m.Y', $i )];
											$work_done[date( 'd.m.Y', $i )] += $task_array[$value][date( 'd.m.Y', $i )];
										} else {
											$work_done[date( 'd.m.Y', $i )] += $last_entry[$value];
										}
									} else {
										$work_done[date( 'd.m.Y', $i )] += $task_array[$value][date( 'd.m.Y', $sprintinfo['start'] )];
									}
								}
							}
							$previousDate = date( 'd.m.Y', $i );
						}
						
						if( $work_done[date( 'd.m.Y', $sprintinfo['start'] )] == 0 ) {
							$work_done[date( 'd.m.Y', $sprintinfo['start'] )] = $planned_capacity_new;
						}
						
						for( $i = $sprintinfo['start']; $i <= $sprintinfo['end']; $i += 86400 ) {
							$number_of_days++;
						}
						$number_of_days -= 1;
						
						echo '<charts>';
						
						$bugList = substr( $bugList, 0, -1 );
						
						// Storypoints Burndown Chart
						include_once (AGILEMANTIS_PLUGIN_URI . 'core/chart/burndown/storypoints.php');
						
						// Stunden Burndown Chart
						include_once (AGILEMANTIS_PLUGIN_URI . 'core/chart/burndown/hours.php');
						
						// Task Burndown Chart
						include_once (AGILEMANTIS_PLUGIN_URI . 'core/chart/burndown/tasks.php');
						
						// Allgemeine Statistiken
						include_once (AGILEMANTIS_PLUGIN_URI . 'core/chart/developer_userstory.php');
						
						echo '</charts>';
					}
					break;
				case 'getVelocityData':
					include_once (AGILEMANTIS_PLUGIN_URI . 'core/chart/generate_velocity_data.php');
					break;
				case 'getClosedTeamSprints':
					
					$agilemantis_sprint->sprint_id = $_POST['sprintName'];
					$sprintData = $agilemantis_sprint->getSprintById();
					$team_sprint = $agilemantis_sprint->getLatestSprints( $sprintData['team_id'] );
					echo '<team_sprints>';
					foreach( $team_sprint as $num => $row ) {
						echo '<sprint id="' . $row['id'] . '" name="' . string_display($row['name']) . '" />';
					}
					echo '</team_sprints>';
					break;
				case 'setCookie':
					$buglist = str_replace( '-', ',', $_POST['bugList'] );
					setcookie( $g_cookie_prefix . 'BUG_LIST_COOKIE', $buglist, 0, '/' );
					echo 1;
					break;
				case 'sendEmail':
					include_once ($_SERVER['DOCUMENT_ROOT'] . $subdir . 'library/phpmailer/class.phpmailer.php');
					try {
						$mail = new PHPMailer(); // New instance, with exceptions enabled
						$mail->IsSMTP();
						$mail->Host = $g_smtp_host;
						
						$sender_info = $agilemantis_tasks->getUser( $user_id );
						$mail->From = $sender_info['email'];
						$mail->FromName = $sender_info['realname'];
						$mail->Sender = $sender_info['email'];
						$mail->AddReplyTo( $sender_info['email'], $sender_info['realname'] );
						
						$subject = "";
						
						if( !empty( $_POST['sprint_id'] ) ) {
							$agilemantis_sprint->sprint_id = $_POST['sprint_id'];
							$sprint_info = $agilemantis_sprint->getSprintByName();
							$developer = $agilemantis_team->getScrumTeamMember( $sprint_info['team_id'] );
							if( !empty( $developer ) ) {
								$first = true;
								$usernames = "";
								foreach( $developer as $num => $row ) {
									if( !array_key_exists( $row['user_id'], $user_ids ) && $row['user_id'] != $user_id ) {
										$mail->AddAddress( $row['email'] );
									}
									$user_ids[$row['user_id']] = true;
									if( $first ) {
										$first = false;
									} else {
										$usernames .= ", ";
									}
									$usernames .= $row['username'];
								}
							}
						}
						
						if( !empty( $_POST['userstory_id'] ) ) {
							$userstory_id = $_POST['userstory_id'];
							$task_info = $agilemantis_sprint->getSprintTasks( $userstory_id );
							if( !empty( $task_info ) ) {
								$first = true;
								$usernames = "";
								foreach( $task_info as $num => $row ) {
									if( !array_key_exists( $row['developer_id'], $user_ids ) && $row['developer_id'] != $user_id ) {
										$recipient_info = $agilemantis_tasks->getUser( $row['developer_id'] );
										$mail->AddAddress( $recipient_info['email'] );
										if( $first ) {
											$first = false;
										} else {
											if( !empty( $recipient_info['username'] ) ) {
												$usernames .= ", ";
											}
										}
										$usernames .= $recipient_info['username'];
									}
									$user_ids[$row['developer_id']] = true;
								}
							}
						}
						
						if( !empty( $_POST['task_id'] ) ) {
							$task_id = $_POST['task_id'];
							$task = $agilemantis_tasks->getSelectedTask( $task_id );
							
							$usernames = "";
							$recipient_info = $agilemantis_tasks->getUser( $task['developer_id'] );
							$mail->AddAddress( $recipient_info['email'] );
							$usernames .= $recipient_info['username'];
							
							$userstory_id = $task['us_id'];
							$additional_subject = $task['name'];
						}
						
						if( $userstory_id > 0 ) {
							$agilemantis_team->us_id = $userstory_id;
							$userstory_info = $agilemantis_team->getUserStoryById();
							$subject .= $userstory_info[0]['summary'] . ' - ';
							if( $additional_subject != "" ) {
								$subject .= $additional_subject . ' - ';
							}
						}
						
						if( $_POST['sendToMe'] == 1 ) {
							$mail->AddAddress( $sender_info['email'] );
							$user_ids[$user_id] = true;
							$usernames .= ', ' . $sender_info['username'];
						}
						
// 						// Email Betreff erweitern, wenn Userstory oder Task ausgewählt worden ist
						$subject .= $_POST['subject'];
						$message = $_POST['message'];
						
						$mail->Subject = $subject;
						$mail->Body = $message;
						$mail->AltBody = $message;
						
						$email['subject'] = 'An: ' . $usernames . '<br><br>' . $subject;
						$email['message'] = $message;
						
						if( !$mail->Send() ) {
							echo 'Message was not sent.';
							echo 'Mailer error: ' . $mail->ErrorInfo;
						}
						
						if( $_POST['addNote'] == 1 && $userstory_id > 0 ) {
							$agilemantis_userstory->addBugNote( $userstory_id, $user_id, $email );
						}
					} catch ( phpmailerException $e ) {
						echo $e->errorMessage();
					}
					echo 1;
					break;
				case 'addNote':
					$email['subject'] = $_POST['noticeMessage'];
					$agilemantis_userstory->addBugNote( $_POST['id'], $user_id, $email, $_POST['noticePrivacy'] );
					echo 1;
					break;
				case 'transferTask':
					
					$agilemantis_sprint->sprint_id = $_POST['sprintName'];
					$sprintInfo = $agilemantis_sprint->getSprintById();
					
					$task = $agilemantis_tasks->getSelectedTask( $_POST['id'] );
					
// 					// Allgemein
					$agilemantis_tasks->us_id = $task['us_id'];
					$agilemantis_tasks->name = $task['name'];
					$agilemantis_tasks->description = $task['description'];
					$agilemantis_tasks->daily_scrum = 1;
					
// 					// Alte Task
					$agilemantis_tasks->id = $task['id'];
					$agilemantis_tasks->developer = $task['developer_id'];
					$agilemantis_tasks->status = 4;
					$agilemantis_tasks->planned_capacity = $task['planned_capacity'];
					$agilemantis_tasks->rest_capacity = 0;
					$agilemantis_tasks->addFinishedNote( $agilemantis_tasks->us_id, $agilemantis_tasks->id, $user_id );
					$agilemantis_tasks->editTask();
					$agilemantis_tasks->setDailyScrum( $agilemantis_tasks->id, $agilemantis_tasks->daily_scrum );
					
// 					// Neue Task
					$agilemantis_tasks->id = 0;
					$agilemantis_tasks->us_id = $task['us_id'];
					$agilemantis_tasks->description = $task['description'];
					$agilemantis_tasks->developer = 0;
					$agilemantis_tasks->status = 1;
					if( $sprintInfo['status'] == 0 ) {
						$agilemantis_tasks->planned_capacity = $task['rest_capacity'];
					} else {
						$agilemantis_tasks->planned_capacity = 0;
					}
					$agilemantis_tasks->rest_capacity = $task['rest_capacity'];
					$agilemantis_tasks->capacity -= $agilemantis_tasks->planned_capacity;
					$agilemantis_tasks->editTask();
					$agilemantis_tasks->setDailyScrum( $agilemantis_tasks->id, $agilemantis_tasks->daily_scrum );
					
					echo 1;
					break;
				case 'synchronizeOptionsParameter':
					$agilemantis_tasks->setConfigValue( $_POST['config_id'], $user_id, $_POST['value'] );
					echo 1;
					break;
				case 'synchronizePluginParameter':
					$agilemantis_tasks->setConfigValue( $_POST['config_id'], 0, $_POST['value'] );
					echo 1;
					break;
				case 'getPluginParameter':
					echo config_get( $_POST['config_id'], null, $user_id );
					break;
				case 'revokeUserstory':
					if( $_POST['userstory_id'] > 0 ) {
						$agilemantis_tasks->doUserStoryToSprint( $_POST['userstory_id'], "" );
						echo 1;
					}
					break;
				case 'userDayCapacity':
					$agilemantis_sprint->sprint_id = $_POST['sprintName'];
					$sprintData = $agilemantis_sprint->getSprintById();
					echo $agilemantis_tasks->getUserDayCapacity( $_POST['user'], $sprintData['team_id'] );
					break;
				case 'performedCapacity':
					echo $agilemantis_tasks->getPerformedCapacity( $_POST['task_id'] );
					break;
				case 'userstoryIsTodayAssumed':
					$agilemantis_sprint->sprint_id = urldecode( $_POST['sprintName'] );
					$getSprint = $agilemantis_sprint->getSprintById();
					$sprint_end_date = strtotime( $getSprint['end'] );
					$endDate = mktime( 23, 59, 59, date( 'm', $sprint_end_date ), date( 'd', $sprint_end_date ), date( 'Y', $sprint_end_date ) );
					if( $endDate < mktime() ) {
						$endDate = mktime();
					}
					$userstory = $agilemantis_tasks->getAssumedUserStories( $_POST['userstory_id'], strtotime( $getSprint['commit'] ), $endDate );
					if( $getSprint['status'] == 1 && $_POST['rest_capacity'] == 0.00 && $_POST['status'] < 3 && date( 'dmy', $userstory[0]['date_modified'] ) == date( 'dmy' ) ) {
						echo 1;
					} else {
						echo 0;
					}
					break;
				case 'developerCapacityPerDay':
					$agilemantis_sprint->sprint_id = $_POST['sprintName'];
					$sprintData = $agilemantis_sprint->getSprintById();
					$agilemantis_team->id = $sprintData['team_id'];
					$team_member = $agilemantis_team->getTeamDeveloper();
					$sprint_start = date( 'Y-m-d', strtotime( $sprintData['start'] ) );
					$sprint_end = date( 'Y-m-d', strtotime( $sprintData['end'] ) );
					echo '<developerCapacityPerDay>';
					foreach( $team_member as $num => $row ) {
						$capacity = $agilemantis_av->getUserCapacityByTeam( $sprintData['team_id'], $row['user_id'], $sprint_start, $sprint_end );
						if( $agilemantis_tasks->getUserName( $row['user_id'] ) != "" ) {
							$name = $agilemantis_tasks->getUserName( $row['user_id'] );
						} else {
							$name = "NN";
						}
						echo '<developer name="' . $name . '">';
						foreach( $capacity as $key => $value ) {
							
							$convertedDate = substr($value['date'], 0, 10);
							echo '<date>' . date( 'd.m.Y', strtotime( $convertedDate ) ) . '</date>';
							echo '<capacity>' . $value['capacity'] . '</capacity>';
						}
						echo '</developer>';
					}
					echo '</developerCapacityPerDay>';
					break;
				case 'readFile':
					if( is_file( AGILEMANTIS_LICENSE_PATH ) ) {
						echo file_get_contents( AGILEMANTIS_LICENSE_PATH, FILE_USE_INCLUDE_PATH );
					} else {
						echo 'File Not Found';
					}
					break;
				case 'setSession':
					$agilemantis_au->setExpert( ( int ) $_POST['user'], 1 );
					break;
				default:
			}
		}
	}
}
?>