<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		DashboardMain.php
//	Program Title:		Dashboard Main Screen
//	Created by:			Ernie Paredes
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
global $username, $firstnm, $lastnm, $seclevel, $default_logo;
global $view, $refresh, $group, $machines;
global $SYSTEM_color, $SYSTEM_msg, $CHKSYSDATE_color, $KEYLCKPOS_color, $CUMPTFPKG_color, $ASPUSED_color, $DSKSTSERR_color, $WRKPRBERR_color, $QSYSMSGERR_color, $BATSTS_color, $BATSTSDAYS_color;
global $SAVSYS_days, $CHKSYSDATE_days, $QDATETIME_days;
global $QDATETIME_color, $MXMON_color, $MXDGERR_color, $MXAUDERR_color, $MXNFYERR_color, $OBJDELAY_color, $MXMSGW_color, $THRESHOLD_color, $SRCASPUSED_color, $TGTASPUSED_color;
global $sort, $orderby;
global $x; 

// Preset values
$view = 'MAIN';
$refresh = 5940;
$x = 1;

// Get session variables
getsession();

// As a default task for this program, execute the display function
//if ($pf_task == 'default')
switch($pf_task)
{
	case 'default':
	getinputfrombrowser();
	// Check WEBCOP
	webcop('DASHBOARD', $username, '2');
	switch($view)
	{
		case 'MAIN':
		display();
		break;
		case 'ERROR':
		display();
		break;
		case 'MIMIX':
		displayMX();
		break;
		case 'MIMIXERROR':
		displayMX();
		break;
	}
	break;
	
	case 'off': 
	// Check WEBCOP
	webcop('DASHBOARD', $username, '9');
	// Clear Session
	$_SESSION['DASHBOARD'] = null;
	$_SESSION['DASHBOARDMAIN'] = null;
	$_SESSION['DASHBOARDGROUP'] = null;
	// Goto signon screen
	header("Location: dashboard.php");
	break;
}

// Set session before exiting program
setsession();

// close the database connection
db2_close($db2conn);   

/********************
 End of mainline code
 ********************/

//===============================================================================
function getinputfrombrowser()
{ 
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if($arraykey != 'GLOBALS')
		{
			global $$arraykey;
		}
	}
	
	// Get view
	if (isset($_REQUEST['view'])) $view = strtoupper($_REQUEST['view']);
	// Auto Refresh
	if (isset($_REQUEST['refresh'])) $refresh = $_REQUEST['refresh'];
	
}

//===============================================================================
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
	
	// Get what colum to sort
	// get input from browser
	if (isset($_REQUEST['sort'])) 
		$sort = trim($_REQUEST['sort']);
	if ($sort == '')	
		$orderby = "ORDER BY UPPER(SYSTEM)";
	else
		$orderby = "ORDER BY $sort "; 
	
	// What group to display
	if ($group <> null) $machines = 'WHERE CHKSYSKEY in('.$machines.')';			
	
	// Selection
	$query = "select 
	CHKSYSKEY, upper(SYSTEM) as SYSTEM, 
	METHOD, 
	CHKSYSDATE, 
	CHKSYSTIME, 
	TIMEZONE, 
	QSRLNBR, 
	QMODEL, 
	QPRCFEAT, 
	PRCGRP, 
	CONSOLETYP, 
	KEYLCKPOS, 
	OSRLS, 
	CUMPTFPKG, 
	CUMPTFSTS, 
	TOTASP, 
	ASPUSED, 
	DSKSTSERR, 
	WRKPRBERR, 
	QSYSMSGERR, 
	BATSTS, 
	BATSTSDAYS, 
	SAVSYS  
	from DASHBOARD/FCHKSYS 
	$machines
	$orderby";
	
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
		
		
		$CHKSYSKEY = trim($row['CHKSYSKEY']);
		$SYSTEM = trim($row['SYSTEM']);
		$METHOD = trim($row['METHOD']);
		$CHKSYSDATE = substr($row['CHKSYSDATE'],0,4).'/'.substr($row['CHKSYSDATE'],4,2).'/'.substr($row['CHKSYSDATE'],6,4);
		$CHKSYSTIME = '000000'.$row['CHKSYSTIME'];
		$CHKSYSTIME = substr($CHKSYSTIME,-6,2).':'.substr($CHKSYSTIME,-4,2).':'.substr($CHKSYSTIME,-2,2);
		$TIMEZONE = trim($row['TIMEZONE']);
		$QSRLNBR = trim($row['QSRLNBR']);
		$QMODEL = trim($row['QMODEL']);
		$QPRCFEAT = trim($row['QPRCFEAT']);
		$PRCGRP = trim($row['PRCGRP']);
		$CONSOLETYP = trim($row['CONSOLETYP']);
		$KEYLCKPOS = trim($row['KEYLCKPOS']);
		$OSRLS = trim($row['OSRLS']);
		$CUMPTFPKG = trim($row['CUMPTFPKG']);
		$CUMPTFSTS = trim($row['CUMPTFSTS']);
		$TOTASP = trim($row['TOTASP']);
		$ASPUSED = round($row['ASPUSED'],1).'%';
		$DSKSTSERR = trim($row['DSKSTSERR']);
		$WRKPRBERR = trim($row['WRKPRBERR']);
		$QSYSMSGERR = trim($row['QSYSMSGERR']);
		$BATSTS = trim($row['BATSTS']);
		$BATSTSDAYS = $row['BATSTSDAYS'];
		$SAVSYS = substr($row['SAVSYS'],4,2).'/'.substr($row['SAVSYS'],6,2).'/'.substr($row['SAVSYS'],0,4);
		
		
		// *** Look for errors and change backgrounds  ***
		// Keylock Position
		if ($KEYLCKPOS != '*NORMAL') $KEYLCKPOS_color = "#FF8080"; else $KEYLCKPOS_color = null;
		// CUM PTF Package
		if ($CUMPTFPKG == '') $CUMPTFPKG_color = "#FF8080"; else $CUMPTFPKG_color = null;
		// CUM PTF Package *Not Installed 
		if ($CUMPTFSTS == 'Installed') $CUMPTFPKG_color = null; else $CUMPTFPKG_color = "#FF8080";
		// Check if PTF are up to date based on OS release and there's not error
		if ($CUMPTFPKG_color == null) {
			if ($CUMPTFPKG != getCUMPTFPKG($OSRLS)) $CUMPTFPKG_color = "#FFFF80"; else $CUMPTFPKG_color = null;	
		}
		// ASP Used
		if ($row['ASPUSED'] >= 80) {
			$ASPUSED_color = "#FFFF80";
			if ($row['ASPUSED'] >= 90) 	$ASPUSED_color = "#FF8080";
		} else {
			$ASPUSED_color = null;
		}
		// WRKDSKSTS Errors
		if ($DSKSTSERR != '*NO') $DSKSTSERR_color = "#FF8080"; else $DSKSTSERR_color = null;
		// WRKPRB Errors
		if ($WRKPRBERR != '*NO') $WRKPRBERR_color = "#FF8080"; else $WRKPRBERR_color = null;
		// QSYSMSG Critical Errors
		if ($QSYSMSGERR != '*NO') $QSYSMSGERR_color = "#FF8080"; else $QSYSMSGERR_color = null;
		// Cache Battery Status 
		if (($BATSTS <= '90' && $BATSTS >= '0') || ($BATSTS == '*UNKNOWN') || ($BATSTS == '*NOPTF'))  
			$BATSTS_color = "#FF8080"; else $BATSTS_color = null;
		// Cache Battery Status (Days)	
		if ($BATSTSDAYS <= 90) $BATSTSDAYS_color = "#FF8080"; else $BATSTSDAYS_color = null;
		// Calculate the # of days since last SAVSYS	
		$SAVSYS_days = dateDiff("/", date("m/d/Y"), $SAVSYS);
		if ($SAVSYS_days >= 180) {
			$SYSTEM_color = "#FFFF80";
			$SYSTEM_msg = "     ** Warning ** \n\n Last SAVSYS: $SAVSYS \n    $SAVSYS_days Days Old ";
			if ($SAVSYS_days >= 365) {
				$SYSTEM_color = "#FF8080"; 
				$SYSTEM_msg = "     ** Error ** \n\n Last SAVSYS: $SAVSYS \n    $SAVSYS_days Days Old ";
			}
		} else { 
			$SYSTEM_color = null;
			$SYSTEM_msg = null;
		}
		// Calculate the # of days since last CHKSYS	
		$CHKSYSDATE_days = dateDiff("/", date("m/d/Y"), substr($row['CHKSYSDATE'],4,2).'/'.substr($row['CHKSYSDATE'],6,2).'/'.substr($row['CHKSYSDATE'],0,4));
		if ($CHKSYSDATE_days > 2) $CHKSYSDATE_color = "#FF8080"; else $CHKSYSDATE_color = null;
		
		
		// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
		sanitize_output('DATA');
		
		if($view == 'MAIN'){
			wrtseg("ListDetails");
			$x++;
		}
		
		if($view == 'ERROR' && ($SYSTEM_color.$SYSTEM_msg.$CHKSYSDATE_color.$KEYLCKPOS_color.$CUMPTFPKG_color.$ASPUSED_color.$DSKSTSERR_color.$WRKPRBERR_color.$QSYSMSGERR_color.$BATSTS_color.$BATSTSDAYS_color != '')) {
			wrtseg("ListDetails");
			$x++;
		}
		
	}
	
	wrtseg("ListFooter");
}

//===============================================================================
function displayMX()
{ 
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	// Get what colum to sort
	// get input from browser
	if (isset($_REQUEST['sort'])) 
		$sort = trim($_REQUEST['sort']);
	if ($sort == '')	
		$orderby = "ORDER BY UPPER(SYSTEM)";
	else
		$orderby = "ORDER BY $sort "; 
	
	// What group to display
	if ($group <> null) $machines = 'WHERE MXKEY in('.$machines.')';			
	
	$query = "select 
	upper(FMIMIX.SYSTEM) as SYSTEM, 
	FMIMIX.LASTUPDBY, 
	FMIMIX.METHOD, 
	FMIMIX.MXSTSRCDS, 
	FMIMIX.MXMONRCDS, 
	FMIMIX.QDATETIME, 
	FMIMIX.MXROLE, 
	FMIMIX.MXRLS, 
	FMIMIX.MXMON, 
	FMIMIX.MXDGERR, 
	FMIMIX.MXAUDERR, 
	FMIMIX.MXNFYERR, 
	FMIMIX.OBJDELAY,
	FMIMIX.MXMSGW,
	FMIMIX.MXSRC, 
	FMIMIX.MXTGT,
	FMIMIX.RDB,
	FCHKSYS.CHKSYSKEY,
	FMIMIX.THRESHOLD,  
	FMIMIX.SRCASPUSED,  
	FMIMIX.TGTASPUSED  
	from DASHBOARD/FMIMIX left outer join DASHBOARD/FCHKSYS 
		on FMIMIX.MXKEY = FCHKSYS.CHKSYSKEY 
	$machines
	$orderby";
	
	// Fetch rows for page: relative to initial cursor 
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		die("<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	// Output header
	wrtseg("ListMXHeader"); 
	
	while ($row = db2_fetch_assoc($stmt))
	{
		// set color of the line
		xl_set_row_color('altcol1', 'altcol2');
		
		$SYSTEM = trim($row['SYSTEM']);
		$LASTUPDBY = trim($row['LASTUPDBY']);
		$METHOD = trim($row['METHOD']);
		$MXSTSRCDS = $row['MXSTSRCDS'];
		$MXMONRCDS = $row['MXMONRCDS'];
		$QDATETIME = substr($row['QDATETIME'],0,4).'/'.substr($row['QDATETIME'],4,2).'/'.substr($row['QDATETIME'],6,2)."&nbsp;&nbsp;&nbsp;&nbsp;".substr($row['QDATETIME'],8,2).':'.substr($row['QDATETIME'],10,2).':'.substr($row['QDATETIME'],12,2);
		$MXROLE = trim($row['MXROLE']);
		$MXRLS = trim($row['MXRLS']);
		$MXMON = trim($row['MXMON']);
		$MXDGERR = trim($row['MXDGERR']);
		$MXAUDERR = trim($row['MXAUDERR']);
		$MXNFYERR = trim($row['MXNFYERR']);
		$OBJDELAY = $row['OBJDELAY'];
		$MXMSGW = trim($row['MXMSGW']);
		$MXSRC = trim($row['MXSRC']);
		$MXTGT = trim($row['MXTGT']);
		$RDB = trim($row['RDB']);
		$CHKSYSKEY = trim($row['CHKSYSKEY']);
		$THRESHOLD = trim($row['THRESHOLD']);
		$SRCASPUSED = round($row['SRCASPUSED'],1).'%';
		$TGTASPUSED = round($row['TGTASPUSED'],1).'%';
		
		
		// *** Look for errors and change backgrounds  ***
		// Keylock Position
		if ($MXMON != '*ACTIVE') $MXMON_color = "#FF8080"; else $MXMON_color = null;
		// MIMIX Datagroup Error
		if ($MXDGERR == '*YES') $MXDGERR_color = "FF8080"; else $MXDGERR_color = null;
		// MIMIX Audit Error
		if ($MXAUDERR > 0) $MXAUDERR_color = "FF8080"; else $MXAUDERR_color = null;
		// MIMIX Notify Error
		if ($MXNFYERR > 0) $MXNFYERR_color = "FF8080"; else $MXNFYERR_color = null;
		// MIMIX Object Delay
		if ($OBJDELAY > 0) $OBJDELAY_color = "#FFFF80"; else $OBJDELAY_color = null;
		// MIMIX MSGW
		if ($MXMSGW <> '*NONE') $MXMSGW_color = "#FFFF80"; else $MXMSGW_color = null;
		// MIMIX Threshold
		if ($THRESHOLD == '*SRC' || $THRESHOLD == '*TGT' || $THRESHOLD == '*BOTH') 
			$THRESHOLD_color = "#FFFF80"; else $THRESHOLD_color = null;
		// *SRC ASP Used
		if ($row['SRCASPUSED'] >= 80) {
			$SRCASPUSED_color = "#FFFF80";
			if ($row['SRCASPUSED'] >= 90) $SRCASPUSED_color = "#FF8080";
		} else{ $SRCASPUSED_color = null;
		}
		// *TGT ASP Used
		if ($row['TGTASPUSED'] >= 80) {
			$TGTASPUSED_color = "#FFFF80";
			if ($row['TGTASPUSED'] >= 90) $TGTASPUSED_color = "#FF8080";
		} else {
			$TGTASPUSED_color = null;
		}
		
		// QDATETIME is older then 2 day.  Only used date in calulation and not time.
		$QDATETIME_days = dateDiff("/", date("m/d/Y"), substr($row['QDATETIME'],4,2).'/'.substr($row['QDATETIME'],6,2).'/'.substr($row['QDATETIME'],0,4));
		if ($QDATETIME_days > 2) $QDATETIME_color = "#FF8080"; else $QDATETIME_color = null;
		
		
		// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
		sanitize_output('DATA');
		
		if($view == 'MIMIX') {
			wrtseg("ListMXDetails");
			$x++;
		}
		
		if($view == 'MIMIXERROR' && ($MXMON_color.$MXDGERR_color.$MXAUDERR_color.$MXNFYERR_color.$OBJDELAY_color.$MXMSGW_color.$THRESHOLD_color.$SRCASPUSED_color.$TGTASPUSED_color != '')) {
			wrtseg("ListMXDetails");
			$x++;
		}
	}
	
	wrtseg("ListMXFooter");
	
	//echo "view: $view <br> refresh: $refresh <br>";
}


//===============================================================================
function getCUMPTFPKG($OSRLS)
{ 
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	$query = "select CUMPTFPKG  
	from DASHBOARD/FPTFCUR 
	where OSRLS = '$OSRLS'";
	
	// Fetch rows for page: relative to initial cursor 
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		die("<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	while ($row = db2_fetch_assoc($stmt))
	{
		return trim($row['CUMPTFPKG']);
		
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
		$default_logo = $info['default_logo'];
	}
	else
	{
		header("Location: dashboard.php?task=off");
	}
	
	if(isset($_SESSION['DASHBOARDMAIN'])){
		$info = $_SESSION['DASHBOARDMAIN'];
		$view = $info['view'];
		$refresh = $info['refresh'];
		$sort = $info['sort'];
	}
	
	if(isset($_SESSION['DASHBOARDGROUP'])){
		$info = $_SESSION['DASHBOARDGROUP'];
		$group = $info['group'];
		$machines = $info['machines'];
	}
	
}

//===============================================================================
function setsession()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if($arraykey[0]!='_' && $arraykey != 'GLOBALS')
			global $$arraykey;
	}
	
	// Store the data sent from the form into an array 
	$info['pgmnm'] = "DASHBOARDMAIN";
	$info['view'] = $view;
	$info['refresh'] = $refresh;
	$info['sort'] = $sort;
	
	// Store the array in a session. 
	$_SESSION['DASHBOARDMAIN'] = $info;
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
function dateDiff($dformat, $endDate, $beginDate)
{    
	$date_parts1=explode($dformat, $beginDate);
	$date_parts2=explode($dformat, $endDate);
	$start_date=gregoriantojd($date_parts1[0], $date_parts1[1], $date_parts1[2]);    
	$end_date=gregoriantojd($date_parts2[0], $date_parts2[1], $date_parts2[2]);    
	return $end_date - $start_date - 1;
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
    <title>Dashboard Main Screen</title>
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/Lincoln/css/print.css" media="print" />
    <link href="styles/fixedheadertableTheme.css" rel="stylesheet" media="screen" />
    
    <script type="text/javascript" src="/websmart/v9.0/javascript/jquery.min.js"></script>
    
    <!--Reference the jQuery code -->
    <link rel="stylesheet" href="javascript/jquery-ui-1.8.18/themes/base/jquery-ui.css" type="text/css" />
    <script type="text/javascript" src="javascript/jquery-ui-1.8.18/jquery-1.7.1.js"></script>
    <script type="text/javascript" src="javascript/jquery-ui-1.8.18/ui/jquery-ui.js"></script>
    
    <script src="javascript/jquery/jquery.fixedheadertable.js"></script>
    
    
    <script language="JavaScript">
<!--

	function Submit() {
    document.form1.action = "DashboardMain.php"
    document.form1.target = "_parent";    
    document.form1.submit();             
    return true;
	}

	var TRchange_color = "#66FFCC"
	function TRmover(aa) {
		TRbgcolor = aa.style.backgroundColor;
	 	aa.style.backgroundColor = TRchange_color;
	}
	function TRmout(aa) {
 		aa.style.backgroundColor = TRbgcolor;
	}

	var change_color = "#B4DCAF"
	function mover(aa) {
		bgcolor = aa.style.backgroundColor;
	 	aa.style.backgroundColor = change_color;
	}
	function mout(aa) {
 		aa.style.backgroundColor = bgcolor;
	}

	$.fx.speeds._default = 1000;
	function ajaxCall(CHKSYSKEY, rowid)
	{
	  $("table td").css('background-color','');
	  var parms = "CHKSYSKEY=" + CHKSYSKEY
	  $.ajax(
	   {
	     type: "POST",
	     url: "DashboardCustView.php",
	     data: parms,
	     success: function(data)
	              {   	    
      	            $("#" + rowid + " td").css('background-color','#B4DCAF');
                    $("#dialog").dialog(
                                        { 
                                        /*    buttons: 
                                                   { "Close": function() 
                                                           {  
                                                             $("#dialog").dialog('close'); 
                                                             $("#" + rowid + " td").css('background-color','');
                                                           }
                                                   },
                                        */           
                                            show: 'blind',
                                            hide: 'fold',
                                            position: [150, 130], 
                                            minWidth: 430,
                                            modal: false,
                                            close: function() 
                                                           {  
                                                             $("#" + rowid + " td").css('background-color','');
                                                           }
                                          });
                    $(".ui-dialog-titlebar-close").show();// ui-corner-all                                          
      	            $("#dialog").html(data);
	               }
	    });   
	}

//-->
</script>
    
    <script language="JavaScript">

/***********************************************
* Dynamic Countdown script- © Dynamic Drive (http://www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit http://www.dynamicdrive.com/ for this script and 100s more.
***********************************************/

var dragapproved=false
var minrestore=0
var initialwidth,initialheight
var ie5=document.all&&document.getElementById
var ns6=document.getElementById&&!document.all

function iecompattest(){
return (!window.opera && document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function drag_drop(e){
if (ie5&&dragapproved&&event.button==1){
document.getElementById("dwindow").style.left=tempx+event.clientX-offsetx+"px"
document.getElementById("dwindow").style.top=tempy+event.clientY-offsety+"px"
}
else if (ns6&&dragapproved){
document.getElementById("dwindow").style.left=tempx+e.clientX-offsetx+"px"
document.getElementById("dwindow").style.top=tempy+e.clientY-offsety+"px"
}
}

function initializedrag(e){
offsetx=ie5? event.clientX : e.clientX
offsety=ie5? event.clientY : e.clientY
document.getElementById("dwindowcontent").style.display="none" //extra
tempx=parseInt(document.getElementById("dwindow").style.left)
tempy=parseInt(document.getElementById("dwindow").style.top)

dragapproved=true
document.getElementById("dwindow").onmousemove=drag_drop
}

function loadwindow(url,width,height){
if (!ie5&&!ns6)
window.open(url,"","width=width,height=height,scrollbars=1")
else{
document.getElementById("dwindow").style.display=''
document.getElementById("dwindow").style.width=initialwidth=width+"px"
document.getElementById("dwindow").style.height=initialheight=height+"px"
document.getElementById("dwindow").style.left="30px"
document.getElementById("dwindow").style.top=ns6? window.pageYOffset*1+30+"px" : iecompattest().scrollTop*1+30+"px"
document.getElementById("cframe").src=url
}
}

function maximize(){
if (minrestore==0){
minrestore=1 //maximize window
document.getElementById("maxname").setAttribute("src","images/restore.gif")
document.getElementById("dwindow").style.width=ns6? window.innerWidth-20+"px" : iecompattest().clientWidth+"px"
document.getElementById("dwindow").style.height=ns6? window.innerHeight-20+"px" : iecompattest().clientHeight+"px"
}
else{
minrestore=0 //restore window
document.getElementById("maxname").setAttribute("src","images/max.gif")
document.getElementById("dwindow").style.width=initialwidth
document.getElementById("dwindow").style.height=initialheight
}
document.getElementById("dwindow").style.left=ns6? window.pageXOffset+"px" : iecompattest().scrollLeft+"px"
document.getElementById("dwindow").style.top=ns6? window.pageYOffset+"px" : iecompattest().scrollTop+"px"
}

function closeit(){
document.getElementById("dwindow").style.display="none"
}

function stopdrag(){
dragapproved=false;
document.getElementById("dwindow").onmousemove=null;
document.getElementById("dwindowcontent").style.display="" //extra
}

</script>


<script>

$(document).ready(function($)
{

var h = $(window).height() - 230;

// window re-size  * not working with IE
//$(window).resize(function() {
//$('#myTable').fixedHeaderTable({ height: h});
//});

//$('#myTable').fixedHeaderTable({ footer: true, cloneHeadToFoot: true, altClass: 'odd', autoShow: false });
//$('#myTable').fixedHeaderTable('show', 1000);
$('#myTable').fixedHeaderTable({ height: h});


});
</script>



    
    <style type="text/css">
    <!--
	body {
	background-image: url(images/back_grey.jpg);
	}
.ui-dialog .ui-dialog-content { background: #CCCCCC; }
.ui-dialog .ui-dialog-titlebar { background: #999999; }
.ui-dialog .ui-dialog-buttonpane { background: #CCCCCC; }
	-->
    </style>
    
  </head>
  <body onload="setTimeout('Submit()',
SEGDTA;
 echo $refresh*1000; 
		echo <<<SEGDTA
)">
    <!--Dialog-->
    <div id="dialog" title="Customer View"></div>
    
    
SEGDTA;
 include('Includes/Header.php'); 
		echo <<<SEGDTA

    
SEGDTA;
 include('Includes/menu.php'); 
		echo <<<SEGDTA

    
    <!--Popup-->
    <div id="dwindow" style="position:absolute;background-color:#EBEBEB;cursor:hand;left:0px;top:0px;display:none" onMousedown="initializedrag(event)" onMouseup="stopdrag()" onSelectStart="return false">
      <div align="right" style="background-color:navy"><img src="images/max.gif" id="maxname" onClick="maximize()"><img src="images/close.gif" onClick="closeit()"></div>
      <div id="dwindowcontent" style="height:100%">
        <iframe id="cframe" src="" width=100% height=100%></iframe>
	</div>
	</div>


    <form name="form1" method="post" action="">
      
      <div id="contents">
        
        <!--<? php 	wrtseg("NavigationBar");  ?>--> 
        
        <input name="sort" type="hidden" id="sort" value="$sort" />
        <input name="view" type="hidden" id="view" value="$view" />
        <input name="refresh" type="hidden" id="refresh" value="$refresh" />

        <table id="myTable" class="mainlist">
          <thead class="fixedHeader">

          <tr>
            
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;SYSTEM&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">System<br>
              Description</th>
            <th width="100"  title="Click to Sort" onClick="form1.sort.value = &quot;METHOD&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">CHKSYS<br>
              Method</th>
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;CHKSYSDATE, CHKSYSTIME&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">CHKSYS<br>
              (Date Time Zone)<br>(CCYY/MM/DD&nbsp;&nbsp;HH:MM:SS)</th>
            <!--<th width="88">Serial<br>
          Number<br>(QSRLNBR)</th>-->
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;QMODEL&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">Model<br>
              (QMODEL)</th>
            <!--<th width="96">Processor<br>
          Feature<br>(QPRCFEAT)</th>-->
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;PRCGRP&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">Processor<br>
              Group</th>
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;CONSOLETYP&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">Console<br>
              Type</th>
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;KEYLCKPOS&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">Keylock<br>
              Position</th>
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;OSRLS&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">OS<br>
              Release</th>
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;CUMPTFPKG&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">Cumulative<br>
              PTF<br>Package</th>
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;TOTASP&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">Total<br>
              ASP</th>
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;ASPUSED&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">ASP<br>
              Used</th>
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;DSKSTSERR&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">WRKDSKSTS<br>
              Errors</th>
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;WRKPRBERR&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">WRKPRB<br>
              Errors</th>
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;QSYSMSGERR&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">QSYSMSG<br>
              Critical<br>Errors</th>
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;BATSTS&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">Cache<br>
              Battery<br>Status</th>
            <!--<th width="107" title="Click to Sort" onClick="form1.sort.value = &quot;BATSTSDAYS&quot;;
          return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">Cache<br>
          Battery<br>Status<br>(Days)</th>-->
            <th width="100" title="Click to Sort" onClick="form1.sort.value = &quot;CHKSYSKEY&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">Reference Key<br>
            </th>
          </tr>

          </thead>
          <tbody>
          
SEGDTA;
		return;
	}
	if($segment == "listdetails")
	{

		echo <<<SEGDTA
<tr class="$pf_altrowclr" id="$x" onmouseover="TRmover(this);" onmouseout="TRmout(this);">
  <!--<td nowrap="nowrap" class="text">$CHKSYSKEY</td>-->
  <!--<td nowrap="nowrap" bgcolor="$SYSTEM_color" class="text" title="$SYSTEM_msg"><div align="left" onmouseover="mover(this);" onmouseout="mout(this);" onClick="window.open(&quot;DashboardCustView.php?CHKSYSKEY=$CHKSYSKEY&quot;, &quot;&quot;, &quot;width=450,height=480,scrollbars=yes,screenX=0,screenY=0,left=0,top=0,&quot;);window.focus();"><strong>$SYSTEM&nbsp;&nbsp;</strong></div></td>-->
  <td nowrap="nowrap" bgcolor="$SYSTEM_color" class="text" title="$SYSTEM_msg"><div align="left" onmouseover="mover(this);" onmouseout="mout(this);" onClick="ajaxCall('$CHKSYSKEY', '$x');"><strong>$SYSTEM</strong></div></td>
  <td nowrap="nowrap" class="text">$METHOD</td>
  <td nowrap="nowrap" bgcolor="$CHKSYSDATE_color" class="text"><div align="center">$CHKSYSDATE&nbsp;&nbsp;$CHKSYSTIME&nbsp;&nbsp;$TIMEZONE</div></td>
  <!--<td nowrap="nowrap" class="text">$QSRLNBR</td>-->
  <td nowrap="nowrap" class="text">$QMODEL</td>
  <!--<td nowrap="nowrap" class="text">$QPRCFEAT</td>-->
  <td nowrap="nowrap" class="text">$PRCGRP</td>
  <td nowrap="nowrap" class="text">$CONSOLETYP</td>
  <td nowrap="nowrap" bgcolor="$KEYLCKPOS_color" class="text">$KEYLCKPOS</td>
  <td nowrap="nowrap" class="text"><div align="center">$OSRLS</div></td>

  <td nowrap="nowrap" bgcolor="$CUMPTFPKG_color" class="text" onClick="loadwindow('CumulativePTFPackage.php?CHKSYSKEY=$CHKSYSKEY&amp;OSRLS=$OSRLS',700,750)" ><div align="center" onmouseover="mover(this);"  onmouseout="mout(this);">
  $CUMPTFPKG</div></td>

  <td nowrap="nowrap" class="text"><div align="right">$TOTASP</div></td>
  <td nowrap="nowrap" bgcolor="$ASPUSED_color" class="text right">$ASPUSED</td>

  <td nowrap="nowrap" bgcolor="$DSKSTSERR_color" class="text" onClick="loadwindow('FDSKSTS.php?CHKSYSKEY=$CHKSYSKEY',1000,750)" ><div align="center" onmouseover="mover(this);"  onmouseout="mout(this);">
  $DSKSTSERR</div></td>

  <!--<td nowrap="nowrap" bgcolor="$WRKPRBERR_color" class="text"><div align="center">$WRKPRBERR</div></td>-->
  <td nowrap="nowrap" bgcolor="$WRKPRBERR_color" class="text" onClick="loadwindow('FDSPPRBERR.php?CHKSYSKEY=$CHKSYSKEY',1000,750)" ><div align="center" onmouseover="mover(this);"  onmouseout="mout(this);">
  $WRKPRBERR</div></td>

  <!--<td nowrap="nowrap" bgcolor="$QSYSMSGERR_color" class="text"><div align="center">$QSYSMSGERR</div></td>-->
  <td nowrap="nowrap" bgcolor="$QSYSMSGERR_color" class="text" onClick="loadwindow('FQSYSMSG.php?CHKSYSKEY=$CHKSYSKEY',900,750)" ><div align="center" onmouseover="mover(this);"  onmouseout="mout(this);">
  $QSYSMSGERR</div></td>

  <!--<td nowrap="nowrap" bgcolor="$BATSTS_color" class="text"><div align="center">$BATSTS</div></td>-->
  <td nowrap="nowrap" bgcolor="$BATSTS_color" class="text" onClick="loadwindow('FBATSTS.php?CHKSYSKEY=$CHKSYSKEY',1200,750)" ><div align="center" onmouseover="mover(this);"  onmouseout="mout(this);">
  $BATSTS</div></td>


<!--  <td nowrap="nowrap" bgcolor="$SYSTEM_color" class="text" title="$SYSTEM_msg"><div align="right" onmouseover="mover(this);" onmouseout="mout(this);" onClick="window.open('DBCPUusage.php?CHKSYSKEY=$CHKSYSKEY','_blank','width=950,height=700,status=no,scrollbars=yes,resizable=yes');">
    <div align="center"><strong>$CHKSYSKEY</strong></div>
  </div></td>-->
  <td nowrap="nowrap"  class="text" title="$SYSTEM_msg">
    <div align="center"><strong>$CHKSYSKEY</strong>&nbsp;&nbsp;
    <a href="#"><img src="images/bar_chart-icon.gif" title="System Performance" width="9" height="9" onClick="window.open('DBCPUusage.php?CHKSYSKEY=$CHKSYSKEY','_blank','width=950,height=700,status=no,scrollbars=yes,resizable=yes');"> 	
    
    </a></div> 
  </td>
</tr>

SEGDTA;
		return;
	}
	if($segment == "listfooter")
	{

		echo <<<SEGDTA
</tbody>
</table>
</div>

<!--------------- Begin Footer --------------->
<!--------------- End Footer --------------->



</form>
</body>
</html>

SEGDTA;
		return;
	}
	if($segment == "listmxheader")
	{

		echo <<<SEGDTA
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta name="generator" content="WebSmart" />
    <meta http-equiv="Pragma" content="no-cache" />
    <title>Dashboard Main Screen</title>
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/Lincoln/css/print.css" media="print" />
    <script type="text/javascript" src="/websmart/v8.9/javascript/jquery.min.js"></script>
    
    <!--Reference the jQuery code -->
    <link rel="stylesheet" href="javascript/jquery-ui-1.8.18/themes/base/jquery-ui.css" type="text/css" />
    <script type="text/javascript" src="javascript/jquery-ui-1.8.18/jquery-1.7.1.js"></script>
    <script type="text/javascript" src="javascript/jquery-ui-1.8.18/ui/jquery-ui.js"></script>
    
    
    <script language="JavaScript">
<!--

	function Submit() {
    document.form1.action = "DashboardMain.php"
    document.form1.target = "_parent";    
    document.form1.submit();             
    return true;
    }

	var TRchange_color = "#66FFCC"
	function TRmover(aa) {
		TRbgcolor = aa.style.backgroundColor;
	 	aa.style.backgroundColor = TRchange_color;
	}
	function TRmout(aa) {
 		aa.style.backgroundColor = TRbgcolor;
	}

	var change_color = "#B4DCAF"
	function mover(aa) {
		bgcolor = aa.style.backgroundColor;
	 	aa.style.backgroundColor = change_color;
	}
	function mout(aa) {
 		aa.style.backgroundColor = bgcolor;
	}
	$.fx.speeds._default = 1000;
	function ajaxCall(CHKSYSKEY, rowid)
	{
	  $("table td").css('background-color','');
	  var parms = "CHKSYSKEY=" + CHKSYSKEY
	  $.ajax(
	   {
	     type: "POST",
	     url: "DashboardCustView.php",
	     data: parms,
	     success: function(data)
	              {   	    
      	            $("#" + rowid + " td").css('background-color','#B4DCAF');
                    $("#dialog").dialog(
                                        { 
                                        /*    buttons: 
                                                   { "Close": function() 
                                                           {  
                                                             $("#dialog").dialog('close'); 
                                                             $("#" + rowid + " td").css('background-color','');
                                                           }
                                                   },
                                        */           
                                            show: 'blind',
                                            hide: 'fold',
                                            position: [150, 130], 
                                            minWidth: 430,
                                            modal: false,
                                            close: function() 
                                                           {  
                                                             $("#" + rowid + " td").css('background-color','');
                                                           }
                                          });
                    $(".ui-dialog-titlebar-close").show();// ui-corner-all                                          
      	            $("#dialog").html(data);
	               }
	    });   
	}

//-->
</script>
    
    <script language="JavaScript">

/***********************************************
* Dynamic Countdown script- © Dynamic Drive (http://www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit http://www.dynamicdrive.com/ for this script and 100s more.
***********************************************/

var dragapproved=false
var minrestore=0
var initialwidth,initialheight
var ie5=document.all&&document.getElementById
var ns6=document.getElementById&&!document.all

function iecompattest(){
return (!window.opera && document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function drag_drop(e){
if (ie5&&dragapproved&&event.button==1){
document.getElementById("dwindow").style.left=tempx+event.clientX-offsetx+"px"
document.getElementById("dwindow").style.top=tempy+event.clientY-offsety+"px"
}
else if (ns6&&dragapproved){
document.getElementById("dwindow").style.left=tempx+e.clientX-offsetx+"px"
document.getElementById("dwindow").style.top=tempy+e.clientY-offsety+"px"
}
}

function initializedrag(e){
offsetx=ie5? event.clientX : e.clientX
offsety=ie5? event.clientY : e.clientY
document.getElementById("dwindowcontent").style.display="none" //extra
tempx=parseInt(document.getElementById("dwindow").style.left)
tempy=parseInt(document.getElementById("dwindow").style.top)

dragapproved=true
document.getElementById("dwindow").onmousemove=drag_drop
}

function loadwindow(url,width,height){
if (!ie5&&!ns6)
window.open(url,"","width=width,height=height,scrollbars=1")
else{
document.getElementById("dwindow").style.display=''
document.getElementById("dwindow").style.width=initialwidth=width+"px"
document.getElementById("dwindow").style.height=initialheight=height+"px"
document.getElementById("dwindow").style.left="30px"
document.getElementById("dwindow").style.top=ns6? window.pageYOffset*1+30+"px" : iecompattest().scrollTop*1+30+"px"
document.getElementById("cframe").src=url
}
}

function maximize(){
if (minrestore==0){
minrestore=1 //maximize window
document.getElementById("maxname").setAttribute("src","images/restore.gif")
document.getElementById("dwindow").style.width=ns6? window.innerWidth-20+"px" : iecompattest().clientWidth+"px"
document.getElementById("dwindow").style.height=ns6? window.innerHeight-20+"px" : iecompattest().clientHeight+"px"
}
else{
minrestore=0 //restore window
document.getElementById("maxname").setAttribute("src","images/max.gif")
document.getElementById("dwindow").style.width=initialwidth
document.getElementById("dwindow").style.height=initialheight
}
document.getElementById("dwindow").style.left=ns6? window.pageXOffset+"px" : iecompattest().scrollLeft+"px"
document.getElementById("dwindow").style.top=ns6? window.pageYOffset+"px" : iecompattest().scrollTop+"px"
}

function closeit(){
document.getElementById("dwindow").style.display="none"
}

function stopdrag(){
dragapproved=false;
document.getElementById("dwindow").onmousemove=null;
document.getElementById("dwindowcontent").style.display="" //extra
}

</script>
    
    <style type="text/css">
    <!--
	body {
	background-image: url(images/back_grey.jpg);
	}
.ui-dialog .ui-dialog-content { background: #CCCCCC; }
.ui-dialog .ui-dialog-titlebar { background: #999999; }
.ui-dialog .ui-dialog-buttonpane { background: #CCCCCC; }
	-->
    </style>
    
  </head>
  <body onload="setTimeout('Submit()',
SEGDTA;
 echo $refresh*1000; 
		echo <<<SEGDTA
)">
    <!--Dialog-->
    <div id="dialog" title="Customer View"></div>
    
    
SEGDTA;
 include('Includes/Header.php'); 
		echo <<<SEGDTA

    
SEGDTA;
 include('Includes/menu.php'); 
		echo <<<SEGDTA

    
    <!--Popup-->
    <div id="dwindow" style="position:absolute;background-color:#EBEBEB;cursor:hand;left:0px;top:0px;display:none" onMousedown="initializedrag(event)" onMouseup="stopdrag()" onSelectStart="return false">
      <div align="right" style="background-color:navy"><img src="images/max.gif" id="maxname" onClick="maximize()"><img src="images/close.gif" onClick="closeit()"></div>
      <div id="dwindowcontent" style="height:100%">
        <iframe id="cframe" src="" width=100% height=100%></iframe>
	</div>
	</div>

    
    <form name="form1" method="post" action="">
      
      <div id="contents">
        
        <!--<? php 	wrtseg("NavigationBar");  ?>--> 
        
        <input name="sort" type="hidden" id="sort" value="$sort" />
        <input name="view" type="hidden" id="view" value="$view" />
        <input name="refresh" type="hidden" id="refresh" value="$refresh" />
        
        <table id="listtable" class="mainlist">
          <tr>
            
            <th width="130" title="Click to Sort" onClick="form1.sort.value = &quot;SYSTEM&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">System<br>Description</th>
            <th width="80" title="Click to Sort" onClick="form1.sort.value = &quot;LASTUPDBY&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">Last Update<br>By Job</th>
            <th width="60" title="Click to Sort" onClick="form1.sort.value = &quot;METHOD&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">Delivery<br>Method</th>
            <th width="120" title="Click to Sort" onClick="form1.sort.value = &quot;QDATETIME&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">Date & Time<br>(QDATETIME)<br>(CCYY/MM/DD&nbsp;&nbsp;HH:MM:SS)</th>
            <th width="60" title="Click to Sort" onClick="form1.sort.value = &quot;MXROLE&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">MIMIX<br>Role</th>
            <th width="80" title="Click to Sort" onClick="form1.sort.value = &quot;MXRLS&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">MIMIX<br>Release</th>
            <th width="80" title="Click to Sort" onClick="form1.sort.value = &quot;MXMON&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">MIMIX<br>Monitor<br>Status</th>
            <th width="80" title="Click to Sort" onClick="form1.sort.value = &quot;MXDGERR&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">MIMIX<br>Datagroup<br>Error</th>
            <th width="80" title="Click to Sort" onClick="form1.sort.value = &quot;MXAUDERR&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">MIMIX<br>Audit<br>Error</th>
            <th width="80" title="Click to Sort" onClick="form1.sort.value = &quot;MXNFYERR&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">MIMIX<br>Notify<br>Error</th>
            <th width="60" title="Click to Sort" onClick="form1.sort.value = &quot;OBJDELAY&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">MIMIX<br>Object<br>Delay</th>
            <th width="60" title="Click to Sort" onClick="form1.sort.value = &quot;MXMSGW&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">MIMIX<br>MSGW</th>
            <th width="60" title="Click to Sort" onClick="form1.sort.value = &quot;THRESHOLD&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">MIMIX<br>Threshold</th>
            <th width="60" title="Click to Sort" onClick="form1.sort.value = &quot;SRCASPUSED&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">*SRC<br>ASP%<br>Used</th>
            <th width="60" title="Click to Sort" onClick="form1.sort.value = &quot;TGTASPUSED&quot;;
              return Submit();" onMouseOver="mover(this);" onMouseOut="mout(this);">*TGT<br>ASP%<br>Used</th>
          </tr>
          
SEGDTA;
		return;
	}
	if($segment == "listmxdetails")
	{

		echo <<<SEGDTA
<tr class="$pf_altrowclr" id="$x" onmouseover="TRmover(this);" onmouseout="TRmout(this);">
  <!--<td nowrap="nowrap" class="text"><div align="left" onmouseover="mover(this);" onmouseout="mout(this);" onClick="window.open(&quot;DashboardCustView.php?CHKSYSKEY=$CHKSYSKEY&quot;, &quot;&quot;, &quot;width=450,height=480,scrollbars=yes,screenX=0,screenY=0,left=0,top=0,&quot;);window.focus();"><strong>$SYSTEM</strong></div></td>-->
  <td nowrap="nowrap" class="text"><div align="left" onmouseover="mover(this);" onmouseout="mout(this);" onClick="ajaxCall('$CHKSYSKEY', '$x');"><strong>$SYSTEM</strong></div></td>
  <td nowrap="nowrap" class="text">$LASTUPDBY</td>
  <td nowrap="nowrap" class="text">$METHOD</td>
  <td nowrap="nowrap" bgcolor="$QDATETIME_color" class="text"><div align="center">$QDATETIME</div></td>
  <td nowrap="nowrap" class="text"><div align="center">$MXROLE</div></td>
  <td nowrap="nowrap" class="text"><div align="center">$MXRLS</div></td>
  <td nowrap="nowrap" bgcolor="$MXMON_color" class="text"><div align="center">$MXMON</div></td>
  
  
  <td nowrap="nowrap" bgcolor="$MXDGERR_color" class="text" onClick="loadwindow('DSPMXDG.php?CHKSYSKEY=$CHKSYSKEY&amp;BG=$MXDGERR_color',950,500)" ><div align="center" onmouseover="mover(this);"  onmouseout="mout(this);">
  $MXDGERR</div></td>
  
  <td nowrap="nowrap" bgcolor="$MXAUDERR_color" class="text" onClick="loadwindow('DSPMXAUD.php?CHKSYSKEY=$CHKSYSKEY&amp;BG=$MXAUDERR_color',750,500)"><div align="center" onmouseover="mover(this);"  onmouseout="mout(this);">
  $MXAUDERR</div></td>
  
  <td nowrap="nowrap" bgcolor="$MXNFYERR_color" class="text" onClick="loadwindow('DSPMXNFY.php?CHKSYSKEY=$CHKSYSKEY&amp;BG=$MXNFYERR_color',800,400)"><div align="center" onmouseover="mover(this);"  onmouseout="mout(this);">
  $MXNFYERR</div></td>

  <td nowrap="nowrap" bgcolor="$OBJDELAY_color" class="text"><div align="center">$OBJDELAY</div></td>
  <td nowrap="nowrap" bgcolor="$MXMSGW_color" class="text"><div align="center">$MXMSGW</div></td>
  <td nowrap="nowrap" bgcolor="$THRESHOLD_color" class="text"><div align="center">$THRESHOLD</div></td>
  <td nowrap="nowrap" bgcolor="$SRCASPUSED_color" class="text right">$SRCASPUSED</td>
  <td nowrap="nowrap" bgcolor="$TGTASPUSED_color" class="text right">$TGTASPUSED</td>
</tr>

SEGDTA;
		return;
	}
	if($segment == "listmxfooter")
	{

		echo <<<SEGDTA
</table>
</div>

<!--------------- Begin Footer --------------->

<!--------------- End Footer --------------->
</form>
</body>
</html>

SEGDTA;
		return;
	}
	if($segment == "navigationbar")
	{

		echo <<<SEGDTA
&nbsp;&nbsp;
<input name="view" type="radio" class="disabled" onClick="return Submit();" id="radio" value="main" 
SEGDTA;
 if ($view == 'MAIN') echo 'checked="checked"'; 
		echo <<<SEGDTA
/>View All
&nbsp;&nbsp;&nbsp;&nbsp;
<input name="view" type="radio" class="disabled" onclick="return Submit();" id="radio" value="error" 
SEGDTA;
 if ($view == 'ERROR') echo 'checked="checked"'; 
		echo <<<SEGDTA
/>View Only Errors
&nbsp;&nbsp;&nbsp;&nbsp;
<input name="view" type="radio" class="disabled" onclick="return Submit();" id="radio" value="mimix" 
SEGDTA;
 if ($view == 'MIMIX') echo 'checked="checked"'; 
		echo <<<SEGDTA
/>View MIMIX
&nbsp;&nbsp;&nbsp;&nbsp;
<input name="view" type="radio" class="disabled" onclick="return Submit();" id="radio" value="mimixerror" 
SEGDTA;
 if ($view == 'MIMIXERROR') echo 'checked="checked"'; 
		echo <<<SEGDTA
/>
View MIMIX Errors
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<!--<a href="DashboardGroupBy.php">test</a>-->&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="button" id="button" value="Refresh Page" />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Auto Refresh Every 
<select name="refresh" id="refresh" onchange="return Submit();" >
  <option 
SEGDTA;
 if ($refresh == 5940) echo 'selected="selected"'; 
		echo <<<SEGDTA
 value="5940">N/A</option>
  <option 
SEGDTA;
 if ($refresh == 60) echo 'selected="selected"'; 
		echo <<<SEGDTA
 value="60">1 Minute</option>
  <option 
SEGDTA;
 if ($refresh == 300) echo 'selected="selected"'; 
		echo <<<SEGDTA
 value="300">5 Minutes</option>
  <option 
SEGDTA;
 if ($refresh == 600) echo 'selected="selected"'; 
		echo <<<SEGDTA
 value="600">10 Minutes</option>
  <option 
SEGDTA;
 if ($refresh == 900) echo 'selected="selected"'; 
		echo <<<SEGDTA
 value="900">15 Minutes</option>
  <option 
SEGDTA;
 if ($refresh == 1800) echo 'selected="selected"'; 
		echo <<<SEGDTA
 value="1800">30 Minutes</option>
  <option 
SEGDTA;
 if ($refresh == 3600) echo 'selected="selected"'; 
		echo <<<SEGDTA
 value="3600">60 Minutes</option>
</select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$pf_scriptname?task=off"><img src="images/Log_Out.gif" title="Log-Out" width="44" height="36" border="0"></a>
<br>
<br>

SEGDTA;
		return;
	}
	if($segment == "grouping")
	{

		echo <<<SEGDTA
 
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
	$pf_scriptname = 'DashboardMainScroll.php';

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

    // Last Generated CRC: 7E231BF2 8D426911 2C81D4A1 0C55F9B5
    // Path: C:\Program Files\ESDI\WebSmart\temp\Dashboard_www_websmart_htdocs_dashboard_PHW Filels_DashboardMainScroll.phw
}
?>