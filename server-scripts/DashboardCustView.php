<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		DashboardCustView.php
//	Program Title:		Customer View
//	Created by:			Owner
//	Template family:	Lincoln
//	Template name:		Single Record (by Key).tpl
//	Purpose:        
//	Program Modifications:

require('/esdi/websmart/v8.9/include/xl_functions001.php');

// Connect to the database.
$options = array('i5_naming' => DB2_I5_NAMING_ON);

global $db2conn;
$db2conn = xl_db2_connect($options);

if(!$db2conn)
{
	die('Could not connect to database: ' . db2_conn_error());
}

// Global work fields should be defined here
global $target, $execsql;
global $username, $firstnm, $lastnm, $seclevel;

// Get session variables
getsession();

// Check WEBCOP
webcop('DASHBOARD', $username, '2');


$target = "Location: ";


// Do the task
switch($pf_task)
{
	
	case 'default':
	disprcd();
	break;
}

//Release the database resource
db2_close($db2conn);


// Display details for selected record:
function disprcd()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	// Get the key field values which identify the record
	$CHKSYSKEY = xl_get_parameter('CHKSYSKEY');
	
	
	//build the where string  
	$wherestring = 'WHERE ' . 'CHKSYSKEY = \'' . xl_encode($CHKSYSKEY, 'db2_search') . "'";
	
	$sqlstatement = bldselstring();
	$sqlstatement .= $wherestring;
	
	// Make sure our selection criteria matches only a single record
	$execsql = 'SELECT COUNT(*) from DASHBOARD/FCHKSYS where ' . 'CHKSYSKEY = \'' . xl_encode($CHKSYSKEY, 'db2_search') . "'";
	$result = db2_exec($db2conn, $execsql);
	$row = db2_fetch_array($result);
	if($row[0] > 1)
	{
		db2_close($db2conn);
		die("Error: More than one record is identified by the key values you've specified. No record has been displayed.");
	}
	
	// Fetch the row for page
	if (!($result = db2_exec($db2conn, $sqlstatement, array('CURSOR' => DB2_SCROLLABLE)))) 
	{
		//Release the database resource
		db2_close($db2conn);
		
		die("<b>Error ". db2_stmt_error().":" . db2_stmt_errormsg()."</b>"); 
	}
	
	// put the result into global variable and show it    
	$row = db2_fetch_assoc($result);
	
	//make sure we got a row
	if($row == null)
	{
		db2_close($db2conn);
		die("No record exists with the specified key value(s)");
	}
	
	// get the fields 
	$CHKSYSKEY = rtrim($row['CHKSYSKEY']);
	$SYSTEM = rtrim($row['SYSTEM']);
	$CHKSYSDATE = substr($row['CHKSYSDATE'],0,4).'/'.substr($row['CHKSYSDATE'],4,2).'/'.substr($row['CHKSYSDATE'],6,4);
	$CHKSYSTIME = '0'.$row['CHKSYSTIME'];
	$CHKSYSTIME = substr($CHKSYSTIME,-6,2).':'.substr($CHKSYSTIME,-4,2).':'.substr($CHKSYSTIME,-2,2);
	$ASPUSED = round($row['ASPUSED'],1).'%';
	$WRKPRBERR = rtrim($row['WRKPRBERR']);
	$QSYSMSGERR = rtrim($row['QSYSMSGERR']);
	$BATSTSDAYS = rtrim($row['BATSTSDAYS']);
	$MIMIXSBS = rtrim($row['MIMIXSBS']);
	$MXDGERR = rtrim($row['MXDGERR']);
	$MXAUDERR = rtrim($row['MXAUDERR']);
	$MXNFYERR = rtrim($row['MXNFYERR']);
	$MXMON = rtrim($row['MXMON']);
	$OBJDELAY = rtrim($row['OBJDELAY']);
	$MXMSGW = rtrim($row['MXMSGW']);
	$MXSTSDATE = rtrim($row['MXSTSDATE']);
	$MXSTSTIME = rtrim($row['MXSTSTIME']);
	$MXSTSDATE = substr($row['MXSTSDATE'],0,4).'/'.substr($row['MXSTSDATE'],4,2).'/'.substr($row['MXSTSDATE'],6,4);
	$MXSTSTIME = '0'.$row['MXSTSTIME'];
	$MXSTSTIME = substr($MXSTSTIME,-6,2).':'.substr($MXSTSTIME,-4,2).':'.substr($MXSTSTIME,-2,2);


	
	// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
	sanitize_output('DATA');
	
	// output the segment
	wrtseg('RcdDisplay');
	if ($MXSTSDATE <> '//') wrtseg('RcdDisplayMX');
	wrtseg('Footer');
}



function bldselstring()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	return 'SELECT 
		FCHKSYS.CHKSYSKEY, 
		FCHKSYS.SYSTEM,
		FCHKSYS.CHKSYSDATE,
		FCHKSYS.CHKSYSTIME,
		FCHKSYS.ASPUSED,
		FCHKSYS.WRKPRBERR,
		FCHKSYS.QSYSMSGERR,
		FCHKSYS.BATSTSDAYS,
		FCHKSYS.MIMIXSBS,
		FMIMIX.MXDGERR,
		FMIMIX.MXAUDERR,
		FMIMIX.MXNFYERR,
		FMIMIX.MXMON,
		FMIMIX.OBJDELAY,
		FMIMIX.MXMSGW,
		FMIMIX.MXSTSDATE,
		FMIMIX.MXSTSTIME 
		FROM DASHBOARD/FCHKSYS left outer join DASHBOARD/FMIMIX 
		on FCHKSYS.CHKSYSKEY = FMIMIX.MXKEY ';
	
	
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
		$CHKSYSKEY = htmlspecialchars($CHKSYSKEY, ENT_QUOTES);
		$SYSTEM = htmlspecialchars($SYSTEM, ENT_QUOTES);
		$CHKSYSDATE = htmlspecialchars($CHKSYSDATE, ENT_QUOTES);
		$CHKSYSTIME = htmlspecialchars($CHKSYSTIME, ENT_QUOTES);
		$ASPUSED = htmlspecialchars($ASPUSED, ENT_QUOTES);
		$WRKPRBERR = htmlspecialchars($WRKPRBERR, ENT_QUOTES);
		$QSYSMSGERR = htmlspecialchars($QSYSMSGERR, ENT_QUOTES);
		$BATSTSDAYS = htmlspecialchars($BATSTSDAYS, ENT_QUOTES);
		$MIMIXSBS = htmlspecialchars($MIMIXSBS, ENT_QUOTES);
		$MXDGERR = htmlspecialchars($MXDGERR, ENT_QUOTES);
		$MXAUDERR = htmlspecialchars($MXAUDERR, ENT_QUOTES);
		$MXNFYERR = htmlspecialchars($MXNFYERR, ENT_QUOTES);
		$MXMON = htmlspecialchars($MXMON, ENT_QUOTES);
		$OBJDELAY = htmlspecialchars($OBJDELAY, ENT_QUOTES);
		$MXMSGW = htmlspecialchars($MXMSGW, ENT_QUOTES);
		$MXSTSDATE = htmlspecialchars($MXSTSDATE, ENT_QUOTES);
		$MXSTSTIME = htmlspecialchars($MXSTSTIME, ENT_QUOTES);
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


function wrtseg($segment)
{
	// Make sure it's case insensitive
	$segment = strtolower($segment);

	// Make all global variables available locally
	foreach($GLOBALS as $arraykey=>$arrayvalue) {if($arraykey != "GLOBALS"){global $$arraykey;}}

	// Output the requested segment:

	if($segment == "rcddisplay")
	{

		echo <<<SEGDTA

<div id="pagetitle" class="pagetitle"> $SYSTEM </div>

<div id="contents">
  <br><br>      
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <strong>SYSTEM</strong><br>
  <table id="listtable" class="mainlist">
    
    <tr>
      <td width="200" nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>CHKSYS KEY:</strong></td>
      <td width="178" nowrap="nowrap" bgcolor="#FFFFFF" class="text">$CHKSYSKEY</td>
    </tr>
    <tr>
      <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>SYSTEM TEXT DESCRIPTION:</strong></td>
      <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$SYSTEM</td>
    </tr>
    <tr>
      <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>CHKSYS DATE:</strong></td>
      <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$CHKSYSDATE</td>
    </tr>
    <tr>
      <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>CHKSYS TIME:</strong></td>
      <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$CHKSYSTIME</td>
    </tr>
    <tr>
      <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>PERCENTAGE ASP USED:</strong></td>
      <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$ASPUSED</td>
    </tr>
    <tr>
      <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>WRKPRB ERRORS:</strong></td>
      <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$WRKPRBERR</td>
    </tr>
    <tr>
      <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>QSYSMSG CRITICAL ERRORS:</strong></td>
      <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$QSYSMSGERR</td>
    </tr>
    <tr>
      <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>CACHE BATTERY STATUS (DAYS):</strong></td>
      <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$BATSTSDAYS</td>
    </tr>
    
  </table>
 
SEGDTA;
		return;
	}
	if($segment == "rcddisplaymx")
	{

		echo <<<SEGDTA
<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <strong>MIMIX</strong><br>

<table id="listtable" class="mainlist">
  <tr>
    <td width="200" nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>MIMIX SBS STATUS:</strong></td>
    <td width="178" nowrap="nowrap" bgcolor="#FFFFFF" class="text">$MIMIXSBS</td>
  </tr>
  <tr>
    <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>MIMIX DATAGROUP ERROR:</strong></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$MXDGERR</td>
  </tr>
  <tr>
    <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>MIMIX AUDIT ERROR:</strong></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$MXAUDERR</td>
  </tr>
  <tr>
    <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>MIMIX NOTIFY ERROR:</strong></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$MXNFYERR</td>
  </tr>
  <tr>
    <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>MIMIX MONITOR STATUS:</strong></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$MXMON</td>
  </tr>
  <tr>
    <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>NUMBER OF OBJECTS DELAYED:</strong></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$OBJDELAY</td>
  </tr>
  <tr>
    <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>MIMIX MSGW:</strong></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$MXMSGW</td>
  </tr>
  <tr>
    <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>CHKMXSTS DATE:</strong></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$MXSTSDATE</td>
  </tr>
  <tr>
    <td nowrap="nowrap" bgcolor="#a9cce8" class="label"><strong>CHKMXSTS TIME:</strong></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF" class="text">$MXSTSTIME</td>
  </tr>
</table>

SEGDTA;
		return;
	}
	if($segment == "footer")
	{

		echo <<<SEGDTA
</div>
<br><br>
SEGDTA;
		return;
	}

	// If we reach here, the segment is not found
	echo("Segment $segment is not defined! ");
}

function internal_init()
{
	
global $CHKSYSKEY,$SYSTEM,$CHKSYSRLS,$METHOD,$CHKSYSRCDS,$QUSREXTPGM,$PRCDATE,$PRCTIME,$CHKSYSDATE,$CHKSYSTIME,$QDATETIME,$TIMEZONE,$QSRLNBR,$HDWTYPE,$LPARTYPE,$QMODEL,$QPRCFEAT,$QCONSOLE,$QCTLSBSD,$QSTRUPPGM,$CONSOLETYP,$KEYLCKPOS,$PRCGRP,$SYSNAME,$OSRLS,$CUMPTFPKG,$CUMPTFSTS,$PARTID,$JOB,$USER,$NBR,$TOTASP,$ASPUSED,$SAVPREMISE,$WATCHDOG,$RTVPFRSTS,$DSKMON,$PRBMON,$QSYSMSG,$BATMON,$RCLSTG,$RCLSTGDUR,$SAVSYS,$SAVSECDTA,$SAVCFG,$SAVNONSYS,$SAVALLUSR,$SAVIBM,$MINISAVSYS,$DSKSTSERR,$WRKPRBERR,$QSYSMSGERR,$BATSTS,$BATSTSDAYS,$MIMIXSBS,$MXLIB,$MXRLS,$MXROLE,$MXMON,$MXSTSDATE,$MXSTSTIME,$CHKSYS;
global $MXKEY,$SYSTEM,$LASTUPDBY,$METHOD,$MXSTSRCDS,$MXMONRCDS,$USREXTPGM,$MXSTSRLS,$MXMONRLS,$PRCDATE,$PRCTIME,$QDATETIME,$SYSNAME,$OSRLS,$QSRLNBR,$PARTID,$MXLIB,$MXRLS,$MXSRC,$MXTGT,$RDB,$MXDGERR,$MXAUDERR,$MXNFYERR,$MXSTSDATE,$MXSTSTIME,$MXSTSJOB,$MXSTSUSER,$MXSTSNBR,$MIMIXSBS,$MXMON,$MXMONDATE,$MXMONTIME,$MXMONJOB,$MXMONUSER,$MXMONNBR,$OBJDELAY,$MXROLE,$MXMSGW,$THRESHOLD,$SRCASPUSED,$SRCSYSASP,$TGTASPUSED,$TGTSYSASP,$SDSKSTSORG,$SDSKSTSCUR,$TDSKSTSORG,$TDSKSTSCUR,$CHKMXSTS,$STRMXMON;
	global $pf_scriptname;
	$pf_scriptname = 'DashboardCustView.php';

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

    // Last Generated CRC: DE8385AE 112F90AE 99BB6423 336CB831
    // Path: C:\Program Files\ESDI\WebSmart\temp\Dashboard_www_websmart_htdocs_dashboard_PHW Filels_DashboardCustView.phw
}
?>