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


	html_page_top(plugin_lang_get( 'info_title' ));
	
	if(!config_is_set('plugin_agileMantis_gadiv_agilemantis_version')){
		config_set('plugin_agileMantis_gadiv_agilemantis_version', 0);
	}
?>

<?php
	$t_user_right = $agilemantis_au->authUser();
	if ( $t_user_right == 2 || $t_user_right == 3 || current_user_is_administrator() ) {?>
<br>
<?php include(AGILEMANTIS_PLUGIN_URI.'/pages/footer_menu.php')?>
<br>
<?php
	echo $system;
?>
<div class="table-container">
<table align="center" class="width75" cellspacing="1">
<tr>
	<td colspan="2"><b><?php echo plugin_lang_get( 'info_title' )?></b></td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">Version</td>
	<td>
		<?php echo $g_plugin_cache['agileMantis']->version;?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category"><?php echo plugin_lang_get( 'info_company' )?></td>
	<td>gadiv GmbH, Boevingen 148, 53804 Much, Germany</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category"><?php echo plugin_lang_get( 'info_website' )?></td>
	<td><a href="http://www.gadiv.de">http://www.gadiv.de</a></td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category"><?php echo plugin_lang_get( 'info_contact_email' )?></td>
	<td><a href="mailto:agileMantis@gadiv.de">agileMantis@gadiv.de</a></td>
</tr>
</table></div>
<br>
<?php
if(plugin_is_loaded('agileMantisExpert')){
	event_signal( 'EVENT_LOAD_THIRDPARTY');
}
?>
<?php } else {
		echo '<br><center><span class="message_error">'.
		plugin_lang_get( 'info_error_921000' ).'</span></center>';}?>
<?php html_page_bottom() ?>