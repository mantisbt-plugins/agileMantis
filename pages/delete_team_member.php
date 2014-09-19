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

	
	html_page_top(plugin_lang_get( 'remove_member_title' ));
	
	# get real name by user name
	$username = $agilemantis_team->getUserRealName($_POST['user_id']);
	
	# get team member role id
	switch($_POST['role_id']){
		case 3:
			$rolename = plugin_lang_get( 'remove_member_developer' );
		break;
		case 4:
			$rolename = plugin_lang_get( 'remove_member_customer' );
		break;
		case 5:
			$rolename = plugin_lang_get( 'remove_member_user' );
		break;
		case 6:
			$rolename = 'Manager';
		break;
	}
	
?>
	<br>
	<div align="center">
		<hr width="50%">
			<?php echo plugin_lang_get( 'remove_member_messageA' )?> <?php 
				echo $rolename?> <?php echo plugin_lang_get( 'remove_member_messageB' )?><br>
			<?php echo plugin_lang_get( 'remove_member_member' )?>: <?php echo $username?><br><br>
			<form action="<?php echo plugin_page('edit_team.php') ?>" method="post">
				<input type="hidden" name="action" value="deleteTeamMember">
				<input type="hidden" name="id" value="<?php echo $_POST['team_id']?>">
				<input type="hidden" name="user_id" value="<?php echo $_POST['user_id']?>">
				<input type="hidden" name="role_id" value="<?php echo $_POST['role_id']?>">
				<input type="submit" name="deleteTeamMember" value="<?php 
					echo plugin_lang_get( 'remove_member_title' )?>" class="button">
				<input type="submit" name="backTeam" value="<?php 
					echo plugin_lang_get( 'button_back' )?>" class="button">
			</form>
		<hr width="50%">
	</div>
<?php html_page_bottom() ?>