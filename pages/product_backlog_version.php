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

$t_description = project_get_field( $row['project_id'], 'description' );

?>
<td>
	<a class="version_tooltip" href="javascript: void(0)">
	<?php echo $row['project_name']?> <?php echo $row['target_version']?>
	<span>
		<b><?php echo $row['project_name']?> <?php echo $row['target_version']?></b>
		<br><br>
		<?php if($version_info['date_order'] != ""){?>
			<b><?php echo plugin_lang_get( 'product_backlog_time' )?></b>
			<br>
			<?php echo date('d.m.Y',$version_info['date_order'])?>
			<br><br>
		<?php } ?>
		<b><?php echo plugin_lang_get( 'version_issue' )?></b>
		<br>
		<?php echo plugin_lang_get( 'product_backlog_amount' )?>: <?php 
			echo $agilemantis_version->getVersionTracker(
							$row['project_id'], $row['target_version'], '10,20,30,40,50,60,70,80,90') ?>
		<br>
		<?php echo plugin_lang_get( 'product_backlog_opened' )?>: <?php 
			echo $agilemantis_version->getVersionTracker(
				$row['project_id'],$row['target_version'], '10,20,30,40,50,60,70')?>
		<br>
		<br>
		<b>User Stories</b>
		<br>
		<?php echo plugin_lang_get( 'product_backlog_amount' )?>: <?php 
			echo $agilemantis_version->getVersionUserStories(
				$row['project_id'],$row['target_version'])?>
		<br>
		<?php echo plugin_lang_get( 'product_backlog_opened' )?>: <?php 
			echo $agilemantis_version->getNumberOfUserStories(
				$row['project_id'], $row['target_version'] )?>
		<?php if( !empty($version_info['description'] ) ) { ?>
			<br>
			<br>
			<b><?php echo plugin_lang_get( 'common_description' )?></b>
			<br>
			<?php echo nl2br( $version_info['description'] )?>
		<?php } elseif($t_description != "") {?>
			<br>
			<br>
			<b><?php echo plugin_lang_get( 'common_description' )?></b>
			<br>
			<?php echo nl2br( $t_description )?>
		<?php } ?>
		</span>
	</a>
</td>