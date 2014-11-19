<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		FREPORT.php
//	Program Title:		Reports
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
	
	$query = "select REPORTKEY, SYSTEM, QDATETIME, BASESTMF, SPLFTXT, AVLTXT, AVLPDF, AVLHTML  
	from DASHBOARD/FREPORT 
	where REPORTKEY = '$CHKSYSKEY'
	order by SPLFTXT";
	
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
		
		
		$REPORTKEY = $row['REPORTKEY'];
		$SYSTEM = $row['SYSTEM'];
		//$QDATETIME = $row['QDATETIME'];
		$QDATETIME = substr($row['QDATETIME'],0,4).'/'.substr($row['QDATETIME'],4,2).'/'.substr($row['QDATETIME'],6,2)."&nbsp;&nbsp;&nbsp;&nbsp;".substr($row['QDATETIME'],8,2).':'.substr($row['QDATETIME'],10,2).':'.substr($row['QDATETIME'],12,2);
		$BASESTMF = trim($row['BASESTMF']);
		$BASESTMF =	urlencode($BASESTMF);
		$SPLFTXT = $row['SPLFTXT'];
		$AVLTXT = $row['AVLTXT'];
		$AVLPDF = $row['AVLPDF'];
		$AVLHTML = $row['AVLHTML'];
		
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
			//	$BASESTMF = htmlspecialchars($BASESTMF, ENT_QUOTES);
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
    <title>Reports</title>
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
    <div id="pagetitle" class="pagetitle">
      <div id="pagetitle2" class="pagetitle">Cache Battery Status&nbsp;&nbsp;-&nbsp;&nbsp;System Name: $systemname</div>
    </div>
  <div id="pagetitleborder"></div>
    
    <div id="contents">
      <table align="center" class="mainlist" id="listtable">
        <tr>
          
          <th width="300" height="32" valign="middle"><div align="left">Report Description</div></th>
          <th width="200" valign="middle">Date &amp; Time Created</th>
          <th colspan="2" valign="middle">Formats<br>Available</th>
        </tr>
        
SEGDTA;
		return;
	}
	if($segment == "listdetails")
	{

		echo <<<SEGDTA
<tr class="$pf_altrowclr" onmouseover="TRmover(this);" onmouseout="TRmout(this);">
  <td valign="middle" nowrap="nowrap" class="text">$SPLFTXT</td>
  <td valign="middle" nowrap="nowrap" class="text"><div align="center">$QDATETIME</div></td>
  <td width="40" valign="middle" nowrap="nowrap" class="text"><div align="center">
  	<a href="getreportifs.php?task=PDF&stmf=$BASESTMF" target="_blank">
      
SEGDTA;
 if ($AVLPDF == '*YES') echo '<img src="images/pdf_icon.png" title="PDF Format" height="20">'; 
		echo <<<SEGDTA

    </a>  
    </div></td>
  <td width="40" valign="middle" nowrap="nowrap" class="text"><div align="center">
  	<a href="getreportifs.php?task=HTML&stmf=$BASESTMF" target="_blank">
      
SEGDTA;
 if ($AVLHTML == '*YES') echo '<img src="images/html_icon.png" title="HTML Format" height="20">'; 
		echo <<<SEGDTA

    </a>  
    </div></td>
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
	
global $REPORTKEY,$SYSTEM,$QDATETIME,$SPLF,$JOB,$USER,$NBR,$SPLNBR,$BASESTMF,$SPLFTXT,$AVLTXT,$AVLPDF,$AVLHTML;
	global $pf_scriptname;
	$pf_scriptname = 'FREPORT.php';

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

    // Last Generated CRC: 9AFD1D33 C6E49677 43170AD4 4CFF188F
    // Path: C:\Program Files\ESDI\WebSmart\temp\Dashboard_www_websmart_htdocs_dashboard_PHW Filels_FREPORT.phw
}
?>