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

# agileMantis plugin class
class agileMantisPlugin extends MantisPlugin {

	# agileMantis plugin register information
	function register() {
		$this->name = "agileMantis";
		$this->description = "Enables the Scrum Framework to your Mantis-Installation";
		$this->page = "info";
		$this->version = "1.3.0";
		$this->requires = array("MantisCore" => "1.2.5");
		$this->author = "gadiv GmbH";
		$this->contact = "agileMantis@gadiv.de";
		$this->url = "http://www.gadiv.de";
	}

	# agileMantis init method
	function init() {
	}

	# agileMantis config method
	function config() {
		return array(
		);
	}

	# agileMantis installation
	function install(){
		# fetch config file
		$t_path = config_get_global('plugin_path'). plugin_get_current() . DIRECTORY_SEPARATOR;
		$filename = BASE_PATH . DIRECTORY_SEPARATOR . "config_inc.php";

		#load installation file
		include_once($t_path .'install.php');
	}

	# agileMantis uninstallation
	function uninstall(){
		$t_path = config_get_global('plugin_path'). plugin_get_current() . DIRECTORY_SEPARATOR;
		include_once($t_path .'uninstall.php');
	}

	# agileMantis custom events
	function events (){
		return array (
			'EVENT_LOAD_TASKBOARD' => EVENT_TYPE_EXECUTE,
			'EVENT_LOAD_STATISTICS' => EVENT_TYPE_EXECUTE,
			'EVENT_LOAD_USERSTORY' => EVENT_TYPE_EXECUTE,
			'EVENT_LOAD_SETTINGS'	=> EVENT_TYPE_EXECUTE,
			'EVENT_LOAD_THIRDPARTY' => EVENT_TYPE_EXECUTE
		);
	}

	# add hooks to agileMantis plugin
	function hooks() {
		return array(
			'EVENT_MENU_MAIN' 					=> 	"addStructure",				// CHAIN
			'EVENT_CORE_READY' 					=> 	"event_core_ready",				// CHAIN
			'EVENT_LAYOUT_CONTENT_BEGIN' 		=> 	"addPageAction",			// EXECUTE
			"EVENT_REPORT_BUG_FORM" 			=> 	"event_report_bug_form",	// EXECUTE
			"EVENT_UPDATE_BUG_FORM" 			=> 	"event_update_bug_form",	// EXECUTE
			"EVENT_VIEW_BUG_DETAILS" 			=>	"event_view_bug_details",	// EXECUTE
			"EVENT_UPDATE_BUG" 					=> 	"event_update_bug",			// CHAIN
			"EVENT_REPORT_BUG" 					=> 	"event_report_bug",			// CHAIN
			"EVENT_BUG_ACTION" 					=> 	"event_bug_action",			// CHAIN
			"EVENT_LAYOUT_RESOURCES"			=>	"event_layout_resources",
			"EVENT_LAYOUT_CONTENT_END"			=>	"event_layout_content_end",
			"EVENT_FILTER_FIELDS"				=>	"event_filter_fields"
		);
	}

	# add agileMantis plugin functions to bug report page
	# - adding agileMantis custom Fields
	# - adding save functions
	function event_report_bug_form($p_event) {
		if($_SESSION['ISMANTISADMIN'] == 1 || $_SESSION['ISMANTISUSER'] == 1){
			include_once(PLUGIN_CLASS_URI.'class_product_backlog.php');
			include_once(PLUGIN_CLASS_URI."class_sprint.php");
			$cpb = new gadiv_product_backlog();
			$cs = new gadiv_sprint();
			$disable_custom_fields = true;
			if($cs->projectHasBacklogs(helper_get_current_project()) == false){
				$disable_custom_fields = false;
			}
			$ppid = helper_get_current_project();
			$pbl = $cpb->getProjectProductBacklogs($ppid);
			$s = $cs->getSprints();
			if($disable_custom_fields == false){
				echo '
					<tr '.helper_alternate_class().'>
						<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">Business Value</td>
						<td colspan="5">
							<input type="text" style="width:655px;" name="businessValue" value="'.$story['businessValue'].'">
						</td>
					</tr>
					';
				if(plugin_config_get('gadiv_ranking_order')=='1' && $cs->customFieldIsInProject("RankingOrder")==true){
					echo '
						<tr '.helper_alternate_class().'>
							<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">'.lang_get( 'RankingOrder' ).'</td>
							<td colspan="5">
								<input type="text" style="width:655px;" name="rankingorder" value="">
							</td>
						</tr>
					';
				}
				echo '
					<tr '.helper_alternate_class().'>
						<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">Story Points</td>
						<td colspan="5">';
						if(plugin_config_get('gadiv_storypoint_mode') == 1){
							echo '<input type="text" style="width:655px;" name="storypoints" value="'.$story['storypoints'].'">';
						} else {
							echo '<select name="storypoints" style="width:660px;">';
							echo '<option value=""></option>';
								$cpb->getFibonacciNumbers($story['storypoints']);
							'</select>';
						}
						echo '</td>
					</tr>
					';
				if(plugin_config_get('gadiv_tracker_planned_costs')=='1' && $cs->customFieldIsInProject("PlannedWork")==true){
					echo '
						<tr '.helper_alternate_class().'>
							<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">'.lang_get( 'PlannedWork' ).' ('.plugin_config_get('gadiv_userstory_unit_mode').')</td>
							<td colspan="5">
								<input type="text" style="width:655px;" name="plannedWork" value="'.$story['plannedWork'].'">
							</td>
						</tr>
					';
				}

				echo '
					<tr '.helper_alternate_class().'>
						<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">Product Backlog</td>
						<td colspan="5">
							<select name="backlog" style="width:660px;" '.$disabled.'>';?>
								<option value=""><?php echo plugin_lang_get( 'view_issue_chose_product_backlog' )?></option>
								<?foreach($pbl AS $num => $row){?>
									<option value="<?php echo $row['name']?>" <?php if($row['name']==$story['name']){echo 'selected';}?>><?php echo $row['name']?></option>
								<?}?>
								<? echo '
							</select>
						</td>
					</tr>
				';
				if(plugin_config_get('gadiv_presentable')=='1' && $cs->customFieldIsInProject("Presentable")==true){
					echo '
						<tr '.helper_alternate_class().'>
							<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">'.lang_get( 'Presentable' ).'</td>
							<td colspan="5">
								<select name="presentable" style="width:660px;">
									<option value="3">'.plugin_lang_get( 'view_issue_non_presentable' ).'</option>
									<option value="1">'.plugin_lang_get( 'view_issue_technical_presentable' ).'</option>
									<option value="2">'.plugin_lang_get( 'view_issue_functional_presentable' ).'</option>
								</select>
							</td>
						</tr>
					';
				}
				if(plugin_config_get('gadiv_technical')=='1' && $cs->customFieldIsInProject("Technical")==true){
					echo '
						<tr '.helper_alternate_class().'>
							<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">'.lang_get( 'Technical' ).'</td>
							<td colspan="5">
								<input type="checkbox" style="width:10px;" name="technical" value="1">
							</td>
						</tr>
					';
				}
				if(plugin_config_get('gadiv_release_documentation')=='1' && $cs->customFieldIsInProject("inReleaseDocu")==true){
					echo '
						<tr '.helper_alternate_class().'>
							<td style="background-color:#B3B3CC;font-weight:bold;color:#000;">'.lang_get( 'InReleaseDocu' ).'</td>
							<td colspan="5">
								<input type="checkbox" style="width:10px;" name="inReleaseDocu" value="1">
							</td>
						</tr>
					';
				}
			}
		}
	}

	# add agileMantis plugin functions to update issue page
	# - adding agileMantis custom Fields
	# - including agileMantisCustomFields.php
	function event_update_bug_form($p_event, $project_id) {
		if($_SESSION['ISMANTISADMIN'] == 1 || $_SESSION['ISMANTISUSER'] == 1){
			include_once(PLUGIN_CLASS_URI.'class_product_backlog.php');
			include_once(PLUGIN_CLASS_URI."class_sprint.php");
			$cpb = new gadiv_product_backlog();
			$cs = new gadiv_sprint();

			$cpb->setUserStoryUnit($project_id,plugin_config_get('gadiv_userstory_unit_mode'));
			if($cs->getUserStoryStatus($project_id) < 80){
				$disabled = '';
				$readonly = '';
			} else {
				$disabled = 'disabled';
				$readonly = 'readonly';
			}

			$disable_custom_fields = true;
			if($cs->projectHasBacklogs(helper_get_current_project()) == false){
				$disable_custom_fields = false;
			}
			$pbl = $cpb->getProjectProductBacklogs(helper_get_current_project());
			$story = $cpb->checkForUserStory($project_id);
			bug_update_date($project_id);
			$s = $cs->getBacklogSprints($story['name']);
			if($disable_custom_fields == false){
				include_once(PLUGIN_URI."agileMantisCustomFields.php");
			}
		}
	}

	# add agileMantis plugin functions to view issue page
	# - adding agileMantis custom Fields
	# - adding additonal buttons (Save & Edit Task)
	# - including agileMantisCustomFields.php
	function event_view_bug_details ($p_event , $project_id) {
		if($_SESSION['ISMANTISADMIN'] == 1 || $_SESSION['ISMANTISUSER'] == 1){
			include_once(PLUGIN_CLASS_URI.'class_product_backlog.php');
			include_once(PLUGIN_CLASS_URI."class_sprint.php");
			$cpb = new gadiv_product_backlog();
			$cs = new gadiv_sprint();

			$disable_custom_fields = true;
			if($cs->projectHasBacklogs(helper_get_current_project()) == false){
				$disable_custom_fields = false;
			}

			if($disable_custom_fields == false){
				if($_POST['saveValues']){
					$cpb->setCustomFieldValues($project_id);
					bug_update_date($project_id);

					if((int)$_GET['bug_id']){
						header('Location:'.$_SERVER['PHP_SELF'].'?bug_id='.$project_id.'&save=true');
					} else {
						header('Location:'.$_SERVER['PHP_SELF'].'?id='.$project_id.'&save=true');
					}
					email_generic( $project_id, 'updated', 'email_notification_title_for_action_bug_updated' );
				}

				$pbl = $cpb->getProjectProductBacklogs(helper_get_current_project());
				$story = $cpb->checkForUserStory($project_id);
				$s = $cs->getBacklogSprints($story['name']);

				include_once(PLUGIN_URI."agileMantisCustomFields.php");

				if($_GET['save'] == true){$hinweis = '<span style="color:green; font-size:12px; font-weight:bold;">'.plugin_lang_get( 'view_issue_successfully_saved' ).'</span>';} else {$hinweis = '';};
				if($story['name'] == ""){$task_disable = 'disabled';}
				echo '
					<tr '.helper_alternate_class().'>
						<td style="background-color:#B3B3CC;color:#000;font-weight:bold;">agileMantis-'.plugin_lang_get( 'common_actions' ).'</td>
						<td colspan="5">
							<input type="submit" name="saveValues" value="'.plugin_lang_get( 'view_issue_save_infos' ).'">
							</form>
							<form action="'.plugin_page("task_page.php").'&us_id='.$project_id.'" method="post">
								<input type="submit" value="'.plugin_lang_get( 'view_issue_edit_tasks' ).'" '.$task_disable.'>
							</form>
							'.$hinweis.'
						</td>
					</tr>
				';
			}
		}
	}

	# add agileMantis plugin functions after sending bug data to database when a bug is reported
	# - adding custom field values to mantis and agilemantis tables
	function event_report_bug( $p_bug_event, $p_bug_data){
		if($_SESSION['ISMANTISADMIN'] == 1 || $_SESSION['ISMANTISUSER'] == 1){
			include_once(PLUGIN_CLASS_URI.'/class_product_backlog.php');
			$cpb = new gadiv_product_backlog();
			$bug_id = $p_bug_data->id;

			# set new user story unit
			if(plugin_config_get('gadiv_userstory_unit_mode') == 'keine'){
				$cpb->setUserStoryUnit($bug_id,'');
			} else {
				$cpb->setUserStoryUnit($bug_id,plugin_config_get('gadiv_userstory_unit_mode'));
			}

			# save custom field values in agileMantis tables
			$cpb->setCustomFieldValues($bug_id);

			# do further checks on planned work
			$_POST['plannedWork'] = str_replace(',','.',$_POST['plannedWork']);
			if(is_numeric($_POST['plannedWork'])){$cpb->AddPlannedWork($bug_id,sprintf("%.2f",$_POST['plannedWork']));}
			if(empty($_POST['plannedWork'])){$cpb->AddPlannedWork($bug_id,$_POST['plannedWork']);}

		}
	}

	# add agileMantis plugin functions after sending bug data to database when a bug is updated
	# - adding custom field values to mantis and agilemantis tables
	function event_update_bug($p_bug_event, $p_bug_data, $p_bug_id){
		$request = array_merge($_GET, $_POST);
		if($_SESSION['ISMANTISADMIN'] == 1 || $_SESSION['ISMANTISUSER'] == 1){
			include_once(PLUGIN_CLASS_URI.'class_product_backlog.php');
			$cpb = new gadiv_product_backlog();
			if(isset($_POST['backlog']) || isset($_POST['storypoints']) || isset($_POST['businessValue']) || isset($_POST['rankingorder']) || isset($_POST['technical']) || isset($_POST['presentable']) || isset($_POST['inReleaseDocu']) || isset($_POST['sprint'])){
				$bug_id = $_POST['bug_id'];
				$cpb->setCustomFieldValues($bug_id);

				# change Product Backlog
				if($_POST['old_product_backlog'] != $_POST['backlog'] && $_POST['backlog'] != ""){
					$p_bug_data->handler_id = $_SESSION['tracker_handler'];
					$p_bug_data->status = 50;
				}

				# change back to Team User if no Product Backlog is selected
				if($_POST['old_product_backlog'] != $_POST['backlog'] && $_POST['backlog'] == ""){
					$product_backlog_id = $cpb->get_product_backlog_id($_POST['old_product_backlog']);
					$handler_id = 0;
					if($cpb->count_productbacklog_teams($product_backlog_id) > 0){
						$team_id = $cpb->getTeamIdByBacklog($product_backlog_id);
						$product_owner = $cpb->getProductOwner($team_id);
						$handler_id = $cpb->getUserIdByName($product_owner);
					}
					$p_bug_data->handler_id = $handler_id;
				}
			}
		}
		return $p_bug_data;
	}
	/*
	* Bei jedem Seitenauf innerhalb von Mantis werden spezielle Zugriffvariablen
	* gesetzt und es wird überprüft, ob ein Mantis-Benutzer auch erweiterte Rechte
	* für agileMantis hat.
	*/

	# add additonal agileMantis page action
	# - checks which rights does the current user have
	function addPageAction($p_event) {
		unset($_SESSION['bug']);
		unset($_SESSION['custom_field']);
		unset($_SESSION['custom_field_id']);

		include_once(PLUGIN_CLASS_URI.'class_commonlib.php');
		$commonlib = new gadiv_commonlib();
		$user = $commonlib->getAdditionalUserFields(auth_get_current_user_id());

		# unset buglist cookie
		if(!stristr($_GET['page'],'assume_userstories')){
			setcookie('BugListe', '', time()-6410);
		}

		# set administrator rights for current user
		if($user[0]['administrator'] == 1){
			define("ISMANTISUSER",false);
			define("ISMANTISADMIN",true);
			$_SESSION['ISMANTISADMIN'] = 1;
		}

		# set developer rights for current user
		if($user[0]['developer'] == 1) {
			define("ISMANTISUSER",true);
			define("ISMANTISADMIN",false);
			$_SESSION['ISMANTISUSER'] = 1;
		}

		# set participant rights for current user
		if($user[0]['participant'] == 1) {
			define("ISMANTISUSER",true);
			define("ISMANTISADMIN",false);
			$_SESSION['ISMANTISUSER'] = 1;
		}

		# set administrator rights for current user
		if($user[0]['administrator'] == 1 && $user[0]['developer'] == 1){
			define("ISMANTISUSER",true);
			define("ISMANTISADMIN",true);
			$_SESSION['ISMANTISUSER'] = 1;
			$_SESSION['ISMANTISADMIN'] = 1;
		}

		# set participant rights for current user
		if($user[0]['administrator'] != 1 && $user[0]['participant'] != 1 && $user[0]['developer'] != 1){
			define("ISMANTISADMIN",false);
			define("ISMANTISUSER",false);
			$_SESSION['ISMANTISADMIN'] = 0;
			$_SESSION['ISMANTISUSER'] = 0;
		}

		# additional bug update functionality
		if($_SESSION['event'] == 'EVENT_UPDATE_BUG'){
			$bug_id = $_SESSION['tracker_id'];
			$handler_id = $_SESSION['tracker_handler'];

			if(!empty($bug_id)){
				if((int)$_GET['bug_id']){
					header('Location:'.$_SERVER['PHP_SELF'].'?bug_id='.$bug_id);
				} else {
					header('Location:'.$_SERVER['PHP_SELF'].'?id='.$bug_id);
				}
			}
		}

		unset($_SESSION['event']);
		unset($_SESSION['tracker_id']);
		unset($_SESSION['tracker_handler']);
		if(!empty($_GET['bug_arr']) && stristr($_GET['action'], 'custom_')){
			$custom_field = str_replace('custom_field_','',$_GET['action']);
			$_SESSION['custom_field_id'] = $custom_field;
			foreach($_GET['bug_arr'] AS $num => $row){
				$_SESSION['custom_field'][$row] = $commonlib->getCustomFieldValueById($row,$custom_field);
				$_SESSION['bug'][$num] = $row;
			}
		}

	}

	# add additional agileMantis functions when performing a bug action
	function event_bug_action ($event, $action, $bug_id){

		$project_id = helper_get_current_project();

		include_once(PLUGIN_CLASS_URI.'class_sprint.php');
		include_once(PLUGIN_CLASS_URI.'class_product_backlog.php');

		$product_backlog = new gadiv_product_backlog();

		$commonlib = new gadiv_sprint();
		$commonlib->getAdditionalProjectFields();

		# restore values from selected bug list if necessary
		foreach($_POST['bug_arr'] AS $num => $row){

			$pb_id 			= $commonlib->getProductBacklogIDByName($row);
			$list_sprints 	= $commonlib->getSprintsByBacklogId($pb_id);
			$current_sprint = $commonlib->getCustomFieldSprint($row);
			$storypoints 	= $product_backlog->getStoryPoints($row);

			# restore story points value
			if($_SESSION['custom_field_id'] == $commonlib->sp){
				if($current_sprint['status'] > 0 || $pb_id == 0){
					$commonlib->restoreCustomFieldValue($row,$_SESSION['custom_field_id'],$_SESSION['custom_field'][$row]);
				}
			}

			# restore product backlog value
			if($_SESSION['custom_field_id'] == $commonlib->pb){
				$pbl = $commonlib->getProjectProductBacklogs(helper_get_current_project());
				$do_not_reset = false;
				if(!empty($pbl)){
					foreach($pbl AS $key => $value){
						if($value['pb_id'] == $pb_id){
							$do_not_reset = true;
						}
					}
				}

				$value_resettet = false;
				if($current_sprint['name'] != '' || $pb_id == 0 || empty($pbl) || $do_not_reset == false){
					$commonlib->restoreCustomFieldValue($row,$_SESSION['custom_field_id'],$_SESSION['custom_field'][$row]);
					$value_resettet = true;
				}

				if(empty($_SESSION['custom_field'][$row]) && $value_resettet == false){
					$commonlib->setTrackerStatus($row,50);
					$commonlib->id = $pb_id;
					$backlog = $commonlib->getSelectedProductBacklog();
					$commonlib->updateTrackerHandler($row , $backlog[0]['user_id'] , $pb_id);
				}

			}

			if($_SESSION['custom_field_id'] == $commonlib->spr){
				if(empty($list_sprints)){
					$commonlib->restoreCustomFieldValue($row,$_SESSION['custom_field_id'],$_SESSION['custom_field'][$row]);
				}

				# old sprint information
				$commonlib->sprint_id = $_SESSION['custom_field'][$row];
				$sprintInfo = $commonlib->getSprintById();

				if($current_sprint['pb_id'] != $pb_id){
					$commonlib->restoreCustomFieldValue($row,$_SESSION['custom_field_id'],$_SESSION['custom_field'][$row]);
				}

				if($sprintInfo['status'] > 0 || $pb_id == 0){
					$commonlib->restoreCustomFieldValue($row,$_SESSION['custom_field_id'],$_SESSION['custom_field'][$row]);
				}
			}

			# update bug date
			bug_update_date($bug_id);

		}
	}

	# add menu items to mantis main menu between "Summary" and "Manage"
	function addStructure() {
		include_once(PLUGIN_CLASS_URI.'class_commonlib.php');
		$commonlib = new gadiv_commonlib();
		$user = $commonlib->getAdditionalUserFields(auth_get_current_user_id());
		$menu = array();

		# add product backlog menu item
		if($user[0]['participant'] == 1 || $user[0]['developer'] == 1 || $user[0]['administrator'] == 1){
			$menu[2] =  '<a href="' . plugin_page("product_backlog.php") . '" style="font-weight:bold;text-decoration:underline">Product Backlog</a>';
		}

		# add sprint backlog or taskboard menu item
		if($user[0]['participant'] == 1 || $user[0]['developer'] == 1 || $user[0]['administrator'] == 1){
			if(plugin_config_get('gadiv_taskboard') == 0){
				$menu[0] =  '<a href="' . plugin_page("sprint_backlog.php") . '" style="font-weight:bold;text-decoration:underline">Sprint Backlog</a>';
			} else {
				$menu[0] =  '<a href="' . plugin_page("taskboard.php") . '" style="font-weight:bold;text-decoration:underline">Sprint Backlog</a>';
			}
		}

		# add daily scrum board
		if(($user[0]['participant'] == 1 || $user[0]['developer'] == 1 || $user[0]['administrator'] == 1) && plugin_config_get('gadiv_daily_scrum') == 1){
			$menu[1] =  '<a href="' . plugin_page("daily_scrum_meeting.php") . '" style="font-weight:bold;text-decoration:underline">Daily Scrum Meeting</a>';
		}

		# add agileMantis menu item
		if(current_user_is_administrator() || $user[0]['administrator'] == 1){
			$menu[3] =  '<a href="' . plugin_page("info.php") . '" style="font-weight:bold;text-decoration:underline">agileMantis</a>';
		}

		return $menu;
	}

	# adds a separate footer at the end of each agileMantis page
	function event_layout_content_end() {
		if(stristr($_GET['page'],'agileMantis') || stristr($_GET['page'],'sprint_backlog') || stristr($_GET['page'],'taskboard') || stristr($_GET['page'],'daily_scrum_meeting') || stristr($_GET['page'],'statistics')){
			echo '<div style="clear:both;"></div>';
			echo '<br>';
			echo '<div align="center"><a href="https://sourceforge.net/p/agilemantis/wiki/Home/" target="_blank">agileMantis-Wiki</a></div>';
			echo '<table border="0" width="100%" cellspacing="0" cellpadding="0"><tr valign="top"><td>';
			echo '<a href="http://www.gadiv.de/de/opensource/agilemantis/agilemantisen.html" target="_blank">agileMantis '.$this->version.'</a><br>';
			echo '<a href="'. plugin_page('info.php') .'" target="_blank">Copyright © 2012-'.date('Y').' gadiv GmbH</a> - <a href="http://gadiv.de" target="_blank">www.gadiv.de</a><br>';
			echo '<a href="mailto:agileMantis@gadiv.de">agileMantis@gadiv.de</a>';
			echo '</td><td valign="middle">', "\n\t", '<div align="right">';
			echo '<a href="http://www.gadiv.de/de/opensource/agilemantis/agilemantisen.html" title="agileMantis auf gadiv-Webseite" target="_blank"><img src="'.PLUGIN_URL.'images/agilemantis_logo.gif" width="32" height="32" alt="gadiv GmbH Logo" border="0"/></a>';
			echo '', "\n", '</div></td></tr></table>', "\n";
			echo "\t", '<hr size="1" />', "\n";
		}
	}

	function event_layout_resources() {
		echo '<link rel="stylesheet" href="'.PLUGIN_URL.'/css/jquery-ui.css">';
	}
}
?>