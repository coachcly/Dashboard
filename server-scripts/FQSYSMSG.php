<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		FQSYSMSG.php
//	Program Title:		QSYSMSG Critical Errors
//	Created by:			Ernie Paredes
//	Template name:		Record Listing.tpl
//	Purpose:        
//	Program Modifications:

// DB Connection code
require('/esdi/websmart/v9.0/include/xl_functions001.php');
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
global $CHKSYSKEY, $systemname;

// Get session variables
getsession();

// get input from browser
if (isset($_REQUEST['CHKSYSKEY']))
	$CHKSYSKEY = strtoupper($_REQUEST['CHKSYSKEY']); 

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
	
	$query = "select MSGDATE, MSGTIME, MSGID, SEV, MSGTYPE, MSG  
	from DASHBOARD/FQSYSMSG 
	where MSGKEY = '$CHKSYSKEY'
	order by MSGDATE desc, MSGTIME, MSGID, SEV";
	
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
		
		
		$MSGDATE = substr($row['MSGDATE'],0,4).'/'.substr($row['MSGDATE'],4,2).'/'.substr($row['MSGDATE'],6,4);
		$MSGTIME = '000000'.$row['MSGTIME'];
		$MSGTIME = substr($MSGTIME,-6,2).':'.substr($MSGTIME,-4,2).':'.substr($MSGTIME,-2,2);
		$MSGID = $row['MSGID'];
		$SEV = $row['SEV'];
		$MSGTYPE = $row['MSGTYPE'];
		$MSG = $row['MSG'];
		
		// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
		sanitize_output('DATA');
		
		wrtseg("ListDetails");
		
	}
	
	wrtseg("ListFooter");
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
	$query = "select upper(SYSTEM) as SYSTEM from DASHBOARD/FCHKSYS where CHKSYSKEY = '$CHKSYSKEY'";

	// Fetch rows for page: relative to initial cursor 
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		die("<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	if ($row = db2_fetch_assoc($stmt))
	{
		$systemname = trim($row['SYSTEM']);
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
    <title>QSYSMSG Critical Errors</title>
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/print.css" media="print" />
    <script type="text/javascript" src="/websmart/v9.0/javascript/jquery.min.js"></script>

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
	background-color: #CCCCCC;
	}
	-->
    </style>

  </head>
  <body>
    <div id="pagetitle" class="pagetitle"> QSYSMSG Critical Errors&nbsp;&nbsp;-&nbsp;&nbsp;System Name: $systemname</div>
    <div id="pagetitleborder"></div>
    
    <div id="contents">
      <table id="listtable" class="mainlist">
        <tr>
          
          <th width="80"><div align="center">Message<br>
          Date</div></th>
          <th width="60"><div align="center">Message<br>
          Time</div></th>
          <th width="70"><div align="center">Message<br>
          ID</div></th>
          <th width="20"><div align="center">Message<br>
          Severity</div></th>
          <th width="80"><div align="center">Message<br>
          Type</div></th>
          <th width="500"><div align="left">Message</div></th>
        </tr>
        
SEGDTA;
		return;
	}
	if($segment == "listdetails")
	{

		echo <<<SEGDTA
<tr class="$pf_altrowclr" onmouseover="TRmover(this);" onmouseout="TRmout(this);">
  <td nowrap="nowrap" class="text right"><div align="center">$MSGDATE</div></td>
  <td nowrap="nowrap" class="text right"><div align="center">$MSGTIME</div></td>
  <td nowrap="nowrap" class="text"><div align="center">$MSGID</div></td>
  <td nowrap="nowrap" class="text"><div align="center">$SEV</div></td>
  <td nowrap="nowrap" class="text"><div align="center">$MSGTYPE</div></td>
  <td nowrap="nowrap" class="text">$MSG</td>
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
	
global $MSGKEY,$SYSTEM,$MSGDATE,$MSGTIME,$MSGID,$SEV,$MSGTYPE,$MSG;
	global $pf_scriptname;
	$pf_scriptname = 'FQSYSMSG.php';

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

    // Last Generated CRC: 323A37EC C83384DA DFE3DD16 283BE9D0
    // Path: C:\Premise\WIP\FQSYSMSG.phw
}
?>