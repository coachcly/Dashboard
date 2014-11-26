<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 11/20/14
 * Time: 10:11 PM
 */


/* DB2 connection details */
$pf_db2SystemName = '10.170.2.53';
$pf_db2UserID = 'cyoung';
$pf_db2Password = 'cyoung';

function xl_db2_connect($options)
{
    $conn = db2_connect($GLOBALS['pf_db2SystemName'],
        $GLOBALS['pf_db2UserID'],
        $GLOBALS['pf_db2Password'],
        $options);
    return $conn;
}

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
