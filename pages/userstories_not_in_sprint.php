<? html_page_top("Task testen"); ?>
<?php
	
	function getTasksWithWrontUnit($sprint_name=""){
		$userstory = new gadiv_userstory();
		$sprint = new gadiv_sprint();
		$unit = 1;
		$userstories = $userstory->getUserStoriesWithSpecificSprint($sprint_name);
		// Tasks zu User Stories, die zu keinem Sprint gehören
		if(!empty($userstories)){
			foreach($userstories AS $num => $row){
				$task = $sprint->getSprintTasks($row['bug_id'],0);
				if(!empty($task)){
					foreach($task AS $key => $value){
						if($unit != $value['unit'] && $value['status'] < 4){
							echo "Die Einheit der Task #".$value['id']." stimmt nicht mit der globalen überein! \r\n";
							$task_list[$value['id']] = true;
						}
					}
				}
			}
		}
		return $task_list;
	}
	
	$task_list = getTasksWithWrontUnit();
	// Ermittle neue Sprints
	$new_sprints = $sprint->getNewSprints();
	if(!empty($new_sprints)) {
		foreach($new_sprints AS $num => $row){
			$task_list = getTasksWithWrontUnit($row['name']);
		}
	}
	
	print_r($task_list);
?>
<?php html_page_bottom() ?>