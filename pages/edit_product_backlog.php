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


	
	html_page_top(plugin_lang_get( 'edit_product_backlog_title' )); 
?>
<br>
<?php include(AGILEMANTIS_PLUGIN_URI.'/pages/footer_menu.php');?>
<br>
<?php
	
	# back button actions

	// Redirect to backlog list view if invalid or back_button
	if( empty($_POST) || $_POST['back_button'] ) {
		$fromPage = 'product_backlogs.php';
		if( $_POST['pageFrom'] ) {
			$fromPage = $_POST['pageFrom'];
		}
		header("Location: ".plugin_page($fromPage));
	} else {
		
		# get product backlog id
	if( $_POST['edit'] ) {
		$agilemantis_pb->id = implode( '', array_flip( $_POST['edit'] ) );
	} else {
		$agilemantis_pb->id = $_POST['id'];
	}
	
	if( $_GET['pbid'] > 0 ) {
		$agilemantis_pb->id = ( int ) $_GET['pbid'];
	}
		
		# add product backlog to a project and add custom fields
	if( $_POST['project_id'] > 0 
			&& $_POST['deleteProject'] == "" 
			&& $_POST['backProductBacklog'] == "" ) {
		
		$t_project_id = ( int ) $_POST['project_id'];
		
		if( $_POST['project_id'] > 0 ) {
			$t_default = null;
			$t_user_id = NO_USER;
			$t_view_issues_page_columns = config_get( 'view_issues_page_columns', 
									$t_default, $t_user_id, $t_src_project_id );
			
			// Add Product Backlog Custom Field If Custom Field Is Not In Array
			$key = array_search( 'custom_productbacklog', $t_view_issues_page_columns );
			if( $key == NULL ) {
				$t_view_issues_page_columns[] = 'custom_productbacklog';
			}
			
			// Add Sprint Custom Field If Custom Field Is Not In Array
			$key = array_search( 'custom_sprint', $t_view_issues_page_columns );
			if( $key == NULL ) {
				$t_view_issues_page_columns[] = 'custom_sprint';
			}
			
			config_set( 'view_issues_page_columns', 
						$t_view_issues_page_columns, 
						$t_user_id, $t_project_id );
			
			$agilemantis_project->addAdditionalProjectFields( $t_project_id );
			$agilemantis_pb->editProjects( $agilemantis_pb->id, $t_project_id );
		}
	}
	
	if( $t_project_id > 0 ) {
		
		if( $agilemantis_project->backlog_project_is_unique( $t_project_id ) == false ) {
			$system = plugin_lang_get( 'edit_product_backlog_error_100600' );
		} else {
			$system = "";
		}
		
		// give access_level 'reporter (25)' to team-user
		$result = $agilemantis_pb->getTeamUserId( $agilemantis_pb->id );
		if( $result > -1 ) {
			$user_id_team_user = $result;
			$agilemantis_pb->giveDeveloperRightsToTeamUser( $user_id_team_user, $t_project_id );
		}
		
		// warning message if there are users without access rights
		$result = $agilemantis_project->get_user_with_no_accessrights( $agilemantis_pb->id, $t_project_id );
														
		if( count( $result ) > 0 ) {
			$names = $result[0]["realname"];
			for( $i = 1; $i < count( $result ); $i++ ) {
				$names .= ", " . $result[$i]["realname"];
			}
			$msg = plugin_lang_get( 'edit_product_backlog_error_100602' );
			$msg = str_replace( "[names]", $names, $msg );
			if( $system != "" ) {
				$system .= "<br>";
			}
			$system .= $msg;
		}
	}
	
	if( $_POST['action'] == "edit" ) {
		
		$agilemantis_pb->id = $_POST['id'];
		$agilemantis_pb->name = $_POST['pbl_name'];
		$pb_name_old = $_POST['pbl_name_old'];
		$agilemantis_pb->email = $_POST['pbl_email'];
		$agilemantis_pb->user_id = $_POST['pbl_user_id'];
		$agilemantis_pb->description = $_POST['pbl_description'];
		
		if( empty( $agilemantis_pb->name ) ) {
			$system = plugin_lang_get( 'edit_product_backlog_error_922600' );
		} else {
			if( empty( $_POST['pbl_email'] ) || email_is_valid( $agilemantis_pb->email ) == false ) {
				$system = plugin_lang_get( 'edit_product_backlog_error_923600' );
			} else {
				$isNewPBOk = !$_POST['id'] 
							&& $agilemantis_pb->isNameUnique(); // New PB with unique name?
				
				$isExistingPbOk = $_POST['id'] > 0 & 	// Existing PB?
					( ( $agilemantis_pb->name != $pb_name_old && $agilemantis_pb->isNameUnique() ) 
					|| ( $agilemantis_pb->name == $pb_name_old ) ); // PB name didn't change, Ok!
				

				if( $isNewPBOk || $isExistingPbOk ) {			
					if ( ! $agilemantis_pb->editProductBacklog() ) {
						$system = plugin_lang_get( 'edit_product_backlog_error_982601' );
					} else {
						
						if( $_POST['pbl_email'] != $_POST['pbl_email_old'] ){
							$t_team_user_id = $agilemantis_pb->getTeamUserId( $agilemantis_pb->id );
							user_set_field( $t_team_user_id, 'email',  $_POST['pbl_email'] );
						}
						
						$agilemantis_pb->updatePBCustomFieldStrings( 
														$pb_name_old, $agilemantis_pb->name );
						
						if( $_POST['project_id'] == 0 && $_POST['id'] > 0 ) {
							$fromPage = 'product_backlogs.php';
							if( $_POST['pageFrom'] ) {
								$fromPage = $_POST['pageFrom'];
							}
							header("Location: ".plugin_page($fromPage));
						}
					}
				} else {
					$system = plugin_lang_get( 'edit_product_backlog_error_982600' );
				}
			}
		}
	}
	
	if( $agilemantis_pb->id > 0 ) {
		$pbData = $agilemantis_pb->getSelectedProductBacklog();
	}
	
	# delete project from a product backlog
	if( $_POST['deleteProject'] != "" ) {
		$project_id = ( int ) $_POST['project_id'];
		$backlog_id = ( int ) $_POST['id'];
		$agilemantis_pb->getAdditionalProjectFields();
		if( $_POST['delete_user_stories'] ) {
			$userstories = $agilemantis_pb->getUserStoriesByProject( $project_id, $pbData[0]['name'] );
			if( !empty( $userstories ) ) {
				foreach( $userstories as $num => $row ) {
					$sprint = $agilemantis_pb->userStoriesInClosedSprints( $row['id'] );
					if( empty( $sprint ) ) {
						$agilemantis_pb->restoreCustomFieldValue( $row['id'], $agilemantis_pb->pb, '' );
						$agilemantis_pb->restoreCustomFieldValue( $row['id'], $agilemantis_pb->spr, '' );
						$agilemantis_pb->updateTrackerHandler( $row['id'], 0 );
						$agilemantis_pb->setTrackerStatus( $row['id'], 10 );
					}
				}
			}
		}
		
		# check if project is not in another product backlog
		if( $agilemantis_project->backlog_project_is_unique( $project_id ) == true ) {
			$agilemantis_project->deleteAdditionalProjectFields( $project_id );
		}
		$agilemantis_project->deleteProject( $backlog_id, $project_id );
		
		if( $_POST['delete_user_stories'] ) {
			$system .= plugin_lang_get( 'edit_product_backlog_error_100601' );
		}
	}
	
	if( $agilemantis_pb->id > 0 ) {
		$pbData = $agilemantis_pb->getSelectedProductBacklog();
	}
}
?>
	<?php if( $system ) {?>
<br>
<center>
	<span class="message_error"><?php echo $system?></span>
</center>
<br>
<?php }?>
<form action="<?php echo plugin_page('edit_product_backlog.php') ?>"
	method="post">
	<input type="hidden" name="action" value="edit"> <input type="hidden"
		name="id" value="<?php echo $agilemantis_pb->id?>"> <input
		type="hidden" name="pbl_name_old"
		value="<?php echo $pbData[0]['name']?>"> <input type="hidden"
		name="pbl_user_id" value="<?php echo $pbData[0]['user_id']?>">
	<div class="table-container">
		<table align="center" class="width75" cellspacing="1">
			<tr>
				<td class="form-title" colspan="3">
				<?php echo plugin_lang_get( 'edit_product_backlog_title' )?>
			</td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category" width="30%">*Name</td>
				<?php  
				if($_POST['pbl_name'] ) { 
					$t_value = $_POST['pbl_name']; 
				} else { 
					$t_value = $pbData[0]['name'];
				}
				?>
				<td class="left" width="70%"><input type="text" size="105"
					maxlength="128" name="pbl_name"
					value="<?php echo $t_value ?>">
				</td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category">
				<?php echo plugin_lang_get( 'common_description' )?>
			</td>
				<?php 
				if( $_POST['pbl_description'] ) {
					$t_descr = $_POST['pbl_description'];
				} else {
					$t_descr = $pbData[0]['description'];
				}
				?>
			
				<td class="left"><textarea name="pbl_description" cols="80"
						rows="10"><?php echo $t_descr ?></textarea>
				</td>
			</tr>

			<tr <?php echo helper_alternate_class() ?>>
				<td class="category">
				*<?php echo plugin_lang_get( 'edit_product_backlog_user_email' )?>
				
				<a class="version_tooltip" href="javascript: void(0)" style="border-bottom: 0;">
					<img src="<?php echo AGILEMANTIS_PLUGIN_URL?>images/info-icon.png" height="16" width="16">
					<span style="font-weight: normal; width: 500px; left: 25px;">
						<?php echo plugin_lang_get( 'edit_product_backlog_team_user_info' )?>
					</span>
				</a>
			</td>
				<td class="left">
				<?php
					$t_email = "";
					if($_POST['pbl_email']){
						$t_email = $_POST['pbl_email'];
					} else if (!empty($pbData[0]['user_id'])) {
						$t_email = $agilemantis_pb->getUserEmail($pbData[0]['user_id']);
					}
				?>
				<input type="hidden" name="pbl_email_old" value="<?php echo $t_email?>">
				<input type="text" size="105" maxlength="128" name="pbl_email" value="<?php echo $t_email?>">	
				</td>
			</tr>
			<tr>
				<td><span class="required"> * <?php echo lang_get( 'required' ) ?></span>
				</td>
				<td class="center">
					<input type="submit" class="button" value="<?php echo plugin_lang_get( 'button_save' )?>" /> 
					<input type="submit" name="back_button" value="<?php echo plugin_lang_get( 'button_back' )?>" />
					<input type="hidden" name="pageFrom" value="<?php echo $_POST['pageFrom'] ?>" />
				</td>
			</tr>
			</form>
		</table>
	</div>
	
	<?php if($agilemantis_pb->id > 0){?>
	<br>
	<div class="table-container">
		<table align="center" class="width75" cellspacing="1">
			<tr>
				<td class="form-title" colspan="3">
				<?php echo plugin_lang_get( 'edit_product_backlog_projects' )?>
			</td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category"><?php echo plugin_lang_get( 'edit_product_backlog_projects' )?></td>
				<td class="category"><?php echo plugin_lang_get( 'common_actions' )?></td>
			</tr>
		<?php 
			# get all product backlog projects
			$backlog_projects = $agilemantis_pb->getBacklogProjects($agilemantis_pb->id);
			if(!empty($backlog_projects)){
			foreach($backlog_projects AS $key => $value){
		?>
		<tr <?php echo helper_alternate_class() ?>>
				<td><?php echo $value['name']?></td>
				<td>
				<?php
					# check if project can be deleted from product backlog
				$no_userstories = true;
				$delete_with_warning = false;
				
				$userstories = $agilemantis_pb->getUserStoriesByProject( 
														$value['id'], $pbData[0]['name'], 80 );
				if( !empty( $userstories ) ) {
					foreach( $userstories as $num => $row ) {
						$sprint = $agilemantis_pb->userStoriesInRunningSprints( $row['id'] );
						if( empty( $sprint ) ) {
							$delete_with_warning = true;
						}
						
						if( $sprint[0]['status'] == 0 ) {
							$delete_with_warning = true;
						}
						
						if( $sprint[0]['status'] == 1 ) {
							$no_userstories = false;
							$delete_with_warning = false;
						}
						
						if( $sprint[0]['status'] == 2 ) {
							$delete_with_warning = false;
						}
					}
				}
				
				if( $no_userstories ) {
				?>
				<form action="<?php echo plugin_page('delete_project.php') ?>"
						method="post">
						<input type="hidden" name="product_backlog_id"
							value="<?php echo $agilemantis_pb->id?>"> <input type="hidden"
							name="project_id" value="<?php echo $value['id']?>"> <input
							type="hidden" name="delete_with_warning"
							value="<?php echo $delete_with_warning?>"> <input type="submit"
							name="deleteProject"
							value="<?php echo plugin_lang_get( 'button_remove' )?>">
					</form>
				<?php } ?>
			</td>
			</tr>
			<?php }?>
		<?php }?>
		<tr>
				<td class="left" colspan="2">
				<?php $projects = $agilemantis_project->getProjectWithHierachy();?>
				<?php
			# get all projects
				function readRecursivly( $projects, $array_depth = "" ) {
					if( $array_depth == '' ) {
						$array_depth = 0;
					}
					if( !empty( $projects[$array_depth] ) ) {
						foreach( $projects[$array_depth] as $num => $row ) {
							if( $array_depth > 0 ) {
								$addSeperator = '» ';
							}
							echo '<option value="' . $num . '">' . $addSeperator . $row . '</option>';
							readRecursivly( $projects, $num );
						}
					}
				}	
				?>
				<form action="<?php echo plugin_page('edit_product_backlog.php') ?>"
						method="post">
						<input type="hidden" name="id"
							value="<?php echo $agilemantis_pb->id?>"> <select
							name="project_id">
							<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
						<?php readRecursivly($projects,0); ?>
					</select> <input type="submit" name="addProjekt"
							value="<?php echo plugin_lang_get( 'edit_product_backlog_add_project' )?>">
					</form>
				</td>
			</tr>
		</table>
	</div>
	<?php }?>
<?php html_page_bottom() ?>