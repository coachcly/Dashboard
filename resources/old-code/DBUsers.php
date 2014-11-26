<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		DBUsers.php
//	Program Title:		DB Users
//	Created by:			Ernie Paredes
//	Template family:	Lincoln
//	Template name:		Page at a Time (by Key).tpl
//	Purpose:        	Maintain a database file using embedded SQL. Supports options for add, change, delete and display. 
//	Program Modifications:

require('/esdi/websmart/v9.0/include/xl_functions001.php');

// DB Connection code
$options = array('i5_naming' => DB2_I5_NAMING_ON, 'i5_date_fmt' => DB2_I5_FMT_USA, 'i5_time_fmt' => DB2_I5_FMT_USA);

global $db2conn;
$db2conn = xl_db2_connect($options);

if(!$db2conn)
{
	die('Could not connect to database: ' . db2_conn_error());
}

global $USERNAME_filt;
global $LASTNM_filt;
// Global variables should be defined here
global $username, $firstnm, $lastnm, $seclevel, $default_logo;
global $view, $refresh, $group, $machines;
global $ww_rrn, $ww_ordby, $ww_orddir, $ww_page, $ww_nx, $ww_prevpage, $ww_nextpage, $ww_listsize, $ww_whrclause, $ww_selstring, $ww_program_state, $ww_count;
global $pf_encrypt_key, $PASSWORD1, $usernamemsg, $passwordmsg, $firstnmmsg, $lastnmmsg, $emailmsg;

// Set decryption key
$pf_encrypt_key = null;

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

if (isset($ww_program_state['USERNAME_filt']))
	$USERNAME_filt = $ww_program_state['USERNAME_filt'];
if (isset($ww_program_state['LASTNM_filt']))
	$LASTNM_filt = $ww_program_state['LASTNM_filt'];

getsession();
// Check WEBCOP
webcop('DASHBOARD', $username, '2');

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
	
	// Delete confirm
	case 'delconf':
	disprcd();
	break;
	
	// Actual record deletion
	case 'del':
	deletercd();
	break;
	
	// Start the add process
	case 'beginadd': 
	beginadd();
	break;
	// Complete the add process
	case 'endadd':
	endadd();
	break; 
	// Start the change process
	case 'beginchange':
	beginchange();
	break;
	// Complete the change process
	case 'endchange':
	endchange();
	break;    
	
	case 'filter':
	filter();
	break;
	
	case 'forgotpwd':
	forgotpwd();
	break;
	
	
}

//Release the database resource
db2_close($db2conn);
/********************
 End of mainline code
 ********************/


// Load page with filters
function filter()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey[0]!='_' && $arraykey != "GLOBALS")
			global $$arraykey;
	}
	
	// get the filters from request 
	
	$USERNAME_filt = strtoupper(trim(xl_get_parameter('USERNAME_filt')));
	$LASTNM_filt = strtoupper(trim(xl_get_parameter('LASTNM_filt')));
	
	// save filter into session 
	$ww_program_state['USERNAME_filt'] = $USERNAME_filt;
	$ww_program_state['LASTNM_filt'] = $LASTNM_filt;
	display();
}
// Load first page and use ordby parameter from form to determine new sort order, direction 
function display()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey[0]!='_' && $arraykey != "GLOBALS")
			global $$arraykey;
	}
	
	
	// Ensure that filtering criteria are valid.
	
	
	
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
		$ww_rrn = $row['00001'];
		$USERNAME = trim($row['USERNAME']);
		$LASTNM = trim($row['LASTNM']);
		$FIRSTNM = trim($row['FIRSTNM']);
		$DISABLE = trim($row['DISABLE']);
		$SECLEVEL = trim($row['SECLEVEL']);
		$EMAIL = trim($row['EMAIL']);
		$PWDCHNGDT = trim($row['PWDCHNGDT']);
		$RCDDTSTMP = trim($row['RCDDTSTMP']);
		$RCDTMSTMP = trim($row['RCDTMSTMP']);
		
		// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
		sanitize_output('DATA');
		
		if ($DISABLE == 'Y') $DISABLE = '<img src="images/Check.gif" title="User Disabled" border="0">';
		
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
	
	$ww_selstring = 'SELECT RRN(DBUSERS), DBUSERS.USERNAME, DBUSERS.LASTNM, DBUSERS.FIRSTNM, DBUSERS.DISABLE, DBUSERS.SECLEVEL, DBUSERS.EMAIL, DBUSERS.PWDCHNGDT, DBUSERS.RCDDTSTMP, DBUSERS.RCDTMSTMP FROM dashboard/DBUSERS'; 
	
	/**** Build 'where' clause ****/
	
	$ww_whrclause = '';
	$whrlink = " where";
	
	// filter by USERNAME
	if ($USERNAME_filt <> '')
	{
		$ww_whrclause = trim($ww_whrclause) . $whrlink . ' DBUSERS.USERNAME like ' . "'" . xl_encode(trim($USERNAME_filt), 'db2_search') . "%'";
		$whrlink = " and";
	}
	
	// filter by LASTNM
	if ($LASTNM_filt <> '')
	{
		$ww_whrclause = trim($ww_whrclause) . $whrlink . ' upper(DBUSERS.LASTNM) like ' . "'" . xl_encode(trim($LASTNM_filt), 'db2_search') . "%'";
		$whrlink = " and";
	}
	
	$ww_selstring = trim($ww_selstring) . ' ' . $ww_whrclause;
	
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
		$ww_selstring = trim($ww_selstring) . ' order by USERNAME ';
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
	$USERNAME = xl_get_parameter('USERNAME');
	
	
	// Make sure our key values match only a single record
	$result = db2_exec($db2conn, "SELECT COUNT(*) FROM dashboard/DBUSERS where USERNAME = '". xl_encode($USERNAME, 'db2_search') ."'");
	$row = db2_fetch_array($result);
	if($row[0] > 1)
	{
		db2_close($db2conn);
		die("Error: More than one record is identified by the key values you've specified. No record has been displayed.");
	}
	
	// Fetch the row for page
	$sqlstr = "SELECT  FIRSTNM, LASTNM, USERNAME, PASSWORD, FORCEPWCHG, EMAIL, DISABLE, SECLEVEL, DEFAULTGP, DEFAULTVW, DEFAULTRF, DEFAULTLOG, RCDDTSTMP, RCDTMSTMP, PWDCHNGDT FROM dashboard/DBUSERS WHERE USERNAME = '". xl_encode($USERNAME, 'db2_search') ."'";
	if (!($result = db2_exec($db2conn, $sqlstr))) 
	{
		db2_close($db2conn);
		die("<b>Error ".db2_stmt_error().":".db2_stmt_errormsg()."</b>"); 
	}
	
	// put the result into global variable and show it    
	$row = db2_fetch_assoc($result);
	
	// Get fields 
	$FIRSTNM = $row['FIRSTNM'];
	$LASTNM = $row['LASTNM'];
	$PASSWORD = $row['PASSWORD'];
	$FORCEPWCHG = $row['FORCEPWCHG'];
	$EMAIL = $row['EMAIL'];
	$DISABLE = $row['DISABLE'];
	$SECLEVEL = $row['SECLEVEL'];
	$DEFAULTGP = $row['DEFAULTGP'];
	$DEFAULTVW = $row['DEFAULTVW'];
	$DEFAULTRF = $row['DEFAULTRF'];
	$DEFAULTLOG = $row['DEFAULTLOG'];
	$RCDDTSTMP = $row['RCDDTSTMP'];
	$RCDTMSTMP = $row['RCDTMSTMP'];
	$PWDCHNGDT = $row['PWDCHNGDT'];
	
	// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
	sanitize_output('DATA');
	
	if ($DISABLE == 'Y') $DISABLE = '<img src="images/Check.gif" title="User Disabled" border="0">';
	if ($FORCEPWCHG == 'Y') $FORCEPWCHG = '<img src="images/Check.gif" title="$FORCEPWCHG" border="0">';
	
	// output the segment
	wrtseg('rcddisplay');
}

// Delete the record
function deletercd() 
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey[0]!='_' && $arraykey != "GLOBALS")
			global $$arraykey;
	}
	
	// Get the key field values which identify the record
	$USERNAME = xl_get_parameter('USERNAME');
	
	
	// Make sure we'll only be deleting a single record
	$result = db2_exec($db2conn, "SELECT COUNT(*) from dashboard/DBUSERS where USERNAME = '". xl_encode($USERNAME, 'db2_search') ."'");
	$row = db2_fetch_array($result);
	if($row[0] > 1)
	{
		db2_close($db2conn);
		die("Error: More than one record is identified by the key values you've specified. Record was NOT deleted.");
	}
	
	// delete the record and avoid deleting the whole table
	$result = db2_exec($db2conn, "DELETE from dashboard/DBUSERS where USERNAME = '". xl_encode($USERNAME, 'db2_search') ."'");
	if (!$result)
	{
		db2_close($db2conn);
		die("Error" .db2_stmt_error() . ":" . db2_stmt_errormsg());
	}
	
	// Redirect to display page
	header("Location: $pf_scriptname?page=" . (string)$ww_page);
}

// Present panel to prepare to modify records:
function beginchange() 
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey[0]!='_' && $arraykey != "GLOBALS")
			global $$arraykey;
	}
	
	// Get the key field values which identify the record
	$USERNAME = xl_get_parameter('USERNAME');
	
	
	$sqlstatement = "SELECT  USERNAME , FIRSTNM, LASTNM, PASSWORD, FORCEPWCHG, EMAIL, DISABLE, SECLEVEL, DEFAULTGP, DEFAULTVW, DEFAULTRF, DEFAULTLOG, RCDDTSTMP, RCDTMSTMP, PWDCHNGDT FROM dashboard/DBUSERS ";
	$condition = "WHERE USERNAME = '". xl_encode($USERNAME, 'db2_search') ."'";
	
	$sqlstatement .= $condition;
	
	// Make sure we would only be changing a single record
	$result = db2_exec($db2conn, "SELECT COUNT(*) as ROWCOUNT FROM dashboard/DBUSERS ".$condition);
	$row = db2_fetch_array($result);
	if($row[0] > 1)
	{
		db2_close($db2conn);
		die("Updating rows with requested parameters would change more than one record. A record was not opened for changes.");
	}	
	
	// Fetch the row for page
	if (!($result = db2_exec($db2conn, $sqlstatement, array('CURSOR' => DB2_SCROLLABLE)))) 
	{
		db2_close($db2conn);
		die("<b>Error ". db2_stmt_error().":" . db2_stmt_errormsg()."</b>"); 
	}
	
	// put the result into global variable and show it    
	$row = db2_fetch_assoc($result);
	
	// Get the fields 
	
	$FIRSTNM = rtrim($row['FIRSTNM']);
	$LASTNM = rtrim($row['LASTNM']);
	$PASSWORD = rtrim($row['PASSWORD']);
	$PASSWORD1 = rtrim($row['PASSWORD']);
	$FORCEPWCHG = rtrim($row['FORCEPWCHG']);
	$EMAIL = rtrim($row['EMAIL']);
	$DISABLE = rtrim($row['DISABLE']);
	$SECLEVEL = rtrim($row['SECLEVEL']);
	$DEFAULTGP = rtrim($row['DEFAULTGP']);
	$DEFAULTVW = rtrim($row['DEFAULTVW']);
	$DEFAULTRF = rtrim($row['DEFAULTRF']);
	$DEFAULTLOG = rtrim($row['DEFAULTLOG']);
	$RCDDTSTMP = rtrim($row['RCDDTSTMP']);
	$RCDTMSTMP = rtrim($row['RCDTMSTMP']);
	$PWDCHNGDT = rtrim($row['PWDCHNGDT']);
	
	// Password decryption
	$PASSWORD = base64_decode($PASSWORD);
	if ($PASSWORD != null) {
		$PASSWORD = trim(xl_decrypt($PASSWORD));
		$PASSWORD1 = $PASSWORD;
	}
	
	
	// Converts special characters in the data fields to their HTML entities. This will prevent most XSS attacks
	sanitize_output('DATA');
	
	// Output the segment
	wrtseg('RcdChange');
}
function beginadd()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey[0]!='_' && $arraykey != "GLOBALS")
			global $$arraykey;
	}
	
	// Initialize data here
	
	//Output the segment
	wrtseg('RcdAdd');
}


function endchange()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey[0]!='_' && $arraykey != "GLOBALS")
			global $$arraykey;
	}
	
	// Get the key field values which identify the record
	$USERNAME_ = xl_get_parameter('USERNAME_');
	
	
	// get values from the page 
	$USERNAME = xl_get_parameter('USERNAME');
	$FIRSTNM = xl_get_parameter('FIRSTNM');
	$LASTNM = xl_get_parameter('LASTNM');
	$PASSWORD = strtoupper(xl_get_parameter('PASSWORD'));
	$PASSWORD1 = strtoupper(xl_get_parameter('PASSWORD1'));
	$FORCEPWCHG = xl_get_parameter('FORCEPWCHG');
	$EMAIL = xl_get_parameter('EMAIL');
	$DISABLE = xl_get_parameter('DISABLE');
	$SECLEVEL = xl_get_parameter('SECLEVEL');
	$DEFAULTGP = xl_get_parameter('DEFAULTGP');
	$DEFAULTVW = xl_get_parameter('DEFAULTVW');
	$DEFAULTRF = xl_get_parameter('DEFAULTRF');
	$DEFAULTLOG = xl_get_parameter('DEFAULTLOG');
	$RCDDTSTMP = xl_get_parameter('RCDDTSTMP');
	if($RCDDTSTMP == '')
		$RCDDTSTMP = '0001-01-01';
	$RCDTMSTMP = xl_get_parameter('RCDTMSTMP');
	if($RCDTMSTMP == '')
		$RCDTMSTMP = '00:00:00';
	$PWDCHNGDT = xl_get_parameter('PWDCHNGDT');
	if($PWDCHNGDT == '')
		$PWDCHNGDT = '0001-01-01';
	
	//Protect Key Fields from being Changed
	$USERNAME = $USERNAME_;
	
	// do any change validation here
	
	// Check if name is blank
	if (strlen($FIRSTNM) == null) {
		$firstnmmsg = '<<< Invalid First Name >>>';
		$errors = '1';
	}
	if (strlen($LASTNM) == null) {
		$lastnmmsg = '<<< Invalid Last Name >>>';
		$errors = '1';
	}
	
	// Validate email
	if(!validateEmail()) {
		$emailmsg = '<<< Invalid eMail >>>';
		$errors = '1';
	}
	
	// Check if the password is to short
	if (strlen($PASSWORD) < 6) {
		$passwordmsg = '<<< Password to short (6 Char Min) >>>';
		$errors = '1';
	}
	
	// Check if the passwords match
	if ($PASSWORD != $PASSWORD1) {
		$passwordmsg = '<<< Passwords do not match >>>';
		$errors = '1';
	}
	
	// Validation Errors found - Return to Page
	if ($errors) {
		wrtseg('RcdChange');
		db2_close($db2conn);
		exit();
	}
	
	// Encrypt Password
	$PASSWORD = xl_encrypt($PASSWORD);
	$PASSWORD = base64_encode($PASSWORD);
	
	$PWDCHNGDT = date("m/d/Y");
	
	// Make sure we wouldn't be creating a duplicate row
	if( ($USERNAME != $USERNAME_) )
	{
		$result = db2_exec($db2conn, "SELECT COUNT(*) FROM dashboard/DBUSERS where USERNAME = '". xl_encode($USERNAME, 'db2_search') ."'");
		$row = db2_fetch_array($result);
		if($row[0] > 0)
		{
			db2_close($db2conn);
			die("Updating rows with requested parameters would change more than one record. A record was not opened for changes.");
		}
	}
	
	// Update row in table. 
	$result = db2_exec($db2conn, "UPDATE dashboard/DBUSERS SET( FIRSTNM, LASTNM, USERNAME, PASSWORD, FORCEPWCHG, EMAIL, DISABLE, SECLEVEL, DEFAULTGP, DEFAULTVW, DEFAULTRF, DEFAULTLOG, RCDDTSTMP, RCDTMSTMP, PWDCHNGDT) = ('"
	. xl_encode($FIRSTNM, 'db2_search') ."', '"
	. xl_encode($LASTNM, 'db2_search') ."', '"
	. xl_encode($USERNAME, 'db2_search') ."', '"
	. xl_encode($PASSWORD, 'db2_search') ."', '"
	. xl_encode($FORCEPWCHG, 'db2_search') ."', '"
	. xl_encode($EMAIL, 'db2_search') ."', '"
	. xl_encode($DISABLE, 'db2_search') ."', '"
	. xl_encode($SECLEVEL, 'db2_search') ."', '"
	. xl_encode($DEFAULTGP, 'db2_search') ."', '"
	. xl_encode($DEFAULTVW, 'db2_search') ."', '"
	. xl_encode($DEFAULTRF, 'db2_search') ."', '"
	. xl_encode($DEFAULTLOG, 'db2_search') ."', '"
	. xl_encode($RCDDTSTMP, 'db2_search') ."', '"
	. xl_encode($RCDTMSTMP, 'db2_search') ."', '"
	. xl_encode($PWDCHNGDT, 'db2_search') ."') where USERNAME = '". xl_encode($USERNAME_, 'db2_search') ."' with NC"); 
	
	// error handling
	if (!$result) 
	{
		db2_close($db2conn);
		die("<b>Error ". db2_stmt_error().":" . db2_stmt_errormsg()."</b>"); 
	}
	
	//Redirect to display page
	header("Location: $pf_scriptname?page=" . (string)$ww_page);
}



function endadd()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey[0]!='_' && $arraykey != "GLOBALS")
			global $$arraykey;
	}
	
	// get values from the page 
	$USERNAME = strtoupper(xl_get_parameter('USERNAME'));
	$FIRSTNM = xl_get_parameter('FIRSTNM');
	$LASTNM = xl_get_parameter('LASTNM');
	$PASSWORD = strtoupper(xl_get_parameter('PASSWORD'));
	$PASSWORD1 = strtoupper(xl_get_parameter('PASSWORD1'));
	$FORCEPWCHG = xl_get_parameter('FORCEPWCHG');
	$EMAIL = xl_get_parameter('EMAIL');
	$DISABLE = xl_get_parameter('DISABLE');
	$SECLEVEL = xl_get_parameter('SECLEVEL');
	$DEFAULTGP = xl_get_parameter('DEFAULTGP');
	$DEFAULTVW = xl_get_parameter('DEFAULTVW');
	$DEFAULTRF = xl_get_parameter('DEFAULTRF');
	$DEFAULTLOG = xl_get_parameter('DEFAULTLOG');
	$RCDDTSTMP = xl_get_parameter('RCDDTSTMP');
	if($RCDDTSTMP == '')
		$RCDDTSTMP = '0001-01-01';
	$RCDTMSTMP = xl_get_parameter('RCDTMSTMP');
	if($RCDTMSTMP == '')
		$RCDTMSTMP = '00:00:00';
	$PWDCHNGDT = xl_get_parameter('PWDCHNGDT');
	if($PWDCHNGDT == '')
		$PWDCHNGDT = '0001-01-01';
	
	
	// do any add validation here	
	
	// Check if name is blank
	if (strlen($FIRSTNM) == null) {
		$firstnmmsg = '<<< Invalid First Name >>>';
		$errors = '1';
	}
	if (strlen($LASTNM) == null) {
		$lastnmmsg = '<<< Invalid Last Name >>>';
		$errors = '1';
	}
	
	
	// Check if the username is to short
	if (strlen($USERNAME) < 6) {
		$usernamemsg = '<<< Username to short (6 Char Min) >>>';
		$errors = '1';
	} else {
		// Make sure we wouldn't be creating a duplicate row
		$result = db2_exec($db2conn, "SELECT COUNT(*) FROM dashboard/DBUSERS where USERNAME = '". xl_encode($USERNAME, 'db2_search') ."'");
		$row = db2_fetch_array($result);
		if($row[0] > 0)
		{
			$usernamemsg = '<<< Username already exist in database >>>';
			$errors = '1';
		}
	}
	
	
	// Validate email
	if(!validateEmail()) {
		$emailmsg = '<<< Invalid eMail >>>';
		$errors = '1';
	}
	
	// Check if the password is to short
	if (strlen($PASSWORD) < 6) {
		$passwordmsg = '<<< Password to short (6 Char Min) >>>';
		$errors = '1';
	}
	
	// Check if the passwords match
	if ($PASSWORD != $PASSWORD1) {
		$passwordmsg = '<<< Passwords do not match >>>';
		$errors = '1';
	}
	
	// Validation Errors found - Return to Page
	if ($errors) {
		wrtseg('RcdAdd');
		db2_close($db2conn);
		exit();
	}
	
	// Encrypt Password
	$PASSWORD = xl_encrypt($PASSWORD);
	$PASSWORD = base64_encode($PASSWORD);
	
	$PWDCHNGDT = date("m/d/Y");
	$RCDDTSTMP = date("m/d/Y");
	$RCDTMSTMP = date("H:i:s");
	
	// Add row to table: 
	$result = db2_exec($db2conn, "INSERT INTO dashboard/DBUSERS ( FIRSTNM, LASTNM, USERNAME, PASSWORD, FORCEPWCHG, EMAIL, DISABLE, SECLEVEL, DEFAULTGP, DEFAULTVW, DEFAULTRF, DEFAULTLOG, RCDDTSTMP, RCDTMSTMP, PWDCHNGDT) VALUES('"
	. xl_encode($FIRSTNM, 'db2_search') . "', '"
	. xl_encode($LASTNM, 'db2_search') ."', '"
	. xl_encode($USERNAME, 'db2_search') ."', '"
	. xl_encode($PASSWORD, 'db2_search') ."', '"
	. xl_encode($FORCEPWCHG, 'db2_search') ."', '"
	. xl_encode($EMAIL, 'db2_search') ."', '"
	. xl_encode($DISABLE, 'db2_search') ."', '"
	. xl_encode($SECLEVEL, 'db2_search') ."', '"
	. xl_encode($DEFAULTGP, 'db2_search') ."', '"
	. xl_encode($DEFAULTVW, 'db2_search') ."', '"
	. xl_encode($DEFAULTRF, 'db2_search') ."', '"
	. xl_encode($DEFAULTLOG, 'db2_search') ."', '"
	. xl_encode($RCDDTSTMP, 'db2_search') ."', '"
	. xl_encode($RCDTMSTMP, 'db2_search') ."', '"
	. xl_encode($PWDCHNGDT, 'db2_search') ."') with NC");
	
	// error handling
	if (!$result) 
	{
		db2_close($db2conn);
		die("<b>Error ". db2_stmt_error().":" . db2_stmt_errormsg()."</b>"); 
	}
	
	//Redirect to display page
	header("Location: $pf_scriptname?page=" . (string)$ww_page);
}

//===============================================================================
function load_groups()
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
	
	while ($row = db2_fetch_assoc($stmt))
	{
		
		$GROUP = trim($row['GROUP']);
		// Converts special characters in the data fields to their HTML entities. 
		$GROUP = htmlspecialchars($GROUP, ENT_QUOTES);
		
		echo '<option value="'.$GROUP.'"';
		if ($GROUP == $DEFAULTGP) echo ' selected="selected"';
		echo '>'.$GROUP.'</option>';
		
	}
}

//===============================================================================
function load_logos()
{ 
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	
	if ($handle = opendir('images/Logos')) {
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				//echo "$entry <br>";
				echo '<option value="'.$entry.'"';
				if ($entry == $DEFAULTLOG) echo ' selected="selected"';
				echo '>'.$entry.'</option>';
			}
		}
		closedir($handle);
	}
	
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
		$USERNAME_filt = htmlspecialchars($USERNAME_filt, ENT_QUOTES);
		$LASTNM_filt = htmlspecialchars($LASTNM_filt, ENT_QUOTES);
	}
	else if($type == "DATA")
	{
		$FIRSTNM = htmlspecialchars($FIRSTNM, ENT_QUOTES);
		$LASTNM = htmlspecialchars($LASTNM, ENT_QUOTES);
		$USERNAME = htmlspecialchars($USERNAME, ENT_QUOTES);
		$PASSWORD = htmlspecialchars($PASSWORD, ENT_QUOTES);
		$FORCEPWCHG = htmlspecialchars($FORCEPWCHG, ENT_QUOTES);
		$EMAIL = htmlspecialchars($EMAIL, ENT_QUOTES);
		$DISABLE = htmlspecialchars($DISABLE, ENT_QUOTES);
		$SECLEVEL = htmlspecialchars($SECLEVEL, ENT_QUOTES);
		$DEFAULTGP = htmlspecialchars($DEFAULTGP, ENT_QUOTES);
		$DEFAULTVW = htmlspecialchars($DEFAULTVW, ENT_QUOTES);
		$DEFAULTRF = htmlspecialchars($DEFAULTRF, ENT_QUOTES);
		$DEFAULTLOG = htmlspecialchars($DEFAULTLOG, ENT_QUOTES);
		$RCDDTSTMP = htmlspecialchars($RCDDTSTMP, ENT_QUOTES);
		$RCDTMSTMP = htmlspecialchars($RCDTMSTMP, ENT_QUOTES);
		$PWDCHNGDT = htmlspecialchars($PWDCHNGDT, ENT_QUOTES);
	}
}

//===============================================================================
function validateEmail()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if($arraykey[0]!='_' && $arraykey != 'GLOBALS')
			global $$arraykey;
	}
	
	//check that email matches the basic email format
	if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $EMAIL))
	{
		$valid = FALSE;
	}
	else
	{
		$valid = TRUE;
	}
	return $valid;
}

//===============================================================================
function forgotpwd()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if($arraykey[0]!='_' && $arraykey != 'GLOBALS')
			global $$arraykey;
	}
	
	// Retrieve the rrn
	if (isset($_REQUEST['user']))
		$ww_rrn = $_REQUEST['user'];
	
	$sqlstatement = 'SELECT  USERNAME, PASSWORD, FIRSTNM, LASTNM, EMAIL, DISABLE FROM dashboard/DBUSERS ';
	$condition = 'WHERE rrn(DBUSERS)= ' . $ww_rrn;
	
	$sqlstatement .= $condition;
	
	// Fetch the row for page
	if (!($result = db2_exec($db2conn, $sqlstatement, array('CURSOR' => DB2_SCROLLABLE)))) 
	{
		//Release the database resource
		db2_close($db2conn);
		
		die("<b>Error ". db2_stmt_error().":" . db2_stmt_errormsg()."</b>"); 
	}
	
	// put the result into global variable and show it    
	$row = db2_fetch_assoc($result);
	
	// Get the fields 
	
	$USERNAME = rtrim($row['USERNAME']);
	$PASSWORD = rtrim($row['PASSWORD']);
	$FIRSTNM = rtrim($row['FIRSTNM']);
	$LASTNM = rtrim($row['LASTNM']);
	$EMAIL = rtrim($row['EMAIL']);
	$DISABLE = rtrim($row['DISABLE']);
	
	// Password decryption
	$PASSWORD = base64_decode($PASSWORD);
	if ($PASSWORD != null) {
		$PASSWORD = trim(xl_decrypt($PASSWORD));
		$PASSWORD1 = $PASSWORD;
	}
	
	$to      = $EMAIL;
	$subject = '** Confidential **';
	
	include('Includes/GetSysDateTime.php');
	if ($ampm == ' AM') {
		$message = "Good morning $FIRSTNM $LASTNM, \r\n \r\n";
	} else {
		$message = "Good afternoon $FIRSTNM $LASTNM, \r\n \r\n";
	}
	$message .= "It looks like you have forgotten your DASHBOARD password. \r\n";
	$message .= "For security reason, it has been emailed to you. \r\n \r\n";
	
	$message .= "Your Username: $USERNAME \r\n";
	$message .= "Your password is: $PASSWORD \r\n \r\n";
	$message .= "URL: http://dashboard.premiseinc.com/dashboard/dashboard.php \r\n \r\n";
	
	$message .= "Should you have any questions, please contact the support team. \r\n \r\n \r\n";
	$message .= "\r\n";
	$message .= "** DO-NOT-REPLY to this mail as it was programmatically generated. **";
	
	mail($to, $subject, $message);
	
	wrtseg('Forgot');
	
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
    <title>DB Users</title>
    
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/print.css" media="print" />
    <script type="text/javascript" src="/websmart/v9.0/javascript/jquery.min.js"></script>
    <script type="text/javascript">
		//focus the first input on page load
		jQuery(document).ready( function()
		{
			jQuery('input[disabled=false]:first').focus();
		});

		function Confirm(rrn, name) 
		{
			var is_confirm = window.confirm("Are you sure you want to email      \\n\\n"+name.value+"\\n\\nthe password via eMail ?");
			if (is_confirm==true) {
				window.open('$pf_scriptname?task=forgotpwd&user='+rrn.value+'&rnd=$rnd','Forgot_Password','width=600,height=400,status=no,screenY=160,top=160');	
				return false;
			} else {
				return false;}
		}
	</script>
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
      
      <!-- Table containing filter inputs -->
      <form id="filterform" action="$pf_scriptname" method="POST">
        <input id="task" type="hidden" name="task" value="filter">
        <table id="filtertable" class="keys">
          <tr>
            <td width="102"><div align="right"><strong>Username:</strong></div></td>
            <td width="428"><input id="USERNAME_filt" type="text" name="USERNAME_filt" value="$USERNAME_filt"></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Last Name:</strong></div></td>
            <td><input id="LASTNM_filt" type="text" name="LASTNM_filt" value="$LASTNM_filt">&nbsp;&nbsp;&nbsp;&nbsp;
            <input id="filterbutton" type="submit" value="Filter" /></td>
          </tr>
        </table>
      </form>
      <!-- End table containing filter inputs -->
      
      <div id="listtopcontrol">
        <a id="prevlinktop" class="prevlink nondisp">Previous $ww_listsize</a>
        <a class="addlink" href="$pf_scriptname?task=beginadd&rnd=$rnd">Add Record</a>
        <a id="nextlinktop" class="nextlink nondisp">Next $ww_listsize</a>
      </div> 
      <table id="listtable" class="mainlist">
        <tr>
          <th nowrap="nowrap">Action</th>
          <th width="200" nowrap="nowrap"><a href="$pf_scriptname?ordby=USERNAME&rnd=$rnd">Username</a></th>
          <th width="100" nowrap="nowrap"><a href="$pf_scriptname?ordby=LASTNM||FIRSTNM&rnd=$rnd">Last Name</a></th>
          <th width="100" nowrap="nowrap"><a href="$pf_scriptname?ordby=FIRSTNM&rnd=$rnd">First Name</a></th>
          <th width="80" nowrap="nowrap"><a href="$pf_scriptname?ordby=DISABLE&rnd=$rnd">Disabled<br />
          User</a></th>
          <th width="80" nowrap="nowrap"><a href="$pf_scriptname?ordby=SECLEVEL&rnd=$rnd">Security<br />
          Level</a></th>
          <th width="200" nowrap="nowrap"><a href="$pf_scriptname?ordby=EMAIL&rnd=$rnd">eMail Address</a></th>
          <th width="100" nowrap="nowrap"><a href="$pf_scriptname?ordby=PWDCHNGDT&rnd=$rnd">Password<br />
          Last Changed</a></th>
          <th width="100" nowrap="nowrap"><a href="$pf_scriptname?ordby=RCDDTSTMP&rnd=$rnd">Record<br />
          Created<br />Date Stamp</a></th>
          <th width="80" nowrap="nowrap"><a href="$pf_scriptname?ordby=RCDTMSTMP&rnd=$rnd">Record<br />
          Created<br />Time Stamp</a></th>
          <th width="80" nowrap="nowrap">Forgot<br>Password</th>
        </tr>
        
SEGDTA;
		return;
	}
	if($segment == "listdetails")
	{

		echo <<<SEGDTA

<tr class="$pf_altrowclr">
  <td valign="middle" class="actions">
    <div align="center">&nbsp;&nbsp;
    	<a href="$pf_scriptname?task=disp&USERNAME=
SEGDTA;
 echo urlencode($USERNAME); 
		echo <<<SEGDTA
&rnd=$rnd"><img src="images/view.gif" alt="Display" title="Display"></a>&nbsp;&nbsp;
        <a href="$pf_scriptname?task=beginchange&USERNAME=
SEGDTA;
 echo urlencode($USERNAME); 
		echo <<<SEGDTA
&rnd=$rnd"><img src="images/edit.gif"  alt="Edit" title="Edit"></a>&nbsp;&nbsp;
        <a href="$pf_scriptname?task=delconf&USERNAME=
SEGDTA;
 echo urlencode($USERNAME); 
		echo <<<SEGDTA
"><img src="images/delete.gif" alt="Delete" title="Delete"></a>&nbsp;&nbsp;&nbsp;  </div></td>
  <td valign="middle" nowrap="nowrap">$USERNAME</td>
  <td valign="middle" nowrap="nowrap">$LASTNM</td>
  <td valign="middle" nowrap="nowrap">$FIRSTNM</td>
  <td valign="middle" nowrap="nowrap"><div align="center">$DISABLE</div></td>
  <td valign="middle" nowrap="nowrap"><div align="center">$SECLEVEL</div></td>
  <td valign="middle" nowrap="nowrap">$EMAIL</td>
  <td valign="middle" nowrap="nowrap"><div align="center">$PWDCHNGDT</div></td>
  <td valign="middle" nowrap="nowrap"><div align="center">$RCDDTSTMP</div></td>
  <td valign="middle" nowrap="nowrap"><div align="center">$RCDTMSTMP</div></td>
  <td valign="middle"><div align="center">
      
  
SEGDTA;

  
  	if ($DISABLE == '') {
		$string = '<a href="#" target="_blank"><img src="images/email.gif"  alt="eMail Password" width="14" height="11" title="eMail Password" onClick="return Confirm(user'.$ww_rrn.', name'.$ww_rrn.');return false;" /></a>';
		echo $string;
	}
  
		echo <<<SEGDTA

      <input name="user$ww_rrn" type="hidden" id="user$ww_rrn" value="$ww_rrn" />
      <input name="name$ww_rrn" type="hidden" id="name$ww_rrn" value="$FIRSTNM $LASTNM" />
      
    </div></td>

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
  <a class="addlink" href="$pf_scriptname?task=beginadd&rnd=$rnd">Add Record</a>
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta name="generator" content="WebSmart" />
    <meta http-equiv="Pragma" content="no-cache" />
    <title>DB Users - Display</title>
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/print.css" media="print" />
    <script type="text/javascript" src="/websmart/v9.0/javascript/jquery.min.js"></script>
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
      <div id="navtop">
        
SEGDTA;

  	if ($pf_task == 'disp') wrtseg('RtnToList'); 
	else 
		if ($pf_task == 'delconf') wrtseg('DelChoice');
  			

		echo <<<SEGDTA

        
      </div>
      <br>
      <table bgcolor="#FFFFFF" id="displaytable">
        <tr><td height="10" nowrap="nowrap"><div align="right"></div></td>
          <td height="10" nowrap="nowrap">&nbsp;</td>
        </tr>
        
        <tr><td width="204" nowrap="nowrap"><div align="right"><strong>Users First Name:</strong></div></td>
          <td width="340" nowrap="nowrap">$FIRSTNM</td>
        </tr>
        <tr><td nowrap="nowrap"><div align="right"><strong>Users Last Name:</strong></div></td><td nowrap="nowrap">$LASTNM</td>
        </tr>
        <tr><td height="10" nowrap="nowrap"><div align="right"></div></td>
          <td height="10" nowrap="nowrap">&nbsp;</td>
        </tr>
        <tr><td height="22" nowrap="nowrap"><div align="right"><strong>Username:</strong></div></td>
          <td nowrap="nowrap">$USERNAME</td>
        </tr>
        <tr><td nowrap="nowrap"><div align="right"><strong>Disable User:</strong></div></td><td nowrap="nowrap">$DISABLE</td>
        </tr>
        <tr><td height="10" nowrap="nowrap"><div align="right"></div></td>
          <td height="10" nowrap="nowrap">&nbsp;</td>
        </tr>
        <tr><td nowrap="nowrap"><div align="right"><strong>Password (Encrypted):</strong></div></td><td nowrap="nowrap">$PASSWORD</td>
        </tr>
        <tr><td nowrap="nowrap"><div align="right"><strong>Password Last Change Date:</strong></div></td><td nowrap="nowrap">$PWDCHNGDT</td>
        </tr>
        <tr><td nowrap="nowrap"><div align="right"><strong>Force Password Change:</strong></div></td><td nowrap="nowrap">$FORCEPWCHG</td>
        </tr>
        <tr><td height="10" nowrap="nowrap"><div align="right"></div></td>
          <td height="10" nowrap="nowrap">&nbsp;</td>
        </tr>
        <tr><td nowrap="nowrap"><div align="right"><strong>eMail Address:</strong></div></td><td nowrap="nowrap">$EMAIL</td>
        </tr>
        <tr><td nowrap="nowrap"><div align="right"><strong>Security Level:</strong></div></td><td nowrap="nowrap">$SECLEVEL</td>
        </tr>
        <tr><td nowrap="nowrap"><div align="right"><strong>Default Group:</strong></div></td><td nowrap="nowrap">$DEFAULTGP</td>
        </tr>
        </tr>
        <tr><td nowrap="nowrap"><div align="right"><strong>Default View:</strong></div></td><td nowrap="nowrap">$DEFAULTVW</td>
        </tr>
        </tr>
        <tr><td nowrap="nowrap"><div align="right"><strong>Default Refresh:</strong></div></td><td nowrap="nowrap">$DEFAULTRF</td>
        </tr>
        <tr><td nowrap="nowrap"><div align="right"><strong>Default Logo:</strong></div></td><td nowrap="nowrap">$DEFAULTLOG</td>
        </tr>
        <tr><td height="10" nowrap="nowrap"><div align="right"></div></td>
          <td height="10" nowrap="nowrap">&nbsp;</td>
        </tr>
        <tr><td nowrap="nowrap"><div align="right"><strong>Record Created Date Stamp:</strong></div></td><td nowrap="nowrap">$RCDDTSTMP</td>
        </tr>
        <tr><td nowrap="nowrap"><div align="right"><strong>Record Created Time Stamp:</strong></div></td><td nowrap="nowrap">$RCDTMSTMP</td>
        </tr>
        
        <tr><td height="10" nowrap="nowrap"><div align="right"></div></td>
          <td height="10" nowrap="nowrap">&nbsp;</td>
        </tr>
      </table>
      
    </div>		
  </body>
</html>

SEGDTA;
		return;
	}
	if($segment == "delchoice")
	{

		echo <<<SEGDTA
<p>Are you SURE you want to delete this record?</p>
<a href="$pf_scriptname?task=del&USERNAME=
SEGDTA;
 echo urlencode($USERNAME); 
		echo <<<SEGDTA
">Yes</a>
&nbsp;&nbsp;
<a href="javascript:location.href='?page=$ww_page'">No</a>

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
	if($segment == "rcdadd")
	{

		echo <<<SEGDTA
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta name="generator" content="WebSmart" />
    <meta http-equiv="Pragma" content="no-cache" />
    <title>DB Users - Add</title>
    
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/print.css" media="print" />
    <script type="text/javascript" src="/websmart/v9.0/javascript/jquery.min.js"></script>
    <script type="text/javascript">
		//focus the first input on page load
		jQuery(document).ready( function()
		{
			jQuery('input[disabled=false]:first').focus();
		});	
	</script>
    <style type="text/css">
<!--
	body {
	background-image: url(images/back_grey.jpg);
	}
.style1 {color: #FF0000}
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
      <form id="addform" action="$pf_scriptname" method="get">
        <input id="task" type="hidden" name="task" value="endadd">
        <input id="USERNAME_" type="hidden" name="USERNAME_" value="$USERNAME">
        
        
        <table bgcolor="#FFFFFF" id="addtable">
          <tr>
            <td height="10" colspan="2" nowrap="nowrap"><div align="right"></div></td>
          </tr>
          <tr>
            <td width="153"><div align="right"><strong>Users First Name:</strong></div></td>
            <td width="435" nowrap="nowrap"><input type="text" id="FIRSTNM" name="FIRSTNM" size="30" maxlength="30" value="$FIRSTNM">
              <span class="style1">$firstnmmsg</span></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Users Last Name:</strong></div></td>
            <td nowrap="nowrap"><input type="text" id="LASTNM" name="LASTNM" size="30" maxlength="30" value="$LASTNM">
              <span class="style1">$lastnmmsg</span></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Username:</strong></div></td>
            <td nowrap="nowrap"><input type="text" id="USERNAME" name="USERNAME" size="50" maxlength="50" value="$USERNAME">
              <span class="style1">$usernamemsg</span></td>
          </tr>
          <tr>
            <td height="15" colspan="2" nowrap="nowrap"><div align="right"></div></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Password:</strong></div></td>
            <td nowrap="nowrap"><input type="password" id="PASSWORD" name="PASSWORD" size="20" maxlength="20" value="$PASSWORD">
              <span class="style1">$passwordmsg</span></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Confirm Password:</strong></div></td>
            <td nowrap="nowrap"><input type="password" id="PASSWORD1" name="PASSWORD1" size="20" maxlength="20" value="$PASSWORD1">
              <span class="style1">$passwordmsg</span></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Force Password Change:</strong></div></td>
            <td nowrap="nowrap"><input name="FORCEPWCHG" type="checkbox" id="FORCEPWCHG" value="Y" border="0"></td>
          </tr>
          <tr>
            <td height="15" colspan="2" nowrap="nowrap"><div align="right"></div></td>
          </tr>
          <tr>
            <td><div align="right"><strong>eMail Address:</strong></div></td>
            <td nowrap="nowrap"><input type="text" id="EMAIL" name="EMAIL" size="50" maxlength="50" value="$EMAIL">
              <span class="style1">$emailmsg</span></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Security Level:</strong></div></td>
            <td nowrap="nowrap"><select name="SECLEVEL" id="SECLEVEL">
              <option value="CLIENT">Client</option>
              <option value="USER">User</option>
              <option value="ADMIN">Administrator</option>
              </select>            </td>
          </tr>
          <tr>
            <td><div align="right"><strong>Default Group:</strong></div></td>
            <td nowrap="nowrap"><select name="DEFAULTGP" id="DEFAULTGP">
              <option value="">** None **</option>
              <!-- Get groups for drop-down -->
              
SEGDTA;
 load_groups(); 
		echo <<<SEGDTA

            </select>            </td>
          </tr>	
          <tr>
            <td><div align="right"><strong>Default View:</strong></div></td>
            <td nowrap="nowrap"><select name="DEFAULTVW" size="1" id="DEFAULTVW">
          <option value="MAIN" selected="selected">View All</option>
                <option value="ERROR">View Only Errors</option>
                <option value="MIMIX">View MIMIX</option>
                <option value="MIMIXERROR">View MIMIX Errors</option>
            </select>            </td>
          </tr>	
          <tr>
            <td><div align="right"><strong>Default Refresh:</strong></div></td>
            <td nowrap="nowrap"><select name="DEFAULTRF" id="DEFAULTRF">
            <option value="5940" selected="selected">N/A</option>
                <option value="60">1 Minute</option>
                <option value="300">5 Minutes</option>
                <option value="600">10 Minutes</option>
                <option value="900">15 Minutes</option>
                <option value="1800">30 Minutes</option>
                <option value="3600">60 Minutes</option>
            </select>            </td>
          </tr>	
          <tr>
            <td><div align="right"><strong>Default Logo:</strong></div></td>
            <td nowrap="nowrap"><select name="DEFAULTLOG" id="DEFAULTLOG">
              <!-- Get Logos for drop-down -->
              
SEGDTA;
 load_logos(); 
		echo <<<SEGDTA

              <option value="Premise_logo.png">Premise_logo.png</option>
              <option value="TCM_logo.png">TCM_logo.png</option>
            </select>            </td>
          </tr>	
          <tr>
            <td height="10" colspan="2" nowrap="nowrap"><div align="right"></div></td>
          </tr>
        </table>
        <br />
        <div id="navbottom">
          <input id="submitbutton" type="submit" class="navbutton" value="Add">&nbsp;&nbsp;&nbsp;&nbsp;
          <button type="button" class="navbutton" onclick="javascript:location.href='?page=$ww_page'">Cancel</button>
        </div>		
      </form>
    </div>
  </body>
</html>

SEGDTA;
		return;
	}
	if($segment == "rcdchange")
	{

		echo <<<SEGDTA
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta name="generator" content="WebSmart" />
    <meta http-equiv="Pragma" content="no-cache" />
    <title>DB Users - Change</title>
    
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/screen.css" media="screen, tv, projection" />
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Lincoln/css/print.css" media="print" />
    <script type="text/javascript" src="/websmart/v9.0/javascript/jquery.min.js"></script>
    <script type="text/javascript">
		//focus the first input on page load
		jQuery(document).ready( function()
		{
			jQuery('input[disabled=false]:first').focus();
			
			
			jQuery("input[name='USERNAME']").attr("disabled",true);
		});	
	</script>
    <style type="text/css">
    <!--
	body {
	background-image: url(images/back_grey.jpg);
	}
.style3 {color: #FF0000}
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
      <form id="changeform" action="$pf_scriptname" method="get">
        <input id="task" type="hidden" name="task" value="endchange">
        <input id="USERNAME_" type="hidden" name="USERNAME_" value="$USERNAME">
        
        
        <table width="626" bgcolor="#FFFFFF" id="changetable">
          
          <tr>
            <td height="15" colspan="2" nowrap="nowrap"><div align="right"></div></td>
          </tr>
          <tr>
            <td width="224"><div align="right"><strong>Users First Name:</strong></div></td>
            <td width="390" nowrap="nowrap"><input type="text" id="FIRSTNM" name="FIRSTNM" size="30" maxlength="30" value="$FIRSTNM">
              <span class="style3">$firstnmmsg</span></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Users Last Name:</strong></div></td>
            <td nowrap="nowrap"><input type="text" id="LASTNM" name="LASTNM" size="30" maxlength="30" value="$LASTNM">
              <span class="style3">$lastnmmsg</span></td>
          </tr>
          <tr>
            <td height="15" colspan="2" nowrap="nowrap"><div align="right"></div></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Username:</strong></div></td>
            <td nowrap="nowrap"><input type="text" id="USERNAME" name="USERNAME" size="50" maxlength="50" value="$USERNAME"></td>
          </tr>
          <tr>
            <td><p align="right"><strong>Disable User:</strong></p>          </td>
            <td nowrap="nowrap"><input name="DISABLE" type="checkbox" id="DISABLE" value="Y" border="0" 
SEGDTA;
 if ($DISABLE == 'Y') echo "Checked"; 
		echo <<<SEGDTA
 ></td>
          </tr>
          <tr>
            <td height="15" colspan="2" nowrap="nowrap"><div align="right"></div></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Password:</strong></div></td>
            <td nowrap="nowrap"><input type="password" id="PASSWORD" name="PASSWORD" size="20" maxlength="20" value="$PASSWORD">
              <span class="style3">$passwordmsg</span></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Confirm Password:</strong></div></td>
            <td nowrap="nowrap"><input type="password" id="PASSWORD1" name="PASSWORD1" size="20" maxlength="20" value="$PASSWORD1">
              <span class="style3">$passwordmsg</span></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Password Last Change Date:</strong></div></td>
            <td nowrap="nowrap">&nbsp;$PWDCHNGDT</td>
          </tr>	
          <tr>
            <td><div align="right"><strong>Force Password Change:</strong></div></td>
            <td nowrap="nowrap"><input name="FORCEPWCHG" type="checkbox" id="FORCEPWCHG" value="Y" border="0" 
SEGDTA;
 if ($FORCEPWCHG == 'Y') echo "Checked"; 
		echo <<<SEGDTA
 /></td>
          </tr>
          <tr>
            <td height="15" colspan="2" nowrap="nowrap"><div align="right"></div></td>
          </tr>
          <tr>
            <td><div align="right"><strong>eMail Address:</strong></div></td>
            <td nowrap="nowrap"><input type="text" id="EMAIL" name="EMAIL" size="50" maxlength="50" value="$EMAIL">
              <span class="style3">$emailmsg</span></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Security Level:</strong></div></td>
            <td nowrap="nowrap"><select name="SECLEVEL" id="SECLEVEL">
              <option value="CLIENT" 
SEGDTA;
 if ($SECLEVEL == 'CLIENT') echo 'selected="selected"'; 
		echo <<<SEGDTA
 >Client</option>
              <option value="USER" 
SEGDTA;
 if ($SECLEVEL == 'USER') echo 'selected="selected"'; 
		echo <<<SEGDTA
 >User</option>
              <option value="ADMIN" 
SEGDTA;
 if ($SECLEVEL == 'ADMIN') echo 'selected="selected"'; 
		echo <<<SEGDTA
 >Administrator</option>
              </select></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Default Group:</strong></div></td>
            <td nowrap="nowrap"><select name="DEFAULTGP" id="DEFAULTGP">
              <option value="">** None **</option>
              <!-- Get groups for drop-down -->
              
SEGDTA;
 load_groups(); 
		echo <<<SEGDTA

            </select>            </td>
          </tr>
          <tr>
            <td><div align="right"><strong>Default View:</strong></div></td>
            <td nowrap="nowrap"><select name="DEFAULTVW" size="1" id="DEFAULTVW">
              <option value="MAIN" 
SEGDTA;
 if ($DEFAULTVW == 'MAIN') echo 'selected="selected"'; 
		echo <<<SEGDTA
>View All</option>
              <option value="ERROR" 
SEGDTA;
 if ($DEFAULTVW == 'ERROR') echo 'selected="selected"'; 
		echo <<<SEGDTA
>View Only Errors</option>
              <option value="MIMIX" 
SEGDTA;
 if ($DEFAULTVW == 'MIMIX') echo 'selected="selected"'; 
		echo <<<SEGDTA
>View MIMIX</option>
              <option value="MIMIXERROR" 
SEGDTA;
 if ($DEFAULTVW == 'MIMIXERROR') echo 'selected="selected"'; 
		echo <<<SEGDTA
>View MIMIX Errors</option>
              </select>          </td>
          </tr>	
          <tr>
            <td><div align="right"><strong>Default Refresh:</strong></div></td>
            <td nowrap="nowrap"><select name="DEFAULTRF" id="DEFAULTRF">
              <option value="5940" 
SEGDTA;
 if ($DEFAULTRF == 5940) echo 'selected="selected"'; 
		echo <<<SEGDTA
>N/A</option>
              <option value="60" 
SEGDTA;
 if ($DEFAULTRF == 60) echo 'selected="selected"'; 
		echo <<<SEGDTA
>1 Minute</option>
              <option value="300" 
SEGDTA;
 if ($DEFAULTRF == 300) echo 'selected="selected"'; 
		echo <<<SEGDTA
>5 Minutes</option>
              <option value="600" 
SEGDTA;
 if ($DEFAULTRF == 600) echo 'selected="selected"'; 
		echo <<<SEGDTA
>10 Minutes</option>
              <option value="900" 
SEGDTA;
 if ($DEFAULTRF == 900) echo 'selected="selected"'; 
		echo <<<SEGDTA
>15 Minutes</option>
              <option value="1800" 
SEGDTA;
 if ($DEFAULTRF == 1800) echo 'selected="selected"'; 
		echo <<<SEGDTA
>30 Minutes</option>
              <option value="3600" 
SEGDTA;
 if ($DEFAULTRF == 3600) echo 'selected="selected"'; 
		echo <<<SEGDTA
>60 Minutes</option>
              </select>          </td>
          </tr>	
          <tr>
            <td><div align="right"><strong>Default Logo:</strong></div></td>
            <td nowrap="nowrap"><select name="DEFAULTLOG" id="DEFAULTLOG">
              <!-- Get Logos for drop-down -->
              
SEGDTA;
 load_logos(); 
		echo <<<SEGDTA

              </select>          </td>
          </tr>	
          <tr>
            <td height="15" colspan="2" nowrap="nowrap"><div align="right"></div></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Record Created Date Stamp:</strong></div></td>
            <td nowrap="nowrap">&nbsp;$RCDDTSTMP
              <input name="RCDDTSTMP" type="hidden" id="RCDDTSTMP" value="$RCDDTSTMP" /></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Record Created Time Stamp:</strong></div></td>
            <td nowrap="nowrap">&nbsp;$RCDTMSTMP
              <input name="RCDTMSTMP" type="hidden" id="RCDTMSTMP" value="$RCDTMSTMP" /></td>
          </tr>
          <tr>
            <td height="15" colspan="2" nowrap="nowrap"><div align="right"></div></td>
          </tr>
        </table>
        
        <div id="navbottom">
          <input id="submitbutton" type="submit" class="navbutton" value="Change">&nbsp;&nbsp;&nbsp;&nbsp;
          <button type="button" class="navbutton" onclick="javascript:location.href='?page=$ww_page'">Cancel</button>
        </div>		
      </form>
    </div>
  </body>
</html>

SEGDTA;
		return;
	}
	if($segment == "forgot")
	{

		echo <<<SEGDTA
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
  
  <head>
    <meta name="generator" content="WebSmart" />
    <title>Forgot My Password</title>
    <link rel="stylesheet" type="text/css" href="/websmart/v9.0/Idaho/css/print.css" media="print" />
  </head>

    <style type="text/css">
    <!--
	body {
	background-image: url(images/back_grey.jpg);
	}
	-->
    </style>
  
  <body onBlur="self.close()" onClick="self.close()">
    

SEGDTA;
 
	global $i5conn;

	//	Connect to i5
	if (!($i5conn = xl_i5_connect()))
		die('Unable to get an i5 connection');
 
		echo <<<SEGDTA


<div align="center">
  <table width="49%" height="80" border="0" cellpadding="0" cellspacing="0">
    <tr> 
      <td width="16%" nowrap><img src="images/Premise_logo.png" width="202" height="101" border="0" alt="Premise, Inc." />&nbsp;&nbsp;&nbsp;</td>
      <td width="84%" align="right" valign="middle"><div align="left"><font color="#003366" size="2" face="Arial, Helvetica, sans-serif">

SEGDTA;
             
		print 	i5_get_system_value("QMONTH") . "/" .  
				i5_get_system_value("QDAY") . "/20" .
				i5_get_system_value("QYEAR");

		echo <<<SEGDTA
            
		<br><br>

SEGDTA;
   
		$hour = i5_get_system_value("QHOUR");
		$ampm = ' AM';
		
		if ($hour >= 12) {$ampm = ' PM';}
		if ($hour > 12)	{$hour = $hour - 12;}
		if ($hour == 00)	{$hour = 12;}
					
		print 	$hour . ":" .
				i5_get_system_value("QMINUTE") . $ampm;

		echo <<<SEGDTA
 
              </font></div></td>
  </tr>
</table>
</div>


SEGDTA;
 
    // Close i5 Connection
    i5_close($i5conn); 

		echo <<<SEGDTA

<hr />
<br>
    
    <table width="58%" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr> 
        <td height="48" colspan="2" align="right" nowrap="nowrap"><div align="left"><strong>&nbsp;A CONFIDENTIAL email containing the username and password was issued to:</strong></div> 
          <br>
          <div align="left"></div></td>
      </tr>
      <tr>
        <td width="28%" height="32" align="right" nowrap="nowrap"><div align="right">Usernamer:&nbsp;</div></td>
        <td width="72%" align="right"><div align="left"><strong><font color="#996600">$USERNAME</font></strong></div></td>
      </tr>
      <tr>
        <td width="28%" height="32" align="right" nowrap="nowrap"><div align="right">
            <div align="right">Name:&nbsp;</div>
          </div></td>
        <td width="72%" align="right"><div align="left"><strong><font color="#0000FF">$FIRSTNM $LASTNM</font></strong></div></td>
      </tr>
      <tr>
        <td width="28%" height="32" align="right" nowrap="nowrap"><div align="right">
            <div align="right">eMail Address:&nbsp;</div>
          </div></td>
        <td width="72%" align="right"><div align="left"><strong><font color="#990000">$EMAIL</font></strong></div></td>
      </tr>
    </table>
<br /><br /><br />
    <div align="center"><br />
      <strong><font color="#FF0000">&lt; Click anywhere to Close Window &gt; </font></strong>
    </div>
    
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
	
global $FIRSTNM,$LASTNM,$USERNAME,$PASSWORD,$FORCEPWCHG,$EMAIL,$DISABLE,$SECLEVEL,$DEFAULTGP,$DEFAULTVW,$DEFAULTRF,$DEFAULTLOG,$RCDDTSTMP,$RCDTMSTMP,$PWDCHNGDT;
	global $pf_scriptname;
	$pf_scriptname = 'DBUsers.php';

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

    // Last Generated CRC: 817B8DBE D0741AF9 0A7C2BBB 2D4005B2
    // Path: C:\Program Files\ESDI\WebSmart\temp\Dashboard_www_websmart_htdocs_dashboard_PHW Filels_DBusers.phw
}
?>