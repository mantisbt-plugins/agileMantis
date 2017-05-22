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


	# add sprint backlog functions
	include( AGILEMANTIS_PLUGIN_URI.'pages/sprint_backlog_functions.php' );
	
if( $show_all_sprints == true ) {
	include(AGILEMANTIS_PLUGIN_URI.'pages/chose_sprint.php');
} else {
	include(AGILEMANTIS_PLUGIN_URI.'pages/sprint_backlog_header.php');
		
	if( $no_sprints == false ) {
	?>
	<br>
	<?php include(AGILEMANTIS_PLUGIN_URI.'pages/sprint_backlog_actions.php');?>
	<br>
	<center>
	<?php 
		if( !plugin_is_loaded( 'agileMantisExpert' ) ) {
			$images = array();
			
			$images[] = AGILEMANTIS_PLUGIN_URL.'images/taskboard_before_sprint_starts.png';
			$images[] = AGILEMANTIS_PLUGIN_URL.'images/taskboard_beginning_of_the_sprint.png';
			$images[] = AGILEMANTIS_PLUGIN_URL.'images/taskboard_during_of_the_sprint.png';
			$images[] = AGILEMANTIS_PLUGIN_URL.'images/taskboard_end_of_the_sprint.png';
			$images[] = AGILEMANTIS_PLUGIN_URL.'images/taskboard_end_resolved_userstories_of_the_sprint.png';
			$images[] = AGILEMANTIS_PLUGIN_URL.'images/taskboard_end_closed_userstories_of_the_sprint.png';
		
	?>
		<h2><?php echo plugin_lang_get( 'screenshot_title' );?></h2>
		<a href="<?php echo AGILEMANTIS_EXPERT_DOWNLOAD_LINK ?>"><?php 
			echo plugin_lang_get( 'license_download' )?></a>
		<br><br>
		<a href="<?php echo AGILEMANTIS_ORDER_PAGE_URL ?>"><?php 
			echo plugin_lang_get( 'license_buy' )?></a>
		<div style="height:830px;">
		<img src="<?php echo $images[rand(0,count($images)-1)]?>" 
			alt="Screenshot Taskboard" id="highScreenshot" style="height: auto;max-width: 100%;" 
			onmousedown="loadDescription();">
		</div>
		<h2><?php echo plugin_lang_get( 'screenshot_title_more' );?></h2>
		<div style="margin-bottom: 15px;" align="center">
			<?php 
				for($i = 0; $i < count($images); $i++){
			?>
				<img src="<?php echo $images[$i]?>" alt="Screenshot" 
				onmouseover="changeScreenshot('<?php echo $images[$i] ?>')" class="previewImage">
			<?php
				}
			?>
		</div>
		<div style="clear:both;"></div>
	<div id="dialog" title="<?php echo plugin_lang_get( 'screenshot_dialog_title' );?>" 
			style="display:none;">
	  <p><?php echo plugin_lang_get( 'screenshot_dialog_text' );?>
	  	<a href="http://www.gadiv.de">http://gadiv.de</a></p>
	</div>

	<?php }?>
	</center>
	<br>
	<?php html_status_legend();?>
	<?php html_page_bottom() ?>
	<?php } else {?>
<br>
<center><span class="message_error"><?php echo $system?></span></center>
<br>
<?php }}?>