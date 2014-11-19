<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		menu_code.php 
//	Program Title:		menu code
//	Created by:			Ernie Paredes
//	Template name:		A Simple Page.tpl
//	Purpose:        
//	Program Modifications:

require('/esdi/websmart/v9.0/include/xl_functions001.php');

// Retrieve the task (default to "default")
if ($pf_task == 'default')
{
	generic();
}

function generic()
{
	wrtseg('MainSeg');
}


function wrtseg($segment)
{
	// Make sure it's case insensitive
	$segment = strtolower($segment);

	// Make all global variables available locally
	foreach($GLOBALS as $arraykey=>$arrayvalue) {if($arraykey != "GLOBALS"){global $$arraykey;}}

	// Output the requested segment:

	if($segment == "mainseg")
	{

		echo <<<SEGDTA


<style type="text/css">
/* menu styles */
#drpdwn
{	margin: 0;
padding: 0}

#drpdwn li
{	float: left;
list-style: none;
text-align: center;
font: 11px Tahoma, Arial}

#drpdwn li a
{	display: block;
background: #000099;
padding: 4px 10px;
text-decoration: none;
border-right: 1px solid white;
width: 70px;
color: #EAFFED;
white-space: nowrap}

#drpdwn li a:hover
{	background: #3366FF}

#drpdwn li ul
{	margin: 0;
padding: 0;
position: absolute;
visibility: hidden;
text-align: left;
border-top: 1px solid white}

#drpdwn li ul li
{	float: none;
display: inline}

#drpdwn li ul li a
{	width: auto;
background: #CCCCCC;
color: #000000}

#drpdwn li ul li a:hover
{	background: #999999}
</style>

<script type="text/javascript">
var timeout         = 500;
var closetimer		= 0;
var ddmenuitem      = 0;

function drpdwn_open()
{	drpdwn_canceltimer();
	drpdwn_close();
	ddmenuitem = $(this).find('ul').eq(0).css('visibility', 'visible');}

function drpdwn_close()
{	if(ddmenuitem) ddmenuitem.css('visibility', 'hidden');}

function drpdwn_timer()
{	closetimer = window.setTimeout(drpdwn_close, timeout);}

function drpdwn_canceltimer()
{	if(closetimer)
	{	window.clearTimeout(closetimer);
		closetimer = null;}}

$(document).ready(function()
{	$('#drpdwn > li').bind('mouseover', drpdwn_open);
	$('#drpdwn > li').bind('mouseout',  drpdwn_timer);});

document.onclick = drpdwn_close;
</script>

<table width="100%" border="0">
  <tr>
    <td width="100%" valign="bottom" bgcolor="#000099">
      <ul id="drpdwn">
      <li><a href="DashboardMain.php" title="
      
SEGDTA;

      echo "Current Settings     &#10;&#10; $username  &#10;  $firstnm $lastnm  "; 

      if ($view == 'MAIN') echo "&#10;&#10;  View All  "; 
      if ($view == 'ERROR') echo "&#10;&#10;  View Only Errors  "; 
      if ($view == 'MIMIX') echo "&#10;&#10;  View MIMIX  "; 
      if ($view == 'MIMIXERROR') echo "&#10;&#10;  View MIMIX Errors  "; 
      
      if ($refresh == 5940) echo "&#10;&#10;  Refresh No  &#10;";
      if ($refresh == 60) echo "&#10;&#10;  Refresh Every 1 Minute  &#10;";
      if ($refresh == 300) echo "&#10;&#10;  Refresh Every 5 Minutes  &#10;";
      if ($refresh == 600) echo "&#10;&#10;  Refresh Every 10 Minutes  &#10;";
      if ($refresh == 900) echo "&#10;&#10;  Refresh Every 15 Minutes  &#10;";
      if ($refresh == 1800) echo "&#10;&#10;  Refresh Every 30 Minutes  &#10;";
      if ($refresh == 3600) echo "&#10;&#10;  Refresh Every 60 Minutes  &#10;";
      
		echo <<<SEGDTA

      ">Home / Refresh</a></li>
      <li><a href="#">Views</a>
      <ul>
      <li><a href="#" onClick="form1.view.value = &quot;main&quot;;return Submit();">
      
SEGDTA;
 if ($view == 'MAIN') echo '<img src="images/correct.gif" border="0">'; else echo '<img src="images/blank.gif" border="0">'; 
		echo <<<SEGDTA
 View All</a></li>
      <li><a href="#" onClick="form1.view.value = &quot;error&quot;;return Submit();">
      
SEGDTA;
 if ($view == 'ERROR') echo '<img src="images/correct.gif" border="0">'; else echo '<img src="images/blank.gif" border="0">'; 
		echo <<<SEGDTA
 View Only Errors</a></li>
      <li><a href="#" onClick="form1.view.value = &quot;mimix&quot;;return Submit();">
      
SEGDTA;
 if ($view == 'MIMIX') echo '<img src="images/correct.gif" border="0">'; else echo '<img src="images/blank.gif" border="0">'; 
		echo <<<SEGDTA
 View MIMIX</a></li>
      <li><a href="#" onClick="form1.view.value = &quot;mimixerror&quot;;return Submit();">
      
SEGDTA;
 if ($view == 'MIMIXERROR') echo '<img src="images/correct.gif" border="0">'; else echo '<img src="images/blank.gif" border="0">'; 
		echo <<<SEGDTA
 View MIMIX Errors</a></li>
      </ul>
      </li>
      <li><a href="#">Auto Refresh</a>
      <ul>
      <li><a href="#" onClick="form1.refresh.value = &quot;5940&quot;;return Submit();">
      
SEGDTA;
 if ($refresh == 5940) echo '<img src="images/correct.gif" border="0">'; else echo '<img src="images/blank.gif" border="0">'; 
		echo <<<SEGDTA
 &nbsp;&nbsp;&nbsp;N/A</a></li>
      <li><a href="#" onClick="form1.refresh.value = &quot;60&quot;;return Submit();">
      
SEGDTA;
 if ($refresh == 60) echo '<img src="images/correct.gif" border="0">'; else echo '<img src="images/blank.gif" border="0">'; 
		echo <<<SEGDTA
 1 Minute</a></li>
      <li><a href="#" onClick="form1.refresh.value = &quot;300&quot;;return Submit();">
      
SEGDTA;
 if ($refresh == 300) echo '<img src="images/correct.gif" border="0">'; else echo '<img src="images/blank.gif" border="0">'; 
		echo <<<SEGDTA
 5 Minutes</a></li>
      <li><a href="#" onClick="form1.refresh.value = &quot;600&quot;;return Submit();">
      
SEGDTA;
 if ($refresh == 600) echo '<img src="images/correct.gif" border="0">'; else echo '<img src="images/blank.gif" border="0">'; 
		echo <<<SEGDTA
 10 Minutes</a></li>
      <li><a href="#" onClick="form1.refresh.value = &quot;900&quot;;return Submit();">
      
SEGDTA;
 if ($refresh == 900) echo '<img src="images/correct.gif" border="0">'; else echo '<img src="images/blank.gif" border="0">'; 
		echo <<<SEGDTA
 15 Minutes</a></li>
      <li><a href="#" onClick="form1.refresh.value = &quot;1800&quot;;return Submit();">
      
SEGDTA;
 if ($refresh == 1800) echo '<img src="images/correct.gif" border="0">'; else echo '<img src="images/blank.gif" border="0">'; 
		echo <<<SEGDTA
 30 Minutes</a></li>
      <li><a href="#" onClick="form1.refresh.value = &quot;3600&quot;;return Submit();">
      
SEGDTA;
 if ($refresh == 3600) echo '<img src="images/correct.gif" border="0">'; else echo '<img src="images/blank.gif" border="0">'; 
		echo <<<SEGDTA
 60 Minutes</a></li>
      </ul>
      </li>
      <li><a href="#">Groups</a>
      <ul>
      <li><a href="DashboardGroupBy.php">Group By</a></li>
      <li><a href="#">Add a Group</a></li>
      <li><a href="#">Work with Groups</a></li>
      </ul>
      </li>
      <li><a href="#">Users</a>
      <ul>
      <li><a href="DBUsers.php">Work with Users</a></li>
      <li><a href="#">View Usage</a></li>
      </ul>
      </li>
      <li><a href="#">Reports</a>
      <ul>
      <li><a href="#">Report 1</a></li>
      <li><a href="#">Report 2</a></li>
      <li><a href="#">Report 3</a></li>
      <li><a href="#">Report 4</a></li>
      <li><a href="#">Report 5</a></li>
      </ul>
      </li>
      <li><a href="#">Graphs</a></li>
      <li><a href="#">Help</a></li>
      <li><a href="DashboardMain.php?task=off">Log Out</a></li>
      </ul>
      
    </td>
  </tr>
</table>
<br />


SEGDTA;
		return;
	}

	// If we reach here, the segment is not found
	echo("Segment $segment is not defined! ");
}

function internal_init()
{
	
	global $pf_scriptname;
	$pf_scriptname = 'menu_code.php';

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


    // Last Generated CRC: 6E34E11E DF2329BD A46D1591 0C1666F5
    // Path: C:\Program Files\ESDI\WebSmart\temp\Dashboard_www_websmart_htdocs_dashboard_includes_menu_code.phw
}
?>