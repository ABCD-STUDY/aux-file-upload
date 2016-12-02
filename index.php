<?php
  session_start();

  include($_SERVER["DOCUMENT_ROOT"]."/code/php/AC.php");
  $user_name = check_logged(); /// function checks if visitor is logged.
  $admin = false;

  if ($user_name == "") {
    // user is not logged in
    return;
  } else {
    if ($user_name == "admin")
      $admin = true;
    echo('<script type="text/javascript"> user_name = "'.$user_name.'"; </script>'."\n");
    echo('<script type="text/javascript"> admin = '.($admin?"true":"false").'; </script>'."\n");
  }
  
  $permissions = list_permissions_for_user( $user_name );

  // find the first permission that corresponds to a site
  // Assumption here is that a user can only add assessment for the first site he has permissions for!
  $site = "";
  foreach ($permissions as $per) {
     $a = explode("Site", $per); // permissions should be structured as "Site<site name>"

     if (count($a) > 0) {
        $site = $a[1];
	break;
     }
  }
  if ($site == "") {
     echo (json_encode ( array( "message" => "Error: no site assigned to this user" ) ) );
     return;
  }

  // if there is a running session it would have the follow information
  $subjid = "";
  $sessionid = "";
  $run = "";
  if( isset($_SESSION['ABCD']) && isset($_SESSION['ABCD']['auxfileupload']) ) {
     if (isset($_SESSION['ABCD']['auxfileupload']['subjid'])) {
        $subjid  = $_SESSION['ABCD']['auxfileupload']['subjid'];
     }
     if (isset($_SESSION['ABCD']['auxfileupload']['sessionid'])) {
        $sessionid  = $_SESSION['ABCD']['auxfileupload']['sessionid'];
     }
     if (isset($_SESSION['ABCD']['auxfileupload']['run'])) {
        $run  = $_SESSION['ABCD']['auxfileupload']['run'];
     }
  }

  echo('<script type="text/javascript"> subjid  = "'.$subjid.'"; </script>'."\n");
  echo('<script type="text/javascript"> session = "'.$sessionid.'"; </script>'."\n");
  echo('<script type="text/javascript"> run     = "'.$run.'"; </script>'."\n");
  echo('<script type="text/javascript"> site    = "'.$site.'"; </script>'."\n");
?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>ABCD's Auxilary File Upload</title>

  <!-- Bootstrap Core CSS -->
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

  <!-- Custom CSS -->
  <!-- required for the date and time pickers -->
  <link href="css/bootstrap-datetimepicker.css" rel="stylesheet" type="text/css">

  <!-- <link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.6.0/fullcalendar.min.css' /> -->
  <!-- media="print" is required to display the fullcalendar header buttons -->
  <!-- <link rel='stylesheet' media='print' href='//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.6.0/fullcalendar.print.css' /> -->

  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/jquery.fileupload.css">
  <link rel="stylesheet" href="css/select2.min.css">

</head>

<body>

  <nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">Auxilary File Upload</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li class="active"><a href="/index.php" title="Back to report page">Report</a></li>
        <li class="active"><a href="Uploading fMRI Task Data.pdf" title="Help on upload file structure">Help Uploading fMRI Task Data</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="#" class="connection-status" id="connection-status">Connection Status</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span id="session-active">User</span> <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="#" id="user_name"></a></li>
            <li><a href="#" class="subject-id"></a></li>
            <li><a href="#" class="session-id"></a></li>
            <li><a href="#" class="run-id"></a></li>
            <li role="separator" class="divider"></li>
            <li><a href="#" onclick="closeSession();">Close Session</a></li>
            <li><a href="#" onclick="logout();">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

  <!-- start session button -->
  <section id="admin-top" class="bg-light-gray">
    <div class="container">
      <div class="row" style="margin-bottom: 20px;"></div>
      <div class="row start-page">
        <div class="col-md-12">
          <div class="date">Adolescent Brain Cognitive Development</div>
	  <div style='position: relative;'>
	    <h1>ABCD's Auxilary File Upload</h1>
	    <div class='date2'>June 2016</div>
	  </div>
	  <p>
	     Upload files that the ABCD study stores for each participant and imaging session. Valid participants and sessions have to be created in REDCap first (exist in your data access group and have been screened). In order to upload files for a participant, select the participant and session first, press Select files to add files for that participant.
	  </p>
        </div>
      </div>
      <div class="row" style="margin-bottom: 20px;"></div>
      <div class="row">
        <div class="col-md-12">

              <h2>Select a session</h2>
              <form name="sentMessage" id="sessionInfoForm" novalidate>
                <div class="col-md-6">

                  <div class="form-group">
                    <label for="session-participant" class="control-label">Participant ID (screened in REDCap)</label>
                    <!-- <input type="text" class="form-control" placeholder="NDAR-#####" id="session-participant" required data-validation-required-message="Please enter the participant NDAR ID." autofocus> -->
		    <select class="form-control" id="session-participant"></select>
                    <p class="help-block text-danger"></p>
                  </div>

                  <div class="form-group">
                    <label for="session-name" class="control-label" title="Should be Baseline (Year 1).">Event name</label>
                    <!-- <input type="text" class="form-control" placeholder="Baseline-01" id="session-name" required data-validation-required-message="Please enter the session ID."> -->
		    <select class="form-control" id="session-name"></select>
                    <p class="help-block text-danger"></p>
                  </div>

                  <div class="form-group">
                    <label for="session-run" class="control-label" title="Always start with the 01 event. Only in the rare case you need a second, or third run of the whole imaging session, select 02 and 03.">Event type</label>
		    <select class="form-control" id="session-run">
		      <option value="SessionA1"  title="T1, rsFMRI (2 runs), DTI, T2, rsfMRI (up to 2 runs)">A1</option>
		      <option value="SessionA2"  title="3 fMRI tasks">A2</option>
		      <option value="SessionB1"  title="T1, 3 fMRI tasks">B1</option>
		      <option value="SessionB2"  title="rsfMRI (2 runs), DTI, T2, rsfMRI (up to 2 runs)">B2</option>
		      <option value="SessionC"   title="Combined scan">C</option>
		    </select>		  
                    <p class="help-block text-danger"></p>
                  </div>

                  <div class="form-group">
                    <label for="session-date" class="control-label" title="Only if you enter a new dataset this date will be used to link to the scan date.">Session Date</label>
                    <div class='input-group date' id='session-date-picker'>
                      <input type='text' data-format="MM/dd/yyyy HH:mm:ss PP" id="session-date" class="form-control" placeholder="(TODO: Fill in with the current date)" />
                      <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                      </span>
                    </div>
                  </div>

                  <div class="clearfix"></div>
                </div>
              </form>
	</div>
      </div>
      <div class="row">
        <div class="col-md-12">

	      <div id="upload-objects">

	      </div>

        </div>
      </div>
      <div class="row">
         &nbsp;
      </div>
      <div class="row">
        <div class="col-md-3">
	   <button id="download-as-zip" class="btn btn-primary">Download site copy</button>
        </div>
        <div class="col-md-9">
	   <p>Data is stored at the DAIC immediately after each upload. You may download a copy of the uploaded data for the current participant.</p>
	</div>
      </div>
      <div class="row">
        <div class="col-md-12">
            <hr>
	    <span style="color: gray;"><i>A service provided by the Data Analysis and Informatics Core of the Adolescent Brain Cognitive Development Study.</i></span>
	</div>
      </div>
      <div class="row">
        <div class="col-md-12">&nbsp;</div>
      </div>
    </div>
  </section>

  <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
  <script src='js/moment.min.js'></script>
  <script src="js/jquery.iframe-transport.js"></script>
  <!-- The basic File Upload plugin -->
  <script src="js/jquery.fileupload.js"></script>

  <!-- Bootstrap Core JavaScript -->
  <script src="js/bootstrap.min.js"></script>

  <script src="js/bootstrap-datetimepicker.js"></script>
  <script src="js/bootstrap-datepicker.js"></script>
  <script src="js/select2.full.min.js"></script>


  <script type="text/javascript" src="js/all.js"></script>

</body>

</html>
