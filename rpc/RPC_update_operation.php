<?php
require_once 'mysql_itf.php';
require_once 'ldc_config.php';
require_once 'tools.php';

/* 
 {
    "id":0,
    "date":"2009-03-03",
    "value":15,
    "description":"coucou c'est une description",
    "confirm":1,
    "cats": [{"id":1, "value":12}, {"id":2, "value": 3}],
    "labels": ["leader-price", "carrefour"]
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
		

$json_id  = mysql_real_escape_string($json->id);
$op = MYSQL_operation_get($json_id);
if ($op == false) {
    error("Operation $json_id not found");
}

/* update fields */
if (isset($json->date)) {
    $op->date = mysql_real_escape_string($json->date);
}
if (isset($json->value)) {
    $op->value = mysql_real_escape_string($json->value);
}
if (isset($json->description)) {
    $op->description = mysql_real_escape_string($json->description);
}
if (isset($json->account)) {
    $op->account = mysql_real_escape_string($json->account);
}
if (isset($json->recurring)) {
    $op->recurring = mysql_real_escape_string($json->recurring);
}
if (isset($json->confirm)) {
    $op->confirm = mysql_real_escape_string($json->confirm);
}
MYSQL_operation_update($op->id, $op->date, $op->value, $op->description, $op->account, $op->recurring, $op->confirm);



/* cat */
if (isset($json->cats)) {
    MYSQL_opcat_del_from_op($op->id);
    foreach ($json->cats as $cat) {
	MYSQL_opcat_add($op->id, $cat->{'id'}, $cat->{'value'});
    }
}

/* labels */
if (isset($json->labels)) {
    MYSQL_oplabel_del_from_op($op->id);
    foreach ($json->labels as $name) {
	$name = mysql_real_escape_string($name);
	$label_id = MYSQL_label_get_from_name($name);
	if ($label_id == -1) {
	    $label_id = MYSQL_label_add($name);
	}
	/* on a l'id correspondant au tag, on peut ajouter la relatetion  operation-tag */
	MYSQL_oplabel_add($op->id, $label_id);
    }  
}


$response->result = true;
$ret = json_encode($response);
debug($ret);
print $ret;

?>
