<?php 

//	Program Name:		WebCop.php 
//	Program Title:		Track PHP application users
//	Created by:			paredes1
//	Purpose:        	Program used to track user and manage application timer
//						Calls RPG program with parameter to mange users and timer.
//						Parms:	Program Name	10 Char
//								User Name		10 Char		
//								Option			1 Char
//
//						Options: 	0 - Create a user in WEBUSEON
//									1 - Check user from Signon Screen (Not used in PHP used in CGI)
//									2 - Check user from any screen and updates timer
//									9 - Used from loggin off a user and clean up
//									X - Returned value if timer has expired									
//
// Example of the function call: 
//===============================================================================
// 			webcop('DASHBOARD', $usrprf, '9');
//
//
// Example of the function:
//===============================================================================
//		function webcop($WebCopProgramName, $WebCopUser, $WebCopOption)
//		{
//			// Make all global variables available here
//			foreach($GLOBALS as $arraykey=>$arrayvalue) 
//			{
//				if($arraykey[0]!='_' && $arraykey != 'GLOBALS')
//				global $$arraykey;
//			}
//	
//			include('Includes/WebCop.php');
//			
//			if ($WebCopOption == 'X') {	
//				header("Location: QuickQuery.php");
//				exit();
//			}
//		}
//===============================================================================
//
//	Program Modifications:
//  

	global $i5conn, $clientIP;
	
	$clientIP =  $_SERVER['REMOTE_ADDR'];

	//	Connect to i5
	if (!($i5conn = xl_i5_connect()))
		die('Unable to get an i5 connection');

	// Add Library WEBDEV
	$cmd1 = i5_command("ADDLIBLE DASHBOARD");               
	if (!$cmd1) {
		echo "<br>i5_command - ADDLIBLE DASHBOARD <br> ";
		echo "Error Number: ".i5_errno()." <br> ";
		echo "Error Message: ".i5_errormsg()."<br>";
		exit();                                                                 
	}  


	// Set program parameters	
	$description = array(                                                
	array(                                                           
	"Name"=>"PGMNM",                                           
	"IO"=>I5_INOUT,                                               
	"Type"=>I5_TYPE_CHAR,                                      
	"Length"=>"10"),                                                             
	array(                                                         
	"Name"=>"USERNM",                                        
	"IO"=>I5_INOUT,                                               
	"Type"=>I5_TYPE_CHAR,                                      
	"Length"=>"10"),                                                            
	array(                                                         
	"Name"=>"CLIENTIP",                                        
	"IO"=>I5_INOUT,                                               
	"Type"=>I5_TYPE_CHAR,                                      
	"Length"=>"15"),                                                            
	array(                                                         
	"Name"=>"OPTION",                                        
	"IO"=>I5_INOUT,                                               
	"Type"=>I5_TYPE_CHAR,                                      
	"Length"=>"1")                                                            
	);                                                                        
	
	$pgm = i5_program_prepare("DASHBOARD/WEBCOP_PHP",$description);               
	
	if (!$pgm) {                                                                
		echo "<br>i5_program_prepare - WEBCOP_PHP <br> ";
		echo "Error Number: ".i5_errno()." <br> ";
		echo "Error Message: ".i5_errormsg()."<br>";
		exit();                                                                 
	}                                                                           
	
	$parmIn = array(                                                         
	"PGMNM"=>$WebCopProgramName,                                                    
	"USERNM"=>$WebCopUser,                                               
	"CLIENTIP"=>$clientIP,                                               
	"OPTION"=>$WebCopOption
	);                                                                          
	
	$parmOut = array(                                                           
	"PGMNM"=>"WebCopProgramName",                                                    
	"USERNM"=>"WebCopUser",                                               
	"CLIENTIP"=>"clientIP",                                               
	"OPTION"=>"WebCopOption"
	);                                                                           
	
	$ret = i5_program_call($pgm, $parmIn, $parmOut);                          
	
	if (!$ret) {                                                                 
		echo "<br>i5_program_call - WEBCOP_PHP <br> ";
		echo "Error Number: ".i5_errno()." <br> ";
		echo "Error Message: ".i5_errormsg()."<br>";
		exit();                                                                  
	}                                                                            
	
	i5_program_close($pgm); 
	
	// Close i5 Connection
    i5_close($i5conn); 

?>