<?php
require_once 'mysql_itf.php';
require_once 'ldc_config.php';
require_once 'tools.php';

/* 
 {
    "date":"2009-03-03",
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
$json2 = clone $json;
unset($json2->cats);
unset($json2->labels);

// connection a la base de données
$link = mysql_connect(LDC_MYSQL_HOST, LDC_MYSQL_USER, LDC_MYSQL_PASSWD);
mysql_select_db(LDC_MYSQL_DB, $link);
		

$json2 = MYSQL_operation_add($json2);	
$json->id = $json2->id;

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
