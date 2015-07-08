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




# modify table if ranking order custom field is enabled
if( plugin_config_get( 'gadiv_ranking_order' ) == '1' ) {
	$colspan = 'colspan="2"';
} else {
	$colspan = 'colspan="4"';
}

# modify table if ranking order custom field is enabled and is active in the chosen project
if( plugin_config_get( 'gadiv_ranking_order' ) == '1' &&
	 $agilemantis_sprint->customFieldIsInProject( "RankingOrder" ) == true ) {
	$colspan = 'colspan="2"';
} else {
	$colspan = 'colspan="5"';
}

if( $story['unit'] != '' ) {
	$story['unit'] = '(' . $story['unit'] . ')';
}
	
# add all active agilemantis custom fields within view issues page
echo '<form action="" method="post">';
echo '
	<tr '.helper_alternate_class().'>

			<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">Business Value</td>
			<td>
				<input type="text" name="businessValue" value="'.$story['businessValue'].'">
			</td>
	';
if( plugin_config_get('gadiv_ranking_order') == '1' 
		&& $agilemantis_sprint->customFieldIsInProject( "RankingOrder" ) == true ) {
	echo '
		<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">'.
					plugin_lang_get( 'RankingOrder' ).'</td>
		<td>
			<input type="text" style="" name="rankingorder" value="'.$story['rankingorder'].'">
		</td>
	';
}

echo '
	<td '.$colspan.'></td>
	<tr>
';

if( plugin_config_get( 'gadiv_tracker_planned_costs' ) == '1' &&
	 $agilemantis_sprint->customFieldIsInProject( "PlannedWork" ) == true ) {
	$colspan = 'colspan="2"';
} else {
	$colspan = 'colspan="4"';
}
$agilemantis_sprint->sprint_id = $story['sprint'];
$getSprint = $agilemantis_sprint->getSprintById();
if( $getSprint['status'] > 0 ) {
	$disable_storypoints = 'disabled';
	echo '<input type="hidden" name="storypoints" value="' . $story['storypoints'] . '">';
} else {
	$disable_storypoints = '';
}
echo '
	<tr '.helper_alternate_class().'>
		<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">Story Points</td>
		<td>';
			if( plugin_config_get( 'gadiv_storypoint_mode' ) == 1 ) {
				echo '<input type="text" name="storypoints" value="'.
						$story['storypoints'].'" '.$readonly.' '.$disable_storypoints.'>';
			} else {
				echo '<select name="storypoints" '.$disable_storypoints.'>';
				echo '<option value=""></option>';
					$agilemantis_pb->getFibonacciNumbers( $story['storypoints'] );
				'</select>';
			}
		echo '</td>
';
if( plugin_config_get( 'gadiv_tracker_planned_costs' ) == '1' 
		&& $agilemantis_sprint->customFieldIsInProject( "PlannedWork" ) == true ) {
	echo '
		<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">'.
				plugin_lang_get( 'PlannedWork' ).' '.$story['unit'].'</td>
		<td>
			<input type="text" name="plannedWork" value="'.$story['plannedWork'].'">
		</td>
	';
}

echo '		<td '.$colspan.'>
			</td>
	<tr>
';

if( $story['sprint'] != "" ) {
		$pbro = 'disabled';?>
<input type="hidden" name="backlog" value="<?php echo $story['name']?>">
<?php
	}
echo '
	<tr '.helper_alternate_class().'>
		<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">Product Backlog</td>
		<td>
			<input type="hidden" name="action" value="editProductBacklog">
			<input type="hidden" name="old_product_backlog" value="'.$story['name'].'">
			<select name="backlog" '.$pbro.'>';?>
<option value=""><?php echo plugin_lang_get( 'custom_chose_product_backlog' )?></option>
<?php foreach( $pbl AS $num => $row ) {?>
<option value="<?php echo $row['name']?>"
	<?php 
	if( $row['name'] == $story['name'] ) { 
		echo 'selected';
	}?>><?php 
	echo string_display($row['name'])?></option>
<?php }?>
				<?php echo '
			</select>
		</td>
	';
	if( empty( $story['name'] ) ) {
		$sprintDis = 'disabled';
	}
	
	if( $story['name'] != "" ) {
		$sprintDis = '';
	}
	
	$agilemantis_sprint->sprint_id = $story['sprint'];
	$getSprint = $agilemantis_sprint->getSprintById();
	if( !empty( $story['name'] ) && $getSprint['status'] > 0 ) {
		$sprintDis = 'disabled';
		echo '<input type="hidden" name="sprint" value="' . $story['sprint'] . '">';
	}
	
	$selected = false;

	echo '

		<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">Sprint</td>
		<td colspan="3">
			<select name="sprint" '.$sprintDis.'>';?>
<option value=""><?php echo plugin_lang_get( 'custom_chose_sprint' )?></option>
<?php foreach( $s as $num => $row ) {
				  if( $row['status'] != 2 && $row['sname'] != "" ) {
				?>
<option value="<?php echo $row['sname']?>"
	<?php if( $row['sname'] == $story['sprint'] ) { 
			echo 'selected';
			$selected = true;
		}?>><?php
		echo string_display( $row['sname'] )?></option>
<?php }}?>
	<?php if($agilemantis_sprint->getUserStoryStatus( $p_project_id ) >= 80 
				&& !empty( $story['sprint'] ) && $selected == false ) {?>
<option value="<?php echo $story['sprint']?>" selected><?php echo $story['sprint']?></option>
<?php }?>
			<?php echo ' </select>
		</td>
	</tr>
	';
	echo '<tr '.helper_alternate_class().'>';
			if( plugin_config_get( 'gadiv_presentable' ) == '1' &&
				 $agilemantis_sprint->customFieldIsInProject( "Presentable" ) == true ) {
				if( $story['presentable'] == 3 ) {
					$marked_3 = 'selected';
				}
				if( $story['presentable'] == 1 ) {
					$marked_1 = 'selected';
				}
				if( $story['presentable'] == 2 ) {
					$marked_2 = 'selected';
				}
				if( plugin_config_get( 'gadiv_release_documentation' ) == '1' &&
					 $agilemantis_sprint->customFieldIsInProject( "inReleaseDocu" ) == true &&
					 plugin_config_get( 'gadiv_technical' ) == '1' &&
					 $agilemantis_sprint->customFieldIsInProject( "Technical" ) == true ) {
					$colspan = 0;
				} elseif( plugin_config_get( 'gadiv_release_documentation' ) == '1' &&
					 $agilemantis_sprint->customFieldIsInProject( "inReleaseDocu" ) == true ) {
					$colspan = 1;
				} elseif( plugin_config_get( 'gadiv_technical' ) == '1' &&
					 $agilemantis_sprint->customFieldIsInProject( "Technical" ) == true ) {
					$colspan = 1;
				} else {
					$colspan = 5;
				}
				echo '
			<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">' .
					 plugin_lang_get( 'Presentable' ) . '</td>
			<td colspan="' . $colspan . '">
				<select name="presentable">
					<option value="3" ' .
					 $marked_3 . '>' . plugin_lang_get( 'view_issue_non_presentable' ) .
					 '</option>
					<option value="1" ' .
					 $marked_1 . '>' . plugin_lang_get( 'view_issue_technical_presentable' ) .
					 '</option>
					<option value="2" ' .
					 $marked_2 . '>' . plugin_lang_get( 'view_issue_functional_presentable' ) . 
					 '</option>
				</select>
			</td>
		';
			}
			if( plugin_config_get( 'gadiv_technical' ) == '1' &&
				 $agilemantis_sprint->customFieldIsInProject( "Technical" ) == true ) {
				if( plugin_config_get( 'gadiv_release_documentation' ) == '1' &&
				 $agilemantis_sprint->customFieldIsInProject( "inReleaseDocu" ) == true &&
				 plugin_config_get( 'gadiv_presentable' ) == '1' &&
				 $agilemantis_sprint->customFieldIsInProject( "Presentable" ) == true ) {
				$colspan = 0;
			} elseif( plugin_config_get( 'gadiv_presentable' ) == '1' &&
				 $agilemantis_sprint->customFieldIsInProject( "Presentable" ) == true ) {
				$colspan = 3;
			} elseif( plugin_config_get( 'gadiv_release_documentation' ) == '1' &&
				 $agilemantis_sprint->customFieldIsInProject( "inReleaseDocu" ) == true ) {
				$colspan = 1;
			} else {
				$colspan = 5;
			}
			
			if( $story['technical'] == 1 || $story['technical'] == 'Ja' ) {
				$checked = "checked";
			} else {
				$checked = "";
			}
			echo '
				<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">' .
				 plugin_lang_get( 'Technical' ) . '</td>
				<td colspan="' . $colspan . '">
					<input type="checkbox" style="width:10px;" name="technical" value="1" ' . 
						$checked . '>
				</td>
		';
		}
		if( plugin_config_get( 'gadiv_release_documentation' ) == '1' &&
			 $agilemantis_sprint->customFieldIsInProject( "inReleaseDocu" ) == true ) {
			if( plugin_config_get( 'gadiv_technical' ) == '1' &&
			 $agilemantis_sprint->customFieldIsInProject( "Technical" ) == true &&
			 plugin_config_get( 'gadiv_presentable' ) == '1' &&
			 $agilemantis_sprint->customFieldIsInProject( "Presentable" ) == true ) {
			$colspan = 0;
		} elseif( plugin_config_get( 'gadiv_presentable' ) == '1' &&
			 $agilemantis_sprint->customFieldIsInProject( "Presentable" ) == true ) {
			$colspan = 3;
		} elseif( plugin_config_get( 'gadiv_technical' ) == '1' &&
			 $agilemantis_sprint->customFieldIsInProject( "Technical" ) == true ) {
			$colspan = 3;
		} else {
			$colspan = 5;
		}
		
		if( $story['inReleaseDocu'] == 1 || $story['inReleaseDocu'] == 'Ja' ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		echo '
				<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">' .
			 plugin_lang_get( 'InReleaseDocu' ) . '</td>
				<td colspan="' . $colspan . '">
					<input type="checkbox" style="width:10px;" name="inReleaseDocu" value="1" ' .
			 $checked . '>
				</td>

		';
	}
	echo '</tr>';
?>