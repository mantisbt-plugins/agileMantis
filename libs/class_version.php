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


class gadiv_productVersion extends gadiv_commonlib {
	
	# get mantis version info by project id
	function getVersionInformation( $p_project_id, $p_version = "" ) {
		$t_version_id = version_get_id( $p_version, $p_project_id );
		$t_version_id = version_cache_row( $t_version_id, false );
		return $t_version_id;
	}
	
	# get all tracker from a certain version and status
	function getVersionTracker( $project_id, $version = " ", $status ) {
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		$t_sql = "SELECT count(*) AS tracker 
						FROM $t_mantis_bug_table 
						WHERE project_id=" . db_param( 0 ) . " 
						AND target_version=" . db_param( 1 ) . " 
						AND status IN(" . $status . ")
						GROUP BY project_id";
		$t_params = array( $project_id, $version );
		$number_of_tracker = $this->executeQuery( $t_sql, $t_params );
		return 0 + $number_of_tracker[0]['tracker'];
	}
	
	# get all user stories from a certain project and version
	function getVersionUserStories( $project_id, $version ) {
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		$this->getAdditionalProjectFields();
		
		$t_sql = "SELECT count(*) AS userstories 
						FROM $t_mantis_bug_table " . " 
						INNER JOIN $t_mantis_custom_field_string_table ON id = bug_id 
						WHERE project_id=" . db_param( 0 ) . " 
						AND target_version=" . db_param( 1 ) . " 
						AND field_id=" . db_param( 2 ) . " 
						AND value != ''" . "
						GROUP BY field_id";
		$t_params = array( $project_id, $version, $this->pb );
		$number_of_tracker = $this->executeQuery( $t_sql, $t_params );
		return 0 + $number_of_tracker[0]['userstories'];
	}
	
	# count number of user stories from a certain project and version
	function getNumberOfUserStories( $project_id, $version ) {
		$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		$this->getAdditionalProjectFields();
		
		$t_sql = "SELECT count(*) AS userstories 
					FROM $t_mantis_bug_table 
					INNER JOIN $t_mantis_custom_field_string_table ON id = bug_id 
					WHERE project_id=" . db_param( 0 ) . " 
					AND target_version = " . db_param( 1 ) . " 
					AND status < 80 
					AND field_id=" . db_param( 2 ) . " 
					AND value != ''" . " 
					GROUP BY field_id";
		$t_params = array( $project_id, $version, $this->pb );
		$total = $this->executeQuery( $t_sql, $t_params );
		return 0 + $total[0]['userstories'];
	}
}

?>