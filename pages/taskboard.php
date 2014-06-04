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
	
	# add sprint backlog functions
	include(PLUGIN_URI.'pages/sprint_backlog_functions.php');
	if($show_all_sprints == true){
		include(PLUGIN_URI.'pages/chose_sprint.php');
	} else {
		include(PLUGIN_URI.'pages/sprint_backlog_header.php');
	if($no_sprints == false){
	?>
	<br>
	<?php include(PLUGIN_URI.'pages/sprint_backlog_actions.php');?>
	<br>
	<center>
	<?php 
		if(plugin_is_loaded('agileMantisExpert')){
			event_signal( 'EVENT_LOAD_TASKBOARD', array( auth_get_current_user_id(), $s['name'], 0 ) );
		}  else {
			$images = array();
			
			$images[] = PLUGIN_URL.'images/taskboard_before_sprint_starts.png';
			$images[] = PLUGIN_URL.'images/taskboard_beginning_of_the_sprint.png';
			$images[] = PLUGIN_URL.'images/taskboard_during_of_the_sprint.png';
			$images[] = PLUGIN_URL.'images/taskboard_end_of_the_sprint.png';
			$images[] = PLUGIN_URL.'images/taskboard_end_resolved_userstories_of_the_sprint.png';
			$images[] = PLUGIN_URL.'images/taskboard_end_closed_userstories_of_the_sprint.png';
		
	?>
		<h2><?php echo plugin_lang_get( 'screenshot_title' );?></h2>
		<center>
			<a href="http://www.gadiv.de/de/opensource/agilemantis/agilemantisen.html"><?php echo plugin_lang_get( 'license_download' )?></a>
			<br><br>
			<form method="post" action="http://www.gadiv.de/de/opensource/agilemantis/agilemantisen.html">
				<input type="hidden" name="action" value="buyLicense">
				<input type="submit" name="buyLicense" value="<?php echo plugin_lang_get( 'license_buy' )?>">
			</form>
		</center>
		<div style="height:830px;">
		<img src="<?php echo $images[rand(0,count($images)-1)]?>" alt="Screenshot Taskboard" id="highScreenshot" style="height: auto;max-width: 100%;" onmousedown="loadDescription();">
		</div>
		<h2><?php echo plugin_lang_get( 'screenshot_title_more' );?></h2>
		<div style="margin-bottom: 15px;" align="center">
			<?php 
				for($i = 0; $i < count($images); $i++){
			?>
				<img src="<?php echo $images[$i]?>" alt="Screenshot" onmouseover="changeScreenshot('<?php echo $images[$i] ?>')" class="previewImage">
			<?php
				}
			?>
		</div>
		<div style="clear:both;"></div>
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
	<div id="dialog" title="<?php echo plugin_lang_get( 'screenshot_dialog_title' );?>" style="display:none;">
	  <p><?php echo plugin_lang_get( 'screenshot_dialog_text' );?><a href="http://www.gadiv.de">http://gadiv.de</a></p>
	</div>
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
	<?php }?>
	</center>
	<br>
	<?php html_status_legend();?>
	<?php html_page_bottom() ?>
	<?php include(PLUGIN_URI.'pages/agileMantisActions.js.php');?>
	<?php } else {?>
<br>
<center><span style="color:red; font-size:16px; font-weight:bold;"><?php echo $system?></span></center>
<br>
<?php }}?>