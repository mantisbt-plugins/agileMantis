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


class gadiv_projects extends gadiv_commonlib {
	
	# get project name by id and returns it
	function getProjectName( $p_project_id ) {
		return project_get_field( $p_project_id, 'name' );
	}
	
	function getProjectDescription ( $p_project_id ) {
		return project_get_field( $p_project_id, 'description' );
	}
	
	# delete a project from a product backlog
	function deleteProject( $backlog_id, $project_id ) {
		$t_sql = "DELETE FROM gadiv_rel_productbacklog_projects 
				WHERE pb_id=" . db_param( 0 ) . " 
				AND project_id=" . db_param( 1 );
		$t_params = array( $backlog_id, $project_id );
		db_query_bound( $t_sql, $t_params );
	}
	
	# fetch all mantis projects and put them in the right order.
	function getProjectWithHierachy() {
		$t_mantis_project_table = db_get_table( 'mantis_project_table' );
		$t_mantis_project_hierarchy_table = db_get_table( 'mantis_project_hierarchy_table' );
		
		$t_sql = "SELECT DISTINCT p.id, ph.parent_id, p.name, p.inherit_global, ph.inherit_parent 
					FROM $t_mantis_project_table p 
					LEFT JOIN $t_mantis_project_hierarchy_table ph ON ph.child_id = p.id 
					WHERE p.enabled = 1 
					ORDER BY p.name";
		$result = $this->executeQuery( $t_sql );
		
		foreach( $result as $num => $row ) {
			if( $row['parent_id'] == NULL ) {
				$id = 0;
			} else {
				$id = $row['parent_id'];
			}
			$project[$id][$row['id']] = $row['name'];
		}
		
		return $project;
	}
	
	# adds agileMantis custom fields to all projects where a product backlog is assigned
	function addAdditionalProjectFields( $project_id ) {
		$this->deleteAdditionalProjectFields( $project_id );
		$this->getAdditionalProjectFields();
		
		if( $project_id != "" ) {
			$this->addAdditionalProjectField( $this->bv, $project_id );
			$this->addAdditionalProjectField( $this->pb, $project_id );
			$this->addAdditionalProjectField( $this->sp, $project_id );
			$this->addAdditionalProjectField( $this->spr, $project_id );
			
			if( plugin_config_get( 'gadiv_presentable' ) == '1' ) {
				$this->addAdditionalProjectField( $this->pr, $project_id );
			}
			
			if( plugin_config_get( 'gadiv_ranking_order' ) == '1' ) {
				$this->addAdditionalProjectField( $this->ro, $project_id );
			}
			
			if( plugin_config_get( 'gadiv_technical' ) == '1' ) {
				$this->addAdditionalProjectField( $this->tech, $project_id );
			}
			
			if( plugin_config_get( 'gadiv_release_documentation' ) == '1' ) {
				$this->addAdditionalProjectField( $this->rld, $project_id );
			}
			
			if( plugin_config_get( 'gadiv_tracker_planned_costs' ) == '1' ) {
				$this->addAdditionalProjectField( $this->pw, $project_id );
			}
			
			$this->addAdditionalProjectField( $this->un, $project_id );
		}
		
		$this->bv = "";
		$this->pb = "";
		$this->sp = "";
		$this->ro = "";
		$this->pr = "";
		$this->spr = "";
		$this->tech = "";
		$this->rld = "";
		$this->pw = "";
		$this->un = "";
	}

	function addAdditionalProjectField( $p_field_id, $p_project_id ) {
		custom_field_link( $p_field_id, $p_project_id );
	}
	
	# when a project is deleted from a product backlog, all additional agileMantis custom fields will be deleted too.
	function deleteAdditionalProjectFields( $project_id ) {
		$this->project_id = $project_id;
		if( $this->backlog_project_is_unique( $project_id ) == true ) {
			$this->getAdditionalProjectFields();
			
			$this->deleteAdditionalProjectField( $this->bv, $project_id );
			$this->deleteAdditionalProjectField( $this->pb, $project_id );
			$this->deleteAdditionalProjectField( $this->sp, $project_id );
			$this->deleteAdditionalProjectField( $this->spr, $project_id );
			$this->deleteAdditionalProjectField( $this->ro, $project_id );
			$this->deleteAdditionalProjectField( $this->pr, $project_id );
			$this->deleteAdditionalProjectField( $this->tech, $project_id );
			$this->deleteAdditionalProjectField( $this->rld, $project_id );
			$this->deleteAdditionalProjectField( $this->pw, $project_id );
			$this->deleteAdditionalProjectField( $this->un, $project_id );
			
			$this->bv = "";
			$this->pb = "";
			$this->sp = "";
			$this->ro = "";
			$this->pr = "";
			$this->spr = "";
			$this->tech = "";
			$this->rld = "";
			$this->pw = "";
			$this->un = "";
		}
	}

	function deleteAdditionalProjectField( $p_field_id, $p_project_id ) {
		custom_field_unlink( $p_field_id, $p_project_id );
	}
	
	# get all projects in all product backlogs
	function getProjectsInBacklogs() {
		$t_mantis_project_table = db_get_table( 'mantis_project_table' );
		
		$t_sql = "SELECT DISTINCT(project_id) 
					FROM gadiv_rel_productbacklog_projects 
					LEFT JOIN $t_mantis_project_table ON project_id = id";
		return $this->executeQuery( $t_sql );
	}
	
	# check if a project is only in one product backlog
	function backlog_project_is_unique( $project_id ) {
		$t_sql = "SELECT count(*) AS projects 
					FROM gadiv_rel_productbacklog_projects 
					WHERE project_id=" . db_param( 0 ) . "
					GROUP BY project_id";
		$t_params = array( $project_id );
		$result = $this->executeQuery( $t_sql, $t_params );
		if( $result[0]['projects'] > 1 ) {
			return false;
		}
		return true;
	}
	
	# find team members of a specified product backlog
	# that have no access rights to a spefied project
	# Teammitglieder eines PBs finden, 
	# die keine Zugriffsrechte auf ein bestimmtes Projekt haben
	function get_user_with_no_accessrights( $pb_id, $project_id ) {
		$t_mantis_user_table = db_get_table( 'mantis_user_table' );
		$t_mantis_project_user_list_table = db_get_table( 'mantis_project_user_list_table' );
		
		$t_sql = "SELECT DISTINCT ut.id, ut.username, ut.realname
					FROM $t_mantis_user_table AS ut 
					JOIN gadiv_rel_team_user AS tu ON tu.user_id = ut.id 
					JOIN gadiv_teams AS teams ON teams.id = tu.team_id
					WHERE teams.pb_id=" . db_param( 0 ) . "
					AND tu.role IN (1, 2, 3) 
					AND ut.access_level <> 90 
					AND ut.id NOT IN (
						SELECT user_id
						FROM $t_mantis_project_user_list_table
						WHERE project_id=" . db_param( 1 ) . ");";
		$t_params = array( $pb_id, $project_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	// Alle Projekte, die dem Product Backlog eines Teams zugeordnet sind
	function get_projects_by_team_id( $team_id ) {
		$t_mantis_project_table = db_get_table( 'mantis_project_table' );
		
		$t_sql = "SELECT DISTINCT project.id, project.name
					FROM $t_mantis_project_table project
					JOIN gadiv_rel_productbacklog_projects rel ON rel.project_id = project.id
					JOIN gadiv_productbacklogs pb on pb.id = rel.pb_id
					JOIN gadiv_teams teams ON teams.pb_id = pb.id
					WHERE teams.id=" . db_param( 0 ) . ");";
		$t_params = array( $team_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
	
	// Alle Projekte, die dem Product Backlog eines Teams zugeordnet sind,
	// zu denen ein Benutzer keine Zugriffsrechte besitzt		
	function get_projects_by_team_id_where_user_has_no_access_rights( $team_id, $user_id ) {
		$t_mantis_project_table = db_get_table( 'mantis_project_table' );
		$t_mantis_project_user_list_table = db_get_table( 'mantis_project_user_list_table' );
		
		$t_sql = "SELECT DISTINCT project.id, project.name
					FROM $t_mantis_project_table project
					JOIN gadiv_rel_productbacklog_projects rel ON rel.project_id = project.id
					JOIN gadiv_productbacklogs pb on pb.id = rel.pb_id
					JOIN gadiv_teams teams ON teams.pb_id = pb.id
					WHERE teams.id=" . db_param( 0 ) . " 
					AND project.id NOT IN (
						SELECT project.id
						FROM $t_mantis_project_table project
						JOIN $t_mantis_project_user_list_table pul ON pul.project_id = project.id
						WHERE pul.user_id=" . db_param( 1 ) . ")";
		$t_params = array( $team_id, $user_id );
		return $this->executeQuery( $t_sql, $t_params );
	}
}
?>