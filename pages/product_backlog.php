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



	# include additional product backlog functions
	include( AGILEMANTIS_PLUGIN_URI.'pages/product_backlog_functions.php' );
	
	# show chose product backlog page or a product backlog directly
	if( $show_all_backlogs == true && $lock_productbacklog == false ) {
		include( AGILEMANTIS_PLUGIN_URI.'pages/chose_product_backlog.php' );
	} elseif( $lock_productbacklog == false ) {
		include( AGILEMANTIS_PLUGIN_URI.'pages/product_backlog_header.php' );
		include( AGILEMANTIS_PLUGIN_URI.'pages/product_backlog_actions.php' );
		include( AGILEMANTIS_PLUGIN_URI.'pages/product_backlog_stories.php' );
		html_status_legend();
	} else {
		html_page_top( plugin_lang_get( 'product_backlog_chose' ) );
		echo '<br>
			<center>
				<span style="color:red; font-size:16px; font-weight:bold;">'.
					plugin_lang_get( 'product_backlog_error_921B00' ).'</span>
			</center>';
	}
	
	if( plugin_is_loaded( 'agileMantisExpert' ) ) {
		event_signal( 'EVENT_LOAD_USERSTORY' );
	}
	html_page_bottom();
?>