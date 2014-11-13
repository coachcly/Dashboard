<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		DBCPUusagedetail.php
//	Program Title:		Detail
//	Created by:			Ernie Paredes
//	Template family:	Lincoln
//	Template name:		Page at a Time (by Key).tpl
//	Purpose:        	Maintain a database file using embedded SQL. Supports options for add, change, delete and display. 
//	Program Modifications:

// DB Connection code
require('/esdi/websmart/v9.0/include/xl_functions001.php');
$options = array('i5_naming' => DB2_I5_NAMING_ON);

global $db2conn;
$db2conn = xl_db2_connect($options);

if(!$db2conn)
{
	die('Could not connect to database: ' . db2_conn_error());
}

// Global variables should be defined here
global $username, $firstnm, $lastnm, $seclevel;
global $ww_ordby, $ww_orddir, $ww_page, $ww_nx, $ww_prevpage, $ww_nextpage, $ww_listsize, $ww_whrclause, $ww_selstring, $ww_program_state, $ww_count;
global $SBSJOB, $member, $systemname, $datein, $timein, $dayofweek, $datetime, $date_back;

// Get session variables
getsession();

// Check WEBCOP
webcop('DASHBOARD', $username, '2');

// Set maximum list size to 25 for this program
$ww_listsize = 25;

// Initialize the previous page and count of records
$ww_prevpage = 0;
$ww_count = 0;

// Create random field to avoid caching
$rnd = rand(0, 99999); 

// retrieve the  last state of the list: order-by column and direction (ascend/descend). 
if(isset($_SESSION[$pf_scriptname]))
	$ww_program_state = $_SESSION[$pf_scriptname];
if (isset($ww_program_state['ww_orddir']))
	$ww_orddir = $ww_program_state['ww_orddir'];
if (isset($ww_program_state['ww_ordby']))
	$ww_ordby = $ww_program_state['ww_ordby'];
if (isset($ww_program_state['ww_page']))
	$ww_page = $ww_program_state['ww_page'];

// Get values from Browser
if (isset($_REQUEST['CHKSYSKEY']))
	$member = strtoupper($_REQUEST['CHKSYSKEY']); 
if (isset($_REQUEST['date']))
	$datein = $_REQUEST['date']; else $datein = 0;
if (isset($_REQUEST['time']))
	$timein = $_REQUEST['time']; else $timein = 0;

// Day of week
$dayofweek = date('l',strtotime($datein));
// Date Time Display
$datetime = substr($datein,4,2).'/'.substr($datein,6,2).'/'.substr($datein,0,4).' '.substr('000000'.$timein,-6,2).':'.substr('000000'.$timein,-4,2);
$date_back = urlencode( substr($datein,4,2).'/'.substr($datein,6,2).'/'.substr($datein,0,4) );

// Get System Name
getsystemname();

// run the specified task
switch($pf_task)
{
	case 'default':
	getPFRSTSC();
	display();
	break;
}

//Release the database resource
db2_close($db2conn);
/********************
 End of mainline code
 ********************/

// Load first page and use ordby parameter from form to determine new sort order, direction 
function display()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey[0]!='_' && $arraykey != "GLOBALS")
			global $$arraykey;
	}
	
	
	
	// Retrieve or set the page to list
	$pagenum = 1;
	$pagenum = (int) xl_get_parameter('page');
	if($pagenum == 0)
		$pagenum = 1;
	
	// Calculate next and previous page number
	$ww_prevpage = $pagenum - 1;
	$ww_nextpage = $pagenum + 1;
	
	// Compute table row cursor offset, offset starts at 0
	$ww_nx = $pagenum * $ww_listsize;
	
	// Build select string for SQL exec
	bldselstr();    
	
	// Store the last used order-by settings: 
	$ww_program_state['ww_orddir'] = $ww_orddir;
	$ww_program_state['ww_ordby'] = $ww_ordby;
	$ww_program_state['ww_page'] = $pagenum;
	$_SESSION[$pf_scriptname] = $ww_program_state;
	
	// Build first page of table rows
	bldpage();    
}

// Build current page of rows up to listsize. 
function bldpage()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey[0]!='_' && $arraykey != "GLOBALS")
			global $$arraykey;
	}
	
		// Create Alias
	$query = "CREATE ALIAS QTEMP/$member FOR DASHBOARD/FPFRSTSJ($member)";
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   

		wrtseg('NoMember');
		die;
		//die("<b>Create Alias Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	// Converts special characters in the filter fields to their HTML entities. This will prevent most XSS attacks
	sanitize_output('FILTER');
	
	// Output page and list header
	wrtseg('ListHeader');
	
	// Fetch rows for page: relative to initial cursor 
	$ww_selstring = $ww_selstring." FETCH FIRST $ww_nx ROWS ONLY";
	if (!($stmt = db2_exec($db2conn, $ww_selstring, array('CURSOR' => DB2_SCROLLABLE)))) 
	{
		db2_close($db2conn);
		die("<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	// While SQL retrieves records and show them
	$index = $ww_nx - $ww_listsize + 1;
	while ($row = db2_fetch_assoc($stmt, $index))
	{
		// set color of the line
		xl_set_row_color('altcol1', 'altcol2');
		
		// Get the fields 
		$SYSSTSDATE = trim($row['SYSSTSDATE']);
		$SYSSTSTIME = trim($row['SYSSTSTIME']);
		$INTRECNBR = trim($row['INTRECNBR']);
		$SBS = trim($row['SBS']);
		$JOB = trim($row['JOB']);
		$JOBUSER = trim($row['JOBUSER']);
		$JOBNBR = trim($row['JOBNBR']);
		$JOBTYPE = trim($row['JOBTYPE']);
		$JOBPOOL = trim($row['JOBPOOL']);
		$JOBRUNPTY = trim($row['JOBRUNPTY']);
		$JOBCPUTIME = trim($row['JOBCPUTIME']);
		$JOBTEMPSTG = trim($row['JOBTEMPSTG']);
		$JOBAUXIO = trim($row['JOBAUXIO']);
		$JOBTHREADS = trim($row['JOBTHREADS']);
		$JOBCPUPRC = trim($row['JOBCPUPRC']);
		$JOBFNC = trim($row['JOBFNC']);
		$JOBSTATUS = trim($row['JOBSTATUS']);
		$CURUSER = trim($row['CURUSER']);
		
		if ($SBS == '*NONE') {
			$SBSJOB = $JOB;
		} else {
			$SBSJOB = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$JOB;
		}
		
		// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
		sanitize_output('DATA');
		
		wrtseg('ListDetails');
		$index++;
	}
	
	// test for more records
	$ww_count = $index - ($ww_nx - $ww_listsize) - 1;                               
	
	// show the footer
	wrtseg('ListFooter');  
	
}

// Build SQL Select string: 
function bldselstr()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey[0]!='_' && $arraykey != "GLOBALS")
			global $$arraykey;
	}
	
	// Date to use
	if ($timein != 1) {
		$date_use = $datein;
	} else {
		$date_use = strtotime ( '+1 day' , strtotime(substr($datein,0,4).'-'.substr($datein,4,2).'-'.substr($datein,6,2)) ) ;
		$date_use = date ( 'Ymd' , $date_use );
	}
	
	$ww_selstring = "SELECT 
		SYSSTSDATE, 
		SYSSTSTIME, 
		INTRECNBR, 
		SBS, 
		JOB, 
		JOBUSER, 
		JOBNBR, 
		JOBTYPE, 
		JOBPOOL, 
		JOBRUNPTY, 
		JOBCPUTIME, 
		JOBTEMPSTG, 
		JOBAUXIO, 
		JOBTHREADS, 
		JOBCPUPRC, 
		JOBFNC, 
		JOBSTATUS, 
		CURUSER 
		FROM QTEMP/$member
		WHERE SYSSTSDATE = $date_use
		AND SYSSTSTIME = $timein"; 
	
	
	/**** Build 'order-by' clause ****/
	
	// If a column header was clicked, set the order by field to 
	// the correct column and control ascending and descending order
	// Check for a sort request
	if (isset($_REQUEST['ordby']))
	{
		$ordby = xl_get_parameter('ordby');  
		
		// If we previously sorted on this column, then reverse the order of the sort: 
		if ($ordby == $ww_ordby) 
		{
			if ($ww_orddir == 'A') 
				$ww_orddir = 'D';
			else 
				$ww_orddir = 'A';
		}       
		else
		{
			// Save last used column for sort.
			$ww_ordby = $ordby;           
			
			// Ascending order
			$ww_orddir ='A';    
		}
	}
	
	// If a sort-by column exists then use that to build the order-by
	if ($ww_ordby <> "")
	{
		$ww_selstring = trim($ww_selstring) . ' order by ' . $ww_ordby; 
		
		// If descending order: 
		if ($ww_orddir == 'D') 
			$ww_selstring = trim($ww_selstring) . ' DESC'; 
	}
	else 
	{
		// Otherwise just use the default order by
		$ww_selstring = trim($ww_selstring) . ' order by SYSSTSDATE , SYSSTSTIME, INTRECNBR';
	}
}                         


//===============================================================================
function getPFRSTSC()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if($arraykey[0]!='_' && $arraykey != 'GLOBALS')
			global $$arraykey;
	}
	
		// Create Alias
	$query = "CREATE ALIAS QTEMP/FPFRSTSC FOR DASHBOARD/FPFRSTSC($member)";
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		wrtseg('NoMember');
		die;
		//die("<b>Create Alias Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}

	
	// Date to use
	if ($timein != 1) {
		$date_use = $datein;
	} else {
		$date_use = strtotime ( '+1 day' , strtotime(substr($datein,0,4).'-'.substr($datein,4,2).'-'.substr($datein,6,2)) ) ;
		$date_use = date ( 'Ymd' , $date_use );
	}
	
	
	$query = "SELECT * 
	FROM QTEMP/FPFRSTSC
	WHERE SYSSTSDATE = $date_use
	AND SYSSTSTIME = $timein"; 
	
	// Fetch rows for page: relative to initial cursor 
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		echo "<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"; 
		die;
	}
	
	while ($row = db2_fetch_assoc($stmt))
	{
		$CPUUSED = $row['CPUUSED'];
		$DBUSED = $row['DBUSED'];
		$JOBUSED = $row['JOBUSED'];
		$TASKUSED = $row['TASKUSED'];
		$JOBSINSYS = number_format($row['JOBSINSYS']);
		
	}
	
}

//===============================================================================
// Converts special characters in the output fields to their HTML entities. This will prevent most XSS attacks.
// When $type is 'FILTER' the variables holding the filter input will be sanitized which should be done before being displayed to the page.
// When $type is 'DATA' the variables holding field data will be sanitized which should be done before being displayed to the page.
function sanitize_output($type)
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey[0]!='_' && $arraykey != "GLOBALS")
			global $$arraykey;
	}
	
	if($type == "FILTER")
	{
	}
	else if($type == "DATA")
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
    <title>Work with Performance Status - Historical Work Active Job</title>
    
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/Lincoln/css/print.css" media="print" />
    <script type="text/javascript" src="/websmart/v8.9/javascript/jquery.min.js"></script>
    <script type="text/javascript">
		//focus the first input on page load
		jQuery(document).ready( function()
		{
			jQuery('input[disabled=false]:first').focus();
		});
	</script>

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
.style1 {color: #0000FF}
.style2 {color: #0000FF; font-weight: bold; }
-->
    </style>
</head>
  
  <body  background="images/back_grey.jpg">
  

  
    <div id="pagetitle" class="pagetitle">Work with Performance Status   -   Historical Work Active Job</div>
    <div id="pagetitleborder"></div>
    
    <table width="700" border="0">
  <tr>
    <td width="179"><div align="right"><strong>Job %:</strong></div></td>
    <td width="60"><div align="right" class="style2">$JOBUSED</div></td>
    <td colspan="2"><div align="right"><strong>System Activity as of:<span class="style1"> $dayofweek, $datetime</span></strong></div></td>
  </tr>
  <tr>
    <td><div align="right"><strong>DB %:</strong></div></td>
    <td><div align="right" class="style2">$DBUSED</div></td>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td><div align="right"><strong>Task %:</strong></div></td>
    <td><div align="right" class="style2"><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$TASKUSED</u></div></td>
    <td><div align="right"><strong>System Name: </strong></div></td>
    <td><strong><span class="style1">$systemname</span></strong></td>
  </tr>
  <tr>
    <td><div align="right"><strong>CPU %:</strong></div></td>
    <td><div align="right" class="style2">$CPUUSED</div></td>
    <td width="342"><div align="right"><strong>Active System Jobs: </strong></div></td>
    <td width="101"><strong> <span class="style1">$JOBSINSYS</span></strong></td>
  </tr>
</table>
<br />
<div id="contents">
      <button onClick="javascript:location.href='DBCPUusage.php?CHKSYSKEY=$member&datein=$date_back';">Go Back</button><br><br>
      <div id="listtopcontrol">
        <a id="prevlinktop" class="prevlink nondisp">Previous $ww_listsize</a>
        
        <a id="nextlinktop" class="nextlink nondisp">Next $ww_listsize</a>
      </div> 
      <table id="listtable" class="mainlist">
        <tr valign="middle">
          
          <th width="100" nowrap="nowrap"><a href="$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&ordby=INTRECNBR&rnd=$rnd">Subsystem/Job</a></th>
          <th width="80" nowrap="nowrap"><a href="$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&ordby=JOBUSER&rnd=$rnd">User</a></th>
          <th width="50" nowrap="nowrap"><div align="right"><a href="$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&ordby=JOBNBR&rnd=$rnd">Number</a></div></th>
          <th width="50" nowrap="nowrap"><div align="right"><a href="$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&ordby=JOBTYPE&rnd=$rnd">Type</a></div></th>
          <th width="50" nowrap="nowrap"><div align="right"><a href="$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&ordby=JOBPOOL&rnd=$rnd">Pool</a></div></th>
          <th width="50" nowrap="nowrap"><div align="right"><a href="$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&ordby=JOBRUNPTY&rnd=$rnd">Priority</a></div></th>
          <th width="75" nowrap="nowrap"><div align="right"><a href="$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&ordby=JOBCPUTIME&rnd=$rnd">CPU Time<br>
          in Seconds</a></div></th>
          <th width="70" nowrap="nowrap"><div align="right"><a href="$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&ordby=JOBTEMPSTG&rnd=$rnd">Temporary<br>
          Storage</a></div></th>
          <th width="60" nowrap="nowrap"><div align="right"><a href="$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&ordby=JOBAUXIO&rnd=$rnd">Auxilory<br>
          I/O</a></div></th>
          <th width="60" nowrap="nowrap"><div align="right"><a href="$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&ordby=JOBTHREADS&rnd=$rnd">Threads</a></div></th>
          <th width="60" nowrap="nowrap"><div align="right"><a href="$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&ordby=JOBCPUPRC&rnd=$rnd">CPU %</a>&nbsp;&nbsp;</div></th>
          <th width="130" nowrap="nowrap"><a href="$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&ordby=JOBFNC&rnd=$rnd">Function</a></th>
          <th width="60" nowrap="nowrap"><a href="$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&ordby=JOBSTATUS&rnd=$rnd">Status</a></th>
          <th width="75" nowrap="nowrap"><a href="$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&ordby=CURUSER&rnd=$rnd">Current<br>
          User</a></th>
        </tr>
        
SEGDTA;
		return;
	}
	if($segment == "listdetails")
	{

		echo <<<SEGDTA

<tr class="$pf_altrowclr" onmouseover="TRmover(this);" onmouseout="TRmout(this);">
  
  <td>$SBSJOB</td>
  <td>$JOBUSER</td>
  <td><div align="right">$JOBNBR</div></td>
  <td><div align="right">$JOBTYPE</div></td>
  <td><div align="right">$JOBPOOL</div></td>
  <td><div align="right">$JOBRUNPTY</div></td>
  <td><div align="right">$JOBCPUTIME</div></td>
  <td><div align="right">$JOBTEMPSTG</div></td>
  <td><div align="right">$JOBAUXIO</div></td>
  <td><div align="right">$JOBTHREADS</div></td>
  <td><div align="right">$JOBCPUPRC&nbsp;&nbsp;</div></td>
  <td>$JOBFNC</td>
  <td>$JOBSTATUS</td>
  <td>$CURUSER</td>
</tr>

SEGDTA;
		return;
	}
	if($segment == "listfooter")
	{

		echo <<<SEGDTA
</table>

<div id="listbottomcontrol">
  <a id="prevlinkbot" class="prevlink nondisp">Previous $ww_listsize</a>
  
  <a id="nextlinkbot" class="nextlink nondisp">Next $ww_listsize</a>
</div>
</div>


<script type="text/javascript">

	// write the PREV link if necessary
	if ($ww_prevpage > 0) 
	{
		jQuery(".prevlink").removeClass("nondisp");
		jQuery(".prevlink").attr("href", "$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&page=$ww_prevpage&rnd=$rnd");
	}
	
	// write the NEXT link if necessary
	if ($ww_count == $ww_listsize) 
	{
		jQuery(".nextlink").removeClass("nondisp");
		jQuery(".nextlink").attr("href", "$pf_scriptname?CHKSYSKEY=$member&date=$datein&time=$timein&page=$ww_nextpage&rnd=$rnd");
	}	
</script>

</body>
</html>

SEGDTA;
		return;
	}
	if($segment == "nomember")
	{

		echo <<<SEGDTA
<!--        Results - -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta name="generator" content="WebSmart" />
    <meta http-equiv="Pragma" content="no-cache" />
    <title>System Performance</title>
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/Lincoln/css/print.css" media="print" />

</head>
  <body background="images/back_grey.jpg">

    <div id="pagetitle" class="pagetitle">&nbsp;&nbsp;** Error - Unable to allocate the data requested. **&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;System Name: $systemname</div>
    <div id="pagetitleborder"></div>

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
	
global $SYSNAME,$SYSSTSDATE,$SYSSTSTIME,$INTRECNBR,$STRTIME,$ENDTIME,$ELAPSETIME,$SBS,$JOB,$JOBUSER,$JOBNBR,$JOBTYPE,$JOBPOOL,$JOBRUNPTY,$JOBCPUTIME,$JOBINTTRAN,$JOBRSP,$JOBAUXIO,$JOBCPUPRC,$JOBFNC,$JOBSTATUS,$JOBTHREADS,$CURUSER,$JOBTEMPSTG,$JOBSTRDATE,$JOBSTRTIME;
global $SYSNAME,$OSREL,$SYSSTSDATE,$SYSSTSTIME,$PRCTYPE,$TOTPART,$CURAVLPRC,$CURINTCPW,$CURMEM,$CURPARTID,$CURPRC,$CURPRCCPTY,$NBRUSRACT,$NBRUSRDSC,$NBRUSRRQS,$NBRUSRGRP,$NBRBCHMSGW,$NBRBCHRUN,$NBRBCHHLD,$NBRBCHSCD,$NBRBCHJOBQ,$ENDJOBSPLF,$CPUUSED,$DBUSED,$JOBUSED,$TASKUSED,$ELAPSETIME,$JOBSINSYS,$PERMADDR,$TEMPADDR,$ASP1SIZE,$ASP1USED,$TOTAUXSTG,$CURUNPRSTG,$MAXUNPRSTG,$NBRACTPOOL,$QACTJOB,$QADLACTJ,$QADLTOTJ,$QBASACTLVL,$QBASPOOL,$QDAYOFWEEK,$QPFRADJ,$QMAXACTLVL,$QMCHPOOL,$QMODEL,$QPRCFEAT,$QSRLNBR,$QTOTJOB,$QTSEPOOL;
	global $pf_scriptname;
	$pf_scriptname = 'DBCPUusagedetail.php';

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

    // Last Generated CRC: 14CE84A7 55839186 EC6F3367 3FAC4E1D
    // Path: C:\Program Files\ESDI\WebSmart\temp\Dashboard_www_websmart_htdocs_dashboard_PHW Filels_DBCPUusagedetail.phw
}
?>