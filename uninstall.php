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

	$sql = "DROP TABLE ";
	$sql .= "`gadiv_additional_user_fields`, ";
	$sql .= "`gadiv_daily_task_performance`, ";
	$sql .= "`gadiv_productbacklogs`, ";
	$sql .= "`gadiv_rel_productbacklog_projects`, "; 
	$sql .= "`gadiv_rel_sprint_closed_information`, ";
	$sql .= "`gadiv_rel_team_user`, ";
	$sql .= "`gadiv_rel_userstory_splitting_table`, ";
	$sql .= "`gadiv_rel_user_availability`, "; 
	$sql .= "`gadiv_rel_user_availability_week`, ";
	$sql .= "`gadiv_rel_user_team_capacity`, ";
	$sql .= "`gadiv_sprints`, ";
	$sql .= "`gadiv_tasks`, ";
	$sql .= "`gadiv_task_log`, ";
	$sql .= "`gadiv_teams`";
	
	mysql_query($sql);

	$mantisPath = realpath(dirname(__FILE__));
	$mantisPath = str_replace('plugins' . DIRECTORY_SEPARATOR . 'agileMantis', '', $mantisPath);
	include_once($mantisPath . 'config_inc.php');
	$configPath = $mantisPath . 'plugins' . DIRECTORY_SEPARATOR . 'agileMantis' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR;
	include_once($configPath . 'config_api.php');
	
	# delete custom_strings_inc.php
	@unlink($mantisPath . "custom_strings_inc.php");

	# restore custom_strings_inc.php backup
	$filename_custom = $mantisPath . "custom_strings_inc.php.bak";
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