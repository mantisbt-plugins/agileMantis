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

// set end-attribut and commit-attribut for ms-sql-server access
if (db_is_mssql()) {
	define( "AGILEMANTIS_END_FIELD", '[end]');
	define( "AGILEMANTIS_COMMIT_FIELD", '[commit]');
} else {
	define( "AGILEMANTIS_END_FIELD", 'end');
	define( "AGILEMANTIS_COMMIT_FIELD", 'commit');
}

// URL to agileMantis plugin
define( "AGILEMANTIS_PLUGIN_URL", 
		config_get_global( 'path' ) . 'plugins/' . plugin_get_current() . '/' );

// Path to agileMantis plugin folder
define( "AGILEMANTIS_PLUGIN_URI", 
		config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR);

// Path to agileMantis core folder
define( "AGILEMANTIS_CORE_URI", AGILEMANTIS_PLUGIN_URI . 'core' . DIRECTORY_SEPARATOR );

// Path to agileMantis libs folder
define( "AGILEMANTIS_PLUGIN_CLASS_URI", AGILEMANTIS_PLUGIN_URI . 'libs' . DIRECTORY_SEPARATOR );

// Path to agileMantisExpert license 
define( "AGILEMANTIS_LICENSE_PATH",	
		 config_get_global( 'plugin_path' ) . 'agileMantisExpert'. DIRECTORY_SEPARATOR . 
		 'license' . DIRECTORY_SEPARATOR . 'license.txt' );

// URL to agileMantis remote interface
define("AGILEMANTIS_SCHNITTSTELLEN_URL", 
		'plugins/' . plugin_get_current() . '/core/schnittstelle.php');

// URL to agileMantis Source Forge website
define( "AGILEMANTIS_DEMO_VERSION_HOST", 'agilemantis.sourceforge.net' );

// URL to agileMantisExpert order website
define( "AGILEMANTIS_ORDER_PAGE_URL", 'https://getagilemantislicense.com/' );

// URL to agileMantisExpert plugin download
define( "AGILEMANTIS_EXPERT_DOWNLOAD_LINK" , 
	'https://www.gadiv.de/media/files/opensource/agilemantis/agileMantisExpert220.zip');

// Load agileMantis common Functions
require_once (AGILEMANTIS_PLUGIN_CLASS_URI . 'class_commonlib.php');
global $agilemantis_commonlib;
$agilemantis_commonlib = new gadiv_commonlib();

// Load agileMantis User Functions
require_once (AGILEMANTIS_PLUGIN_CLASS_URI . 'class_agileuser.php');
global $agilemantis_au;
$agilemantis_au = new gadiv_agileuser();

// Load agileMantis Team Functions
require_once (AGILEMANTIS_PLUGIN_CLASS_URI . 'class_team.php');
global $agilemantis_team;
$agilemantis_team = new gadiv_team();

// Load agileMantis Styling functions
require_once (AGILEMANTIS_PLUGIN_CLASS_URI . 'class_agile_mantis_style.php');
global $agilemantis_agm;
$agilemantis_agm = new gadiv_agileMantisStyle();

// Load agileMantis Availability Functions
require_once (AGILEMANTIS_PLUGIN_CLASS_URI . 'class_availability.php');
global $agilemantis_av;
$agilemantis_av = new gadiv_availability();

// Load agileMantis Calendar Functions
require_once (AGILEMANTIS_PLUGIN_CLASS_URI . 'class_calendar.php');
global $agilemantis_cal;
$agilemantis_cal = new gadiv_calendar();

// Load agileMantis Userstory functions
require_once (AGILEMANTIS_PLUGIN_CLASS_URI . 'class_userstory.php');
global $agilemantis_userstory;
$agilemantis_userstory = new gadiv_userstory();

// Load agileMantis Product Backlog Functions
require_once (AGILEMANTIS_PLUGIN_CLASS_URI . 'class_product_backlog.php');
global $agilemantis_pb;
$agilemantis_pb = new gadiv_productBacklog();

// Load agileMantis Task Functions
require_once (AGILEMANTIS_PLUGIN_CLASS_URI . 'class_tasks.php');
global $agilemantis_tasks;
$agilemantis_tasks = new gadiv_tasks();

// Load agileMantis Project Functions
require_once (AGILEMANTIS_PLUGIN_CLASS_URI . 'class_projects.php');
global $agilemantis_project;
$agilemantis_project = new gadiv_projects();

// Load agileMantis Sprint Functions
require_once (AGILEMANTIS_PLUGIN_CLASS_URI . 'class_sprint.php');
global $agilemantis_sprint;
$agilemantis_sprint = new gadiv_sprint();

// Load agileMantis Sprint Functions
require_once (AGILEMANTIS_PLUGIN_CLASS_URI . 'class_version.php');
global $agilemantis_version;
$agilemantis_version = new gadiv_productVersion();

?>