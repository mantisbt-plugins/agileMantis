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



// check, if expert button was pressed by user
$_SESSION['expert'] = $_POST['expert'];
$isExpertButton = false;
if( !empty( $_POST['expert'] ) ) {
	foreach( $_POST['expert'] as $num => $row ) {
		if( $num > 0 ) {
			$agilemantis_au->setExpert( $num, 0 );
			header( "Location: " . plugin_page( "agileuser.php" ) );
		}
	}
}

$yes = plugin_lang_get( 'manage_user_su_yes' );
$no = plugin_lang_get( 'manage_user_su_no' );

// Only if above condition does not apply.
$rsUser = $agilemantis_au->getAllUser();
foreach( $rsUser as $num => $usr ) {
	$i = $usr[id];
	
	if( $_POST['participant'][$i] == 1 || $_POST['developer'][$i] == 1 ) {
		$particpant = 1;
	} else {
		$particpant = 0;
	}
	$_SESSION['participant'][$i] = $particpant;
	
	if( $_POST['developer'][$i] == 1 ) {
		$developer = 1;
	} else {
		$developer = 0;
	}
	$_SESSION['developer'][$i] = $developer;
	
	if( $_POST['administrator'][$i] == 1 ) {
		$administrator = 1;
	} else {
		$administrator = 0;
	}
	$_SESSION['administrator'][$i] = $administrator;
}

# For all users check, if change of rights is allowed.


$stilTab = "<div class=\"table-container\"><table align=\"center\" style=\"width:300px\">";

$users[0] = $stilTab; # No change
$users[1] = $stilTab; # Right changed, no restrictions
$users[2] = $stilTab; # Request: Stakeholders
$users[3] = $stilTab; # Request: Developers without tasks
$users[4] = $stilTab; # Stop: Developers with tasks loose right
$users[5] = $stilTab; # Stop: Master/Owner loose right


for( $j = 0; $j <= 5; $j++ ) {
	$toggle[$j] = 0;
}
$retMax = 0;

foreach( $rsUser as $num => $usr ) {
	$i = $usr[id];
	
	$ret = $agilemantis_au->checkChangeRightsAllowed( $i, $_SESSION['participant'][$i], 
		$_SESSION['developer'][$i], $_SESSION['administrator'][$i] );
	if( $ret > $retMax )
		$retMax = $ret;
		
		# Get name of User and store it for output for warnings/confirms
	$username = $agilemantis_au->getUserName( ( int ) $i );
	
	$toggle[$ret] = ($toggle[$ret] + 1) % 2; // Alternate colors of table rows
	$users[$ret] .= '<tr ' .
		 helper_alternate_class( $toggle[$ret] ) . '><td> <b>' . $username . "</b></td></tr>";
}
$users[0] = $users[0] . "</table></div>";
$users[1] = $users[1] . "</table></div>";
$users[2] = $users[2] . "</table></div>";
$users[3] = $users[3] . "</table></div>";
$users[4] = $users[4] . "</table></div>";
$users[5] = $users[5] . "</table></div>";

html_page_top( plugin_lang_get( 'manage_user_title' ) ); 

?>
<br>
<div align="center">
	<div class="dialog_border" align="center">

<?php if( $retMax == 5 ) { ?>
	<?php echo plugin_lang_get( 'manage_user_su_no_save'); ?>
	<br>
		<br>
	<?php echo plugin_lang_get( 'manage_user_su_is_owner_or_master'); ?>
	<br>
		<br>
	<?php echo plugin_lang_get( 'manage_user_su_please_remove_member'); ?>
	<br>
		<br>
	<?php echo $users[$retMax];?>
	<br>
		<br>
		<div align="center">
			<form action="<?php echo plugin_page( 'agileuser.php' ) ?>"
				method="post">
				<input type="hidden" name="backUsers" value="cancel"> <input
					type="submit" name="cancel" value="OK" class="button">
			</form>
		</div>
<?php } ?>


<?php if ( $retMax == 4 ) { ?>
	<?php echo plugin_lang_get( 'manage_user_su_no_save' ); ?>
	<br>
		<br>
	<?php echo plugin_lang_get( 'manage_user_su_is_developer_2' ); ?>
	<br>
		<br>
	<?php echo plugin_lang_get( 'manage_user_su_please_remove_developer' ); ?>
	<br>
		<br>
	<?php echo $users[$retMax];?>
	<br>
		<br>
		<div align="center">
			<form action="<?php echo plugin_page( 'agileuser.php' ) ?>"
				method="post">
				<input type="hidden" name="backUsers" value="cancel"> <input
					type="submit" name="cancel" value="OK" class="button">
			</form>
		</div>
<?php }?>

<?php if ( $retMax == 3 ) { ?>
	<?php echo plugin_lang_get( 'manage_user_su_is_developer_3'); ?>
	<br>
		<br>
	<?php echo plugin_lang_get( 'manage_user_su_developer_remove'); ?>
	<br>
		<br>
	<?php echo plugin_lang_get( 'manage_user_su_want_remove_developer'); ?>
	<br>
		<br>
	<?php echo $users[$retMax];?>
	<br>
		<br>
		<div align="center">
			<form action="<?php echo plugin_page( 'agileuser.php' ) ?>"
				method="post">
				<input type="hidden" name="action" value="saveUsers"> <input
					type="submit" name="saveUsers" value="<?php echo $yes; ?>" class="button">
			</form>
			<form action="<?php echo plugin_page( 'agileuser.php' ) ?>"
				method="post">
				<input type="hidden" name="backUsers" value="cancel"> <input
					type="submit" name="cancel" value="<?php echo $no; ?>" class="button">
			</form>
		</div>
<?php }

	if ( $retMax == 2 ) { 
		echo plugin_lang_get( 'manage_user_su_is_stakeholder'); ?>
	<br>
		<br>
	<?php echo plugin_lang_get( 'manage_user_su_stakeholder_remove'); ?>
	<br>
		<br>
	<?php echo plugin_lang_get( 'manage_user_su_want_remove_sakeholder'); ?>
	<br>
		<br>
	<?php echo $users[$retMax];?>
	<br>
		<br>
		<div align="center">
			<form action="<?php echo plugin_page( 'agileuser.php' ) ?>"
				method="post">
				<input type="hidden" name="action" value="saveUsers"> <input
					type="submit" name="saveUsers" value="<?php echo $yes; ?>" class="button">
			</form>
			<form action="<?php echo plugin_page( 'agileuser.php' ) ?>"
				method="post">
				<input type="hidden" name="backUsers" value="cancel"> <input
					type="submit" name="cancel" value="<?php echo $no; ?>" class="button">
			</form>
		</div>
<?php }?>

<?php if( $retMax == 1 ) { ?>
	<?php echo plugin_lang_get( 'manage_user_su_want_change_rights'); ?>
	<br>
		<br>
	<?php echo $users[$retMax];?>
	<br>
		<br>
		<div align="center">
			<form action="<?php echo plugin_page( 'agileuser.php' ) ?>"
				method="post">
				<input type="hidden" name="action" value="saveUsers"> <input
					type="submit" name="saveUsers" value="<?php echo $yes; ?>" class="button">
			</form>
			<form action="<?php echo plugin_page( 'agileuser.php' ) ?>"
				method="post">
				<input type="hidden" name="backUsers" value="cancel"> <input
					type="submit" name="cancel" value="<?php echo $no; ?>" class="button">
			</form>
		</div>
<?php } ?>

<?php if ( $retMax == 0 ) { ?>
	<?php echo plugin_lang_get( 'manage_user_su_no_change' ); ?>
	<br>
		<br>
		<div align="center">
			<form action="<?php echo plugin_page( 'agileuser.php' ) ?>"
				method="post">
				<input type="hidden" name="backUsers" value="cancel"> <input
					type="submit" name="cancel" value="OK" class="button">
			</form>
		</div>
<?php }?>

</div>
</div>


<?php html_page_bottom(); ?>


