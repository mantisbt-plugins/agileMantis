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
	
	html_page_top(plugin_lang_get( 'delete_sprint_title' ));
	
	# get sprint information 
	$sprint->sprint_id = (int) $_POST['sprint_id'];
	$sprintinfo = $sprint->getSprintByName();
?>
	<br>
	<div align="center">
		<hr width="50%">
			<?php echo plugin_lang_get( 'delete_sprint_message' )?>
			<br>
			Sprint: <?php echo $sprintinfo['name']?><br><br>
			<form action="<?php echo plugin_page('sprints.php') ?>" method="post">
				<input type="hidden" name="action" value="deleteSprint">
				<input type="hidden" name="sprint_id" value="<?php echo (int) $_POST['sprint_id']?>">
				<input type="submit" name="deleteSprint" value="<?php echo plugin_lang_get( 'delete_sprint_title' )?>" class="button">
				<input type="submit" name="backSprint" value="<?php echo plugin_lang_get( 'button_back' )?>" class="button">
			</form>
		<hr width="50%">
	</div>
<?php html_page_bottom() ?>