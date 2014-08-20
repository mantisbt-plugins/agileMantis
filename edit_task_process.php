<?php
	if($_POST['action']=="edit"){

		// Wandle alle Komma in Punkt um
		$_POST['performed_capacity_today'] 	= str_replace(',','.',$_POST['performed_capacity_today']);
		
		// Sammle alle Variablen
		$tasks->developer 			= (int) $_POST['developer'];
		$tasks->us_id				= (int) $_POST['us_id'];
		$tasks->id 					= (int) $_POST['id'];
		$tasks->user_id 			= (int) auth_get_current_user_id();
		$tasks->name 				= $_POST['name'];
		$tasks->description 		= $_POST['description'];
		$tasks->planned_capacity	= str_replace(',','.',$_POST['planned_capacity']);
		$tasks->performed_capacity	= str_replace(',','.',$_POST['performed_capacity']);
		$tasks->plan_correction 	= str_replace(',','.',$_POST['plan_correction']);
		$tasks->capacity			= str_replace(',','.',$_POST['performed_capacity_today']);
		$tasks->rest_capacity 		= $_POST['rest_capacity'] - $_POST['performed_capacity_today'];
		$tasks->corrected_plan 		= $_POST['corrected_plan'] + $tasks->plan_correction;
		$tasks->status				= $_POST['status'];
		
		// Aktuelles Tagesdatum
		$date 						= date('Y').'-'.date('m').'-'.date('d');
		
		// Überprüfe, ob Task eine Bezeichnung hat
		if($_POST['name'] == ""){
			$system = "Bitte geben Sie eine Bezeichnung für die Task an!";
		}
		
		// Überprüfe, ob die Plan-Korrektur eine Zahl ist
		if(!is_double($_POST['plan_correction']) && $_POST['planned_capacity'] > 0.00 && $system == ''){
			$system = "Bitte geben Sie eine Zahl für die Plan-Korrektur an!";
		}	
		
		// Überprüfe, ob der erbrachte Aufwand eine Zahl ist
		if(!is_double($_POST['performed_capacity_today']) && $system == ''){
			$system = "Bitte geben Sie eine Zahl für den 'erbrachten Aufwand' an!";
		}
		
		// Überprüfe, ob korrigierter Plan - Plan-Korrektur größer als 0 ist
		if(($_POST['corrected_plan'] + $_POST['plan_correction']) < 0 && $system == ''){
			$system = "Der korrigerte Plan konnte nicht geändert werden, weil dieser sonst negativ werden würde";
		}
		
		// Überprüfe, ob die Plan-Korrektur + Rest-Aufwand größer als 0 ist
		if($tasks->plan_correction + $tasks->rest_capacity < 0 && $system == ''){
			$system = 'Der eingetragene Aufwand kann nicht gespeichert werden.<br>Der Rest-Aufwand kann nicht negativ werden!';
		}
		
		// Überprüfe, ob der erbrachte Aufwand + Summe Aufwand größer als der korrigierte Plan ist
		if(($_POST['performed_capacity_today'] + $tasks->performed_capacity) > $tasks->corrected_plan && $system == ''){
			$system = 'Der eingetragene Aufwand kann nicht gespeichert werden.<br>Der Plan-Aufwand muss erweitert werden!';
		}
		
		// Überprüfe, ob der Rest-Aufwand - erbrachter Aufwand nicht größer als der korrigierte Plan ist
		if(($tasks->rest_capacity - $_POST['performed_capacity_today']) > $tasks->corrected_plan && $system == ''){
			$system = 'Der eingetragene Aufwand kann nicht gespeichert werden.<br>Der Rest-Aufwand kann nicht größer als der korrigierte Plan sein!';
		}
		
		// Überprüfe, ob Summe Aufwand + erbrachter Aufwand größer als 0 ist
		if(($tasks->performed_capacity + $_POST['performed_capacity_today']) < 0 && $system == ''){
			$system = 'Der eingetragene Aufwand kann nicht gespeichert werden.<br>Der erbrachte Aufwand kann nicht negativ sein!';
		}

		if($tasks->developer > 0 && $tasks->status < 3 && $system == '' && $tasks->getDeveloperSprintCapacity($_POST['developer'],$_POST['us_id'],$_POST['planned_capacity']) == 0){
			$developer_has_no_capacities = true;
		}
		
		if($tasks->developer > 0 && $tasks->status < 4 && $_POST['id'] > 0 && $_POST['plan_correction'] == '' && $system == '' && $tasks->getDeveloperSprintCapacity($_POST['developer'],$_POST['us_id'],$_POST['corrected_plan']) == 0 ){
			$developer_has_no_capacities = true;
		}
		
		if($tasks->developer > 0 && $tasks->status < 5 && $_POST['id'] > 0 && $_POST['plan_correction'] > 0.00 && $system == '' && $tasks->getDeveloperSprintCapacity($_POST['developer'],$_POST['us_id'],$_POST['plan_correction']) == 0){
			$developer_has_no_capacities = true;
		}
		
		if($_POST['sprintName'] != '' && $_POST['id'] == 0 && $system == ""){
			$userstories = $sprint->getSprintStories($_POST['sprintName']);
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

		if($system == ""){
			
			// Wenn eine Task neu angelegt wird, setze Rest-Aufwand = Plan-Aufwand und Korrigierter Plan = Rest-Aufwand
			if($_POST['id'] == 0){
				$tasks->rest_capacity  = $tasks->planned_capacity;
				$tasks->corrected_plan = $tasks->planned_capacity;
			}
			
			if($tasks->plan_correction != 0){
				$tasks->corrected_plan = $tasks->corrected_plan + $tasks->plan_correction;
				$tasks->rest_capacity = $tasks->rest_capacity + $tasks->plan_correction;
				$tasks->capacity -= $tasks->plan_correction;
				$tasks->saveDailyPerformance(0);
			}
			
			if($tasks->plan_correction + $tasks->rest_capacity == 0){
				$tasks->status = 4;
			}
			
			if($tasks->status == 2){
				$tasks->addBugMonitor($tasks->developer,$tasks->us_id);
			}
			
			// Wenn von Geschlossen oder Erledigt auf Neu oder Zugewiesen gewechselt wird, ändere Tasklog	
			if(($_POST['oldstatus'] == 4 || $_POST['oldstatus'] == 5) && $tasks->status < 3){
				$tasks->updateTaskLog($tasks->id , $user_id, "reopened", $date);
				$tasks->addReopenNote($tasks->us_id,$tasks->id,$user_id);
			}
			
			// Wenn Task-Status übernommen, dann setze Eintrag "übernommen"
			if($tasks->status == 3){
				$tasks->updateTaskLog($tasks->id , $user_id, "confirmed", $date);
				$tasks->deleteTaskLog($tasks->id,'closed');
				$tasks->deleteTaskLog($tasks->id,'resolved');
			}
			
			// Wenn Status 4 oder 5, verringere den korrigierten Plan um den Rest-Aufwand und setze Rest-Aufwand auf 0
			if($tasks->status == 4 || $tasks->status == 5){
				$tasks->corrected_plan -= $tasks->rest_capacity;
				$tasks->rest_capacity = 0;
			}
			
			if($tasks->status == 4){
				$tasks->updateTaskLog($tasks->id , $user_id, "resolved", $date);
				$tasks->addStatusNote($tasks->us_id,$tasks->id,$user_id);
				$task_resolved = true;
			}
		
			if($tasks->status == 5){
				$tasks->updateTaskLog($tasks->id , $user_id, "closed", $date);
				$task_closed = true;
			}
			
			$tasks->editTask();
			
			$tasks->setConfirmationStatus($tasks->us_id);
			if($tasks->hasTasksLeft($tasks->us_id) != ""){
				$tasks->closeUserStory($tasks->us_id,80,$user_id);
			}

			// Wenn Tasks bereits einen Aufwand geplant haben, gebe eine Meldung aus
			if($tasks_have_been_planned == true){
				$addlink = '&warning=1';
			}
			
			// Wenn gewählter Entwickler keine Kapazitäten mehr zur Verfügung hat, dann gebe Meldung aus
			if($developer_has_no_capacities == true){
				$addlink = '&warning=2';
			}
			
			// Wenn Task erledigt, dann gebe Meldung "erledigt" aus
			if($task_resolved == true){
				$addlink = '&warning=3';
			}
			
			// Wenn Task geschlossen, dann gebe Meldung "geschlossen" aus
			if($task_closed == true){
				$addlink = '&warning=4';
			}
			
			if($_POST['fromSprintBacklog']) {
				header("Location: ".plugin_page('sprint_backlog.php')."&sprintName=".urlencode($_POST['sprintName']).$addlink);
			} 
			
			if($_POST['fromTaskboard']) {
				header("Location: ".plugin_page('taskboard.php')."&sprintName=".urlencode($_POST['sprintName']).$addlink);
			} 
			
			if(empty($_POST['fromSprintBacklog']) && empty($_POST['fromTaskboard'])){
				header("Location: ".plugin_page('task_page.php&us_id='.$tasks->us_id).$addlink);
			}
		}
	}
	
	$usData 	= $tasks->checkForUserStory($tasks->us_id);
	$usSumText 	= $tasks->getUserStoryById();
	$sprint->sprint_id = $usData['sprint'];
	$getSprint = $sprint->getSprintById();
?>