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


	html_page_top(plugin_lang_get( 'product_backlog_chose' ));
	print_recently_visited();
?>
<br>
<div class="table-container">
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
	$userTeams = $agilemantis_team->allTeamsByUser( $user_id );
	$product_backlogs = $agilemantis_pb->getProductBacklogs();
	
	if( $userTeams ) {
		foreach( $userTeams as $key => $value ) {
			$user_teams .= $value['team_id'] . ',';
		}
		$user_teams = substr( $user_teams, 0, -1 );
	}
	$user_product_backlogs = $agilemantis_team->getBacklogByTeam( $user_teams );
	
	# filter shown product backlogs by user rights
	
	$show_all_teams = false;
	
	if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] ) {
		
		$show_all_teams = true;
	} else if ($_SESSION['AGILEMANTIS_ISMANTISUSER'] && count( $userTeams ) == 1 ) {
		
		$show_all_teams = false;
	} else {
		$show_all_teams = true;
	}

	
// 	if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] 
// 		&& $_SESSION['AGILEMANTIS_ISMANTISUSER'] == false ) {

// 		$show_all_teams = true;
// 	}
	
// 	if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] 
// 		&& $_SESSION['AGILEMANTIS_ISMANTISUSER'] == true ) {

// 		$show_all_teams = true;
// 	}
	
// 	if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] && empty( $userTeams ) ) {

// 		$show_all_teams = true;
// 	}

	
	# list all product backlogs in a table
	if( !empty( $product_backlogs ) ) {
		foreach( $product_backlogs as $num => $row ) {
			$sprints = $agilemantis_pb->productBacklogHasRunningSprint( $row['id'] );
			$running_sprints = '';
			if( !empty( $sprints ) ) {
				foreach( $sprints as $key => $value ) {
					$convertedDateStart = substr( $value['start'], 0, 10 );
					$convertedDateEnd = substr( $value['end'], 0, 10 );
					$date_start = explode( '-', $convertedDateStart );
					$date_end = explode( '-', $convertedDateEnd );
					$running_sprints .= '<b>' . string_display_line_links( $value['name'] ) . '</b>' .
						 plugin_lang_get( 'product_backlog_from' ) . $date_start[2] . '.' .
						 $date_start[1] . '.' . $date_start[0] .
						 plugin_lang_get( 'product_backlog_till' ) . $date_end[2] . '.' .
						 $date_end[1] . '.' . $date_end[0] . ', <br>';
				}
			}
			$projects = $agilemantis_pb->getBacklogProjects( $row['id'] );
			$project_list = '';
			
			if( !empty( $projects ) ) {
				foreach( $projects as $key => $value ) {
					$project_list .= string_display( $value['name'] ) . ', ';
				}
			}
			if( $show_all_teams === false ) {
				if( !empty( $user_product_backlogs ) ) {
					foreach( $user_product_backlogs as $key => $value ) {
						if( $value['pb_id'] == $row['id'] ) {
							?>
								<form action="<?php echo plugin_page( "product_backlog" )?>"
			method="post">
			<input type="hidden" name="productBacklogName"
				value="<?php echo string_display($row['name'])?>">
			<tr <?php echo helper_alternate_class() ?>>
				<td><?php echo string_display_line_links( $row['name'] )?></td>
				<td><?php echo nl2br( string_display_line_links( $row['description'] ) )?></td>
				<td><?php echo substr( $running_sprints, 0, -6 )?></td>
				<td><?php echo substr( $project_list, 0, -2 )?></td>
				<td><input type="submit" name="submit"
					value="<?php echo plugin_lang_get( 'product_backlog_open_backlog' )?>"></td>
			</tr>
		</form>
				<?php
						}
					}
				}
			} elseif( $show_all_teams === true ) {
				?>
					<form action="<?php echo plugin_page( "product_backlog" )?>"
			method="post">
			<input type="hidden" name="productBacklogName"
				value="<?php echo string_display( $row['name'] )?>">
			<tr <?php echo helper_alternate_class() ?>>
				<td><?php echo string_display_line_links( $row['name'] )?></td>
				<td><?php echo nl2br( string_display_line_links( $row['description'] ) )?></td>
				<td><?php echo substr( $running_sprints, 0, -6 )?></td>
				<td><?php echo substr( $project_list, 0, -2 )?></td>
				<td><input type="submit" name="submit"
					value="<?php echo plugin_lang_get( 'product_backlog_open_backlog' )?>"></td>
			</tr>
		</form>
	<?php
			}
		}
	}
	?>
</table>
</div>