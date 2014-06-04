<?php
	$capacityValue = 0;
	$velocityValue = 0;
	sort($avarage);
	$sprintCapacity = $av->getTeamCapacity($sprintinfo['team_id'],$sprintinfo['start'],$sprintinfo['end']);
	echo '
		<VelocityCapacity>';
			echo '<capacity>';
			if(!empty($avarage)){
				foreach($avarage AS $num => $row){
					if($row['status'] == 2){
						echo '<entry name="'.$row['name'].'" value="'.$row['total_developer_capacity'].'"></entry>';
					}
				}
			}
			echo '<entry name="'.$sprintinfo['name'].'" value="'.$sprintCapacity.'"></entry>';
			echo '</capacity>';
			echo '<velocity>';
			if(!empty($avarage)){
				foreach($avarage AS $num => $row){
					echo '<entry name="'.$row['name'].'" value="'.$row['storypoints_sprint'].'"></entry>';
					$velocityValue += $row['storypoints_sprint'];
				}
			}
			echo '<entry name="'.$sprintinfo['name'].'" value="'.$storypointsSprintCurrentDayRest.'"></entry>';
			$avgVelocity = $velocityValue / $_POST['amountOfSprints'];
			echo '</velocity>';
			echo '<avgVelocity>';
			echo '<entry value="'.$avgVelocity.'"></entry>';
			echo '</avgVelocity>';
	echo '
		</VelocityCapacity>
	';
?>