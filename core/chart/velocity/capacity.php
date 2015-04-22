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

$capacityValue = 0;
$velocityValue = 0;
sort( $avarage );
$sprintCapacity = $agilemantis_av->getTeamCapacity( $sprintinfo['team_id'], $convertedStart, $convertedEnd, false );

echo '
		<VelocityCapacity>';
echo '<capacity>';
if( !empty( $avarage ) ) {
	foreach( $avarage as $num => $row ) {
		if( $row['status'] == 2 ) {
			echo '<entry name="' . string_html_specialchars($row['name']) . '" value="' . $row['total_developer_capacity'] .
				 '"></entry>';
		}
	}
}
echo '<entry name="' . string_html_specialchars($sprintinfo['name']) . '" value="' . $sprintCapacity . '"></entry>';
echo '</capacity>';
echo '<velocity>';
if( !empty( $avarage ) ) {
	foreach( $avarage as $num => $row ) {
		echo '<entry name="' . string_html_specialchars($row['name']) . '" value="' . $row['storypoints_sprint'] . '"></entry>';
		$velocityValue += $row['storypoints_sprint'];
	}
}
echo '<entry name="' . string_html_specialchars($sprintinfo['name']) . '" value="' . $storypointsSprintCurrentDayRest .
	 '"></entry>';
if( $_POST['amountOfSprints'] != 0 ){	
	$avgVelocity = $velocityValue / $_POST['amountOfSprints'];
} else {
	$agilemantis_commonlib->createAgManWarning( 'amountOfSprints' );
	$avgVelocity = $velocityValue;
}
echo '</velocity>';
echo '<avgVelocity>';
echo '<entry value="' . $avgVelocity . '"></entry>';
echo '</avgVelocity>';
echo '
		</VelocityCapacity>
	';
?>