<?php
echo '<TaskBurnDownChart>';
	echo '<ideal_burndown>';
		echo '<entry date="'.date('d.m.Y H:i',$sprintinfo['start']).'" value="'.$countStartTasks.'"></entry>';
		echo '<entry date="'.date('d.m.Y H:i',$sprintinfo['end']).'" value="0"></entry>';
	echo '</ideal_burndown>';
	echo '<actual_burndown>';
	echo '<entry date="'.date('d.m.Y H:i',$sprintinfo['start']).'" value="'.$countStartTasks.'"></entry>';
		$gesamt_tasks = $countStartTasks;
		ksort($current_tasks);
		foreach($current_tasks AS $key => $value){
			$gesamt_tasks -= $value;
			if($key <= mktime()+86400 && $key >= $sprintinfo['start'] && $key <= $sprintinfo['end']+86400 && $gesamt_tasks >= 0){
				echo '<entry date="'.date('d.m.Y H:i', $key).'" value="'.$gesamt_tasks.'"></entry>';
			}
		}
		for($i = $sprintinfo['start']; $i <= $sprintinfo['end'];$i+=86400){
			if($key < $i && $i <= mktime()+86400 && $gesamt_tasks >= 0){
				echo '<entry date="'.date('d.m.Y H:i',$i).'" value="'.$gesamt_tasks.'"></entry>';
			}
		}
	echo '</actual_burndown>';
echo '</TaskBurnDownChart>';
?>
