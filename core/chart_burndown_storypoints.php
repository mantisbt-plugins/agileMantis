<?php
echo '<StoryPointBurnDown>';
	echo '<ideal_burndown>';
		echo '<entry date="'.date('d.m.Y H:i',$sprintinfo['start']).'" value="'.$storypoints.'"></entry>';
		echo '<entry date="'.date('d.m.Y H:i',$sprintinfo['end']).'" value="0"></entry>';
	echo '</ideal_burndown>';
	echo '<actual_burndown>';
	echo '<entry date="'.date('d.m.Y H:i',$sprintinfo['start']).'" value="'.$storypoints.'"></entry>';
		$gesamt_storypoints = $storypoints;
		ksort($storypoints_left);
		foreach($storypoints_left AS $key => $value){
			$gesamt_storypoints -= $value;
			if($key <= mktime() && $key >= $sprintinfo['start'] && $key <= $sprintinfo['end']+86400 && $gesamt_storypoints >= 0){
				echo '<entry date="'.date('d.m.Y H:i', $key).'" value="'.$gesamt_storypoints.'"></entry>';
			}
		}
			for($i = $sprintinfo['start']; $i <= $sprintinfo['end'];$i+=86400){
				if($key < $i && $i <= mktime()+86400 && $gesamt_storypoints >= 0){
					echo '<entry date="'.date('d.m.Y H:i',$i).'" value="'.$gesamt_storypoints.'"></entry>';
				}
				if(empty($key) && $i <= mktime()+86400){
					echo '<entry date="'.date('d.m.Y H:i',$i).'" value="'.$gesamt_storypoints.'"></entry>';
				}
			}
	echo '</actual_burndown>';
echo '</StoryPointBurnDown>';
?>