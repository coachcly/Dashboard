<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		FPTFGRP.php
//	Program Title:		PTF Group
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

// As a default task for this program, execute the display function
if ($pf_task == 'default')
{
	displayGRP();
	displaySUM();
	displayCUR();
}

// close the database connection
db2_close($db2conn);   

/********************
 End of mainline code
 ********************/
function displayGRP()
{ 
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	$query = "select PTFGRP, PTFGRPLVL, PTFGRPSTS, PTFGRPTXT  
	from DASHBOARD/FPTFGRP 
	where PTFGRPKEY  = 'Q10B518103'
	order by PTFGRP, PTFGRPLVL";
	
	// Fetch rows for page: relative to initial cursor 
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		die("<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	// Output header
	wrtseg("ListHeaderGRP"); 
	
	while ($row = db2_fetch_assoc($stmt))
	{
		// set color of the line
		xl_set_row_color('altcol1', 'altcol2');
		
		
		$PTFGRP = $row['PTFGRP'];
		$PTFGRPLVL = $row['PTFGRPLVL'];
		$PTFGRPSTS = $row['PTFGRPSTS'];
		$PTFGRPTXT = $row['PTFGRPTXT'];
		
		// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
		sanitize_output('DATA');
		
		wrtseg("ListDetailsGRP");
		
	}
	
	wrtseg("ListFooterGRP");
}


function displaySUM()
{ 
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	$query = "select PTFSUMCTR, PTFSUMSTS
	from DASHBOARD/FPTFSUM 
	where PTFSUMKEY  = 'Q10B518103'";
	
	// Fetch rows for page: relative to initial cursor 
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		die("<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	// Output header
	wrtseg("ListHeaderSUM"); 
	
	while ($row = db2_fetch_assoc($stmt))
	{
		// set color of the line
		xl_set_row_color('altcol1', 'altcol2');
		
		
		$PTFSUMCTR = $row['PTFSUMCTR'];
		$PTFSUMSTS = $row['PTFSUMSTS'];
		
		// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
		sanitize_output('DATA');
		
		wrtseg("ListDetailsSUM");
		
	}
	
	wrtseg("ListFooterSUM");
}


function displayCUR()
{ 
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	$query = "select OSRLS, PTFID, PTFLVL, CUMPTFPKG  
	from DASHBOARD/FPTFCUR 
	order by OSRLS";
	
	// Fetch rows for page: relative to initial cursor 
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		die("<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	// Output header
	wrtseg("ListHeaderCUR"); 
	
	while ($row = db2_fetch_assoc($stmt))
	{
		// set color of the line
		xl_set_row_color('altcol1', 'altcol2');
		
		
		$OSRLS = $row['OSRLS'];
		$PTFID = $row['PTFID'];
		$PTFLVL = $row['PTFLVL'];
		$CUMPTFPKG = $row['CUMPTFPKG'];
		
		// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
		sanitize_output('DATA');
		
		wrtseg("ListDetailsCUR");
		
	}
	
	wrtseg("ListFooterCUR");
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


function wrtseg($segment)
{
	// Make sure it's case insensitive
	$segment = strtolower($segment);

	// Make all global variables available locally
	foreach($GLOBALS as $arraykey=>$arrayvalue) {if($arraykey != "GLOBALS"){global $$arraykey;}}

	// Output the requested segment:

	if($segment == "listheadergrp")
	{

		echo <<<SEGDTA
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta name="generator" content="WebSmart" />
    <meta http-equiv="Pragma" content="no-cache" />
    <title>PTF Group</title>
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/print.css" media="print" />
    <script type="text/javascript" src="/websmart/v9.0/javascript/jquery.min.js"></script>
  </head>
  <body>
    <div id="pagetitle" class="pagetitle"> PTF Group </div>
    <div id="pagetitleborder"></div>
    
    <div id="contents">
      <table id="listtable" class="mainlist">
        <tr>
          <th width="80" nowrap="nowrap"><div align="center">PTF<br>Group</div></th>
          <th width="80" nowrap="nowrap"><div align="center">PTF<br>Group<br>Level</div></th>
          <th width="150" nowrap="nowrap"><div align="left">PTF<br>Group<br>Status</div></th>
          <th width="250" nowrap="nowrap"><div align="left">PTF<br>Group<br>Text</div></th>
        </tr>
        
SEGDTA;
		return;
	}
	if($segment == "listdetailsgrp")
	{

		echo <<<SEGDTA
<tr class="$pf_altrowclr">
  <td nowrap="nowrap" class="text"><div align="center">$PTFGRP</div></td>
  <td nowrap="nowrap" class="text"><div align="right">$PTFGRPLVL &nbsp;&nbsp;&nbsp;&nbsp;</div></td>
  <td nowrap="nowrap" class="text">$PTFGRPSTS</td>
  <td nowrap="nowrap" class="text">$PTFGRPTXT</td>
</tr>

SEGDTA;
		return;
	}
	if($segment == "listfootergrp")
	{

		echo <<<SEGDTA
</table>

SEGDTA;
		return;
	}
	if($segment == "listheadersum")
	{

		echo <<<SEGDTA
<br><br>
<table id="listtable" class="mainlist">
  <tr>
    <th width="80" nowrap="nowrap"><div align="center">PTF<br>Summary<br>Counter</div></th>
    <th width="250" nowrap="nowrap"><div align="left">PTF<br>Summary<br>Status</div></th>
  </tr>
  
SEGDTA;
		return;
	}
	if($segment == "listdetailssum")
	{

		echo <<<SEGDTA
<tr class="$pf_altrowclr">
  <td nowrap="nowrap" class="text"><div align="right">$PTFSUMCTR &nbsp;&nbsp;&nbsp;&nbsp;</div></td>
  <td nowrap="nowrap" class="text">$PTFSUMSTS</td>
</tr>

SEGDTA;
		return;
	}
	if($segment == "listfootersum")
	{

		echo <<<SEGDTA
</table>

SEGDTA;
		return;
	}
	if($segment == "listheadercur")
	{

		echo <<<SEGDTA
<br><br>
<table id="listtable" class="mainlist">
  <tr>
    <th width="90" nowrap="nowrap"><div align="center">OS<br>Release</div></th>
    <th width="90" nowrap="nowrap"><div align="center">PTF<br>ID</div></th>
    <th width="70" nowrap="nowrap"><div align="center">PTF<br>Level</div></th>
    <th width="90" nowrap="nowrap"><div align="center">Current<br>Cummulative<br>PTF Package</div></th>
  </tr>
  
SEGDTA;
		return;
	}
	if($segment == "listdetailscur")
	{

		echo <<<SEGDTA
<tr class="$pf_altrowclr">
  <td nowrap="nowrap" class="text"><div align="center">$OSRLS</div></td>
  <td nowrap="nowrap" class="text"><div align="center">$PTFID</div></td>
  <td nowrap="nowrap" class="text"><div align="right">$PTFLVL &nbsp;&nbsp;&nbsp;&nbsp;</div></td>
  <td nowrap="nowrap" class="text"><div align="center">$CUMPTFPKG</div></td>
</tr>

SEGDTA;
		return;
	}
	if($segment == "listfootercur")
	{

		echo <<<SEGDTA
</table>
</div>
<!--------------- Begin Footer --------------->
<!--------------- End Footer --------------->
<br>
<br>
<h2>
<strong>Other Helpful Links: &nbsp;&nbsp;&nbsp;&nbsp;
<a href="http://www-912.ibm.com/a_dir/as4ptf.nsf/GroupPTFs?OpenView&amp;view=GroupPTFs" target="_blank">IBM</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="http://www.itjungle.com/ptf/DLB-PTF_091711_V13N02.html" target="_blank">IT Jungle</a>
</strong>
</h2>
<br>
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
	
global $PTFGRPKEY,$SYSTEM,$PTFGRP,$PTFGRPLVL,$PTFGRPSTS,$PTFGRPTXT;
global $PTFSUMKEY,$SYSTEM,$PTFSUMCTR,$PTFSUMSTS;
global $OSRLS,$PTFID,$PTFLVL,$CUMPTFPKG;
	global $pf_scriptname;
	$pf_scriptname = 'FPTFGRP.php';

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

    // Last Generated CRC: A61746CF 8AE7F3DB 49F02E4E 124EADFF
    // Path: C:\Premise\WIP\FPTFGRP.phw
}
?>