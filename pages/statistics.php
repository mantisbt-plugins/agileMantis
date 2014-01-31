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
	
	# merge global $_POST / $_GET array
	$request = array_merge($_POST, $_GET);
	include(PLUGIN_URI.'pages/sprint_backlog_functions.php');
	if($show_all_sprints == true){
		include(PLUGIN_URI.'pages/chose_sprint.php');
	} else {
		$request = array_merge($_POST, $_GET);
		$sitekey = $tasks->getConfigValue('plugin_agileMantis_gadiv_sitekey');
		$heute = mktime(0,0,0,date('m'),date('d'),date('y'));
		$current_user = $tasks->getUserPassword(auth_get_current_user_id());
		
		$images = array();
		
		$images[] = PLUGIN_URL.'images/statistics_before_sprint_starts.png';
		$images[] = PLUGIN_URL.'images/statistics_beginning_of_the_sprint.png';
		$images[] = PLUGIN_URL.'images/statistics_during_of_the_sprint.png';
		$images[] = PLUGIN_URL.'images/statistics_end_of_the_sprint.png';
		$images[] = PLUGIN_URL.'images/statistics_end_resolved_userstories_of_the_sprint.png';
		$images[] = PLUGIN_URL.'images/statistics_end_closed_userstories_of_the_sprint.png';
?>
<?php html_page_top(plugin_lang_get( 'statistics_title' )); ?>
<br>
<center>
	<h2><?php echo plugin_lang_get( 'screenshot_title' );?></h2>
	<img src="<?php echo $images[rand(0,count($images)-1)]?>" alt="Screenshot Statistiken" id="highScreenshot" style="height: auto;max-width: 100%;" onmousedown="loadDescription();">
	<div style="margin-bottom: 15px;" align="center">
		<h2><?php echo plugin_lang_get( 'screenshot_title_more' );?></h2>
		<?php 
			for($i = 0; $i < count($images); $i++){
		?>
			<img src="<?php echo $images[$i]?>" alt="Screenshot" onmouseover="changeScreenshot('<?php echo $images[$i] ?>')" class="previewImage">
		<?php
			}
		?>
	</div>
	<div style="clear:both;"></div>
</center>
<br>
<center>
	<form action="<?php echo plugin_page("taskboard.php")?>" method="post">
		<input type="hidden" name="sprintName" value="<?php echo $request['sprintName']?>">
		<input type="submit" name="taskboard" value="Taskboard">
	</form>
	<form action="<?php echo plugin_page("sprint_backlog.php")?>" method="post">
		<input type="hidden" name="sprintName" value="<?php echo $request['sprintName']?>">
		<input type="submit" name="submit" value="Sprint Backlog">
	</form>
</center>
<div id="dialog" title="<?php echo plugin_lang_get( 'screenshot_dialog_title' );?>" style="display:none;">
  <p><?php echo plugin_lang_get( 'screenshot_dialog_text' );?><a href="http://www.gadiv.de">http://gadiv.de</a></p>
</div>
<?php }?>
<script type="text/javascript">
	function changeScreenshot(screenshot){
		document.getElementById("highScreenshot").src = screenshot;
	}
	function loadDescription(){
		$( "#dialog" ).dialog({
			height: 140,
			width: 'auto'
		});
	}
</script>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<style>
	.previewImage {
		border				: 1px solid	#333;
		float				: left;
		height				: 100px;
		margin-right		: 10px;
		margin-bottom		: 10px;
		width				: 250px;
	}
</style>
<?php html_page_bottom() ?>