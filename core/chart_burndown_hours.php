<?php
if($_POST['taskUnit'] == 'T'){
	$multiplier = $_POST['workdayInHours'];
} else {
	$multiplier = 1;
}

echo '<HoursBurnDown>';
	echo '<ideal_burndown>';

		# WP(S)
		$workPlanned = $planned_capacity_new * $multiplier;
		
		# WPz(S)
		$userstories = $tasks->getAssumedUserStories($bugList, $sprintinfo['start'], $sprintinfo['end']);
		if($userstories){
			foreach($userstories AS $story => $task){
				$tasked = $sprint->getSprintTasks($task['bug_id'],0);
				if(!empty($tasked) && $task['date_modified'] >= $sprintinfo['commit']){
					foreach($tasked AS $num => $row){
						$tasklog = $tasks->getTaskLog($row['id']);
						$date = strtotime($tasklog[0]['date']);
						$capacity = $tasks->getDailyPerformance($row['id']);
						if($date <= $end){
							$additionalCapacity += $capacity[0]['rest'] * $multiplier;
						}
					}
				}
			}
		}
		
		# WPr(S)
		for($i = $sprintinfo['start']; $i <= $sprintinfo['end'];$i+=86400){
			$additionalCapacity -= $userstory->getWorkMovedFromSplittedStories($bugList, date('Y-m-d',$i)) * $multiplier;
		}
		
		# K(S)
		$sprintCapacity = $av->getTeamCapacity($sprintinfo['team_id'],date('Y-m-d',$sprintinfo['start']),date('Y-m-d',$sprintinfo['end']));

		# VPK(S) = (WP(S) + WPz(S) - WPr(S)) / K(S)
		$vpks = ($workPlanned - $additionalCapacity) / $sprintCapacity;
		echo '<entry date="'.date('d.m.Y H:i',$sprintinfo['start']).'" value="'.$planned_capacity_new * $multiplier.'"></entry>';
		for($i = $sprintinfo['start']; $i <= $sprintinfo['end']+86340;$i+=86400){
			echo '<entry date="'.date('d.m.Y H:i',$i).'" value="'.$workPlanned.'"></entry>';
			if($workPlanned - $av->getTeamCapacity($sprintinfo['team_id'], date('Y-m-d',$i), date('Y-m-d',$i)) * $vpks < 0){
				$workPlanned = 0;
			} else {
				$workPlanned -= $av->getTeamCapacity($sprintinfo['team_id'], date('Y-m-d',$i), date('Y-m-d',$i)) * $vpks;
			}
		}
	echo '</ideal_burndown>';
	echo '<actual_burndown>';
		echo '<entry date="'.date('d.m.Y H:i',$sprintinfo['start']).'" value="'.$planned_capacity_new * $multiplier.'"></entry>';
		foreach($work_done AS $key => $value){
			$date = strtotime($key);
			if($sprintinfo['end'] >= mktime()){
				if($date <= mktime() && $date >= $sprintinfo['start'] && $date <= $sprintinfo['end'] + 86400){
					echo '<entry date="'.$key.' 23:59" value="'.$value * $multiplier.'"></entry>';
				}
			} else {
				echo '<entry date="'.$key.' 23:59" value="'.$value * $multiplier.'"></entry>';
			}
		}
	echo '</actual_burndown>';
	echo '<capacity>';
		$start_hours = $av->getTeamCapacity($sprintinfo['team_id'],date('Y-m-d',$sprintinfo['start']),date('Y-m-d',$sprintinfo['end']));
		echo '<entry date="'.date('d.m.Y H:i',$sprintinfo['start']).'" value="'.$start_hours.'"></entry>';
		for($i = $sprintinfo['start']; $i <= $sprintinfo['end']+86340;$i+=86400){
			if($previousDate == date('d.m.Y',$i)){
				$i += 3600;
			}
			echo '<entry date="'.date('d.m.Y H:i', $i).'" value="'.$start_hours.'"></entry>';
			if($start_hours - $av->getTeamCapacity($sprintinfo['team_id'], date('Y-m-d',$i), date('Y-m-d',$i)) > 0){
				$start_hours -= $av->getTeamCapacity($sprintinfo['team_id'], date('Y-m-d',$i), date('Y-m-d',$i));
			} else {
				$start_hours = 0;
			}
			$previousDate = date('d.m.Y',$i);
		}
	echo '</capacity>';
	echo '<optimal_burndown>';
		$start_capacity = $planned_capacity_new * $multiplier;
		echo '<entry date="'.date('d.m.Y H:i',$sprintinfo['start']).'" value="'.$start_capacity.'"></entry>';
		for($i = $sprintinfo['start']; $i <= $sprintinfo['end']+86340;$i+=86400){
			
			if($i == $sprintinfo['start']){
				$start = mktime(date('H',$sprintinfo['commit']),date('i',$sprintinfo['commit']),date('s',$sprintinfo['commit']),date('m',$i), date('d',$i), date('Y',$i));
				$end = mktime(23,59,59,date('m',$sprintinfo['commit']), date('d',$sprintinfo['commit']), date('Y',$sprintinfo['commit']));
			} else {
				$start = mktime(0,0,0,date('m',$i), date('d',$i), date('Y',$i));
				$end = mktime(23,59,59,date('m',$i), date('d',$i), date('Y',$i));
			}
			
			$userstories = $tasks->getAssumedUserStories($bugList, $start, $end);
			$additional_capacity = 0;
			if($userstories){
				foreach($userstories AS $story => $task){
					$tasked = $sprint->getSprintTasks($task['bug_id'],0);
					if(!empty($tasked) && $task['date_modified'] >= $sprintinfo['commit']){
						foreach($tasked AS $num => $row){
							$tasklog = $tasks->getTaskLog($row['id']);
							$date = strtotime($tasklog[0]['date']);
							$capacity = $tasks->getDailyPerformance($row['id']);
							if($date <= $end){
								$additional_capacity += $capacity[0]['rest'] * $multiplier;
							}
						}
					}
				}
			}

			$additional_capacity -= $userstory->getWorkMovedFromSplittedStories($bugList, date('Y-m-d',$i)) * $multiplier;
			
			echo '<entry date="'.date('d.m.Y H:i', $i).'" value="'.$start_capacity.'"></entry>';
			if($start_capacity - $av->getTeamCapacity($sprintinfo['team_id'], date('Y-m-d',$i), date('Y-m-d',$i)) + $additional_capacity > 0){
				$start_capacity -= $av->getTeamCapacity($sprintinfo['team_id'], date('Y-m-d',$i), date('Y-m-d',$i));
				$start_capacity += $additional_capacity;
			} else {
				$start_capacity = 0;
			}
		}
	echo '</optimal_burndown>';
echo '</HoursBurnDown>';
?>