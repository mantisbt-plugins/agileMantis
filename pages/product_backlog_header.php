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
	
	html_page_top(plugin_lang_get( 'product_backlog_title' ));
	print_recently_visited();
	
	# merge global $_POST / $_GET array
	$request = array_merge($_POST,$_GET);
	
	# get product backlog information
	$product_backlog = $request['productBacklogName'];
	$userTeams = $team->allTeamsByUser($user_id);

	# get product backlog name
	if($_SESSION['ISMANTISUSER'] && $teams == 1 && empty($product_backlog)){
		$team_backlog = $team->getBacklogByTeam($userTeams[0]['team_id']);
		$pb->id = $team_backlog[0]['pb_id'];
		$information = $pb->getSelectedProductBacklog();
		$product_backlog = $information[0]['name'];
		unset($pb->id);
	}
		
	# save / update values (ranking order , business value) from product backlog table
	if($_POST['action'] == 'save_values'){
		$bug_id = $_POST['us_id'];
		if(!empty($_POST['rankingOrder'])){
			foreach($_POST['rankingOrder'] AS $num => $row){
				$pb->addRankingOrder($num,$row);
			}
		}
		
		if(!empty($_POST['businessValue'])){
			foreach($_POST['businessValue'] AS $num => $row){
				$pb->addBusinessValue($num,$row);
			}
		}
		$system = '<center><span style="color:green; font-size:16px; font-weight:bold;">'.plugin_lang_get( 'product_backlog_saved_successfully' ).'</span></center><br>';
	}

	# get developer and scrum-master roles
	$developer = $team->getProductBacklogTeamRole($product_backlog, $user_id, 3);
	$scrumMaster = $team->getProductBacklogTeamRole($product_backlog, $user_id, 2);

	# set team id, product backlog id for a developer
	if(!empty($developer)){
		$one_backlog_more_teams = false;
		foreach($developer AS $num => $row){
			if($t_pb_id != '' && $t_pb_id == $t_pb_id && $t_team_id != $row['team_id']){
				$one_backlog_more_teams = true;
			}
			$t_pb_id = $row['pb_id'];
			$t_team_id = $row['team_id'];
		}
		if($row['name'] == $product_backlog){
			$team_id = $row['team_id'];
		}
	}
	
	# if many teams work on one product backlog, set only one team id and product backlog id
	if($one_backlog_more_teams == false){
		if(!empty($scrumMaster)){
			$one_backlog_more_teams = false;
			foreach($scrumMaster AS $num => $row){
				if($t_pb_id != '' && $t_pb_id == $t_pb_id && $t_team_id != $row['team_id']){
					$one_backlog_more_teams = true;
				}
				$t_pb_id = $row['pb_id'];
				$t_team_id = $row['team_id'];
			}
		}
		if($row['name'] == $product_backlog){
			$team_id = $row['team_id'];
		}
	}

	# set developer product backlog id
	if($developer[0]['pb_id'] > 0){
		$pb_id = $developer[0]['pb_id'];
	}

	# set scrum-master product backlog id
	if($scrumMaster[0]['pb_id'] > 0){
		$pb_id = $scrumMaster[0]['pb_id'];
	}

	# check if product backlog has running sprints
	$sprints = $pb->productBacklogHasRunningSprint($pb_id);
	if(!empty($sprints)){
		$no_sprint_found = true;
		$sprint_in_same_time = 0;
		foreach($sprints AS $num => $row){

			$sprint_start_date = explode('-',$row['start']);
			$sprint_end_date = explode('-',$row['end']);

			$row['start'] = mktime(0,0,0,$sprint_start_date[1],$sprint_start_date[2],$sprint_start_date[0]);
			$row['end'] = mktime(0,0,0,$sprint_end_date[1],$sprint_end_date[2],$sprint_end_date[0]);
			if($row['team_id'] == $team_id && time() >= $row['start'] && time() <= $row['end']){
				$sprint_start = $row['start'];
				$sprint_end = $row['end'];
				$no_sprint_found = false;
				$sprint_in_same_time++;
			}
		}
	}

	# if many teams work on one product backlog, disable some buttons in the product backlog dialogue
	if($one_backlog_more_teams || (empty($developer) && empty($scrumMaster))){
		$disable_button = 'disabled';
	} else {
		$disable_button = '';
	}

	# stylesheet for the version dialogue
?>
<style type="text/css">
.version_tooltip {
	color				: #000;
	position			: relative;
	text-decoration 	: none;
	border-bottom		: 2px dotted #000;
	padding-bottom		: 2px;
	cursor				: default;
}
.version_tooltip span {
	background-color	: #FFF;
	border				: 1px solid #000;
	margin-left			: -999em;
	position			: absolute;
	text-decoration		: none;
	padding				: 0.8em 1em;
}
.version_tooltip:hover span {
	display				: block;
	font-family			: Arial;
	left				: 10em;
	margin-left			: 0;
	position			: absolute;
	top					: -5em;
	width				: 250px;
	z-index				: 99;
}
</style>
<br>
<table align="center" class="width100" cellspacing="1">
	<tr>
		<td colspan="7">
			<b><?php echo plugin_lang_get( 'product_backlog_title' )?></b>
			<form action="<?php echo plugin_page("availability.php")?>" method="post">
				<?php if($disable_button == ''){?>
					<input type="hidden" name="team_id" value="<?php echo $team_id?>">
				<?php }?>
				<input type="hidden" name="month" value="2">
				<input type="hidden" name="fromProductBacklog" value="1">
				<input type="hidden" name="productBacklogName" value="<?php echo $product_backlog?>">
				<?php
					# get all team member
					$team->id = $team_id;
					$team_member = $team->getTeamDeveloper();
					if(!empty($team_member)){
						foreach($team_member AS $num => $row){?>
							<input type="hidden" name="kalender[<?php echo $row['id']?>]" value="Open Calender">
				<?php
						}
					}
				?>
				<input type="submit" name="manage_availability" value="<?php echo plugin_lang_get( 'product_backlog_availability' )?>" <?php echo $disable_button?>>
			</form>
			<form action="<?php echo plugin_page("capacity.php")?>" method="post">
				<input type="hidden" name="productBacklogName" value="<?php echo $product_backlog?>">
				<?php if($disable_button == ''){?>
					<input type="hidden" name="team" value="<?php echo $team_id?>">
				<?php }?>
				<?php if($no_sprint_found == false && $sprint_in_same_time == 1){?>
					<input type="hidden" name="start" value="<?php echo date('d.m.Y',$sprint_start)?>">
					<input type="hidden" name="end" value="<?php echo date('d.m.Y',$sprint_end)?>">
					<input type="hidden" name="submit_button" value="<?php echo plugin_lang_get( 'product_backlog_capacity' )?>">
				<?php } else {?>
					<input type="hidden" name="start" value="<?php echo date('d.m.Y')?>">
				<?php }?>
				<input type="hidden" name="fromProductBacklog" value="1">
				<input type="submit" name="manage_developer_capacities" value="<?php echo plugin_lang_get( 'product_backlog_capacityB' )?>" <?php echo $disable_button?>>
			</form>
		</td>
	</tr>
</table>