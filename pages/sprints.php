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


html_page_top( plugin_lang_get( 'manage_sprints_title' ) );
$t_user_right = $agilemantis_au->authUser();
if( $t_user_right == 2 || $t_user_right == 3 || current_user_is_administrator() ) {
	?>
<br>
<?php
	include (AGILEMANTIS_PLUGIN_URI . '/pages/footer_menu.php');
	
	# delete selected sprint by id
	if( $_POST['deleteSprint'] != "" ) {
		$agilemantis_sprint->id = ( int ) $_POST['sprint_id'];
		$agilemantis_sprint->deleteSprint();
	}
	?>
<br>
<div class="table-container">
	<table align="center" class="width100" cellspacing="1">
		<tr>
			<td colspan="4"><b><?php echo plugin_lang_get( 'manage_sprints_title' )?></b>
				<form action="<?php echo plugin_page("edit_sprint.php")?>" method="post">
					<input type="hidden" name="new_sprint" value="1">
					<input type="submit" name="submit"
						value="<?php echo plugin_lang_get( 'manage_sprints_add' )?>">
				</form>
			</td>
			<td colspan="2">
				<form action="" method="POST" style="float: right">
			<?php if( $_GET['klickStatus'] == 1 ) { ?>
				<input type="hidden" name="disable_click" value="1">
			<?php } ?>
			<input type="checkbox" name="show_all_sprints"
						<?php 
							if( $_POST['show_all_sprints'] == '1' 
								|| ( $_GET['klickStatus'] == 1 
								&& $_POST['disable_click'] != 1 ) ) {
							$klick=1;
						?>
						checked <?php } else {
							$klick = 0;
						}?> value="1"
						onClick="this.form.submit();">
			<?php echo plugin_lang_get( 'manage_sprints_show_closed' )?>
			</form>
			</td>
		</tr>
		<tr>
			<td style="background: #C8C8E8;"><b><a
					href="<?php echo plugin_page("sprints.php")?>&sort_by=id&klickStatus=
							<?php echo $klick?>">Sprint </a></b></td>
			<td style="background: #C8C8E8;"><b><a
					href="<?php echo plugin_page("sprints.php")?>&sort_by=start&klickStatus=
							<?php echo $klick?>">
							<?php echo plugin_lang_get( 'manage_sprints_begin' )?>
							</a></b></td>
			<td style="background: #C8C8E8;"><b><a
					href="<?php echo plugin_page("sprints.php")?>&sort_by=end&klickStatus=
							<?php echo $klick?>"><?php echo plugin_lang_get( 'manage_sprints_end' )?>
							</a></b></td>
			<td style="background: #C8C8E8;"><b><a
					href="<?php echo plugin_page("sprints.php")?>&sort_by=rest&klickStatus=
							<?php echo $klick?>"><?php echo plugin_lang_get( 'manage_sprints_rest' )?>
					</a></b></td>
			<td style="background: #C8C8E8;"><b><a
					href="<?php echo plugin_page("sprints.php")?>&sort_by=team&klickStatus=
							<?php echo $klick?>">Team</a></b></td>
			<td style="background: #C8C8E8;"><b>
					<?php echo plugin_lang_get( 'common_actions' )?></b></td>
		</tr>
	<?php
		# get all sprints and list in a table
	$sprints = $agilemantis_sprint->getSprints();
	foreach( $sprints as $num => $row ) {
		switch( $row['status'] ) {
			case 0:
				$color = '#fcbdbd';
				$status = plugin_lang_get( 'status_open' );
				break;
			case 1:
				$color = '#C2DFFF';
				$status = plugin_lang_get( 'status_running' );
				break;
			case 2:
				$color = '#c9ccc4';
				$status = plugin_lang_get( 'status_closed' );
				break;
		}
		
		# format sprint start and end date
		$convertedDateStart = substr($row['start'], 0, 10);
		$convertedDateEnd = substr($row['end'], 0, 10);
		$temp_start_date = explode('-',$convertedDateStart);
		$temp_end_date = explode('-',$convertedDateEnd);		
		$row['start'] = mktime( 0, 0, 0, $temp_start_date[1], $temp_start_date[2], $temp_start_date[0] );
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
		
		if( stristr( $anzahl_tage, "-" ) ) {
			$anzahl_tage = str_replace( "-", "", $anzahl_tage );
		}
		
		if( ($row['status'] == 0 
			&& $agilemantis_sprint->sprintHasUserStories( $row['sname'] ) == false) ) {
			$do_not_delete = true;
		} else {
			$do_not_delete = false;
		}
		
		$agilemantis_sprint->sprint_id = $row['sid'];
		?>
	<tr>
			<td style="background:<?php echo $color?>;">
			<?php echo string_display_line_links( $row['sname'] )?>
		</td>
			<td style="background:<?php echo $color?>;">
			<?php echo date( 'd.m.Y',$row['start'] )?>
		</td>
			<td style="background:<?php echo $color?>;">
			<?php echo date( 'd.m.Y',$row['end'] )?>
		</td>
			<td style="background:<?php echo $color?>;">
			<?php echo $anzahl_tage?> <?php echo plugin_lang_get( 'days' )?>
		</td>
			<td style="background:<?php echo $color?>;">
			<?php echo string_display_line_links( $agilemantis_sprint->getTeamById( $row['team_id'] ))?>
		</td>
			<td style="background:<?php echo $color?>;">
				<form method="post"
					action="<?php echo plugin_page( 'edit_sprint.php' ) ?>">
					<input type="submit" name="edit[<?php echo $row['sid']?>]"
						value="<?php echo plugin_lang_get( 'button_edit' )?>">
				</form>
				<form method="post"
					action="<?php echo plugin_page( 'delete_sprint.php' ) ?>">
					<input type="hidden" name="sprint_id"
						value="<?php echo $row['sid']?>"> <input type="submit"
						name="deleteSprint"
						value="<?php echo plugin_lang_get( 'button_delete' )?>"
						<?php if( $do_not_delete == false ) { ?> disabled <?php } ?>>
				</form>
			</td>
		</tr>
	<?php } ?>
</table>
</div>
<br>
<div class="table-container">
	<table align="center" class="width100" cellspacing="1">
		<tr>
			<td style="background: #fcbdbd;"><?php echo plugin_lang_get( 'status_open' )?></td>
			<td style="background: #C2DFFF;"><?php echo plugin_lang_get( 'status_running' )?></td>
			<td style="background: #c9ccc4;"><?php echo plugin_lang_get( 'status_closed' )?></td>
		</tr>
	</table>
</div>
<?php
	} else {
?>
<br>
<center>
	<span style="color: red; font-size: 16px; font-weight: bold;">
		<?php echo plugin_lang_get( 'manage_sprints_error_921500' )?>
	</span>
</center>
<?php
	}
?>
<?php html_page_bottom() ?>