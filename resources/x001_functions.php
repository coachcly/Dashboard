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
$pf_i5IPAddress = '';

/* DB2 connection details */
$pf_db2SystemName = '10.170.2.53';
$pf_db2UserID = 'cyoung';
$pf_db2Password = 'cyoung';


/* MySQL connection details */
$pf_mysqlUrl      = '';
$pf_mysqlUserId   = '';
$pf_mysqlPassword = '';
$pf_mysqlDataBase = '';

/* Oracle connection details */
$pf_orclDB = '';
$pf_orclUserId = '';
$pf_orclPassword = '';

/* Encryption key for xl_encrypt, xl_decrypt functions */
$pf_encrypt_key;


//@include('xl_user_preferences.php');

function xl_set_libl($liblname,$liblconn=null)
{
    $liblname = strtoupper($liblname);
    global $pf_liblLibs;

    $parametersIn = array (
        array ("name"=>"LIBLNM", "io"=>I5_IN, "type" => I5_TYPE_CHAR,"length"=> 10),
        array ("name"=>"RTVLIBL","io"=>I5_IN, "type" => I5_TYPE_CHAR,"length"=> 550),
        array ("name"=>"SETRTN", "io"=>I5_INOUT, "type" => I5_TYPE_CHAR,"length"=> 1));

    if(is_null($liblconn))
    {
        $pgm = i5_program_prepare("xl_webspt/xl_setlibl",$parametersIn);
    }
    else
    {
        $pgm = i5_program_prepare("xl_webspt/xl_setlibl",$parametersIn,$liblconn);
    }
    if (!$pgm)
    {
        $errorTab = i5_error();
        var_dump($errorTab);
        return false;
    }

    // if we need to use *FILES, then construct the list of files
    $AllLibs = '';
    if ($liblname == '*FILES')
    {
        foreach ($pf_liblLibs as $lib)
        {
            $AllLibs .= $lib . ' ';
        }
    }

    $pgmcall = i5_program_call($pgm,
        array("LIBLNM" =>$liblname,"RTVLIBL" =>$AllLibs),
        array("SETRTN" => "rtnVal"));
    if (!$pgmcall)
    {
        $errorTab = i5_error();
        var_dump($errorTab);
        return false;
    }

    // get return values
    if($rtnVal != "0")
    {
        return false;
    }

    // if we haven't failed yet, then we succeeded!
    return true;
}


function xl_i5_connect()
{
    // DB Connection code
    $conn = i5_connect($GLOBALS['pf_i5IPAddress'],
        $GLOBALS['pf_i5UserID'],
        $GLOBALS['pf_i5Password']);
    return $conn;
}

function xl_db2_connect($options)
{
    $conn = db2_connect($GLOBALS['pf_db2SystemName'],
        $GLOBALS['pf_db2UserID'],
        $GLOBALS['pf_db2Password'],
        $options);
    return $conn;
}

function xl_mysql_connect()
{
    $conn = mysql_connect($GLOBALS['pf_mysqlUrl'],
        $GLOBALS['pf_mysqlUserId'],
        $GLOBALS['pf_mysqlPassword']);
    return $conn;
}

function xl_mysql_select_db($conn)
{

    $db_selected = mysql_select_db($GLOBALS['pf_mysqlDataBase'], $conn);

    return $db_selected;
}

function xl_oci_connect()
{
    // DB Connection code
    $conn = oci_connect(
        $GLOBALS['pf_orclUserId'],
        $GLOBALS['pf_orclPassword'],
        $GLOBALS['pf_orclDB']);
    return $conn;
}

function xl_oci_errormsg()
{
    $e = oci_error();
    return $e['message'];
}

function xl_oci_query($conn, $sqlstmt)
{
    $stmt = oci_parse($conn, $sqlstmt);
    if (!$stmt)
    {
        die("<b>Error ".xl_oci_errormsg()."</b>");
    }
    if (!oci_execute($stmt))
    {
        die("<b>Error ".xl_oci_errormsg()."</b>");
    }
    return $stmt;
}

function xl_raw_to_array($rawstring, $needquote = true)
{
    $arr = array();

    $decodedstring = urldecode($rawstring);

    // get an array of all the key/value pairs
    $variables = split('[&;]',$decodedstring);
    foreach($variables as $var)
    {
        // split them by the equal sign and stick
        // them in an associative array indexed by
        // variable name
        list($key, $value) = explode("=", $var);
        if($needquote)
            $arr[$key][] = xl_quote_string($value);
        else
            $arr[$key][] = $value;
    }

    return $arr;
}

function xl_quote_string($string)
{
    return str_replace("'", "''", $string);
}

function xl_set_row_color($color1, $color2)
{
    global $pf_altrowclr;
    // set the color
    if(!isset($pf_altrowclr) || $pf_altrowclr == $color2)
        $pf_altrowclr = $color1;
    else
        $pf_altrowclr = $color2;
}

function xl_encrypt($plain_text, $encryption_key = null)
{
    /* globalize encryption key from xl_user_preferences.php */
    global $pf_encrypt_key;
    if(!isset($encryption_key))
    {
        /* If the encryption key from xl_user_preferences.php
           is not set return the plain text */
        if(isset($pf_encrypt_key))
            $encryption_key = $pf_encrypt_key;
        else
            return $plain_text;
    }
    /* Open module, and create the initialization vector */
    $encryption_descriptor = mcrypt_module_open(MCRYPT_TWOFISH, '', MCRYPT_MODE_ECB, '');
    $encryption_key = substr($encryption_key, 0, mcrypt_enc_get_key_size($encryption_descriptor));
    $init_vector = mcrypt_create_iv(mcrypt_enc_get_iv_size($encryption_descriptor), MCRYPT_RAND);

    /* Initialize encryption handle */
    if (mcrypt_generic_init($encryption_descriptor, $encryption_key, $init_vector) != -1)
    {
        /* Encrypt data */
        $cipher_text = mcrypt_generic($encryption_descriptor, $plain_text);

        /* Clean up */
        mcrypt_generic_deinit($encryption_descriptor);
    }

    mcrypt_module_close($encryption_descriptor);

    return $cipher_text;
    // ...end Encrypt Data
}

function xl_decrypt($cipher_text, $encryption_key = null)
{
    /* globalize encryption key from xl_user_preferences.php */
    global $pf_encrypt_key;
    if(!isset($encryption_key))
    {
        /* If the encryption key from xl_user_preferences.php
           is not set return the cipher text */
        if(isset($pf_encrypt_key))
            $encryption_key = $pf_encrypt_key;
        else
            return $cipher_text;
    }
    /* Open module, and create the initialization vector */
    $encryption_descriptor = mcrypt_module_open(MCRYPT_TWOFISH, '', MCRYPT_MODE_ECB, '');
    $encryption_key = substr($encryption_key, 0, mcrypt_enc_get_key_size($encryption_descriptor));
    $init_vector = mcrypt_create_iv(mcrypt_enc_get_iv_size($encryption_descriptor), MCRYPT_RAND);

    /* Initialize decryption handle */
    if (mcrypt_generic_init($encryption_descriptor, $encryption_key, $init_vector) != -1)
    {
        /* decrypt data */
        $plain_text = mdecrypt_generic ($encryption_descriptor, $cipher_text);

        /* Clean up */
        mcrypt_generic_deinit($encryption_descriptor);
    }

    mcrypt_module_close($encryption_descriptor);

    return $plain_text;
    // ...end Decrypt Data
}


function xl_get_parameter($xl_sField, $xl_sEncType = '')
{
    $xl_val = '';
    //since $_REQUEST will also read cookies (and take priority), we check both $_POST and $_GET, instead. Although, in this case $_POST will win over the $_GET
    if (isset($_POST[$xl_sField]))
    {
        $xl_val = $_POST[$xl_sField];
    }
    else
    {
        if (isset($_GET[$xl_sField]))
        {
            $xl_val = $_GET[$xl_sField];
        }
    }
    if ($xl_sEncType != '')
    {
        if (is_array($xl_val))
        {
            $xl_val = xl_encode_array($xl_val, $xl_sEncType);
        }
        else
        {
            $xl_val = xl_encode($xl_val, $xl_sEncType);
        }
    }
    return $xl_val;
}

function xl_encode($xl_val, $xl_sEncType)
{
    switch(strtolower($xl_sEncType))
    {
        case 'db2_search': //optimized for db2 sql as a WHERE clause, allow special characters like & < > " \, but escape single quotes (')
            $xl_sOldChars = array("'");
            $xl_sNewChars = array("''");
            $xl_val = str_replace($xl_sOldChars, $xl_sNewChars, $xl_val);
            break;
        case 'addslashes':
            $xl_val = addslashes($xl_val);
            break;
        case 'mysql_search':
            // Testing on multiple mysql databases shows that single quotes need to be delimited with another
            // single quote rather than a backslash.
            //$xl_val = mysql_real_escape_string($xl_val);
            $xl_sOldChars = array("'");
            $xl_sNewChars = array("''");
            $xl_val = str_replace($xl_sOldChars, $xl_sNewChars, $xl_val);
            break;
        // ... end of 'mysql_search' change
        case 'oracle_search':
            // Right now this is the same as db2 and mysql but one day it may not be so use this in oracle templates
            $xl_sOldChars = array("'");
            $xl_sNewChars = array("''");
            $xl_val = str_replace($xl_sOldChars, $xl_sNewChars, $xl_val);
            break;
        case 'htmlentities':
            $xl_val = htmlentities($xl_val, ENT_QUOTES);
            break;
        case 'htmlspecialchars':
            $xl_val = htmlspecialchars($xl_val, ENT_QUOTES);
            break;
        case 'urlencode':
            $xl_val = urlencode($xl_val);
            break;
        case '':
            break;
        default:
            break;
    }

    return $xl_val;
}

function xl_encode_array($xl_arr = array(), $xl_sEncType = '')
{
    $xl_rs =  array();

    while(list($xl_key,$xl_val) = each($xl_arr))
    {
        if(is_array($xl_val))
        {
            $xl_rs[$xl_key] = xl_encode_array($xl_val, $xl_sEncType);
        }
        else
        {
            $xl_rs[$xl_key] = xl_encode($xl_val, $xl_sEncType);
        }
    }

    return $xl_rs;
}

?>