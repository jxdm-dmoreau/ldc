<?php
require_once 'mysql_itf.php';
require_once 'ldc_config.php';
require_once 'tools.php';

/* 
 {
    "id":0,
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

$s->id = $json->id;		
$o = MYSQL_cat_get($s);

/* update fields */
foreach($json as $attr => $value) {
    $o->$attr = $value;
}
MYSQL_cat_update($o);

$o->result = true;
$ret = json_encode($o);
debug($ret);
print $ret;

?>
