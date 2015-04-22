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



echo '<StoryPointBurnDown>';
echo '<ideal_burndown>';
echo '<entry date="' . date( 'd.m.Y H:i', $sprintinfo['start'] ) . '" value="' . $storypoints .
	 '"></entry>';
echo '<entry date="' . date( 'd.m.Y H:i', $sprintinfo['end'] ) . '" value="0"></entry>';
echo '</ideal_burndown>';
echo '<actual_burndown>';
echo '<entry date="' . date( 'd.m.Y H:i', $sprintinfo['start'] ) . '" value="' . $storypoints .
	 '"></entry>';

$gesamt_storypoints = $storypoints;
if( !empty( $storypoints_left ) ){
	ksort( $storypoints_left );

	foreach( $storypoints_left as $key => $value ) {
		$gesamt_storypoints -= $value;
		if( $key <= mktime() && $key >= $sprintinfo['start'] && $key <= $sprintinfo['end'] + 86400 &&
			 $gesamt_storypoints >= 0 ) {
			echo '<entry date="' . date( 'd.m.Y H:i', $key ) . '" value="' . $gesamt_storypoints .
			 '"></entry>';
		}
	}
}
for( $i = $sprintinfo['start']; $i <= $sprintinfo['end']; $i += 86400 ) {
	if( $key < $i && $i <= mktime() + 86400 && $gesamt_storypoints >= 0 ) {
		echo '<entry date="' . date( 'd.m.Y H:i', $i ) . '" value="' . $gesamt_storypoints .
			 '"></entry>';
	}
	if( empty( $key ) && $i <= mktime() + 86400 ) {
		echo '<entry date="' . date( 'd.m.Y H:i', $i ) . '" value="' . $gesamt_storypoints .
			 '"></entry>';
	}
}
echo '</actual_burndown>';
echo '</StoryPointBurnDown>';
?>