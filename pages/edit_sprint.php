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


	html_page_top(plugin_lang_get( 'edit_sprint_title' )); 
?>
<br>
<?php include(AGILEMANTIS_PLUGIN_URI.'/pages/footer_menu.php');?>
<br>
<?php

// Redirect to backlog list view if invalid or back_button

if( empty($_POST) || $_POST['back_button'] ) {
	header( $agilemantis_sprint->forwardReturnToPage( "sprints.php" ) );
}

# set current date
$current_date = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );

# collecting sprint information 
$agilemantis_sprint->pb_id = ( int ) $_POST['product_backlog_id'];
$agilemantis_sprint->sprint_id = $_POST['id'];
$agilemantis_sprint->name = $_POST['name'];
$agilemantis_sprint->description = $_POST['description'];
$agilemantis_sprint->team_id = $_POST['team_id'];
$agilemantis_sprint->start = str_replace( ',', '.', $_POST['start_date'] );
$agilemantis_sprint->end = str_replace( ',', '.', $_POST['end_date'] );
$agilemantis_sprint->daily_scrum = ($_POST['daily_scrum'] ? 1 : 0);

# only change description when sprint is closed
if( $_POST['change_description'] && $_POST['status'] == 2 ) {
	$agilemantis_sprint->editSprint();
	header( $agilemantis_sprint->forwardReturnToPage( "sprints.php" ) );
}

if( $_POST['action'] == 'edit' && $_POST['save_sprint'] ) {
	# check sprint name 
	if( empty( $agilemantis_sprint->name ) ) {
		$system = plugin_lang_get( 'edit_sprints_error_922500' );
	}
	
	# check if sprint name is unique
	if( !$agilemantis_sprint->sprintnameisunique() && $system == "" ) {
		$system = plugin_lang_get( 'edit_sprints_error_982500' );
	}
	
	# check start date 
	if( empty( $agilemantis_sprint->start ) && $system == "" ) {
		$system = plugin_lang_get( 'edit_sprints_error_923501' );
	}
	
	# check if reformatted date is numeric
	if( !is_numeric( str_replace( '.', '', $agilemantis_sprint->start ) ) && $system == "" ) {
		$system = plugin_lang_get( 'edit_sprints_error_985502' );
	}
	
	# check if start date is between 6 and 10 digits long
	if( (strlen( $agilemantis_sprint->start ) < 6 
			|| strlen( $agilemantis_sprint->start ) > 10) && $system == "" ) {
		$system = plugin_lang_get( 'edit_sprints_error_985503' );
	}
	
	# make date checks
	if( $system == "" ) {
		$system = plugin_lang_get( 'edit_sprints_error_985500' );
		$format = 'dd.mm.yyyy';
		
		$separator_only = str_replace( array( 'm','d','y' ), '', $format );
		$separator = $separator_only[0];
		
		if( $separator && strlen( $separator_only ) == 2 ) {
			
			$regexp = str_replace( 'mm', '(0?[1-9]|1[0-2])', $format );
			$regexp = str_replace( 'dd', '(0?[1-9]|[1-2][0-9]|3[0-1])', $regexp );
			$regexp = str_replace( 'yyyy', '(19|20)?[0-9][0-9]', $regexp );
			$regexp = str_replace( $separator, "\\" . $separator, $regexp );
			
			if( $regexp != $agilemantis_sprint->start && preg_match( '/' . $regexp . '\z/', $agilemantis_sprint->start ) ) {
				
				$date = explode( $separator, $agilemantis_sprint->start );
				$day = $date[0];
				$month = $date[1];
				$year = $date[2];
				
				if( strlen( $year ) == 2 ) {
					$year = '20' . $year;
					$agilemantis_sprint->start = $day . '.' . $month . '.' . $year;
				}
				
				if( @checkdate( $month, $day, $year ) ) {
					$system = "";
				}
			}
		}
	}
	
	$t_start = strtotime( $agilemantis_sprint->start );
	$t_old_start = strtotime( $_POST['old_start_date'] );
	
	if( $agilemantis_sprint->sprint_id > 0 && $system == "" ) {
		if( $t_start != $t_old_start && $t_start < $current_date && $system == "" ) {
			$system = plugin_lang_get( 'edit_sprints_error_980503' );
			$t_start = $t_old_start;
		}
	} elseif( $system == "" ) {
		if( $t_start < $current_date && $system == "" ) {
			$system = plugin_lang_get( 'edit_sprints_error_980503' );
			unset( $_POST['start_date'] );
		}
	}
	
	# check end date 
	if( empty( $agilemantis_sprint->end ) && $system == "" ) {
		$system = plugin_lang_get( 'edit_sprints_error_923500' );
	}
	
	# check if reformatted date is numeric
	if( !is_numeric( str_replace( '.', '', $agilemantis_sprint->end ) ) && $system == "" ) {
		$system = plugin_lang_get( 'edit_sprints_error_985504' );
	}
	
	# check if start date is between 6 and 10 digits long
	if( (strlen( $agilemantis_sprint->end ) < 6 
		|| strlen( $agilemantis_sprint->end ) > 10) && $system == "" ) {
		$system = plugin_lang_get( 'edit_sprints_error_985505' );
	}
	
	if( $system == "" ) {
		$system = plugin_lang_get( 'edit_sprints_error_985501' );
		$format = 'dd.mm.yyyy';
		
		$separator_only = str_replace( array( 'm','d','y' ), '', $format );
		$separator = $separator_only[0];
		
		if( $separator && strlen( $separator_only ) == 2 ) {
			
			$regexp = str_replace( 'mm', '(0?[1-9]|1[0-2])', $format );
			$regexp = str_replace( 'dd', '(0?[1-9]|[1-2][0-9]|3[0-1])', $regexp );
			$regexp = str_replace( 'yyyy', '(19|20)?[0-9][0-9]', $regexp );
			$regexp = str_replace( $separator, "\\" . $separator, $regexp );
			
			if( $regexp != $agilemantis_sprint->end 
				&& preg_match( '/' . $regexp . '\z/', $agilemantis_sprint->end ) ) {
				
				$date = explode( $separator, $agilemantis_sprint->end );
				$day = $date[0];
				$month = $date[1];
				$year = $date[2];
				
				if( strlen( $year ) == 2 ) {
					$year = '20' . $year;
					$agilemantis_sprint->end = $day . '.' . $month . '.' . $year;
				}
				
				if( @checkdate( $month, $day, $year ) ) {
					$system = "";
				}
			}
		}
	}
	
	$t_end = strtotime( $agilemantis_sprint->end );
	$t_old_end = strtotime( $_POST['old_end_date'] );
	
	if( $agilemantis_sprint->sprint_id > 0 && $system == "" ) {
		if( $t_end != $t_old_end && $t_end < $current_date && $system == "" ) {
			$system = plugin_lang_get( 'edit_sprints_error_980501' );
			$t_end = $t_old_end;
		}
	} elseif( $system == "" ) {
		if( $t_end < $current_date && $system == "" ) {
			$system = plugin_lang_get( 'edit_sprints_error_980501' );
			unset( $_POST['end_date'] );
		}
	}
	
	# check both dates
	if( $t_start > $t_end && $system == "" ) {
		$system = plugin_lang_get( 'edit_sprints_error_980502' );
	}
	
	# check team 
	if( $agilemantis_sprint->team_id == 0 && $system == "" ) {
		$system = plugin_lang_get( 'edit_sprints_error_923502' );
	}
	
	$agilemantis_sprint->start = date( 'Y-m-d', $t_start );
	$agilemantis_sprint->end = date( 'Y-m-d', $t_end );
	
	# check if this sprints overlaps with another sprint of the team
	if( $agilemantis_sprint->isSprintOverlapping() && $system == "" ) {
		$system = plugin_lang_get( 'edit_sprints_error_980500' );
	}
	
	if( $system == "" && $_POST['negative'] != 'no_save' ) {

		$t_old_sprint = $agilemantis_sprint->getSprintByName();

		$agilemantis_sprint->editSprint();
		

		if ( !empty($t_old_sprint) ) {
			$agilemantis_sprint->updateSprintCustomFieldStrings(
						$t_old_sprint['name'], $agilemantis_sprint->name );
		}		
		
		header( $agilemantis_sprint->forwardReturnToPage( "sprints.php" ) );
	}
}
?>
<?php if($system){?>
<center>
	<span class="message_error"><?php echo $system?></span>
</center>
<br>
<?php }
	
	# get sprint id
if( $_GET['sprint_id'] > 0 || $_POST['id'] > 0 ) {
	if( $_GET['sprint_id'] ) {
		$agilemantis_sprint->sprint_id = $_GET['sprint_id'];
	} else {
		$agilemantis_sprint->sprint_id = $_POST['id'];
	}
}
if( $_POST['edit'] ) {
	$agilemantis_sprint->sprint_id = implode( '', array_flip( $_POST['edit'] ) );
}

if( $agilemantis_sprint->sprint_id > 0 ) {
	$s = $agilemantis_sprint->getSprintByName();
	
	# mark input fields as read only or disabled when sprint is running / closed
	if( $s['status'] == 1 || $s['status'] == 2 ) {
		$disabled = 'style="background-color: #EBEBE4;" readonly';
		$disables = "disabled";
	} else if( $s['team_id'] > 0 ) {
		$disables = "disabled";
	}
}

if( !empty( $s['start'] ) ) {
	$s['start'] = strtotime( $s['start'] );
}
if( !empty( $s['end'] ) ) {
	$s['end'] = strtotime( $s['end'] );
}

if( !$s['start'] ) {
	$s['start'] = time();
}
if( !$s['end'] ) {
	$s['end'] = (time() + (86400 * plugin_config_get( 'gadiv_sprint_length' )));
}
?>
<form action="<?php echo plugin_page('edit_sprint.php') ?>"
	method="post" id="sprint_edit_form">
	<input type="hidden" name="action" value="edit"> <input type="hidden"
		name="id" value="<?php echo $s['id']?>"> <input type="hidden"
		name="status" value="<?php echo $s['status']?>"> <input type="hidden"
		name="fromProductBacklog"
		value="<?php echo  (int) $_POST['fromProductBacklog']?>"> <input
		type="hidden" name="productBacklogName"
		value="<?php echo $_POST['productBacklogName']?>"> <input
		type="hidden" name="fromSprintBacklog"
		value="<?php echo (int) $_POST['fromSprintBacklog']?>"> <input
		type="hidden" name="fromTaskboard"
		value="<?php echo (int) $_POST['fromTaskboard']?>"> <input
		type="hidden" name="fromDailyScrum"
		value="<?php echo (int) $_POST['fromDailyScrum']?>"> <input
		type="hidden" name="fromStatistics"
		value="<?php echo (int) $_POST['fromStatistics']?>"> <input
		type="hidden" name="sprintName"
		value="<?php echo $_POST['sprintName']?>">
<?php $agilemantis_sprint->sprint_id = $s['id'];?>
<div class="table-container">
		<table align="center" class="width75" cellspacing="1">
			<tr>
				<td class="form-title" colspan="3">
		<?php echo plugin_lang_get( 'edit_sprint_title' )?>
    </td>
			</tr>

			<tr <?php echo helper_alternate_class() ?>>
				<td class="category" width="30%">*Name</td>
				<td class="left" width="70%"><input type="text" size="105"
					maxlength="128" name="name"
					value="<?php 
						if( $s['id'] ) { 
							echo $s['name']; 
						} else { 
							echo $_POST['name'];
						}?>"
					<?php echo $disabled?>></td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category">
		<?php echo plugin_lang_get( 'edit_sprint_goals' )?>
	</td>
				<td class="left"><textarea cols="80" rows="10" name="description"><?php 
						if( $s['id'] ) { 
							echo $s['description'];
						} else { 
							echo $_POST['description'];
						}
				?></textarea></td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category">*<?php echo plugin_lang_get( 'edit_sprints_begin' )?> <?php 
							echo plugin_lang_get( 'edit_sprints_date_format' )?>
        </td>
				<td class="left"><input type="text" size="105" maxlength="128"
					name="start_date"
					value="<?php 
						if( $s['id'] ) { 
							echo date( 'd.m.Y', $s['start'] ); 
						} elseif ( $_POST['start_date'] ) { 
							echo $_POST['start_date'];
						} else { 
							echo date('d.m.Y', time() ) ; 
						}?>"
					<?php echo $disabled?>>
			<?php if( $s['id'] > 0 ) { ?>
				<input type="hidden" name="old_start_date"
					value="<?php echo date('d.m.Y',$s['start'])?>">
			<?php }?>
		</td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category">
            *<?php echo plugin_lang_get( 'edit_sprints_end' )?> <?php 
            		echo plugin_lang_get( 'edit_sprints_date_format' )?>
        </td>
				<td class="left"><input type="text" size="105" maxlength="128"
					name="end_date"
					value="<?php 
						if( $s['id'] ) {
							echo date( 'd.m.Y',$s['end'] );
						} elseif( $_POST['end_date'] ) {
							echo $_POST['end_date'];
						} else {
							echo date( 'd.m.Y', ( time() 
								+ ( plugin_config_get( 'gadiv_sprint_length' ) -1 ) * 86400 ) ); 
						} ?>"
					<?php if( $s['status'] == 2 ) {
						?> <?php echo $disabled?>
					<?php }?>>
			<?php if( $s['id'] > 0 ) { ?>
				<input type="hidden" name="old_end_date"
					value="<?php echo date('d.m.Y',$s['end'])?>">
			<?php }?>
		</td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category"><input type="hidden" name="status"
					value="<?php echo $s['status']?>">
            <?php echo plugin_lang_get( 'edit_sprints_status' )?>
        </td>
				<td class="left">
			<?php if( $s['status'] == 0 ) {?>
				<?php echo plugin_lang_get( 'status_open' )?>
			<?php }?>
			<?php if( $s['status'] == 1 ) {?>
				<?php echo plugin_lang_get( 'status_running' )?>
			<?php }?>
			<?php if( $s['status'] == 2 ) {?>
				<?php echo plugin_lang_get( 'status_closed' )?>
			<?php }?>
        </td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category">*Team</td>
				<td class="left"><select name="team_id" <?php echo $disables?>
					<?php if( $_POST['fromProductBacklog'] ) { ?> disabled <?php }?>
					onChange="this.form.submit();">
						<option><?php echo plugin_lang_get( 'common_chose' )?></option>
			<?php
			if( $s['id'] && $_POST['team_id'] == 0 ) {
				$team_id = $s['team_id'];
			} else {
				$team_id = $_POST['team_id'];
			}
			
			$teamdata = $agilemantis_team->getCompleteTeams();
			foreach( $teamdata as $num => $row ) {
				$teamSelected = '';
				if( $row['id'] == $team_id ) {
					$teamSelected = 'selected';
					$selectedProductBacklog = $row['product_backlog'];
					$selectedTeam = $row['id'];
					
					$agilemantis_team->id = $selectedProductBacklog;
					$productBacklog = $agilemantis_team->getSelectedProductBacklog();
					$agilemantis_team->id = $selectedTeam;
					$t = $agilemantis_team->getSelectedTeam();
				}
				
				?><option value="<?php echo $row['id']?>"
							<?php echo $teamSelected?>><?php echo $row['name']?></option><?php
			}
			?>
		</select>
			
			</tr>
	<?php if(plugin_config_get('gadiv_daily_scrum') == 1){?>
	<tr <?php echo helper_alternate_class() ?>>
				<td class="category">Daily Scrum Meeting mit Taskboard</td>
				<td class="left"><input type="checkbox" name="daily_scrum"
					<?php if( plugin_config_get('gadiv_daily_scrum') == 0 || $s['status'] == 2 ) {?>
					disabled <?php }?>
					<?php if( $s['daily_scrum'] == 1 
							|| ( $s['id'] == 0 && $t[0]['daily_scrum'] == 1 ) ) {?>
					checked <?php }?> value="1"></td>
			</tr>
	<?php } ?>
	<?php if( $disables == 'disabled' || $_POST['fromProductBacklog'] ) { ?>
		<input type="hidden" name="team_id" value="<?php echo $team_id?>">
	<?php }?>
	<?php if( !$productBacklog[0]['name'] ) { ?>
		<input type="hidden" name="negative" value="no_save">
	<?php }?>
	<?php if( $agilemantis_team->id > 0 ) {?>
	<tr <?php echo helper_alternate_class() ?>>
				<td class="category">Product Backlog</td>
				<td class="left">
			<?php if( !$productBacklog[0]['name'] ) { ?>
				<input type="hidden" name="negative" value="no_save">
			<?php }?>
			<?php echo string_display_line_links($productBacklog[0]['name'])?>
			<input type="hidden" name="product_backlog_id"
					value="<?php echo $productBacklog[0]['id']?>">
				</td>
			</tr>
	<?php }?>
	<tr>
				<td><span class="required"> * <?php echo lang_get( 'required' ) ?></span>
				</td>
				<td class="center">
			<?php if( $s['status'] == 2 ) { ?>
				<input type="submit" name="change_description" class="button"
					value="<?php echo plugin_lang_get( 'edit_sprints_change_description' )?>">
			<?php } else {?>
				<input type="submit" name="save_sprint" class="button"
					value="<?php echo plugin_lang_get( 'button_save' )?>">
			<?php }?>
			<input type="submit" name="back_button"
					value="<?php echo plugin_lang_get( 'button_back' )?>">
				</td>
			</tr>
		</table>
	</div>
</form>
<div style="clear: both"></div>
<?php html_page_bottom() ?>