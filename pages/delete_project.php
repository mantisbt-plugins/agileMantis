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


	html_page_top( plugin_lang_get( 'delete_project_title' ) );
	
	# get project name by id
	$projectname = $agilemantis_project->getProjectName( $_POST['project_id'] );
?>
	<br>
	<div align="center">
		<hr width="50%">
			<?php echo plugin_lang_get( 'delete_project_message' )?><br>
			<?php echo plugin_lang_get( 'delete_project_name' )?>: <?php echo $projectname?>
			<br>
			<?php if( $_POST['delete_with_warning'] ) {?>
				<?php echo plugin_lang_get( 'delete_project_warning' )?>
			<?php } ?>
			<br>
			<form action="<?php echo plugin_page('edit_product_backlog.php') ?>" method="post">
				<input type="hidden" name="action" value="deleteProject">
				<input type="hidden" name="id" value="<?php 
						echo $_POST['product_backlog_id']?>">
				<input type="hidden" name="project_id" value="<?php 
						echo $_POST['project_id']?>">
				<input type="hidden" name="delete_user_stories" value="<?php 
						echo $_POST['delete_with_warning']?>">
				<input type="submit" name="deleteProject" value="<?php 
						echo plugin_lang_get( 'delete_project_title' )?>" class="button">
				<input type="submit" name="backProductBacklog" value="<?php 
						echo plugin_lang_get( 'button_back' )?>" class="button">
			</form>
		<hr width="50%">
	</div>
<?php html_page_bottom() ?>