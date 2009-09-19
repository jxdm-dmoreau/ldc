<?php
Header("content-type: application/xml");

require_once('./Categorie.php');
require_once('./Categories.php');
require_once('./monaAPI.php');




$api = new monaAPI();
$xml = $api->getOperationsXml("20080101", "20091212");
print("$xml");









?>
