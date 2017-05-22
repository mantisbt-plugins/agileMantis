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


# merge global $_POST / $_GET array
$request = array_merge( $_POST, $_GET );
include (AGILEMANTIS_PLUGIN_URI . 'pages/sprint_backlog_functions.php');
if( $show_all_sprints == true ) {
	include (AGILEMANTIS_PLUGIN_URI . 'pages/chose_sprint.php');
} else {
	
	if( !config_is_set( 'velocity_checkbox_selected', auth_get_current_user_id() ) ) {
		config_set( 'velocity_checkbox_selected', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_sp_gesamt', auth_get_current_user_id() ) ) {
		config_set( 'velocity_sp_gesamt', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_je_entwickler', auth_get_current_user_id() ) ) {
		config_set( 'velocity_je_entwickler', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_je_entwickler_tag', auth_get_current_user_id() ) ) {
		config_set( 'velocity_je_entwickler_tag', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_je_aufwands_tag', auth_get_current_user_id() ) ) {
		config_set( 'velocity_je_aufwands_tag', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_kapazitaet', auth_get_current_user_id() ) ) {
		config_set( 'velocity_kapazitaet', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_referenz_sprint', auth_get_current_user_id() ) ) {
		config_set( 'velocity_referenz_sprint', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_vorgaenger_sprint', auth_get_current_user_id() ) ) {
		config_set( 'velocity_vorgaenger_sprint', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'velocity_letzte_x_vorg_sprints', auth_get_current_user_id() ) ) {
		config_set( 'velocity_letzte_x_vorg_sprints', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_hours', auth_get_current_user_id() ) ) {
		config_set( 'burndown_hours', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_hours_capacity', auth_get_current_user_id() ) ) {
		config_set( 'burndown_hours_capacity', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_hours_optimal', auth_get_current_user_id() ) ) {
		config_set( 'burndown_hours_optimal', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_hours_ideal', auth_get_current_user_id() ) ) {
		config_set( 'burndown_hours_ideal', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_hours_actual', auth_get_current_user_id() ) ) {
		config_set( 'burndown_hours_actual', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_hours_trend', auth_get_current_user_id() ) ) {
		config_set( 'burndown_hours_trend', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_sp', auth_get_current_user_id() ) ) {
		config_set( 'burndown_sp', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_sp_ideal', auth_get_current_user_id() ) ) {
		config_set( 'burndown_sp_ideal', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_sp_actual', auth_get_current_user_id() ) ) {
		config_set( 'burndown_sp_actual', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_sp_trend', auth_get_current_user_id() ) ) {
		config_set( 'burndown_sp_trend', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_tasks', auth_get_current_user_id() ) ) {
		config_set( 'burndown_tasks', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_tasks_ideal', auth_get_current_user_id() ) ) {
		config_set( 'burndown_tasks_ideal', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_tasks_actual', auth_get_current_user_id() ) ) {
		config_set( 'burndown_tasks_actual', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'burndown_tasks_trend', auth_get_current_user_id() ) ) {
		config_set( 'burndown_tasks_trend', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'utilization_distribution_planned', auth_get_current_user_id() ) ) {
		config_set( 'utilization_distribution_planned', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'utilization_distribution_remains', auth_get_current_user_id() ) ) {
		config_set( 'utilization_distribution_remains', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'utilization_utilizationdetailed', auth_get_current_user_id() ) ) {
		config_set( 'utilization_utilizationdetailed', 1, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'statistic_velocity_amount_of_sprints', auth_get_current_user_id() ) ) {
		config_set( 'statistic_velocity_amount_of_sprints', 5, auth_get_current_user_id() );
	}
	
	if( !config_is_set( 'statistic_velocity_referenced_sprint', auth_get_current_user_id() ) ) {
		config_set( 'statistic_velocity_referenced_sprint', "", auth_get_current_user_id() );
	}
	
	include (AGILEMANTIS_PLUGIN_URI . 'pages/sprint_backlog_header.php');
	?>
<br>
<?php include(AGILEMANTIS_PLUGIN_URI.'pages/sprint_backlog_actions.php');?>
<br>
<center>
<?php
	if( !plugin_is_loaded( 'agileMantisExpert' ) ) {
		$images = array();
		
		$images[] = AGILEMANTIS_PLUGIN_URL . 'images/statistics_before_sprint_starts.png';
		$images[] = AGILEMANTIS_PLUGIN_URL . 'images/statistics_beginning_of_the_sprint.png';
		$images[] = AGILEMANTIS_PLUGIN_URL . 'images/statistics_during_of_the_sprint.png';
		$images[] = AGILEMANTIS_PLUGIN_URL . 'images/statistics_end_of_the_sprint.png';
		$images[] = AGILEMANTIS_PLUGIN_URL .
			 'images/statistics_end_resolved_userstories_of_the_sprint.png';
		$images[] = AGILEMANTIS_PLUGIN_URL .
			 'images/statistics_end_closed_userstories_of_the_sprint.png';
		?>
	<h2><?php echo plugin_lang_get( 'screenshot_title' );?></h2>
	<a href="<?php echo AGILEMANTIS_EXPERT_DOWNLOAD_LINK ?>"><?php 
			echo plugin_lang_get( 'license_download' )?></a>
	<br>
	<br> <a href="<?php echo AGILEMANTIS_ORDER_PAGE_URL ?>"><?php 
			echo plugin_lang_get( 'license_buy' )?></a>
	<img src="<?php echo $images[rand(0, count( $images ) - 1 )]?>"
		alt="Screenshot Statistiken" id="highScreenshot"
		style="height: auto; max-width: 100%;"
		onmousedown="loadDescription();">
	<div style="margin-bottom: 15px;" align="center">
		<h2><?php echo plugin_lang_get( 'screenshot_title_more' );?></h2>
		<?php 
			for( $i = 0; $i < count( $images ); $i++ ) {
		?>
			<img src="<?php echo $images[$i]?>" alt="Screenshot"
			onmouseover="changeScreenshot('<?php echo $images[$i] ?>')"
			class="previewImage">
		<?php
			}
		?>
	</div>
	<div style="clear: both;"></div>
<?php } ?>
</center>
<br>
<div id="dialog"
	title="<?php echo plugin_lang_get( 'screenshot_dialog_title' );?>"
	style="display: none;">
	<p><?php echo plugin_lang_get( 'screenshot_dialog_text' );?><a
			href="http://www.gadiv.de">http://gadiv.de</a>
	</p>
</div>
<?php }?>
<?php html_page_bottom() ?>