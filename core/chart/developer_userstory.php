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
echo '<UtilizationDistribution>';
if( isset( $developer ) ){
	foreach( $developer as $teamdev => $developer ) {
		if( $agilemantis_tasks->getUserName( $teamdev ) != "" ) {
			$name = $agilemantis_tasks->getUserName( $teamdev );
		} else {
			$name = "NN";
		}
		echo '<user name="' . $name . '" value1="' . $developer['planned_capacity'] * $multiplier .
			 '" value2="' . $developer['rest_capacity'] * $multiplier . '" value3="' .
			 $developer['performed_capacity'] * $multiplier . '"></user>';
	}
}
echo '</UtilizationDistribution>';
?>