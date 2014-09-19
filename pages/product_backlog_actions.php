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


?>
<br>
<div class="table-container">
	<table align="center" class="width100" cellspacing="1">
		<tr>
			<td colspan="7"><b>Product Backlog</b>
				<form action="<?php echo plugin_page("product_backlog.php")?>"
					method="post">
					<input type="submit" name="chose_product_backlog"
						value="<?php echo plugin_lang_get( 'product_backlog_chose' )?>">
				</form>
				<form action="<?php echo plugin_page("edit_sprint.php")?>"
					method="post">
					<input type="hidden" name="productBacklogName"
						value="<?php echo $product_backlog?>"> <input type="hidden"
						name="fromProductBacklog" value="1">
				<?php if($disable_button == ''){?>
					<input type="hidden" name="team_id" value="<?php echo $t_team_id?>">
				<?php }?>
				<input type="submit" name="add_new_sprint"
						value="<?php echo plugin_lang_get( 'product_backlog_add_sprint' )?>"
						<?php echo $disable_button?>>
				</form></td>
		</tr>
	<?php
		# get all sprint which work on a product backlog and get the latest out of it
	$pb_info = $agilemantis_pb->getProductBacklogByName( $product_backlog );
	if( $agilemantis_pb->checkProductBacklogMoreOneTeam( $product_backlog ) ) {
		$agilemantis_team->id = $agilemantis_pb->getTeamIdByBacklog( $pb_info[0]['id'] );
		$team_info = $agilemantis_team->getSelectedTeam();
		$sprints = $agilemantis_pb->productBacklogHasRunningSprint( $pb_info[0]['id'] );
		if( !empty( $sprints ) ) {
			foreach( $sprints as $num => $row ) {
				$sprint_start_date = explode( '-', $row['start'] );
				$sprint_end_date = explode( '-', $row['end'] );
				$row['start'] = mktime( 0, 0, 0, $sprint_start_date[1], $sprint_start_date[2], 
					$sprint_start_date[0] );
				$row['end'] = mktime( 0, 0, 0, $sprint_end_date[1], $sprint_end_date[2], 
					$sprint_end_date[0] );
				if( $agilemantis_team->id == $row['team_id'] ) {
					$sprintName = $row['name'];
				} 
			}
		}
	}
	?>
	<tr>
			<td class="category"><?php echo plugin_lang_get( 'product_backlog_name' )?></td>
			<td class="category"><?php echo plugin_lang_get( 'common_description' )?></td>
		<?php if( $agilemantis_pb->checkProductBacklogMoreOneTeam( $product_backlog ) ) {?>
		<td class="category">Team</td>
			<td class="category">Product Owner</td>
			<td class="category">Scrum Master</td>
			<td class="category"><?php echo plugin_lang_get( 'product_backlog_current_sprint' )?></td>
		<?php }?>
	</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td><?php echo $product_backlog?></td>
			<td><?php echo nl2br($pb_info[0]['description'])?></td>
		<?php if( $agilemantis_pb->checkProductBacklogMoreOneTeam( $product_backlog ) ) { ?>
		<td><?php echo $team_info[0]['name']?></td>
			<td><?php echo $agilemantis_team->getUserName( $agilemantis_team->getTeamProductOwner() )?></td>
			<td><?php echo $agilemantis_team->getUserName( $agilemantis_team->getTeamScrumMaster() )?></td>
			<td><a
				href="<?php echo plugin_page( 'sprint_backlog.php' )."&sprintName=".
							urlencode( $sprintName )?>"><?php echo $sprintName?></a></td>
		<?php }?>
	</tr>
	</table>
</div>