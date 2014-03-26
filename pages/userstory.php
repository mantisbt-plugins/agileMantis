<div style="overflow:hidden; height:650px;">
<?php
	if(plugin_is_loaded('agileMantisExpert')){
		event_signal( 'EVENT_LOAD_USERSTORY', array( auth_get_current_user_id(), (int) $_GET['userstory_id'] ) );
	} else {
?>
<a href="http://www.gadiv.de/de/opensource/agilemantis/agilemantisde.html">Expert-Komponenten downloaden</a>
<br><br>
<form method="post" action="http://jansnasserver.dyndns.org/paypal/lizenzen/">
	<input type="hidden" name="action" value="buyLicense">
	<input type="submit" name="buyLicense" value="Expert-Lizenz erwerben">
</form>
<img src="<?php echo PLUGIN_URL.'images/show_userstory_information.png'?>" alt="Screenshot User Story Information">

<?php
	}
?>
</div>