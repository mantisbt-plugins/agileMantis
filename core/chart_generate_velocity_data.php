<?php

	// choosen Sprint
	$name = $_POST['sprintName'];
	$userstories = $sprint->getSprintStories($name,0,false);
	$sprint->sprint_id = $name;
	$sprintinfo = $sprint->getSprintById();
	
	// get current Sprint velocity data
	$current = $sprint->getVelocityDataFromSprint($userstories);
	// number of team members
	$team->id = $sprintinfo['team_id'];
	$countDeveloper = count($team->getTeamDeveloper());
	
	// get referenced Sprint velocity data
	$referenced = $sprint->getLatestSprints($sprintinfo['team_id'], 1, $_POST['referencedSprint']);
	if($referenced[0]['status'] < 2){
		$userstories = $sprint->getSprintStories($referenced[0]['name'],0,false);
		$referencedSprint = $sprint->getVelocityDataFromSprint($userstories);
		$referenced[0]['storypoints_moved'] = $referencedSprint['storypointsmoved'];
		$referenced[0]['storypoints_in_splitted_user_stories'] = $referencedSprint['workmoved'];
		$referenced[0]['storypoints_sprint'] = $referencedSprint['storypoints'];
		if($referenced[0]['status'] == 0){
			$referenced[0]['workday_length'] = $tasks->getConfigValue('plugin_agileMantis_gadiv_workday_in_hours');
		}
		$countDeveloper = count($team->getTeamDeveloper());
		$referenced[0]['count_developer_team'] = $countDeveloper;
		if(strtotime($referenced[0]['start']) >= mktime()){
			$restCapacity = $sprint->getTeamCapacityInSprint($referenced[0]['team_id'],$referenced[0]['start'], $referenced[0]['end']);
		} else {
			$restCapacity = $sprint->getTeamCapacityInSprint($referenced[0]['team_id'],date('Y-m-d', strtotime('+1 day')), $referenced[0]['end']);
		}		
		$referenced[0]['work_performed'] = $referencedSprint['performed'] + $restCapacity;
		$referenced[0]['total_developer_capacity'] =  $restCapacity;
	}
	
	// get previous Sprint velocity data
	$previous = $sprint->getPreviousSprint($sprintinfo['id'],$sprintinfo['team_id']);
	if($previous[0]['status'] < 2){
		$userstories = $sprint->getSprintStories($previous[0]['name'],0,false);
		$previousSprint = $sprint->getVelocityDataFromSprint($userstories);
		$previous[0]['storypoints_moved'] = $previousSprint['storypointsmoved'];
		$previous[0]['storypoints_in_splitted_user_stories'] = $previousSprint['workmoved'];
		$previous[0]['storypoints_sprint'] = $previousSprint['reststorypoints'];
		$previous[0]['work_performed'] = $previousSprint['performed'];
		if($previous[0]['status'] == 0){
			$previous[0]['workday_length'] = $tasks->getConfigValue('plugin_agileMantis_gadiv_workday_in_hours');
		}
		$previous[0]['count_developer_team'] = $countDeveloper;
		if(strtotime($previous[0]['start']) >= mktime()){
			$restCapacity = $sprint->getTeamCapacityInSprint($previous[0]['team_id'],$previous[0]['start'], $previous[0]['end']);
		} else {
			$restCapacity = $sprint->getTeamCapacityInSprint($previous[0]['team_id'],date('Y-m-d', strtotime('+1 day')), $previous[0]['end']);
		}	
		$previous[0]['work_performed'] = $previousSprint['performed'] + $restCapacity;
		$previous[0]['total_developer_capacity'] = $sprint->getTeamCapacityInSprint($previous[0]['team_id'],$previous[0]['start'], $previous[0]['end']);
	}
	
	// get avarage Sprint velocity data
	$avarage = $sprint->getLatestSprints($sprintinfo['team_id'], $_POST['amountOfSprints']);
	
	if($sprintinfo['status'] < 1){
		$sprintinfo['workday_length'] = $tasks->getConfigValue('plugin_agileMantis_gadiv_workday_in_hours');
	}
	
	$capacity = $sprint->getTeamCapacityInSprint($sprintinfo['team_id'],$sprintinfo['start'], $sprintinfo['end']);
	if(strtotime($sprintinfo['start']) >= mktime()){
		$restCapacity = $sprint->getTeamCapacityInSprint($sprintinfo['team_id'],$sprintinfo['start'], $sprintinfo['end']);
	} else {
		$restCapacity = $sprint->getTeamCapacityInSprint($sprintinfo['team_id'],date('Y-m-d', strtotime('+1 day')), $sprintinfo['end']);
	}
	
	// Storypoints gesamt - Sprint Current
	$storypointsSprintCurrentDayStoryPointsMoved = $current['storypointsmoved'];
	$storypointsSprintCurrentDayWorkMoved = $current['workmoved'] - $storypointsSprintCurrentDayStoryPointsMoved;
	$storypointsSprintCurrentDayRest = $current['reststorypoints'] - $storypointsSprintCurrentDayWorkMoved - $storypointsSprintCurrentDayStoryPointsMoved;

	//Storypoints gesamt - Sprint Total
	$storypointsSprintTotalStoryPointsMoved = $current['storypointsmoved'];
	$storypointsSprintTotalWorkMoved = $current['workmoved'] - $storypointsSprintTotalStoryPointsMoved;
	$storypointsSprintTotalRest = $current['storypoints'] - $storypointsSprintTotalWorkMoved - $storypointsSprintTotalStoryPointsMoved;

	// Storypoints gesamt - Sprint Referenced
	$storypointsSprintReferencedStoryPointsMoved = $referenced[0]['storypoints_moved'];
	$storypointsSprintReferencedWorkMoved = $referenced[0]['storypoints_in_splitted_user_stories'] - $storypointsSprintReferencedStoryPointsMoved;
	$storypointsSprintReferencedRest = $referenced[0]['storypoints_sprint'] - $storypointsSprintReferencedWorkMoved - $storypointsSprintReferencedStoryPointsMoved;

	// Storypoints gesamt -  Sprint Previous
	$storypointsSprintPreviousStoryPointsMoved = $previous[0]['storypoints_moved'];
	$storypointsSprintPreviousWorkMoved = $previous[0]['storypoints_in_splitted_user_stories'] - $storypointsSprintPreviousStoryPointsMoved;
	$storypointsSprintPreviousRest = $previous[0]['storypoints_sprint'] - $storypointsSprintPreviousWorkMoved - $storypointsSprintPreviousStoryPointsMoved;
	
	// Storypoints gesamt - Sprint Avarage
	if(!empty($avarage)){
		foreach($avarage AS $num => $row){
			if($row['status'] < 2){
				$userstories = $sprint->getSprintStories($row['name'],0,false);
				$avgSprint = $sprint->getVelocityDataFromSprint($userstories);
				$row['storypoints_moved'] = $avgSprint['storypointsmoved'];
				$row['storypoints_in_splitted_user_stories'] = $avgSprint['workmoved'];
				$row['storypoints_sprint'] = $avgSprint['storypoints'];
				$row['work_performed'] = $avgSprint['performed'];
				if($row['status'] == 0){
					$row['workday_length'] = $tasks->getConfigValue('plugin_agileMantis_gadiv_workday_in_hours');
				}
				$row['count_developer_team'] = $countDeveloper;
				if(strtotime($row['start']) >= mktime()){
					$restCapacity = $sprint->getTeamCapacityInSprint($row['team_id'],$row['start'], $row['end']);
				} else {
					$restCapacity = $sprint->getTeamCapacityInSprint($row['team_id'],date('Y-m-d', strtotime('+1 day')), $row['end']);
				}	
				$row['total_developer_capacity'] =  $restCapacity;
			}
			$SprintAvarageRest += $row['storypoints_sprint'];
			$SprintAvarageWorkMoved += $row['storypoints_in_splitted_user_stories'];
			$SprintAvarageStoryPointsMoved += $row['storypoints_moved'];
			$SprintAvarageDeveloper += $row['count_developer_team'];
			$SprintAvarageKid += $row['total_developer_capacity'] / $row['workday_length'];
			$SprintAvarageWes += $row['work_performed'] / $row['workday_length'];
			$SprintAvarageWorkPerformed += $row['work_performed'] / $row['workday_length'];
		}
	}
	
	$storypointsSprintAvarageStoryPointsMoved = sprintf("%.2f",($SprintAvarageStoryPointsMoved / $_POST['amountOfSprints']));
	$storypointsSprintAvarageWorkMoved = sprintf("%.2f",($SprintAvarageWorkMoved / $_POST['amountOfSprints']) - $storypointsSprintAvarageStoryPointsMoved);
	$storypointsSprintAvarageRest = sprintf("%.2f",($SprintAvarageRest / $_POST['amountOfSprints']) - $storypointsSprintAvarageWorkMoved - $storypointsSprintAvarageStoryPointsMoved);
	
	$currentSprintKid = $capacity / $sprintinfo['workday_length'];
	$currentSprintWes = ($current['performed'] + $restCapacity) / $sprintinfo['workday_length'];
	
	$kidref = $referenced[0]['total_developer_capacity'] / $referenced[0]['workday_length'];
	$kidpre = $previous[0]['total_developer_capacity'] / $previous[0]['workday_length'];
	
	$wesref = ($referenced[0]['work_performed'] / $referenced[0]['workday_length']);
	$wespre = ($previous[0]['work_performed'] / $previous[0]['workday_length']);
	
	// Storypints Entwickler - Sprint Current
	$developerSprintCurrentDayStoryPointsMoved = sprintf("%.2f",($current['storypointsmoved'] / $countDeveloper));
	$developerSprintCurrentDayWorkMoved = sprintf("%.2f",($current['workmoved'] / $countDeveloper) - $developerSprintCurrentDayStoryPointsMoved);
	$developerSprintCurrentDayRest = sprintf("%.2f",($current['reststorypoints'] / $countDeveloper) - $developerSprintCurrentDayWorkMoved - $developerSprintCurrentDayStoryPointsMoved);

	// Storypoints Entwickler - Sprint Total
	$developerSprintTotalStoryPointsMoved = sprintf("%.2f",($current['storypointsmoved'] / $countDeveloper));
	$developerSprintTotalWorkMoved = sprintf("%.2f",($current['workmoved'] / $countDeveloper) - $developerSprintTotalStoryPointsMoved);
	$developerSprintTotalRest = sprintf("%.2f",($current['storypoints'] / $countDeveloper) - $developerSprintTotalWorkMoved - $developerSprintTotalStoryPointsMoved);

	// Storypoints Entwickler - Sprint Referenced
	$developerSprintReferencedStoryPointsMoved = sprintf("%.2f",($referenced[0]['storypoints_moved'] / $referenced[0]['count_developer_team']));
	$developerSprintReferencedWorkMoved = sprintf("%.2f",($referenced[0]['storypoints_in_splitted_user_stories'] / $referenced[0]['count_developer_team']) - $developerSprintReferencedStoryPointsMoved);
	$developerSprintReferencedRest = sprintf("%.2f",($referenced[0]['storypoints_sprint'] / $referenced[0]['count_developer_team']) - $developerSprintReferencedWorkMoved - $developerSprintReferencedStoryPointsMoved);

	// Storypoints Entwickler - Sprint Previous
	$developerSprintPreviousStoryPointsMoved = sprintf("%.2f",($previous[0]['storypoints_moved'] / $previous[0]['count_developer_team']));
	$developerSprintPreviousWorkMoved = sprintf("%.2f",($previous[0]['storypoints_in_splitted_user_stories'] / $previous[0]['count_developer_team']) - $developerSprintPreviousStoryPointsMoved);
	$developerSprintPreviousRest = sprintf("%.2f",($previous[0]['storypoints_sprint'] / $previous[0]['count_developer_team']) - $developerSprintPreviousWorkMoved - $developerSprintPreviousStoryPointsMoved);

	// Storypoints Entwickler - Sprint Avarage
	$developerSprintAvarageStoryPointsMoved = sprintf("%.2f",($SprintAvarageStoryPointsMoved / $SprintAvarageDeveloper));
	$developerSprintAvarageWorkMoved = sprintf("%.2f",($SprintAvarageWorkMoved / $SprintAvarageDeveloper) - $developerSprintAvarageStoryPointsMoved);
	$developerSprintAvarageRest = sprintf("%.2f",($SprintAvarageRest / $SprintAvarageDeveloper) - $developerSprintAvarageWorkMoved - $developerSprintAvarageStoryPointsMoved);
	
	if($currentSprintKid > 0){
		// Entwickler-Tag - Sprint Current
		$developerDaySprintCurrentDayStoryPointsMoved = sprintf("%.2f",($current['storypointsmoved'] / $currentSprintKid));
		$developerDaySprintCurrentDayWorkMoved = sprintf("%.2f",($current['workmoved'] / $currentSprintKid) - $developerDaySprintCurrentDayStoryPointsMoved);
		$developerDaySprintCurrentDayRest = sprintf("%.2f",($current['reststorypoints'] / $currentSprintKid) - $developerDaySprintCurrentDayWorkMoved - $developerDaySprintCurrentDayStoryPointsMoved);

		// Entwickler-Tag - Sprint Total
		$developerDaySprintTotalStoryPointsMoved = sprintf("%.2f",($current['storypointsmoved'] / $currentSprintKid));
		$developerDaySprintTotalWorkMoved = sprintf("%.2f",($current['workmoved'] / $currentSprintKid) - $developerDaySprintTotalStoryPointsMoved);
		$developerDaySprintTotalRest = sprintf("%.2f",($current['storypoints'] / $currentSprintKid) - $developerDaySprintTotalWorkMoved - $developerDaySprintTotalStoryPointsMoved);
	}

	// Entwickler-Tag - Sprint Referenced
	if($kidref > 0){
		$developerDaySprintReferencedStoryPointsMoved = sprintf("%.2f",($referenced[0]['storypoints_moved'] / $kidref));
		$developerDaySprintReferencedWorkMoved = sprintf("%.2f",($referenced[0]['storypoints_in_splitted_user_stories'] / $kidref) - $developerDaySprintReferencedStoryPointsMoved);
		$developerDaySprintReferencedRest = sprintf("%.2f",($referenced[0]['storypoints_sprint'] / $kidref) - $developerDaySprintReferencedWorkMoved - $developerDaySprintReferencedStoryPointsMoved);
	}

	// Entwickler-Tag - Sprint Previous
	if($kidpre > 0){
		$developerDaySprintPreviousStoryPointsMoved = sprintf("%.2f",($previous[0]['storypoints_moved'] / $kidpre));
		$developerDaySprintPreviousWorkMoved = sprintf("%.2f",($previous[0]['storypoints_in_splitted_user_stories'] / $kidpre) - $developerDaySprintPreviousStoryPointsMoved);
		$developerDaySprintPreviousRest = sprintf("%.2f",($previous[0]['storypoints_sprint'] / $kidpre) - $developerDaySprintPreviousWorkMoved - $developerDaySprintPreviousStoryPointsMoved);
	}

	// Entwickler-Tag - Sprint Avarage
	if($SprintAvarageKid > 0){
		$developerDaySprintAvarageStoryPointsMoved = sprintf("%.2f",($SprintAvarageStoryPointsMoved / $SprintAvarageKid));
		$developerDaySprintAvarageWorkMoved = sprintf("%.2f",($SprintAvarageWorkMoved / $SprintAvarageKid) - $developerDaySprintAvarageStoryPointsMoved);
		$developerDaySprintAvarageRest = sprintf("%.2f",($SprintAvarageRest / $SprintAvarageKid) - $developerDaySprintAvarageWorkMoved - $developerDaySprintAvarageStoryPointsMoved);
	}

	// Aufwands-Tag - Sprint Current
	$capacityDaySprintCurrentDayStoryPointsMoved = sprintf("%.2f",($current['storypointsmoved'] / $currentSprintWes));
	$capacityDaySprintCurrentDayWorkMoved = sprintf("%.2f",($current['workmoved'] / $currentSprintWes) - $capacityDaySprintCurrentDayStoryPointsMoved);
	$capacityDaySprintCurrentDayRest = sprintf("%.2f",($current['reststorypoints'] / $currentSprintWes) - $capacityDaySprintCurrentDayWorkMoved - $capacityDaySprintCurrentDayStoryPointsMoved);

	// Aufwands-Tag - Sprint Total
	$capacityDaySprintTotalStoryPointsMoved = sprintf("%.2f",($current['storypointsmoved'] / $currentSprintWes));
	$capacityDaySprintTotalWorkMoved = sprintf("%.2f",($current['workmoved'] / $currentSprintWes) - $capacityDaySprintTotalStoryPointsMoved);
	$capacityDaySprintTotalRest = sprintf("%.2f",($current['storypoints'] / $currentSprintWes) - $capacityDaySprintTotalWorkMoved - $capacityDaySprintTotalStoryPointsMoved);
	
	// Aufwands-Tag - Sprint Referenced
	if($referenced[0]['work_performed'] > 0){
		$capacityDaySprintReferencedStoryPointsMoved = sprintf("%.2f",($referenced[0]['storypoints_moved'] / $wesref));
		$capacityDaySprintReferencedWorkMoved = sprintf("%.2f",($referenced[0]['storypoints_in_splitted_user_stories'] / $wesref) - $capacityDaySprintReferencedStoryPointsMoved);
		$capacityDaySprintReferencedRest = sprintf("%.2f",($referenced[0]['storypoints_sprint'] / $wesref) - $capacityDaySprintReferencedWorkMoved - $capacityDaySprintReferencedStoryPointsMoved);
	}

	// Aufwands-Tag - Sprint Previous
	if($previous[0]['work_performed'] > 0){
		$capacityDaySprintPreviousStoryPointsMoved = sprintf("%.2f",($previous[0]['storypoints_moved'] / $wespre));
		$capacityDaySprintPreviousWorkMoved = sprintf("%.2f",($previous[0]['storypoints_in_splitted_user_stories'] / $wespre) - $capacityDaySprintPreviousStoryPointsMoved);
		$capacityDaySprintPreviousRest = sprintf("%.2f",($previous[0]['storypoints_sprint'] / $wespre) - $capacityDaySprintPreviousWorkMoved - $capacityDaySprintPreviousStoryPointsMoved);
	}

	// Aufwands-Tag - Sprint Avarage
	if($SprintAvarageWorkPerformed > 0){
		$capacityDaySprintAvarageStoryPointsMoved = sprintf("%.2f",($SprintAvarageStoryPointsMoved / $SprintAvarageWes));
		$capacityDaySprintAvarageWorkMoved = sprintf("%.2f",($SprintAvarageWorkMoved / $SprintAvarageWes) - $capacityDaySprintAvarageStoryPointsMoved);
		$capacityDaySprintAvarageRest = sprintf("%.2f",($SprintAvarageRest / $SprintAvarageWes) - $capacityDaySprintAvarageWorkMoved - $capacityDaySprintAvarageStoryPointsMoved);
	}
	
	echo '<charts>';

	// Storypoints gesamt
	include_once(PLUGIN_URI.'core/chart_velocity_storypoints_total.php');

	// Storypoints Entwickler
	include_once(PLUGIN_URI.'core/chart_velocity_storypoints_developer.php');

	// Storypoints gesamt
	include_once(PLUGIN_URI.'core/chart_velocity_storypoints_developer_day.php');

	// Storypoints gesamt
	include_once(PLUGIN_URI.'core/chart_velocity_storypoints_capacity_day.php');

	// Übersicht Velocity / Kapazität gesamt
	include_once(PLUGIN_URI.'core/chart_velocity_capacity.php');

	echo '</charts>';
?>