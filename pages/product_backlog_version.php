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
?>
<td>
<a class="version_tooltip" href="javascript: void(0)">
	<?php echo $row['project_name']?> <?php echo $row['target_version']?>
	<span>
		<b><?php echo $row['project_name']?> <?php echo $row['target_version']?></b>
		<br><br>
		<b><?php echo plugin_lang_get( 'product_backlog_time' )?></b>
		<br>
		<?php echo date('d.m.Y',$version_info['date_order'])?>
		<br><br>
		<b>Tracker</b>
		<br>
		<?php echo plugin_lang_get( 'product_backlog_amount' )?>: <?php echo $version->getVersionTracker($row['project_id'],$row['target_version'], '80,90') + $version->getVersionTracker($row['project_id'],$row['target_version'], '10,20,30,40,50,60,70') ?>
		<br>
		<?php echo plugin_lang_get( 'product_backlog_opened' )?>: <?php echo $version->getVersionTracker($row['project_id'],$row['target_version'], '10,20,30,40,50,60,70')?>
		<br>
		<br>
		<b>User Stories</b>
		<br>
		<?php echo plugin_lang_get( 'product_backlog_amount' )?>: <?php echo $version->getVersionUserStories($row['project_id'],$row['target_version'])?>
		<br>
		<?php echo plugin_lang_get( 'product_backlog_opened' )?>: <?php echo $version->getNumberOfUserStories($row['project_id'],$row['target_version'])?>
		<?php if(!empty($version_info['description'])){?>
		<br>
		<br>
		<b><?php echo plugin_lang_get( 'common_description' )?></b>
		<br>
		<?php echo nl2br($version_info['description'])?>
		<?php } ?>
		</span>
	</a>
</td>