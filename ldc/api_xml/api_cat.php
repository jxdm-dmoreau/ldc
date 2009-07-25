<?php

/* GET
 * op :get, add, remove, update
 * id: 5
 */
Header("content-type: application/xml");

require_once('./Categorie.php');
require_once('./Categories.php');
require_once('./monaAPI.php');
require_once('./EponaTools.php');


$api = new monaAPI();
$tools = new EponaTools();
$xml =  "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";


$tools->assert(isset($_GET['op']), "GET['op'] not defined");

if ($_GET['op'] == 'add') {
    /* add a categorie */
    $tools->assert(isset($_GET['name']), "GET['name'] not defined");
    $tools->assert(isset($_GET['father_id']), "GET['father_id'] not defined");
    $name = $_GET['name'];
    $fatherId = $_GET['father_id'];
    $id = $api->addCategorie($name, $fatherId);
    $xml .= "<results>$id</results>\n";
    print($xml);

}else if ($_GET['op'] == 'get') {
    /* get all categories */
    $cats = $api->getCategories();
    $xml = $cats->getXml();
    print($xml);

}else if ($_GET['op'] == 'remove') {
    /* remove a categorie */
    $tools->assert(isset($_GET['id']), "GET['id'] not defined");
    $ret = $api->removeCategorie($_GET['id']);
    $xml .= "<results>$ret</results>\n";
    print($xml);

}else if ($_GET['op'] == 'update') {
    /* update a categorie */
    $tools->assert(isset($_GET['id']), "GET['id'] not defined");
    $tools->assert(isset($_GET['name']), "GET['name'] not defined");
    $tools->assert(isset($_GET['father_id']), "GET['father_id'] not defined");
    $name = $_GET['name'];
    $fatherId = $_GET['father_id'];
    $id = $_GET['id'];
    $ret = $api->updateCategorie($id, $name, $fatherId);
    $xml .= "<results>$ret</results>\n";
    print($xml);

} else {
    $tools->error("Invalid GET[op] value (".$_GET['op'].")");
}






?>
