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

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_sitekey'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_plugin_table WHERE basename = 'agileMantis'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_tracker_planned_costs'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_release_documentation'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_technical'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_ranking_order'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_presentable'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_scrum'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_license_key'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_sprint_length'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_taskboard'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_fibonacci_length'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_storypoint_mode'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_userstory_unit_mode'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_task_unit_mode'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_config_table WHERE config_id = 'plugin_agileMantis_gadiv_workday_in_hours'";
	mysql_query($sql);

	$sql = "DROP TABLE `gadiv_additional_user_fields`, `gadiv_daily_task_performance`, `gadiv_productbacklogs`, `gadiv_rel_productbacklog_projects`, `gadiv_rel_team_user`, `gadiv_rel_user_availbility`, `gadiv_rel_user_availbility_week`, `gadiv_rel_user_team_capacity`, `gadiv_sprints`, `gadiv_tasks`, `gadiv_task_log`, `gadiv_teams`";
	mysql_query($sql);

	include($_SERVER['DOCUMENT_ROOT'].$subdir.'config_inc.php');
	
	# set subfolder if necassary
	$filename = $_SERVER['DOCUMENT_ROOT'].$subdir."config_inc.php";

	# rewrite Mantis-Config file
	$string = '<?php'."\r\n";
	$string.= '$g_hostname = \''.$g_hostname.'\';'."\r\n";
	$string.= '$g_db_type = \''.$g_db_type.'\';'."\r\n";
	$string.= '$g_database_name = \''.$g_database_name.'\';'."\r\n";
	$string.= '$g_db_username = \''.$g_db_username.'\';'."\r\n";
	$string.= '$g_db_password = \''.$g_db_password.'\';'."\r\n";
	$string.= '?>';

	$fp = fopen($filename, 'w+');
	fwrite($fp, $string);
	fclose($fp);

	# delete custom_strings_inc.php
	@unlink($_SERVER['DOCUMENT_ROOT'].$subdir."custom_strings_inc.php");

	# restore custom_strings_inc.php backup
	$filename_custom = $_SERVER['DOCUMENT_ROOT'].$subdir."custom_strings_inc.php.bak";
	if(is_file($filename_custom)){
		@copy($filename_custom,substr(0,-4,$filename_custom));
	}

	# delete agilemantis custom fields
	include_once(PLUGIN_CLASS_URI.'class_commonlib.php');
	$commonlib = new gadiv_commonlib();
	$commonlib->getAdditionalProjectFields();

	$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$commonlib->bv."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$commonlib->pb."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$commonlib->sp."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$commonlib->spr."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$commonlib->ro."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$commonlib->pr."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$commonlib->tech."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$commonlib->rld."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_project_table WHERE field_id = '".$commonlib->pw."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_string_table WHERE field_id = '".$commonlib->bv."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_string_table WHERE field_id = '".$commonlib->pb."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_string_table WHERE field_id = '".$commonlib->sp."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_string_table WHERE field_id = '".$commonlib->spr."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_string_table WHERE field_id = '".$commonlib->ro."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_string_table WHERE field_id = '".$commonlib->pr."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_string_table WHERE field_id = '".$commonlib->tech."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_string_table WHERE field_id = '".$commonlib->rld."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_string_table WHERE field_id = '".$commonlib->pw."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_table WHERE id = '".$commonlib->bv."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_table WHERE id = '".$commonlib->pb."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_table WHERE id = '".$commonlib->sp."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_table WHERE id = '".$commonlib->spr."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_table WHERE id = '".$commonlib->ro."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_table WHERE id = '".$commonlib->pr."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_table WHERE id = '".$commonlib->tech."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_table WHERE id = '".$commonlib->rld."'";
	mysql_query($sql);

	$sql = "DELETE FROM mantis_custom_field_table WHERE id = '".$commonlib->pw."'";
	mysql_query($sql);
?>