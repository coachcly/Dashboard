<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey[0]!='_' && $arraykey!="GLOBALS")global $$arraykey;}

//	Program Name:		ErnieTesting1.php
//	Program Title:		Testing
//	Created by:			143627
//	Template family:	Idaho
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
global $ww_ordby, $ww_orddir, $ww_page, $ww_nx, $ww_prevpage, $ww_nextpage, $ww_listsize, $ww_whrclause, $ww_selstring, $ww_program_state, $ww_count;
global $x;
$x=1;

// Set maximum list size to 20 for this program
$ww_listsize = 20;

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


// run the specified task
switch($pf_task)
{
	case 'default':
	display();
	break;
	
	// Record display option
	case 'disp':
	disprcd();
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
		$CMCUST = trim($row['CMCUST']);
		$CMNAME = trim($row['CMNAME']);
		$CMCITY = trim($row['CMCITY']);
		$CMSTATE = trim($row['CMSTATE']);
		$CMCOUNT = trim($row['CMCOUNT']);
		$CMPOST = trim($row['CMPOST']);
		
		// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
		sanitize_output('DATA');
		
		wrtseg('ListDetails');
		$index++;
		$x++;
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
	
	$ww_selstring = 'SELECT MU_CUSTF.CMCUST, MU_CUSTF.CMNAME, MU_CUSTF.CMCITY, MU_CUSTF.CMSTATE, MU_CUSTF.CMCOUNT, MU_CUSTF.CMPOST FROM XL_WEBDEMO/MU_CUSTF'; 
	
	
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
		$ww_selstring = trim($ww_selstring) . ' order by CMCUST ';
	}
}                         

// Display details for selected record:
function disprcd()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey[0]!='_' && $arraykey != "GLOBALS")
			global $$arraykey;
	}
	
	// Get the key field values which identify the record
	$CMCUST = xl_get_parameter('CMCUST');
	
	
	// Make sure our key values match only a single record
	$result = db2_exec($db2conn, "SELECT COUNT(*) FROM XL_WEBDEMO/MU_CUSTF where CMCUST = ". xl_encode($CMCUST, 'db2_search') ."");
	$row = db2_fetch_array($result);
	if($row[0] > 1)
	{
		db2_close($db2conn);
		die("Error: More than one record is identified by the key values you've specified. No record has been displayed.");
	}
	
	// Fetch the row for page
	$sqlstr = "SELECT  CMCUST, CMNAME, CMADR1, CMCITY, CMSTATE, CMCOUNT, CMPOST, CMAREA, CMPHON, CMCONT, CMEMAIL FROM XL_WEBDEMO/MU_CUSTF WHERE CMCUST = ". xl_encode($CMCUST, 'db2_search') ."";
	if (!($result = db2_exec($db2conn, $sqlstr))) 
	{
		db2_close($db2conn);
		die("<b>Error ".db2_stmt_error().":".db2_stmt_errormsg()."</b>"); 
	}
	
	// put the result into global variable and show it    
	$row = db2_fetch_assoc($result);
	
	// Get fields 
	$CMNAME = $row['CMNAME'];
	$CMADR1 = $row['CMADR1'];
	$CMCITY = $row['CMCITY'];
	$CMSTATE = $row['CMSTATE'];
	$CMCOUNT = $row['CMCOUNT'];
	$CMPOST = $row['CMPOST'];
	$CMAREA = $row['CMAREA'];
	$CMPHON = $row['CMPHON'];
	$CMCONT = $row['CMCONT'];
	$CMEMAIL = $row['CMEMAIL'];
	
	// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
	sanitize_output('DATA');
	
	// output the segment
	wrtseg('rcddisplay');
}


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
		$CMCUST = htmlspecialchars($CMCUST, ENT_QUOTES);
		$CMNAME = htmlspecialchars($CMNAME, ENT_QUOTES);
		$CMADR1 = htmlspecialchars($CMADR1, ENT_QUOTES);
		$CMCITY = htmlspecialchars($CMCITY, ENT_QUOTES);
		$CMSTATE = htmlspecialchars($CMSTATE, ENT_QUOTES);
		$CMCOUNT = htmlspecialchars($CMCOUNT, ENT_QUOTES);
		$CMPOST = htmlspecialchars($CMPOST, ENT_QUOTES);
		$CMAREA = htmlspecialchars($CMAREA, ENT_QUOTES);
		$CMPHON = htmlspecialchars($CMPHON, ENT_QUOTES);
		$CMCONT = htmlspecialchars($CMCONT, ENT_QUOTES);
		$CMEMAIL = htmlspecialchars($CMEMAIL, ENT_QUOTES);
	}
}


function wrtseg($segment)
{
	// Make sure it's case insensitive
	$segment = strtolower($segment);

	// Make all global variables available locally
	foreach($GLOBALS as $arraykey=>$arrayvalue) {if($arraykey[0]!='_' && $arraykey != "GLOBALS")global $$arraykey;}

	// Output the requested segment:

	if($segment == "listheader")
	{

		echo <<<SEGDTA
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta name="generator" content="WebSmart" />
    <meta http-equiv="Pragma" content="no-cache" />
    <title>Testing</title>
    
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Idaho/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Idaho/css/print.css" media="print" />
    <link rel="stylesheet" href="javascript/jQuery/jquery-ui.css" type="text/css" />
    <script type="text/javascript" src="javascript/jQuery/jquery.js"></script>
    <script type="text/javascript" src="javascript/jQuery/jquery-ui.js"></script>
    
    
    <script type="text/javascript">
		//focus the first input on page load
		jQuery(document).ready( function()
		{
			jQuery('input[disabled=false]:first').focus();
		});

$.fx.speeds._default = 1000;
	function ajaxCall(CMCUST, rowid)
	{
	  $("table td").css('background-color','');
	  var parms = "task=disp&CMCUST=" + CMCUST
	  $.ajax(
	   {
	     type: "POST",
	     url: "$pf_scriptname",
	     data: parms,
	     success: function(data)
	              {   	    

      	            $("#" + rowid + " td").css('background-color','#66FFCC');
                    $("#dialog").dialog(
                                        { 
                                           buttons: 
                                                   { "Close": function() 
                                                           {  
                                                             $("#dialog").dialog('close'); 
                                                             $("#" + rowid + " td").css('background-color','');
                                                           }
                                                   },
                                            show: 'blind',
                                            hide: 'blind',
                                            position: [200, 100], 
                                            minWidth: 350,
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
	</script>
    <style type="text/css">
    <!--
.ui-dialog .ui-dialog-content {
    border: 2;
    padding: .5em 1em;
    background: #B0FFFF;
    overflow: auto;
    zoom: 1;
}
.ui-dialog .ui-dialog-titlebar { background: #999999; }
.ui-dialog .ui-dialog-buttonpane { background: #CCCCCC; }
-->
    </style>
    
  </head>
  <body>
    <!--Dialog-->
    <div id="dialog" class="style1" title="Details"></div>
    
    <div id="pagetitle" class="pagetitle"> Testing </div>
    <div id="pagetitleborder"></div>
    
    <div id="contents">
      
      <div id="listtopcontrol">
        <a id="prevlinktop" class="prevlink nondisp">Previous $ww_listsize</a>
        
        <a id="nextlinktop" class="nextlink nondisp">Next $ww_listsize</a>
      </div> 
      <table id="listtable" class="mainlist">
        <tr>
          <th>Action</th>
          <th width="70"><a href="$pf_scriptname?ordby=CMCUST&rnd=$rnd">Customer Number</a></th>
          <th width="500"><a href="$pf_scriptname?ordby=CMNAME&rnd=$rnd">Customer Name</a></th>
          <th width="200"><a href="$pf_scriptname?ordby=CMCITY&rnd=$rnd">City</a></th>
          <th width="20"><a href="$pf_scriptname?ordby=CMSTATE&rnd=$rnd">State/Prov</a></th>
          <th width="20"><a href="$pf_scriptname?ordby=CMCOUNT&rnd=$rnd">Country</a></th>
          <th width="70"><a href="$pf_scriptname?ordby=CMPOST&rnd=$rnd">Postal/Zip Code</a></th>
        </tr>
        
SEGDTA;
		return;
	}
	if($segment == "listdetails")
	{

		echo <<<SEGDTA

<tr class="$pf_altrowclr" id="$x">
  <td class="actions">
  
  <!--<a href="$pf_scriptname?task=disp&CMCUST=
SEGDTA;
 echo urlencode($CMCUST); 
		echo <<<SEGDTA
&rnd=$rnd"><img src="/websmart/v9.0/Idaho/images/view.gif" alt="Display" title="Display"></a>-->
  <a href="#" onClick="ajaxCall('$CMCUST', '$x');"><img src="/websmart/v9.0/Idaho/images/view.gif" alt="Display" title="Display"></a>

  </td>
  <td>$CMCUST</td>
  <td>$CMNAME</td>
  <td>$CMCITY</td>
  <td>$CMSTATE</td>
  <td>$CMCOUNT</td>
  <td>$CMPOST</td>
  
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
		jQuery(".prevlink").attr("href", "$pf_scriptname?page=$ww_prevpage&rnd=$rnd");
	}
	
	// write the NEXT link if necessary
	if ($ww_count == $ww_listsize) 
	{
		jQuery(".nextlink").removeClass("nondisp");
		jQuery(".nextlink").attr("href", "$pf_scriptname?page=$ww_nextpage&rnd=$rnd");
	}	
</script>

</body>
</html>

SEGDTA;
		return;
	}
	if($segment == "rcddisplay")
	{

		echo <<<SEGDTA

      <table id="displaytable">
        
        <tr><td><strong>Customer Number:</strong></td>
        <td>$CMCUST</td></tr>
        <tr><td><strong>Customer Name:</strong></td>
        <td>$CMNAME</td></tr>
        <tr><td><strong>Address 1:</strong></td>
        <td>$CMADR1</td></tr>
        <tr><td><strong>City:</strong></td>
        <td>$CMCITY</td></tr>
        <tr><td><strong>State/Prov:</strong></td>
        <td>$CMSTATE</td></tr>
        <tr><td><strong>Country:</strong></td>
        <td>$CMCOUNT</td></tr>
        <tr><td><strong>Postal/Zip Code:</strong></td>
        <td>$CMPOST</td></tr>
        <tr><td><strong>Area Code:</strong></td>
        <td>$CMAREA</td></tr>
        <tr><td><strong>Phone Number:</strong></td>
        <td>$CMPHON</td></tr>
        <tr><td><strong>Contact Name:</strong></td>
        <td>$CMCONT</td></tr>
        <tr><td><strong>Email Address:</strong></td>
        <td>$CMEMAIL</td></tr>
      </table>
<br><br>
SEGDTA;
		return;
	}
	if($segment == "rtntolist")
	{

		echo <<<SEGDTA

<button onClick="javascript:location.href='?page=$ww_page';">Back</button>
SEGDTA;
		return;
	}

	// If we reach here, the segment is not found
	echo("Segment $segment is not defined! ");
}

function internal_init()
{
	
global $CMCUST,$CMNAME,$CMADR1,$CMADR2,$CMCITY,$CMSTATE,$CMCOUNT,$CMPOST,$CMAREA,$CMPHON,$CMCONT,$CMEMAIL,$CMTERM,$CMDACR,$CMDSCNT;
	global $pf_scriptname;
	$pf_scriptname = 'ErnieTesting1.php';

	session_start();

	global $pf_task;
	if(isset($_REQUEST['task']))
		$pf_task = $_REQUEST['task'];
	else
		$pf_task = 'default';
	
	// this is an array
	global $pf_liblLibs;

$pf_liblLibs[1] = 'XL_WEBDEMO';

}
?>