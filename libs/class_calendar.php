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




# generates a calendary view for agileMantis in order to plan capacities and developer availabilities
class gadiv_calendar extends gadiv_commonlib {
	
	# function which generates on calendar with a fixed start and enddate for one user
	function getCalender( $start, $end, $user_id, 
		$headline = array( 'Mo', 'Di', 'Mi', 'Do' ,'Fr' , 'Sa', 'So' ) ) {
			
		# make a new instance of availability class and get user information
		global $agilemantis_av;
		$agilemantis_av->user_id = $user_id;
		$team = ( int ) $_POST['team'];
		
		# calculating the month attributes
		$start_day = date( 'j', $start );
		$month = date( 'm', $start );
		$year = date( 'Y', $start );
		$end_day = date( 'j', $end );
		$FirstDaySum = date( 'd', $start );
		
		$end_of_month = date( 't', $end );
		
		# current day, month and year
		$current_day = date( 'j', time() );
		$current_month = date( 'n', time() );
		$current_year = date( 'Y', time() );
		
		# array with all month of one year
		$arrMonth = array( "January" => plugin_lang_get( 'january' ), 
			"February" => plugin_lang_get( 'february' ), "March" => plugin_lang_get( 'march' ), 
			"April" => plugin_lang_get( 'april' ), "May" => plugin_lang_get( 'may' ), 
			"June" => plugin_lang_get( 'june' ), "July" => plugin_lang_get( 'july' ), 
			"August" => plugin_lang_get( 'august' ), "September" => plugin_lang_get( 'september' ), 
			"October" => plugin_lang_get( 'october' ), "November" => plugin_lang_get( 'november' ), 
			"December" => plugin_lang_get( 'december' ) );
		
		# calendar head
		echo '<div class="headline_month">' . $arrMonth[date( 'F', $start )] . ' ' .
			 date( 'Y', $start ) . '</div><div class="clear"></div>';
		foreach( $headline as $key => $value ) {
			echo "<div class=\"headline_days\">" . $value . "</div>\n";
		}
		
		# collect all availabilities and capacities from one user
		if( $team > 0 ) {
			
			$date_start = $year . '-' . $month . '-' . date( 'd', $start );
			$date_end = $year . '-' . $month . '-' . date( 'd', $end );
			$user1 = $agilemantis_av->getUserCapacityByTeam( $team, $user_id, $date_start, 
				$date_end );
			
			if( !empty( $user1 ) ) {
				foreach( $user1 as $num => $row ) {
					$convertedDate = substr($row['date'], 0, 10);
					$user[$row['user_id']][$convertedDate] = $row['capacity'];
				}
			}
			if( $_POST['addavailability'] ) {
				if( $current_month != $month ) {
					$startdaymonth = $start_day;
				} else {
					$startdaymonth = $current_day;
				}
				if( $current_year != $year ) {
					$startdayyear = $year;
				} else {
					$startdayyear = $current_year;
				}
				
				if( $start > mktime() ) {
					$date_start = $year . '-' . $month . '-' . date( 'd', $start );
				} else {
					$date_start = $year . '-' . $month . '-' . date( 'd' );
				}
				$date_end = $year . '-' . $month . '-' . date( 'd', $end );
				
				$user2 = $agilemantis_av->getPredaysCapacity( $user_id, $date_start, $date_end );
			    
				if( !empty( $user2 ) ) {
					foreach( $user2 as $num => $row ) {
						if( $user[$row['user_id']][$row['date']] == "" ||
							 $user[$row['user_id']][$row['date']] == 0 ) {
							
							$row['capacity'] = $row['capacity'] - $agilemantis_av->getCapacityToSavedAvailability( 
								$row['user_id'], $row['date'] );
							
							if( $row['capacity'] <= 0.00 ) {
								$row['capacity'] = 0.00;
							}
							
							$convertedDate = substr($row['date'], 0, 10);
							$user[$row['user_id']][$convertedDate] = $row['capacity'];
						}
					}
				}
			}
		} else {
			$user = $agilemantis_av->getUserCapacity();
			if( $_POST['standard_availability'] != "" ) {
				foreach( $_POST['standard_availability'] as $num => $row ) {
					foreach( $row as $key => $value ) {
						if( $key > 12 ) {
							$standard_month = $key - 12;
						} else {
							$standard_month = $key;
						}
						foreach( $value as $new => $title ) {
							$availability_year = $new;
						}
					}
				}
			}
			if( empty( $user ) || $month == $standard_month ) {
				$month = date( 'n', $start );
				if( $standard_month > 0 && $month == $standard_month ) {
					$month = $standard_month;
				}
				if( $_POST['standard_availability'] ) {
					
					$date_start = $year . '-' . $month . '-01';
					$date_end = $year . '-' . $month . '-' . date( 'd', $end );
					for( $i = $start_day; $i <= $end_day; $i++ ) {
						$date = $availability_year . '-' . $month . '-' . $i;
						$day_number = date( 'w', mktime( 0, 0, 0, $month, $i, $availability_year ) );
						$user[$user_id][$date] = $agilemantis_av->getUserAvailability( $user_id, 
							$day_number );
						if( date( "j", time() ) != 1 && date( 'n', time() ) == $month ) {
							$user4 = $agilemantis_av->getPredaysCapacity( $user_id, $date_start, 
								$date_end );
							if( !empty( $user4 ) ) {
								foreach( $user4 as $num => $row ) {
									$convertedDate = substr($row['date'], 0, 10);
									$user[$row['user_id']][$convertedDate] = $row['capacity'];
								}
							}
						}
					}
				}
			}
		}
		
		$d = $end_day + 1;
		
		# echo all days of the calendar and fill it with the collected values
		$count[$user_id] = 0;
		for( $i = $start_day; $i <= $end_day; $i++ ) {
			$day_number = date( 'w', 
				mktime( 0, 0, 0, date( 'm', $start ), $i, date( 'Y', $start ) ) );
			$day_name = date( 'D', mktime( 0, 0, 0, date( 'm', $start ), $i, date( 'Y', $start ) ) );
			$month_name = date( 'm', $start );
			$year_name = date( 'Y', $start );
			
			if( $i == date( "d", $start ) ) {
				$s = array_search( $day_name, 
					array( 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ) );
				for( $b = $s; $b > 0; $b-- ) {
					if( $FirstDaySum - $b > 0 ) {
						$x = $FirstDaySum - $b;
						echo '
							<div class="before_month">
								' . sprintf( "%02d", $x ) .
							 '<br>
								<center><input type="text" name="" value="" ' . 'class="dateField_disabled" readonly></center>
							</div>';
					} else {
						$predays = mktime( 0, 0, 0, date( "m", $start ) - 1, date( "d", $start ), 
							date( "Y", $start ) );
						$x = date( 't', $predays ) + 1 - $b;
						echo '
							<div class="before_month">
								' . sprintf( "%02d", $x ) .
							 '<br>
								<center><input type="text" name="" value="" ' . 'class="dateField_disabled" readonly></center>
							</div>';
					}
				}
			}
			
			# get the current date
			if( date( 'n', time() ) == $current_month ) {
				$heute = date( 'j', 
					mktime( 0, 0, 0, date( 'm', time() ), date( 'd', time() ), date( 'Y', time() ) ) );
			}
			
			# check if the current date is newer than the original start date and add one day to the end
			if( date( 'n', $start ) < date( 'n', time() ) ) {
				$heute = $end_day + 1;
			}
			
			if( $current_year > $year ) {
				if( $_POST['team'] ) {
					$value = sprintf( "%.2f", 
						$user[$agilemantis_av->user_id]['' . $year_name . '-' . $month_name . '-' .
							 sprintf( "%02d", $i ) . ''] );
					echo '<div class="current_month">' . sprintf( "%02d", $i ) .
						 '<br><center><input type="text" name="capacity[' . $agilemantis_av->user_id .
						 '][' . date( 'Y', $start ) . '-' . date( 'm', $start ) . '-' .
						 sprintf( "%02d", $i ) . ']"  style="' . $warning .
						 '" class="dateField_disabled" value="' . $value .
						 '" readonly></center></div>';
				} else {
					$value = sprintf( "%.2f", 
						$user[$agilemantis_av->user_id]['' . $year_name . '-' . $month_name . '-' .
							 sprintf( "%02d", $i ) . ''] );
					echo '<div class="current_month">' . sprintf( "%02d", $i ) .
						 '<br><center><input type="text" name="capacity[' . $agilemantis_av->user_id .
						 '][' . date( 'Y', $start ) . '-' . date( 'm', $start ) . '-' .
						 sprintf( "%02d", $i ) . ']"  style="' . $warning .
						 '" class="dateField_disabled" value="' . $value .
						 '" readonly></center></div>';
				}
			} else {
				if( $i < $heute && $current_month >= $month && $current_year == $year ) {
					if( $_POST['team'] ) {
						$value = sprintf( "%.2f", 
							$user[$agilemantis_av->user_id]['' . $year_name . '-' . $month_name . '-' .
								 sprintf( "%02d", $i ) . ''] );
						echo '<div class="current_month">' . sprintf( "%02d", $i ) .
							 '<br><center><input type="text" name="capacity[' .
							 $agilemantis_av->user_id . '][' . date( 'Y', $start ) . '-' .
							 date( 'm', $start ) . '-' . sprintf( "%02d", $i ) .
							 ']" class="dateField_disabled" value="' . $value .
							 '" readonly></center></div>';
					} else {
						$value = sprintf( "%.2f", 
							$user[$agilemantis_av->user_id]['' . $year_name . '-' . $month_name . '-' .
								 sprintf( "%02d", $i ) . ''] );
						echo '<div class="current_month">' . sprintf( "%02d", $i ) .
							 '<br><center><input type="text" name="capacity[' .
							 $agilemantis_av->user_id . '][' . date( 'Y', $start ) . '-' .
							 date( 'm', $start ) . '-' . sprintf( "%02d", $i ) . ']"  style="' .
							 $warning . '" class="dateField_disabled" value="' . $value .
							 '" readonly></center></div>';
					}
				} else {
					$warning = '';
					if( $agilemantis_av->compareAvailabilityWithCapacity( $user_id, $year_name, 
						$month_name, $i ) == true ) {
						$warning = '';
					} else {
						$warning = 'color:red;font-weight:bold;';
						$count[$user_id]++;
					}
					echo '<div class="current_month">' . sprintf( "%02d", $i ) .
						 '<br><center><input type="text" name="capacity[' . $agilemantis_av->user_id .
						 '][' . date( 'Y', $start ) . '-' . date( 'm', $start ) . '-' .
						 sprintf( "%02d", $i ) . ']" value="' . sprintf( "%.2f", 
							$user[$agilemantis_av->user_id][date( 'Y', $start ) . '-' .
							 date( 'm', $start ) . '-' . sprintf( "%02d", $i )] ) . '" style="' .
						 $warning . '" class="dateField" maxlength="5"></center></div>';
				}
				
				if( $i == $end_day ) {
					$next_sum = $i + (6 - array_search( $day_name, 
						array( 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ) ));
					
					for( $c = $i + 1; $c <= $next_sum; $c++ ) {
						if( $c > $end_of_month ) {
							$c = 1;
							$next_sum -= $i;
						}
						echo '<div class="after_month">
							' . sprintf( "%02d", $c ) .
							 '<br>
							<center><input type="text" name="" value="" ' . 'class="dateField_disabled" readonly></center>
						</div>';
					}
				}
			}
		}
		return $count[$user_id];
	}
}
?>