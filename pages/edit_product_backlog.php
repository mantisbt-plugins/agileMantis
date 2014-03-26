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
	
	html_page_top(plugin_lang_get( 'edit_product_backlog_title' )); 
?>
<br>
<?php include(PLUGIN_URI.'/pages/footer_menu.php');?>
<br>
<?php
	
	# back button actions
	if($_POST['back_button']){
		header("Location: ".plugin_page('product_backlogs.php'));
	} else {
		
		# get product backlog id
		if($_POST['edit']){
			$pb->id = implode('',array_flip($_POST['edit']));
		} else {
			$pb->id = $_POST['id'];
		}
		
		if($_GET['pbid'] > 0){
			$pb->id = (int) $_GET['pbid'];
		}
		
		# add product backlog to a project and add custom fields
		if($_POST['project_id'] > 0 && $_POST['deleteProject'] == "" && $_POST['backProductBacklog'] == ""){
			$t_project_id = (int) $_POST['project_id'];
			if($_POST['project_id'] > 0){
				$t_default = null;
				$t_user_id = NO_USER;
				$t_view_issues_page_columns = config_get( 'view_issues_page_columns', $t_default, $t_user_id, $t_src_project_id );
				
				// Add Product Backlog Custom Field If Custom Field Is Not In Array
				$key = array_search('custom_productbacklog',$t_view_issues_page_columns);
				if($key == NULL){$t_view_issues_page_columns[] = 'custom_productbacklog';}
				
				// Add Sprint Custom Field If Custom Field Is Not In Array
				$key = array_search('custom_sprint',$t_view_issues_page_columns);
				if($key == NULL){$t_view_issues_page_columns[] = 'custom_sprint';}
				
				config_set( 'view_issues_page_columns', $t_view_issues_page_columns, $t_user_id, $t_project_id);
				$project->addAdditionalProjectFields($t_project_id);
				$pb->editProjects($pb->id, $t_project_id);
			}
		}
		
		if($t_project_id > 0){
			if($project->backlog_project_is_unique($t_project_id) == false){
				$system = plugin_lang_get( 'edit_product_backlog_error_100600' );
			} else {
				$system = "";
			}
		}
					
		if($_POST['action']=="edit"){
			
			$pb->id 			= $_POST['id'];
			$pb->name 			= $_POST['pbl_name'];
			$pb->email 			= $_POST['pbl_email'];
			$pb->user_id 		= $_POST['pbl_user_id'];
			$pb->description 	= $_POST['pbl_description'];

			if(empty($pb->name)){
				$system = plugin_lang_get( 'edit_product_backlog_error_922600' );
			} else {
				if(empty($_POST['pbl_email']) || email_is_valid($pb->email) == false){
					$system = plugin_lang_get( 'edit_product_backlog_error_923600' );
				} else {
					if($_POST['id'] > 0 || $pb->isNameUnique()){
						$pb->editProductBacklog();
						
						if($_POST['project_id'] == 0 && $_POST['id'] > 0){
							header("Location: ".plugin_page('product_backlogs.php'));
						}
					} else {
						$system = plugin_lang_get( 'edit_product_backlog_error_982600' );
					}
				}
			}
		}

		if($pb->id > 0 ) {	
			$pbData = $pb->getSelectedProductBacklog();
		}
		
		# delete project from a product backlog
		if($_POST['deleteProject'] != ""){
			$project_id = (int) $_POST['project_id'];
			$backlog_id = (int) $_POST['id'];
			$pb->getAdditionalProjectFields();
			if($_POST['delete_user_stories']){
				$userstories = $pb->getUserStoriesByProject($project_id,$pbData[0]['name']);
				if(!empty($userstories)){
					foreach($userstories AS $num => $row){
						$sprint = $pb->userStoriesInClosedSprints($row['id']);
						if(empty($sprint)){
							$pb->restoreCustomFieldValue($row['id'],$pb->pb,'');
							$pb->restoreCustomFieldValue($row['id'],$pb->spr,'');
							$pb->updateTrackerHandler($row['id'],0);
							$pb->setTrackerStatus($row['id'],10);
						}
					}
				}
			}

			# check if project is not in another product backlog
			if($project->backlog_project_is_unique($project_id) == true){
				$project->deleteAdditionalProjectFields($project_id);
			}
			$project->deleteProject($backlog_id,$project_id);
		
			if($_POST['delete_user_stories']){
				$system .= plugin_lang_get( 'edit_product_backlog_error_100601' );
			}
		}
		
		if($pb->id > 0 ) {	
			$pbData = $pb->getSelectedProductBacklog();
		}
	}
?>
	<?php if($system){?>
		<br>
		<center><span style="color:red; font-size:16px; font-weight:bold;"><?php echo $system?></span></center>
		<br>
	<?php }?>
	<form action="<?php echo plugin_page('edit_product_backlog.php') ?>" method="post">
	<input type="hidden" name="action" value="edit">
	<input type="hidden" name="id" value="<?php echo $pb->id?>">
	<input type="hidden" name="pbl_user_id" value="<?php echo $pbData[0]['user_id']?>">
		<table align="center" class="width75" cellspacing="1">
		<tr>
			<td class="form-title" colspan="3">
				<?php echo plugin_lang_get( 'edit_product_backlog_title' )?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category" width="30%">
				*Name
			</td>
			<td class="left" width="70%">
				<input type="text" size="105" maxlength="128" name="pbl_name" value="<?php if($_POST['pbl_name']){?><?php echo $_POST['pbl_name']?><?php } else {?><?php echo $pbData[0]['name']?><?php }?>">
			</td>
		</tr> 
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo plugin_lang_get( 'common_description' )?>
			</td>
			<td class="left">
				<textarea name="pbl_description" cols="80" rows="10"><?php if($_POST['pbl_description']){?><?php echo $_POST['pbl_description']?><?php } else {?><?php echo $pbData[0]['description']?><?php }?></textarea>
			</td>
		</tr>  

		<tr <?php echo helper_alternate_class() ?>>
				<td class="category">
					*<?php echo plugin_lang_get( 'edit_product_backlog_user_email' )?>
				</td>
				<td class="left">
					<input type="text" size="105" maxlength="128" name="pbl_email" value="<?php if($_POST['pbl_email']){?><?php echo $_POST['pbl_email']?><?php } else {?><?php echo $pb->getEmailByUserId($pbData[0]['user_id'])?><?php }?>" <?php if($pbData[0]['id']>0){?>readonly<?php }?>>
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
		</form>
	</table>
	
	<?php if($pb->id > 0){?>
	<br>
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
			$backlog_projects = $pb->getBacklogProjects($pb->id);
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
				
				$userstories = $pb->getUserStoriesByProject($value['id'],$pbData[0]['name'], 80);
				if(!empty($userstories)){
					foreach($userstories AS $num => $row){
						$sprint = $pb->userStoriesInRunningSprints($row['id']);
						if(empty($sprint)){
							$delete_with_warning = true;
						}
						
						if($sprint[0]['status'] == 0){
							$delete_with_warning = true;
						}
						
						if($sprint[0]['status'] == 1){
							$no_userstories = false;
							$delete_with_warning = false;
						}
						
						if($sprint[0]['status'] == 2){
							$delete_with_warning = false;
						}
					}
				}
				
				if($no_userstories){
				?>
				<form action="<?php echo plugin_page('delete_project.php') ?>" method="post">
					<input type="hidden" name="product_backlog_id" value="<?php echo $pb->id?>">
					<input type="hidden" name="project_id" value="<?php echo $value['id']?>">
					<input type="hidden" name="delete_with_warning" value="<?php echo $delete_with_warning?>">
					<input type="submit" name="deleteProject" value="<?php echo plugin_lang_get( 'button_remove' )?>">
				</form>
				<?php } ?>
			</td>
		</tr>
			<?php }?>
		<?php }?>
		<tr>
			<td class="left" colspan="2">
				<?php $projects = $project->getProjectWithHierachy();?>
				<?php
				# get all projects
				function readRecursivly($projects,$array_depth = ""){
					if($array_depth == ''){$array_depth = 0;}
					if(!empty($projects[$array_depth])){
						foreach($projects[$array_depth] AS $num => $row){
							if($array_depth > 0){$addSeperator = '» ';}
							echo '<option value="'.$num.'">'.$addSeperator . $row.'</option>';
							readRecursivly($projects,$num);
						}
					}
				}	
				?>
				<form action="<?php echo plugin_page('edit_product_backlog.php') ?>" method="post">
					<input type="hidden" name="id" value="<?php echo $pb->id?>">
					<select name="project_id">
						<option value="0"><?php echo plugin_lang_get( 'common_chose' )?></option>
						<?php readRecursivly($projects,0); ?>
					</select>
					<input type="submit" name="addProjekt" value="<?php echo plugin_lang_get( 'edit_product_backlog_add_project' )?>">
				</form>
			</td>
		</tr> 
	</table>
	<?php }?>
<?php html_page_bottom() ?>