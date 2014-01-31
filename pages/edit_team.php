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
	
	html_page_top(plugin_lang_get( 'edit_teams_title' )); 
	
	if($_POST['back_button']){
		header($sprint->forwardReturnToPage("teams.php"));
	} else {

		# try to change the product
		if($_POST['product_owner'] == 0 && isset($_POST['product_owner']) && $_POST['id'] > 0 && $team->hasSprints($_POST['id']) > 0){
			$_POST['product_owner'] = $_POST['old_product_owner'];
			$system = plugin_lang_get( 'edit_teams_error_100200' );
		}

		# delete product owner
		if($_POST['product_owner'] == 0 && isset($_POST['product_owner']) && $system == ""){
			$user_id = (int) $_POST['product_owner'];
			$team_id = $_POST['id'];
			$team->deleteTeamRoleMember($team_id,1);
		}

		# change product owner
		if($_POST['product_owner'] > 0){
			$user_id = (int) $_POST['product_owner'];
			$team_id = $_POST['id'];
			$team->deleteTeamRoleMember($team_id,1);
			$team->addTeamMember($user_id,$team_id,1);
			$_SESSION['setscrum'] = 1;
			$_SESSION['hasUser'] = 1;
		}

		# try to change the scrum master
		if($_POST['scrum_master'] == 0 && isset($_POST['scrum_master']) && $_POST['id'] > 0 && $team->hasSprints($_POST['id']) > 0){
			$_POST['scrum_master'] = $_POST['old_scrum_master'];
			$system = plugin_lang_get( 'edit_teams_error_100201' );
		}

		# delete scrum master
		if($_POST['scrum_master'] == 0 && isset($_POST['scrum_master']) && $system == ""){
			$user_id = (int) $_POST['scrum_master'];
			$team_id = $_POST['id'];
			$team->deleteTeamRoleMember($team_id,2);
		}

		# change scrum master
		if($_POST['scrum_master'] > 0){
			$user_id = (int) $_POST['scrum_master'];
			$team_id = $_POST['id'];
			$team->deleteTeamRoleMember($team_id,2);
			$team->addTeamMember($user_id,$team_id,2);
			$_SESSION['setscrum'] = 1;
			$_SESSION['hasUser'] = 1;
		}

		# add developer
		if($_POST['developer'] > 0){
			$user_id = (int) $_POST['developer'];
			$team_id = $_POST['id'];
			$team->addTeamMember($user_id,$team_id ,3);
			$_SESSION['hasUser'] = 1;
		}

		# add customer
		if($_POST['customer'] > 0){
			$user_id = (int) $_POST['customer'];
			$team_id = $_POST['id'];
			$team->addTeamMember($user_id,$team_id ,4);
			$_SESSION['hasUser'] = 1;
		}

		# add user 
		if($_POST['user'] > 0){
			$user_id = (int) $_POST['user'];
			$team_id = $_POST['id'];
			$team->addTeamMember($user_id,$team_id ,5);
			$_SESSION['hasUser'] = 1;
		}

		# add manager
		if($_POST['manager'] > 0){
			$user_id = (int) $_POST['manager'];
			$team_id = $_POST['id'];
			$team->addTeamMember($user_id,$team_id ,6);
			$_SESSION['hasUser'] = 1;
		}

		# edit team information
		if($_POST['action'] == "edit"){

			$team->id = (int) $_POST['id'];
			$team->name = $_POST['t_name'];

			if(empty($team->name)){
					$system = plugin_lang_get( 'edit_teams_error_922200' );
					if($_POST['old_team_name'] != ""){
						$_POST['t_name'] = $_POST['old_team_name'];
					}
			} else {
				if($team->isTeamNameUnique()==false){
					$system = plugin_lang_get( 'edit_teams_error_982200' );
				} elseif($system == "") {
					if((int)$_POST['product_backlogs'] > 0){
						$team->description = $_POST['t_description'];
						$team->product_backlog = $_POST['product_backlogs'];
						$team->id = (int) $_POST['id'];
						$p_username = $team->generateTeamUser($team->product_backlog);
						if($p_username){
							$team->insertProductBacklogTeamMember($team->id,$p_username);
						}
						$team->editTeam();
					} else {
						$system = plugin_lang_get( 'edit_teams_error_923200' );
					}
					if($_SESSION['hasUser'] == 1 && (int) $team->id > 0 && $_POST['product_backlogs'] > 0 && $_POST['user'] == 0 && $_POST['manager'] == 0 && $_POST['customer'] == 0 && $_POST['developer'] == 0 && $_POST['old_product_owner'] == $_POST['product_owner'] && $_POST['old_scrum_master'] == $_POST['scrum_master']){
						$_SESSION['hasUser'] = 0;
						header("Location: ".plugin_page('teams.php'));
					}
				}
			}
		}
		
		# delete one team member
		if($_POST['deleteTeamMember'] != "" && (int) $_POST['id'] > 0 && (int) $_POST['user_id'] > 0 && (int) $_POST['role_id'] > 0){
			$team->deleteSelectedTeamMember($_POST['id'],$_POST['user_id'],$_POST['role_id']);
		}

		if($_POST['edit']){
			$team->id = (int) implode(',',array_flip($_POST['edit']));
		}

		if((int) $_POST['id'] > 0){
			$team->id = (int) $_POST['id'];
		}

		if((int)$team->id > 0 ) {
			$t = $team->getSelectedTeam();
		}
	}
?>
<br>
<?php include(PLUGIN_URI.'/pages/footer_menu.php');?>
<br>
<?php if($system){?>
	<br>
	<center><span style="color:red; font-size:16px; font-weight:bold;"><?php echo $system?></span></center>
	<br>
<?php }?>
<form action="<?php echo plugin_page('edit_team.php') ?>" method="post">
<input type="hidden" name="action" value="edit">
<input type="hidden" name="id" value="<?php echo $team->id?>">
	<table align="center" class="width75" cellspacing="1">
	<tr>
		<td class="form-title" colspan="3">
			<?php echo plugin_lang_get( 'edit_teams_title' )?>
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category" width="30%">
			*Name
		</td>
		<td class="left" width="70%">
			<input type="text" size="105" maxlength="128" name="t_name" value="<?php if($t[0]['name']){?><?php echo $t[0]['name']?><?php } else {?><?php echo $_POST['t_name']?><?php }?>">
			<input type="hidden" name="old_team_name" value="<?php echo $t[0]['name']?>">
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo plugin_lang_get( 'common_description' )?>
		</td>
		<td class="left">
			<textarea name="t_description" cols="80" rows="10"><?php if($t[0]['description']){?><?php echo $t[0]['description']?><?php } else {?><?php echo $_POST['t_description']?><?php }?></textarea>
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			 *Product Backlog
		</td>
		<td class="left">
			<?php 
				if($team->hasSprints($team->id) > 0 && $team->id > 0){
					$disable_product_backlog = 'disabled';
				} else {
					$disable_product_backlog = '';
				}
			?>
			<?php if($team->hasSprints($team->id) > 0){?>
				<input type="hidden" name="product_backlogs" value="<?php echo $t[0]['pb_id']?>">
			<?php }?>
			<select name="product_backlogs" <?php echo $disable_product_backlog?>>
				<option><?php echo plugin_lang_get( 'common_chose' )?></option>
				<?php 
					$data = $team->getProductBacklogs();
					foreach($data AS $num => $row){
				?>
					<option value="<?php echo $row['id']?>" <?php if($t[0]['pb_id']==$row['id']){echo 'selected';$name=$row['name'];}?>><?php echo $row['name']?></option>
				<?php }?>
			</select>
		</td>
	</tr> 
	<tr>
		<td>
			<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
		</td>
		<td class="center">
			<input type="submit" class="button" value="<?php echo plugin_lang_get( 'button_save' )?>">
			<input type="submit" name="back_button" value="<?php echo plugin_lang_get( 'button_back' )?>">
		</td>
	</tr>
	</table>
</form>
<?php if($team->id > 0){?>
<br>
	<?php if($team->hasSprints($team->id) > 0){?>
		<input type="hidden" name="product_backlogs" value="<?php echo $t[0]['pb_id']?>">
	<?php }?>
	<table align="center" class="width75" cellspacing="1">
	<tr <?php echo helper_alternate_class() ?>>
		<tr>
			<td class="form-title" colspan="2">
				Team User
			</td>
		</tr>
		<td class="left">
			<?php echo $team->getTeamUserByBacklogName($name)?>
		</td>
	</tr>
	</table>
	<br>
	<table align="center" class="width75" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				Product Owner
			</td>
		</tr>
		<tr>
			<td class="left">
				<form action="<?php echo plugin_page('edit_team.php') ?>" method="post">
					<input type="hidden" name="action" value="addTeamMember">
					<input type="hidden" name="id" value="<?php echo $team->id?>">
					<input type="hidden" name="old_product_owner" value="<?php echo $opoid?>">
					<select name="product_owner">
						<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
						<?php $team_user = $au->getAgileUser();foreach($team_user AS $num => $row){ ?>
						<option value="<?php echo $row['id']?>" <?php if($team->getTeamProductOwner() == $row['id']){ echo 'selected'; $opoid = $row['id'];}?>><?php echo $row['realname']?></option>
						<?php }?>
					</select>
					<input type="submit" name="addProductOwner" value="<?php echo plugin_lang_get( 'button_change' )?>">
				</form>
			</td>
		</tr>
	</table>
	<br>
	<table align="center" class="width75" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				Scrum Master
			</td>
		</tr>
		<tr>
			<td class="left">
			<form action="<?php echo plugin_page('edit_team.php') ?>" method="post">
				<input type="hidden" name="action" value="addTeamMember">
				<input type="hidden" name="id" value="<?php echo $team->id?>">
				<input type="hidden" name="old_scrum_master" value="<?php echo $oscid?>">
				<select name="scrum_master">
					<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
					<?php $team_user = $au->getAgileUser();foreach($team_user AS $num => $row){?>
					<option value="<?php echo $row['id']?>" <?php if($team->getTeamScrumMaster() == $row['id']){ echo 'selected';$oscid = $row['id'];}?>><?php echo $row['realname']?></option>
					<?php }?>
				</select>
				<input type="submit" name="addProductOwner" value="<?php echo plugin_lang_get( 'button_change' )?>">
			</form>
			</td>
		</tr>
	</table>
	<br>
	<table align="center" class="width75" cellspacing="1">
		<tr <?php echo helper_alternate_class() ?>>
			<td class="form-title" colspan="4">
				<?php echo plugin_lang_get( 'edit_team_developer' )?>
			</td>
		</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">Name</td>
		<td class="category"><?php echo plugin_lang_get( 'edit_team_username' )?></td>
		<td class="category">Email</td>
		<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
	</tr>
	<?php
		$tm = $team->getTeamDeveloper();
		if(!empty($tm)){
			foreach($tm AS $num => $row){
	?>
		<tr <?php echo helper_alternate_class() ?>>
			<td><?php echo $row['realname']?></td>
			<td><?php echo $row['username']?></td>
			<td><?php echo $row['email']?></td>
			<td>
				<?php
				# if the team is not working on a sprint, all team members can be deleted
				if($team->hasSprints($team->id) > 0 ){
					$do_not_delete_last_member = true;
					if($do_not_delete_last_member){
						if((count($tm)-1) > 0){
							$do_not_delete_last_member = false;
						} else {
							$do_not_delete_last_member = true;
						}
					}
				}
				
				# team members with an open task cannot be deleted
				if($team->memberHasOpenTasks($team->id,$row['id']) == false){
					if($do_not_delete_last_member == false){
				?>
					<form action="<?php echo plugin_page('delete_team_member.php') ?>" method="post">
						<input type="hidden" name="action" value="deleteTeamMember">
						<input type="hidden" name="team_id" value="<?php echo $row['team_id'] ?>">
						<input type="hidden" name="user_id" value="<?php echo $row['user_id'] ?>">
						<input type="hidden" name="role_id" value="3">
						<input type="submit" name="deleteTeamMember" value="<?php echo plugin_lang_get( 'button_remove' )?>">
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
			<form action="<?php echo plugin_page('edit_team.php') ?>" method="post">
				<input type="hidden" name="action" value="addDeveloper">
				<input type="hidden" name="id" value="<?php echo $team->id?>">
				<select name="developer">
					<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
					<?php 
					$team_user = $au->getAgileUser(true);
					foreach($team_user AS $num => $row){
						if($row['developer'] == 1){
					?>
					<option value="<?php echo $row['id']?>"><?php echo $row['realname']?></option>
					<?php
						}
					}
					?>
				</select>
				<input type="submit" name="addDeveloper" value="<?php echo plugin_lang_get( 'button_add' )?>">
			</form>
			</td>
		</tr>
	</table>
	<br>
	<table align="center" class="width75" cellspacing="1">
		<tr <?php echo helper_alternate_class() ?>>
			<td class="form-title" colspan="4">
				<?php echo plugin_lang_get( 'edit_team_product_user' )?>
			</td>
		</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">Name</td>
		<td class="category"><?php echo plugin_lang_get( 'edit_team_username' )?></td>
		<td class="category">Email</td>
		<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
	</tr>
	<?php
		$productUser = $team->getTeamProductUser();
		if(!empty($productUser)){
			foreach($productUser AS $num => $row){
	?>
		<tr <?php echo helper_alternate_class() ?>>
			<td><?php echo $row['realname']?></td>
			<td><?php echo $row['username']?></td>
			<td><?php echo $row['email']?></td>
			<td>
				<form action="<?php echo plugin_page('delete_team_member.php') ?>" method="post">
					<input type="hidden" name="action" value="deleteTeamMember">
					<input type="hidden" name="team_id" value="<?php echo $row['team_id'] ?>">
					<input type="hidden" name="user_id" value="<?php echo $row['user_id'] ?>">
					<input type="hidden" name="role_id" value="5">
					<input type="submit" name="deleteTeamMember" value="<?php echo plugin_lang_get( 'button_remove' )?>">
				</form>
			</td>
		</tr>
	<?php 
			}
		} 
	?>
		<tr>
			<td class="left">
			<form action="<?php echo plugin_page('edit_team.php') ?>" method="post">
				<input type="hidden" name="action" value="addProductUser">
				<input type="hidden" name="id" value="<?php echo $team->id?>">
				<select name="user">
					<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
					<?php
					$team_user = $au->getAgileUser();
					foreach($team_user AS $num => $row){
					?>
					<option value="<?php echo $row['id']?>"><?php echo $row['realname']?></option>
					<?php
					}
					?>
				</select>
				<input type="submit" name="addProductUser" value="<?php echo plugin_lang_get( 'button_add' )?>">
			</form>
			</td>
		</tr>
	</table>
	<br>
	<table align="center" class="width75" cellspacing="1">
		<tr <?php echo helper_alternate_class() ?>>
			<td class="form-title" colspan="4">
				Manager
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
		<td class="category">Name</td>
		<td class="category"><?php echo plugin_lang_get( 'edit_team_username' )?></td>
		<td class="category">Email</td>
		<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
	</tr>
	<?php
		$man = $team->getTeamManager();
		if(!empty($man)){
			foreach($man AS $num => $row){
	?>
		<tr <?php echo helper_alternate_class() ?>>
			<td><?php echo $row['realname']?></td>
			<td><?php echo $row['username']?></td>
			<td><?php echo $row['email']?></td>
			<td>
				<form action="<?php echo plugin_page('delete_team_member.php') ?>" method="post">
					<input type="hidden" name="action" value="deleteTeamMember">
					<input type="hidden" name="team_id" value="<?php echo $row['team_id'] ?>">
					<input type="hidden" name="user_id" value="<?php echo $row['user_id'] ?>">
					<input type="hidden" name="role_id" value="6">
					<input type="submit" name="deleteTeamMember" value="<?php echo plugin_lang_get( 'button_remove' )?>">
				</form>
			</td>
		</tr>
	<?php 
			}
		} 
	?>
		<tr>
			<td class="left">
			<form action="<?php echo plugin_page('edit_team.php') ?>#customer" method="post">
				<input type="hidden" name="action" value="addManager">
				<input type="hidden" name="id" value="<?php echo $team->id?>">
				<select name="manager">
					<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
					<?php
					$team_user = $au->getAgileUser();
					foreach($team_user AS $num => $row){
					?>
					<option value="<?php echo $row['id']?>"><?php echo $row['realname']?></option>
					<?php
					}
					?>
				</select>
				<input type="submit" name="addManager" value="<?php echo plugin_lang_get( 'button_add' )?>">
			</form>
			</td>
		</tr>
	</table>
	<br>
		<table align="center" class="width75" cellspacing="1">
		<tr <?php echo helper_alternate_class() ?>>
			<td class="form-title" colspan="4">
				<a name="customer"><?php echo plugin_lang_get( 'edit_team_customer' )?></a>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
		<td class="category">Name</td>
		<td class="category"><?php echo plugin_lang_get( 'edit_team_username' )?></td>
		<td class="category">Email</td>
		<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
	</tr>
	<?php
		$tc = $team->getTeamCustomer();
		if(!empty($tc)){
			foreach($tc AS $num => $row){
	?>
		<tr <?php echo helper_alternate_class() ?>>
			<td><?php echo $row['realname']?></td>
			<td><?php echo $row['username']?></td>
			<td><?php echo $row['email']?></td>
			<td>
				<form action="<?php echo plugin_page('delete_team_member.php') ?>" method="post">
					<input type="hidden" name="action" value="deleteTeamMember">
					<input type="hidden" name="team_id" value="<?php echo $row['team_id'] ?>">
					<input type="hidden" name="user_id" value="<?php echo $row['user_id'] ?>">
					<input type="hidden" name="role_id" value="4">
					<input type="submit" name="deleteTeamMember" value="<?php echo plugin_lang_get( 'button_remove' )?>">
				</form>
			</td>
		</tr>
	<?php 
			}
		} 
	?>
		<tr>
			<td class="left">
			<form action="<?php echo plugin_page('edit_team.php')?>#customer" method="post">
				<input type="hidden" name="action" value="addCustomer">
				<input type="hidden" name="id" value="<?php echo $team->id?>">
				<select name="customer">
					<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
					<?php
					$team_user = $au->getAgileUser();
					foreach($team_user AS $num => $row){
					?>
					<option value="<?php echo $row['id']?>"><?php echo $row['realname']?></option>
					<?php
					}
					?>
				</select>
				<input type="submit" name="addCustomer" value="<?php echo plugin_lang_get( 'button_add' )?>">
			</form>
			</td>
		</tr>
	</table>
	<br>
<?php }?>
<?php html_page_bottom() ?>