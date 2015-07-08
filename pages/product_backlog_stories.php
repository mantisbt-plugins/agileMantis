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



	# get all user stories from a selected product backlog
	$userstories = $agilemantis_pb->getUserStoriesByProductBacklogName( $product_backlog );
	if( config_get( 'current_user_product_backlog_filter_direction',
						null,auth_get_current_user_id()) == 'ASC' ) {
		
		$direction = 'DESC';
	} else {
		$direction = 'ASC';
	}

	# calculate amount of table columns
	$columns = 4;
	$columns += plugin_config_get('gadiv_ranking_order');
	$columns += config_get('show_project_target_version');
?>
<br>
<?php echo $system?>
<div class="table-container">
<table align="center" class="width100" cellspacing="1">
	<tr>
		<td colspan="4">
			<b>User Stories</b>
			<input type="button" name="submit" 
				value="<?php echo plugin_lang_get( 'button_save' )?>" 
				onclick="document.getElementById('fileform').submit();">
		</td>
		<td colspan="<?php echo $columns?>">
			<form action="" method="post" name="filterform" style="float:right;">
				<input type="hidden" 
					name="action" 
					value="save_product_backlog_filter">
				<input type="hidden" 
					name="productBacklogName" 
					value="<?php echo $product_backlog?>">
				<input type="checkbox" 
					name="show_only_us_without_storypoints" 
					<?php if( config_get('show_only_us_without_storypoints',
							0,auth_get_current_user_id()) == 1 ) { ?>checked<?php 
						}?> 
					value="1" 
					onClick="this.form.submit();"> <?php 
						echo plugin_lang_get( 'product_backlog_without_sp' )?>&nbsp;
				<input type="checkbox" 
					name="show_resolved_userstories" 
					value="1" 
					<?php if( config_get('show_resolved_userstories',
							null,auth_get_current_user_id()) == 1 ) { ?>checked<?php }?> 
					onClick="this.form.submit();"> <?php 
						echo plugin_lang_get( 'product_backlog_show_resolved' )?>&nbsp;
				<input type="checkbox" 
					name="show_closed_userstories" 
					value="1" 
					<?php if(config_get('show_closed_userstories',
							null,auth_get_current_user_id() ) == 1 ) {?>checked<?php }?> 
					onClick="this.form.submit();"> <?php 
						echo plugin_lang_get( 'product_backlog_show_closed' )?>&nbsp;
				<input type="checkbox" 
					name="show_only_userstories_without_sprint" 
					<?php if(config_get('show_only_userstories_without_sprint',
							null,auth_get_current_user_id()) == 1){?>checked<?php }?> 
					value="1" 
					onClick="this.form.submit();"> <?php 
						echo plugin_lang_get( 'product_backlog_without_sprint' )?>&nbsp;
				<input type="checkbox" 
					name="show_only_project_userstories" 
					value="1" 
					<?php if( config_get( 'show_only_project_userstories',
							null, auth_get_current_user_id() ) == 1 ) { ?>checked<?php }?> 
					onClick="this.form.submit();"> <?php 
						echo plugin_lang_get( 'product_backlog_current_project' )?>&nbsp;
				<input type="checkbox" 
					name="show_project_target_version" 
					value="1" 
					<?php if( config_get('show_project_target_version',
							null, auth_get_current_user_id() ) == 1 ) { ?>checked<?php }?> 
							onClick="this.form.submit();"> <?php 
						echo plugin_lang_get( 'product_backlog_show_project_version' )?>&nbsp;
			</form>
		</td>
	</tr>
	<tr>
		<form action="<?php echo plugin_page("product_backlog.php")?>" id="fileform" method="post">
		<input type="hidden" name="action" value="save_values">
		<input type="hidden" name="productBacklogName" value="<?php echo $product_backlog?>">
		<?php if(plugin_config_get('gadiv_ranking_order')=='1'){?>
		<td class="category" width="60">
		<a href="<?php echo plugin_page("product_backlog.php")?>&productBacklogName=<?php echo $product_backlog?>&sort_by=rankingOrder&direction=<?php echo $direction?>"><?php echo plugin_lang_get( 'product_backlog_rankingorder' )?></a></td>
		<?php }?>
		<td class="category" width="60"><a href="<?php 
			echo plugin_page("product_backlog.php")?>&productBacklogName=<?php 
			echo $product_backlog?>&sort_by=businessValue&direction=<?php 
			echo $direction?>">Business Value</a></td>
		<td class="category" width="50"><a href="<?php 
			echo plugin_page("product_backlog.php")?>&productBacklogName=<?php 
			echo $product_backlog?>&sort_by=storyPoints&direction=<?php 
			echo $direction?>">Story Points</a></td>
		<td class="category" width="20"></td>
		<td class="category" width="50"><a href="<?php
			echo plugin_page("product_backlog.php")?>&productBacklogName=<?php 
			echo $product_backlog?>&sort_by=id&direction=<?php 
			echo $direction?>">ID</a></td>
		<td class="category"><a href="<?php 
			echo plugin_page("product_backlog.php")?>&productBacklogName=<?php 
			echo $product_backlog?>&sort_by=category&direction=<?php 
			echo $direction?>"><?php 
			echo plugin_lang_get( 'product_backlog_category' )?></a></td>
		<?php if(config_get('show_project_target_version') == 1){?>
		<td class="category"><a href="<?php 
			echo plugin_page("product_backlog.php")?>&productBacklogName=<?php 
			echo $product_backlog?>&sort_by=version&direction=<?php 
			echo $direction?>"><?php 
			echo plugin_lang_get( 'product_backlog_target_version' )?></a></td>
		<?php }?>
		<td class="category" width="20"></td>
		<td class="category"><a href="<?php 
			echo plugin_page("product_backlog.php")?>&productBacklogName=<?php 
			echo $product_backlog?>&sort_by=summary&direction=<?php 
			echo $direction?>"><?php 
			echo plugin_lang_get( 'product_backlog_summary' )?></a></td>
		<td class="category"><a href="<?php 
			echo plugin_page("product_backlog.php")?>&productBacklogName=<?php 
			echo $product_backlog?>&sort_by=sprint&direction=<?php 
			echo $direction?>">Sprint</a></td>
	</tr>
	<?php
	if(!empty($userstories)){
		foreach($userstories AS $num => $row){
			$t_buglist .= $row['id'].',';
			# set background color for each user story row
			switch ($row['status']){
				case '10':
					$background_color = '#FCBDBD';
				break;
				case '20':
					$background_color = '#E3B7EB';
				break;
				case '30':
					$background_color = '#FFCD85';
				break;
				case '40':
					$background_color = '#FFF494';
				break;
				case '50':
					$background_color = '#C2DFFF';
					break;
				case '80':
					$background_color = '#D2F5B0';
				break;
				case '90':
					$background_color = '#c9ccc4';
				break;
			}
			$storypoints_gesamt += $row['storyPoints'];
		?>
		<tr style="background-color:<?php echo $background_color?>;">
			<?php if(plugin_config_get('gadiv_ranking_order')=='1'){?>
			<td>
				<input type="text" name="rankingOrder[<?php echo $row['id']?>]" value="<?php echo $row['rankingOrder']?>" <?php if($row['status'] >= 80){?>readonly<?php }?> style="width:50px;">
				<input type="hidden" name="rankingOrderOld[<?php echo $row['id']?>]" value="<?php echo $row['rankingOrder']?>"/>
			</td>
			<?php }?>
			<td>
				<input type="text" name="businessValue[<?php echo $row['id']?>]" value="<?php echo $row['businessValue']?>" <?php if($row['status'] >= 80){?>readonly<?php }?> style="width:50px;">
				<input type="hidden" name="businessValueOld[<?php echo $row['id']?>]" value="<?php echo $row['businessValue']?>"/>
			</td>
			<td><?php echo $row['storyPoints']?></td>
			<td width="20">
				<?php if(!bug_is_readonly( $row['id'] )){?><a href="bug_update_page.php?bug_id=<?php echo $row['id']?>" height="16" width="16"><img src="images/update.png" alt="Detailinformation zur User Story bearbeiten" height="16" width="16"></a><?php }?>
			</td>
			<td><a href="view.php?id=<?php echo $row['id']?>"><?php echo $row['id']?></a></td>
			<td><?php echo $row['category_name']?></td>
			<?php
			if(config_get('show_project_target_version',null,auth_get_current_user_id()) == 1){
				# get user story version information
				$version_info = $agilemantis_version->getVersionInformation($row['project_id'],$row['target_version']);

				# include version dialogue
				include(AGILEMANTIS_PLUGIN_URI.'pages/product_backlog_version.php');
			}
			?>
			<td width="20">
				<?php
					$functionName = 'loadUserstoryNoExpert';
					if(plugin_is_loaded('agileMantisExpert')) {
						$functionName = 'loadUserstoryExpert';
					}
					
					if( plugin_config_get( 'gadiv_taskboard' ) == 0 ) {
						$page_backlog = plugin_page( "sprint_backlog.php" );
					} else {
						$page_backlog = plugin_page( "taskboard.php" );
					}
				?>
				<img src="<?php echo AGILEMANTIS_PLUGIN_URL?>images/info-icon.png" alt="<?php 
						echo plugin_lang_get( 'product_backlog_show_info' );?>" 
						onclick="<?php echo $functionName?>(<?php echo $row['id']?>, '<?php 
						echo AGILEMANTIS_PLUGIN_URL?>');" height="16" width="16">
			</td>
			<td><?php echo string_display_line_links($row['summary'])?></td>
			<td><a href="<?php echo $page_backlog?>&sprintName=<?php echo urlencode($row['sprint'])?>"><?php echo string_display($row['sprint']);?></a></td>
		</tr>
		<?php
			# add bug list cookie
			gpc_set_cookie( config_get( 'bug_list_cookie' ), substr($t_buglist,0,-1) );
		?>
	<?php	}
	}
	?>
	<tr>
		</form>
		<?php if(!empty($t_buglist)){?>
			<?php if(plugin_config_get('gadiv_ranking_order')=='1'){?>
			<td style="background-color:#B1DDFF"></td>
			<?php }?>
			<td style="background-color:#B1DDFF"></td>
			<td style="background-color:#B1DDFF; font-weight:bold;"><?php echo $storypoints_gesamt?></td>
			<td style="background-color:#B1DDFF"></td>
			<td style="background-color:#B1DDFF"></td>
			<td style="background-color:#B1DDFF"></td>
			<td style="background-color:#B1DDFF"></td>
			<td style="background-color:#B1DDFF"></td>
			<td style="background-color:#B1DDFF"></td>
			<?php if(config_get('show_project_target_version',null,auth_get_current_user_id()) == 1){?>
			<td style="background-color:#B1DDFF"></td>
			<?php }?>
		<?php }?>
	</tr>
</table></div>
<br>
<center><input type="button" name="submit" value="<?php echo plugin_lang_get( 'button_save' )?>" onclick="document.getElementById('fileform').submit();"></center>
