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
	
	# set calendary hight
	$height = '400px';
	switch($amount_of_weeks){
		case '1':
			$height = '200';
		break;
		case '2':
			$height = '250';
		break;
		case '3':
			$height = '350';
		break;
		default:
			$height = '400';
		break;
	}
	
	# set calendary stylesheet information
?>
<style type="text/css">
	.breaker {
		clear				: both;
		margin				: 0;
		padding				: 0;
	}
	
	.fullcalendar {
		float				: left;
		margin-right		: 20px;
		width				: 385px;
		height				: <?php echo $height + 150?>px;
	}
	
	.calendar {
		margin-bottom		: 10px;
		height				: <?php echo $height?>px;
		width				: 385px;
	}

	.headline_month {
		background-color	: #427BD6;
		color				: #FFF;
		font-family			: Arial;
		font-weight			: bold;
		font-size			: 14px;
		margin-bottom		: 5px;
		text-align			: center;
		padding				: 10px;
		width				: 365px;
	}
	
	.headline_days {
		background-color	: #427BD6;
		float				: left;
		color				: #FFF;
		font-family			: Arial;
		font-weight			: bold;
		font-size			: 14px;
		text-align			: center;
		padding				: 10px;
		width				: 35px;
	}
	
	.after_month,
	.before_month,
	.current_month,
	.current_day {
		float				: left;
		color				: #000;
		font-family			: Arial;
		font-size			: 14px;
		padding				: 10px;
		height				: 30px;
		text-align			: center;
		width				: 35px;
	}
	
	.current_day {
		font-weight			: bold;
	}
	
	.after_month,
	.before_month {
		color:silver;
	}
	
	.dateField_disabled,
	.dateField {
		font-family			: Arial;
		font-size			: 10px;
		height				: 20px;
		width				: 35px;
		background-color	: #FFF;
		margin-top			: 2px;
		text-align			: center;
		border				: 1px solid #7F9DB9; 
	}
	
	.dateField_disabled {
		background-color	: #EBEBE4;
	}
	
</style>