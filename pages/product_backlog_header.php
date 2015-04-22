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


html_page_top( plugin_lang_get( 'product_backlog_title' ) );
print_recently_visited();

# merge global $_POST / $_GET array
$request = array_merge( $_POST, $_GET );

# get product backlog information
$product_backlog = $request['productBacklogName'];
$userTeams = $agilemantis_team->allTeamsByUser( $user_id );

# get product backlog name
if( $_SESSION['AGILEMANTIS_ISMANTISUSER'] && $teams == 1 && empty( $product_backlog ) ) {
	$team_backlog = $agilemantis_team->getBacklogByTeam( $userTeams[0]['team_id'] );
	$agilemantis_pb->id = $team_backlog[0]['pb_id'];
	$information = $agilemantis_pb->getSelectedProductBacklog();
	$product_backlog = $information[0]['name'];
	unset( $agilemantis_pb->id );
}

# save / update values (ranking order , business value) from product backlog table
if( $_POST['action'] == 'save_values' ) {
	$changed = false;
	$bug_id = $_POST['us_id'];
	if( !empty( $_POST['rankingOrder'] ) ) {
		foreach( $_POST['rankingOrder'] as $num => $row ) {
			if( $row != $_POST['rankingOrderOld'][$num] ) {
				$agilemantis_pb->addRankingOrder( $num, $row );
				$changed = true;
			}
		}
	}
	
	if( !empty( $_POST['businessValue'] ) ) {
		foreach( $_POST['businessValue'] as $num => $row ) {
			if( $row != $_POST['businessValueOld'][$num] ) {
				$agilemantis_pb->addBusinessValue( $num, $row );
				$changed = true;
			}
		}
	}
	if( $changed ) {
		$system = '<center><span style="color:green; font-size:16px; font-weight:bold;">' .
			 plugin_lang_get( 'product_backlog_saved_successfully' ) . '</span></center><br>';
	}
}

# get developer and scrum-master roles
$developer = $agilemantis_team->getProductBacklogTeamRole( $product_backlog, $user_id, 3 );

# set team id, product backlog id for a developer
if( !empty( $developer ) ) {
	$one_backlog_more_teams = false;
	foreach( $developer as $num => $row ) {
		if( $t_pb_id != '' && $t_team_id != $row['team_id'] ) {
			$one_backlog_more_teams = true;
			break;
		}
		$t_pb_id = $row['pb_id'];
		$t_team_id = $row['team_id'];
	}
}

# if many teams work on one product backlog, set only one team id and product backlog id
if( $one_backlog_more_teams == false ) {
	$scrumMaster = $agilemantis_team->getProductBacklogTeamRole( $product_backlog, $user_id, 2 );
	
	if( !empty( $scrumMaster ) ) {
		$one_backlog_more_teams = false;
		foreach( $scrumMaster as $num => $row ) {
			if( $t_pb_id != '' && $t_team_id != $row['team_id'] ) {
				$one_backlog_more_teams = true;
				break;
			}
			$t_pb_id = $row['pb_id'];
			$t_team_id = $row['team_id'];
		}
	}
}

# check if product backlog has running sprints
$sprints = $agilemantis_pb->productBacklogHasRunningSprint( $t_pb_id );
if( !empty( $sprints ) ) {
	$no_sprint_found = true;
	$sprint_in_same_time = 0;
	foreach( $sprints as $num => $row ) {
		
		$convertedDateStart = substr($row['start'], 0, 10);
		$convertedDateEnd = substr($row['end'], 0, 10);
		$sprint_start_date = explode( '-', $convertedDateStart );
		$sprint_end_date = explode( '-', $convertedDateEnd );
		
		$row['start'] = mktime( 0, 0, 0, $sprint_start_date[1], $sprint_start_date[2], 
			$sprint_start_date[0] );
		$row['end'] = mktime( 0, 0, 0, $sprint_end_date[1], $sprint_end_date[2], 
			$sprint_end_date[0] );
		if( $row['team_id'] == $t_team_id && time() >= $row['start'] && time() <= $row['end'] ) {
			$sprint_start = $row['start'];
			$sprint_end = $row['end'];
			$no_sprint_found = false;
			$sprint_in_same_time++;
		}
	}
}

# if many teams work on one product backlog, disable some buttons in the product backlog dialogue
if( $one_backlog_more_teams || (empty( $developer ) && empty( $scrumMaster )) ) {
	$disable_button = 'disabled';
} else {
	$disable_button = '';
}

?>
<br>
<div class="table-container">
	<table align="center" class="width100" cellspacing="1">
		<tr>
			<td colspan="7"><b><?php echo plugin_lang_get( 'product_backlog_title' )?></b>
				<form action="<?php echo plugin_page("availability.php")?>"
					method="post">
				<?php if($disable_button == ''){?>
					<input type="hidden" name="team_id" value="<?php echo $t_team_id?>">
				<?php }?>
				<input type="hidden" name="month" value="2"> <input type="hidden"
						name="fromProductBacklog" value="1"> <input type="hidden"
						name="productBacklogName" value="<?php echo $product_backlog?>">
				<?php
					# get all team member
					if($t_team_id) {
						$agilemantis_team->id = $t_team_id;
						$team_member = $agilemantis_team->getTeamDeveloper();
						if(!empty($team_member)){
							foreach($team_member AS $num => $row){
								?><input type="hidden" name="kalender[<?php echo $row['id']?>]"
						value="Open Calender"><?php
							}
						}
					}
				?>
				<input type="submit" name="manage_availability"
						value="<?php echo plugin_lang_get( 'product_backlog_availability' )?>"
						<?php echo $disable_button?>>
				</form>
				<form action="<?php echo plugin_page("capacity.php")?>"
					method="post">
					<input type="hidden" name="productBacklogName"
						value="<?php echo $product_backlog?>">
				<?php if($disable_button == ''){?>
					<input type="hidden" name="team" value="<?php echo $t_team_id?>">
				<?php }?>
				<?php if($no_sprint_found == false && $sprint_in_same_time == 1){?>
					<input type="hidden" name="start"
						value="<?php echo date('d.m.Y',$sprint_start)?>"> <input
						type="hidden" name="end"
						value="<?php echo date('d.m.Y',$sprint_end)?>"> <input
						type="hidden" name="submit_button"
						value="<?php echo plugin_lang_get( 'product_backlog_capacity' )?>">
				<?php } else {?>
					<input type="hidden" name="start"
						value="<?php echo date('d.m.Y')?>">
				<?php }?>
				<input type="hidden" name="fromProductBacklog" value="1"> <input
						type="submit" name="manage_developer_capacities"
						value="<?php echo plugin_lang_get( 'product_backlog_capacityB' )?>"
						<?php echo $disable_button?>>
				</form></td>
		</tr>
	</table>
</div>