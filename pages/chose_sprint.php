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



# agileMantis page information
$_GET['page'] = str_replace( 'agileMantis/', '', $_GET['page'] );
$page_name = str_replace( '.php', '', $_GET['page'] );

# call taskboard or sprint backlog page
switch( $_GET['page'] ) {
	case 'taskboard.php':
		$name = "Taskboard";
		break;
	default:
	case 'sprint_backlog.php':
		$name = 'Sprint Backlog';
		break;
}

html_page_top( plugin_lang_get( 'sprint_backlog_chose_sprint' ) );
print_recently_visited();

?>
<br>
<div class="table-container">
	<table align="center" class="width100" cellspacing="1">
		<tr>
			<td colspan="5"><b><?php echo $name?> - <?php 
						echo plugin_lang_get( 'sprint_backlog_chose_sprint' )?></b></td>
			<td colspan="2">
				<form action="<?php echo plugin_page($_GET['page'])?>" method="post"
					style="float: right">
				<?php if($_GET['klickStatus']==1){?>
				<input type="hidden" name="disable_click" value="1">
				<?php }?>
				<input type="hidden" name="do_not_enter_sprint" value="1"> <input
						type="checkbox" name="show_all_sprints"
				<?php 
					if( $_POST['show_all_sprints'] == '1' 
							|| ( $_GET['klickStatus'] == 1 
							&& $_POST['disable_click'] != 1 ) ) {
 
						$klick=1; ?> checked <?php 
					} else {
						$klick = 0;
					}?> value="1"
						onClick="this.form.submit();">
				<?php echo plugin_lang_get( 'sprint_backlog_show_closed' )?>
			</form>
			</td>
		</tr>
		<tr>
			<td class="category"><a
				href="<?php echo plugin_page($_GET['page'])?>&sort_by=id&klickStatus=<?php 
					echo $klick?>">Sprint</a></td>
			<td class="category"><a
				href="<?php echo plugin_page($_GET['page'])?>&sort_by=start&klickStatus=<?php 
					echo $klick?>"><?php echo plugin_lang_get( 'sprint_backlog_begin' ) ?></a></td>
			<td class="category"><a
				href="<?php echo plugin_page($_GET['page'])?>&sort_by=end&klickStatus=<?php 
					echo $klick?>"><?php echo plugin_lang_get( 'sprint_backlog_end' ) ?></a></td>
			<td class="category"><a
				href="<?php echo plugin_page($_GET['page'])?>&sort_by=rest&klickStatus=<?php 
					echo $klick?>"><?php echo plugin_lang_get( 'sprint_backlog_rest' ) ?></a></td>
			<td class="category"><a
				href="<?php echo plugin_page($_GET['page'])?>&sort_by=team&klickStatus=<?php 
					echo $klick?>">Team</a></td>
			<td class="category"><a
				href="<?php echo plugin_page($_GET['page'])?>&sort_by=pb&klickStatus=<?php 
					echo $klick?>">Product
					Backlog</a></td>
			<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
		</tr>
	<?php
	
	# get all sprints and team information
	$userTeams = $agilemantis_team->allTeamsByUser( $user_id );
	$sprints = $agilemantis_sprint->getSprints();
	
	# filter sprints by user rights
	if( $_SESSION['AGILEMANTIS_ISMANTISUSER'] ) {
		$show_all_teams = false;
	}
	
	if( $_SESSION['AGILEMANTIS_ISMANTISADMIN'] ) {
		$show_all_teams = true;
	}
	
	# get all sprints and list in a table
	foreach( $sprints as $num => $row ) {
		
		$convertedDateStart = substr($row['start'], 0, 10);
		$convertedDateEnd = substr($row['end'], 0, 10);
		$temp_start_date = explode('-',$convertedDateStart);
		$temp_end_date = explode('-',$convertedDateEnd);
		$row['start'] = mktime( 0, 0, 0, $temp_start_date[1], $temp_start_date[2], 
			$temp_start_date[0] );
		$row['end'] = mktime( 0, 0, 0, $temp_end_date[1], $temp_end_date[2], $temp_end_date[0] );
		
		$end_date = $row['end'];
		if( time() >= $row['start'] ) {
			$start_date = time();
		} else {
			$start_date = $row['start'];
		}
		
		if( $row['status'] == 0 ) {
			$start_date = $row['start'];
		}
		$diff = $end_date - $start_date;
		$anzahl_tage = ceil( $diff / 86400 );
		
		if( $anzahl_tage == 0 && $end_date > time() ) {
			$anzahl_tage = 1;
		} elseif( $anzahl_tage <= 0 ) {
			$anzahl_tage = 0;
		}
		
		# change row color according to sprint status
		if( $row['status'] == 0 ) {
			$bgcolor = '#fcbdbd';
		}
		if( $row['status'] == 1 ) {
			$bgcolor = '#C2DFFF';
		}
		if( $row['status'] == 2 ) {
			$bgcolor = '#c9ccc4';
		}
		if( $show_all_teams === false ) {
			foreach( $userTeams as $key => $value ) {
				if( $row['team_id'] == $value['team_id'] ) {
					?>
						<?php if( $_GET['page'] == 'daily_scrum_meeting.php' ){?>
								<?php if( $row['status'] == 1 ){?>
								<tr style="background-color:<?php echo $bgcolor?>;">
			<form action="<?php echo plugin_page( $_GET['page'] )?>" method="post">
				<input type="hidden" name="sprintName"
					value="<?php echo $row['sname']?>">
				<td><?php echo string_display_line_links( $row['sname'] )?></td>
				<td><?php echo date( 'd.m.Y',$row['start'] )?></td>
				<td><?php echo date( 'd.m.Y',$row['end'] )?></td>
				<td><?php echo $anzahl_tage?> <?php echo plugin_lang_get( 'days' ) ?></td>
				<td><?php echo string_display_line_links( $agilemantis_sprint->getTeamById( $row['team_id'] ) );?></td>
				<td><?php echo string_display_line_links( $agilemantis_sprint->getProductBacklogByTeam( $row['team_id'] ) );?></td>
				<td><input type="submit" name="submit"
					value="<?php echo plugin_lang_get( 'sprint_backlog_backlog' )?>"></td>
			</form>
		</tr>
							<?php } ?>
						<?php } else {?>
							<tr style="background-color:<?php echo $bgcolor?>;">
			<form action="<?php echo plugin_page($_GET['page'])?>" method="post">
				<input type="hidden" name="sprintName"
					value="<?php echo $row['sname']?>">
				<td><?php echo string_display_line_links( $row['sname'] )?></td>
				<td><?php echo date('d.m.Y',$row['start'])?></td>
				<td><?php echo date('d.m.Y',$row['end'])?></td>
				<td><?php echo $anzahl_tage?> <?php echo plugin_lang_get( 'days' ) ?></td>
				<td><?php echo string_display_line_links( $agilemantis_sprint->getTeamById( $row['team_id'] ) );?></td>
				<td><?php echo string_display_line_links( $agilemantis_sprint->getProductBacklogByTeam( $row['team_id'] ) );?></td>
				<td><input type="submit" name="submit"
					value="<?php echo plugin_lang_get( 'sprint_backlog_backlog' )?>"></td>
			</form>
		</tr>
						<?php }?>
				<?php
				}
			}
		} elseif( $show_all_teams === true ) {
			?>
				<?php if( $_GET['page'] == 'daily_scrum_meeting.php' ){?>
					<?php if( $row['status'] == 1 ){?>
					<tr style="background-color:<?php echo $bgcolor?>;">
			<form action="<?php echo plugin_page( $_GET['page'] )?>" method="post">
				<input type="hidden" name="sprintName"
					value="<?php echo $row['sname']?>">
				<td><?php echo string_display_line_links( $row['sname'] )?></td>
				<td><?php echo date( 'd.m.Y',$row['start'] )?></td>
				<td><?php echo date( 'd.m.Y',$row['end'] )?></td>
				<td><?php echo $anzahl_tage?> <?php echo plugin_lang_get( 'days' ) ?></td>
				<td><?php echo string_display_line_links( $agilemantis_sprint->getTeamById( $row['team_id'] ) );?></td>
				<td><?php echo string_display_line_links( $agilemantis_sprint->getProductBacklogByTeam( $row['team_id'] ) );?></td>
				<td><input type="submit" name="submit"
					value="<?php echo plugin_lang_get( 'sprint_backlog_backlog' )?>"></td>
			</form>
		</tr>
					<?php } ?>
				<?php } else {?>
					<tr style="background-color:<?php echo $bgcolor?>;">
			<form action="<?php echo plugin_page( $_GET['page'] )?>" method="post">
				<input type="hidden" name="sprintName"
					value="<?php echo $row['sname']?>">
				<td><?php echo string_display_line_links( $row['sname'] )?></td>
				<td><?php echo date( 'd.m.Y',$row['start'] )?></td>
				<td><?php echo date( 'd.m.Y',$row['end'] )?></td>
				<td><?php echo $anzahl_tage?> <?php echo plugin_lang_get( 'days' ) ?></td>
				<td><?php echo string_display_line_links( $agilemantis_sprint->getTeamById( $row['team_id'] ) );?></td>
				<td><?php echo string_display_line_links( $agilemantis_sprint->getProductBacklogByTeam( $row['team_id'] ) );?></td>
				<td><input type="submit" name="submit"
					value="<?php echo plugin_lang_get( 'sprint_backlog_backlog' )?>"></td>
			</form>
		</tr>
				<?php }?>
		<?php }?>
	<?php }?>
</table>
</div>
<br>
<?php
html_page_bottom();
?>