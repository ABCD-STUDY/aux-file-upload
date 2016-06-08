//----------------------------------------
// User accounts
//----------------------------------------
// logout the current user
function logout() {
    jQuery.get('/code/php/logout.php', function(data) {
	if (data == "success") {
            // user is logged out, reload this page
	} else {
            alert('something went terribly wrong during logout: ' + data);
	}
	window.location.href = "/applications/User/login.php";
    });
}

function checkConnectionStatus() {
    jQuery.getJSON('/code/php/heartbeat.php', function() {
	//jQuery('#connection-status').addClass('connection-status-ok');
	jQuery('#connection-status').css('color', "#228B22");
	jQuery('#connection-status').attr('title', 'Connection established last at ' + Date());
    }).error(function() {
	// jQuery('#connection-status').removeClass('connection-status-ok');
	jQuery('#connection-status').css('color', "#CD5C5C");
	jQuery('#connection-status').attr('title', 'Connection failed at ' + Date());
    });
}


function storeSubjectAndName() {
    var subject = jQuery('#session-participant').val();
    var session = jQuery('#session-name').val();
    var run     = jQuery('#session-run').val();

    if (subject === null)
	return; // don't store anything
    jQuery('#session-participant').val(subject);
    jQuery('#session-name').val(session);
    jQuery('.subject-id').text("Subject ID: " + subject);
    jQuery('.session-id').text("Session: " + session);
    jQuery('.run-id').text("Run: " + run);
    
    if (subject.length > 0 && session.length > 0) {
	jQuery('#session-active').text("Active Session");
    } else {
	jQuery('#session-active').text("No Active Session");
    }
    
    var data = {
	"subjid": subject,
	"sessionid": session,
	"task": "aux-file-upload",
	"run": run
    };
    
    jQuery.get('../../code/php/session.php', data, function() {
	console.log('stored subject, session and run: ' +  subject + ", " + session + ", " + run );
    });
}

// forget about the current session
function closeSession() {
    // just set to empty strings and submit
    jQuery('#session-participant').val("");
    jQuery('#session-name').val("");
    jQuery('#session-run').val("01");
    storeSubjectAndName();
}

// get valid session names
function getSessionNamesFromREDCap() {
    jQuery.getJSON('/code/php/getRCEvents.php', function(data) {
	for (var i = 0; i < data.length; i++) {
	    val = "";
	    if (i == 1) {
		val = "selected=\"selected\"";
	    }
	    jQuery('#session-name').append("<option " + val + " value=\"" + data[i].unique_event_name + "\">" + data[i].event_name + "</option>");
	}
	getParticipantNamesFromREDCap();
    });
}

function getParticipantNamesFromREDCap() {
    jQuery.getJSON('/code/php/getParticipantNamesFromREDCap.php', function(data) {
	for (var i = 0; i < data.length; i++) {
	    jQuery('#session-participant').append("<option value=\"" + data[i] + "\">" + data[i] + "</option>");
	}
	jQuery('#session-run').val("01");
	storeSubjectAndName();
    });
}

function createFileUploads() {
    jQuery('#upload-objects').children().remove();
    // get a list of the objects that are allowed for each subject
    jQuery.getJSON('fileObjects.json', function(data) {
	// get list of objects
	for (var i = 0; i < data.length; i++) {
	    jQuery('#upload-objects').append("<div class=\"upload-group\"><h2>" + data[i].name + "</h2><p>"+data[i].description+"</p><div id=\"uo"+ i + "\"></div></div>");
	    for (var j = 0; j < data[i].objects.length; j++) {
		var o = data[i].objects[j];
		jQuery('#uo'+i).append("<div class=\"upload-item\"><h3>"+ o.name + "</h3><p>" + o.description + "</p><div id=\"uoi"+i+j+"\"></div></div>");
                jQuery('#uoi'+i+j).append("<span class=\"btn btn-success fileinput-button\"><i class=\"glyphicon glyphicon-plus\"></i><span> Select files...</span><input id=\"fileupload" + i + j + "\" type=\"file\" name=\"files"+o.tag+"[]\" multiple> </span><br><div id=\"progress" +o.tag +"\" class=\"progress\"><div class=\"progress-bar progress-bar-success\"></div></div><div id=\"files"+o.tag+"\" class=\"files\"></div>");

		// start this with a this that has each o.tag in it....
		(function( o ) {
		  return jQuery('#fileupload'+i+j).fileupload( {
		    url: "server/php/",
		    dataType: 'json',
	            acceptFileTypes: /(\.|\/)(zip|tgz|tar.gz|bzip2)$/i,
		    paramName: 'files'+o.tag,
    	              formData: { 'param_name': 'files'+o.tag, 'type': o.tag },
		    done: function(e, data) {
			jQuery.each(data.files, function (index, file) {
			    jQuery('<p/>').text(file.name).appendTo('#files'+o.tag);
			});
		    },
		    progressall: function(e,data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			jQuery('#progress'+o.tag+' .progress-bar').css(
			    'width',
			    progress + '%'
			);
		    }
		  }).on('fileuploadfail', function(e,data) {
		      console.log(e);
		      jQuery.each(data.files, function(index, file) {
			  var error = jQuery('<span class=\"test-danger\"/>').text('File upload failed.');
			  console.log("error during file upload");
			  //jQuery(data.context.children()[index]).append('<br>').append(error);
		      });
		  }).prop('disabled', !jQuery.support.fileInput)
			.parent().addClass(jQuery.support.fileInput ? undefined : 'disabled');
		})(o);
	    }
	}
    });    
}

jQuery(document).ready(function() {

    getSessionNamesFromREDCap();

    createFileUploads();
    
    // add the session variables to the interface
    jQuery('#user_name').text("User: " + user_name);
    jQuery('#session-participant').val(subjid);
    jQuery('#session-name').val(session);
    jQuery('#session-run').val(run);
    
    storeSubjectAndName();
    
    checkConnectionStatus();
    // Disable for now: setInterval( checkConnectionStatus, 5000 );
    
    jQuery('#session-participant').change(function() {
	storeSubjectAndName();
    });
    jQuery('#session-name').change(function() {
	storeSubjectAndName();
    });
    jQuery('#session-run').change(function() {
	storeSubjectAndName();
    });
    
    jQuery('#session-date-picker').datetimepicker({language: 'en', format: "MM/DD/YYYY" });    
    jQuery('#session-date-picker').data("DateTimePicker").setDate(new Date());
});
