<?php
define("VERSIONAGMAN","Version 1.3");

if( isset($_SERVER['HTTPS']) ) {
	$protocol = 'https://';
} else {
	$protocol = 'http://';
}

$mantisPath = realpath(dirname(__FILE__));
$mantisPath = str_replace('plugins' . DIRECTORY_SEPARATOR . 'agileMantis' . DIRECTORY_SEPARATOR . 'core', '', $mantisPath);
$mantisPath = str_replace('plugins' . DIRECTORY_SEPARATOR . 'agileMantis', '', $mantisPath);

define("SUBFOLDER", dirname($_SERVER['PHP_SELF']) . "/");
define("PLUGIN_URL", $protocol.$_SERVER['HTTP_HOST']. SUBFOLDER . "plugins/agileMantis/");
define("BASE_URL", $protocol.$_SERVER['HTTP_HOST'].SUBFOLDER);
define("BASE_URI", $mantisPath . 'plugins' . DIRECTORY_SEPARATOR . 'agileMantis' . DIRECTORY_SEPARATOR);
define("PLUGIN_URI", BASE_URI);
define("PLUGIN_CLASS_URI", PLUGIN_URI.'libs' . DIRECTORY_SEPARATOR);
define("LICENSE_PATH", $mantisPath . 'plugins'. DIRECTORY_SEPARATOR . 'agileMantisExpert' . DIRECTORY_SEPARATOR . 'license'. DIRECTORY_SEPARATOR .'license.txt');
define("SCHNITTSTELLEN_URL", "plugins/agileMantis/core/schnittstelle.php");

include_once(PLUGIN_CLASS_URI.'class_commonlib.php');

// Load agileMantis User Functions
include_once(PLUGIN_CLASS_URI.'class_agileuser.php');
global $au;
$au = new gadiv_agileuser();

// Load agileMantis Team Functions
include_once(PLUGIN_CLASS_URI.'class_team.php');
global $team;
$team = new gadiv_team();

// Load agileMantis Styling functions
include_once(PLUGIN_CLASS_URI.'class_agileMantisStyle.php');
global $agm;
$agm = new gadiv_agileMantisStyle();

// Load agileMantis Availability Functions
include_once(PLUGIN_CLASS_URI.'class_availability.php');
global $av;
$av = new gadiv_availability();

// Load agileMantis Calendar Functions
include_once(PLUGIN_CLASS_URI.'class_calendar.php');
global $cal;
$cal = new gadiv_calendar();

// Load agileMantis Userstory functions
include_once(PLUGIN_CLASS_URI.'class_userstory.php');
global $userstory;
$userstory = new gadiv_userstory();

// Load agileMantis Product Backlog Functions
include_once(PLUGIN_CLASS_URI.'class_product_backlog.php');
global $pb;
$pb = new gadiv_product_backlog();

// Load agileMantis Task Functions
include_once(PLUGIN_CLASS_URI.'class_tasks.php');
global $tasks;
$tasks = new gadiv_tasks();

// Load agileMantis Project Functions
include_once(PLUGIN_CLASS_URI.'class_projects.php');
global $project;
$project = new gadiv_projects();

// Load agileMantis Sprint Functions
include_once(PLUGIN_CLASS_URI.'class_sprint.php');
global $sprint;
$sprint = new gadiv_sprint();

// Load agileMantis Sprint Functions
include_once(PLUGIN_CLASS_URI.'class_version.php');
global $version;
$version = new gadiv_product_version();
?>