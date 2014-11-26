<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		contruction.php 
//	Program Title:		Under Construction
//	Created by:			Ernie Paredes
//	Template name:		A Simple Page.tpl
//	Purpose:        
//	Program Modifications:

require('/esdi/websmart/v9.0/include/xl_functions001.php');

// Retrieve the task (default to "default")
if ($pf_task == 'default')
{
	generic();
}

function generic()
{
	wrtseg('MainSeg');
}


function wrtseg($segment)
{
	// Make sure it's case insensitive
	$segment = strtolower($segment);

	// Make all global variables available locally
	foreach($GLOBALS as $arraykey=>$arrayvalue) {if($arraykey != "GLOBALS"){global $$arraykey;}}

	// Output the requested segment:

	if($segment == "mainseg")
	{

		echo <<<SEGDTA
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta name="generator" content="WebSmart" />
    <meta http-equiv="Pragma" content="no-cache" />
    <title>Under Construction</title>
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/print.css" media="print" />
    <script type="text/javascript" src="/websmart/v8.9/javascript/jquery.min.js"></script>
    <style type="text/css">
    <!--
	body {
	background-image: url(images/back_grey.jpg);
	}
.style1 {color: #0000FF}
	-->
    </style>

  </head>
  <body>

    
    
SEGDTA;
 include('Includes/Header.php'); 
		echo <<<SEGDTA

    
SEGDTA;
 include('Includes/menu.php'); 
		echo <<<SEGDTA


<center>
  <h2><br />
    <br />
    <br />
      <strong>You have reached a Page that is still &quot;Under Construction&quot;.</strong><br />
    <br />
    <br />
    Click on "<span class="style1"><a href="DashboardMain.php">Home / Refresh</a></span>" to return. </h2>
</center>      

    <!--------------- Begin Footer --------------->
    <!--------------- End Footer --------------->
    
  </body>
</html>
SEGDTA;
		return;
	}

	// If we reach here, the segment is not found
	echo("Segment $segment is not defined! ");
}

function internal_init()
{
	
	global $pf_scriptname;
	$pf_scriptname = 'contruction.php';

	session_start();

	global $pf_task;
	if(isset($_REQUEST['task']))
	{
		$pf_task = $_REQUEST['task'];
	}
	else
	{
		$pf_task = 'default';
	}
	
	// this is an array
	global $pf_liblLibs;


    // Last Generated CRC: 129DEB46 96385ED7 8537C09F 11CB7C29
    // Path: C:\Premise\WIP\contruction.phw
}
?>