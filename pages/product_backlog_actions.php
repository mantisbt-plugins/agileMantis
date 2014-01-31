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
<br>
<table align="center" class="width100" cellspacing="1">
	<tr>
		<td colspan="7">
			<b>Product Backlog</b>
			<form action="<?php echo plugin_page("product_backlog.php")?>" method="post">
				<input type="submit" name="chose_product_backlog" value="<?php echo plugin_lang_get( 'product_backlog_chose' )?>">
			</form>			
			<form action="<?php echo plugin_page("edit_sprint.php")?>" method="post">
				<input type="hidden" name="productBacklogName" value="<?php echo $product_backlog?>">
				<input type="hidden" name="fromProductBacklog" value="1">
				<?php if($disable_button == ''){?>
					<input type="hidden" name="team_id" value="<?php echo $team_id?>">
				<?php }?>
				<input type="submit" name="add_new_sprint" value="<?php echo plugin_lang_get( 'product_backlog_add_sprint' )?>" <?php echo $disable_button?>>
			</form>
		</td>
	</tr>
	<?php 
		# get all sprint which work on a product backlog and get the latest out of it
		$pb_info = $pb->getProductBacklogByName($product_backlog);
		if($pb->checkProductBacklogMoreOneTeam($product_backlog)){
			$team->id = $pb->getTeamIdByBacklog($pb_info[0]['id']);
			$team_info = $team->getSelectedTeam();
			$sprints = $pb->productBacklogHasRunningSprint($pb_info[0]['id']);
			if(!empty($sprints)){
				foreach($sprints AS $num => $row){
					$sprint_start_date = explode('-',$row['start']);
					$sprint_end_date = explode('-',$row['end']);
					$row['start'] = mktime(0,0,0,$sprint_start_date[1],$sprint_start_date[2],$sprint_start_date[0]);
					$row['end'] = mktime(0,0,0,$sprint_end_date[1],$sprint_end_date[2],$sprint_end_date[0]);
					if($team->id == $row['team_id']){
						$sprintName = $row['name'];
					} 
				}
			}
		}
	?>
	<tr>
		<td class="category"><?php echo plugin_lang_get( 'product_backlog_name' )?></td>
		<td class="category"><?php echo plugin_lang_get( 'common_description' )?></td>
		<?php if($pb->checkProductBacklogMoreOneTeam($product_backlog)){?>
		<td class="category">Team</td>
		<td class="category">Product Owner</td>
		<td class="category">Scrum Master</td>
		<td class="category"><?php echo plugin_lang_get( 'product_backlog_current_sprint' )?></td>
		<?php }?>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td><?php echo $product_backlog?></td>
		<td><?php echo nl2br($pb_info[0]['description'])?></td>
		<?php if($pb->checkProductBacklogMoreOneTeam($product_backlog)){?>
		<td><?php echo $team_info[0]['name']?></td>
		<td><?php echo $team->getUserById($team->getTeamProductOwner())?></td>
		<td><?php echo $team->getUserById($team->getTeamScrumMaster())?></td>
		<td><a href="<?php echo plugin_page('sprint_backlog.php')."&sprintName=".urlencode($sprintName)?>"><?php echo $sprintName?></a></td>
		<?php }?>
	</tr>
</table>