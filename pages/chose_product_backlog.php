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
	
	html_page_top(plugin_lang_get( 'product_backlog_chose' ));
?>
<br>
<table align="center" class="width100" cellspacing="1">
	<tr>
		<td colspan="5"><b><?php echo plugin_lang_get( 'product_backlog_chose' )?></b></td>
	</tr>
	<tr>
		<td class="category"><?php echo plugin_lang_get( 'product_backlog_name' )?></td>
		<td class="category"><?php echo plugin_lang_get( 'common_description' )?></td>
		<td class="category"><?php echo plugin_lang_get( 'product_backlog_running_sprint' )?></td>
		<td class="category"><?php echo plugin_lang_get( 'product_backlog_projects' )?></td>
		<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
	</tr>
	<?php 
		
		# get all product backlog and user team information
		$userTeams = $team->allTeamsByUser($user_id);
		$product_backlogs = $pb->getProductBacklogs();

		if($userTeams){
			foreach($userTeams AS $key => $value){
				$user_teams .= $value['team_id'].',';
			}
			$user_teams = substr($user_teams,0,-1);
		}
		$user_product_backlogs = $team->getBacklogByTeam($user_teams);
		
		# filter shown product backlogs by user rights
		if($_SESSION['ISMANTISUSER']){
			$show_all_teams = false;
		}
		
		if($_SESSION['ISMANTISADMIN'] && $_POST['chose_product_backlog'] == '' && $_SESSION['ISMANTISUSER'] == false){
			$show_all_teams = true;
		}
		
		if($_SESSION['ISMANTISADMIN'] && $_POST['chose_product_backlog'] != '' && $_SESSION['ISMANTISUSER'] == true){
			$show_all_teams = true;
		}
		
		if($_SESSION['ISMANTISADMIN'] && $_POST['chose_product_backlog'] == '' && $_SESSION['ISMANTISUSER'] == true){
			$show_all_teams = false;
		}
		
		if($_SESSION['ISMANTISADMIN'] && empty($userTeams)){
			$show_all_teams = true;
		}
		
		# list all product backlogs in a table
		if(!empty($product_backlogs)){
			foreach($product_backlogs AS $num => $row){
				$sprints = $pb->productBacklogHasRunningSprint($row['id']);
				$running_sprints = '';
				if(!empty($sprints)){
					foreach($sprints AS $key => $value){
						$date_start = explode('-',$value['start']);
						$date_end = explode('-',$value['end']);
						$running_sprints .= '<b>'.$value['name'].'</b>'.plugin_lang_get( 'product_backlog_from' ).$date_start[2].'.'.$date_start[1].'.'.$date_start[0].plugin_lang_get( 'product_backlog_till' ).$date_end[2].'.'.$date_end[1].'.'.$date_end[0].', <br>';
					}
				}
				$projects = $pb->getBacklogProjects($row['id']);
				$project_list = '';

				if(!empty($projects)){
					foreach($projects AS $key => $value){
						$project_list .= $value['name'].', ';
					}
				}
				if($show_all_teams === false){
					if(!empty($user_product_backlogs)){
						foreach($user_product_backlogs AS $key => $value){
							if($value['pb_id'] == $row['id']){
				?>
								<form action="<?php echo plugin_page("product_backlog")?>" method="post">
									<input type="hidden" name="productBacklogName" value="<?php echo $row['name']?>">
									<tr <?php echo helper_alternate_class() ?>>
										<td><?php echo $row['name']?></td>
										<td><?php echo nl2br($row['description'])?></td>
										<td><?php echo substr($running_sprints,0,-6)?></td>
										<td><?php echo substr($project_list,0,-2)?></td>
										<td><input type="submit" name="submit" value="<?php echo plugin_lang_get( 'product_backlog_open_backlog' )?>"></td>
									</tr>
								</form>
				<?php	
							}
						}
					}
				} elseif($show_all_teams === true) {?>
					<form action="<?php echo plugin_page("product_backlog")?>" method="post">
						<input type="hidden" name="productBacklogName" value="<?php echo $row['name']?>">
						<tr <?php echo helper_alternate_class() ?>>
							<td><?php echo $row['name']?></td>
							<td><?php echo nl2br($row['description'])?></td>
							<td><?php echo substr($running_sprints,0,-6)?></td>
							<td><?php echo substr($project_list,0,-2)?></td>
							<td><input type="submit" name="submit" value="<?php echo plugin_lang_get( 'product_backlog_open_backlog' )?>"></td>
						</tr>
					</form>
	<?php
				}
			}
		}
	?>
</table>