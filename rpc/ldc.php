<?php
require_once 'mysql_itf.php';
require_once 'ldc_config.php';
require_once 'tools.php';



if (!isset($_POST['json']) ) {
    error('Invalid POST parameters');
}

if (!isset($_POST['action']) ) {
    error('Invalid POST parameters');
}



$json = str_replace('\\', '', $_POST['json']);
$action = $_POST['action'];
debug("$action: $json");

$o = json_decode($json);
switch($action) {
    case "add_operation":
	$ret['id'] = MYSQL_add_operation($o);
	break;
    case "get_operation":
	$ret = MYSQL_get_operation($o);
	break;
    case "del_operation":
	$ret = MYSQL_del_operation($o);
	break;
    case "update_operation":
	$ret = MYSQL_update_operation($o);
	break;
    case "get_operations":
	$ret = MYSQL_get_operations($o);
	break;
    default:
	$ret = "Invalid action";
	break;
}

$ret = json_encode($ret);
debug($ret);
print "$ret";

exit(0);
