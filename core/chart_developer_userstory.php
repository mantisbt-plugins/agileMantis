<?php
if($_POST['taskUnit'] == 'T'){
	$multiplier = $_POST['workdayInHours'];
} else {
	$multiplier = 1;
}
echo '<UtilizationDistribution>';
foreach($developer AS $teamdev => $developer){
	if($tasks->getDeveloperById($teamdev) != ""){
		$name = $tasks->getDeveloperById($teamdev);
	} else {
		$name = "NN";
	}
	echo '<user name="'.$name.'" value1="'.$developer['planned_capacity'] * $multiplier.'" value2="'.$developer['rest_capacity'] * $multiplier.'" value3="'.$developer['performed_capacity'] * $multiplier.'"></user>';
}
echo '</UtilizationDistribution>';
?>