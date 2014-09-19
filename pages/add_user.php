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



if( $_POST['submit'] == plugin_lang_get( 'button_back' ) ) {
	header( $agilemantis_au->forwardReturnToPage( 'agileuser.php' ) );
}
if( $_POST['action'] == 'addUser' ) {
	
	$f_username = gpc_get_string( 'username' );
	$f_realname = gpc_get_string( 'realname', '' );
	$f_password = gpc_get_string( 'password', '' );
	$f_password_verify = gpc_get_string( 'password_verify', '' );
	$f_email = gpc_get_string( 'email', '' );
	$f_protected = false;
	$f_enabled = true;
	
	if( $_POST['administrator'] == 1 ) {
		$f_access_level = 70;
	} elseif( $_POST['developer'] == 1 ) {
		$f_access_level = 55;
	} else {
		$f_access_level = 25;
	}
	
	# check for empty username
	$f_username = trim( $f_username );
	if( is_blank( $f_username ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}
	
	# Check the name for validity here so we do it before promting to use a
	#  blank password (don't want to prompt the user if the process will fail
	#  anyway)
	# strip extra space from real name
	$t_realname = string_normalize( $f_realname );
	user_ensure_name_valid( $f_username );
	user_ensure_realname_valid( $t_realname );
	user_ensure_realname_unique( $f_username, $f_realname );
	
	if( $f_password != $f_password_verify ) {
		trigger_error( ERROR_USER_CREATE_PASSWORD_MISMATCH, ERROR );
	}
	
	$f_email = email_append_domain( $f_email );
	email_ensure_not_disposable( $f_email );
	
	if( is_blank( $f_password ) ) {
		helper_ensure_confirmed( lang_get( 'empty_password_sure_msg' ), 
								lang_get( 'empty_password_button' ) );
	}
	
	lang_push( config_get( 'default_language' ) );
	
	$t_admin_name = user_get_name( auth_get_current_user_id() );
	$t_cookie = user_create( $f_username, $f_password, $f_email, 
				$f_access_level, $f_protected, $f_enabled, $t_realname, $t_admin_name );
	
	# set language back to user language
	lang_pop();
	
	$t_user_id = user_get_id_by_name( $f_username );
	
	user_set_password( $t_user_id, $f_password, false );
	$agilemantis_au->setAgileMantisUserRights( 
				$t_user_id, $_POST['participant'], $_POST['developer'], $_POST['administrator'] );
	
	header( $agilemantis_au->forwardReturnToPage( 'agileuser.php' ) );
} else {
	html_page_top( plugin_lang_get( 'manage_user_add_new_user' ) );
}
?>

<?php if(user_get_name(auth_get_current_user_id()) == 'administrator'){?>
<br>
<div align="center">
	<form method="post" action="<?php echo plugin_page("add_user.php")?>"
		method="post">
		<input type="hidden" name="action" value="addUser">
		<div class="table-container">
			<table class="width50" cellspacing="1">
				<tr>
					<td class="form-title" colspan="2">
		<?php echo lang_get( 'create_new_account_title' ) ?>
	</td>
				</tr>
				<tr <?php echo helper_alternate_class() ?>>
					<td class="category" width="25%">
		<?php echo lang_get( 'username' ) ?>
	</td>
					<td width="75%"><input type="text" name="username" size="32"
						maxlength="<?php echo USERLEN;?>" /></td>
				</tr>
<?php
	if ( !$t_ldap || config_get( 'use_ldap_realname' ) == OFF ) {
?>
<tr <?php echo helper_alternate_class() ?>>
					<td class="category">
		<?php echo lang_get( 'realname' ) ?>
	</td>
					<td><input type="text" name="realname" size="32"
						maxlength="<?php echo REALLEN;?>" /></td>
				</tr>
<?php
	}

	if ( !$t_ldap || config_get( 'use_ldap_email' ) == OFF ) {
?>
<tr <?php echo helper_alternate_class() ?>>
					<td class="category">
		<?php echo lang_get( 'email' ) ?>
	</td>
					<td>
		<?php print_email_input( 'email', '' ) ?>
	</td>
				</tr>
<?php
	}
?>
<tr <?php echo helper_alternate_class() ?>>
					<td class="category">
		<?php echo lang_get( 'password' ) ?>
	</td>
					<td><input type="password" name="password" size="32"
						maxlength="<?php echo PASSLEN;?>" /></td>
				</tr>
				<tr <?php echo helper_alternate_class() ?>>
					<td class="category">
		<?php echo lang_get( 'verify_password' ) ?>
	</td>
					<td><input type="password" name="password_verify" size="32"
						maxlength="<?php echo PASSLEN;?>" /></td>
				</tr>
				<tr <?php echo helper_alternate_class() ?>>
					<td class="category">
		<?php echo plugin_lang_get( 'manage_user_participant' )?>
	</td>
					<td><input type="checkbox" name="participant" value="1"></td>
				</tr>
				<tr <?php echo helper_alternate_class() ?>>
					<td class="category">
		<?php echo plugin_lang_get( 'manage_user_developer' )?>
	</td>
					<td><input type="checkbox" name="developer" value="1"></td>
				</tr>
				<tr <?php echo helper_alternate_class() ?>>
					<td class="category">
		<?php echo plugin_lang_get( 'manage_user_administrator' )?>
	</td>
					<td><input type="checkbox" name="administrator" value="1"></td>
				</tr>
				<tr>
					<td class="center" colspan="2"><input type="submit" class="button"
						value="<?php echo lang_get( 'create_user_button' ) ?>" /> <input
						type="submit" class="button" name="submit"
						value="<?php echo plugin_lang_get( 'button_back' ) ?>" /></td>
				</tr>
			</table>
		</div>
	</form>
</div>
<?php } else {
	echo '<br><center><span class="message_error">'.
			plugin_lang_get( 'info_error_921001' ).'</span></center>';
	}?>
<?php html_page_bottom() ?>