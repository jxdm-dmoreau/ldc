<?php
Header("content-type: text/html");
// Inclusion des librairies
require_once('./monaAPI.php');
require_once('./EponaTools.php');

/* BEGIN */
$api = new monaAPI();
$tools = new EponaTools();



/* 
 * valeurs attendues :
op-calendar
op-cat-id_1
op-cat-value_1
op-tags
op-type: debit
 */       


$total = 0;
$coef = 1;
$pos = 0;

foreach($_POST as $key => $value) {
	if($key == 'op-calendar') {
		$date = $value;
	}
	if ($key == "op-type" && $value == 'debit') {
		$coef = -1;
	}
	if ($key == "op-tags") {
		$labels = split(", ", $value);
	}
	if (preg_match('/op-cat-id/', $key) == 1) {
		$tab = split('_', $key);
		$id = $tab[1];
		$cat_tab[$pos]['id'] = $value;
		$value = $_POST["op-cat-value_$id"];
		$cat_tab[$pos]['somme'] = $value;
		$total += $value;
		$pos++;
	}
}

$total *= $coef;

/* conversion de la date */
list($day, $month, $year) = split('-', $date);
$date_str = $year.$month.$day;

/* ajout de l'opération */
$op = $api->addOperation($date_str, $total, $cat_tab, $labels);

print("$op");

exit(0);













