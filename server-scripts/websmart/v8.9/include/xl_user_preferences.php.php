<?php

//  Security Notice: 
//
//  Your Apache configuration file should contain the following directive:
//
//  <DirectoryMatch /esdi/websmart/v*/include/>
//  Order Deny,Allow
//  Deny From all
//  </DirectoryMatch> 
//
//
//  This will deny requests to view this file and any other files in the /include/ directory.

// default values: if UserIDs and Passwords are left blank, profile NOBODY will be used.
$pf_i5UserID = '';
$pf_i5Password = '';
$pf_i5IPAddress = '127.0.0.1';

/* DB2 connection details */
$pf_db2SystemName = 'premise';
$pf_db2UserID = '';
$pf_db2Password = '';

/* MySQL connection details */
$pf_mysqlUrl   = '192.168.0.39';
$pf_mysqlUserId   = 'root';
$pf_mysqlPassword   = 'cataadmin';
$pf_mysqlDataBase   = 'test';

/* Oracle connection details */
$pf_orclDB = 'ORCL11';
$pf_orclUserId = 'esdi';
$pf_orclPassword = 'cataadmin';

/* Encryption key for xl_encrypt, xl_decrypt functions */
$pf_encrypt_key;


?>
