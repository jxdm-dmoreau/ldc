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
		

$op = MYSQL_operation_get($json);

$op_label = MYSQL_oplabel_get_from_op($op->id);
$op_cat   = MYSQL_opcat_get_from_op($op->id);

foreach ($op_label as $value) {
    $label = MYSQL_label_get($value->label_id);
    $op->labels[] = $label->name;
}

$i = 0;
foreach ($op_cat as $value) {
    $op->cats[$i]->id    = $value->cat_id;
    $op->cats[$i]->value = $value->value;
    $i++;
}

$op->result = true;

$ret = json_encode($op);
debug($ret);
print $ret;

?>
