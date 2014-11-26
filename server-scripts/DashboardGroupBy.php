<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		DashboardGroupBy.php
//	Program Title:		Group By
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
global $view, $refresh;
global $group, $checked, $onclick, $machines;
global $username, $firstnm, $lastnm, $seclevel, $default_logo;
global $groupx;

getsession();
// Check WEBCOP
webcop('DASHBOARD', $username, '2');

// As a default task for this program, execute the display function
switch($pf_task)
{
	case 'default':
	display();
	break;
	
	case 'submit':
	getgroups();
	setsession();
	//die;
	header("Location: DashboardMain.php");
	break;
}

// close the database connection
db2_close($db2conn);   

/********************
 End of mainline code
 ********************/

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
	
	
	$query = 'select distinct GROUP from DASHBOARD/FGROUPBY 
	GROUP BY GROUP
	ORDER BY GROUP';
	
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
		
		
		$GROUP = trim($row['GROUP']);
		// Get all the systems for this Group
		setsystems();
		
		// Replace splaces with '_' 
		$groupx = str_replace(' ', '_', $GROUP);
		
		// Check if it has been selected
		if (strstr($group,$GROUP)) 
			$checked = 'checked="checked"';
		else
			$checked = null;		
		
		// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
		sanitize_output('DATA');
		
		wrtseg("ListDetails");
		
	}
	
	wrtseg("ListFooter");
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
	
	// Kill the job if Security Level is not ADMIN
	if ($seclevel != 'ADMIN') die;
	
	// Retrieve the session information if it is set here...
	if(isset($_SESSION['DASHBOARDGROUP'])){
		$info = $_SESSION['DASHBOARDGROUP'];
		$group = $info['group'];
		$machines = $info['machines'];
	}
	
}

//===============================================================================
function setsystems()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if($arraykey[0]!='_' && $arraykey != 'GLOBALS')
			global $$arraykey;
	}
	
	$loop = 0;
	
	$query = "select CSTSYSKEY, CSTSYSTEXT from DASHBOARD/FGROUPBY 
	WHERE GROUP = '$GROUP'
	ORDER BY CSTSYSTEXT";
	
	// Fetch rows for page: relative to initial cursor 
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		die("<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	while ($row = db2_fetch_assoc($stmt))
	{
		
		if ($loop == 0) {	
			$CSTSYSKEY = "'".trim($row['CSTSYSKEY'])."'";
			$CSTSYSTEXT = trim($row['CSTSYSTEXT']);
		} else{
			$CSTSYSKEY .= ', ' . "'".trim($row['CSTSYSKEY'])."'";
			$CSTSYSTEXT .= '<br>' . trim($row['CSTSYSTEXT']);
		}
		
		$loop++;
		
		// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
		sanitize_output('DATA');
		
	}
	
	
}	

//===============================================================================
function getgroups()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if($arraykey[0]!='_' && $arraykey != 'GLOBALS')
			global $$arraykey;
	}
	
	// Reset
	$group = null;
	$machines = null;
	
	// Get data from screen POST
	if (isset($_REQUEST['group'])){
		foreach($_REQUEST['group'] as $key => $value)
		{
			if ($key == 0) {
				$group = "'$value'";
				$value = str_replace(' ', '_', $value);
				if (isset($_REQUEST["K"."$value"]))	$machines = $_REQUEST["K"."$value"]; 
			} else {
				$group = $group.', '."'$value'";
				$value = str_replace(' ', '_', $value);
				$cstsyskey = $group.', '."'$value'";
				if (isset($_REQUEST["K"."$value"]))	$machines .= ', '.$_REQUEST["K"."$value"]; 
			}
		}
	} 
	
	// Remove the first comma, if needed
	if (substr($machines,0,1) == ',') $machines = substr($machines,1);
	
	//echo "You have selected: $group <br>";
	//echo "Machines: $machines <br>";
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
	$info['pgmnm'] = "DASHBOARDGROUP";
	$info['group'] = $group;
	$info['machines'] = $machines;
	
	// Store the array in a session. 
	$_SESSION['DASHBOARDGROUP'] = $info;
	
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

	if($segment == "listheader")
	{

		echo <<<SEGDTA
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta name="generator" content="WebSmart" />
    <meta http-equiv="Pragma" content="no-cache" />
    <title>Group By</title>
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v8.9/Lincoln/css/print.css" media="print" />
    <script type="text/javascript" src="/websmart/v8.9/javascript/jquery.min.js"></script>
    
 	<SCRIPT language="javascript"> 
		$(function(){       
		
		// add multiple select / deselect functionality     
		$("#selectall").click(function () {           
			$('.case').attr('checked', this.checked);     
		});       
		
		// if all checkbox are selected, check the selectall checkbox     
		// and viceversa     
		$(".case").click(function(){           
		
			if($(".case").length == $(".case:checked").length) {             
				$("#selectall").attr("checked", "checked");         
			} else {				            
				$("#selectall").removeAttr("checked");         
			}      
		}); 
	}); 
	</SCRIPT>
    
    <style type="text/css">
    <!--
	body {
	background-image: url(images/back_grey.jpg);
	}
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

    
    <div id="contents">
      <form action="$pf_scriptname" method="post" name="form1">
        <table width="680" align="center">
          <tr>
            <td width="152" bgcolor="#FFFFFF"><div align="center">
                <input name="task" type="hidden" id="hiddenField" value="submit" />
                <input type="submit" name="button" id="button" value="Submit" />
              </div>            </td>
            <td width="518"><table width="548" align="center" class="mainlist" id="listtable">
                <tr>
                  <th width="60">Select<br />
                    Group</th>
                  <th width="234"><div align="left">Group Name</div></th>
                  <th width="238"><div align="left">Customer<br />
                      Systems Names</div></th>
                </tr>


            <tr>
              <td valign="middle" bgcolor="#CCCCCC">
                <div align="center">
                  <input type="checkbox" id="selectall"/>
                </div></td>
              <td valign="middle" bgcolor="#CCCCCC" class="text">Select / Deselect All</td>
              <td bgcolor="#CCCCCC" class="text">&nbsp;</td>
            </tr>
                 
SEGDTA;
		return;
	}
	if($segment == "listdetails")
	{

		echo <<<SEGDTA

            <tr class="$pf_altrowclr">
              <td valign="middle">
                <div align="center">
                  <input type="checkbox" class="case" name="group[]" value="$GROUP" $checked />
                </div></td>
              <td valign="middle" class="text">$GROUP              
              <input name="K$groupx" type="hidden" id="Key" value="$CSTSYSKEY" /></td>
              <td class="text">$CSTSYSTEXT</td>
            </tr>
            
SEGDTA;
		return;
	}
	if($segment == "listfooter")
	{

		echo <<<SEGDTA

</table></td>
</tr>
</table>
  </form>

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
	
global $CSTSYSKEY,$CSTSYSTEXT,$GROUP;
	global $pf_scriptname;
	$pf_scriptname = 'DashboardGroupBy.php';

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

    // Last Generated CRC: E0E21E0F 41CE0509 171F37DE E477EE9D
    // Path: C:\Program Files\ESDI\WebSmart\temp\Dashboard_www_websmart_htdocs_dashboard_PHW Filels_DashboardGroupBy.phw
}
?>