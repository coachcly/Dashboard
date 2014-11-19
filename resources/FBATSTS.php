<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		FBATSTS.php
//	Program Title:		Battery Stats
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
	
	$query = "select RESOURCE, SRLNBR, IOATYPMDL, FRAMEID, CARDSLOT, BATLOC, BATTYP, BATSTS, WRNDAYS, WRNDATE, ERRDAYS, ERRDATE, CONMAINT, SAFELYRPLD  
				from DASHBOARD/FBATSTS 
				where BATSTSKEY = '$CHKSYSKEY'";
	
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
		
		
		$RESOURCE = $row['RESOURCE'];
		$SRLNBR = $row['SRLNBR'];
		$IOATYPMDL = $row['IOATYPMDL'];
		$FRAMEID = $row['FRAMEID'];
		$CARDSLOT = $row['CARDSLOT'];
		$BATLOC = $row['BATLOC'];
		$BATTYP = $row['BATTYP'];
		$BATSTS = $row['BATSTS'];
		$WRNDAYS = $row['WRNDAYS'];
		$WRNDATE = $row['WRNDATE'];
		$ERRDAYS = $row['ERRDAYS'];
		$ERRDATE = $row['ERRDATE'];
		$CONMAINT = $row['CONMAINT'];
		$SAFELYRPLD = $row['SAFELYRPLD'];
		
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
		if ($username == 'DEMOUSER') { 
			$systemname = 'Demo - '.$CHKSYSKEY;
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
    <title>Battery Stats</title>
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
    <div id="pagetitle" class="pagetitle">Cache Battery Status&nbsp;&nbsp;-&nbsp;&nbsp;System Name: $systemname</div>
    <div id="pagetitleborder"></div>
    
    <div id="contents">
      <table id="listtable" class="mainlist">
        <tr>
          
          <th width="60" nowrap="nowrap">Resource</th>
          <th width="90" nowrap="nowrap">Serial<br>Number</th>
          <th width="70" nowrap="nowrap">IOA<br>Type-Model</th>
          <th width="50" nowrap="nowrap">Frame<br>ID</th>
          <th width="50" nowrap="nowrap">Card<br>Slot</th>
          <th width="60" nowrap="nowrap">Frame ID<br>-<br>Card<br>Position</th>
          <th width="150" nowrap="nowrap">Battery<br>Type</th>
          <th width="250" nowrap="nowrap">Battery<br>Status</th>
          <th width="40" nowrap="nowrap">Battery<br>Warning<br>(Days)</th>
          <th width="80" nowrap="nowrap">Estimated<br>Warning<br>Date</th>
          <th width="40" nowrap="nowrap">Battery<br>Error<br>(Days)</th>
          <th width="60" nowrap="nowrap">Estimated<br>Error<br>Date</th>
          <th width="50" nowrap="nowrap">Conc.<br>Maint.<br>Battery<br>Pack</th>
          <th width="60" nowrap="nowrap">Battery<br>Pack<br>can be<br>Safely<br>Replaced</th>
        </tr>
        
SEGDTA;
		return;
	}
	if($segment == "listdetails")
	{

		echo <<<SEGDTA
<tr class="$pf_altrowclr" onmouseover="TRmover(this);" onmouseout="TRmout(this);">
  <td class="text"><div align="center">$RESOURCE</div></td>
  <td class="text"><div align="center">$SRLNBR</div></td>
  <td class="text"><div align="center">$IOATYPMDL</div></td>
  <td class="text"><div align="center">$FRAMEID</div></td>
  <td class="text"><div align="center">$CARDSLOT</div></td>
  <td class="text"><div align="center">$BATLOC</div></td>
  <td class="text">$BATTYP</td>
  <td class="text">$BATSTS</td>
  <td class="text right"><div align="center">$WRNDAYS</div></td>
  <td class="text right"><div align="center">$WRNDATE</div></td>
  <td class="text right"><div align="center">$ERRDAYS</div></td>
  <td class="text right"><div align="center">$ERRDATE</div></td>
  <td class="text"><div align="center">$CONMAINT</div></td>
  <td class="text"><div align="center">$SAFELYRPLD</div></td>
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
	
global $BATSTSKEY,$SYSTEM,$RESOURCE,$SRLNBR,$IOATYPMDL,$FRAMEID,$CARDSLOT,$BATLOC,$BATTYP,$BATSTS,$WRNDAYS,$WRNDATE,$ERRDAYS,$ERRDATE,$CONMAINT,$SAFELYRPLD;
	global $pf_scriptname;
	$pf_scriptname = 'FBATSTS.php';

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

    // Last Generated CRC: 56A6D374 231999B4 78D24977 9490FCBB
    // Path: C:\Program Files\ESDI\WebSmart\temp\Dashboard_www_websmart_htdocs_dashboard_PHW Filels_FBATSTS.phw
}
?>