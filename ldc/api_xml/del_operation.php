<?php
Header("content-type: text");


require_once('./monaAPI.php');


/* BEGIN */
$api = new monaAPI();
$ok = '{result: "OK"}';
$ko = '{result: "KO"}';

/* PRECONDITIONS */
if (!isset($_GET['id'])) {
	print($ko);
	exit(0);
}

$id = $_GET['id'];
$ret = $api->removeOperation($id);
if ($ret == FALSE) {
	/* MySQL erreur */
	print($ko);
}

print($ok);






