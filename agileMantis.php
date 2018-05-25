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
# Copyright (C) 2012-2015 gadiv GmbH
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


# agileMantis plugin class
class agileMantisPlugin extends MantisPlugin {

	var $version;

	/**
	 * Plugin registration information, some will be shown on plugin overview.
	 *
	 * The required minimum MantisBT version can be specified too.
	 */
	function register() {
		$this->name = "agileMantis";
		$this->description = "Enables Scrum on your MantisBT-Installation";
		$this->page = "info";
		$this->version = "2.2.2";
		$this->requires = array( "MantisCore" => "1.2.5" );
		$this->author = "gadiv GmbH";
		$this->contact = "agileMantis@gadiv.de";
		$this->url = "http://www.gadiv.de";
	}

	/**
	 *  Overriding this function allows the plugin to set itself up,
	 *  include any necessary API‘s, declare or hook events, etc.
	 */
	function init() {

		// Get path to core folder
		$t_core_path = config_get_global( 'plugin_path' ) .
						plugin_get_current() .
						DIRECTORY_SEPARATOR .
						'core' .
						DIRECTORY_SEPARATOR;

		// Include constants
		require_once($t_core_path . 'config_api.php');

	}

	/**
	 * Setup of plugin settings.
	 *
	 * e.g. return array('setting1' => ON, 'user_count' => 99);
	 */
	function config() {

		return array(
			'gadiv_userstory_unit_mode' 	=> 'h',
			'gadiv_task_unit_mode' 			=> 'h',
			'gadiv_workday_in_hours' 		=> '8',
			'gadiv_storypoint_mode' 		=> 0,
			'gadiv_fibonacci_length' 		=> 12,
			'gadiv_sprint_length' 			=> 28,
			'gadiv_scrum' 					=> 0,
			'gadiv_daily_scrum' 			=> 0,
			'gadiv_presentable' 			=> 0,
			'gadiv_ranking_order' 			=> 0,
			'gadiv_technical' 				=> 0,
			'gadiv_release_documentation' 	=> 0,
			'gadiv_tracker_planned_costs' 	=> 0
		);
	}

	# agileMantis custom events
	function events () {
		return array (
			'EVENT_LOAD_TASKBOARD' 	=> EVENT_TYPE_EXECUTE,
			'EVENT_LOAD_DAILYSCRUM' => EVENT_TYPE_EXECUTE,
			'EVENT_LOAD_STATISTICS' => EVENT_TYPE_EXECUTE,
			'EVENT_LOAD_USERSTORY' 	=> EVENT_TYPE_EXECUTE,
			'EVENT_LOAD_SETTINGS'	=> EVENT_TYPE_EXECUTE,
			'EVENT_LOAD_THIRDPARTY' => EVENT_TYPE_EXECUTE
		);
	}

	/*
	 * Add event hooks for callback functions
	 */
	function hooks() {

		return array(
			'EVENT_MENU_MAIN' 				=> 	"event_add_structure",		// CHAIN
			'EVENT_LAYOUT_CONTENT_BEGIN' 	=> 	"event_add_page_action",	// EXECUTE
			"EVENT_REPORT_BUG_FORM" 		=> 	"event_report_bug_form",	// EXECUTE
			"EVENT_UPDATE_BUG_FORM" 		=> 	"event_update_bug_form",	// EXECUTE
			"EVENT_VIEW_BUG_DETAILS" 		=>	"event_view_bug_details",	// EXECUTE
			"EVENT_UPDATE_BUG" 				=> 	"event_update_bug",			// CHAIN
			"EVENT_REPORT_BUG" 				=> 	"event_report_bug",			// CHAIN
			"EVENT_BUG_ACTION" 				=> 	"event_bug_action",			// CHAIN
			"EVENT_LAYOUT_RESOURCES"		=>	"event_layout_resources",
			"EVENT_LAYOUT_CONTENT_END"		=>	"event_layout_content_end"
		);
	}


	/**
	 * Database schema database installation/update.
	 *
	 * The schema is divided in blocks. Each main array index is corresponding to the schema block.
	 * So main array index 0 is also schema block 0.
	 */

 	function schema() {

        if ( db_is_mysql() ) {

            /*******************
             * agileMantis 1.3.x
             *******************/
            return array(
                    // block #0: table 'gadiv_additional_user_fields'
                    array( 'CreateTableSQL',
                    array( 'gadiv_additional_user_fields' , "
                    user_id			I			NOTNULL UNSIGNED PRIMARY,
                    developer		L			NOTNULL DEFAULT '0',
                    participant 	L			NOTNULL DEFAULT '0',
                    administrator 	L			NOTNULL DEFAULT '0'
                    ",
                    array( "mysql" => "ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" ) ) ),

                    // block #1: table 'gadiv_daily_task_performance'
                    array( 'CreateTableSQL',
                    array( 'gadiv_daily_task_performance' , "
                    task_id		I				NOTNULL UNSIGNED,
                    user_id		I				NOTNULL UNSIGNED,
                    performed	N(6.2)			NOTNULL,
                    rest		N(6.2)			NOTNULL,
                    date		T				NOTNULL,
                    rest_flag	L				NOTNULL
                    ",
                    array( "mysql" => "ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" ) ) ),

                    // block #2: table 'gadiv_productbacklogs'
                    array( 'CreateTableSQL',
                    array( 'gadiv_productbacklogs' , "
                    id			I				NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                    name		C(255)			NOTNULL,
                    description	X				NOTNULL,
                    user_id		I				NOTNULL UNSIGNED
                    ",
                    array( "mysql" => "ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" ) ) ),

                    // block #3: table 'gadiv_rel_productbacklog_projects'
                    array( 'CreateTableSQL',
                    array( 'gadiv_rel_productbacklog_projects' , "
                    pb_id		I				NOTNULL UNSIGNED PRIMARY,
                    project_id	I				NOTNULL UNSIGNED PRIMARY
                    ",
                    array( "mysql" => "ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" ) ) ),

                    // block #4: table 'gadiv_rel_sprint_closed_information'
                    array( 'CreateTableSQL',
                    array( 'gadiv_rel_sprint_closed_information' , "
                    sprint_id			I		NOTNULL UNSIGNED PRIMARY,
                    count_user_stories	I		NOTNULL UNSIGNED,
                    count_task_sprint	I		NOTNULL UNSIGNED,
                    count_splitted_user_stories_sprint	I	NOTNULL UNSIGNED,
                    storypoints_sprint	N(6.2)	NOTNULL,
                    storypoints_in_splitted_user_stories	N(6.2) NOTNULL,
                    work_planned_sprint	N(6.2)	NOTNULL,
                    work_performed		N(6.2)	NOTNULL,
                    work_moved			N(6.2)	NOTNULL,
                    storypoints_moved	N(6.2)	NOTNULL,
                    count_developer_team	I	NOTNULL UNSIGNED,
                    total_developer_capacity	N(6.2) NOTNULL,
                    count_developer_team_task	I	NOTNULL UNSIGNED,
                    total_developer_capacity_task	N(6.2) NOTNULL
                    ",
                    array( "mysql" => "ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" ) ) ),

                    // block #5: table 'gadiv_rel_team_user'
                    array( 'CreateTableSQL',
                    array( 'gadiv_rel_team_user' , "
                    team_id				I		NOTNULL PRIMARY,
                    user_id				I		NOTNULL PRIMARY,
                    role				I1		NOTNULL PRIMARY
                    ",
                    array( "mysql" => "ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" ) ) ),

                    // block #6: table 'gadiv_rel_userstory_splitting_table'
                    array( 'CreateTableSQL',
                    array( 'gadiv_rel_userstory_splitting_table' , "
                    old_userstory_id	I		NOTNULL UNSIGNED PRIMARY,
                    new_userstory_id	I		NOTNULL UNSIGNED,
                    work_moved			N(6.2)	NOTNULL,
                    storypoints_moved	N(6.2)	NOTNULL,
                    date				T		NOTNULL
                    ",
                    array( "mysql" => "ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" ) ) ),

                    // block #7: table 'gadiv_rel_user_availability'
                    array( 'CreateTableSQL',
                    array( 'gadiv_rel_user_availability' , "
                    user_id				I		NOTNULL UNSIGNED PRIMARY,
                    date				D		NOTNULL PRIMARY,
                    capacity			N(4.2)	NOTNULL
                    ",
                    array( "mysql" => "ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" ) ) ),

                    // block #8: table 'gadiv_rel_user_availability_week'
                    array( 'CreateTableSQL',
                    array( 'gadiv_rel_user_availability_week' , "
                    user_id				I		NOTNULL UNSIGNED PRIMARY,
                    monday				N(4.2)	NOTNULL DEFAULT '0.00',
                    tuesday				N(4.2)	NOTNULL DEFAULT '0.00',
                    wednesday			N(4.2)	NOTNULL DEFAULT '0.00',
                    thursday			N(4.2)	NOTNULL DEFAULT '0.00',
                    friday				N(4.2)	NOTNULL DEFAULT '0.00',
                    saturday			N(4.2)	NOTNULL DEFAULT '0.00',
                    sunday				N(4.2)	NOTNULL DEFAULT '0.00',
                    marked				L		NOTNULL	DEFAULT '0'
                    ",
                    array( "mysql" => "ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" ) ) ),

                    // block #9: table 'gadiv_rel_user_team_capacity'
                    array( 'CreateTableSQL',
                    array( 'gadiv_rel_user_team_capacity' , "
                    user_id				I		NOTNULL UNSIGNED PRIMARY,
                    team_id				I		NOTNULL UNSIGNED PRIMARY,
                    date				D		NOTNULL PRIMARY,
                    capacity			N(4.2)	NOTNULL
                    ",
                    array( "mysql" => "ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" ) ) ),

                    // block #10: table 'gadiv_sprints'
                    array( 'CreateTableSQL',
                    array( 'gadiv_sprints' , "
                    id					I		NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                    team_id				I		NOTNULL UNSIGNED,
                    pb_id				I		NOTNULL UNSIGNED,
                    name				C(255)	NOTNULL,
                    description			X		NOTNULL,
                    status				I1		NOTNULL,
                    daily_scrum			L		NOTNULL,
                    start				D		NOTNULL,
                    commit				T		NOTNULL,
                    end					D		NOTNULL,
                    closed				T		NOTNULL,
                    unit_storypoints	I1		NOTNULL,
                    unit_planned_work	I1		NOTNULL,
                    unit_planned_task	I1		NOTNULL,
                    workday_length		N(6.2)	NOTNULL
                    ",
                    array( "mysql" => "ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" ) ) ),

                    // block #11: table 'gadiv_tasks'
                    array( 'CreateTableSQL',
                    array( 'gadiv_tasks' , "
                    id					I		NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                    us_id				I		NOTNULL UNSIGNED,
                    developer_id		I		NOTNULL UNSIGNED,
                    name				C(255)	NOTNULL,
                    description			X		NOTNULL,
                    status				I1		NOTNULL UNSIGNED,
                    planned_capacity	N(6.2)	NOTNULL DEFAULT '0.00',
                    performed_capacity	N(6.2)	NOTNULL DEFAULT '0.00',
                    rest_capacity		N(6.2)	NOTNULL DEFAULT '0.00',
                    unit				I1		NOTNULL,
                    daily_scrum			L		NOTNULL
                    ",
                    array( "mysql" => "ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" ) ) ),

                    // block #12: table 'gadiv_task_log'
                    array( 'CreateTableSQL',
                    array( 'gadiv_task_log' , "
                    task_id				I		NOTNULL UNSIGNED PRIMARY,
                    event				C(12)	NOTNULL	PRIMARY,
                    user_id				I		NOTNULL UNSIGNED,
                    date				T		NOTNULL
                    ",
                    array( "mysql" => "ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" ) ) ),

                    // block #13: table 'gadiv_teams'
                    array( 'CreateTableSQL',
                    array( 'gadiv_teams' , "
                    id					I		NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                    name				C(255)	NOTNULL	PRIMARY,
                    description			X		NOTNULL,
                    pb_id				I		NOTNULL UNSIGNED,
                    daily_scrum			L		NOTNULL
                    ",
                    array( "mysql" => "ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" ) ) ),

                    /*** Updates for agileMantis 1.3 (for expert addon) ***/

                    // block #14: add field 'expert' to table 'gadiv_additional_user_fields'
                    array( 'AddColumnSQL',
                    array( 'gadiv_additional_user_fields' , "
                    expert				L		NOTNULL
                    " ) ),

                    /*** Updates for agileMantis 2.0 ***/

                    // block #15 bit -> tinyint in table 'gadiv_additional_user_fields'
                    array( 'AlterColumnSQL',
                    array( 'gadiv_additional_user_fields' , "
                    developer			I1		NOTNULL DEFAULT '0',
                    participant 		I1		NOTNULL,
                    administrator 		I1		NOTNULL,
                    expert				I1		NOTNULL
                    " ) ),

                    // block #16 bit -> tinyInt in table 'gadiv_daily_task_performance'
                    array( 'AlterColumnSQL',
                    array( 'gadiv_daily_task_performance' , "
                    rest_flag			I1		NOTNULL
                    " ) ),

                    // block #17 bit -> tinyInt in table 'gadiv_rel_user_availability_week'
                    array( 'AlterColumnSQL',
                    array( 'gadiv_rel_user_availability_week' , "
                    marked				I1		NOTNULL	DEFAULT '0'
                    " ) ),

                    // block #18 bit -> tinyInt in table 'gadiv_sprints'
                    array( 'AlterColumnSQL',
                    array( 'gadiv_sprints' , "
                    daily_scrum			I1		NOTNULL
                    " ) ),

                    // block #19 bit -> tinyInt in table 'gadiv_tasks'
                    array( 'AlterColumnSQL',
                    array( 'gadiv_tasks' , "
                    daily_scrum			I1		NOTNULL
                    " ) ),

                    // block #20 bit -> tinyInt in table 'gadiv_teams'
                    array( 'AlterColumnSQL',
                    array( 'gadiv_teams' , "daily_scrum			I1		NOTNULL
                    " ) ),

                    // block #21 accessrights for teamuser in projects must be developer (55)
                    array( 'UpdateSQL',
                    array( 'mantis_project_user_list_table' , "
                    SET access_level=55
                    WHERE user_id IN (
				    SELECT id
				    FROM mantis_user_table
				    WHERE username LIKE 'Team-User-%')
                    " ) ),

                    /*** Updates for agileMantis 2.1 ***/

                    // block #22 change fieldname 'commit' to 'dispose' in table 'gadiv_sprints'
                    array( 'RenameColumnSQL',
                    array( 'gadiv_sprints' ,
                    "commit", "dispose", "commit				T		NOTNULL"
                    ) ),

                    // block #23 change fieldname 'end' to 'enddate' in table 'gadiv_sprints'
                    array( 'RenameColumnSQL',
                    array( 'gadiv_sprints' ,
                    "end", "enddate", "end					D		NOTNULL"
                    ) )

                );
            }

            if ( db_is_mssql() ) {

                /*******************
                * agileMantis 2.1.x
                *******************/
                return array(
                    // block #0: table 'gadiv_additional_user_fields'
                    array( 'CreateTableSQL',
                    array( 'gadiv_additional_user_fields' , "
                    user_id			I			NOTNULL UNSIGNED PRIMARY,
                    developer		I1			NOTNULL DEFAULT '0',
                    participant 	I1			NOTNULL,
                    administrator 	I1			NOTNULL,
                    expert			I1    		NOTNULL
                    " ) ),

                    // block #1: table 'gadiv_daily_task_performance'
                    array( 'CreateTableSQL',
                    array( 'gadiv_daily_task_performance' , "
                    task_id		I				NOTNULL UNSIGNED,
                    user_id		I				NOTNULL UNSIGNED,
                    performed	N(6.2)			NOTNULL,
                    rest		N(6.2)			NOTNULL,
                    date		T				NOTNULL,
                    rest_flag	I1				NOTNULL
                    " ) ),

                    // block #2: table 'gadiv_productbacklogs'
                    array( 'CreateTableSQL',
                    array( 'gadiv_productbacklogs' , "
                    id			I				NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                    name		C(255)			NOTNULL,
                    description	X				NOTNULL,
                    user_id		I				NOTNULL UNSIGNED
                    " ) ),

                    // block #3: table 'gadiv_rel_productbacklog_projects'
                    array( 'CreateTableSQL',
                    array( 'gadiv_rel_productbacklog_projects' , "
                    pb_id		I				NOTNULL UNSIGNED PRIMARY,
                    project_id	I				NOTNULL UNSIGNED PRIMARY
                    " ) ),

                    // block #4: table 'gadiv_rel_sprint_closed_information'
                    array( 'CreateTableSQL',
                    array( 'gadiv_rel_sprint_closed_information' , "
                    sprint_id			I		NOTNULL UNSIGNED PRIMARY,
                    count_user_stories	I		NOTNULL UNSIGNED,
                    count_task_sprint	I		NOTNULL UNSIGNED,
                    count_splitted_user_stories_sprint	I	NOTNULL UNSIGNED,
                    storypoints_sprint	N(6.2)	NOTNULL,
                    storypoints_in_splitted_user_stories	N(6.2) NOTNULL,
                    work_planned_sprint	N(6.2)	NOTNULL,
                    work_performed		N(6.2)	NOTNULL,
                    work_moved			N(6.2)	NOTNULL,
                    storypoints_moved	N(6.2)	NOTNULL,
                    count_developer_team	I	NOTNULL UNSIGNED,
                    total_developer_capacity	N(6.2) NOTNULL,
                    count_developer_team_task	I	NOTNULL UNSIGNED,
                    total_developer_capacity_task	N(6.2) NOTNULL
                    " ) ),

                   // block #5: table 'gadiv_rel_team_user'
                    array( 'CreateTableSQL',
                    array( 'gadiv_rel_team_user' , "
                    team_id				I		NOTNULL PRIMARY,
                    user_id				I		NOTNULL PRIMARY,
                    role				I1		NOTNULL PRIMARY
                    " ) ),

                    // block #6: table 'gadiv_rel_userstory_splitting_table'
                    array( 'CreateTableSQL',
                    array( 'gadiv_rel_userstory_splitting_table' , "
                    old_userstory_id	I		NOTNULL UNSIGNED PRIMARY,
                    new_userstory_id	I		NOTNULL UNSIGNED,
                    work_moved			N(6.2)	NOTNULL,
                    storypoints_moved	N(6.2)	NOTNULL,
                    date				T		NOTNULL
                    " ) ),

                    // block #7: table 'gadiv_rel_user_availability'
                    array( 'CreateTableSQL',
                    array( 'gadiv_rel_user_availability' , "
                    user_id				I		NOTNULL UNSIGNED PRIMARY,
                    date				D		NOTNULL PRIMARY,
                    capacity			N(4.2)	NOTNULL
                    " ) ),

                    // block #8: table 'gadiv_rel_user_availability_week'
                    array( 'CreateTableSQL',
                    array( 'gadiv_rel_user_availability_week' , "
                    user_id				I		NOTNULL UNSIGNED PRIMARY,
                    monday				N(4.2)	NOTNULL DEFAULT '0.00',
                    tuesday				N(4.2)	NOTNULL DEFAULT '0.00',
                    wednesday			N(4.2)	NOTNULL DEFAULT '0.00',
                    thursday			N(4.2)	NOTNULL DEFAULT '0.00',
                    friday				N(4.2)	NOTNULL DEFAULT '0.00',
                    saturday			N(4.2)	NOTNULL DEFAULT '0.00',
                    sunday				N(4.2)	NOTNULL DEFAULT '0.00',
                    marked				I1		NOTNULL	DEFAULT '0'
                    " ) ),

                    // block #9: table 'gadiv_rel_user_team_capacity'
                    array( 'CreateTableSQL',
                    array( 'gadiv_rel_user_team_capacity' , "
                    user_id				I		NOTNULL UNSIGNED PRIMARY,
                    team_id				I		NOTNULL UNSIGNED PRIMARY,
                    date				D		NOTNULL PRIMARY,
                    capacity			N(4.2)	NOTNULL
                    " ) ),

                    // block #10: table 'gadiv_sprints'
                    array( 'CreateTableSQL',
                    array( 'gadiv_sprints' , "
                    id					I		NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                    team_id				I		NOTNULL UNSIGNED,
                    pb_id				I		NOTNULL UNSIGNED,
                    name				C(255)	NOTNULL,
                    description			X		NOTNULL,
                    status				I1		NOTNULL,
                    daily_scrum			I1		NOTNULL,
                    start				D		NOTNULL,
                    dispose				T		NOTNULL,
                    enddate				D		NOTNULL,
                    closed				T		NOTNULL,
                    unit_storypoints	I1		NOTNULL,
                    unit_planned_work	I1		NOTNULL,
                    unit_planned_task	I1		NOTNULL,
                    workday_length		N(6.2)	NOTNULL
                    " ) ),

                    // block #11: table 'gadiv_tasks'
                    array( 'CreateTableSQL',
                    array( 'gadiv_tasks' , "
                    id					I		NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                    us_id				I		NOTNULL UNSIGNED,
                    developer_id		I		NOTNULL UNSIGNED,
                    name				C(255)	NOTNULL,
                    description			X		NOTNULL,
                    status				I1		NOTNULL UNSIGNED,
                    planned_capacity	N(6.2)	NOTNULL DEFAULT '0.00',
                    performed_capacity	N(6.2)	NOTNULL DEFAULT '0.00',
                    rest_capacity		N(6.2)	NOTNULL DEFAULT '0.00',
                    unit				I1		NOTNULL,
                    daily_scrum			I1		NOTNULL
                    " ) ),

                    // block #12: table 'gadiv_task_log'
                    array( 'CreateTableSQL',
                    array( 'gadiv_task_log' , "
                    task_id				I		NOTNULL UNSIGNED PRIMARY,
                    event				C(12)	NOTNULL	PRIMARY,
                    user_id				I		NOTNULL UNSIGNED,
                    date				T		NOTNULL
                    " ) ),

                    // block #13: table 'gadiv_teams'
                    array( 'CreateTableSQL',
                    array( 'gadiv_teams' , "
                    id					I		NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                    name				C(255)	NOTNULL	PRIMARY,
                    description			X		NOTNULL,
                    pb_id				I		NOTNULL UNSIGNED,
                    daily_scrum			I1		NOTNULL
                    " ) )

                );
            }
	}

	/**
	 * Will be executed, if the user hits the install link on the plugin
	 * overview page and before schema()
	 */
	function install() {
		$t_plugin_path = config_get_global('plugin_path') . plugin_get_current() . DIRECTORY_SEPARATOR;

 		// Install site key
 		plugin_config_set('gadiv_sitekey', md5(uniqid(rand(), true)));

 		$this->installConfigurationParams();

 		// Create custom fields
 		$this->create_custom_field( 'ProductBacklog',
 				array( 'possible_values'	=> '', 'type' => '6' ) ); // List
 		$this->create_custom_field('Sprint',
 				array( 'possible_values'	=> '', 'type' => '6' ) ); // List
 		$this->create_custom_field('Storypoints',
 				array( 'possible_values' => '', 'type' => '1' ) );  // Number
 		$this->create_custom_field('BusinessValue',
 				array( 'possible_values' => '', 'type' => '0' ) ); // Text
 		$this->create_custom_field('RankingOrder',
 				array( 'possible_values' => '', 'type' => '1' ) ); // Number
 		$this->create_custom_field('Presentable',
 				array( 'possible_values' => '1|2|3', 'type' => '6' ) );  // List
 		$this->create_custom_field('Technical',
 				array( 'possible_values' => 'Ja', 'type' => '5' ) ); // Checkbox
 		$this->create_custom_field('InReleaseDocu',
 				array( 'possible_values' => 'Ja', 'type' => '5' ) ); // Checkbox
 		$this->create_custom_field('PlannedWork',
 				array( 'possible_values' => '', 'type' => '1' ) );  // Number
 		$this->create_custom_field('PlannedWorkUnit',
 				array( 'possible_values' => '0|1|2|3', 'type' => '0',  'filter_by' => '0' ) );  // Text

 		// If old tables exists, that where not created by ADODB, the setting 'plugin_agileMantis_schema'
 		// needs to be set to the last block of the schema that was created without ADODB.
 		// e.g. if the last table created without ADODB was 'gadiv_teams', the plugin_agileMantis_schema
 		// needs to be set to the corresponding schema block. Only the blocks after that will be executed.
 		if ( plugin_config_get( 'schema', -1 ) == -1 ) {

            if ( db_is_mysql() ) {

                if ( $this->getDBVersion() == '2.0.0' ) {
                    // Version 2.0. is installed, set block to #21
                    plugin_config_set( 'schema', 21 );

                } else if ( db_field_exists('expert', 'gadiv_additional_user_fields' ) ) {
                    // Version 1.3 is installed, set block to #14
                    plugin_config_set( 'schema', 14 );

                } else if ( db_table_exists( 'gadiv_sprints' ) ) {
                    // Version < 1.3 is installed, set block to #13
                    plugin_config_set( 'schema', 13 );
                }

 			} else if ( db_is_mssql() ) {
                plugin_config_set( 'schema', -1 );
 			}
 		}

		return ( TRUE );
	}


    function getDBVersion() {
		$mantis_config_table = db_get_table( 'mantis_config_table' );

		$t_sql = "SELECT value AS db_version
					FROM $mantis_config_table
					WHERE config_id='plugin_agileMantis_gadiv_agilemantis_version'";

        $t_result = db_query_bound( $t_sql );
		$t_result_set = array();
		if( $t_result === TRUE ) {
			$t_result_set = true;
		} else {
			while( $t_rs = db_fetch_array( $t_result ) ) {
				if( !empty( $t_rs ) ) {
					$t_result_set[] = $t_rs;
				}
			}
		}

		return ( $t_result_set[0]['db_version'] );

	}
	/**
	 * Get's executed after the normal schema upgrade process has executed.
	 * This gives the plugin the chance to convert or normalize data after an upgrade
	 */
	function upgrade() {

		plugin_config_set( 'gadiv_agilemantis_version', $this->version = "2.2.2" );

		$this->installConfigurationParams();

		return ( TRUE );
	}

	/**
	 * Will be executed after the normal uninstallation process, and should
	 * handle such operations as reverting database schemas, removing unnecessary data,
	 * etc. This callback should be used only if Mantis would break when this plugin
	 * is uninstalled without any other actions taken, as users may not want to lose
	 * data, or be able to re-install the plugin later.
	 */
	function uninstall() {

		return ( TRUE );
	}

	/********************************************
	 * Callbacks section starts here            *
	 ********************************************/

	# add agileMantis plugin functions to bug report page
	# - adding agileMantis custom Fields
	# - adding save functions
	function event_report_bug_form( $p_event ) {
		global $agilemantis_sprint;
		global $agilemantis_pb;
		global $agilemantis_commonlib;

		// Only projects with agilMantis backlog
		if( !$agilemantis_commonlib->projectHasBacklogs( helper_get_current_project() ) ) {
			return;
		}

		if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] == 1
				|| $_SESSION['AGILEMANTIS_ISMANTISUSER'] == 1 ) {

			$ppid = helper_get_current_project();
			$pbl = $agilemantis_pb->getProjectProductBacklogs( $ppid );
			$s = $agilemantis_sprint->getSprints();

				echo '
					<tr '.helper_alternate_class().'>
						<td class="custom_field_form">Business Value</td>
						<td colspan="5">
							<input type="text" class="custom_field_form_width"
								name="businessValue" value="'.$story['businessValue'].'">
						</td>
					</tr>
					';
				if( plugin_config_get( 'gadiv_ranking_order' ) == '1'
						&& $agilemantis_sprint->customFieldIsInProject( "RankingOrder" ) == true ) {
					echo '
						<tr '.helper_alternate_class().'>
							<td class="custom_field_form">'.
							plugin_lang_get( 'RankingOrder' ).'</td>
							<td colspan="5">
								<input type="text" class="custom_field_form_width"
									name="rankingorder" value="">
							</td>
						</tr>
					';
				}
				echo '
					<tr '.helper_alternate_class().'>
						<td class="custom_field_form">Story Points</td>
						<td colspan="5">';

				if( plugin_config_get( 'gadiv_storypoint_mode' ) == 1 ) {
					echo '<input type="text" class="custom_field_form_width"
								name="storypoints" value="'.
							$story['storypoints'].'">';
				} else {
					echo '<select name="storypoints" class="custom_field_form_width">';
					echo '<option value=""></option>';
					$agilemantis_pb->getFibonacciNumbers( $story['storypoints'] );
					'</select>';
				}
				echo '</td>
					</tr>
					';
				if( plugin_config_get( 'gadiv_tracker_planned_costs') == '1'
						&& $agilemantis_sprint->customFieldIsInProject( "PlannedWork" ) == true ) {
					echo '
						<tr '.helper_alternate_class().'>
							<td class="custom_field_form">'.
									plugin_lang_get( 'PlannedWork' ).' ('.
									plugin_config_get( 'gadiv_userstory_unit_mode' ).')</td>
							<td colspan="5">
								<input type="text" class="custom_field_form_width"
									name="plannedWork" value="'.
								$story['plannedWork'].'">
							</td>
						</tr>
					';
				}
				echo '
					<tr '.helper_alternate_class().'>
						<td class="custom_field_form">Product Backlog</td>
						<td colspan="5">
								<select name="backlog" class="custom_field_form_width" '.$disabled.'>';?>
									<option value="">
									<?php echo plugin_lang_get( 'view_issue_chose_product_backlog' )?></option>
									<?php foreach( $pbl AS $num => $row ) { ?>
										<option value="<?php echo $row['name'] ?>"
									<?php if($row['name']==$story['name'] ) {
											echo 'selected';
										}?>>
									<?php echo $row['name']?></option>
									<?php }?>
									<?php echo '
								</select>
							</td>
						</tr>
					';
					if( plugin_config_get( 'gadiv_presentable' ) == '1'
						&& $agilemantis_sprint->customFieldIsInProject( "Presentable" ) == true ) {
						echo '
						<tr '.helper_alternate_class().'>
							<td class="custom_field_form">'.
								plugin_lang_get( 'Presentable' ).'</td>
							<td colspan="5">
								<select name="presentable" class="custom_field_form_width">
									<option value="3">'.
										plugin_lang_get( 'view_issue_non_presentable' ).'</option>
									<option value="1">'.
										plugin_lang_get( 'view_issue_technical_presentable' ).'</option>
									<option value="2">'.
										plugin_lang_get( 'view_issue_functional_presentable' ).'</option>
								</select>
							</td>
						</tr>
						';
					}
					if( plugin_config_get( 'gadiv_technical' ) == '1'
						&& $agilemantis_sprint->customFieldIsInProject( "Technical" ) == true ) {
						echo '
						<tr '.helper_alternate_class().'>
							<td class="custom_field_form">'.
							plugin_lang_get( 'Technical' ).'</td>
							<td colspan="5">
								<input type="checkbox" style="width:10px;" name="technical" value="1">
							</td>
						</tr>
					';
					}
					if( plugin_config_get( 'gadiv_release_documentation' ) == '1'
					&& $agilemantis_sprint->customFieldIsInProject( "inReleaseDocu" ) == true ) {
						echo '
						<tr '.helper_alternate_class().'>
							<td class="custom_field_form">'.
							plugin_lang_get( 'InReleaseDocu' ).'</td>
							<td colspan="5">
								<input type="checkbox" style="width:10px;" name="inReleaseDocu" value="1">
							</td>
						</tr>
					';
					}
 			}
		}

		# add agileMantis plugin functions to update issue page
		# - adding agileMantis custom Fields
		# - including agile_mantis_custom_fields_inc.php
		function event_update_bug_form( $p_event, $p_project_id ) {
			global $agilemantis_sprint;
			global $agilemantis_pb;
			global $agilemantis_commonlib;

			// Only projects with agilMantis backlog
			if( !$agilemantis_commonlib->projectHasBacklogs( helper_get_current_project() ) ) {
				return;
			}

			if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] == 1
					|| $_SESSION['AGILEMANTIS_ISMANTISUSER'] == 1 ) {

				$agilemantis_pb->setUserStoryUnit( $p_project_id,
							plugin_config_get( 'gadiv_userstory_unit_mode' ) );

				if( $agilemantis_sprint->getUserStoryStatus( $p_project_id ) < 80 ) {
					$disabled = '';
					$readonly = '';
				} else {
					$disabled = 'disabled';
					$readonly = 'readonly';
				}

				$story = $agilemantis_pb->checkForUserStory( $p_project_id );

				bug_update_date( $p_project_id );
				$s = $agilemantis_sprint->getBacklogSprints( $story['name'] );

				$pbl = $agilemantis_pb->getProjectProductBacklogs( helper_get_current_project() );

				require_once( AGILEMANTIS_CORE_URI."agile_mantis_custom_fields_inc.php" );
			}
		}

		# add agileMantis plugin functions to view issue page
		# - adding agileMantis custom Fields
		# - adding additonal buttons (Save & Edit Task)
		# - including agile_mantis_custom_fields_inc.php
		function event_view_bug_details ( $p_event , $p_project_id ) {
			global $agilemantis_sprint;
			global $agilemantis_pb;
			global $agilemantis_commonlib;

			// Only projects with agilMantis backlog
			if( !$agilemantis_commonlib->projectHasBacklogs( helper_get_current_project() ) ) {
				return;
			}

			if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] == 1
					|| $_SESSION['AGILEMANTIS_ISMANTISUSER'] == 1 ) {

				if( $_POST['saveValues'] ) {
					$agilemantis_pb->setCustomFieldValues( $p_project_id );
					bug_update_date( $p_project_id );

					if( (int) $_GET['bug_id'] ) {
						header( 'Location:'.$_SERVER['PHP_SELF'].'?bug_id='.$p_project_id.'&save=true' );
					} else {
						header( 'Location:'.$_SERVER['PHP_SELF'].'?id='.$p_project_id.'&save=true' );
					}

					email_generic( $p_project_id, 'updated',
								'email_notification_title_for_action_bug_updated' );
				}

				$pbl = $agilemantis_pb->getProjectProductBacklogs( helper_get_current_project() );
				$story = $agilemantis_pb->checkForUserStory( $p_project_id );
				$s = $agilemantis_sprint->getBacklogSprints( $story['name'] );

				// check wether bugnotes are available or not
				$t_bugnotes = bugnote_get_all_bugnotes( $p_project_id );
				$t_amount_bugnotes = count($t_bugnotes);

				// activate or disable bugnotes link
				$bugnotes_disable = '';
				if($t_amount_bugnotes > 0){
					$bugnotes_disable = '<a href="#bugnotes">'.plugin_lang_get('view_issue_look_through_notes').'</a>';
				}

				$pb_name = $story['name'];
				$sprint_name = $story['sprint'];
				$disable_sprint_button = '';
				if( $sprint_name == "" ) {
					$disable_sprint_button = 'disabled';
				} else {
					$page_backlog = plugin_page( "sprint_backlog.php" );
				}
				require_once( AGILEMANTIS_CORE_URI."agile_mantis_custom_fields_inc.php" );

				if( $_GET['save'] == true ) {
					$hinweis = '<span class="message_ok">'.
								plugin_lang_get( 'view_issue_successfully_saved' ).'</span>';
				} else {
					$hinweis = '';
				}

				if( $story['name'] == "" ) {
					$task_disable = 'disabled';
				}

				echo '
					<tr '.helper_alternate_class().'>
						<td class="custom_field_form">agileMantis-'.
							plugin_lang_get( 'common_actions' ).'</td>
						<td colspan="5">
							<input type="submit" name="saveValues" value="'.
							plugin_lang_get( 'view_issue_save_infos' ).'">
							</form>
							<form action="'.plugin_page("task_page.php").'&us_id='.
								$p_project_id.'" method="post">
								<input type="submit" value="'.
								plugin_lang_get( 'view_issue_edit_tasks' ).'" '.$task_disable.'>
							</form>
							<form action="'.plugin_page("product_backlog.php").'" method="post">
								<input type="submit" value="'.
								plugin_lang_get( 'view_issue_goto_product_backlog' ).'" '.$task_disable.'>
								<input type="hidden" name="productBacklogName" value="'.$pb_name.'">
							</form>
							<form action="'.$page_backlog.'" method="post">
								<input type="submit" value="'.
								plugin_lang_get( 'view_issue_goto_sprint_backlog' ).'" '.$disable_sprint_button.'>
								<input type="hidden" name="sprintName" value="'.$sprint_name.'">
							</form>
							'.$bugnotes_disable.'
							'.$hinweis.'
						</td>
					</tr>
				';
			}
		}

		# add agileMantis plugin functions after sending bug data to database when a bug is reported
		# - adding custom field values to mantis and agilemantis tables
		function event_report_bug( $p_bug_event, $p_bug_data ) {
			global $agilemantis_pb;
			global $agilemantis_commonlib;

			// Only projects with agilMantis backlog
			if( !$agilemantis_commonlib->projectHasBacklogs( helper_get_current_project() ) ) {
				return;
			}

			$bug_id = $p_bug_data->id;

			if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] == 1
					|| $_SESSION['AGILEMANTIS_ISMANTISUSER'] == 1 ) {



				# set new user story unit
				if( plugin_config_get( 'gadiv_userstory_unit_mode') == 'keine' ) {
					$agilemantis_pb->setUserStoryUnit( $bug_id, '' );
				} else {
					$agilemantis_pb->setUserStoryUnit( $bug_id,
							plugin_config_get( 'gadiv_userstory_unit_mode' ) );
				}

				# save custom field values in agileMantis tables
				$agilemantis_pb->setCustomFieldValues($bug_id);

				# do further checks on planned work
				$_POST['plannedWork'] = str_replace( ',', '.', $_POST['plannedWork'] );

				if( is_numeric( $_POST['plannedWork'] ) ) {
					$agilemantis_pb->AddPlannedWork(
						$bug_id, sprintf( "%.2f",$_POST['plannedWork'] ) );
				}

				if( empty( $_POST['plannedWork'] ) ) {
					$agilemantis_pb->AddPlannedWork( $bug_id,$_POST['plannedWork'] );
				}
			}
		}

		# add agileMantis plugin functions after sending bug data to database when a bug is updated
		# - adding custom field values to mantis and agilemantis tables
		function event_update_bug( $p_bug_event, $p_bug_data, $p_bug_id ) {
						global $agilemantis_pb;
			global $agilemantis_commonlib;

			$t_product_owner = "";
			$t_handler_id = 0;
			$t_product_backlog_id = 0;
			$t_team_id = 0;
			$f_bug_id = 0;

			// Only projects with agilMantis backlog
			if( !$agilemantis_commonlib->projectHasBacklogs( helper_get_current_project() ) ) {
				return;
			}

			$request = array_merge( $_GET, $_POST );

			if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] == 1
					|| $_SESSION['AGILEMANTIS_ISMANTISUSER'] == 1 ) {

				if( isset( $_POST['backlog'] ) || isset( $_POST['storypoints'] )
						|| isset( $_POST['businessValue'] ) || isset( $_POST['rankingorder'] )
						|| isset( $_POST['technical'] ) 	|| isset( $_POST['presentable'] )
						|| isset( $_POST['inReleaseDocu'] ) || isset( $_POST['sprint'] ) ) {

					$f_bug_id = (int) $_POST['bug_id'];
					$agilemantis_pb->setCustomFieldValues( $f_bug_id );

					# change Product Backlog
					if( $_POST['old_product_backlog'] != $_POST['backlog'] && $_POST['backlog'] != "" ) {
						$p_bug_data->handler_id = $_SESSION['tracker_handler'];
						$p_bug_data->status = 50;
					}

					# change back to Team User if no Product Backlog is selected
					if( $_POST['old_product_backlog'] != $_POST['backlog'] && $_POST['backlog'] == "" ) {

						$t_product_backlog_id = $agilemantis_pb->get_product_backlog_id( $_POST['old_product_backlog'] );

						if( $agilemantis_pb->count_productbacklog_teams( $t_product_backlog_id ) > 0 ) {
							$t_team_id = $agilemantis_pb->getTeamIdByBacklog( $t_product_backlog_id );
							$t_product_owner = $agilemantis_pb->getProductOwner( $t_team_id );
							$t_handler_id = user_get_id_by_name( $t_product_owner );
						}

						$p_bug_data->handler_id = $t_handler_id;
					}
				}
			}
			return $p_bug_data;
		}

		# add additonal agileMantis page action
		# - checks which rights does the current user have
		function event_add_page_action( $p_event ) {
			global $agilemantis_commonlib;

			unset( $_SESSION['bug'] );
			unset( $_SESSION['custom_field'] );
			unset( $_SESSION['custom_field_id'] );

			$user = $agilemantis_commonlib->getAdditionalUserFields( auth_get_current_user_id() );

			# unset buglist cookie
			if( isset($_GET['page']) && !stristr($_GET['page'],'assume_userstories') ) {
				setcookie( 'BugListe', '', time() - 6410) ;
			}

			$t_is_mantis_admin = 0;
			if ($user[0]['administrator'] == 1) {
				$t_is_mantis_admin = 1;
			}

			$t_is_mantis_user = 0;
			if ($user[0]['developer'] == 1 || $user[0]['participant'] == 1) {
				$t_is_mantis_user = 1;
			}

			$_SESSION['AGILEMANTIS_ISMANTISADMIN'] = $t_is_mantis_admin;
			$_SESSION['AGILEMANTIS_ISMANTISUSER'] = $t_is_mantis_user;

			# additional bug update functionality
			if( isset($_SESSION['event']) && $_SESSION['event'] == 'EVENT_UPDATE_BUG' ) {
				$f_bug_id = $_SESSION['tracker_id'];
				$handler_id = $_SESSION['tracker_handler'];

				if( !empty( $f_bug_id ) ) {
					if( (int) $_GET['bug_id'] ) {
						header( 'Location:'.$_SERVER['PHP_SELF'].'?bug_id='.$f_bug_id );
					} else {
						header( 'Location:'.$_SERVER['PHP_SELF'].'?id='.$f_bug_id );
					}
				}
			}

			unset( $_SESSION['event'] );
			unset( $_SESSION['tracker_id'] );
			unset( $_SESSION['tracker_handler'] );
			if( !empty( $_GET['bug_arr'] ) && stristr( $_GET['action'], 'custom_' ) ) {
				$custom_field = str_replace( 'custom_field_','',$_GET['action'] );
				$_SESSION['custom_field_id'] = $custom_field;
				foreach( $_GET['bug_arr'] AS $num => $row ) {
					$_SESSION['custom_field'][$row]
						= $agilemantis_commonlib->getCustomFieldValueById( $row, $custom_field );
					$_SESSION['bug'][$num] = $row;
				}
			}
		}

		# add additional agileMantis functions when performing a bug action
		function event_bug_action ( $p_event, $p_action, $p_bug_id ) {
			global $agilemantis_pb;
			global $agilemantis_commonlib;
			global $agilemantis_sprint;

			// Only projects with agilMantis backlog
			if( !$agilemantis_commonlib->projectHasBacklogs( helper_get_current_project() ) ) {
				return;
			}

			$agilemantis_commonlib->getAdditionalProjectFields();

			$t_custom_field_id = $_SESSION['custom_field_id'];

			$pb_id 			= $agilemantis_commonlib->getProductBacklogIDByBugId( $p_bug_id );
			$list_sprints 	= $agilemantis_commonlib->getSprintsByBacklogId( $pb_id );
			$current_sprint = $agilemantis_commonlib->getSprintByBugId( $p_bug_id );

			$t_custom_field_value = $_SESSION['custom_field'][$p_bug_id];
			if( !$t_custom_field_value ) {
				$t_custom_field_value = '';
			}

			$t_status = bug_get_field( $p_bug_id, 'status' );

			# restore story points value
			if( $t_custom_field_id == $agilemantis_commonlib->sp ) {
				if( $current_sprint[0]['status'] > 1 || $pb_id == 0 || $t_status >= 80) {
					$agilemantis_commonlib->restoreCustomFieldValue($p_bug_id, $t_custom_field_id, $t_custom_field_value );
				}
			}

			# restore product backlog value
			if( $t_custom_field_id == $agilemantis_commonlib->pb ) {
				$pbl = $agilemantis_commonlib->getProjectProductBacklogs(
												helper_get_current_project() );
				$do_not_reset = false;
				if( !empty( $pbl ) ) {
					foreach( $pbl AS $key => $value ) {
						if( $value['pb_id'] == $pb_id ) {
							$do_not_reset = true;
						}
					}
				}

				$value_resettet = false;
				if( $current_sprint[0]['name'] != ''
						|| $pb_id == 0
						|| empty($pbl)
						|| $do_not_reset == false ) {

					$agilemantis_commonlib->restoreCustomFieldValue(
										$p_bug_id,
										$t_custom_field_id,
										$t_custom_field_value );

					$value_resettet = true;
				}

				if( empty( $t_custom_field_value ) && $value_resettet == false ) {
					$agilemantis_commonlib->setTrackerStatus( $p_bug_id, 50 );
					$agilemantis_commonlib->id = $pb_id;
					$backlog = $agilemantis_commonlib->getSelectedProductBacklog();
					$agilemantis_commonlib->updateTrackerHandler(
								$p_bug_id , $backlog[0]['user_id'] , $pb_id );
				}

			}

			if( $t_custom_field_id == $agilemantis_commonlib->spr ) {
				if( empty( $list_sprints ) ) {
					$agilemantis_commonlib->restoreCustomFieldValue(
						$p_bug_id, $t_custom_field_id, $t_custom_field_value );
				}

				# old sprint information
				$agilemantis_commonlib->sprint_id = $t_custom_field_value;
				$sprintInfo = $agilemantis_sprint->getSprintById();

				if( $current_sprint[0]['pb_id'] != $pb_id ) {
					$agilemantis_commonlib->restoreCustomFieldValue(
						$p_bug_id, $t_custom_field_id, $t_custom_field_value );
				}

				if( $current_sprint[0]['status'] > 1 || $pb_id == 0 || $t_status >= 80 ) {
					$agilemantis_commonlib->restoreCustomFieldValue(
						$p_bug_id, $t_custom_field_id, $t_custom_field_value );
				}
			}

			# update bug date
			bug_update_date( $p_bug_id );

		}

		# add menu items to mantis main menu between "Summary" and "Manage"
		function event_add_structure() {
			global $agilemantis_commonlib;
			global $agilemantis_sprint;

			$user = $agilemantis_commonlib->getAdditionalUserFields( auth_get_current_user_id() );
			$menu = array();

			# add product backlog menu item
			if( $user[0]['participant'] == 1
					|| $user[0]['developer'] == 1
					|| $user[0]['administrator'] == 1 ) {

				$menu[2] =  '<a href="' .
						plugin_page("product_backlog.php") .
						'" class="agile_menu">Product Backlog</a>';
			}

			# add sprint backlog or taskboard menu item
			if( $user[0]['participant'] == 1
					|| $user[0]['developer'] == 1
					|| $user[0]['administrator'] == 1 ) {

					$menu[0] =  '<a href="' . plugin_page( "sprint_backlog.php" ) .
						'" class="agile_menu">Sprint Backlog</a>';

			}
			
			# add agileMantis menu item
			if( current_user_is_administrator() || $user[0]['administrator'] == 1 ) {
				$menu[3] =  '<a href="' . plugin_page( "info.php" ) .
					'" class="agile_menu">agileMantis</a>';
			}

			return $menu;
		}

		# adds a separate footer at the end of each agileMantis page
		function event_layout_content_end() {
			global $agilemantis_commonlib;

			if( isset( $_GET['page'] ) &&
					( stristr( $_GET['page'], 'agileMantis' )
					|| stristr( $_GET['page'],'sprint_backlog' )
					|| stristr( $_GET['page'],'taskboard' )
					|| stristr( $_GET['page'],'daily_scrum_meeting' )
					|| stristr( $_GET['page'],'statistics' ) ) ) {

				echo '<div style="clear:both;"></div>';
				echo '<br>';
				echo '<div align="center"><a href="https://sourceforge.net/p/agilemantis/wiki/Home/" '.
						'target="_blank">agileMantis-Wiki</a></div>';
				echo '<div class="table-container"><table border="0" width="100%" cellspacing="0" ' .
						'cellpadding="0"><tr valign="top"><td>';
				echo '<a href="http://www.gadiv.de/de/opensource/agilemantis/agilemantisen.html" ' .
					  'target="_blank">agileMantis '.
						$this->version.'</a><br>';
				echo '<a href="'. plugin_page( 'info.php' ) .
						'" target="_blank">Copyright © 2012-'.date('Y').
						' gadiv GmbH</a> - <a href="http://gadiv.de" target="_blank">www.gadiv.de</a><br>';
				echo '<a href="mailto:agileMantis@gadiv.de">agileMantis@gadiv.de</a>';
				echo '</td><td valign="middle">', "\n\t", '<div align="right">';
				echo '<a href="http://www.gadiv.de/de/opensource/agilemantis/agilemantisen.html" ' .
					 'title="agileMantis auf gadiv-Webseite" target="_blank"><img src="'.
					 AGILEMANTIS_PLUGIN_URL.'images/agilemantis_logo.gif" width="32" height="32" ' .
					 'alt="gadiv GmbH Logo" border="0"/></a>';
				echo '', "\n", '</div></td></tr></table></div>', "\n";
				echo "\t", '<hr size="1" />', "\n";
			}
		}

		function event_layout_resources() {

			echo '<link rel="stylesheet" href="'.AGILEMANTIS_PLUGIN_URL.'css/agileMantis.css">';
			echo '<link rel="stylesheet" href="'.AGILEMANTIS_PLUGIN_URL.'css/jquery-ui.css">';
			echo '<script src="' . AGILEMANTIS_PLUGIN_URL . 'js/jquery-1.9.1.js"></script>';
			echo '<script src="' . AGILEMANTIS_PLUGIN_URL . 'js/jquery-ui.js"></script>';
			echo '<script src="' . AGILEMANTIS_PLUGIN_URL . 'js/agileMantisActions.js"></script>';
		}

		/**
		 * Creates a custom field if it does not exist.
		 * The settings of the custom field will be updates in any case.
		 *
		 * @param unknown $p_field_name Name of the
		 * @param unknown $p_def_array
		 */
		function create_custom_field( $p_field_name, $p_def_array ) {
			$p_def_array['name'] = $p_field_name;
			$p_def_array['default_value'] 	= '';
			$p_def_array['access_level_r'] 	= '55';
			$p_def_array['access_level_rw'] = '55';
			$p_def_array['display_report'] 	= '0';
			$p_def_array['display_update'] 	= '0';
			$p_def_array['filter_by'] 		= '1';

			$t_field_id = custom_field_get_id_from_name( $p_field_name );

			if ( !$t_field_id ) {
				// Field does not exist yet, create it.
				$t_field_id = custom_field_create( $p_field_name );
				// Update field settings
				custom_field_update( $t_field_id, $p_def_array );
			}
		}

		function installConfigurationParams(){

			if( !config_is_set( 'plugin_agileMantis_gadiv_workday_in_hours' ) ) {
				config_set( 'plugin_agileMantis_gadiv_workday_in_hours', 8 );
			}

			if( !config_is_set( 'plugin_agileMantis_gadiv_sprint_length' ) ) {
				config_set( 'plugin_agileMantis_gadiv_sprint_length', 28 );
			}

			if( !config_is_set( 'plugin_agileMantis_gadiv_storypoint_mode' ) ) {
				config_set( 'plugin_agileMantis_gadiv_storypoint_mode', 0 );
			}

			if( !config_is_set( 'plugin_agileMantis_gadiv_fibonacci_length' ) ) {
				config_set( 'plugin_agileMantis_gadiv_fibonacci_length', 10 );
			}

			if( !config_is_set( 'plugin_agileMantis_gadiv_show_storypoints' ) ) {
				config_set( 'plugin_agileMantis_gadiv_show_storypoints', 0 );
			}

			if( !config_is_set( 'plugin_agileMantis_gadiv_task_unit_mode' ) ) {
				config_set( 'plugin_agileMantis_gadiv_task_unit_mode', 'h' );
			}

			if( !config_is_set( 'plugin_agileMantis_gadiv_userstory_unit_mode' ) ) {
				config_set( 'plugin_agileMantis_gadiv_userstory_unit_mode', 'h' );
			}
			
			if( !config_is_set( 'plugin_agileMantis_gadiv_daily_scrum' ) ) {
				config_set( 'plugin_agileMantis_gadiv_daily_scrum', 0 );
			}

			if( !config_is_set( 'plugin_agileMantis_gadiv_ranking_order' ) ) {
				config_set( 'plugin_agileMantis_gadiv_ranking_order', 0 );
			}

			if( !config_is_set( 'plugin_agileMantis_gadiv_show_rankingorder' ) ) {
				config_set( 'plugin_agileMantis_gadiv_show_rankingorder', 0 );
			}

			if( !config_is_set( 'plugin_agileMantis_gadiv_presentable' ) ) {
				config_set( 'plugin_agileMantis_gadiv_presentable', 0 );
			}

			if( !config_is_set( 'plugin_agileMantis_gadiv_release_documentation' ) ) {
				config_set( 'plugin_agileMantis_gadiv_release_documentation', 0 );
			}

			if( !config_is_set( 'plugin_agileMantis_gadiv_technical' ) ) {
				config_set( 'plugin_agileMantis_gadiv_technical', 0 );
			}

			if( !config_is_set( 'plugin_agileMantis_gadiv_tracker_planned_costs' ) ) {
				config_set( 'gadiv_tracker_planned_costs', 0 );
			}
		}

}
?>