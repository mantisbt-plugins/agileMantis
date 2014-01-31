<?php

	# agileMantis - makes Mantis ready for Scrum

	# agileMantis is free software: you can redistribute it and/or modify
	# it under the terms of the GNU General Public License as published by
	# the Free Software Foundation, either version 2 of the License, or
	# (at your option) any later version.
	#
	# agileMantis is distributed in the hope that it will be useful,
	# but WITHOUT ANY WARRANTY; without even the implied warranty of
	# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	# GNU General Public License for more details.
	#
	# You should have received a copy of the GNU General Public License
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
		
		
		# deletes already existing agileMantis participant availbilites and insert new capacities for this agileMantis participant 
		function setUserAvailability(){
			$sql = "DELETE FROM gadiv_rel_user_availability_week WHERE user_id = ".$this->user_id;
			mysql_query($sql);
			$sql = "INSERT INTO gadiv_rel_user_availability_week SET user_id = ".$this->user_id.", monday = '".$this->monday."', tuesday = '".$this->tuesday."', wednesday = '".$this->wednesday."', thursday = '".$this->thursday."', friday = '".$this->friday."', saturday = '".$this->saturday."', sunday = '".$this->sunday."'";
			mysql_query($sql);
		}
		

		# deletes availbilities of an agileMantis participant in a predefined period
		function deleteUserCapacity($user_id,$date){
			$sql = "DELETE FROM gadiv_rel_user_availability WHERE user_id = '".$user_id."' AND date = '".$date."'";
			mysql_query($sql);
		}

		# adds availability for a selected user on the specific day
		function setUserCapacity($user_id, $date, $capacity){
			$sql = "INSERT INTO gadiv_rel_user_availability SET user_id = '".$user_id."',date = '".$date."', capacity = '".$capacity."'";
			mysql_query($sql);
		}
		

		# fetch all availbilies of an user  and create a 4D-Array for filling the calender.

		function getUserCapacity(){
			$this->sql = "SELECT * FROM gadiv_rel_user_availability WHERE user_id = ".$this->user_id;
			$user = $this->executeQuery();
			if(!empty($user)){
				foreach($user AS $num => $row){
					$uc[$row['user_id']][$row['date']] = $row['capacity'];
				}
			}
			return $uc;
		}
		
		# get the whole capacity values from a user in a predefined period
		function getPredaysCapacity($user,$start_date,$end_date){
			$this->sql = "SELECT * FROM gadiv_rel_user_availability WHERE user_id = '".$user."' AND date >= '".$start_date."' AND date <= '".$end_date."'";		
			return $this->executeQuery();
		}
		
		# get the whole team member capacity in a certain period of time
		function getMemberCapacity($user_id,$date_start,$date_end){
			$this->sql = "SELECT sum( capacity ) AS total_cap FROM `gadiv_rel_user_team_capacity` WHERE user_id = '".$user_id."' AND date >= '".$date_start."' AND date <= '".$date_end."'";
			$result = $this->executeQuery();
			return $result[0]['total_cap'];
		}
	
		# get the capacity which is planned in a team by a user
		function getUserCapacityByTeam($team,$user,$date_start,$date_end){
			$this->sql = "SELECT * FROM gadiv_rel_user_team_capacity WHERE user_id = '".$user."' AND team_id = '".$team."' AND date >= '".$date_start."' AND date <= '".$date_end."'";		
			return $this->executeQuery();
		}
				
	
		# get the total capacity of one team in a defined period of time
		function getTeamCapacity($team,$date_start,$date_end){
			$this->sql = "SELECT sum( capacity ) AS total_cap FROM gadiv_rel_user_team_capacity WHERE team_id = '".$team."' AND date >= '".$date_start."' AND date <= '".$date_end."'";
			$result = $this->executeQuery();
			return $result[0]['total_cap'];
		}
		
		# saves standard availability for one user
		function saveMonthAvailability($user_id,$year,$month){
			$count_over_capacity = 0;
			$sql = "SELECT * FROM gadiv_rel_user_availability_week WHERE user_id = ".$user_id;
			$result = mysql_query($sql);
			$user = mysql_fetch_row($result);

			if($month != date('n',time())){
				$start_day = 1;
			} else {
				$start_day = date('j',time());
			}
			
			$month_start = mktime(0,0,0,$month,$start_day, $year);
			$end_day = date('t',$month_start);
			$first_day = date('N',$month_start);
			$x = $first_day;
			for($i=$start_day; $i <= $end_day; $i++){
				$sql = "DELETE FROM gadiv_rel_user_availability WHERE user_id = '".$user_id."' AND date = '".$year.'-'.$month.'-'.$i."'";
				mysql_query($sql);
				$sql = "INSERT INTO gadiv_rel_user_availability SET capacity = '".$user[$x]."', user_id = '".$user_id."', date = '".$year.'-'.$month.'-'.$i."'";
				mysql_query($sql);
				if($this->getCapacityToSavedAvailability($user_id,$year.'-'.$month.'-'.$i) > $user[$x]){
					$count_over_capacity++;
				}
				$x++;
				if($x == 8){
					$x = 1;
				}
			}
			
			return $count_over_capacity;
		}
		
		# get standard availability of one user
		function getUserAvailability($user_id,$day){
			$sql = "SELECT * FROM gadiv_rel_user_availability_week WHERE user_id = ".$user_id;
			$result = mysql_query($sql);
			$user = mysql_fetch_assoc($result);
			$user['availability'] = $user['monday'] + $user['tuesday'] + $user['wednesday'] + $user['thursday'] + $user['friday'] + $user['saturday'] + $user['sunday'];
			switch($day){
				case '7':
					return $user['availability'];
				break;
				case '1':
					return $user['monday'];
				break;
				case '2':
					return $user['tuesday'];
				break;
				case '3':
					return $user['wednesday'];
				break;
				case '4':
					return $user['thursday'];
				break;
				case '5':
					return $user['friday'];
				break;
				case '6':
					return $user['saturday'];
				break;
				case '0':
					return $user['sunday'];
				break;
			}
			return $user['availability'];
		}
		
		# check if users capacity is exceeded
		function getUserMarking($id){
			$sql = "SELECT marked FROM `gadiv_rel_user_availability_week` WHERE user_id = '".$id."'";
			$result = mysql_query($sql);
			$is = mysql_fetch_assoc($result);
			if($is['marked'] == 1){return true;}
			return false;
		}
	}
?>