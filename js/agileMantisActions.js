var calculated_storypoints = 0;
var selected_userstories = 0;

// confirm close user stories
function confirmCloseUserstories(message) {
  var answer = confirm( message );
  if( answer ) {
    document.getElementById( "closeUserStories" ).value = '1';
  }  else {
	document.getElementById( "closeUserStories" ).value = '2';
  } 
}

// accept sprint confirmation
function acceptSprintConfirm( message, message2 ) {
	
	var answer;
	if( message2.length > 0 ) {
		answer = confirm( message2 );			
	} else {
		answer = confirm( message );
	}
	var value = '2'; // do not confirm sprint
	if( answer ) {
		value = '1';
	}
    document.getElementById( "confirmSprint" ).value = value;
}

// set new cookie
function setCookie( bug_id, cookielist ) {
	if( cookielist ) {
		document.cookie = 'BugListe' + "=" + cookielist + ',' + bug_id;
	} else {
		document.cookie = 'BugListe' + "=" + bug_id;
	}
	calculateStoryPoints( bug_id );
}

// calculate story points
function calculateStoryPoints( bug_id ) {
	var sp = document.getElementById( "storypoints_" + bug_id ).value;
	if( sp != null && sp > 0 ) {
		sp = parseFloat( sp );
	} else {
		sp = 0;
	}
	
	if( document.getElementById( "bug_id_" + bug_id ).checked == true ) {
		calculated_storypoints += sp;
		selected_userstories++;
	} else {
		calculated_storypoints -= sp;
		selected_userstories--;
	}

	document.getElementById( "calculated_storypoints" ).innerHTML = "" + calculated_storypoints + "";
	document.getElementById( "chosenStoryPoints" ).innerHTML = ""+ calculated_storypoints + "";
	if( parseFloat( selected_userstories ) != 1 ) {
		document.getElementById( "selectedUserStories" ).innerHTML = "<b>" + selected_userstories + "</b> User Stories";
	} else {
		document.getElementById( "selectedUserStories" ).innerHTML = "<b>1</b> User Story";
	}
}

// get bug list cookie information
function getCookie() {

	var i;
	var x;
	var y;
	var value = document.cookie.split( ";" );

	for ( i = 0; i < value.length; i++) {
		x	=	value[i].substr(0,value[i].indexOf( "=" ) );
		y	=	value[i].substr( value[i].indexOf( "=" ) + 1 );
		x	=	x.replace( /^\s+|\s+$/g, "" );
		if ( x	== 'BugListe' ) {
			return unescape( y );
		}
	}
}

// get latest page information
function getLastPage() {
	var i;
	var x;
	var y;
	var value = document.cookie.split( ";" );

	for ( i = 0; i < value.length; i++ ) {
		x	=	value[i].substr( 0, value[i].indexOf( "=" ) );
		y	=	value[i].substr( value[i].indexOf( "=" ) + 1 );
		x	=	x.replace( /^\s+|\s+$/g, "" );
		if ( x	== 'LastPage' ) {
			return unescape( y );
		}
	}
}

// delete bug list cookie information
function deleteCookie() {
	document.cookie = 'BugListe' + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

// mark checkboxes according to bug list cookie
function markCheckboxes() {
	myArray = getCookie().split( ',' );
	for( var i = 0; i < myArray.length; i++ ) {
		document.getElementById( 'bug_id_' + myArray[i] ).checked = true;
		calculateStoryPoints( myArray[i] );
	}
}

// if function getCookie is called, call markCheckboxes function
if( getCookie() ) {
	markCheckboxes();
}

function changeScreenshot( screenshot ) {
	document.getElementById( "highScreenshot" ).src = screenshot;
}

function loadDescription() {
	$( "#dialog" ).dialog( {
		height: 140,
		width: 'auto'
	} );
}

//change task unit warning
function changeTaskUnit( taskunit, messageA, messageB, messageC ) {
	var returnConfirm = confirm( messageA + "\r\n" + messageB + "\r\n" + messageC );
	if( returnConfirm ) {
		document.getElementById( "changeUnit" ).value = "deleteUnit";
	}
	if( !returnConfirm ) {
		for( var i = 0; i < document.getElementById( "gadiv_task_unit_mode" ).length; i++ ) {
			if( document.getElementById( "gadiv_task_unit_mode" ).options[i].value == taskunit ) {
				document.getElementById( "gadiv_task_unit_mode" ).selectedIndex = i;
			}
		}
	}
}

// warning, if user really wants to delete the selected custom field
function deleteProjectField( fieldname, fieldname_database, message ) {
	var msgLocale = message.replace( "%s", fieldname );
	
	var returnConfirm = confirm( msgLocale );
	if( returnConfirm ) {
		document.getElementById( "deleteField" ).value = fieldname_database;
		document.getElementById( "config_form" ).submit();
	}
}

// enables agileMantis custom field delete button
function enableButton(fieldname){
	var fieldname2 = fieldname+'_button';
	if( document.getElementById( fieldname2 ).disabled == true ) {
		document.getElementById( fieldname2 ).disabled = false;
	} else {
		document.getElementById( fieldname2 ).disabled = true;
	}
	if( document.getElementById( fieldname ).readOnly == true ) {
		document.getElementById( fieldname ).readOnly = false;
	} else {
		document.getElementById( fieldname ).readOnly = true;
	}
	if( fieldname == "gadiv_ranking_order" ) {
		if( document.getElementById( fieldname2 ).disabled == false 
					&& fieldname == "gadiv_ranking_order" ) {
			document.getElementById( "show_rankingorder" ).disabled = true;
		} else {
			document.getElementById( "show_rankingorder" ).disabled = false;
		}
	}
}

function loadUserstoryNoExpert( id, baseUrl ) {
	var div = document.createElement( "div" );
	div.id = "userstory_" + id;
	div.style.display = "none";
	div.className = "SpecialUserStoryView";
	div.innerHTML = '<img src="' + baseUrl + 
		'images/show_userstory_information.png" alt="Expert Screenshot">';
	
	$( div ).dialog( {
		autoOpen: 'false',
		height: 720,
		resizable: false,
		width: 760
	} );
}


function showCustomFieldGenerator(customFieldInstru, customFieldInstru1, customFieldInstru2, 
			customFieldInstru3, customFieldInstru4, customFieldTitel){
	var div = document.createElement( "div" );
	div.id = "custom_field_gen";
	div.style.display = "none";
	div.innerHTML =
	'<div style="font-size: 10pt; font-family: Verdana, Arial, Helvetica, sans-serif;">' +
	customFieldInstru + '<br><br>' + customFieldInstru1 + '<br>' + customFieldInstru2 + 
	'<br>' + customFieldInstru3 + '<br><br>' + customFieldInstru4 + '<br><br>' + 
	'</div>' +
	'<code>' +
	'<textarea wrap="false" readonly="true" ' +
	'style="font-size: 10pt; font-family: Courier New, Lucida Console, sans-serif;' +
	' width: 550px; height: 250px; resize:none">' +
	'\<\?php\n' +
	'\tswitch( lang_get_current() ) {\n' +
	'\t\tcase "german":\n' +
	'\t\t\t$s_Presentable = "Pr&amp;auml;sentabel";\n' +
	'\t\t\t$s_InReleaseDocu = "In Freigabedoku";\n' +
	'\t\t\t$s_PlannedWork = "Planaufwand";\n' +
	'\t\t\t$s_RankingOrder = "Rangfolge";\n' +
	'\t\t\t$s_Technical = "Technisch";\n' +
	'\t\t\t$s_PlannedWorkUnit = "Aufwandseinheit";\n'+
	'\t\tbreak;\n' +
	'\t\tcase "english":\n'+
	'\t\t\t$s_Presentable = "Presentable";\n' +
	'\t\t\t$s_InReleaseDocu = "In Releasedocu";\n' +
	'\t\t\t$s_PlannedWork = "Planned Work";\n' +
	'\t\t\t$s_RankingOrder = "Ranking Order";\n' +
	'\t\t\t$s_Technical = "Technical";\n' +
	'\t\t\t$s_PlannedWorkUnit = "Planned Work Unit";\n' +
	'\t\tbreak;\n' +
	'\t}\n\n' +
	'\t$s_ProductBacklog = "Product Backlog";\n' +
	'\t$s_BusinessValue = "Business Value";\n' +
	'?>' +
	'</textarea>' +
	'</code>';
	
	$( div ).dialog( {
		open: function( event, ui ) { 
				$( ".applet-row" ).hide(); 
			  },
		close: function( event, ui ) { 
				$( ".applet-row" ).show(); 
			  },
		title: customFieldTitel,
		autoOpen: 'false',
		height: 540,
		resizable: false,
		width: 580
	} );
}