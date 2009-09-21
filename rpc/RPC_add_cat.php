<?php
require_once 'mysql_itf.php';
require_once 'ldc_config.php';
require_once 'tools.php';

/* 
 {
    "father_id":2,
    "name":"pneu",
    "color":"#1234",
}
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
		
$json_father_id = mysql_real_escape_string($json->father_id);
$json_name = mysql_real_escape_string($json->name);
$json_color = mysql_real_escape_string($json->color);
	
$response = MYSQL_cat_add($json);
$response->result = true;
$ret = json_encode($response);
debug($ret);
print $ret;

?>
