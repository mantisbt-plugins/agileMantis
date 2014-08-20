<?$sitekey = $tasks->getConfigValue('plugin_agileMantis_gadiv_sitekey');?>
<?$heute = mktime(0,0,0,date('m'),date('d'),date('y'));?>
<?$current_user = $tasks->getUserPassword(auth_get_current_user_id());?>
<div style="overflow:hidden; height:650px;">
<applet codebase="<?=PLUGIN_URL?>pages/Applet" code="de.gadiv.agilemantis.UserStoryApplet.class" archive="agileMantisApplets_1.0.0.jar" width="725" height="650" align="top">
	<param name="base_url" value="<?=BASE_URL?>">
    <param name="schnittstellen_url" value="<?=SCHNITTSTELLEN_URL?>">
	<param name="user" value="<?=auth_get_current_user_id()?>">
	<param name="appletkey" value="<?=md5($sitekey.$current_user['password'].$heute)?>">
	<param name="language" value="<?=lang_get_current()?>">
	<param name="userstory_id" value="<?=$_GET['userstory_id']?>">
	<?if(plugin_config_get('gadiv_task_unit_mode') != "keine"){?>
	<param name="userstorycostunit" value="<?=plugin_config_get('gadiv_userstory_unit_mode')?>">
	<?} else {?>
	<param name="userstorycostunit" value="">
	<?}?>
</applet>
<br>
</div>