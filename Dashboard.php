<?php
internal_init();
// Make all global variables available locally
foreach($GLOBALS as $arraykey=>$arrayvalue){if($arraykey!="GLOBALS"){global $$arraykey;}}

//	Program Name:		dashboard.php
//	Program Title:		Sign On Screen
//	Created by:			Ernie Paredes
//	Purpose:        
//	Program Modifications:
// More Comments here:

require('/esdi/websmart/v9.0/include/xl_functions001.php');

// DB Connection code
$options = array('i5_naming' => DB2_I5_NAMING_ON, 'i5_date_fmt' => DB2_I5_FMT_USA, 'i5_time_fmt' => DB2_I5_FMT_USA);

global $db2conn;
$db2conn = xl_db2_connect($options);

if(!$db2conn)
{
	die('Could not connect to database: ' . db2_conn_error());
}

global $username, $password, $msg;
global $support_name, $support_email, $support_phone;
global $pf_encrypt_key;

// Set decryption key
$pf_encrypt_key = null;

// Retrieve the task 
switch($pf_task)
{
	case 'default';
	webcop('DASHBOARD', ' ', '9');
	get_support();
	wrtseg('Signon');
	break;
	
	case 'signon': 
	ValidateUser();
	break;
	
	case 'off': 
	// Clear Session
	$_SESSION['DASHBOARD'] = null;
	$_SESSION['DASHBOARDMAIN'] = null;
	$_SESSION['DASHBOARDGROUP'] = null;
	// Goto signon screen
	header("Location: dashboard.php");
	break;
}

//Release the database resource
db2_close($db2conn);
/********************
 End of mainline code
 ********************/

//===============================================================================
function ValidateUser()
{
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if($arraykey[0]!='_' && $arraykey != 'GLOBALS')
			global $$arraykey;
	}
	
	// Retrieve fields from screen
	if (isset($_REQUEST['username']))
		$username = strtoupper($_REQUEST['username']);
	if (isset($_REQUEST['password']))
		$password = strtoupper($_REQUEST['password']);
	
	$sqlstr = 
	"SELECT FIRSTNM, LASTNM, PASSWORD, FORCEPWCHG, DISABLE, SECLEVEL, DEFAULTGP, DEFAULTVW, DEFAULTRF, DEFAULTLOG
	FROM DASHBOARD/DBUSERS 
	WHERE UPPER(USERNAME) = '$username'";
	
	if (!($result = db2_exec($db2conn, $sqlstr))) 
	{
		echo "<br>$username<br>$sqlstr<br><br>";
		// Release the database resource
		db2_close($db2conn);
		die("<b>Error ".db2_stmt_error().":".db2_stmt_errormsg()."</b>"); 
	}
	
	// get the record and check the password	
	if ($row = db2_fetch_assoc($result))
	{
		
		$FIRSTNM = rtrim($row['FIRSTNM']);
		$LASTNM = rtrim($row['LASTNM']);
		$PASSWORD = rtrim($row['PASSWORD']);
		$FORCEPWCHG = rtrim($row['FORCEPWCHG']);
		$DISABLE = rtrim($row['DISABLE']);
		$SECLEVEL = rtrim($row['SECLEVEL']);
		$DEFAULTGP = rtrim($row['DEFAULTGP']);
		$DEFAULTVW = rtrim($row['DEFAULTVW']);
		$DEFAULTRF = rtrim($row['DEFAULTRF']);
		$DEFAULTLOG = rtrim($row['DEFAULTLOG']);
		
		// Password decryption
		$PASSWORD = base64_decode($PASSWORD);
		$PASSWORD = trim(xl_decrypt($PASSWORD));
		
		
		// Valid Username/Password 
		if ($password == $PASSWORD && $DISABLE <> 'Y') {
			// Add user WEBCOP
			webcop('DASHBOARD', $username, '0');
			// Set current session variables
			setsession();
			// Goto main page
			header("Location: DashboardMain.php");
		} 
		// Invalid Password
		if ($password <> $PASSWORD) {
			$msg = "Invalid Username/Password <br> Please try again...";
			wrtseg('Signon');
			return;
		}
		// User Disabled
		if ($DISABLE == 'Y') {
			$msg = "User Disabled<br>Please contact Technical Support assistance.";
			wrtseg('Signon');
			return;
		}
		
	} else {
		// Trap all other errors
		$msg = "Could not Validate Username<br>Please contact Technical Support assistance.";
		wrtseg('Signon');
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
	$info['pgmnm'] = "DASHBOARD";
	$info['username'] = $username;
	$info['firstnm'] = $FIRSTNM;
	$info['lastnm'] = $LASTNM;
	$info['seclevel'] = $SECLEVEL;
	$info['default_logo'] = $DEFAULTLOG;
	
	// Store the array in a session. 
	$_SESSION['DASHBOARD'] = $info;
	
	
	$info = null;
	
	// Store the data from default values 
	$info['pgmnm'] = "DASHBOARDMAIN";
	$info['view'] = $DEFAULTVW;
	$info['refresh'] = $DEFAULTRF;
	$info['sort'] = '';
	
	// Store the array in a session. 
	$_SESSION['DASHBOARDMAIN'] = $info;
	
	
	if ($DEFAULTGP != '') {
		$info = null;
		
		$machines = get_machines();
		
		if ($machines != '') {		
			// Store the data sent from the form into an array 
			$info['pgmnm'] = "DASHBOARDGROUP";
			$info['group'] = $DEFAULTGP;
			$info['machines'] = $machines;
			
			// Store the array in a session. 
			$_SESSION['DASHBOARDGROUP'] = $info;
		}
	}
}

//===============================================================================
function get_machines()
{ 
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	
	$query = "select CSTSYSKEY from DASHBOARD/FGROUPBY 
	WHERE GROUP = '$DEFAULTGP'
	ORDER BY CSTSYSTEXT";
	
	// Fetch rows for page: relative to initial cursor 
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		die("<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	$loop = 0;
	
	while ($row = db2_fetch_assoc($stmt))
	{
		
		if ($loop == 0) {
			$machines = "'".trim($row['CSTSYSKEY'])."'";
		} else {
			$machines .= ', ' . "'".trim($row['CSTSYSKEY'])."'";
		}
		
		$loop++;
		
	}
	return $machines;	
}

//===============================================================================
function get_support()
{ 
	// Make all global variables available here
	foreach($GLOBALS as $arraykey=>$arrayvalue) 
	{
		if ($arraykey != "GLOBALS")
		{
			global $$arraykey;
		}
	}
	
	
	$query = "select desc, char, decimal from DASHBOARD/PHP_CTRL 
	WHERE PGMNM = 'SUPPORT'";
	
	// Fetch rows for page: relative to initial cursor 
	if (!($stmt = db2_exec($db2conn, $query))) 
	{
		// close the database connection
		db2_close($db2conn);   
		
		die("<b>Error ".db2_stmt_error() .":".db2_stmt_errormsg(). "</b>"); 
	}
	
	if ($row = db2_fetch_assoc($stmt))
	{
			$support_name = trim($row['DESC']);
			$support_email = trim($row['CHAR']);
			$tel = trim($row['DECIMAL']);
			$support_phone = '('.substr($tel,0,3).') '.substr($tel,3,3).'-'.substr($tel,6,4);
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
		header("Location: dashboard.php");
		exit();
	}
}

//===============================================================================
function throw_error($func) 
{
}


function wrtseg($segment)
{
	// Make sure it's case insensitive
	$segment = strtolower($segment);

	// Make all global variables available locally
	foreach($GLOBALS as $arraykey=>$arrayvalue) {if($arraykey != "GLOBALS"){global $$arraykey;}}

	// Output the requested segment:

	if($segment == "signon")
	{

		echo <<<SEGDTA
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
  
  <head>
    <meta name="generator" content="WebSmart" />
    <title>Work with Dashboard</title>
    <script language="JavaScript" type="text/JavaScript">
		function Confirm() {
		window.opener=null; window.close();
		}
	</script>
    
    <style type="text/css">
    <!--
	body {
	background-image: url(images/back_grey.jpg);
	}
	
	#apDiv1 {
	position:absolute;
	width:141px;
	height:130px;
	z-index:2;
	left: 191px;
	top: 270px;
	}
	
	-->
    </style>
    
  </head>
  
  <body onLoad="document.form1.username.focus();">
    
    
SEGDTA;
 include('Includes/HeaderLogOn.php'); 
		echo <<<SEGDTA

    <div id="apDiv1"><strong>Powered By:</strong><br /><br /><img src="images/Premise_logo.png" /></div>

<br>
    <br><br>
    
    <form action="$pf_scriptname" method="post" name="form1">
      <table width="77%" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr> 
          <td width="45%" height="36" align="right" class="inputhead">Username:&nbsp;</td>
          <td colspan="2" nowrap>
            <input name="username" type="text" id="NETUID2" value="$username" size="40" maxlength="50"></td>
        </tr>
        <tr> 
          <td height="43" align="right" class="inputhead">Password:&nbsp;</td>
          <td colspan="2"><input name="password" type="password" id="NETUPWD2" size="20" maxlength="20"> 
          </td>
        </tr>
        <tr> 
          <td height="58" align="right" class="inputhead">&nbsp;</td>
          <td width="19%" nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
            <input name="Log On" type="submit" id="Log On5" value="Log On">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
          <td width="36%"><img src="images/Exit%20door.gif" alt="Exit" width="35" height="46" border="0" onClick="return Confirm();"></td>
        </tr>
        <tr> 
          <td height="43" align="right" class="inputhead" colspan="3">  
            <p align="center"><font color="#FF0000"><strong>$msg</strong></font></p>
          </td>
        </tr>
        <tr> 
          <td height="43" colspan="3" align="right" nowrap="nowrap"> <strong>Technical Support </strong>at 
            $support_phone &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
            via e-mail: <a href="mailto: $support_email">$support_name</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
        </tr>
        <tr> 
          <td height="43" align="right" class="inputhead">  
            <p align="center">&nbsp;</p>      </td>
          <td height="43" colspan="2"><div align="left"><font color="#FF0000"><strong>
              <br>
              </strong></font></div></td>
        </tr>
      </table>
      <input name="task" type="hidden" id="task" value="signon">
    </form>
    
    
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
	$pf_scriptname = 'Dashboard.php';

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

    // Last Generated CRC: 80EEF509 F0C56BF3 161B606B 38DFB737
    // Path: \\192.168.0.21\root\www\websmart\htdocs\dashboard\PHW Filels\Dashboard.phw
}
?>