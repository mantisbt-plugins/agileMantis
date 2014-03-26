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
	
	html_page_top(plugin_lang_get( 'info_title' ));
?>

<?php if ( current_user_is_administrator() || $au->authUser() == 2 || $au->authUser() == 3 ) {?>
<br>
<?php include(PLUGIN_URI.'/pages/footer_menu.php')?>
<?php echo form_security_field('plugin_format_config_edit') ?>
<br>
<table align="center" class="width75" cellspacing="1">
<tr>
	<td colspan="2"><b><?php echo plugin_lang_get( 'info_title' )?></b></td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">Version</td>
	<td>1.2.5</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category"><?php echo plugin_lang_get( 'info_company' )?></td>
	<td>gadiv GmbH, Boevingen 148, D-53804 Much, Germany</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category"><?php echo plugin_lang_get( 'info_website' )?></td>
	<td><a href="http://www.gadiv.de">http://www.gadiv.de</a></td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category"><?php echo plugin_lang_get( 'info_contact_email' )?></td>
	<td><a href="mailto:agileMantis@gadiv.de">agileMantis@gadiv.de</a></td>
</tr>
</table>
<br>
<?php 
if(plugin_is_loaded('agileMantisExpert')){
	event_signal( 'EVENT_LOAD_THIRDPARTY');	
} 
?>
<?php } else {echo '<br><center><span style="color:red;font-size:16px;font-weight:bold;">'.plugin_lang_get( 'info_error_921000' ).'</span></center>';}?>
<?php html_page_bottom() ?>