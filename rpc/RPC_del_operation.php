<?php
require_once 'mysql_itf.php';
require_once 'ldc_config.php';
require_once 'tools.php';

/* 
 { "id":0 }
*/

if (!isset($_POST['json']) ) {
    error('Invalid POST parameters');
}

$json = str_replace('\\', '', $_POST['json']);
debug($json);
$json = json_decode($json);

// connection a la base de données
$link = mysql_connect(LDC_MYSQL_HOST, LDC_MYSQL_USER, LDC_MYSQL_PASSWD);
mysql_select_db(LDC_MYSQL_DB, $link);
		
$json_id = mysql_real_escape_string($json->id);

MYSQL_operation_del($json_id);

$ret->result = true;
$ret = json_encode($ret);
debug($ret);
print $ret;

?>
