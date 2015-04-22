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


html_page_top( plugin_lang_get( 'edit_teams_title' ) );

if( empty($_POST) || $_POST['back_button'] ) {
	header( $agilemantis_sprint->forwardReturnToPage( "teams.php" ) );
} else {
	
	$team_id = "";
	if( isset( $_POST['id'] ) ) {
		$team_id = $_POST['id'];
	}
	$user_id = "";
	if( isset( $_POST['product_owner'] ) ) {
		$user_id = $_POST['product_owner'];
	} elseif( isset( $_POST['scrum_master'] ) ) {
		$user_id = $_POST['scrum_master'];
	} elseif( isset( $_POST['developer'] ) ) {
		$user_id = $_POST['developer'];
	}
	if( $team_id != "" && $user_id != "" && $user_id!="0" ) {
		
		$projects = "";
		if( !$agilemantis_team->is_admin_user( $user_id ) ) {
			$result = $agilemantis_project
					->get_projects_by_team_id_where_user_has_no_access_rights($team_id, $user_id );
			if( count( $result ) > 0 ) {
				$projects = $result[0]['name'];
			}
			for( $i = 1; $i < count( $result ); $i++ ) {
				$projects .= ", " . $result[$i]['name'];
			}
		}
		if( $projects != "" ) {
			$msg = plugin_lang_get( 'edit_product_backlog_error_100603' );
			$msg = str_replace( "[names]", $projects, $msg );
			if( $system != "" ) {
				$system .= "<br>";
			}
			$system .= $msg;
		}
	}
	
	# try to change the product
	if( $_POST['product_owner'] == 0 && isset( $_POST['product_owner'] ) 
			&& $_POST['id'] > 0 && $agilemantis_team->hasSprints( $_POST['id'] ) > 0 ) {
		
		$_POST['product_owner'] = $_POST['old_product_owner'];
		$system = plugin_lang_get( 'edit_teams_error_100200' );
	}
	
	# delete product owner
	if( $_POST['product_owner'] == 0 && isset( $_POST['product_owner'] ) && $system == "" ) {
		
		$user_id = ( int ) $_POST['product_owner'];
		$team_id = $_POST['id'];
		$agilemantis_team->deleteTeamRoleMember( $team_id, 1 );
	}
	
	# change product owner
	if( $_POST['product_owner'] > 0 ) {
		$user_id = ( int ) $_POST['product_owner'];
		$team_id = $_POST['id'];
		$agilemantis_team->deleteTeamRoleMember( $team_id, 1 );
		$agilemantis_team->addTeamMember( $user_id, $team_id, 1 );
		$_SESSION['setscrum'] = 1;
		$_SESSION['hasUser'] = 1;
	}
	
	# try to change the scrum master
	if( $_POST['scrum_master'] == 0 && isset( $_POST['scrum_master'] ) 
			&& $_POST['id'] > 0 && $agilemantis_team->hasSprints( $_POST['id'] ) > 0 ) {
		
		$_POST['scrum_master'] = $_POST['old_scrum_master'];
		$system = plugin_lang_get( 'edit_teams_error_100201' );
	}
	
	# delete scrum master
	if( $_POST['scrum_master'] == 0 && isset( $_POST['scrum_master'] ) && $system == "" ) {
		$user_id = ( int ) $_POST['scrum_master'];
		$team_id = $_POST['id'];
		$agilemantis_team->deleteTeamRoleMember( $team_id, 2 );
	}
	
	# change scrum master
	if( $_POST['scrum_master'] > 0 ) {
		$user_id = ( int ) $_POST['scrum_master'];
		$team_id = $_POST['id'];
		$agilemantis_team->deleteTeamRoleMember( $team_id, 2 );
		$agilemantis_team->addTeamMember( $user_id, $team_id, 2 );
		$_SESSION['setscrum'] = 1;
		$_SESSION['hasUser'] = 1;
	}
	
	# add developer
	if( $_POST['developer'] > 0 ) {
		$user_id = ( int ) $_POST['developer'];
		$team_id = $_POST['id'];
		$agilemantis_team->addTeamMember( $user_id, $team_id, 3 );
		$_SESSION['hasUser'] = 1;
	}
	
	# add customer
	if( $_POST['customer'] > 0 ) {
		$user_id = ( int ) $_POST['customer'];
		$team_id = $_POST['id'];
		$agilemantis_team->addTeamMember( $user_id, $team_id, 4 );
		$_SESSION['hasUser'] = 1;
	}
	
	# add user 
	if( $_POST['user'] > 0 ) {
		$user_id = ( int ) $_POST['user'];
		$team_id = $_POST['id'];
		$agilemantis_team->addTeamMember( $user_id, $team_id, 5 );
		$_SESSION['hasUser'] = 1;
	}
	
	# add manager
	if( $_POST['manager'] > 0 ) {
		$user_id = ( int ) $_POST['manager'];
		$team_id = $_POST['id'];
		$agilemantis_team->addTeamMember( $user_id, $team_id, 6 );
		$_SESSION['hasUser'] = 1;
	}
	
	# edit team information
	if( $_POST['action'] == "edit" ) {
		
		$agilemantis_team->id = ( int ) $_POST['id'];
		$agilemantis_team->name = $_POST['t_name'];
		$agilemantis_team->daily_scrum = $_POST['daily_scrum'];
		
		if( empty( $agilemantis_team->name ) ) {
			$system = plugin_lang_get( 'edit_teams_error_922200' );
			if( $_POST['old_team_name'] != "" ) {
				$_POST['t_name'] = $_POST['old_team_name'];
			}
		} else {
			if( $agilemantis_team->isTeamNameUnique() == false ) {
				$system = plugin_lang_get( 'edit_teams_error_982200' );
			} elseif( $system == "" ) {
				if( ( int ) $_POST['product_backlogs'] > 0 ) {
					$agilemantis_team->description = $_POST['t_description'];
					$agilemantis_team->product_backlog = $_POST['product_backlogs'];
					
					$tmp_user_id = null;
					$tmpPb = $agilemantis_pb->getProductBacklogs( 
											$agilemantis_team->product_backlog );
					if( $tmpPb && sizeof( tmpPb ) === 1 ) {
						$tmp_user_id = $tmpPb[0]['user_id'];
					}
					
					$agilemantis_team->id = ( int ) $_POST['id'];
					$agilemantis_team->editTeam();
					if( $tmp_user_id ) {
						$agilemantis_team->addTeamMember( $tmp_user_id, $agilemantis_team->id, 7 );
					}
				} else {
					$system = plugin_lang_get( 'edit_teams_error_923200' );
				}
				if( $_SESSION['hasUser'] == 1 && ( int ) $agilemantis_team->id > 0 
						&& $_POST['product_backlogs'] > 0 
						&& $_POST['user'] == 0 
						&& $_POST['manager'] == 0 
						&& $_POST['customer'] == 0 
						&& $_POST['developer'] == 0 
						&& $_POST['old_product_owner'] == $_POST['product_owner'] 
						&& $_POST['old_scrum_master'] == $_POST['scrum_master'] ) {
					
					$_SESSION['hasUser'] = 0;
					header( "Location: " . plugin_page( 'teams.php' ) );
				}
			}
		}
		
	}
	
	# delete one team member
	if( $_POST['deleteTeamMember'] != "" && ( int ) $_POST['id'] > 0 
			&& ( int ) $_POST['user_id'] > 0 && ( int ) $_POST['role_id'] > 0 ) {
		
		$agilemantis_team->deleteSelectedTeamMember( 
						$_POST['id'], $_POST['user_id'], $_POST['role_id'] );
	}
	
	if( $_POST['edit'] ) {
		$agilemantis_team->id = ( int ) implode( ',', array_flip( $_POST['edit'] ) );
	}
	
	if( ( int ) $_POST['id'] > 0 ) {
		$agilemantis_team->id = ( int ) $_POST['id'];
	}
	
	if( ( int ) $agilemantis_team->id > 0 ) {
		$t = $agilemantis_team->getSelectedTeam();
	}
}
?>
<br>
<?php include(AGILEMANTIS_PLUGIN_URI.'/pages/footer_menu.php');?>
<br>
<?php if($system){?>
<br>
<center>
	<span class="message_error"><?php echo $system?></span>
</center>
<br>
<?php }?>
<form action="<?php echo plugin_page('edit_team.php') ?>" method="post">
	<input type="hidden" name="action" value="edit"> <input type="hidden"
		name="id" value="<?php echo $agilemantis_team->id?>">
	<div class="table-container">
		<table align="center" class="width75" cellspacing="1">
			<tr>
				<td class="form-title" colspan="3">
			<?php echo plugin_lang_get( 'edit_teams_title' )?>
		</td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category" width="30%">*Name</td>
				
				<?php 
				if($t[0]['name']) { 
					$t_team_name = $t[0]['name'];
				} else {
					$t_team_name = $_POST['t_name']; 
				}
				?>
				
				<td class="left" width="70%"><input type="text" size="105"
					maxlength="128" name="t_name"
					value="<?php echo $t_team_name ?>">
					<input type="hidden" name="old_team_name"
					value="<?php echo $t[0]['name']?>"></td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category">
			<?php echo plugin_lang_get( 'common_description' )?>
		</td>
				<?php   
				if( $t[0]['description'] ) {
					$t_descr = $t[0]['description'];
				} else {
					$t_descr = $_POST['t_description'];
				}
				?>
		
				<td class="left"><textarea name="t_description" 
						cols="80" rows="10"><?php echo string_display_line($t_descr) ?></textarea>
				</td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category">*Product Backlog</td>
				<td class="left">
			<?php 
				if( $agilemantis_team->hasSprints( $agilemantis_team->id ) > 0 
						&& $agilemantis_team->id > 0){
					$disable_product_backlog = 'disabled';
				} else {
					$disable_product_backlog = '';
				}
			?>
			<?php if( $agilemantis_team->hasSprints( $agilemantis_team->id ) > 0 ) { ?>
				<input type="hidden" name="product_backlogs"
					value="<?php echo $t[0]['pb_id']?>">
			<?php }?>
			<select name="product_backlogs" <?php echo $disable_product_backlog?>>
						<option><?php echo plugin_lang_get( 'common_chose' )?></option>
				<?php 
					$data = $agilemantis_team->getProductBacklogs();
					foreach( $data AS $num => $row ) {
				?>
					<option value="<?php echo $row['id']?>"
							<?php 
								if( $t[0]['pb_id']==$row['id'] ) { 
									echo 'selected';
									$name=string_display_line($row['name']);
								}
							?>>
							<?php echo string_display_line($row['name'])?></option>
				<?php }?>
			</select>
				</td>
			</tr>
			<?php if( plugin_config_get('gadiv_daily_scrum') == 1 ) { ?>
			<tr <?php echo helper_alternate_class() ?>>
						<td class="category">Daily Scrum Meeting mit Taskboard</td>
						<td class="left"><input type="checkbox" name="daily_scrum"
							<?php 
								if( $t[0]['daily_scrum'] == 1 
									|| (plugin_config_get('gadiv_daily_scrum') == 1 
										&& $t[0]['id'] == 0 ) ) { ?>
							checked <?php }?> value="1"></td>
					</tr>
			<?php }?>	
			<tr>
				<td><span class="required"> * <?php echo lang_get( 'required' ) ?></span>
				</td>
				<td class="center"><input type="submit" class="button"
					value="<?php echo plugin_lang_get( 'button_save' )?>"> <input
					type="submit" name="back_button"
					value="<?php echo plugin_lang_get( 'button_back' )?>"></td>
			</tr>
		</table>
	</div>
</form>
<?php if( $agilemantis_team->id > 0 ) { ?>
<br>
<?php if( $agilemantis_team->hasSprints( $agilemantis_team->id ) > 0 ) { ?>
<input type="hidden" name="product_backlogs"
	value="<?php echo $t[0]['pb_id']?>">
<?php }?>
<div class="table-container">
	<table align="center" class="width75" cellspacing="1">
		<tr <?php echo helper_alternate_class() ?>>
		
		
		<tr>
			<td class="form-title" colspan="2">Team User</td>
		</tr>
		<td class="left">
			<?php echo $agilemantis_team->getTeamUserByBacklogName( $name )?>
		</td>
		</tr>
	</table>
</div>
<br>
<div class="table-container">
	<table align="center" class="width75" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2"><a name="ProductOwner">Product
					Owner</a></td>
		</tr>
		<tr>
			<td class="left">
				<form
					action="<?php echo plugin_page('edit_team.php')?>#ProductOwner"
					method="post">
					<input type="hidden" name="action" value="addTeamMember"> <input
						type="hidden" name="id" value="<?php echo $agilemantis_team->id?>">
					<input type="hidden" name="old_product_owner"
						value="<?php echo $opoid?>"> <select name="product_owner">
						<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
						<?php 
						$team_user = $agilemantis_au->getAgileUser();
						foreach( $team_user AS $num => $row ) { ?>
						<option value="<?php echo $row['id']?>"
							<?php 
								if( $agilemantis_team->getTeamProductOwner() == $row['id'] ) { 
									echo 'selected'; 
									$opoid = $row['id'];
								}?>>
								<?php echo empty( $row['realname'] ) ? $row['username'] : $row['realname'] ?></option>
						<?php }?>
					</select> <input type="submit" name="addProductOwner"
						value="<?php echo plugin_lang_get( 'button_change' )?>">
				</form>
			</td>
		</tr>
	</table>
</div>
<br>
<div class="table-container">
	<table align="center" class="width75" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2"><a name="ScrumMaster">Scrum Master</a>
			</td>
		</tr>
		<tr>
			<td class="left">
				<form action="<?php echo plugin_page('edit_team.php')?>#ScrumMaster"
					method="post">
					<input type="hidden" name="action" value="addTeamMember"> <input
						type="hidden" name="id" value="<?php echo $agilemantis_team->id?>">
					<input type="hidden" name="old_scrum_master"
						value="<?php echo $oscid?>"> <select name="scrum_master">
						<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
					<?php 
						$team_user = $agilemantis_au->getAgileUser();
						foreach( $team_user AS $num => $row ) { ?>
					<option value="<?php echo $row['id']?>"
							<?php 
								if( $agilemantis_team->getTeamScrumMaster() == $row['id'] ) { 
									echo 'selected';$oscid = $row['id'];
								}?>>
								<?php echo empty( $row['realname'] ) ? $row['username'] : $row['realname'] ?></option>
					<?php } ?>
				</select> <input type="submit" name="addProductOwner"
						value="<?php echo plugin_lang_get( 'button_change' )?>">
				</form>
			</td>
		</tr>
	</table>
</div>
<br>
<div class="table-container">
	<table align="center" class="width75" cellspacing="1">
		<tr <?php echo helper_alternate_class() ?>>
			<td class="form-title" colspan="4"><a name="Developer"><?php 
						echo plugin_lang_get( 'edit_team_developer' )?></a>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">Name</td>
			<td class="category"><?php echo plugin_lang_get( 'edit_team_username' )?></td>
			<td class="category">Email</td>
			<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
		</tr>
	<?php
		$tm = $agilemantis_team->getTeamDeveloper();
		if( !empty( $tm ) ) {
			foreach( $tm AS $num => $row ) {
	?>
		<tr <?php echo helper_alternate_class() ?>>
			<td><?php echo $row['realname']?></td>
			<td><?php echo $row['username']?></td>
			<td><?php echo $row['email']?></td>
			<td>
				<?php
				# if the team is not working on a sprint, all team members can be deleted
				if( $agilemantis_team->hasSprints( $agilemantis_team->id ) > 0 ) {
					$do_not_delete_last_member = true;
					if( $do_not_delete_last_member ) {
						if( (count( $tm ) - 1) > 0 ) {
							$do_not_delete_last_member = false;
						} else {
							$do_not_delete_last_member = true;
						}
					}
				}
				
				# team members with an open task cannot be deleted
				if( $agilemantis_team->memberHasOpenTasks( 
									$agilemantis_team->id,$row['id'] ) == false ) {
					if( $do_not_delete_last_member == false ) {
				?>
					<form action="<?php echo plugin_page('delete_team_member.php') ?>"
					method="post">
					<input type="hidden" name="action" value="deleteTeamMember"> <input
						type="hidden" name="team_id" value="<?php echo $row['team_id'] ?>">
					<input type="hidden" name="user_id"
						value="<?php echo $row['user_id'] ?>"> <input type="hidden"
						name="role_id" value="3"> <input type="submit"
						name="deleteTeamMember"
						value="<?php echo plugin_lang_get( 'button_remove' )?>">
				</form>
				<?php
					}
				}
				?>
			</td>
		</tr>
	<?php 
	
			}
		} 
	?>
		<tr>
			<td class="left">
				<form action="<?php echo plugin_page('edit_team.php')?>#Developer"
					method="post">
					<input type="hidden" name="action" value="addDeveloper"> <input
						type="hidden" name="id" value="<?php echo $agilemantis_team->id?>">
					<select name="developer">
						<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
					<?php 
					$team_user = $agilemantis_au->getAgileUser( true );
					
					foreach( $team_user AS $num => $row ) {
						if( $row['developer'] == 1 ) {
					?>
					<option value="<?php echo $row['id']?>"><?php 
					  echo empty( $row['realname'] ) ? $row['username'] : $row['realname'] ?></option>
					<?php
						}
					}
					?>
				</select> <input type="submit" name="addDeveloper"
						value="<?php echo plugin_lang_get( 'button_add' )?>">
				</form>
			</td>
		</tr>
	</table>
</div>
<br>
<div class="table-container">
	<table align="center" class="width75" cellspacing="1">
		<tr <?php echo helper_alternate_class() ?>>
			<td class="form-title" colspan="4"><a name="ProductUser"><?php 
				echo plugin_lang_get( 'edit_team_product_user' )?></a>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">Name</td>
			<td class="category"><?php echo plugin_lang_get( 'edit_team_username' )?></td>
			<td class="category">Email</td>
			<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
		</tr>
	<?php
		$productUser = $agilemantis_team->getTeamProductUser();
		if( !empty($productUser ) ) {
			foreach( $productUser AS $num => $row ) {
	?>
		<tr <?php echo helper_alternate_class() ?>>
			<td><?php echo $row['realname']?></td>
			<td><?php echo $row['username']?></td>
			<td><?php echo $row['email']?></td>
			<td>
				<form action="<?php echo plugin_page('delete_team_member.php') ?>"
					method="post">
					<input type="hidden" name="action" value="deleteTeamMember"> <input
						type="hidden" name="team_id" value="<?php echo $row['team_id'] ?>">
					<input type="hidden" name="user_id"
						value="<?php echo $row['user_id'] ?>"> <input type="hidden"
						name="role_id" value="5"> <input type="submit"
						name="deleteTeamMember"
						value="<?php echo plugin_lang_get( 'button_remove' )?>">
				</form>
			</td>
		</tr>
	<?php 
			}
		} 
	?>
		<tr>
			<td class="left">
				<form action="<?php echo plugin_page('edit_team.php')?>#ProductUser"
					method="post">
					<input type="hidden" name="action" value="addProductUser"> <input
						type="hidden" name="id" value="<?php echo $agilemantis_team->id?>">
					<select name="user">
						<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
					<?php
					$team_user = $agilemantis_au->getAgileUser();
					foreach( $team_user AS $num => $row ) {
					?>
					<option value="<?php echo $row['id']?>"><?php 
					  echo empty( $row['realname'] ) ? $row['username'] : $row['realname'] ?></option>
					<?php
					}
					?>
				</select> <input type="submit" name="addProductUser"
						value="<?php echo plugin_lang_get( 'button_add' )?>">
				</form>
			</td>
		</tr>
	</table>
</div>
<br>
<div class="table-container">
	<table align="center" class="width75" cellspacing="1">
		<tr <?php echo helper_alternate_class() ?>>
			<td class="form-title" colspan="4"><a name="Manager">Manager</a></td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">Name</td>
			<td class="category"><?php echo plugin_lang_get( 'edit_team_username' )?></td>
			<td class="category">Email</td>
			<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
		</tr>
	<?php
		$man = $agilemantis_team->getTeamManager();
		if( !empty($man) ) {
			foreach( $man AS $num => $row ) {
	?>
		<tr <?php echo helper_alternate_class() ?>>
			<td><?php echo $row['realname']?></td>
			<td><?php echo $row['username']?></td>
			<td><?php echo $row['email']?></td>
			<td>
				<form action="<?php echo plugin_page('delete_team_member.php') ?>"
					method="post">
					<input type="hidden" name="action" value="deleteTeamMember"> <input
						type="hidden" name="team_id" value="<?php echo $row['team_id'] ?>">
					<input type="hidden" name="user_id"
						value="<?php echo $row['user_id'] ?>"> <input type="hidden"
						name="role_id" value="6"> <input type="submit"
						name="deleteTeamMember"
						value="<?php echo plugin_lang_get( 'button_remove' )?>">
				</form>
			</td>
		</tr>
	<?php 
			}
		} 
	?>
		<tr>
			<td class="left">
				<form action="<?php echo plugin_page('edit_team.php') ?>#Manager"
					method="post">
					<input type="hidden" name="action" value="addManager"> <input
						type="hidden" name="id" value="<?php echo $agilemantis_team->id?>">
					<select name="manager">
						<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
					<?php
					$team_user = $agilemantis_au->getAgileUser();
					foreach( $team_user AS $num => $row ) {
					?>
					<option value="<?php echo $row['id']?>"><?php 
						echo empty( $row['realname'] ) ? $row['username'] : $row['realname'] ?></option>
					<?php
					}
					?>
				</select> <input type="submit" name="addManager"
						value="<?php echo plugin_lang_get( 'button_add' )?>">
				</form>
			</td>
		</tr>
	</table>
</div>
<br>
<div class="table-container">
	<table align="center" class="width75" cellspacing="1">
		<tr <?php echo helper_alternate_class() ?>>
			<td class="form-title" colspan="4"><a name="Customer"><?php
						 echo plugin_lang_get( 'edit_team_customer' )?></a>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">Name</td>
			<td class="category"><?php echo plugin_lang_get( 'edit_team_username' )?></td>
			<td class="category">Email</td>
			<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
		</tr>
	<?php
		$tc = $agilemantis_team->getTeamCustomer();
		if( !empty($tc) ) {
			foreach( $tc AS $num => $row ) {
	?>
		<tr <?php echo helper_alternate_class() ?>>
			<td><?php echo $row['realname']?></td>
			<td><?php echo $row['username']?></td>
			<td><?php echo $row['email']?></td>
			<td>
				<form action="<?php echo plugin_page('delete_team_member.php') ?>"
					method="post">
					<input type="hidden" name="action" value="deleteTeamMember"> <input
						type="hidden" name="team_id" value="<?php echo $row['team_id'] ?>">
					<input type="hidden" name="user_id"
						value="<?php echo $row['user_id'] ?>"> <input type="hidden"
						name="role_id" value="4"> <input type="submit"
						name="deleteTeamMember"
						value="<?php echo plugin_lang_get( 'button_remove' )?>">
				</form>
			</td>
		</tr>
	<?php 
			}
		} 
	?>
		<tr>
			<td class="left">
				<form action="<?php echo plugin_page('edit_team.php')?>#Customer"
					method="post">
					<input type="hidden" name="action" value="addCustomer"> <input
						type="hidden" name="id" value="<?php echo $agilemantis_team->id?>">
					<select name="customer">
						<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
					<?php
					$team_user = $agilemantis_au->getAgileUser();
					foreach( $team_user AS $num => $row ) {
					?>
					<option value="<?php echo $row['id']?>"><?php 
					  echo empty( $row['realname'] ) ? $row['username'] : $row['realname'] ?></option>
					<?php
					}
					?>
				</select> <input type="submit" name="addCustomer"
						value="<?php echo plugin_lang_get( 'button_add' )?>">
				</form>
			</td>
		</tr>
	</table>
</div>
<?php }?>
<?php html_page_bottom() ?>