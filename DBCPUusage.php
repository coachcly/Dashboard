<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		DBCPUusage.php 
//	Program Title:		Dashboard CPU Usage
//	Created by:			Ernie Paredes
//	Template name:		A Simple Page.tpl
//	Purpose:        
//	Program Modifications:

require('/esdi/websmart/v8.9/include/xl_functions001.php');

// DB Connection code
$options = array('i5_naming' => DB2_I5_NAMING_ON, 'i5_date_fmt' => DB2_I5_FMT_USA, 'i5_time_fmt' => DB2_I5_FMT_USA);
global $db2conn;
$db2conn = xl_db2_connect($options);

if(!$db2conn)
{
	die('Failed to connect to database!');
}

global $username, $firstnm, $lastnm, $seclevel;
global $datein, $dateout, $cpu, $graph, $cap;
global $nextday, $prevday, $dayofweek, $ctime;
global $member, $systemname, $CHKSYSKEY;

// Get session variables
getsession();

// get input from browser
if (isset($_REQUEST['CHKSYSKEY']))
	$member = strtoupper($_REQUEST['CHKSYSKEY']); 
if (isset($_REQUEST['CHKSYSKEY']))
	$CHKSYSKEY = strtoupper($_REQUEST['CHKSYSKEY']); 

// Check WEBCOP
webcop('DASHBOARD', $username, '2');

// Get System Name
getsystemname();

$ctime = date("g:i a");

set_array();

getusage();


// closet the database connection
db2_close($db2conn);   



//===============================================================================
function getusage()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if($arraykey[0]!='_' && $arraykey != 'GLOBALS')
			global $$arraykey;
	}
	
	// Create Alias
	$query = "CREATE ALIAS QTEMP/$member FOR DASHBOARD/FPFRSTSC($member)";
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		wrtseg('NoMember');
		die;
		//die("<b>Create Alias Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	
	if (isset($_REQUEST['datein']))	{
		$tempdate = $_REQUEST['datein'];
		$dateout = $_REQUEST['datein'];
		$datein = substr($tempdate,6,4).'-'.substr($tempdate,0,2).'-'.substr($tempdate,3,2);
	} else {
		$datein = date("Y-m-d");
		$dateout = date("m/d/Y");
	}	
	
	// Day of week
	$dayofweek = date('l',strtotime($datein));
	
	// Calculate the next and prev day for navigation with arrows
	$nextday = strtotime ( '+1 day' , strtotime ($datein) ) ;
	$nextday = date ( 'm/d/Y' , $nextday );
	
	$prevday = strtotime ( '-1 day' , strtotime ($datein) ) ;
	$prevday = date ( 'm/d/Y' , $prevday );
	
	
	//=============== CPU performance =======================================
	
	$date_new = substr($datein,0,4).substr($datein,5,2).substr($datein,8,2);
	
	$query = "SELECT SYSSTSTIME, CPUUSED, CURPRCCPTY 
	FROM QTEMP/$member
	WHERE SYSSTSDATE = $date_new";
	
	// Fetch rows for page: relative to initial cursor 
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		wrtseg('NoMember');
		//echo "<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"; 
		die;
	}
	
	while ($row = db2_fetch_assoc($stmt))
	{
		$temp = $row['SYSSTSTIME'];
		$cpu[$temp] = $row['CPUUSED'];
		$cap[$temp] = $row['CURPRCCPTY'];
		
		if ($cpu[$temp] <= 50) 
			$graph[$temp] = 
		"<a href='DBCPUusagedetail.php?CHKSYSKEY=$CHKSYSKEY&date=$date_new&time=$temp'><img src='images/line_green.GIF' title='$cpu[$temp]%' width='$cpu[$temp]%' height=8></a> $cpu[$temp]%";
		
		if ($cpu[$temp] > 50 && $cpu[$temp] <= 75) 
			$graph[$temp] = 
		"<a href='DBCPUusagedetail.php?CHKSYSKEY=$CHKSYSKEY&date=$date_new&time=$temp'><img src='images/line_green.GIF' title='$cpu[$temp]%' width='50%' height=8></a>".
		"<a href='DBCPUusagedetail.php?CHKSYSKEY=$CHKSYSKEY&date=$date_new&time=$temp'><img src='images/line_blue.GIF' title='$cpu[$temp]%' width='".($cpu[$temp]-50)."%' height=8></a> $cpu[$temp]%";
		
		if ($cpu[$temp] > 75) 
			$graph[$temp] = 
		"<a href='DBCPUusagedetail.php?CHKSYSKEY=$CHKSYSKEY&date=$date_new&time=$temp'><img src='images/line_green.GIF' title='$cpu[$temp]%' width='50%' height=8></a>".
		"<a href='DBCPUusagedetail.php?CHKSYSKEY=$CHKSYSKEY&date=$date_new&time=$temp'><img src='images/line_blue.GIF' title='$cpu[$temp]%' width='25%' height=8></a>".
		"<a href='DBCPUusagedetail.php?CHKSYSKEY=$CHKSYSKEY&date=$date_new&time=$temp'><img src='images/line_red.GIF' title='$cpu[$temp]%' width='".($cpu[$temp]-75)."%' height=8></a> $cpu[$temp]%";
		
	}
	
	//=============== Get Midnight which is in the following day ================================
	
	$date_use = $date_new;
	$date_new = substr($nextday,6,4).substr($nextday,0,2).substr($nextday,3,2);
	
	$query = "SELECT SYSSTSTIME, CPUUSED, CURPRCCPTY 
	FROM QTEMP/$member
	WHERE SYSSTSDATE = $date_new
	and SYSSTSTIME = 1";
	
	// Fetch rows for page: relative to initial cursor 
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		wrtseg('NoMember');
		//echo "<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"; 
		die;
	}
	
	if ($row = db2_fetch_assoc($stmt))
	{
		$temp = 240000;
		$cpu[$temp] = $row['CPUUSED'];
		$cap[$temp] = $row['CURPRCCPTY'];
		
		if ($cpu[$temp] <= 50) 
			$graph[$temp] = 
		"<a href='DBCPUusagedetail.php?CHKSYSKEY=$CHKSYSKEY&date=$date_use&time=1''><img src='images/line_green.GIF' title='$cpu[$temp]%' width='$cpu[$temp]%' height=8></a> $cpu[$temp]%";
		
		if ($cpu[$temp] > 50 && $cpu[$temp] <= 75) 
			$graph[$temp] = 
		"<a href='DBCPUusagedetail.php?CHKSYSKEY=$CHKSYSKEY&date=$date_use&time=1''><img src='images/line_green.GIF' title='$cpu[$temp]%' width='50%' height=8></a>".
		"<a href='DBCPUusagedetail.php?CHKSYSKEY=$CHKSYSKEY&date=$date_use&time=1''><img src='images/line_blue.GIF' title='$cpu[$temp]%' width='".($cpu[$temp]-50)."%' height=8></a> $cpu[$temp]%";
		
		if ($cpu[$temp] > 75) 
			$graph[$temp] = 
		"<a href='DBCPUusagedetail.php?CHKSYSKEY=$CHKSYSKEY&date=$date_use&time=1''><img src='images/line_green.GIF' title='$cpu[$temp]%' width='50%' height=8></a>".
		"<a href='DBCPUusagedetail.php?CHKSYSKEY=$CHKSYSKEY&date=$date_use&time=1''><img src='images/line_blue.GIF' title='$cpu[$temp]%' width='25%' height=8></a>".
		"<a href='DBCPUusagedetail.php?CHKSYSKEY=$CHKSYSKEY&date=$date_use&time=1''><img src='images/line_red.GIF' title='$cpu[$temp]%' width='".($cpu[$temp]-75)."%' height=8></a> $cpu[$temp]%";
		
	}
	
	// Write page
	wrtseg('Results');
}


//===============================================================================
function set_array()
{
	
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if($arraykey[0]!='_' && $arraykey != 'GLOBALS')
			global $$arraykey;
	}
	
	$count = 000000;
	while($count <= 230000) {
		
		$countx = 1;
		while($countx <= 4) {
			$cpu[$count] = '&nbsp;';
			$graph[$count] = '&nbsp;';
			$cap[$count] = '&nbsp;';
			$count = $count + 1500;
			$countx = $countx + 1;
		}
		$count = $count + 4000;
		$cpu[$count] = '&nbsp;';
		$graph[$count] = '&nbsp;';
		$cap[$count] = '&nbsp;';
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

	if($segment == "results")
	{

		echo <<<SEGDTA
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta name="generator" content="WebSmart" />
    <meta http-equiv="Pragma" content="no-cache" />
    <title>System Performance</title>
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/Lincoln/css/print.css" media="print" />
    <script type="text/javascript" src="/websmart/v8.9/javascript/jquery.min.js"></script>
    <script type="text/javascript" src="/websmart/v8.9/javascript/ui.datepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/include/datepicker.css"/>
    <script type="text/javascript">
        jQuery(document).ready( function()
        {
            // Calendar lookup for field ID "datein"
            jQuery('#datein').datepicker({ dateFormat: 'mm/dd/yy', showOn: 'both', buttonImage: '/websmart/v8.9/include/images/calendar.gif', buttonImageOnly: true, buttonText: 'Popup Date Selector'});
        });
    </script>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
 
     <SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">

// PRELOADING IMAGES
if (document.images) {
 img_onL =new Image();  img_onL.src ="images/ArrowLx.gif"; 
 img_offL=new Image();  img_offL.src="images/ArrowL.gif"; 
 img_clickL=new Image();  img_clickL.src="images/ArrowLc.gif"; 
 img_onR =new Image();  img_onR.src ="images/ArrowRx.gif"; 
 img_offR=new Image();  img_offR.src="images/ArrowR.gif"; 
 img_clickR=new Image();  img_clickR.src="images/ArrowRc.gif"; 
}
function handleOverL() { 
 if (document.images) document.imgLeft.src=img_onL.src;
}
function handleOutL() {
 if (document.images) document.imgLeft.src=img_offL.src;
}
function handleClickL() {
 if (document.images) document.imgLeft.src=img_clickL.src;
}
function handleOverR() { 
 if (document.images) document.imgRight.src=img_onR.src;
}
function handleOutR() {
 if (document.images) document.imgRight.src=img_offR.src;
}
function handleClickR() {
 if (document.images) document.imgRight.src=img_clickR.src;
}
function ClickArrow(valuein) {
	document.form1.datein.value = valuein;
    document.form1.submit();             
}

	</SCRIPT>

</head>
  <body background="images/back_grey.jpg">

    <div id="pagetitle" class="pagetitle">System Performance&nbsp;&nbsp;-&nbsp;&nbsp;System Name: $systemname</div>
    <div id="pagetitleborder"></div>

<center>    
      <table width="80%" border="0">
    <form name="form1" method="post" action="$pf_scriptname">
  <tr valign="middle">
    <th width="31%" height="39" nowrap="nowrap" scope="col"><div align="left">Select Date:
      <!-- Calendar lookup attached to field ID "datein" -->
        <input name="datein" type="text" id="datein" onchange="document.form1.submit();" value="$dateout" size="11" maxlength="10" />
    &nbsp;&nbsp;$dayofweek
    </div></th>
    <th width="13%" nowrap="nowrap" scope="col"> 

      <img src="images/ArrowL.gif" alt="Previous Day" name="imgLeft" width="31" height="31" border="0" id="imgLeft" title="Previous Day" 
                  onclick="ClickArrow('$prevday'); handleClickL(); return true;" 
                  onmouseover="handleOverL();return true;" 
                  onmouseout="handleOutL();return true;" />&nbsp;&nbsp;&nbsp;&nbsp; <img src="images/ArrowR.gif" alt="Next Day" name="imgRight" width="31" height="31" border="0" id="imgRight" title="Next Day" 
                  onclick="ClickArrow('$nextday'); handleClickR(); return true;" 
                  onmouseover="handleOverR();return true;" 
                  onmouseout="handleOutR();return true;" /></th>
    <th width="26%" nowrap="nowrap" scope="col"><div align="right">      <font size="+1">Current Time: <font color="#990000">$ctime</font></font></div></th>
    <th width="30%" nowrap="nowrap" scope="col"><input type="submit" name="button" id="button" value="Refresh Page" />
      <input name="CHKSYSKEY" type="hidden" id="CHKSYSKEY" value="$member" />
      <input name="systemname" type="hidden" id="systemname" value="$systemname" /></th>
  </tr>
  </form>  
</table>
      
           <div id="contents">
 
<table width="80%" border="1" id="listtable"  class="mainlist" cellpadding="1" cellspacing="0">
<tr>
        <th width="12%" valign="middle" nowrap="nowrap" scope="col"><div align="center">Time of Day</div></th>
    <th width="9%" valign="middle" nowrap="nowrap" scope="col"><div align="center">Processor <br />
    Capacity</div></th>
    <th width="79%" valign="middle" nowrap="nowrap" scope="col"><div align="left"> System Performance (CPU Usage in 15min Intervals)</div></th>
  </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">12:01 - 1:00am</div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[1500]<br>
        $cap[3000]<br>
        $cap[4500]<br>
            $cap[10000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[1500]<br>
          $graph[3000]<br>
          $graph[4500]<br>
          $graph[10000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">1:01 - 2:00am</div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[11500]<br>
        $cap[13000]<br>
        $cap[14500]<br>
            $cap[20000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[11500]<br>
          $graph[13000]<br>
          $graph[14500]<br>
          $graph[20000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">2:01 - 3:00am</div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[21500]<br>
        $cap[23000]<br>
        $cap[24500]<br>
            $cap[30000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[21500]<br>
          $graph[23000]<br>
          $graph[24500]<br>
          $graph[30000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">3:01 - 4:00am</div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[31500]<br>
        $cap[33000]<br>
        $cap[34500]<br>
            $cap[40000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[31500]<br>
          $graph[33000]<br>
          $graph[34500]<br>
          $graph[40000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">4:01 - 5:00am</div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[41500]<br>
        $cap[43000]<br>
        $cap[44500]<br>
            $cap[50000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[41500]<br>
          $graph[43000]<br>
          $graph[44500]<br>
          $graph[50000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">5:01 - 6:00am</div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[51500]<br>
        $cap[53000]<br>
        $cap[54500]<br>
            $cap[60000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[51500]<br>
          $graph[53000]<br>
          $graph[54500]<br>
          $graph[60000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">6:01 - 7:00am</div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[61500]<br>
            $cap[63000]<br>
            $cap[64500]<br>
            $cap[70000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[61500]<br>
          $graph[63000]<br>
          $graph[64500]<br>
          $graph[70000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">7:01 - 8:00am</div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[71500]<br>
            $cap[73000]<br>
            $cap[74500]<br>
            $cap[80000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[71500]<br>
          $graph[73000]<br>
          $graph[74500]<br>
          $graph[80000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">8:01 - 9:00am</div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[81500]<br>
            $cap[83000]<br>
            $cap[84500]<br>
            $cap[90000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[81500]<br>
          $graph[83000]<br>
          $graph[84500]<br>
          $graph[90000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">9:01 - 10:00am</div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[91500]<br>
            $cap[93000]<br>
            $cap[94500]<br>
            $cap[100000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[91500]<br>
          $graph[93000]<br>
          $graph[94500]<br>
          $graph[100000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">10:01 - 11:00am</div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[101500]<br>
            $cap[103000]<br>
            $cap[104500]<br>
            $cap[110000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[101500]<br>
          $graph[103000]<br>
          $graph[104500]<br>
          $graph[110000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">11:01 - 12:00pm</div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[111500]<br>
            $cap[113000]<br>
            $cap[114500]<br>
            $cap[120000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[111500]<br>
          $graph[113000]<br>
          $graph[114500]<br>
          $graph[120000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">12:01 - 1:00pm</div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[121500]<br>
            $cap[123000]<br>
            $cap[124500]<br>
            $cap[130000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[121500]<br>
          $graph[123000]<br>
          $graph[124500]<br>
          $graph[130000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">1:01 - 2:00pm</div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[131500]<br>
            $cap[133000]<br>
            $cap[134500]<br>
            $cap[140000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[131500]<br>
          $graph[133000]<br>
          $graph[134500]<br>
          $graph[140000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">2:01 - 3:00pm</div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[141500]<br>
            $cap[143000]<br>
            $cap[144500]<br>
            $cap[150000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[141500]<br>
          $graph[143000]<br>
          $graph[144500]<br>
          $graph[150000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">3:01 - 4:00pm</div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[151500]<br>
            $cap[153000]<br>
            $cap[154500]<br>
            $cap[160000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[151500]<br>
          $graph[153000]<br>
          $graph[154500]<br>
          $graph[160000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">
            <div align="center">4:01 - 5:00pm</div>
          </div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[161500]<br>
            $cap[163000]<br>
            $cap[164500]<br>
            $cap[170000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[161500]<br>
          $graph[163000]<br>
          $graph[164500]<br>
          $graph[170000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">
            <div align="center">5:01 - 6:00pm</div>
          </div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[171500]<br>
            $cap[173000]<br>
            $cap[174500]<br>
            $cap[180000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[171500]<br>
          $graph[173000]<br>
          $graph[174500]<br>
          $graph[180000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">
            <div align="center">6:01 - 7:00pm</div>
          </div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[181500]<br>
            $cap[183000]<br>
            $cap[184500]<br>
            $cap[190000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[181500]<br>
          $graph[183000]<br>
          $graph[184500]<br>
          $graph[190000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">
            <div align="center">7:01 - 8:00pm</div>
          </div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[191500]<br>
            $cap[193000]<br>
            $cap[194500]<br>
            $cap[200000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[191500]<br>
          $graph[193000]<br>
          $graph[194500]<br>
          $graph[200000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">8:01 - 9:00pm</div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[201500]<br>
            $cap[203000]<br>
            $cap[204500]<br>
            $cap[210000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[201500]<br>
          $graph[203000]<br>
          $graph[204500]<br>
          $graph[210000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">
            <div align="center">9:01 - 10:00pm</div>
          </div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[211500]<br>
            $cap[213000]<br>
            $cap[214500]<br>
            $cap[220000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[211500]<br>
          $graph[213000]<br>
          $graph[214500]<br>
          $graph[220000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">
            <div align="center">10:01 - 11:00pm</div>
          </div></td>
    <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[221500]<br>
            $cap[223000]<br>
            $cap[224500]<br>
            $cap[230000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[221500]<br>
          $graph[223000]<br>
          $graph[224500]<br>
          $graph[230000]  </div></td>
    </tr>
      <tr>
        <td valign="middle" nowrap="nowrap" bgcolor="#FFFFFF"><div align="center">
            <div align="center">11:01 - 12:00am</div>
          </div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF"><div align="center"><font color="#990000">$cap[231500]<br>
        $cap[233000]<br>
        $cap[234500]<br>
            $cap[240000]</font></div></td>
        <td nowrap="nowrap" bgcolor="#FFFFFF">
          <div align="left">$graph[231500]<br>
          $graph[233000]<br>
          $graph[234500]<br>
          $graph[240000]  </div></td>
    </tr>
    </table>
  </div> 
  <br>
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
	
	global $pf_scriptname;
	$pf_scriptname = 'DBCPUusage.php';

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


    // Last Generated CRC: DD462889 2786B4DE F8CE994F 9F97E585
    // Path: C:\Program Files\ESDI\WebSmart\temp\Dashboard_www_websmart_htdocs_dashboard_PHW Filels_DBCPUusage.phw
}
?>