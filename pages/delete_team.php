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

	
	html_page_top(plugin_lang_get( 'delete_team_title' ));
	
	# get selected team
	$agilemantis_team->id = (int) $_POST['team_id'];
	$teaminfo = $agilemantis_team->getSelectedTeam();
?>
	<br>
	<div align="center">
		<hr width="50%">
			<?php echo plugin_lang_get( 'delete_team_message' )?><br>
			Team: <?php echo $teaminfo[0]['name']?><br><br>
			<form action="<?php echo plugin_page('teams.php') ?>" method="post">
				<input type="hidden" name="action" value="deleteTeam">
				<input type="hidden" name="team_id" value="<?php echo (int) $_POST['team_id']?>">
				<input type="submit" name="deleteTeam" value="<?php 
					echo plugin_lang_get( 'delete_team_title' )?>" class="button">
				<input type="submit" name="backTeam" value="<?php 
					echo plugin_lang_get( 'button_back' )?>" class="button">
			</form>
		<hr width="50%">
	</div>
<?php html_page_bottom() ?>