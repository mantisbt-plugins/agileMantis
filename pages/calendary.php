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



# get current year
if( $year_start < $year_current ) {
	$year = $year_start;
} elseif( $year_end >= $year_current ) {
	$year = $year_current;
}

# open calendary view for each month and each developer
for( $x = 0; $x <= $amount_of_month; $x++ ) {
	
	$monat = date( 'n', $start ) + $x;
	
	if( $x == 0 && $amount_of_month > 0 && $amount_of_month != $x ) {
		$start_tag = $start;
		$anzahl_tage_im_monat = date( 't', $start );
		$end_tag = mktime( 0, 0, 0, $monat, $anzahl_tage_im_monat, $year_start );
	}
	
	if( $x > 0 && $amount_of_month > 0 && $amount_of_month > $x ) {
		$start_tag = mktime( 0, 0, 0, $monat, 1, $year );
		$anzahl_tage_im_monat = date( 't', $start_tag );
		$end_tag = mktime( 0, 0, 0, $monat, $anzahl_tage_im_monat, $year );
	}
	
	if( $x > 0 && $amount_of_month > 0 && $amount_of_month == $x ) {
		$start_tag = mktime( 0, 0, 0, $monat, 1, $year );
		$end_tag = $end;
	}
	
	if( $x == $amount_of_months && $amount_of_month == 0 ) {
		$start_tag = $start;
		$end_tag = $end;
	}
	
	$date_start = date( 'Y', $start_tag ) . '-' . date( 'm', $start_tag ) . '-' . date( 'd', $start_tag );
	$date_end = date( 'Y', $end_tag ) . '-' . date( 'm', $end_tag ) . '-' . date( 'd', $end_tag );
	
	$getTMC2 = $agilemantis_team->getTeamMemberCapacity( $row['id'], $date_start, $date_end );
	
	$getTMC = $getTMC2[0]['total_cap'];
	$getCap = $agilemantis_av->getMemberCapacity( $row['id'], $date_start, $date_end );

	if( $getTMC == NULL ) {
		$getTMC = 0.00;
	}
	
	if( $getCap <= $getTMC ) {
		$color = "grey";
		$add = '';
	} else {
		$color = "red";
		$add = "font-weight:bold;";
	}

	# set calendary hight
	switch( $amount_of_weeks ) {
		case '1':
			$t_cal_full_height_class = 'height350';
			$t_cal_height_class = 'height200';
			break;
		case '2':
			$t_cal_full_height_class = 'height400';
			$t_cal_height_class = 'height250';
			break;
		case '3':
			$t_cal_full_height_class = 'height500';
			$t_cal_height_class = 'height350';
			break;
		default:
			$t_cal_full_height_class = 'height550';
			$t_cal_height_class = 'height400';
			break;
	}
	
	echo '<div class="fullcalendar ' . $t_cal_full_height_class . '">
			<table cellspacing="1" class="width100">
			<tr>
				<td>
					<span class="message_error">'.$system.'</span><center><h2>'.
					$row['username'].' <br><span style="color:'.$color.';font-size:12px;'.
					$add.'">('.plugin_lang_get( 'manage_capacity_totally_planned' ).
					sprintf("%.2f",$getCap).plugin_lang_get( 'manage_capacity_of' ).
					( sprintf("%.2f",$getTMC)).')</span></h2></center>';
	echo '		</td>
			</tr>
			<tr>
				<td>';
				echo '<div class="calendar ' . $t_cal_height_class . '">';
				$count = $agilemantis_cal->getCalender($start_tag, $end_tag, $row['user_id'], $days_of_week );
							
				echo '</div>';
				if( $count > 0 ) { 
					$error = '<center><span class="message_error">'.
					plugin_lang_get( 'manage_capacity_error_108401' ).'</span></center>';
				} else { 
					$error = '<br>';
				}
				echo $error;
	echo '		</td>
			</tr>
		</table>';
	echo '</div>';
	}
?>