<?php
require_once 'mysql_itf.php';
require_once 'ldc_config.php';
require_once 'tools.php';

/* 
 {
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
		
$json_date        = mysql_real_escape_string($json->date);
$json_value       = mysql_real_escape_string($json->value);
$json_description = mysql_real_escape_string($json->description);
$json_account     = mysql_real_escape_string($json->account);
$json_recurring   = mysql_real_escape_string($json->recurring);
$json_confirm     = mysql_real_escape_string($json->confirm);
	

$json->id = MYSQL_operation_add($json_date, $json_value, $json_description, $json_account, $json_recurring, $json_confirm);	

foreach($json->cats as $cat) {
    MYSQL_opcat_add($json->id, $cat->id, $cat->value);
}


/* et les labels */
foreach ($json->labels as $name) {
    $label_name = mysql_real_escape_string($name);
    $label_id = MYSQL_label_get_from_name($label_name);
    if ($label_id == -1) {
	$label_id = MYSQL_label_add($label_name);
    }
    /* on a l'id correspondant au tag, on peut ajouter la relatetion  operation-tag */
    MYSQL_oplabel_add($json->id, $label_id);
}  


$response->id = $json->id;
$response->result = true;
$ret = json_encode($response);
debug($ret);
print $ret;

?>
