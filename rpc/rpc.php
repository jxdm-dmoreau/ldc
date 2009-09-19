<?php
require_once 'Logger.php';
require_once 'rpc_main.php';

/* action
 * - add_operation
 * - modify_operation
 * - delete_operation
 */

$logger = new Logger('RPC', 255);


if (!isset($_POST['action']) || !isset($_POST['json']) ) {
	$logger->error('Invalid POST parameters');
	die("Invalid POST parameters");
}

$action = $_POST['action'];
$json = str_replace('\\', '', $_POST['json']);
$logger->debug($json);
$json = json_decode($json);


switch($action) {
	case 'add_operation':
		$ret = add_operation($json);
		break;
	case 'modify_opearation':
		$ret = modify_operation($json);
		break;
	case 'del_operation':
		$ret = del_operation($json);
		break;
	default:
		$logger->warn("Invalid action: $action");
		break;
}

$ret = json_encode($ret);
$logger->debug($ret);

print $ret;



?>
