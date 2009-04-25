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

/* ajout de l'op�ration */
$op = $api->addOperation($date_str, $total, $cat_tab, $labels);

print("$op");

exit(0);





/*****************************************************************************/
/*                           labels                                          */
/*****************************************************************************/
$ret = split(", ", $input_labels);
$nb_labels = count($ret);
for($i=0; $i<$nb_labels; $i++) {
    /* creer les tags qui n'existent pas */
    $query = "SELECT id FROM labels WHERE name='$ret[$i]'";
    $result = $mysql->query($query);
    $line = mysql_fetch_assoc($result);
    if (isset($line['id'])) {
        // le label existe
        $label_id = $line['id'];
    } else {
        // le label n'existe pas
        // on l'ajoute
        $query = "INSERT INTO labels VALUES ('', '$ret[$i]')";
        $result = $mysql->query($query);
        // on récupère l'id
        $query = "SELECT id FROM labels WHERE name='$ret[$i]'";
        $result = $mysql->query($query);
        $line = mysql_fetch_assoc($result);
        $label_id = $line['id'];
    }
    /* on a l'id correspondant au tag, on peut ajouter la relatetion 
       operation-tag */
    $query = "INSERT INTO `op_labels` VALUES ('', '$op_id', '$label_id')";
    $result = $mysql->query($query);
}
exit(0);








