<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 11/20/14
 * Time: 10:11 PM
 */


/* DB2 connection details */
require('x001_functions.php');

$options = array('i5_naming' => DB2_I5_NAMING_ON);

global $db2conn;
$db2conn = xl_db2_connect($options);

if(!$db2conn)
{
    die('Could not connect to database: ' . db2_conn_error());
}
else{
    ('Connected');
}
