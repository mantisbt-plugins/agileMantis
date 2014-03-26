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
	
	$file_content = file_get_contents($filename);
	if(!stristr($file_content,"gadiv")){
		# backup Mantis-Config file
		@copy($filename,$filename.'.bak');
		
		# Rewrite Mantis-Config file
		$string = '<?php'."\r\n";
		$string.= 'define("SUBFOLDER","'.dirname($_SERVER['PHP_SELF']).'/");'."\r\n";
		$string.= 'define("PLUGIN_URL","http://".$_SERVER[\'HTTP_HOST\']. SUBFOLDER . "plugins/agileMantis/");'."\r\n";
		$string.= 'define("BASE_URL","http://".$_SERVER[\'HTTP_HOST\']."".SUBFOLDER."/");'."\r\n";
		$string.= 'define("BASE_URI", dirname( __FILE__ ) . DIRECTORY_SEPARATOR);'."\r\n";
		$string.= 'define("PLUGIN_URI",BASE_URI."plugins" . DIRECTORY_SEPARATOR . "agileMantis" . DIRECTORY_SEPARATOR . "");'."\r\n";
		$string.= 'define("PLUGIN_CLASS_URI",PLUGIN_URI.\'libs\' . DIRECTORY_SEPARATOR);'."\r\n";
		$string.= "\r\n";

		$string.= 'include_once(PLUGIN_CLASS_URI.\'class_commonlib.php\');'."\r\n";
		$string.= "\r\n";

		$string.= '// Load agileMantis User Functions'."\r\n";
		$string.= 'include_once(PLUGIN_CLASS_URI.\'class_agileuser.php\');'."\r\n";
		$string.= '$au = new gadiv_agileuser();'."\r\n"."\r\n";
		$string.= "\r\n";

		$string.= '// Load agileMantis Team Functions'."\r\n";
		$string.= 'include_once(PLUGIN_CLASS_URI.\'class_team.php\');'."\r\n";
		$string.= '$team = new gadiv_team();'."\r\n";
		$string.= "\r\n";

		$string.= '// Load agileMantis Styling functions'."\r\n";
		$string.= 'include_once(PLUGIN_CLASS_URI.\'class_agileMantisStyle.php\');'."\r\n";
		$string.= '$agm = new gadiv_agileMantisStyle();'."\r\n";
		$string.= "\r\n";

		$string.= '// Load agileMantis Availability Functions'."\r\n";
		$string.= 'include_once(PLUGIN_CLASS_URI.\'class_availability.php\');'."\r\n";
		$string.= '$av = new gadiv_availability();'."\r\n";
		$string.= "\r\n";

		$string.= '// Load agileMantis Calendar Functions'."\r\n";
		$string.= 'include_once(PLUGIN_CLASS_URI.\'class_calendar.php\');'."\r\n";
		$string.= '$cal = new gadiv_calendar();'."\r\n";
		$string.= "\r\n";

		$string.= '// Load agileMantis Userstory functions'."\r\n";
		$string.= 'include_once(PLUGIN_CLASS_URI.\'class_userstory.php\');'."\r\n";
		$string.= '$userstory = new gadiv_userstory();'."\r\n";
		$string.= "\r\n";

		$string.= '// Load agileMantis Product Backlog Functions'."\r\n";
		$string.= 'include_once(PLUGIN_CLASS_URI.\'class_product_backlog.php\');'."\r\n";
		$string.= '$pb = new gadiv_product_backlog();'."\r\n";
		$string.= "\r\n";

		$string.= '// Load agileMantis Task Functions'."\r\n";
		$string.= 'include_once(PLUGIN_CLASS_URI.\'class_tasks.php\');'."\r\n";
		$string.= '$tasks = new gadiv_tasks();'."\r\n";
		$string.= "\r\n";

		$string.= '// Load agileMantis Project Functions'."\r\n";
		$string.= 'include_once(PLUGIN_CLASS_URI.\'class_projects.php\');'."\r\n";
		$string.= '$project = new gadiv_projects();'."\r\n";
		$string.= "\r\n";

		$string.= '// Load agileMantis Sprint Functions'."\r\n";
		$string.= 'include_once(PLUGIN_CLASS_URI.\'class_sprint.php\');'."\r\n";
		$string.= '$sprint = new gadiv_sprint();'."\r\n";
		$string.= "\r\n";
		
		$string.= '// Load agileMantis Sprint Functions'."\r\n";
		$string.= 'include_once(PLUGIN_CLASS_URI.\'class_version.php\');'."\r\n";
		$string.= '$version = new gadiv_product_version();'."\r\n";
		$string.= '?>'."\r\n";

		$fp = fopen($filename, 'a+');
		fwrite($fp, $string);
		fclose($fp);
	}

	# set subfolder if necassary
	$file_content = file_get_contents($filename_custom);

	if(!stristr($file_content,"gadiv")){
		$filename_custom = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/custom_strings_inc.php";

		# backup custom_strings_inc.php if necessary
		if(is_file($filename_custom)){
			@copy($filename_custom,$filename_custom.'.bak');
		}

		# rewrite custom_strings_inc.php 
		$string = '<?php'."\r\n";
		$string.= 'switch( lang_get_current() ){'."\r\n";
		$string.= "case 'german':"."\r\n";
		$string.= '$s_Presentable = "Pr&auml;sentabel";'."\r\n";
		$string.= '$s_InReleaseDocu = "In Freigabedoku";'."\r\n";
		$string.= '$s_PlannedWork = "Planaufwand";'."\r\n";
		$string.= '$s_RankingOrder = "Rangfolge";'."\r\n";
		$string.= '$s_ReleaseDocumentation = "Freigabedoku-Text";'."\r\n";
		$string.= '$s_Technical = "Technisch";'."\r\n";
		$string.= '$s_PlannedWorkUnit = "Aufwandseinheit";'."\r\n";
		$string.= 'break;'."\r\n";
		$string.= "case 'english':"."\r\n";
		$string.= '$s_Presentable = "Presentable";'."\r\n";
		$string.= '$s_InReleaseDocu = "In Releasedocu";'."\r\n";
		$string.= '$s_PlannedWork = "Planned Work";'."\r\n";
		$string.= '$s_RankingOrder = "Ranking Order";'."\r\n";
		$string.= '$s_ReleaseDocumentation = "Releasedocu-Text";'."\r\n";
		$string.= '$s_Technical = "Technical";'."\r\n";
		$string.= '$s_PlannedWorkUnit = "Planned Work Unit";'."\r\n";
		$string.= 'break;'."\r\n";
		$string.= '}'."\r\n";
		$string.= '$s_ProductBacklog = "Product Backlog";'."\r\n";
		$string.= '$s_BusinessValue = "Business Value";'."\r\n";
		$string.= '?>'."\r\n";

		$fpc = fopen($filename_custom, 'a+');
		fwrite($fpc, $string);
		fclose($fpc);
	}

	$sql = "SELECT * FROM mantis_custom_field_table WHERE name = 'ProductBacklog'";
	$result = mysql_query($sql);
	$customField = mysql_fetch_assoc($result);
	
	# create agilemantis custom field ProductBacklog
	if($customField['name'] == ''){
		$sql 	=	"INSERT INTO mantis_custom_field_table SET
						name 				= 	'ProductBacklog',
						type 				= 	'6',
						access_level_r 		=	'55',
						access_level_rw 	=	'55',
						display_report		=	'0',
						display_update		=	'0',
						filter_by			=	'1'
					";
		mysql_query($sql);
	}
	
	$sql = "SELECT * FROM mantis_custom_field_table WHERE name = 'BusinessValue'";
	$result = mysql_query($sql);
	$customField = mysql_fetch_assoc($result);

	if($customField['name'] == ''){
		# create agilemantis custom field BusinessValue
		$sql 	=	"INSERT INTO mantis_custom_field_table SET
						name 				= 	'BusinessValue',
						type 				= 	'0',
						access_level_r 		=	'55',
						access_level_rw 	=	'55',
						display_report		=	'0',
						display_update		=	'0',
						filter_by			=	'1'
					";
		mysql_query($sql);
	}
	
	$sql = "SELECT * FROM mantis_custom_field_table WHERE name = 'Storypoints'";
	$result = mysql_query($sql);
	$customField = mysql_fetch_assoc($result);

	if($customField['name'] == ''){
		# create agilemantis custom field Storypoints
		$sql 	=	"	INSERT INTO mantis_custom_field_table SET
						name 				= 	'Storypoints',
						type 				= 	'1',
						access_level_r 		=	'55',
						access_level_rw 	=	'55',
						display_report		=	'0',
						display_update		=	'0',
						filter_by			=	'1'
					";
		mysql_query($sql);
	}

	$sql = "SELECT * FROM mantis_custom_field_table WHERE name = 'Sprint'";
	$result = mysql_query($sql);
	$customField = mysql_fetch_assoc($result);
	
	if($customField['name'] == ''){
		# create agilemantis custom field Sprint
		$sql 	=	"	INSERT INTO mantis_custom_field_table SET
						name 				= 	'Sprint',
						type 				= 	'6',
						access_level_r 		=	'55',
						access_level_rw 	=	'55',
						display_report		=	'0',
						display_update		=	'0',
						filter_by			=	'1'
					";
		mysql_query($sql);
	}
	
	$sql = "SELECT * FROM mantis_custom_field_table WHERE name = 'RankingOrder'";
	$result = mysql_query($sql);
	$customField = mysql_fetch_assoc($result);
	
	# create agilemantis custom field RankingOrder
	$sql 	=	"	INSERT INTO mantis_custom_field_table SET
					name 				= 	'RankingOrder',
					type 				= 	'1',
					access_level_r 		=	'55',
					access_level_rw 	=	'55',
					display_report		=	'0',
					display_update		=	'0',
					filter_by			=	'1'
				";
	mysql_query($sql);
	
	$sql = "SELECT * FROM mantis_custom_field_table WHERE name = 'Presentable'";
	$result = mysql_query($sql);
	$customField = mysql_fetch_assoc($result);

	if($customField['name'] == ''){
		# create agilemantis custom field Presentable
		$sql 	=	"	INSERT INTO mantis_custom_field_table SET
						name 				= 	'Presentable',
						possible_values		=	'1|2|3',
						type 				= 	'6',
						access_level_r 		=	'55',
						access_level_rw 	=	'55',
						display_report		=	'0',
						display_update		=	'0',
						filter_by			=	'1'
					";
		mysql_query($sql);
	}
	
	$sql = "SELECT * FROM mantis_custom_field_table WHERE name = 'InReleaseDocu'";
	$result = mysql_query($sql);
	$customField = mysql_fetch_assoc($result);

	if($customField['name'] == ''){
		# create agilemantis custom field InReleaseDocu
		$sql 	=	"	INSERT INTO mantis_custom_field_table SET
						name 				= 	'InReleaseDocu',
						possible_values		=	'Ja',
						type 				= 	'5',
						access_level_r 		=	'55',
						access_level_rw 	=	'55',
						display_report		=	'0',
						display_update		=	'0',
						filter_by			=	'1'
					";
		mysql_query($sql);
	}
	
	$sql = "SELECT * FROM mantis_custom_field_table WHERE name = 'PlannedWork'";
	$result = mysql_query($sql);
	$customField = mysql_fetch_assoc($result);

	if($customField['name'] == ''){
		# create agilemantis custom field PlannedWork
		$sql 	=	"	INSERT INTO mantis_custom_field_table SET
						name 				= 	'PlannedWork',
						type 				= 	'1',
						access_level_r 		=	'55',
						access_level_rw 	=	'55',
						display_report		=	'0',
						display_update		=	'0',
						filter_by			=	'1'
					";
		mysql_query($sql);
	}
	
	$sql = "SELECT * FROM mantis_custom_field_table WHERE name = 'PlannedWorkUnit'";
	$result = mysql_query($sql);
	$customField = mysql_fetch_assoc($result);	

	if($customField['name'] == ''){
		# create agilemantis custom field PlannedWorkUnit
		$sql 	=	"	INSERT INTO mantis_custom_field_table SET
						name 				= 	'PlannedWorkUnit',
						type 				= 	'0',
						access_level_r 		=	'55',
						access_level_rw 	=	'55',
						display_report		=	'0',
						display_update		=	'0',
						filter_by			=	'0'
					";
		mysql_query($sql);
	}

	# create table gadiv_additional_user_fields
	$sql = "
		CREATE TABLE IF NOT EXISTS `gadiv_additional_user_fields` (
		  `user_id` int(11) unsigned NOT NULL,
		  `developer` int(1) NOT NULL DEFAULT b'0',
		  `participant` int(1) NOT NULL,
		  `administrator` int(1) NOT NULL,
		  PRIMARY KEY (`user_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
	";
	mysql_query($sql);

	# create table gadiv_daily_task_performance
	$sql = "
		CREATE TABLE IF NOT EXISTS `gadiv_daily_task_performance` (
		  `task_id` int(11) unsigned NOT NULL,
		  `user_id` int(11) unsigned NOT NULL,
		  `performed` decimal(6,2) NOT NULL,
		  `rest` decimal(6,2) NOT NULL,
		  `date` datetime NOT NULL,
		  `rest_flag` bit(1) NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
	";
	mysql_query($sql);

	# create table gadiv_productbacklogs
	$sql = "
		CREATE TABLE IF NOT EXISTS `gadiv_productbacklogs` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
		  `description` text CHARACTER SET utf8 NOT NULL,
		  `user_id` int(11) unsigned NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;
	";
	mysql_query($sql);

	# create table gadiv_rel_productbacklog_projects
	$sql = "
		CREATE TABLE IF NOT EXISTS `gadiv_rel_productbacklog_projects` (
		  `pb_id` int(11) unsigned NOT NULL,
		  `project_id` int(11) unsigned NOT NULL,
		  PRIMARY KEY (`pb_id`,`project_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
	";
	mysql_query($sql);

	# create table gadiv_rel_sprint_closed_information
	$sql = "
		CREATE TABLE IF NOT EXISTS `gadiv_rel_sprint_closed_information` (
		  `sprint_id` int(11) NOT NULL,
		  `count_user_stories` int(11) NOT NULL,
		  `count_task_sprint` int(11) NOT NULL,
		  `count_splitted_user_stories_sprint` int(11) NOT NULL,
		  `storypoints_sprint` decimal(6,2) NOT NULL,
		  `storypoints_in_splitted_user_stories` decimal(6,2) NOT NULL,
		  `work_planned_sprint` decimal(6,2) NOT NULL,
		  `work_performed` decimal(6,2) NOT NULL,
		  `work_moved` decimal(6,2) NOT NULL,
		  `storypoints_moved` decimal(6,2) NOT NULL,
		  `count_developer_team` int(11) NOT NULL,
		  `total_developer_capacity` decimal(6,2) NOT NULL,
		  `count_developer_team_task` int(11) NOT NULL,
		  `total_developer_capacity_task` decimal(6,2) NOT NULL,
		  PRIMARY KEY (`sprint_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	";
	mysql_query($sql);

	# create table gadiv_rel_team_user
	$sql = "
		CREATE TABLE IF NOT EXISTS `gadiv_rel_team_user` (
		  `team_id` int(11) NOT NULL,
		  `user_id` int(11) NOT NULL,
		  `role` tinyint(3) NOT NULL,
		  PRIMARY KEY (`team_id`,`user_id`,`role`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
	";
	mysql_query($sql);

	# create table gadiv_rel_userstory_splitting_table
	$sql = "
		CREATE TABLE IF NOT EXISTS `gadiv_rel_userstory_splitting_table` (
		  `old_userstory_id` int(11) unsigned NOT NULL,
		  `new_userstory_id` int(11) unsigned NOT NULL,
		  `work_moved` decimal(6,2) NOT NULL,
		  `storypoints_moved` decimal(6,2) unsigned NOT NULL,
		  `date` datetime NOT NULL,
		  PRIMARY KEY (`old_userstory_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	";
	mysql_query($sql);

	# create table gadiv_rel_user_availability
	$sql = "
		CREATE TABLE IF NOT EXISTS `gadiv_rel_user_availability` (
		  `user_id` int(11) unsigned NOT NULL,
		  `date` date NOT NULL,
		  `capacity` decimal(4,2) NOT NULL,
		  PRIMARY KEY (`user_id`,`date`),
		  KEY `user_id` (`user_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
	";
	mysql_query($sql);

	# create table gadiv_rel_user_availability_week
	$sql = "
		CREATE TABLE IF NOT EXISTS `gadiv_rel_user_availability_week` (
		  `user_id` int(11) unsigned NOT NULL,
		  `monday` decimal(4,2) NOT NULL DEFAULT '0.00',
		  `tuesday` decimal(4,2) NOT NULL DEFAULT '0.00',
		  `wednesday` decimal(4,2) NOT NULL DEFAULT '0.00',
		  `thursday` decimal(4,2) NOT NULL DEFAULT '0.00',
		  `friday` decimal(4,2) NOT NULL DEFAULT '0.00',
		  `saturday` decimal(4,2) NOT NULL DEFAULT '0.00',
		  `sunday` decimal(4,2) NOT NULL DEFAULT '0.00',
		  `marked` bit(1) NOT NULL DEFAULT b'0',
		  PRIMARY KEY (`user_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
	";
	mysql_query($sql);

	# create table gadiv_rel_user_team_capacity
	$sql = "
		CREATE TABLE IF NOT EXISTS `gadiv_rel_user_team_capacity` (
		  `user_id` int(11) unsigned NOT NULL,
		  `team_id` int(11) unsigned NOT NULL,
		  `date` date NOT NULL,
		  `capacity` decimal(4,2) NOT NULL,
		  PRIMARY KEY (`user_id`,`team_id`,`date`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
	";
	mysql_query($sql);

	# create table gadiv_sprints
	$sql = "
		CREATE TABLE IF NOT EXISTS `gadiv_sprints` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `team_id` int(11) unsigned NOT NULL,
		  `pb_id` int(11) unsigned NOT NULL,
		  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
		  `description` text CHARACTER SET utf8 NOT NULL,
		  `status` tinyint(1) unsigned NOT NULL,
		  `daily_scrum` bit(1) NOT NULL,
		  `start` date NOT NULL,
		  `commit` datetime NOT NULL,
		  `end` date NOT NULL,
		  `closed` datetime NOT NULL,
		  `unit_storypoints` tinyint(1) NOT NULL,
		  `unit_planned_work` tinyint(1) NOT NULL,
		  `unit_planned_task` tinyint(1) NOT NULL,
		  `workday_length` decimal(6,2) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;
	";
	mysql_query($sql);

	# create table gadiv_tasks
	$sql = "
		CREATE TABLE IF NOT EXISTS `gadiv_tasks` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `us_id` int(11) unsigned NOT NULL,
		  `developer_id` int(11) unsigned NOT NULL,
		  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
		  `description` text CHARACTER SET utf8 NOT NULL,
		  `status` tinyint(1) unsigned NOT NULL,
		  `planned_capacity` decimal(6,2) NOT NULL DEFAULT '0.00',
		  `performed_capacity` decimal(6,2) NOT NULL DEFAULT '0.00',
		  `rest_capacity` decimal(6,2) NOT NULL DEFAULT '0.00',
		  `unit` int(1) NOT NULL,
		  `daily_scrum` bit(1) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;
	";
	mysql_query($sql);

	# create table gadiv_task_log
	$sql = "
		CREATE TABLE IF NOT EXISTS `gadiv_task_log` (
		  `task_id` int(11) unsigned NOT NULL,
		  `event` varchar(12) COLLATE utf8_bin NOT NULL,
		  `user_id` int(11) unsigned NOT NULL,
		  `date` datetime NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
	";
	mysql_query($sql);

	# create table gadiv_teams
	$sql = "
		CREATE TABLE IF NOT EXISTS `gadiv_teams` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
		  `description` text CHARACTER SET utf8 NOT NULL,
		  `pb_id` int(11) unsigned NOT NULL,
		  `daily_scrum` bit(1) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;
	";
	mysql_query($sql);

	# add additional config values
	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_userstory_unit_mode', access_reqd = 90, type = 2, value = 'h'";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_task_unit_mode', access_reqd = 90, type = 2, value = 'h'";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_workday_in_hours', access_reqd = 90, type = 2, value = '8'";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_storypoint_mode', access_reqd = 90, type = 1, value = 0";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_fibonacci_length', access_reqd = 90, type = 1, value = 12";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_taskboard', access_reqd = 90, type = 1, value = 0";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_sprint_length', access_reqd = 90, type = 1, value = 28";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_license_key', access_reqd = 90, type = 1, value = ''";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_scrum', access_reqd = 90, type = 1, value = 0";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_presentable', access_reqd = 90, type = 1, value = 0";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_ranking_order', access_reqd = 90, type = 1, value = 0";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_technical', access_reqd = 90, type = 1, value = 0";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_release_documentation', access_reqd = 90, type = 1, value = 0";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_tracker_planned_costs', access_reqd = 90, type = 1, value = 0";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_licensekey', access_reqd = 90, type = 1, value = ''";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_daily_scrum', access_reqd = 90, type = 1, value = ''";
	mysql_query($sql);

	$sql = "INSERT INTO mantis_plugin_table SET basename = 'agileMantis', enabled = 1, protected = 0, priority = 3";
	mysql_query($sql);
	
	$number_1 = rand(1,10000);
	$number_2 = rand(1,10000);
	$number_3 = rand(1,10000);
	$number_4 = rand(1,10000);
	$randomNumbers =  $number_1 . ' ' . $number_2 . ' ' . $number_3 . ' ' . $number_4;
	$randomNumbers = md5($randomNumbers);
	$randomNumbers .= $_SERVER['SERVER_ADDR'];
	$randomNumbers = md5($randomNumbers);
	$randomNumbers .= time();
	$sitekey = md5($randomNumbers);

	$sql = "INSERT INTO mantis_config_table SET config_id = 'plugin_agileMantis_gadiv_sitekey', access_reqd = 90, type = 0, value = '".$sitekey."'";
	mysql_query($sql);
	

	$sql = "SELECT id FROM mantis_custom_field_table WHERE name IN('Technical','Sprint','RankingOrder','Presentable', 'InReleaseDocu', 'PlannedWork','Storypoints','ProductBacklog','BusinessValue','PlannedWorkUnit') ORDER BY id ASC";
	$result = mysql_query($sql);
	$customFields = array();
	while($row = mysql_fetch_assoc($result)){
		$customFields[] = $row['id'];
	}
	
	$sql = "SELECT id FROM mantis_bug_table ORDER BY id ASC";
	$result = mysql_query($sql);
	
	while($row = mysql_fetch_assoc($result)){
		foreach($customFields AS $key => $value){
			$sql = "INSERT INTO mantis_custom_field_string_table SET field_id='".$value."', bug_id = '".$row['id']."'";
			mysql_query($sql);
		}
	}
	
?>