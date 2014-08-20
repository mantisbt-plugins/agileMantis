var calculated_storypoints = 0;
var selected_userstories = 0;

function confirmCloseUserstories() {
  var answer = confirm("Der Sprint ist geschlossen! Möchten Sie jetzt auch die dazugehörigen User Stories schließen?");
  if(answer) {
    document.getElementById("closeUserStories").value = '1';
  }  else {
	document.getElementById("closeUserStories").value = '2';
  } 
}
function acceptSprintConfirm(){
  var answer = confirm("Soll der Sprint wirklich bestätigt werden?");
  if(answer) {
    document.getElementById("confirmSprint").value = '1';
  }  else {
	document.getElementById("confirmSprint").value = '2';
  } 
}

function acceptSprintPreConfirm(){
  var answer = confirm('Es sind noch User Stories ohne Tasks oder Task ohne geplanten Aufwand vorhanden !\r\nMöchten Sie den Sprint trotzdem bestätigen?');
  if(answer) {
    document.getElementById("preConfirmSprint").value = '1';
  }  else {
	document.getElementById("preConfirmSprint").value = '2';
  } 
}

function openUserStory (url) {
   windowUserStory = window.open(url, 'User Story Informationen', "width=520,height=370,status=no,scrollbars=no,resizable=no");
   windowUserStory.focus();
}

function setCookie(bug_id,cookielist){
	if(cookielist){
		document.cookie = 'BugListe' + "=" + cookielist + ',' +bug_id;
	} else {
		document.cookie = 'BugListe' + "=" + bug_id;
	}
	calculateStoryPoints(bug_id);
}

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

function deleteCookie(){
	document.cookie = 'BugListe' + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

function markCheckboxes() {
	myArray = getCookie().split(',');
	for(i = 0; i < myArray.length; i++){
		document.getElementById('bug_id_'+myArray[i]).checked = true;
		calculateStoryPoints(myArray[i]);
	}
}

if(getCookie()){
	markCheckboxes();
}