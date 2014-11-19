<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		DSPMXDG.php
//	Program Title:		DSPMXDG
//	Created by:			Owner
//	Template name:		Record Listing.tpl
//	Purpose:        
//	Program Modifications:

// DB Connection code
require('/esdi/websmart/v8.9/include/xl_functions001.php');
$options = array('i5_naming' => DB2_I5_NAMING_ON);

global $db2conn;
$db2conn = xl_db2_connect($options);

if(!$db2conn)
{
	die('Failed to connect to database!');
}
// Global variable for calculated fields	


// Global variables should be defined here
global $username, $firstnm, $lastnm, $seclevel;
global $member, $systemname, $BG_color;

// Get session variables
getsession();

// get input from browser
if (isset($_REQUEST['CHKSYSKEY']))
	$member = strtoupper($_REQUEST['CHKSYSKEY']); 
// Background color
if (isset($_REQUEST['BG']))
	$BG_color = strtoupper($_REQUEST['BG']); 
if ($BG_color == null) $BG_color = '#CCCCCC';

// Check WEBCOP
webcop('DASHBOARD', $username, '2');

// Get System Name
getsystemname();

// As a default task for this program, execute the display function
if ($pf_task == 'default')
{
	display();
}

// close the database connection
db2_close($db2conn);   

/********************
 End of mainline code
 ********************/
function display()
{ 
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	// Create Alias
	$query = "CREATE ALIAS QTEMP/$member FOR DASHBOARD/DSPMXDG($member)";
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		die("<b>Create Alias Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	
	// Select Records
	$query = "select DGDFN, DGSYS1, DGSYS2, FEHLDERR, OBJERR, FENOTJRNS, FENOTJRNT, OBJDELAY, DBSNDSTS, OBJSNDSTS, DBAPYSTS, OBJAPYSTS, DGSTS, SRCSYSSTS, TGTSYSSTS  from QTEMP/$member order by DGDFN";
	
	// Fetch rows for page: relative to initial cursor 
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		die("<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	// Output header
	wrtseg("ListHeader"); 
	
	while ($row = db2_fetch_assoc($stmt))
	{
		// set color of the line
		xl_set_row_color('altcol1', 'altcol2');
		
		
		$DGDFN = $row['DGDFN'];
		$DGSYS1 = $row['DGSYS1'];
		$DGSYS2 = $row['DGSYS2'];
		$FEHLDERR = $row['FEHLDERR'];
		$OBJERR = $row['OBJERR'];
		$FENOTJRNS = $row['FENOTJRNS'];
		$FENOTJRNT = $row['FENOTJRNT'];
		$OBJDELAY = $row['OBJDELAY'];
		$DBSNDSTS = $row['DBSNDSTS'];
		$OBJSNDSTS = $row['OBJSNDSTS'];
		$DBAPYSTS = $row['DBAPYSTS'];
		$OBJAPYSTS = $row['OBJAPYSTS'];
		$DGSTS = $row['DGSTS'];
		$SRCSYSSTS = $row['SRCSYSSTS'];
		$TGTSYSSTS = $row['TGTSYSSTS'];
		
		// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
		sanitize_output('DATA');
		
		wrtseg("ListDetails");
		
	}
	
	wrtseg("ListFooter");
	
	// Drop Alias that was created
	$query = "DROP ALIAS QTEMP/$member";
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		die("<b>Drop Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
}


// Converts special characters in the output fields to their HTML entities. This will prevent most XSS attacks.
// When $type is 'FILTER' the variables holding the filter input will be sanitized which should be done before being displayed to the page.
// When $type is 'DATA' the variables holding field data will be sanitized which should be done before being displayed to the page.
function sanitize_output($type)
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	if($type == "DATA")
	{
	}
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

//===============================================================================
function getsystemname()
{ 
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	
	// Selection
	$query = "select upper(SYSTEM) as SYSTEM from DASHBOARD/FCHKSYS where CHKSYSKEY = '$member'";
	
	// Fetch rows for page: relative to initial cursor 
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		die("<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	if ($row = db2_fetch_assoc($stmt))
	{
		if ($username == 'DEMOUSER') { 
			$systemname = 'Demo - '.$member;
		} else {
			$systemname = trim($row['SYSTEM']);
		}
	}
	
}


function wrtseg($segment)
{
	// Make sure it's case insensitive
	$segment = strtolower($segment);

	// Make all global variables available locally
	foreach($GLOBALS as $arraykey=>$arrayvalue) {if($arraykey != "GLOBALS"){global $$arraykey;}}

	// Output the requested segment:

	if($segment == "listheader")
	{

		echo <<<SEGDTA
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta name="generator" content="WebSmart" />
    <meta http-equiv="Pragma" content="no-cache" />
    <title>DSPMXDG - MIMIX Datagroup Error</title>
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/Lincoln/css/print.css" media="print" />
    <script type="text/javascript" src="/websmart/v8.9/javascript/jquery.min.js"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    
    <script language="JavaScript">

	var TRchange_color = "#66FFCC"
	function TRmover(aa) {
		TRbgcolor = aa.style.backgroundColor;
	 	aa.style.backgroundColor = TRchange_color;
	}
	function TRmout(aa) {
 		aa.style.backgroundColor = TRbgcolor;
	}

    </script>


    <style type="text/css">
    <!--
    body {
    background-color: $BG_color;
    }
    -->
    </style>
  </head>
  
  <body>
    
    <div id="pagetitleborder"></div>
    &nbsp;&nbsp;&nbsp;&nbsp;<strong>MIMIX Datagroup Error</strong>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    System Name: <strong>$systemname</strong>
    
    <div id="contents">
      <table id="listtable" class="mainlist">
        <tr>
          
          <th width="60" nowrap="nowrap">DGDFN<br>
            Name</th>
          <th width="60" nowrap="nowrap">DGDFN<br>
            System<br>1</th>
          <th width="60" nowrap="nowrap">DGDFN<br>
            System<br>2</th>
          <th width="50" nowrap="nowrap">Files<br>
            Held for<br>Errors</th>
          <th width="50" nowrap="nowrap">Total<br>
            Objects<br>in<br>Error</th>
          <th width="50" nowrap="nowrap">Files<br>
            Not<br>Journaled<br>Source</th>
          <th width="50" nowrap="nowrap">Files<br>
            Not<br>Journaled<br>Target</th>
          <th width="50" nowrap="nowrap">Total<br>
            Objects<br>Delayed</th>
          <th width="60" nowrap="nowrap">DB<br>
            Send<br>Status</th>
          <th width="60" nowrap="nowrap">Object<br>
            Send<br>Status</th>
          <th width="60" nowrap="nowrap">DB<br>
            Apply<br>Status</th>
          <th width="60" nowrap="nowrap">Object<br>
            Apply<br>Status</th>
          <th width="60" nowrap="nowrap">Overall<br>
            DG<br />Status</th>
          <th width="60" nowrap="nowrap">Source<br>
            Manager<br>Summation</th>
          <th width="60" nowrap="nowrap">Target<br>
            Manager<br>Summation</th>
        </tr>
        
SEGDTA;
		return;
	}
	if($segment == "listdetails")
	{

		echo <<<SEGDTA
<tr class="$pf_altrowclr" onmouseover="TRmover(this);" onmouseout="TRmout(this);">
  <td class="text">$DGDFN</td>
  <td class="text"><div align="center">$DGSYS1</div></td>
  <td class="text"><div align="center">$DGSYS2</div></td>
  <td class="text right"><div align="center">$FEHLDERR</div></td>
  <td class="text right"><div align="center">$OBJERR</div></td>
  <td class="text right"><div align="center">$FENOTJRNS</div></td>
  <td class="text right"><div align="center">$FENOTJRNT</div></td>
  <td class="text right"><div align="center">$OBJDELAY</div></td>
  <td class="text"><div align="center">$DBSNDSTS</div></td>
  <td class="text"><div align="center">$OBJSNDSTS</div></td>
  <td class="text"><div align="center">$DBAPYSTS</div></td>
  <td class="text"><div align="center">$OBJAPYSTS</div></td>
  <td class="text"><div align="center">$DGSTS</div></td>
  <td class="text"><div align="center">$SRCSYSSTS</div></td>
  <td class="text"><div align="center">$TGTSYSSTS</div></td>
</tr>

SEGDTA;
		return;
	}
	if($segment == "listfooter")
	{

		echo <<<SEGDTA
</table>
</div>

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
	
global $ENTRYTSP,$DGDFN,$DGSYS1,$DGSYS2,$FEHLDERR,$OBJERR,$FENOTJRNS,$FENOTJRNT,$OBJDELAY,$DBSNDSTS,$OBJSNDSTS,$DBAPYSTS,$OBJAPYSTS,$DGSTS,$SRCSYSSTS,$TGTSYSSTS;
	global $pf_scriptname;
	$pf_scriptname = 'DSPMXDG.php';

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

$pf_liblLibs[1] = 'DASHBOARD';

    // Last Generated CRC: D2E38549 CDDAED0E 93968E3C 52570474
    // Path: C:\Program Files\ESDI\WebSmart\temp\Dashboard_www_websmart_htdocs_dashboard_PHW Filels_DSPMXDG.phw
}
?>