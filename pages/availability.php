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

ini_set("display_errors",1);
	
	html_page_top( plugin_lang_get( 'manage_availability_title' ) ); 
?>
<br>
<?php include( AGILEMANTIS_PLUGIN_URI.'/pages/footer_menu.php' ); ?>
<br>
<?php if( !$_POST['kalender'] && $_POST['standard_availability']=="" && $_POST['staycal'] == 0 ) { ?>
<div class="table-container">
	<table align="center" class="width75" cellspacing="1">
		<tr>
			<td><a href="<?php echo plugin_page("availability.php")?>"><?php 
						echo plugin_lang_get( 'manage_availability_show_all' )?></a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=a">A</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=b">B</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=c">C</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=d">D</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=e">E</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=f">F</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=g">G</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=h">H</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=i">I</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=j">J</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=k">K</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=l">L</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=m">M</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=n">N</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=o">O</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=p">P</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=q">Q</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=r">R</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=s">S</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=t">T</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=u">U</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=v">V</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=w">W</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=x">X</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=y">Y</a></td>
			<td><a href="<?php echo plugin_page("availability.php")?>&filter=z">Z</a></td>
		</tr>
	</table>
</div>
<?php
	
	# save standard week availability
	if( $_POST['action'] == "save" ) {
		
		foreach( $_POST['mo'] as $num => $row ) {
			if( $agilemantis_av->getUserMarking( $num ) ) {
				$hinweis = plugin_lang_get( 'manage_availability_error_108302' );
				$mark_user[$num] = 1;
			} 
			
			$_POST['mo'][$num] = str_replace( ',', '.', $_POST['mo'][$num] );
			$_POST['tu'][$num] = str_replace( ',', '.', $_POST['tu'][$num] );
			$_POST['we'][$num] = str_replace( ',', '.', $_POST['we'][$num] );
			$_POST['th'][$num] = str_replace( ',', '.', $_POST['th'][$num] );
			$_POST['fr'][$num] = str_replace( ',', '.', $_POST['fr'][$num] );
			$_POST['sa'][$num] = str_replace( ',', '.', $_POST['sa'][$num] );
			$_POST['su'][$num] = str_replace( ',', '.', $_POST['su'][$num] );
			
			if( !is_numeric( $_POST['mo'][$num] ) || !is_numeric( $_POST['tu'][$num] ) ||
				 !is_numeric( $_POST['we'][$num] ) || !is_numeric( $_POST['th'][$num] ) ||
				 !is_numeric( $_POST['fr'][$num] ) || !is_numeric( $_POST['sa'][$num] ) ||
				 !is_numeric( $_POST['su'][$num] ) ) {
				$system = plugin_lang_get( 'manage_availability_error_985300' );
				$mark_user[$num] = 1;
			}
			
			if( $_POST['mo'][$num] > 24.00 || $_POST['tu'][$num] > 24.00 ||
				 $_POST['we'][$num] > 24.00 || $_POST['fr'][$num] > 24.00 ||
				 $_POST['th'][$num] > 24.00 || $_POST['th'][$num] > 24.00 ||
				 $_POST['sa'][$num] > 24.00 || $_POST['su'][$num] > 24.00 ) {
				$system = plugin_lang_get( 'manage_availability_error_984302' );
				$mark_user[$num] = 1;
			}
			
			if( $_POST['mo'][$num] < 0 || $_POST['tu'][$num] < 0 || $_POST['we'][$num] < 0 ||
				 $_POST['fr'][$num] < 0 || $_POST['th'][$num] < 0 || $_POST['th'][$num] < 0 ||
				 $_POST['sa'][$num] < 0 || $_POST['su'][$num] < 0 ) {
				$system = plugin_lang_get( 'manage_availability_error_984303' );
				$mark_user[$num] = 1;
			}
			
			if( $system == "" ) {
				$agilemantis_av->user_id = $num;
				$agilemantis_av->monday = $_POST['mo'][$num];
				$agilemantis_av->tuesday = $_POST['tu'][$num];
				$agilemantis_av->wednesday = $_POST['we'][$num];
				$agilemantis_av->thursday = $_POST['th'][$num];
				$agilemantis_av->friday = $_POST['fr'][$num];
				$agilemantis_av->saturday = $_POST['sa'][$num];
				$agilemantis_av->sunday = $_POST['su'][$num];
				$agilemantis_av->availability = $row + $_POST['tu'][$num] + $_POST['we'][$num] +
					 $_POST['th'][$num] + $_POST['fr'][$num] + $_POST['sa'][$num] +
					 $_POST['su'][$num];
				$agilemantis_av->setUserAvailability();
			}
		}
	}
	
	$userData = $agilemantis_au->getAgileUser( true );
	if( !empty( $userData ) ) {
		foreach( $userData as $num => $row ) {
			if( $agilemantis_av->getUserMarking( $row['id'] ) ) {
				$hinweis = plugin_lang_get( 'manage_availability_error_108302' );
				$mark_user[$row['id']] = 1;
		}
	}
}
?>
<?php if($hinweis){?>
<br>
<center>
	<span class="message_error"><?php echo $hinweis?></span>
</center>
<?php }?>
<?php if($system){?>
<br>
<center>
	<span class="message_error"><?php echo $system?></span>
</center>
<?php }?>
<?php
	if(!$system && $_POST['action'] == 'save'){
		echo '<br><center><span class="message_ok">'.
			plugin_lang_get( 'manage_availability_successful_saved' ).'</span></center>';
	}
?>
<br>
<form action="" method="post">
	<input type="hidden" name="action" value="save"> <input type="hidden"
		name="team_id" value="<?php echo $_POST['team_id']?>">
	<div class="table-container">
		<table align="center" class="width100" cellspacing="1">
			<tr>
				<td colspan="9"><b><?php echo plugin_lang_get( 'manage_availability_standard_title' )?></b></td>
				<td><input type="text" name="month" value="3" size="1" maxlength="2">
					<b><?php echo plugin_lang_get( 'months' )?></b></td>
			</tr>
			<tr>
				<td class="category"><?php echo plugin_lang_get( 'manage_availability_user' )?></td>
				<td class="category"><?php echo plugin_lang_get( 'monday' )?></td>
				<td class="category"><?php echo plugin_lang_get( 'tuesday' )?></td>
				<td class="category"><?php echo plugin_lang_get( 'wednesday' )?></td>
				<td class="category"><?php echo plugin_lang_get( 'thursday' )?></td>
				<td class="category"><?php echo plugin_lang_get( 'friday' )?></td>
				<td class="category"><?php echo plugin_lang_get( 'saturday' )?></td>
				<td class="category"><?php echo plugin_lang_get( 'sunday' )?></td>
				<td class="category"><?php echo plugin_lang_get( 'manage_availability_total' )?></td>
				<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
			</tr>
	<?php
		if(!empty($userData)){
		foreach($userData AS $num => $row){
	?>
			<?php if($row['developer'] == 1){?>
			<tr <?php echo helper_alternate_class() ?>>
				<td
					<?php if($mark_user[$row['id']] == 1 || $agilemantis_av->getUserMarking($row['id'])){?>
					style="background: #FF7C7F;" <?php } else {?>
					style="background:none" <?php }?>><?php echo $row['username']?></td>
				<td><input type="text" style="width: 100px;"
					name="mo[<?php echo $row['id']?>]"
					value="<?php echo sprintf("%.2f",
						$agilemantis_av->getUserAvailability($row['id'],"1"));?>"></td>
				<td><input type="text" style="width: 100px;"
					name="tu[<?php echo $row['id']?>]"
					value="<?php echo sprintf("%.2f",
						$agilemantis_av->getUserAvailability($row['id'],"2"));?>"></td>
				<td><input type="text" style="width: 100px;"
					name="we[<?php echo $row['id']?>]"
					value="<?php echo sprintf("%.2f",
						$agilemantis_av->getUserAvailability($row['id'],"3"));?>"></td>
				<td><input type="text" style="width: 100px;"
					name="th[<?php echo $row['id']?>]"
					value="<?php echo sprintf("%.2f",
						$agilemantis_av->getUserAvailability($row['id'],"4"));?>"></td>
				<td><input type="text" style="width: 100px;"
					name="fr[<?php echo $row['id']?>]"
					value="<?php echo sprintf("%.2f",
						$agilemantis_av->getUserAvailability($row['id'],"5"));?>"></td>
				<td><input type="text" style="width: 100px;"
					name="sa[<?php echo $row['id']?>]"
					value="<?php echo sprintf("%.2f",
						$agilemantis_av->getUserAvailability($row['id'],"6"));?>"></td>
				<td><input type="text" style="width: 100px;"
					name="su[<?php echo $row['id']?>]"
					value="<?php echo sprintf("%.2f",
						$agilemantis_av->getUserAvailability($row['id'],"0"));?>"></td>
				<td><?php echo $before?><?php echo sprintf("%.2f",
					$agilemantis_av->getUserAvailability($row['id'],"7"));?><?php echo $after?></td>
				<td><input type="submit" name="kalender[<?php echo $row['id']?>]"
					value="<?php echo plugin_lang_get( 'button_open_calendar' )?>"></td>
			</tr>
	<?php
				}
		}
	}
	?>
</table>
	</div>
	<br>
	<center>
		<input type="submit" name="submit"
			value="<?php echo plugin_lang_get( 'button_save' )?>">
	</center>
<?php if($error != ""){?>
<br>
	<center>
		<span class="message_error"><?php echo $error?></span>
	</center>
<?php } ?>
</form>
<div class="clear"></div>
<?php }?>
<?php
	# add back button action
if( $_POST['back_button'] ) {
	header( $agilemantis_sprint->forwardReturnToPage( 'availability.php' ) );
} else {
	
	# save / update values from manage availbility for only one user
	if( $_POST['action'] == "saveCal" && $_POST['submit'] ) {
		$today_date = date( 'Y-m-d' );
		foreach( $_POST['kalender'] as $num => $row ) {
			$user_id = $num;
			foreach( $_POST['capacity'][$user_id] as $key => $value ) {

				$agilemantis_av->deleteUserCapacity( $user_id, $key );
				$value = str_replace( ',', '.', $value );
				if( !is_numeric( $value ) ) {
					$system = plugin_lang_get( 'manage_availability_error_985301' );
					$_POST['staycal'] = 1;
				} else {
					if( $value > 24.00 ) {
						$system = plugin_lang_get( 'manage_availability_error_984301' );
						$mark_user[$user_id] = 1;
						$_POST['staycal'] = 1;
					}
					if( $value < 0 ) {
						$system = plugin_lang_get( 'manage_availability_error_984300' );
						$_POST['staycal'] = 1;
					}
					if( $value <= 24.00 && $value >= 0.00 ) {
						$agilemantis_av->setUserCapacity( $user_id, $key, $value );
					}
				}
				$current_day = $key;
				if( $current_day >= $today_date && $system == "" ) {
					if( $agilemantis_av->getCapacityToSavedAvailability( $user_id, $key ) > $value ) {
						$hinweis = plugin_lang_get( 'manage_availability_error_108300' );
						$count_over_capacity[$user_id]++;
						$_POST['staycal'] = 1;
					}
				}
				if( $count_over_capacity[$user_id] > 0 ) {
					$agilemantis_av->setUserAsMarkType( $user_id, 1 );
				} else {
					$agilemantis_av->setUserAsMarkType( $user_id, 0 );
				}
			}
			if( $_POST['standard_availability'] == "" && $system == "" && $hinweis == "" ) {
				header( $agilemantis_sprint->forwardReturnToPage( 'availability.php' ) );
			}
		}
	}
}

# save / update standard availbility for one user
if( $_POST['standard_availability'] != "" ) {
	foreach( $_POST['standard_availability'] as $num => $row ) {
		foreach( $row as $key => $value ) {
			foreach( $value as $new => $title ) {
				$year = $new;
			}
		}
		if( $agilemantis_av->saveMonthAvailability( $num, $year, $key ) > 0 ) {
			$agilemantis_av->setUserAsMarkType( $num, 1 );
			$hinweis = plugin_lang_get( 'manage_availability_error_108300' );
		} else {
			$agilemantis_av->setUserAsMarkType( $num, 0 );
			$hinweis = "";
		}
	}
}

if( $_POST['kalender'] || $_POST['standard_availability'] != "" || $_POST['staycal'] == 1 ) {
	
	$days[0] = plugin_lang_get( 'mo' );
	$days[1] = plugin_lang_get( 'tu' );
	$days[2] = plugin_lang_get( 'we' );
	$days[3] = plugin_lang_get( 'th' );
	$days[4] = plugin_lang_get( 'fr' );
	$days[5] = plugin_lang_get( 'sa' );
	$days[6] = plugin_lang_get( 'su' );

	# include calendary view style
	if( $hinweis ) {
	?>
<center>
	<span class="message_warn"><?php echo $hinweis?></span>
</center>
<br>
<?php } ?>
	<?php
	if( $system ) {
	?>
<center>
	<span class="message_warn"><?php echo $system?></span>
</center>
<br>
<?php } ?>
<form action="" method="post">
	<input type="hidden" name="action" value="saveCal"> <input
		type="hidden" name="month" value="<?php echo $_POST['month']?>"> <input
		type="hidden" name="fromProductBacklog"
		value="<?php echo $_POST['fromProductBacklog']?>"> <input
		type="hidden" name="productBacklogName"
		value="<?php echo $_POST['productBacklogName']?>"> <input
		type="hidden" name="fromStatistics"
		value="<?php echo $_POST['fromStatistics']?>"> <input type="hidden"
		name="sprintName" value="<?php echo $_POST['sprintName']?>"> <input
		type="hidden" name="team_id" value="<?php echo $_POST['team_id']?>"> <input
		type="hidden" name="fromSprintBacklog"
		value="<?php echo $_POST['fromSprintBacklog']?>"> <input type="hidden"
		name="fromTaskboard" value="<?php echo $_POST['fromTaskboard']?>"> <input
		type="hidden" name="fromDailyScrum"
		value="<?php echo $_POST['fromDailyScrum']?>"> <input type="hidden"
		name="submit" value="Alles Speichern">
	<?php foreach($_POST['kalender'] AS $num => $row){?>
		<input type="hidden" name="kalender[<?php echo $num?>]"
		value="Open Calender">
	<?php }?>
	<div class="table-container">
		<table align="center" class="width100" cellspacing="1">
			<tr>
				<td class="left" style="font-weight: bold">
				<?php echo plugin_lang_get( 'manage_availability_planning' )?>
			</td>
				<td class="right"><input type="submit" name="submit"
					value="<?php echo plugin_lang_get( 'manage_availability_save_all' )?>">
					<input type="submit" name="back_button"
					value="<?php echo plugin_lang_get( 'button_back' )?>"></td>
			</tr>
		</table>
	</div>
	<br>
	<?php foreach($_POST['kalender'] AS $num => $row){?>
		<input type="hidden" name="calUser[<?php echo $num?>]"
		value="Add Standard-Availability">
		<?php
		# if more than one make new month calendaries
		if( $_POST['month'] > 0 ) {
			$monat_count = date('n');
			for( $i=0; $i < $_POST['month']; $i++ ) {
				$monat = date( 'n' ) + $i;
				$start = mktime(0, 0, 0, $monat, 1, date('Y') );
				$anzahl_tage_im_monat = date('t', $start);
				$end = mktime(0, 0, 0, $monat, $anzahl_tage_im_monat,date( 'Y' ) );
				?>
	<div class="fullcalendar height550">
		<table cellspacing="1" class="width100">
			<input type="hidden" name="calUser[<?php echo $num?>]"
				value="Add Standard-Availability">
			<tr>
				<td class="left" colspan="2"><b><?php echo plugin_lang_get( 'manage_availability_planning_for' )?></b>
					<span style="font-weight: bold; color: grey;"><?php 
						echo $agilemantis_cal->getUserName($num)?></span></td>
			</tr>
			<tr>
				<td>
					<div class="calendar height400">
						<?php 
						$count = $agilemantis_cal->getCalender($start,$end,$num,$days);
						?>
					</div>
					<center>
						<input type="submit"
							name="standard_availability[<?php 
								echo $num?>][<?php echo $monat_count?>][<?php echo date('Y',$start)?>]"
							value="<?php echo plugin_lang_get( 'manage_availability_save_standard' )?>">
					</center>
					<?php if($count > 0){
							$error = '<br><br><span class="message_error">'.
								plugin_lang_get( 'manage_availability_error_108301' ).'</span>';
						  } else { 
							$error = '<br><br>&nbsp;';
						  }?>
					<center><?php echo $error;?></center>
				</td>
			</tr>
		</table>
	</div> 
			<?php
				if( $monat_count >= 12 ) { 
					$monat_count = 0;
				}
				$monat_count++;
			}
		}
	}
	?>
	<div style="clear: both;"></div>
	<center>
		<input type="submit" style="margin-top: 10px;" name="submit"
			value="<?php echo plugin_lang_get( 'manage_availability_save_all' )?>">
	</center>
	<br>
</form>
<?php }?>
<?php html_page_bottom() ?>