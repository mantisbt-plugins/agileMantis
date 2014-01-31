<?php
/*
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
*/
?>
<script type="text/javascript">
var calculated_storypoints = 0;
var selected_userstories = 0;

// confirm close user stories
function confirmCloseUserstories() {
  var answer = confirm("<?php echo plugin_lang_get( 'sprint_backlog_close_sprint' )?>");
  if(answer) {
    document.getElementById("closeUserStories").value = '1';
  }  else {
	document.getElementById("closeUserStories").value = '2';
  } 
}

// accept sprint confirmation
function acceptSprintConfirm(){
  var answer = confirm("<?php echo plugin_lang_get( 'sprint_backlog_really_close_sprint' )?>");
  if(answer) {
    document.getElementById("confirmSprint").value = '1';
  }  else {
	document.getElementById("confirmSprint").value = '2';
  } 
}

// accept sprint confirmation if not all tasks are planned
function acceptSprintPreConfirm(){
  var answer = confirm("<?php echo plugin_lang_get( 'sprint_backlog_really_stories_left' )?>");
  if(answer) {
    document.getElementById("preConfirmSprint").value = '1';
  }  else {
	document.getElementById("preConfirmSprint").value = '2';
  } 
}

// set new cookie
function setCookie(bug_id,cookielist){
	if(cookielist){
		document.cookie = 'BugListe' + "=" + cookielist + ',' +bug_id;
	} else {
		document.cookie = 'BugListe' + "=" + bug_id;
	}
	calculateStoryPoints(bug_id);
}

// calculate story points
function calculateStoryPoints(bug_id){
	if(document.getElementById("bug_id_"+bug_id).checked == true){
		calculated_storypoints += parseFloat(document.getElementById("storypoints_"+bug_id).value);
		selected_userstories ++;
	} else {
		calculated_storypoints -= parseFloat(document.getElementById("storypoints_"+bug_id).value);
		selected_userstories --;
	}

	document.getElementById("calculated_storypoints").innerHTML = ""+ calculated_storypoints + "";
	document.getElementById("chosenStoryPoints").innerHTML = ""+ calculated_storypoints + "";
	if(parseFloat(selected_userstories) != 1){
		document.getElementById("selectedUserStories").innerHTML = "<b>"+ selected_userstories + "</b> User Stories";
	} else {
		document.getElementById("selectedUserStories").innerHTML = "<b>1</b> User Story";
	}
}

// get bug list cookie information
function getCookie(){

	var i;
	var x;
	var y;
	var value = document.cookie.split(";");

	for (i=0;i<value.length;i++){
		x	=	value[i].substr(0,value[i].indexOf("="));
		y	=	value[i].substr(value[i].indexOf("=")+1);
		x	=	x.replace(/^\s+|\s+$/g,"");
		if (x	== 'BugListe')
		{
		return unescape(y);
		}
	}
}

// get latest page information
function getLastPage(){
	var i;
	var x;
	var y;
	var value = document.cookie.split(";");

	for (i=0;i<value.length;i++){
		x	=	value[i].substr(0,value[i].indexOf("="));
		y	=	value[i].substr(value[i].indexOf("=")+1);
		x	=	x.replace(/^\s+|\s+$/g,"");
		if (x	== 'LastPage')
		{
		return unescape(y);
		}
	}
}

// delete bug list cookie information
function deleteCookie(){
	document.cookie = 'BugListe' + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

// mark checkboxes according to bug list cookie
function markCheckboxes() {
	myArray = getCookie().split(',');
	for(i = 0; i < myArray.length; i++){
		document.getElementById('bug_id_'+myArray[i]).checked = true;
		calculateStoryPoints(myArray[i]);
	}
}

// if function getCookie is called, call markCheckboxes function
if(getCookie()){
	markCheckboxes();
}
</script>