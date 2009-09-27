<?php

require_once '../rpc/ldc_config.php';
require_once '../rpc/mysql_itf.php';

/* Test Mysql_itf */
$link = mysql_connect(LDC_MYSQL_HOST, LDC_MYSQL_USER, LDC_MYSQL_PASSWD);
mysql_select_db(LDC_MYSQL_DB, $link);

$date      = '2009-01-21';
$value     = 12;
$account   = 1;
$recurring = 0;
$confirm   = 1;
$label_name = 'Surprise';


$o = MYSQL_get_operations('2008-01-01', '2010-01-01');
print json_encode($o);
exit(0);

/* Operation */
$op_id = MYSQL_operation_add($date, $value, $desc, $account, $recurring, $confirm);
print("Operation $op_id created<br>");

$op = MYSQL_operation_get($op_id);
if ($op != false) {
    print json_encode($op).'<br>';
} else {
    die('error');
}

MYSQL_operation_update($op_id, '2009-03-03', $value, $desc, $account, $recurring, $confirm);
print("Operation $op_id updated<br>");

$op = MYSQL_operation_get($op_id);
if ($op != false) {
    print json_encode($op).'<br>';
} else {
    die('error');
}

MYSQL_operation_del($op_id);
print("Operation $op_id deleted<br>");

$op = MYSQL_operation_get($op_id);
if ($op != false) {
    die('error');
}





/* label */
$id = MYSQL_label_add($label_name);
print("Label $id created...<br>");

$label = MYSQL_label_get($id);
if ($label != false) {
    print json_encode($label).'<br>';
} else {
    die('error');
}


MYSQL_label_del($label_name);
print("Label deleted...<br>");




/* categories */
$cat_id = MYSQL_cat_add(0, 'Courses', '#000000');
print("Categorie $cat_id created<br>");

$cat = MYSQL_cat_get($cat_id);
if ($cat != false) {
    print json_encode($cat).'<br>';
} else {
    die('error');
}

MYSQL_cat_update($cat_id, 0, 'Courses 2');
print("Categorie $cat_id updated<br>");
$cat = MYSQL_cat_get($cat_id);
if ($cat != false) {
    print json_encode($cat).'<br>';
} else {
    die('error');
}

MYSQL_cat_del($cat_id);
print("Categorie $cat_id deleted<br>");


/* op-cat */
MYSQL_opcat_add(2000, 1000, 10);
print("op-cat created<br>");
MYSQL_opcat_add(2000, 1001, 10);
print("op-cat created<br>");

$o = MYSQL_opcat_get_from_op(2000);
if ($o != false) {
    print json_encode($o).'<br>';
} else {
    die('error');
}

MYSQL_opcat_del_from_op(2000);
print("op-cat deleted<br>");



/* op-label */
MYSQL_oplabel_add(2000, 30);
print("op-label created<br>");

MYSQL_oplabel_add(2000, 31);
print("op-label created<br>");

$o = MYSQL_oplabel_get_from_op(2000);
if ($o != false) {
    print json_encode($o).'<br>';
} else {
    die('error');
}

MYSQL_oplabel_del_from_op(2000);
print("op-label deleted<br>");


?>
