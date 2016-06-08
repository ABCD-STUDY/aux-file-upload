<?php
/*
 * jQuery File Upload Plugin PHP Example
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

session_start();

error_reporting(E_ALL | E_STRICT);
require('UploadHandler.php');

  include($_SERVER["DOCUMENT_ROOT"]."/code/php/AC.php");
  $user_name = check_logged(); /// function checks if user is logged in

  if (!$user_name || $user_name == "") {
     echo (json_encode ( array( "message" => "no user name" ) ) );
     return; // nothing
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

  // Both the subject id and the visit (session) are used to make the assessment unique
   $subjid = "";
   $sessionid = "";
   $active_substances = array();
   if ( isset($_SESSION['ABCD']) && isset($_SESSION['ABCD']['aux-file-upload']) ) {
      if (isset($_SESSION['ABCD']['aux-file-upload']['subjid'])) {  
         $subjid  = $_SESSION['ABCD']['aux-file-upload']['subjid'];
      }
      if (isset($_SESSION['ABCD']['aux-file-upload']['sessionid'])) {
         $sessionid  = $_SESSION['ABCD']['aux-file-upload']['sessionid'];
      }      
      if (isset($_SESSION['ABCD']['aux-file-upload']['run'])) {
         $run  = $_SESSION['ABCD']['aux-file-upload']['run'];
      }      
   }
   if ($subjid == "") {
     echo(json_encode ( array( "message" => "Error: no subject id assigned" ) ) );
     return;
   }
   if ($sessionid == "") {
     echo(json_encode ( array( "message" => "Error: no session specified" ) ) );
     return;
   }
   if ($run == "") {
     echo(json_encode ( array( "message" => "Error: no run specified" ) ) );
     return;
   }

  // this event will be saved at this location
  $events_file = $_SERVER['DOCUMENT_ROOT']."/applications/aux-file-upload/data/" . $site . "/afu_".$subjid."_".$sessionid."_".$run.".file_".$_POST['type'];
  $dd = $_SERVER['DOCUMENT_ROOT']."/applications/aux-file-upload/data/" . $site;
  if (!is_dir($dd)) {
    mkdir($dd, 0777);
  }
  $options = array(
      'param_name' => $_POST['param_name'],
      'upload_dir' => $events_file,
      'upload_url' => "data/" . $site . "/afu_".$subjid."_".$sessionid."_".$run.".file"
  );
  $error_messages = array();
  $upload_handler = new UploadHandler($options, true, $error_messages);

?>