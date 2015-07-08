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


	html_page_top( plugin_lang_get( 'manage_product_backlogs_title' ) );

	if( current_user_is_administrator() || $_SESSION['AGILEMANTIS_ISMANTISADMIN'] == 1 ) {
?>
<br>
<?php include(AGILEMANTIS_PLUGIN_URI.'/pages/footer_menu.php');?>	
<?php
	# delete product backlog by id
	if( $_POST['deleteProductBacklog'] != "" ) {
		$agilemantis_pb->id = (int) $_POST['product_backlog_id'];
		$agilemantis_pb->deleteProductBacklog();
	} 
	
	# get all product backlogs
	$backlogs = $agilemantis_pb->getProductBacklogs();
?>
<br>
<div class="table-container">
<table align="center" class="width100" cellspacing="1">
	<tr>
		<td colspan="3"><b><?php echo plugin_lang_get( 'manage_product_backlogs_title' )?></b> 
		<form action="<?php echo plugin_page( "edit_product_backlog.php" )?>" method="post">
			<input type="submit" name="submit" value="<?php echo plugin_lang_get( 'manage_product_backlogs_add' )?>">
			<input type="hidden" name="new_product_backlog" value="1">
		</form>
		</td>
	</tr>
	<tr>
		<td class="category"><a href="<?php echo plugin_page( "product_backlogs.php" )?>&sort_by=name">Name</a></td>
		<td class="category"><a href="<?php echo plugin_page( "product_backlogs.php" )?>&sort_by=description">
			<?php echo plugin_lang_get( 'common_description' )?></a></td>
		<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
	</tr>
	<?php if( !empty( $backlogs ) ) {
			foreach( $backlogs AS $num => $row ) { ?>
	<?php
	$agilemantis_pb->productBacklogHasStoriesLeft( $row['name'] );
	?>
	<tr <?php echo helper_alternate_class() ?>>
		<td><?php echo string_display_line_links( $row['name'] )?></td>
		<td><?php echo nl2br( string_display_links( $row['description'] ) )?></td>
		<td class="right" width="205">
			<form action="<?php echo plugin_page( 'edit_product_backlog.php' ) ?>" method="post">
				<input type="submit" name="edit[<?php echo $row['id']?>]" 
					value="<?php echo plugin_lang_get( 'button_edit' )?>" style="width:100px;" />
				<input type="hidden" name="pageFrom" value="product_backlogs.php">
			</form>
			<form action="<?php echo plugin_page( 'delete_product_backlog.php' ) ?>" method="post">
				<input type="hidden" name="product_backlog_id" value="<?php echo $row['id']?>">
				<input type="submit" name="deleteProductBacklog" 
					value="<?php echo plugin_lang_get( 'button_delete' )?>" 
					style="width:100px;" 
					<?php 
						if( $agilemantis_pb->checkProductBacklogTeam( $row['id'] ) == true 
						|| $agilemantis_pb->productBacklogHasStoriesLeft( $row['name'] ) == false ) {
					?>
						disabled
					<?php }?>> 
			</form>
		</td>
	</tr>
	<?php }}?>
</table></div>
<?php } else { ?>
<br>
	<center><span style="color:red; font-size:16px; font-weight:bold;"></span></center>
<?php } ?>
<?php html_page_bottom() ?>