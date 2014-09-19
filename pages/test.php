<?php
	error_reporting(E_ALL);

	require_once('core/config_api.php');
	require_once('config_api.php');
	require_once('history_api.php');
	require_once('bug_api.php');
	require_once('custom_field_api.php');
	require_once('database_api.php');
	

	function assert_customfield_bug($p_custom_field_name, $p_bug_id, $p_exp_value) {
		$t_field_id = custom_field_get_id_from_name($p_custom_field_name);
		$t_value = custom_field_get_value($t_field_id, $p_bug_id);
		if ($t_value != $p_exp_value) {
			echo("Test failed on custom_field= " . $p_custom_field_name . " p_bug_id=" . $p_bug_id 
					. " value=" . $t_value . " exp_value=" . $p_exp_value . "<br>");
		}
	}
	
	function assert_customfield_dbfield($p_custom_field_name, $p_db_field_name, $p_exp_value) {
		$t_field_id = custom_field_get_id_from_name($p_custom_field_name);
		$t_value = custom_field_get_field($t_field_id, $p_db_field_name);
		if ($t_value != $p_exp_value) {
			echo("Test failed on custom_field= " . $p_custom_field_name . " db_field=" . $p_db_field_name 
					. " value=" . $t_value . " exp_value=" . $p_exp_value . "<br>");
		}
	}
	
	echo "<html><body>Führe test aus...<hr>";
	
	$common = new gadiv_commonlib();
	
	// Tests common_lib
	$common->changeCustomFieldFilter('RankingOrder', 0);
	assert_customfield_dbfield('RankingOrder', 'filter_by', 0);
	$common->changeCustomFieldFilter('RankingOrder', 1);
	

	//
	$agilemantis_userstory->addBugMonitor(5, 4);

	//
	$agilemantis_userstory->addBugNote(4, 5, array('subject' => 'j.schmitz@gadiv.de', 'message' => 'test!'), false);
	
	//
	if ($common->getUserStoryStatus(2500) != 50) {
		echo "Test failed on getUserStoryStatus<br>";
	}
	
	//
	if (!$common->is_admin_user(1)) {
		echo "Test failed on is_admin_user<br>";
	}
	
	// 
	if ($common->getConfigValue('database_version') != '183')  {
		echo "Test failed on getConfigValue<br>";
	}
	
	//
	if ($common->getConfigUserValue('velocity_checkbox_selected', 4) != '1')  {
		echo "Test failed on getConfigUserValue<br>";
	}
	
	//
	if ($common->getUserIdByName('jesch') != 5) {
		echo "Test failed on getUserIdByName<br>";
	}
	
	//
	if ($common->getUserRealName(5) != 'Jean Schmitz') {
		echo "Test failed on getUserRealName<br>";
	}
	
	
	//
	if (!$common->customFieldIsInProject('Sprint')) {
		echo "Test failed on customFieldIsInProject<br>";
	}
	
	//
	if ($common->getCustomFieldIdByName('Sprint') != custom_field_get_id_from_name('Sprint')) {
		echo "Test failed on getCustomFieldIdByName<br>";
	}
	
	//
	if (!$common->upsertCustomField(custom_field_get_id_from_name('Storypoints'), 3, 99) ) {
		echo "upsertCustomField failed<br>";
	}
	assert_customfield_bug('Storypoints', 3, 99);
	
	//
	if ($common->getCustomFieldValueById(3, custom_field_get_id_from_name('Storypoints')) != 99) {
		echo "Test failed on getCustomFieldValueById<br>";
	}
	
	// 
	$common->restoreCustomFieldValue(3, custom_field_get_id_from_name('Storypoints'), '42');
	if(!empty(history_get_events_array(3))) {
		echo "Test failed on restoreCustomFieldValue<br>";
	} 
	assert_customfield_bug('Storypoints', 3, 42);
	
	//
	$t_pb_id1 = $common->getProductBacklogIDByBugId(2503);
	$t_pb_id2 = $common->getProductBacklogIDByBugId(2505);
	if ($t_pb_id1 != 2 || $t_pb_id2 != 2) {
		echo "Test failed on getProductBacklogIDByName.<br>";
	}
	
	//
	$t_sprint = $common->getSprintByBugId(2503);
	if ($common->getSprintByBugId(2503)) {
		echo "Test failed on getCustomFieldSprint.<br>";
	}
	
	//
	if ($common->getStoryPoints(3) != 42) {
		echo "Test failed on getStoryPoints.<br>";
	}
	
	//
	$t_pb = $common->getProductBacklogByBugId(2505);
	if ($t_pb['name'] != 'Test2') {
		echo "Test failed on getCustomFieldProductBacklog.<br>";
	}

	
	// Tests for class_product_backlog
	
	//
	
	$pb->name = "New PB";
	$pb->description = "Bla";
	$pb->user_id = 1;
	$pb->email = "j.schmitz@gadiv.de";
	
	//
	$pb->editProductBacklog();
	
	//
	$pb->id = 3;
	$pb->deleteProductBacklog();
	
	//
	$t_sp = $pb->getSprintValue(2500);
	if ($t_sp != 'Sprint1') {
		echo "Test failed on getSprintValue.<br>";
	}
	
	// class_project Tests
	
	// 
	$project->addAdditionalProjectField(custom_field_get_id_from_name('Storypoints'), 50);
	//
	$project->deleteAdditionalProjectField(custom_field_get_id_from_name('Storypoints'), 50);
	
	// class_userstory Tests
	
	//
	$userstory->removeCustomField('Technical');
	
	echo "<hr>Test ausgeführt...</body></html>";
?>
