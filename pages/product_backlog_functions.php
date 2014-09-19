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



# get current user information
$user_id = auth_get_current_user_id();
$teams = $agilemantis_team->countMemberTeams( $user_id );
$show_all_backlogs = false;
$lock_productbacklog = false;

# set current user rights and chose wether a chosen product backlog or chose product backlog page is shown
if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] && $_SESSION['AGILEMANTIS_ISMANTISUSER'] == false ) {
	$show_all_backlogs = true;
}
if( $_SESSION['AGILEMANTIS_ISMANTISUSER'] && $teams > 1 ) {
	$show_all_backlogs = true;
}

if( $teams == 0 ) {
	$show_all_backlogs = true;
}
if( $teams == 0 && $_SESSION['AGILEMANTIS_ISMANTISUSER'] &&
	 $_SESSION['AGILEMANTIS_ISMANTISADMIN'] == false ) {
	$lock_productbacklog = true;
}

if( $_POST['chose_product_backlog'] && $_POST['submit'] != "backlog" ) {
	$show_all_backlogs = true;
}
if( $_POST['productBacklogName'] != "" ) {
	$show_all_backlogs = false;
}
if( $_GET['productBacklogName'] != "" ) {
	$show_all_backlogs = false;
}

# add different config values for the product backlog
if( !config_is_set( 'show_only_us_without_storypoints', auth_get_current_user_id() ) ) {
	config_set( 'show_only_us_without_storypoints', 0, auth_get_current_user_id() );
}

if( !config_is_set( 'show_resolved_userstories', auth_get_current_user_id() ) ) {
	config_set( 'show_resolved_userstories', 0, auth_get_current_user_id() );
}

if( !config_is_set( 'show_closed_userstories', auth_get_current_user_id() ) ) {
	config_set( 'show_closed_userstories', 0, auth_get_current_user_id() );
}

if( !config_is_set( 'show_only_userstories_without_sprint', auth_get_current_user_id() ) ) {
	config_set( 'show_only_userstories_without_sprint', 0, auth_get_current_user_id() );
}

if( !config_is_set( 'show_only_project_userstories', auth_get_current_user_id() ) ) {
	config_set( 'show_only_project_userstories', 0, auth_get_current_user_id() );
}

if( !config_is_set( 'show_project_target_version', auth_get_current_user_id() ) ) {
	config_set( 'show_project_target_version', 0, auth_get_current_user_id() );
}

if( !config_is_set( 'current_user_product_backlog_filter', auth_get_current_user_id() ) ) {
	config_set( 'current_user_product_backlog_filter', '', auth_get_current_user_id() );
}

if( !config_is_set( 'current_user_product_backlog_filter_direction', auth_get_current_user_id() ) ) {
	config_set( 'current_user_product_backlog_filter_direction', 'ASC', auth_get_current_user_id() );
}

# check if available
if( plugin_config_get( 'gadiv_ranking_order' ) == 0 &&
	 config_get( 'current_user_product_backlog_filter', null, auth_get_current_user_id() ) ==
	 'rankingOrder' ) {
	config_set( 'current_user_product_backlog_filter', '', auth_get_current_user_id() );
	config_set( 'current_user_product_backlog_filter_direction', 'ASC', auth_get_current_user_id() );
}

if( plugin_config_get( 'gadiv_tracker_planned_costs' ) == 0 &&
	 config_get( 'current_user_product_backlog_filter_direction', null, auth_get_current_user_id() ) ==
	 'plannedWork' ) {
	config_set( 'current_user_product_backlog_filter', '', auth_get_current_user_id() );
	config_set( 'current_user_product_backlog_filter_direction', 'ASC', auth_get_current_user_id() );
}

# save / update filter checkbox values from product backlog
if( $_POST['action'] == 'save_product_backlog_filter' ) {
	updateMantisConfigFlag( 'show_only_us_without_storypoints', 
		$_POST['show_only_us_without_storypoints'] );
	updateMantisConfigFlag( 'show_resolved_userstories', $_POST['show_resolved_userstories'] );
	updateMantisConfigFlag( 'show_closed_userstories', $_POST['show_closed_userstories'] );
	updateMantisConfigFlag( 'show_only_userstories_without_sprint', 
		$_POST['show_only_userstories_without_sprint'] );
	updateMantisConfigFlag( 'show_only_project_userstories', 
		$_POST['show_only_project_userstories'] );
	updateMantisConfigFlag( 'show_project_target_version', $_POST['show_project_target_version'] );
}

function updateMantisConfigFlag( $key, $value ) {
	if( !empty( $value ) ) {
		config_set( $key, 1, auth_get_current_user_id() );
	} else {
		config_set( $key, 0, auth_get_current_user_id() );
	}
}
?>