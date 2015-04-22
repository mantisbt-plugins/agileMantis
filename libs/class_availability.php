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


# manages availbilities and capacities of agileMantis developers
class gadiv_availability extends gadiv_commonlib {
	var $user_id;
	var $monday;
	var $tuesday;
	var $wednesday;
	var $thursday;
	var $friday;
	var $saturday;
	var $sunday;
	
	# deletes already existing agileMantis participant 
	# availbilites and insert new capacities for this agileMantis participant 
	function setUserAvailability() {
				
		$t_sql = "DELETE FROM gadiv_rel_user_availability_week WHERE user_id=" . db_param( 0 );
		$t_params = array( $this->user_id );
		db_query_bound( $t_sql, $t_params );
				
		$t_sql = "INSERT INTO gadiv_rel_user_availability_week 
 		          (user_id,monday,tuesday,wednesday,thursday,friday,saturday,sunday) 
						VALUES (" . db_param( 0 ) . "," . db_param( 1 ) . "," . db_param( 2 ) . "," . 
						db_param( 3 ) . "," . db_param( 4 ) . "," . db_param( 5 ) . "," . db_param( 6 ) . "," . 
		                db_param( 7 ) . ")";
		$t_params = array( $this->user_id, $this->monday, $this->tuesday, $this->wednesday, 
			$this->thursday, $this->friday, $this->saturday, $this->sunday );

		db_query_bound( $t_sql, $t_params );
	}
	
	# deletes availbilities of an agileMantis participant in a predefined period
	function deleteUserCapacity( $p_user_id, $p_date ) {
		
		$db_date = $this->getNormalDateFormat($p_date);
		
		$t_sql = "DELETE FROM gadiv_rel_user_availability WHERE user_id=" . db_param( 0 ) .
			 " AND date=" . db_param( 1 );
		$t_params = array( $p_user_id, $db_date );
		db_query_bound( $t_sql, $t_params );
	}
	
	# adds availability for a selected user on the specific day
	function setUserCapacity( $p_user_id, $p_date, $p_capacity ) {

		$db_date = $this->getNormalDateFormat($p_date);
				
		$t_sql = "INSERT INTO gadiv_rel_user_availability 
						VALUES ( " . db_param( 0 ) . "," . db_param( 1 ) . "," . db_param( 2 ) . ")";
		$t_params = array( $p_user_id, $db_date, $p_capacity );
		db_query_bound( $t_sql, $t_params );
		
	}
	
	# fetch all availbilies of an user  and create a 4D-Array for filling the calender.
	function getUserCapacity() {
		$t_sql = "SELECT * FROM gadiv_rel_user_availability WHERE user_id=" . db_param( 0 );
		$t_params = array( $this->user_id );
		$t_user = $this->executeQuery( $t_sql, $t_params );
		if( !empty( $t_user ) ) {
			foreach( $t_user as $num => $row ) {
				$convertedDate = substr($row['date'], 0, 10);
				$uc[$row['user_id']][$convertedDate] = $row['capacity'];
			}
		}
		return $uc;
	}
	
	# get the whole capacity values from a user in a predefined period
	function getPredaysCapacity( $p_user_id, $p_start_date, $p_end_date ) {
		
		$db_start_date = $this->getNormalDateFormat($p_start_date);
		$db_end_date   = $this->getNormalDateFormat($p_end_date  );
		
		$t_sql = "SELECT * FROM gadiv_rel_user_availability WHERE user_id=" . db_param( 0 ) .
			 " AND date>=" . db_param( 1 ) . " AND date<=" . db_param( 2 );
		$t_params = array( $p_user_id, $db_start_date, $db_end_date );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get the whole team member capacity in a certain period of time
	function getMemberCapacity( $p_user_id, $p_date_start, $p_date_end ) {
		
 		$db_start_date = $this->getNormalDateFormat($p_date_start);
 		$db_end_date   = $this->getNormalDateFormat($p_date_end  );
		
		$t_sql = "SELECT sum( capacity ) AS total_cap 
						FROM gadiv_rel_user_team_capacity 
						WHERE user_id=" . db_param( 0 ) . " 
						AND date>=" . db_param( 1 ) . " 
						AND date<=" . db_param( 2 );
		$t_params = array( $p_user_id, $db_start_date, $db_end_date );
		$t_result = $this->executeQuery( $t_sql, $t_params );
		return $t_result[0]['total_cap'];
	}
	
	# get the capacity which is planned in a team by a user
	function getUserCapacityByTeam( $p_team_id, $p_user_id, $p_date_start, $p_date_end ) {
		
		$db_start_date = $this->getNormalDateFormat($p_date_start);
		$db_end_date   = $this->getNormalDateFormat($p_date_end  );
		
		$t_sql = "SELECT * 
						FROM gadiv_rel_user_team_capacity 
						WHERE user_id=" . db_param( 0 ) . " 
						AND team_id=" . db_param( 1 ) . " 
						AND date>=" . db_param( 2 ) . " 
						AND date<=" . db_param( 3 );
		$t_params = array( $p_user_id, $p_team_id, $db_start_date, $db_end_date );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get the total capacity of one team in a defined period of time
	function getTeamCapacity( $p_team_id, $p_date_start, $p_date_end, $p_withConvertDate = true ) {
		
		if ($p_withConvertDate) {
			$p_date_start = $this->getNormalDateFormat($p_date_start);
			$p_date_end   = $this->getNormalDateFormat($p_date_end  );		
		}
		
		$t_sql = "SELECT sum( capacity ) AS total_cap 
					FROM gadiv_rel_user_team_capacity 
					WHERE team_id=" . db_param( 0 ) . " 
					AND date>=" . db_param( 1 ) . " 
					AND date<=" . db_param( 2 ); 
					//GROUP BY team_id";
		$t_params = array( $p_team_id, $p_date_start, $p_date_end );
		$t_result = $this->executeQuery( $t_sql, $t_params );
		return $t_result[0]['total_cap'];
	}
	
	# saves standard availability for one user
	function saveMonthAvailability( $p_user_id, $p_year, $p_month ) {
		
		$t_count_over_capacity = 0;
		$t_sql = "SELECT * 
					FROM gadiv_rel_user_availability_week 
					WHERE user_id=" . db_param( 0 );
		$t_params = array( $p_user_id );
		$x = $this->executeQuery( $t_sql, $t_params );
		$t_user = $x[0];
		
				
		if( $p_month != date( 'n', time() ) ) {
			$t_start_day = 1;
		} else {
			$t_start_day = date( 'j', time() );
		}
		
		$t_month_start = mktime( 0, 0, 0, $p_month, $t_start_day, $p_year );
		$t_end_day = date( 't', $t_month_start );
		$t_first_day = date( 'N', $t_month_start );
		
		$x = $t_first_day;
		
		for( $i = $t_start_day; $i <= $t_end_day; $i++ ) {
			$t_day_string = $this->getDayStringForIndex( $x );
			
			$t_sql = "DELETE FROM gadiv_rel_user_availability 
						WHERE user_id=" . db_param( 0 ) . " 
						AND date=" . db_param( 1 );
			
			$t_params = array( $p_user_id, ($this->getDateFormat($p_year, $p_month,$i)) );
			//$t_params = array( $p_user_id, ($p_year . '-' . $p_month . '-' . $i) );
			db_query_bound( $t_sql, $t_params );
			
			
			$t_sql = "INSERT INTO gadiv_rel_user_availability
						VALUES ( " . db_param( 0 ) . "," . db_param( 1 ) . "," . db_param( 2 ) . ")";
			
			if ( is_null( $t_user[$t_day_string] ) ) {
				$t_user[$t_day_string] = 0.00;
			}
			
			$t_params = array( $p_user_id, $this->getDateFormat($p_year,$p_month,$i),  $t_user[$t_day_string] );
			db_query_bound( $t_sql, $t_params );
			//$t_params = array( $t_user[$t_day_string], $p_user_id, ($p_year . '-' . $p_month . '-' . $i) );
					
			if( $this->getCapacityToSavedAvailability( $p_user_id, $p_year . '-' . str_pad($p_month, 2 ,'0', STR_PAD_LEFT) . '-' . str_pad($i, 2 ,'0', STR_PAD_LEFT) ) > $t_user[$t_day_string] ) {
				$t_count_over_capacity++;
			}
			$x++;
			if( $x == 8 ) {
				$x = 1;
			}
		}
		
		return $t_count_over_capacity;
	}

	function getDayStringForIndex( $index ) {
		switch( $index ) {
			case 1:
				return 'monday';
			case 2:
				return 'tuesday';
			case 3:
				return 'wednesday';
			case 4:
				return 'thursday';
			case 5:
				return 'friday';
			case 6:
				return 'saturday';
			case 7:
				return 'sunday';
			default:
				return '';
		}
	}
	
	# get standard availability of one user
	function getUserAvailability( $p_user_id, $p_day ) {
		$t_sql = "SELECT * 
				FROM gadiv_rel_user_availability_week 
				WHERE user_id=" . db_param( 0 );
		$t_params = array( $p_user_id );
		$t_user = $this->executeQuery( $t_sql, $t_params );
		$t_user[0]['availability'] = $t_user[0]['monday'] + $t_user[0]['tuesday'] +
			 $t_user[0]['wednesday'] + $t_user[0]['thursday'] + $t_user[0]['friday'] +
			 $t_user[0]['saturday'] + $t_user[0]['sunday'];
		
		switch( $p_day ) {
			case '7':
				return $t_user[0]['availability'];
				break;
			case '1':
				return $t_user[0]['monday'];
				break;
			case '2':
				return $t_user[0]['tuesday'];
				break;
			case '3':
				return $t_user[0]['wednesday'];
				break;
			case '4':
				return $t_user[0]['thursday'];
				break;
			case '5':
				return $t_user[0]['friday'];
				break;
			case '6':
				return $t_user[0]['saturday'];
				break;
			case '0':
				return $t_user[0]['sunday'];
				break;
		}
		return $t_user[0]['availability'];
	}
	
	# check if users capacity is exceeded
	function getUserMarking( $p_id ) {
		$t_sql = "SELECT marked 
					FROM gadiv_rel_user_availability_week 
					WHERE user_id=" . db_param( 0 );
		$t_params = array( $p_id );
		$t_is = $this->executeQuery( $t_sql, $t_params );
		return ($t_is[0]['marked'] == 1);
	}
}
?>