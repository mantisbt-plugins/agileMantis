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


class gadiv_userstory extends gadiv_commonlib {
	
	# adds a user who monitors a bug
	function addBugMonitor( $p_user_id, $p_bug_id ) {
		bug_monitor( $p_bug_id, $p_user_id );
	}
	
	#	adds a bugnote to one tracker/userstory with an email as content
	function addBugNote( $p_bug_id, $p_user_id, $p_email, $p_privacy = true ) {
		$t_note = $p_email['subject'];
		$t_note .= '<br>';
		$t_note .= $p_email['message'];
		
		bugnote_add( $p_bug_id, $t_note, '0:00', false, 0, '', $p_user_id, false );
	}
	
	# get user story tasks
	function getUserStoryTasks( $us_id ) {
		$t_sql = "SELECT * FROM gadiv_tasks WHERE us_id = " . db_param( 0 );
		$t_params = array( $us_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	# get user story notices
	function getNotices( $p_bug_id ) {
		return bugnote_get_all_bugnotes( $p_bug_id );
	}
	
	# get user story sprint history 
	function getUserStorySprintHistory( $bug_id ) {
		$t_mantis_bug_history_table = db_get_table( 'mantis_bug_history_table' );
		
		$t_sql = "SELECT date_modified 
					FROM $t_mantis_bug_history_table 
					WHERE bug_id = " . db_param( 0 ) . " 
					AND field_name = 'Sprint' 
					ORDER BY date_modified DESC";
		$t_params = array( $bug_id );
		$sprint = $this->executeQuery( $t_sql, $t_params );
		return $sprint[0]['date_modified'];
	}
	
	# get amount of moved work from splitted user stories
	function getWorkMovedFromSplittedStories( $bugList, $date ) {
		$t_sql = "SELECT sum(work_moved) AS total_work_moved 
					FROM gadiv_rel_userstory_splitting_table 
					WHERE old_userstory_id IN ( " . $bugList . " ) 
					AND DATE LIKE " . db_param( 0 ) ;
					//TODO: . "	GROUP BY"-> function is unused!
		$t_params = array( "%" . $this->getNormalDateFormat($date) . "%" );
		$userstories = $this->executeQuery( $t_sql, $t_params );
		return $userstories[0]['total_work_moved'];
	}
}
?>