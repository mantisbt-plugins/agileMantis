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
$commonlib = new gadiv_commonlib();
$user = $commonlib->getAdditionalUserFields( auth_get_current_user_id() );
$getPage = explode( '/', $_SERVER['REQUEST_URI'] );
$lai = count( $getPage ) - 1;

if( $user[0]['administrator'] == 1 || current_user_is_administrator() ) {
	# create hover / active effects by requested uri
	switch( $getPage[$lai] ) {
		case 'config.php':
			$style_config = 'style="font-weight:bold;"';
			break;
		case 'agileuser.php':
			$style_user = 'style="font-weight:bold;"';
			break;
		case 'agileuser.php&filter=' . $_GET['filter']:
			$style_user = 'style="font-weight:bold;"';
			break;
		case 'agileuser.php&sort_by=' . $_GET['sort_by']:
			$style_user = 'style="font-weight:bold;"';
			break;
		case 'config.php':
			$style_config = 'style="font-weight:bold;"';
			break;
		case 'edit_team.php':
			$style_team = 'style="font-weight:bold;"';
			break;
		case 'teams.php':
			$style_team = 'style="font-weight:bold;"';
			break;
		case 'teams.php&sort_by=' . $_GET['sort_by']:
			$style_team = 'style="font-weight:bold;"';
			break;
		case 'product_backlogs.php&sort_by=' . $_GET['sort_by']:
			$style_pbl = 'style="font-weight:bold;"';
			break;
		case 'product_backlogs.php':
			$style_pbl = 'style="font-weight:bold;"';
			break;
		case 'edit_product_backlog.php':
			$style_pbl = 'style="font-weight:bold;"';
			break;
		case 'edit_product_backlog.php&pbid=' . $_GET['pbid']:
			$style_pbl = 'style="font-weight:bold;"';
			break;
		case 'availability.php':
			$style_av = 'style="font-weight:bold;"';
			break;
		case 'availability.php&filter=' . $_GET['filter']:
			$style_av = 'style="font-weight:bold;"';
			break;
		case 'capacity.php':
			$style_cc = 'style="font-weight:bold;"';
			break;
		case 'sprints.php':
			$style_sprints = 'style="font-weight:bold;"';
			break;
		case 'edit_sprint.php':
			$style_sprints = 'style="font-weight:bold;"';
			break;
		case 'sprints.php&sort_by=' . $_GET['sort_by'] . '&klickStatus=' . $_GET['klickStatus']:
			$style_sprints = 'style="font-weight:bold;"';
			break;
		case 'sprints.php&sort_by=' . $_GET['sort_by']:
			$style_sprints = 'style="font-weight:bold;"';
			break;
		case 'sprint_backlog.php':
			$style_sprintbl = 'style="font-weight:bold;"';
			break;
		default:
			'';
	}
	
	if( $_POST['sprintName'] == "" ) {
		?>
<center>
	[ 	<a href="<?php echo plugin_page("agileuser.php")?>"
			<?php echo $style_user?>>
			<?php echo plugin_lang_get( 'manage_user_title' )?>
		</a>
	] [ <a href="<?php echo plugin_page("product_backlogs.php")?>"
			<?php echo $style_pbl?>>
			<?php echo plugin_lang_get( 'manage_product_backlogs_title' )?>
		</a>
	] [ <a href="<?php echo plugin_page("teams.php")?>"
			<?php echo $style_team?>>
			<?php echo plugin_lang_get( 'manage_teams_title' )?>
		</a>
	] [ <a href="<?php echo plugin_page("availability.php")?>"
			<?php echo $style_av?>>
			<?php echo plugin_lang_get( 'manage_availability_title' )?>
		</a>
	] [ <a href="<?php echo plugin_page("capacity.php")?>"
			<?php echo $style_cc?>>
			<?php echo plugin_lang_get( 'manage_capacity_title' )?>
		</a>
	] [ <a href="<?php echo plugin_page("sprints.php")?>"
			<?php echo $style_sprints?>>
			<?php echo plugin_lang_get( 'manage_sprints_title' )?>
		</a>
	] [ <a href="<?php echo plugin_page("config.php")?>"
			<?php echo $style_config?>>
			<?php echo plugin_lang_get( 'manage_settings_title' )?>
		</a>
	]
</center>
<?php 
		}
	}
?>