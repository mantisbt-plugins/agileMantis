<? 
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
	
	html_page_top(plugin_lang_get( 'assume_userstories_title' ));

	# merge global $_GET / $_POST array 
	$request = array_merge($_POST, $_GET);
	
	# get information about product backlog, sprint backlog and latest page
	$product_backlog = $request['product_backlog'];
	$sprintName = $request['sprintName'];
	$fromPage = $request['fromPage'];
	
	# get further sprint information
	$sprint->sprint_id = $request['sprintName'];	
	$sprintinfo = $sprint->getSprintById();	

	# check if different units existing
	$different_units = false;
	if($sprint->getUnitId(plugin_config_get('gadiv_task_unit_mode')) != $sprintinfo['unit_planned_task'] && isset($request['action']) && $sprintinfo['status'] == 1){
		$different_units = true;
	}

	if($request['action'] == 'save'){
		if(!empty($request['assume'])){
			foreach($request['assume'] AS $num => $row){
				$tasked = $sprint->getSprintTasks($row,0);
				if(!empty($tasked) && $different_units){
					foreach($tasked AS $key => $value){
						if($value['rest_capacity'] > 0){
							$sprint->resetPlanned($value['id']);
							$resetPlannedCapacity = true;
						}
					}
				}
				$pb->doUserStoryToSprint($row,$sprintName);
				bug_update_date($row);
			}
			if($resetPlannedCapacity){
				echo '<br><center><span style="color:red; font-size:16px; font-weight:bold;">'.plugin_lang_get( 'assume_userstories_error_106C00' ).'</span></center>';
			}
			echo '<br><center><span style="color:green; font-size:16px; font-weight:bold;">'.plugin_lang_get( 'assume_userstories_assume_successfull' ).'</span></center>';
		} else {
			echo '<br><center><span style="color:red; font-size:16px; font-weight:bold;">'.plugin_lang_get( 'assume_userstories_error_120C00' ).'</span></center>';
		}
	}
	
	# set different filters for assume new user stories
	if(!config_is_set('current_user_assume_userstories_filter',auth_get_current_user_id())){
		config_set('current_user_assume_userstories_filter', '', auth_get_current_user_id());
	}

	if(!config_is_set('current_user_assume_userstories_filter_direction',auth_get_current_user_id())){
		config_set('current_user_assume_userstories_filter_direction', 'ASC', auth_get_current_user_id());
	}

	if(config_get('current_user_assume_userstories_filter_direction',null,auth_get_current_user_id()) == 'ASC'){
		$direction = 'DESC';
	} else {
		$direction = 'ASC';
	}
	
	# check if available 
	if(plugin_config_get('gadiv_ranking_order') == 0 && config_get('current_user_assume_userstories_filter',null,auth_get_current_user_id()) == 'rankingOrder'){
		config_set('current_user_assume_userstories_filter', '', auth_get_current_user_id());
		config_set('current_user_assume_userstories_filter_direction', 'ASC', auth_get_current_user_id());
	}
	
	if(plugin_config_get('gadiv_tracker_planned_costs') == 0 && config_get('current_user_assume_userstories_filter',null,auth_get_current_user_id()) == 'plannedWork'){
		config_set('current_user_assume_userstories_filter', '', auth_get_current_user_id());
		config_set('current_user_assume_userstories_filter_direction', 'ASC', auth_get_current_user_id());
	}

	# get all unresolved user stories
	$undone = $pb->getAllUndoneUserStories($product_backlog);
	
	if(empty($undone)){
		echo '<br><center><span style="color:red; font-size:16px; font-weight:bold;">'.plugin_lang_get( 'assume_userstories_error_120C01' ).'</span></center>';
	}
?>
<br>
<form action="" method="post">
<input type="hidden" name="action" value="save">
<input type="hidden" name="product_backlog" value="<?php echo $product_backlog?>">
<input type="hidden" name="sprintName" value="<?php echo $sprintName?>">
<input type="hidden" name="fromPage" value="<?php echo $fromPage?>">
<input type="hidden" name="fromDailyScrum" value="<?php echo $_POST['fromDailyScrum']?>">
<table align="center" class="width100" cellspacing="1">
	<tr>
		<td colspan="9">
			<div style="float:left"><b><?php echo plugin_lang_get( 'assume_userstories_title' )?></b></div>
			<div style="float:right"><span id="selectedUserStories"><b>0</b> User Stories</span>, <b><span id="chosenStoryPoints">0</span></b> <?php echo plugin_lang_get( 'assume_userstories_chosen_sp' )?></div>
		</td>
	</tr>
	<tr>
		<?php if(plugin_config_get('gadiv_ranking_order')=='1'){?>
			<td class="category" width="60">
				<a href="<?php echo plugin_page("assume_userstories.php")?>&sort_by=rankingOrder&product_backlog=<?php echo urlencode($product_backlog)?>&sprintName=<?php echo urlencode($sprintName)?>&fromPage=<?php echo urlencode($fromPage)?>&direction=<?php echo $direction?>"><?php echo plugin_lang_get( 'assume_userstories_rankingorder' )?></a>
			</td>
		<?php }?>
		<td class="category" width="60">
			<a href="<?php echo plugin_page("assume_userstories.php")?>&sort_by=businessValue&product_backlog=<?php echo urlencode($product_backlog)?>&sprintName=<?php echo urlencode($sprintName)?>&fromPage=<?php echo urlencode($fromPage)?>&direction=<?php echo $direction?>">Business Value</a>
		</td>
		<?php if(plugin_config_get('gadiv_tracker_planned_costs')=='1'){?>
			<td class="category" width="100">
				<a href="<?php echo plugin_page("assume_userstories.php")?>&sort_by=plannedWork&product_backlog=<?php echo urlencode($product_backlog)?>&sprintName=<?php echo urlencode($sprintName)?>&fromPage=<?php echo urlencode($fromPage)?>&direction=<?php echo $direction?>"><?php echo plugin_lang_get( 'assume_userstories_planned_work' )?></a>
			</td>
		<?php }?>
		<td class="category" width="50">
			<a href="<?php echo plugin_page("assume_userstories.php")?>&sort_by=storyPoints&product_backlog=<?php echo urlencode($product_backlog)?>&sprintName=<?php echo urlencode($sprintName)?>&fromPage=<?php echo urlencode($fromPage)?>&direction=<?php echo $direction?>">Story Points</a>
		</td>
		<td class="category">
			<a href="<?php echo plugin_page("assume_userstories.php")?>&sort_by=version&product_backlog=<?php echo urlencode($product_backlog)?>&sprintName=<?php echo urlencode($sprintName)?>&fromPage=<?php echo urlencode($fromPage)?>&direction=<?php echo $direction?>">Version</a>
		</td>
		<td class="category" width="20"></td>
		<td class="category" width="50">
			<a href="<?php echo plugin_page("assume_userstories.php")?>&sort_by=id&product_backlog=<?php echo urlencode($product_backlog)?>&sprintName=<?php echo urlencode($sprintName)?>&fromPage=<?php echo urlencode($fromPage)?>&direction=<?php echo $direction?>">ID</a>
		</td>
		<td class="category">
			<a href="<?php echo plugin_page("assume_userstories.php")?>&sort_by=summary&product_backlog=<?php echo urlencode($product_backlog)?>&sprintName=<?php echo urlencode($sprintName)?>&fromPage=<?php echo urlencode($fromPage)?>&direction=<?php echo $direction?>"><?php echo plugin_lang_get( 'assume_userstories_summary' )?></a>
		</td>
	</tr>
	<?php
	
	# change background color according to user story status
	if(!empty($undone)){
		foreach($undone AS $num => $row){
			switch ($row['status']){
				case '40':
					$bgcolor = '#FFF494';
				break;
				case '50':
					$bgcolor = '#C2DFFF';
				break;
				case '80':
					$bgcolor = '#D2F5B0';
				break;
				case '90':
					$bgcolor = '#B7C4A1';
				break;
			}
		?>
		<tr style="background-color:<?php echo $bgcolor?>;">
			<?php if(plugin_config_get('gadiv_ranking_order')=='1'){?>
			<td>
				<?php echo $row['rankingOrder']?>
			</td>
			<?php }?>
			<td>
				<?php echo $row['businessValue']?>
			</td>
			<?php if(plugin_config_get('gadiv_tracker_planned_costs')=='1'){?>
			<td>
				<?php echo $row['plannedWork']?>
			</td>
			<?php }?>
			<td>
				<?php echo $row['storyPoints']?>
			</td>
			<td>
				<?php echo $row['projectname']?> <?php echo $row['version']?>
			</td>
			<td>
				<input type="checkbox" name="assume[]" id="bug_id_<?php echo $row['id']?>" value="<?php echo $row['id']?>" onclick="setCookie(<?php echo $row['id']?>,getCookie())">
				<input type="hidden" name="storypoints[<?php echo $row['id']?>]" id="storypoints_<?php echo $row['id']?>" value="<?php echo $row['storyPoints']?>">
			</td>
			<td>
				<?php echo $row['id']?>
			</td>
			<td>
				<?php echo $row['summary']?>
			</td>
		</tr>
	<?php 
		}
	}
	$additional_fields = 7;
	$additional_fields += plugin_config_get('gadiv_tracker_planned_costs');
	$additional_fields += plugin_config_get('gadiv_ranking_order');
	?>
	<tr>
		<?php if(plugin_config_get('gadiv_ranking_order')=='1'){?>
		<td style="background-color:#B1DDFF"></td>
		<?php }?>
		<td style="background-color:#B1DDFF"></td>
		<td style="background-color:#B1DDFF;"></td>
		<?php if(plugin_config_get('gadiv_tracker_planned_costs')=='1'){?>
		<td style="background-color:#B1DDFF"></td>
		<?php }?>
		<td style="background-color:#B1DDFF;font-weight:bold;"><span id="calculated_storypoints">0</span></td>
		<td style="background-color:#B1DDFF"></td>
		<td style="background-color:#B1DDFF"></td>
		<td style="background-color:#B1DDFF"></td>
	</tr>
	<tr>
		<td colspan="<?php echo $additional_fields?>" class="center">
			<input type="submit" name="assume_userstories" value="<?php echo plugin_lang_get( 'assume_userstories_assume_to_sprint' )?>" onclick="deleteCookie();">
			</form>
			<form action="<?php echo plugin_page($fromPage)?>&sprintName=<?php echo urlencode($sprintName);?>" method="post">
				<input type="submit" name="back_button" value="<?php echo plugin_lang_get( 'button_back' )?>">
			</form>
		</td>
	</tr>
</table>
<?php include(PLUGIN_URI.'pages/agileMantisActions.js.php');?>
<?php html_page_bottom() ?>