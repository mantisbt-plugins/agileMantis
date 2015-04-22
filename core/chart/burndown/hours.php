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



if( $_POST['taskUnit'] == 'T' ) {
	$multiplier = $_POST['workdayInHours'];
} else {
	$multiplier = 1;
}

echo '<HoursBurnDown>';
echo '<ideal_burndown>';

# WP(S)
$workPlanned = $planned_capacity_new * $multiplier;

# WPz(S)
$userstories = $agilemantis_tasks->getAssumedUserStories( $bugList, $sprintinfo['start'], 
	$sprintinfo['end'] );
if( $userstories ) {
	foreach( $userstories as $story => $task ) {
		$tasked = $agilemantis_sprint->getSprintTasks( $task['bug_id'], 0 );
		if( !empty( $tasked ) && $task['date_modified'] >= $sprintinfo['commit'] ) {
			foreach( $tasked as $num => $row ) {
				$tasklog = $agilemantis_tasks->getTaskLog( $row['id'] );
				$date = strtotime( $tasklog[0]['date'] );
				$capacity = $agilemantis_tasks->getDailyPerformance( $row['id'] );
				if( $date <= $end ) {
					$additionalCapacity += $capacity[0]['rest'] * $multiplier;
				}
			}
		}
	}
}

# WPr(S)
for( $i = $sprintinfo['start']; $i <= $sprintinfo['end']; $i += 86400 ) {
	$additionalCapacity -= $agilemantis_userstory->getWorkMovedFromSplittedStories( $bugList, 
		date( 'Y-m-d', $i ) ) * $multiplier;
}

# K(S)
$sprintCapacity = $agilemantis_av->getTeamCapacity( $sprintinfo['team_id'], 
	date( 'Y-m-d', $sprintinfo['start'] ), date( 'Y-m-d', $sprintinfo['end'] ) );

# VPK(S) = (WP(S) + WPz(S) - WPr(S)) / K(S)
if( $sprintCapacity != 0 ){
	$vpks = ($workPlanned - $additionalCapacity) / $sprintCapacity;
} else {
	$agilemantis_commonlib->createAgManWarning( 'sprintCapacity' );
	$vpks = $workPlanned - $additionalCapacity;
}
echo '<entry date="' . date( 'd.m.Y H:i', $sprintinfo['start'] ) . '" value="' .
	 $planned_capacity_new * $multiplier . '"></entry>';
for( $i = $sprintinfo['start']; $i <= $sprintinfo['end'] + 86340; $i += 86400 ) {
	echo '<entry date="' . date( 'd.m.Y H:i', $i ) . '" value="' . $workPlanned . '"></entry>';
	if($agilemantis_av->getTeamCapacity( $sprintinfo['team_id'], date( 'Y-m-d', $sprintinfo['start'] ), 
			date( 'Y-m-d', $sprintinfo['end'] ) ) == 0){
		echo '<entry date="' . date( 'd.m.Y H:i', $sprintinfo['end'] ) . '" value="0"></entry>';
		break;
	}else if( $workPlanned -
		 $agilemantis_av->getTeamCapacity( $sprintinfo['team_id'], date( 'Y-m-d', $i ), 
			date( 'Y-m-d', $i ) ) * $vpks < 0 ) {
		$workPlanned = 0;
	} else {
		$workPlanned -= $agilemantis_av->getTeamCapacity( $sprintinfo['team_id'], date( 'Y-m-d', 
			$i ), date( 'Y-m-d', $i ) ) * $vpks;
	}
}
echo '</ideal_burndown>';
echo '<actual_burndown>';
echo '<entry date="' . date( 'd.m.Y H:i', $sprintinfo['start'] ) . '" value="' .
	 $planned_capacity_new * $multiplier . '"></entry>';
foreach( $work_done as $key => $value ) {
	$date = strtotime( $key );
	if( $sprintinfo['end'] >= mktime() ) {
		if( $date <= mktime() && $date >= $sprintinfo['start'] && $date <= $sprintinfo['end'] + 86400 ) {
			echo '<entry date="' . $key . ' 23:59" value="' . $value * $multiplier . '"></entry>';
		}
	} else {
		echo '<entry date="' . $key . ' 23:59" value="' . $value * $multiplier . '"></entry>';
	}
}
echo '</actual_burndown>';
echo '<capacity>';
$start_hours = $agilemantis_av->getTeamCapacity( $sprintinfo['team_id'], 
	date( 'Y-m-d', $sprintinfo['start'] ), date( 'Y-m-d', $sprintinfo['end'] ) );
echo '<entry date="' . date( 'd.m.Y H:i', $sprintinfo['start'] ) . '" value="' . $start_hours .
	 '"></entry>';
for( $i = $sprintinfo['start']; $i <= $sprintinfo['end'] + 86340; $i += 86400 ) {
	if( $previousDate == date( 'd.m.Y', $i ) ) {
		$i += 3600;
	}
	echo '<entry date="' . date( 'd.m.Y H:i', $i ) . '" value="' . $start_hours . '"></entry>';
	if( $start_hours -
		 $agilemantis_av->getTeamCapacity( $sprintinfo['team_id'], date( 'Y-m-d', $i ), 
			date( 'Y-m-d', $i ) ) > 0 ) {
		$start_hours -= $agilemantis_av->getTeamCapacity( $sprintinfo['team_id'], date( 'Y-m-d', 
			$i ), date( 'Y-m-d', $i ) );
	} else {
		$start_hours = 0;
	}
	$previousDate = date( 'd.m.Y', $i );
}
echo '</capacity>';
echo '<optimal_burndown>';
$start_capacity = $planned_capacity_new * $multiplier;
echo '<entry date="' . date( 'd.m.Y H:i', $sprintinfo['start'] ) . '" value="' . $start_capacity .
	 '"></entry>';
for( $i = $sprintinfo['start']; $i <= $sprintinfo['end'] + 86340; $i += 86400 ) {
	
	if( $i == $sprintinfo['start'] ) {
		$start = mktime( date( 'H', $sprintinfo['commit'] ), date( 'i', $sprintinfo['commit'] ), 
			date( 's', $sprintinfo['commit'] ), date( 'm', $i ), date( 'd', $i ), date( 'Y', $i ) );
		$end = mktime( 23, 59, 59, date( 'm', $sprintinfo['commit'] ), 
			date( 'd', $sprintinfo['commit'] ), date( 'Y', $sprintinfo['commit'] ) );
	} else {
		$start = mktime( 0, 0, 0, date( 'm', $i ), date( 'd', $i ), date( 'Y', $i ) );
		$end = mktime( 23, 59, 59, date( 'm', $i ), date( 'd', $i ), date( 'Y', $i ) );
	}
	
	$userstories = $agilemantis_tasks->getAssumedUserStories( $bugList, $start, $end );
	$additional_capacity = 0;
	if( $userstories ) {
		foreach( $userstories as $story => $task ) {
			$tasked = $agilemantis_sprint->getSprintTasks( $task['bug_id'], 0 );
			if( !empty( $tasked ) && $task['date_modified'] >= $sprintinfo['commit'] ) {
				foreach( $tasked as $num => $row ) {
					$tasklog = $agilemantis_tasks->getTaskLog( $row['id'] );
					$date = strtotime( $tasklog[0]['date'] );
					$capacity = $agilemantis_tasks->getDailyPerformance( $row['id'] );
					if( $date <= $end ) {
						$additional_capacity += $capacity[0]['rest'] * $multiplier;
					}
				}
			}
		}
	}
	
	$additional_capacity -= $agilemantis_userstory->getWorkMovedFromSplittedStories( $bugList, 
		date( 'Y-m-d', $i ) ) * $multiplier;
	
	echo '<entry date="' . date( 'd.m.Y H:i', $i ) . '" value="' . $start_capacity . '"></entry>';
	if( $start_capacity -
		 $agilemantis_av->getTeamCapacity( $sprintinfo['team_id'], date( 'Y-m-d', $i ), 
			date( 'Y-m-d', $i ) ) + $additional_capacity > 0 ) {
		$start_capacity -= $agilemantis_av->getTeamCapacity( $sprintinfo['team_id'], 
			date( 'Y-m-d', $i ), date( 'Y-m-d', $i ) );
		$start_capacity += $additional_capacity;
	} else {
		$start_capacity = 0;
	}
}
echo '</optimal_burndown>';
echo '</HoursBurnDown>';
?>