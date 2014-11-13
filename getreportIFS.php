<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		getreportIFS.php 
//	Program Title:		Get Report from IFS
//	Created by:			Ernie Paredes
//	Template name:		A Simple Page.tpl
//	Purpose:        
//	Program Modifications:

require('/esdi/websmart/v9.0/include/xl_functions001.php');

// Global variables should be defined here
global $username, $firstnm, $lastnm, $seclevel;
global $stmf;

// Get session variables
getsession();

// Check WEBCOP
webcop('DASHBOARD', $username, '2');


// get input from browser
if (isset($_REQUEST['stmf']))
	$stmf = strtoupper($_REQUEST['stmf']); 

// Check to make sure the folder \DASHBOARD is at the begining
if (substr($stmf,0,10) != '/DASHBOARD')  $stmf = '/DASHBOARD'.$stmf;

// Change to URL code
$stmf = urldecode($stmf);

// Retrieve the task (default to "default")
switch($pf_task)
{
	
	case 'PDF':
	pdf();
	break;
	
	case 'HTML':
	html();
	break;
	
	case 'TXT':
	txt();
	break;
	
}

echo "$pf_task";


//===============================================================================
function html()
{
	
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	$filename = "$stmf.".'html';
	
	$handle = @fopen($filename, "r");
	
	// Trap Error if the file is not found in IFS
	if (!$handle) {
		wrtseg('ErrorPage');
		die;
	}		
	
	$contents = fread($handle, filesize($filename));
	fclose($handle);
	
	
	echo $contents;
	
}


//===============================================================================
function pdf()
{
	
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	$path = "$stmf.".'pdf';
	header('Cache-Control:');
	header('Pragma:');
	header('Content-type: application/pdf');
	header('Content-Disposition: inline; filename="$path"');
	header('Content-Length: ' . filesize($path));
	readfile("$path");
	
}

//===============================================================================
function txt()
{
	
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	echo "<br>*** I don't like the way it looks, but I'll keep working on the TXT output.....Ernie<br><br><br>";
	
	
	$path = "$stmf.".'txt';
	header('Cache-Control:');
	header('Pragma:');
	header('Content-type: text/html; charset=utf-8');
	header('Content-Disposition: inline; filename="$path"');
	header('Content-Length: ' . filesize($path));
	readfile("$path");
	
}

//===============================================================================
function getsession()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if($arraykey[0]!='_' && $arraykey != 'GLOBALS')
			global $$arraykey;
	}
	
	// Retrieve the session information if it is set here...
	if(isset($_SESSION['DASHBOARD'])){
		$info = $_SESSION['DASHBOARD'];
		$username = $info['username'];
		$firstnm = $info['firstnm'];
		$lastnm = $info['lastnm'];
		$seclevel = $info['seclevel'];
	}
	else
	{
		header("Location: dashboard.php?task=off");
	}
}

//===============================================================================
function webcop($WebCopProgramName, $WebCopUser, $WebCopOption)
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if($arraykey[0]!='_' && $arraykey != 'GLOBALS')
			global $$arraykey;
	}
	
	include('Includes/WebCop.php');
	
	if ($WebCopOption == 'X') {	
		header("Location: dashboard.php?task=off");
		exit();
	}
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
    <title>Get Report from IFS</title>
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/print.css" media="print" />
  </head>
  <body>
    <div id="pagetitle" style="display:none;"> Get Report from IFS </div>
    
    
    <div id="contents"> 
      Enter Content Here
    </div>
    
    <!--------------- Begin Footer --------------->
    <!--------------- End Footer --------------->
    
  </body>
</html>
SEGDTA;
		return;
	}
	if($segment == "errorpage")
	{

		echo <<<SEGDTA
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
  </head>
  <body>
    <h2>** Error ***</h2>
    <h2>The report requested cannot be found. </h2>
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
	$pf_scriptname = 'getreportIFS.php';

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


    // Last Generated CRC: AB295510 718AB805 5F33FB75 BFB8F869
    // Path: C:\ESDI\WebSmart\temp\Development_www_websmart_htdocs_dashboard_PHW Filels_GETREPORTIFS.phw
}
?>