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
	
	error_reporting(NULL);
	$path = realpath(dirname(__FILE__));
	
	// Lade die agileMantis spezifischen Pfade / Variabeln
	include_once($configPath . 'config_api.php');
	
	// Lade die Konfigurationsdatei und stelle die Datenbankverbindung her
	// Zugriff auf die agileMantis Funktionen
	$path = str_replace('plugins' . DIRECTORY_SEPARATOR . 'agileMantis' . DIRECTORY_SEPARATOR . 'core', '', $path);
	include_once($path . 'config_inc.php');
	
	
	
	// Load language stuff
	if($_POST['language'] == 'german'){
		include_once( $path . 'lang' . DIRECTORY_SEPARATOR . 'strings_german.txt' );
		include_once( $path . 'plugins' . DIRECTORY_SEPARATOR . 'agileMantis' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'strings_german.txt' );
	} else {
		include_once( $path . 'lang' . DIRECTORY_SEPARATOR . 'strings_english.txt' );
		include_once( $path . 'plugins' . DIRECTORY_SEPARATOR . 'agileMantis' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'strings_english.txt' );
	}

	$link = mysql_connect($g_hostname, $g_db_username, $g_db_password);
	mysql_select_db($g_database_name);

	if($_POST['timezone']) {
		date_default_timezone_set($_POST['timezone']);
	}
	
	if($_POST['user']){
		$sitekey = $tasks->getConfigValue('plugin_agileMantis_gadiv_sitekey');
		$heute = mktime(0,0,0,date('m'),date('d'),date('y'));
		$generatedKey = md5($sitekey.$heute);
		$user_id = $_POST['user'];

		if($_POST['event'] == 'checkIdentity'){
			if($generatedKey == $_POST['appletkey']){
				$startApplet = 'go';
				echo true;
			} else {
				$startApplet = 'stop';
				echo false;
			}
		}
		
		if($generatedKey == $_POST['appletkey']){
			// Event-Trigger: Schaue nach ob $_POST['event'] gesetzt worden ist
			// und wenn der gesetzt worden ist, Überprüfe ob ein Event vorliegt.
			// Ansonsten nutze den Standardfall!
			if(isset($_POST['event'])){
				switch($_POST['event']){
					case 'loadLicense':
						echo '<licenseData>';
							if(is_file(LICENSE_PATH)){
								$lines = array();
								$fp = fopen(LICENSE_PATH, 'r');
								while (!feof($fp)){
								
									$line = fgets($fp);

									//process line however you like
									$line = trim($line);

									//add to array
									if($line != ""){
										$lines[] = $line;
									}

								}
								fclose($fp);
								if(count($lines) <= 5){ 
									echo '<licenseKey>'.$lines[0].'</licenseKey>';
									echo '<domain>'.$lines[1].'</domain>';
									echo '<organisation>'.$lines[2].'</organisation>';
									echo '<date>'.$lines[3].'</date>';
									echo '<developer>'.$lines[4].'</developer>';
								} else {
									echo '<licenseKey>corrupted</licenseKey>';
								}
								echo '<amtDeveloper>'.$au->countSessions().'</amtDeveloper>';
								echo '<session>'.$tasks->getSession($user_id).'</session>';
							} else {
								echo '<licenseKey>FILE NOT FOUND</licenseKey>';
							}
						echo '</licenseData>';
					break;
					// Lädt alle Sprints aus der Datenbank und generiert eine dynamische XML-Ausgabe
					case 'loadSprint':
						$sprint->sprint_id = $_POST['sprintName'];
						$sprintData = $sprint->getSprintById();
						if($sprintData['status'] == 0){$status = 'Neu';}
						if($sprintData['status'] == 1){$status = 'Laufend';}
						if($sprintData['status'] == 2){$status = 'Geschlossen';}
						$sprint_start_date = explode('-',$sprintData['start']);
						$sprint_end_date = explode('-',$sprintData['end']);
						$sprintData['start'] = mktime(0,0,0,$sprint_start_date[1],$sprint_start_date[2],$sprint_start_date[0]);
						$sprintData['end'] = mktime(0,0,0,$sprint_end_date[1],$sprint_end_date[2],$sprint_end_date[0]);
						echo '<sprint>';
							echo '<id>'.$sprintData['id'].'</id>';
							echo '<name>'.htmlspecialchars($sprintData['name']).'</name>';
							echo '<status>'.$sprintData['status'].'</status>';
							echo '<team>'.$sprint->getTeamById($sprintData['team_id']).'</team>';
							echo '<start>'.date('d.m.Y',$sprintData['start']).'</start>';
							echo '<end>'.date('d.m.Y',$sprintData['end']).'</end>';
						echo '</sprint>';
					break;
					// Lädt alle Userstories mit den entsprechenden Tasks, die zu
					// einer Userstory geh�ren und generiert eine dynamische XML-Ausgabe
					case 'loadUserStory':
						$severity_array = explode(',',$s_severity_enum_string);
						foreach($severity_array AS $num => $row){
							$temp = explode(':',$row);
							$severity[$temp[0]] = $temp[1];
						}
						$status_array = explode(',',$s_status_enum_string);
						foreach($status_array AS $num => $row){
							$temp = explode(':',$row);
							$status[$temp[0]] = $temp[1];
						}
						$sprint->us_id = $_POST['userstory_id'];
						include(PLUGIN_URI.'core/schnittstelle_load_userstory.php');
					break;
					case 'loadUserstories':
						$severity_array = explode(',',$s_severity_enum_string);
						foreach($severity_array AS $num => $row){
							$temp = explode(':',$row);
							$severity[$temp[0]] = $temp[1];
						}
						$status_array = explode(',',$s_status_enum_string);
						foreach($status_array AS $num => $row){
							$temp = explode(':',$row);
							$status[$temp[0]] = $temp[1];
						}
						if(isset($_POST['sprintName']) && !empty($_POST['sprintName'])){
							$name = $_POST['sprintName'];
							$userstories = $sprint->getSprintStories($name, $user_id);
							if(!empty($userstories)){
								echo '<sprintstories>';
								foreach($userstories AS $num => $row){
									$sprint->us_id = $row['id'];
									include(PLUGIN_URI.'core/schnittstelle_load_userstory.php');
								}
								echo '</sprintstories>';
							}
						}
					break;
					// Der Status kann bearbeitet werden
					case 'updateUserstory':
						$id = (int) $_POST['id'];
						if($id > 0){
							$tasks->setConfirmationStatus($id);
							$status = 50;
							if($tasks->hasTasksLeft($id) !=""){
								$tasks->closeUserStory($id,80,$user_id);
								$status = 80;
							}
							echo $status;
						}
					break;
					// Eine Task kann anhand ihrer id bearbeitet werden, wobei
					// zuerst alle Variablen übergeben werden und dabei der neue
					// Rest-Aufwand gebildet wird und anschließend in die Datenbank
					// geschrieben. Es werden die Änderungen an einer Task in Form
					// von generiertem XML ausgegeben.
					case 'editTask':

						// Hole Sprint Informationen
						$usData = $tasks->checkForUserStory($_POST['us_id']);
						$sprint->sprint_id = $usData['sprint'];
						$getSprint = $sprint->getSprintById();

						if($getSprint['status'] == 0){
							$tasks->planned_capacity = str_replace(',','.',$_POST['planned_capacity']);
						}

						if($getSprint['status'] == 1 && $_POST['planned_capacity'] > 0){
							$tasks->planned_capacity = str_replace(',','.',$_POST['planned_capacity']);
						}

						if($getSprint['status'] == 1 && $_POST['planned_capacity'] > 0 && $_POST['id'] == 0){
							$tasks->planned_capacity = 0;
						}

						// Sammle alle Variablen
						$tasks->developer 			= (int) $_POST['developer'];
						$tasks->us_id				= (int) $_POST['us_id'];
						$tasks->id 					= (int) $_POST['id'];
						$tasks->user_id 			= $user_id;
						$tasks->name 				= $_POST['name'];
						$tasks->description 		= $_POST['description'];
						$tasks->performed_capacity	= sprintf("%.2f",str_replace(',','.',$_POST['performed_capacity']));
						$tasks->rest_capacity 		= sprintf("%.2f",str_replace(',','.',$_POST['rest_capacity']) - str_replace(',','.',$_POST['performed_capacity_today']));
						$tasks->status				= $_POST['status'];
						$tasks->daily_scrum			= 0;

						if($_POST['id'] == 0){
							$tasks->unit			=	$_POST['gadiv_task_unit_mode'];
						}

						if($getSprint['status'] == 1 && str_replace(',','.',$_POST['rest_capacity']) != str_replace(',','.',$_POST['old_rest_capacity']) && str_replace(',','.',$_POST['rest_capacity']) > 0){
							$tasks->daily_scrum	= 1;
						}

						if($getSprint['status'] == 1 && str_replace(',','.',$_POST['performed_capacity_today']) != 0){
							$tasks->daily_scrum	= 1;
						}

						if($getSprint['status'] == 1 && str_replace(',','.',$_POST['performed_capacity_today']) != 0 || $_POST['oldstatus'] == $_POST['status']){
							$tasks->daily_scrum	= 1;
						}

						if($getSprint['status'] == 1 && $tasks->id == 0){
							$tasks->daily_scrum	= 1;
						}

						if($getSprint['status'] == 1 && $_POST['oldstatus'] != $_POST['status']){
							$tasks->daily_scrum	= 1;
						}

						$tasks->capacity = str_replace(',','.',$_POST['performed_capacity_today']);

						if(str_replace(',','.',$_POST['rest_capacity']) == str_replace(',','.',$_POST['old_rest_capacity']) && str_replace(',','.',$_POST['performed_capacity_today']) == 0){
							$tasks->capacity = 0;
						}

						if(str_replace(',','.',$_POST['rest_capacity']) > str_replace(',','.',$_POST['old_rest_capacity']) && str_replace(',','.',$_POST['performed_capacity_today']) == 0) {
							$tasks->capacity = 0;
						}

						if(str_replace(',','.',$_POST['rest_capacity']) < str_replace(',','.',$_POST['old_rest_capacity'])) {
							$tasks->capacity = 0;
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

						if($tasks->performed_capacity + str_replace(',','.',$_POST['performed_capacity_today']) < 0){
							$tasks->capacity = 0;
							$tasks->capacity -= $tasks->performed_capacity;
							$tasks->performed_capacity = 0;
							$tasks->rest_capacity = sprintf("%.2f",str_replace(',','.',$_POST['rest_capacity']));
						}

						// STATUS NEU, wenn von ERLEDIGT oder GESCHLOSSEN gewechselt wird
						if($_POST['oldstatus'] > 3 && $tasks->status == 1 && $tasks->performed_capacity == 0){$tasks->status = 1;}

						// STATUS ZUGEWIESEN, wenn von ERLEDIGT oder GESCHLOSSEN gewechselt wird
						if($_POST['oldstatus'] > 3 && $tasks->status == 1 && $tasks->performed_capacity == 0 && $tasks->developer > 0){$tasks->status = 2;}

						// STATUS ZUGEWIESEN, wenn von ERLEDIGT oder GESCHLOSSEN gewechselt wird
						if($_POST['oldstatus'] > 3 && $tasks->status == 3 && $tasks->performed_capacity > 0 && $tasks->developer > 0 && $tasks->rest_capacity == 0){$tasks->status = 4;}

						// STATUS ÜBERNOMMEN, wenn von ERLEDIGT oder GESCHLOSSEN gewechselt wird
						if($_POST['oldstatus'] > 3 && $tasks->performed_capacity > 0 && $tasks->developer == 0 && $tasks->rest_capacity == 0){$tasks->status = 3;}
						
						if($_POST['oldstatus'] == $_POST['status']){

							// STATUS NEU
							if($tasks->status == 0){
								$tasks->status = 1;
							}

							if($tasks->status == 2 && $tasks->developer == 0 && $task->perfomed_capacity == 0){
								$tasks->status = 1;
							}

							// STATUS ZUGEWIESEN
							if($tasks->status < 3 && $tasks->developer > 0 && $tasks->performed_capacity == 0){
								$tasks->status = 2;
							}

							if($tasks->status < 3 && $tasks->developer > 0 && $tasks->rest_capacity > 0){
								$tasks->status = 2;
							}

							if($tasks->status > 3 && $tasks->developer > 0 && $tasks->rest_capacity > 0){
								$tasks->status = 2;
							}

							// STATUS ÜBERNOMMEN
							if($tasks->performed_capacity != 0 && $tasks->planned_capacity > 0 && $tasks->rest_capacity > 0){
								$tasks->status = 3;
							}

							if($tasks->status == 2 && $_POST['performed_capacity_today'] != 0){
								$tasks->status = 3;
							}

							if(str_replace(',','.',$_POST['rest_capacity']) < str_replace(',','.',$_POST['old_rest_capacity']) && $tasks->rest_capacity > 0 && $getSprint['status'] == 1){
								$tasks->status = 3;
							}

							// STATUS ERLEDIGT
							if($tasks->status != 5 && $tasks->performed_capacity > 0 && $tasks->planned_capacity > 0 && $tasks->rest_capacity <= 0){
								$tasks->status = 4;
							}

							if(($tasks->status == 3 || $tasks->status == 2) && $tasks->performed_capacity > 0 && $tasks->rest_capacity <= 0 ){
								$tasks->status = 4;
							}

							if(($tasks->status == 3 || $tasks->status == 2) && $tasks->rest_capacity <= 0.00 && str_replace(',','.',$_POST['performed_capacity_today']) > 0){
								$tasks->status = 4;
							}

							// STATUS GESCHLOSSEN
							if($tasks->status == 5 && $tasks->rest_capacity == 0 && $tasks->planned_capacity > 0 && $tasks->performed_capacity > 0){
								$tasks->status = 5;
							}

						}

						if($_POST['oldstatus'] > 3 && $tasks->status == 1 && $tasks->developer > 0){
							$tasks->status = 2;
						}
						
						if($_POST['oldstatus'] == 5 && $_POST['status'] == 4){
							$tasks->deleteTaskLog($tasks->id,"closed");
						}

						if($_POST['oldstatus'] > 3 && $tasks->status == 1 && $tasks->developer > 0 && $tasks->performed_capacity > 0){
							$tasks->status = 3;
						}

						if($_POST['oldstatus'] > 3 && $tasks->status == 2 && $tasks->developer > 0 && $tasks->performed_capacity > 0){
							$tasks->status = 3;
						}

						// Wenn eine Task neu angelegt wird, setze Rest-Aufwand = Plan-Aufwand und Korrigierter Plan = Rest-Aufwand
						if($_POST['id'] == 0){
							$tasks->rest_capacity  = str_replace(',','.',$_POST['planned_capacity']);
						}

						if($getSprint['status'] == 0){
							$tasks->rest_capacity  = $tasks->planned_capacity;
							if($_POST['id'] > 0){
								$tasks->replacePlannedCapacity($_POST['id']);
							}
						}

						if($tasks->status == 2){
							$userstory->addBugMonitor($tasks->developer,$tasks->us_id);
						}

						// Wenn von Geschlossen oder Erledigt auf Neu oder Zugewiesen gewechselt wird, ändere Tasklog
						if(($_POST['oldstatus'] >= 4) && $tasks->status <= 3){
							$tasks->updateTaskLog($tasks->id , $user_id, "reopened", $date);
							$tasks->addReopenNote($tasks->us_id,$tasks->id,$user_id);
						}

						// Wenn Task-Status übernommen, dann setze Eintrag "übernommen"
						if($tasks->status == 3){
							$tasks->updateTaskLog($tasks->id , $user_id, "confirmed", $date);
						}

						// Wenn Status 4 oder 5, verringere den korrigierten Plan um den Rest-Aufwand und setze Rest-Aufwand auf 0
						if($tasks->status == 4 || $tasks->status == 5){
							$tasks->rest_capacity = 0;
						}

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
						$id = $tasks->editTask();

						if($_POST['oldstatus'] != $tasks->status){
							$tasks->daily_scrum = 1;
						}

						$tasks->setDailyScrum($tasks->id, $tasks->daily_scrum);
						$tasks->id 	= $id;
						$taskInfo 	= $tasks->getSelectedTask($id);
						$created 	= $tasks->getTaskEvent($row['id'],'created');
						$confirmed 	= $tasks->getTaskEvent($row['id'],'confirmed');
						$resolved 	= $tasks->getTaskEvent($row['id'],'resolved');
						$reopened 	= $tasks->getTaskEvent($row['id'],'reopened');
						$closed 	= $tasks->getTaskEvent($row['id'],'closed');
						echo '<task>';
							echo '<id>'.$taskInfo['id'].'</id>';
							echo '<name>'.htmlspecialchars($taskInfo['name']).'</name>';
							echo '<description>'.htmlspecialchars($taskInfo['description']).'</description>';
							echo '<task_daily_scrum>'.$taskInfo['daily_scrum'].'</task_daily_scrum>';
							if($taskInfo['developer_id'] > 0){
							echo '<developer>';
								echo '<dev_id>'.$taskInfo['developer_id'].'</dev_id>';
								echo '<dev_username>'.$tasks->getUserById($taskInfo['developer_id']).'</dev_username>';
								echo '<dev_realname>'.$tasks->getUserRealName($taskInfo['developer_id']).'</dev_realname>';
							echo '</developer>';
							}
							echo '<status>'.$taskInfo['status'].'</status>';
							echo '<planned_capacity>'.$taskInfo['planned_capacity'].'</planned_capacity>';
							echo '<performed_capacity>'.$taskInfo['performed_capacity'].'</performed_capacity>';
							echo '<rest_capacity>'.$taskInfo['rest_capacity'].'</rest_capacity>';
							$create_date = strtotime($created['date']);
							$confirm_date = strtotime($confirmed['date']);
							$resolve_date = strtotime($resolved['date']);
							$close_date = strtotime($closed['date']);
							$reopen_date = strtotime($reopened['date']);
							if($created['user_id'] > 0){
								echo '<task_created>'.$tasks->getUserById($created['user_id']).' / '.$create_date.'</task_created>';
							}
							if($confirmed['user_id'] > 0){
								echo '<task_confirmed>'.$tasks->getUserById($confirmed['user_id']).' / '.$confirm_date.'</task_confirmed>';
							}
							if($resolved['user_id'] > 0){
								echo '<task_resolved>'.$tasks->getUserById($resolved['user_id']).' / '.$resolve_date.'</task_resolved>';
							}
							if($closed['user_id'] > 0){
								echo '<task_closed>'.$tasks->getUserById($closed['user_id']).' / '.$close_date.'</task_closed>';
							}
							if($reopened['user_id'] > 0){
								echo '<task_reopened>'.$tasks->getUserById($reopened['user_id']).' / '.$reopen_date.'</task_reopened>';
							}
						echo '</task>';
					break;
					// Erhalte alle Team-Benutzer von agileMantis, welche
					// innerhalb eines Teams ein bestimmtes Product Backlog
					// - das im ausgewählten Sprint - bearbeiten sollen.
					// Die ermittelten Entwickler werden als dynamisches XML
					// ausgegeben.
					case 'getDevelopers':
						$tasks->us_id = (int) $_POST['userstory_id'];
						$usData = $tasks->checkForUserStory($tasks->us_id);
						if(!empty($usData)){
							$sprint->sprint_id = $usData['sprint'];
							$sprintInfo = $sprint->getSprintById();
							$team->id = $sprintInfo['team_id'];
							$user = $team->getTeamDeveloper();
						}
						if(!empty($user)){
							echo '<developers>';
								foreach($user AS $num => $row){
									if($row['id'] != 0){
										echo '<developer>';
											echo '<id>'.$row['id'].'</id>';
											echo '<username>'.$row['username'].'</username>';
											echo '<realname>'.$row['realname'].'</realname>';
											echo '<capacity>'.$team->getTotalTeamMemberCapacityBySprint($row['id'],$usData['sprint']).'</capacity>';
										echo '</developer>';
									}
								}
							echo '</developers>';
						}
					break;
					// Löscht eine Task anhand der Benutzer-id
					case 'deleteTask':
						$tasks->id = (int) $_POST['id'];
						echo $tasks->deleteTask();
					break;
					case 'setDailyScrum':
						$tasks->id = (int) $_POST['id'];
						$tasks->setDailyScrum($tasks->id,0);
						if($_POST['developer'] == null || $_POST['developer'] == 0){
							$_POST['developer'] = $_POST['user_id'];
						}
						$tasks->updateTaskLog($tasks->id, (int) $_POST['developer'], "daily_scrum", date('Y-m-d'));
						echo 1;
					break;
					// Loggt alle Vorgänge rund um die Tasks.
					case 'logTaskAction':
						$tasks->updateTaskLog($_POST['id'] , $_POST['user'], $_POST['taskAction'], null);
						if($_POST['taskAction']=='confirmed' || $_POST['taskAction']=='reopened'){
							$tasks->deleteTaskLog($tasks->id,'closed');
							$tasks->deleteTaskLog($tasks->id,'resolved');
						}
					break;
					case 'getStatistics':
						if(isset($_POST['sprintName'])){
							$name = $_POST['sprintName'];
							$userstories = $sprint->getSprintStories($name,$user_id, $_POST['only_open_userstories']);
							
							$sprint->sprint_id = $name;
							$sprintinfo = $sprint->getSprintById();
							
							$sprint_start_date = explode('-',$sprintinfo['start']);
							$sprint_end_date = explode('-',$sprintinfo['end']);
							$sprintinfo['start'] = mktime(0,0,0,$sprint_start_date[1],$sprint_start_date[2],$sprint_start_date[0]);
							$sprintinfo['startdayend'] = mktime(23,59,0,$sprint_start_date[1],$sprint_start_date[2],$sprint_start_date[0]);
							$sprintinfo['end'] 	= mktime(23,59,0,$sprint_end_date[1],$sprint_end_date[2],$sprint_end_date[0]);
							$sprintinfo['commit'] = strtotime($sprintinfo['commit']);
							$sprintinfo['closed'] = strtotime($sprintinfo['closed']);
							
							// Startwerte
							$countStories		= 0;
							$closedStories		= 0;
							$countTasks			= 0;
							$closedTasks		= 0;
							$countStartTasks	= 0;
							$number_of_days 	= 0;
							$countStorypoints	= 0;
							$closedStorypoints	= 0;
							$planned_capacity 	= 0;

							if($sprintinfo['start'] == $sprintinfo['end']){
								$addaday = 86400;
							}
							$first = true;
							if(!empty($userstories)){
								foreach($userstories AS $num => $row){

									// Erstelle eine Bugliste
									$bugList .= $row['id'].',';

									// STORYPOINTS BERECHNUNGEN FÜR DAS BURNDOWN CHART
									$sprint_entry = $userstory->getUserStorySprintHistory($row['bug_id']);

									if($row['status'] >= 80 ){$closedStories++;}
									$tasked = $sprint->getSprintTasks($row['id'],0);
									
									$countTasks += count($tasked);
									if(!empty($tasked)){
										foreach($tasked AS $key => $value){

											$developer[$value['developer_id']]['planned_capacity'] += $value['planned_capacity'];
											$developer[$value['developer_id']]['performed_capacity'] += $value['performed_capacity'];
											$developer[$value['developer_id']]['rest_capacity'] += $value['rest_capacity'];

											if($value['status'] >= 4){$closedTasks++;}
											
											$tasklog = $tasks->getTaskLog($value['id']);
											if(!empty($tasklog)){
												foreach($tasklog AS $task => $log){

													// TASK BERECHNUNGEN FÜR DAS BURNDOWN CHART
													if($log['event'] == 'created'){
														$current_date = strtotime($log['date']);
														if($current_date < $sprintinfo['commit'] && $sprint_entry < $sprintinfo['commit']){
															$countStartTasks++;
														}
														if($current_date >= $sprintinfo['commit']){
															$current_tasks[$current_date] -= 1;
														}
														if($current_date < $sprintinfo['commit'] && $sprint_entry >= $sprintinfo['commit']){
															$current_tasks[$sprint_entry] -= 1;
														}
														$taskdate = strtotime($log['date']);

													}
													if($log['event'] == 'closed'){
														$current_date = strtotime($log['date']);
														$current_tasks[$current_date] += 1;
													} elseif($log['event'] == 'resolved') {
														$current_date = strtotime($log['date']);
														$current_tasks[$current_date] += 1;
													}
													if($log['event'] == 'reopened'){
														$current_date = strtotime($log['date']);
														$current_tasks[$current_date] -= 1;
													}
												}
											}
											
											if($sprintinfo['status'] >= 1){
												if($taskdate <= $sprintinfo['commit'] && $sprint_entry <= $sprintinfo['commit']){
													$planned_capacity += $value['planned_capacity'];
													$planned_capacity_new += $value['planned_capacity'];
												}
											} elseif($sprintinfo['status'] == 0){
												if($taskdate <= $sprintinfo['startdayend']){
													$planned_capacity += $value['planned_capacity'];
													$planned_capacity_new += $value['planned_capacity'];
												}
											}

											// HOURS BURNDOWN CHART BERECHNUNGEN
											$task_result = $tasks->getDailyPerformance($value['id']);
											if(!empty($task_result)){
												foreach($task_result AS $daily => $capacity){
													$date = strtotime($capacity['date']);
													if($sprintinfo['status'] > 0){
														if($date < $sprintinfo['commit'] && $sprint_entry >= $sprintinfo['commit']){
															$task_array[$value['id']][date('d.m.Y',$sprint_entry)] = $capacity['rest'];
														} elseif($date < $sprintinfo['commit'] && $sprint_entry <= $sprintinfo['commit']) {
															$task_array[$value['id']][date('d.m.Y',$sprintinfo['start'])] = $capacity['rest'];
														} else {
															$task_array[$value['id']][date('d.m.Y',$date)] = $capacity['rest'];
														}
													} else {
														$task_array[$value['id']][date('d.m.Y',$sprintinfo['start'])] = $capacity['rest'];
													}
												}
											}
											$sprintTask[] = $value['id'];
										}
									}
								
									$addStorypoints = $sprint->checkForUserStory($row['bug_id']);
									if($sprint_entry < $sprintinfo['commit']){
										$storypoints += $addStorypoints['storypoints'];
									}

									$changes = $tasks->getUserStoryChanges($row['bug_id']);
									if(($changes[0]['new_value'] == 80 || $changes[0]['new_value'] == 90) && $row['status'] >= 80 && $changes[0]['date_modified'] <= $sprintinfo['end']){
										$storypoints_left[$changes[0]['date_modified']] += $addStorypoints['storypoints'];
									}

									if($sprint_entry > $sprintinfo['commit'] && $sprint_entry <= $sprintinfo['end']){
										$storypoints_left[$sprint_entry] -= $addStorypoints['storypoints'];
									}
								}
							}
							
							for($i = $sprintinfo['start']; $i <= $sprintinfo['end'];$i+=86400){
								if($previousDate == date('d.m.Y',$i)){
									continue;
								}
								foreach($sprintTask AS $key => $value){
									if($sprintinfo['status'] >= 1){
										if(isset($task_array[$value][date('d.m.Y',$i)])){
											$last_entry[$value] = $task_array[$value][date('d.m.Y',$i)];
											$work_done[date('d.m.Y',$i)] += $task_array[$value][date('d.m.Y',$i)];
										} else {
											$work_done[date('d.m.Y',$i)] += $last_entry[$value];
										}
									} else {
										$work_done[date('d.m.Y',$i)] += $task_array[$value][date('d.m.Y',$sprintinfo['start'])];
									}
								}
								$previousDate = date('d.m.Y',$i);
							}

							if($work_done[date('d.m.Y',$sprintinfo['start'])] == 0){
								$work_done[date('d.m.Y',$sprintinfo['start'])] = $planned_capacity_new;
							}

							for($i = $sprintinfo['start']; $i <= $sprintinfo['end'];$i+=86400){
								$number_of_days++;
							}
							$number_of_days -= 1;

							echo '<charts>';


							$bugList = substr($bugList, 0,-1);

							// Storypoints Burndown Chart
							include_once(PLUGIN_URI.'core/chart_burndown_storypoints.php');

							// Stunden Burndown Chart
							include_once(PLUGIN_URI.'core/chart_burndown_hours.php');

							// Task Burndown Chart
							include_once(PLUGIN_URI.'core/chart_burndown_tasks.php');

							// Allgemeine Statistiken
							include_once(PLUGIN_URI.'core/chart_developer_userstory.php');

							echo '</charts>';
						}
					break;
					case 'getVelocityData':
						include_once(PLUGIN_URI.'core/chart_generate_velocity_data.php');
					break;
					case 'getClosedTeamSprints':
						$sprint->sprint_id = $_POST['sprintName'];
						$sprintData = $sprint->getSprintById();
						$team_sprint = $sprint->getLatestSprints($sprintData['team_id']);
						echo '<team_sprints>';
						foreach($team_sprint AS $num => $row){
							echo '<sprint id="'.$row['id'].'" name="'.$row['name'].'" />';
						}
						echo '</team_sprints>';
					break;
					case 'setCookie':
						$buglist = str_replace('-',',',$_POST['bugList']);
						setcookie( 'MANTIS_BUG_LIST_COOKIE', $buglist, 0, '/');
						echo 1;
					break;
					case 'sendEmail':
						include_once($_SERVER['DOCUMENT_ROOT'].$subdir.'library/phpmailer/class.phpmailer.php');
						try {
							$mail = new PHPMailer(); //New instance, with exceptions enabled
							$mail->IsSMTP();
							$mail->Host       = $g_smtp_host;

							$sender_info 	  = $tasks->getDeveloperDataById($user_id);
							$mail->From       = $sender_info['email'];
							$mail->FromName   = $sender_info['realname'];
							$mail->Sender	  = $sender_info['email'];
							$mail->AddReplyTo($sender_info['email'],$sender_info['realname']);

							$subject = "";

							if(!empty($_POST['sprint_id'])){
								$sprint->sprint_id = $_POST['sprint_id'];
								$sprint_info = $sprint->getSprintByName();
								$developer = $team->getScrumTeamMember($sprint_info['team_id']);
								if(!empty($developer)){
									$first = true;
									$usernames = "";
									foreach($developer AS $num => $row){
										if(!array_key_exists($row['user_id'],$user_ids) && $row['user_id'] != $user_id){
											$mail->AddAddress($row['email']);
										}
										$user_ids[$row['user_id']] = true;
										if ($first) {
											$first = false;
										} else {
											$usernames .= ", ";
										}
										$usernames .= $row['username'];
									}
								}
							}

							if(!empty($_POST['userstory_id'])){
								$userstory_id = $_POST['userstory_id'];
								$task_info = $sprint->getSprintTasks($userstory_id);
								if(!empty($task_info)){
									$first = true;
									$usernames = "";
									foreach($task_info AS $num => $row){
										if(!array_key_exists($row['developer_id'],$user_ids) && $row['developer_id'] != $user_id){
											$recipient_info = $tasks->getDeveloperDataById($row['developer_id']);
											$mail->AddAddress($recipient_info['email']);
											if ($first) {
												$first = false;
											} else {
												if(!empty($recipient_info['username'])){
													$usernames .= ", ";
												}
											}
											$usernames .= $recipient_info['username'];
										}
										$user_ids[$row['developer_id']] = true;
									}
								}
							}

							if(!empty($_POST['task_id'])){
								$task_id = $_POST['task_id'];
								$task = $tasks->getSelectedTask($task_id);

								$usernames = "";
								$recipient_info = $tasks->getDeveloperDataById($task['developer_id']);
								$mail->AddAddress($recipient_info['email']);
								$usernames .= $recipient_info['username'];

								 $userstory_id = $task['us_id'];
								 $additional_subject = $task['name'];
							}

							if($userstory_id > 0){
								$team->us_id = $userstory_id;
								$userstory_info = $team->getUserStoryById();
								$subject 		.=  $userstory_info[0]['summary'] . ' - ';
								if($additional_subject != ""){
									$subject 		.=  $additional_subject. ' - ';
								}
							}

							if($_POST['sendToMe'] == 1){
								$mail->AddAddress($sender_info['email']);
								$user_ids[$user_id] = true;
								$usernames .= ', '.$sender_info['username'];
							}


							// Email Betreff erweitern, wenn Userstory oder Task ausgewählt worden ist
							$subject .= $_POST['subject'];
							$message = $_POST['message'];

							$mail->Subject  =	$subject;
							$mail->Body		= 	$message;
							$mail->AltBody	=	$message;

							$email['subject'] = 'An: '.$usernames. '<br><br>' . $subject;
							$email['message'] = $message;

							if(!$mail->Send()) {
								echo 'Message was not sent.';
								echo 'Mailer error: ' . $mail->ErrorInfo;
							}

							if($_POST['addNote'] == 1 && $userstory_id > 0){
								$userstory->addBugNote($userstory_id, $user_id,$email);
							}
						} catch (phpmailerException $e) {
							echo $e->errorMessage();
						}
						echo 1;
					break;
					case 'addNote':
						$email['subject'] = $_POST['noticeMessage'];
						$userstory->addBugNote($_POST['id'], $user_id,$email,$_POST['noticePrivacy']);
						echo 1;
					break;
					case 'transferTask':

							$sprint->sprint_id = $_POST['sprintName'];
							$sprintInfo = $sprint->getSprintById();

							$task = $tasks->getSelectedTask($_POST['id']);

							// Allgemein
							$tasks->us_id 				= 	$task['us_id'];
							$tasks->name 				=	$task['name'];
							$tasks->description 		= 	$task['description'];
							$tasks->daily_scrum			= 	1;

							// Alte Task
							$tasks->id 					= 	$task['id'];
							$tasks->developer			= 	$task['developer_id'];
							$tasks->status				= 	4;
							$tasks->planned_capacity 	= 	$task['planned_capacity'];
							$tasks->rest_capacity 		= 	0;
							$tasks->addStatusNote($tasks->us_id,$tasks->id,$user_id);
							$tasks->editTask();
							$tasks->setDailyScrum($tasks->id, $tasks->daily_scrum);

							// Neue Task
							$tasks->id 					= 	0;
							$tasks->us_id 				= 	$task['us_id'];
							$tasks->description 		= 	$task['description'];
							$tasks->developer			= 	0;
							$tasks->status				= 	1;
							if($sprintInfo['status'] ==  0){
								$tasks->planned_capacity 	= 	$task['rest_capacity'];
							} else {
								$tasks->planned_capacity 	= 	0;
							}
							$tasks->rest_capacity		= 	$task['rest_capacity'];
							$tasks->capacity 		   -= $tasks->planned_capacity;
							$tasks->editTask();
							$tasks->setDailyScrum($tasks->id, $tasks->daily_scrum);

						echo 1;
					break;
					case 'synchronizeOptionsParameter':
						$tasks->setConfigValue($_POST['config_id'],$user_id,$_POST['value']);
						echo 1;
					break;
					case 'synchronizePluginParameter':
						$tasks->setConfigValue($_POST['config_id'],0,$_POST['value']);
						echo 1;
					break;
					case 'revokeUserstory':
						if($_POST['userstory_id'] > 0){
							$tasks->doUserStoryToSprint($_POST['userstory_id'],"");
							echo 1;
						}
					break;
					case 'userDayCapacity':
						$sprint->sprint_id = $_POST['sprintName'];
						$sprintData = $sprint->getSprintById();
						echo $tasks->getUserDayCapacity($_POST['user'], $sprintData['team_id']);
					break;
					case 'performedCapacity':
						echo $tasks->getPerformedCapacity($_POST['task_id']);
					break;
					case 'userstoryIsTodayAssumed':
						$sprint->sprint_id = urldecode($_POST['sprintName']);
						$getSprint = $sprint->getSprintById();
						$sprint_end_date = strtotime($getSprint['end']);
						$endDate = mktime(23,59,59,date('m',$sprint_end_date), date('d',$sprint_end_date), date('Y',$sprint_end_date));
						if($endDate < mktime()){
							$endDate = mktime();
						}
						$userstory = $tasks->getAssumedUserStories($_POST['userstory_id'], strtotime($getSprint['commit']),$endDate);
						if($getSprint['status'] == 1 && $_POST['rest_capacity'] == 0.00 && $_POST['status'] < 3 && date('dmy',$userstory[0]['date_modified']) == date('dmy')){
							echo 1;
						} else {
							echo 0;
						}
					break;
					case 'developerCapacityPerDay':
						$sprint->sprint_id = $_POST['sprintName'];
						$sprintData = $sprint->getSprintById();
						$team->id = $sprintData['team_id'];
						$team_member = $team->getTeamDeveloper();
						$sprint_start = date('Y-m-d',strtotime($sprintData['start']));
						$sprint_end = date('Y-m-d',strtotime($sprintData['end']));
						echo '<developerCapacityPerDay>';
						foreach ($team_member AS $num => $row){
							$capacity = $av->getUserCapacityByTeam($sprintData['team_id'], $row['user_id'],$sprint_start, $sprint_end);
							if($tasks->getDeveloperById($row['user_id']) != ""){
								$name = $tasks->getDeveloperById($row['user_id']);
							} else {
								$name = "NN";
							}
							echo '<developer name="'.$name.'">';
							foreach($capacity AS $key => $value){

								echo '<date>'.date('d.m.Y',strtotime($value['date'])).'</date>';
								echo '<capacity>'.$value['capacity'].'</capacity>';
							}
							echo '</developer>';
						}
						echo '</developerCapacityPerDay>';
					break;
					case 'readFile';
						if(is_file(LICENSE_PATH)){	
							echo file_get_contents(LICENSE_PATH,FILE_USE_INCLUDE_PATH);
						} else {
							echo 'File Not Found';
						}
					break;
					case 'setSession':
						$au->setExpert((int) $_POST['user'],1);
					break;
					default:
				}
			}
		}
	}
?>