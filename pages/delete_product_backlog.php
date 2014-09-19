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


	
	html_page_top( plugin_lang_get( 'delete_product_backlog_delete_button' ) );
	
	# get product backlog information by id
	$agilemantis_pb->id = (int) $_POST['product_backlog_id'];
	$productBacklog = $agilemantis_pb->getSelectedProductBacklog();
?>
	<br>
	<div align="center">
		<hr width="50%">
			<?php echo plugin_lang_get( 'delete_product_backlog' )?><br>
			Product Backlog: <?php echo $productBacklog[0]['name']?><br><br>
			<form action="<?php echo plugin_page('product_backlogs.php') ?>" method="post">
				<input type="hidden" name="action" value="deleteProductBacklog">
				<input type="hidden" name="product_backlog_id" value="<?php 
						echo (int) $_POST['product_backlog_id']?>">
				<input type="submit" name="deleteProductBacklog" value="<?php 
						echo plugin_lang_get( 'delete_product_backlog_delete_button' )?>" class="button">
				<input type="submit" name="backProductBacklog" value="<?php 
						echo plugin_lang_get( 'button_back' )?>" class="button">
			</form>
		<hr width="50%">
	</div>
<?php html_page_bottom() ?>