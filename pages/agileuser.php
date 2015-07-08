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


	html_page_top(plugin_lang_get( 'manage_user_title' ));

	# check if user has enough rights
	$t_user_right = $agilemantis_au->authUser();
	if( $t_user_right == 2 || $t_user_right == 3 || current_user_is_administrator() ) { ?>
<br>
<?php include( AGILEMANTIS_PLUGIN_URI.'/pages/footer_menu.php' );?>
<br>
<?php
	# save / update agileMantis additional user rights
	if( $_POST['action'] == 'saveUsers' ) {
		if( $_SESSION['expert'] == "" ) {
			$rsUser = $agilemantis_au->getAllUser();
			foreach( $rsUser as $num => $usr ) {
				$i = $usr[id];

				if( $_SESSION['participant'][$i] == 1 || $_SESSION['developer'][$i] == 1 ) {
					$particpant = 1;
				} else {
					$particpant = 0;
					$agilemantis_team->deleteStakeholderFromTeams( $i );
				}

				if( $_SESSION['developer'][$i] == 1 ) {
					$developer = 1;
				} else {
					$developer = 0;
					$agilemantis_team->deleteScrumDeveloperFromTeams( $i );
				}

				if( $_SESSION['administrator'][$i] == 1 ) {
					$administrator = 1;
				} else {
					$administrator = 0;
				}

				$agilemantis_au->setAgileMantisUserRights( $i, $particpant, $developer,$administrator );

			}
		} else {
			$userArray = array_keys( $_SESSION['expert'] );
			$agilemantis_au->setExpert( $userArray[0], 0 );
		}
		echo '<center><span class="message_ok">' .
			 plugin_lang_get( 'manage_user_successful_saved' ) . '</span></center><br>';
	}
	$user = $agilemantis_au->getAllUser();

	# create a filtered table view with all necassary information
	function createTableView( $id, $username, $realname, $email, $participant, $developer,
		$administrator, $expert ) {
		if( $participant == 1 ) {
			$participant_check = 'checked';
		} else {
			$participant_check = '';
		}
		if( $developer == 1 ) {
			$developer_check = 'checked';
		} else {
			$developer_check = '';
		}
		if( $administrator == 1 ) {
			$administrator_check = 'checked';
		} else {
			$administrator_check = '';
		}
		if( $expert == 1 ) {
			$expert_check = '';
		} else {
			$expert_check = 'disabled';
		}
		if( $developer == 1 || stristr( $username, 'Team-User-' ) ) {
			$participant_disable = 'disabled';
			$additional = '<input type="hidden" name="participant[' . $id . ']" value="1">';
			$participant_check = 'checked';
		} else {
			$participant_disable = '';
			$additional = '';
		}

		return '<tr ' . helper_alternate_class() . '>
					<td><b>' . $username . '</b></td>
					<td><b>' . $realname . '</b></td>
					<td><b>' . $email .
			 '</b></td>
					<td class="center"><input type="checkbox" name="participant[' .
			 $id . ']" value="1" ' . $participant_check . ' ' . $participant_disable . '></td>
					' . $additional .
			 '
					<td class="center"><input type="checkbox" name="developer[' .
			 $id . ']" value="1" ' . $developer_check .
			 '></td>
					<td class="center"><input type="checkbox" name="administrator[' .
			 $id . ']" value="1" ' . $administrator_check .
			 '></td>
					<td class="center"><input type="submit" name="expert[' .
			 $id . ']" value="' . plugin_lang_get( 'manage_user_remove_from_license' ) . '" ' .
			 $expert_check . '></td>
				</tr>';
	}
	?>

<div class="table-container">
	<table align="center" class="width75" cellspacing="1">
		<tr>
			<td><a href="<?php echo plugin_page("agileuser.php")?>">
						<?php echo plugin_lang_get( 'manage_user_show_all' )?></a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=a">A</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=b">B</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=c">C</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=d">D</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=e">E</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=f">F</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=g">G</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=h">H</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=i">I</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=j">J</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=k">K</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=l">L</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=m">M</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=n">N</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=o">O</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=p">P</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=q">Q</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=r">R</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=s">S</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=t">T</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=u">U</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=v">V</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=w">W</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=x">X</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=y">Y</a></td>
			<td><a href="<?php echo plugin_page("agileuser.php")?>&filter=z">Z</a></td>
			<td><form action="<?php echo plugin_page("agileuser.php")?>"
					method="post">
					<input type="submit" name="agileMantisParticipant"
						value="<?php echo plugin_lang_get( 'manage_user_show_only_participants' )?>">
				</form>
				<form action="<?php echo plugin_page("agileuser.php")?>"
					method="post">
					<input type="submit" name="agileMantisDeveloper"
						value="<?php echo plugin_lang_get( 'manage_user_show_only_developers' )?>">
				</form>
				<form action="<?php echo plugin_page("agileuser.php")?>"
					method="post">
					<input type="submit" name="agileMantisAdmin"
						value="<?php echo plugin_lang_get( 'manage_user_show_only_administrators' )?>">
				</form></td>
		</tr>
	</table>
</div>
<br>
<div class="table-container">
	<table align="center" class="width100" cellspacing="1">
		<tr>
			<td
				colspan="<?php
					if( plugin_is_loaded( 'agileMantisExpert' ) ) {
						echo '6';
					} else {
						echo '5';
					};?>">
				<b><?php echo plugin_lang_get( 'manage_user_title' )?></b>
					<?php if( user_get_name( auth_get_current_user_id() ) == 'administrator' ) { ?>
					<form action="<?php echo plugin_page( "add_user.php" ) ?>"
					method="post">
					<input type="submit" name="submit"
						value="<?php echo plugin_lang_get( 'manage_user_add_new_user' )?>">
				</form>
					<?php } ?>
				</td>
		</tr>
		<tr>
			<td class="category"><b><a
					href="<?php echo plugin_page("agileuser.php")?>&sort_by=username"><?php
							echo plugin_lang_get( 'manage_user_username' )?></a></b></td>
			<td class="category"><b><a
					href="<?php echo plugin_page("agileuser.php")?>&sort_by=realname"><?php
							echo plugin_lang_get( 'manage_user_realname' )?></a></b></td>
			<td class="category"><b><a
					href="<?php echo plugin_page("agileuser.php")?>&sort_by=email">Email</a></b></td>
			<td class="category"><center>
					<b><?php echo plugin_lang_get( 'manage_user_participant' )?></b>
				</center></td>
			<td class="category"><center>
					<b><?php echo plugin_lang_get( 'manage_user_developer' )?>



				</center> </b></td>
			<td class="category"><center>
					<b><?php echo plugin_lang_get( 'manage_user_administrator' )?>
				</center> </b></td>
				<?php
					if(plugin_is_loaded('agileMantisExpert')){
				?>
				<td class="category"><center>
					<b><?php echo plugin_lang_get( 'manage_user_expert' )?>
				</center> </b></td>
				<?php
					}
				?>
			</tr>
		<form action="<?php echo plugin_page('save_agileusers.php')?>"
			method="post">
			<input type="hidden" name="action" value="save">
				<?php
					if( !empty($user) ) {
						foreach( $user AS $num => $row ) {
							$mantis_role = $agilemantis_au->getAdditionalUserFields( $row['id'] );
				?>
				<?php if( $_POST['agileMantisParticipant'] && $mantis_role[0]['participant'] == 1 ) {?>
					<?php echo createTableView($row['id'], $row['username'], $row['realname'],
								$row['email'], $mantis_role[0]['participant'],
								$mantis_role[0]['developer'], $mantis_role[0]['administrator'],
								$mantis_role[0]['expert'])?>
				<?php }?>
				<?php if( $_POST['agileMantisAdmin'] && $mantis_role[0]['administrator'] == 1 ) { ?>
					<?php echo createTableView($row['id'], $row['username'], $row['realname'],
								$row['email'], $mantis_role[0]['participant'],
								$mantis_role[0]['developer'], $mantis_role[0]['administrator'],
								$mantis_role[0]['expert'])?>
				<?php }?>
				<?php if( $_POST['agileMantisDeveloper'] && $mantis_role[0]['developer'] == 1 ) { ?>
					<?php echo createTableView($row['id'], $row['username'], $row['realname'],
								$row['email'], $mantis_role[0]['participant'],
								$mantis_role[0]['developer'], $mantis_role[0]['administrator'],
								$mantis_role[0]['expert'] ) ?>
				<?php }?>
				<?php if( $_POST['agileMantisAdmin'] == "" && $_POST['agileMantisParticipant'] == ""
								&& $_POST['agileMantisDeveloper'] == "" ) { ?>
					<?php echo createTableView($row['id'], $row['username'], $row['realname'],
								$row['email'], $mantis_role[0]['participant'],
								$mantis_role[0]['developer'], $mantis_role[0]['administrator'],
								$mantis_role[0]['expert'])?>
				<?php }?>
				<?php
						}
					}
				?>
				<tr>
				<td
					colspan="<?php
						if( plugin_is_loaded('agileMantisExpert' ) ) {
							echo '6';
						} else {
							echo '5';
						};?>"><center>
						<input type="submit" name="edit"
							value="<?php echo plugin_lang_get( 'button_save' )?>">
					</center></td>
			</tr>
		</form>
	</table>
</div>
<?php
	} else {
?>
<br>
<center>
	<span class="message_error"><?php
		echo plugin_lang_get( 'manage_user_error_921A00' )?></span>
</center>
<?php
	}
?>
<?php html_page_bottom() ?>