<?php

require_once "tools.php";
require_once "ldc_config.php";

/*
  OPERATION object 
 {
    "id":0,
    "date":"2009-03-03",
    "description":"coucou c'est une description",
    "confirm":1,
    "account":1,
    "recurring":0,
    "cats": [{"id":1, "value":12}, {"id":2, "value": 3}],
    "labels": ["leader-price", "carrefour"]
}
 */




/********************************************************************************
* PUBLIC FUNCTIONS
********************************************************************************/


/*
 * @brief Ajoute une opération
 */
function MYSQL_add_operation($o)
{
    /* connexion à la BD */
    $link = mysql_connect(LDC_MYSQL_HOST, LDC_MYSQL_USER, LDC_MYSQL_PASSWD);
    mysql_select_db(LDC_MYSQL_DB, $link);

    /* ajout de l'opération */
    add_operation($o);
    $mysql_o = get_operation($o);
    $o->id = $mysql_o->id;

    /* ajout des catégories */
    foreach($o->cats as $cat) {
	add_opcat($o->id, $cat->id, $cat->value);
    }

    /* et les labels */
    foreach ($o->labels as $name) {
	$label_name = mysql_real_escape_string($name);
	$label_id = get_label_id_from_name($label_name);
	if ($label_id == -1) {
	    $label_id = add_label($label_name);
	}
	/* on a l'id correspondant au tag, on peut ajouter la relatetion  operation-tag */
	add_oplabel($o->id, $label_id);
    }
    mysql_close();
    return $o->id; 
}



/*
 * @brief Retourne une operation
 * @param $id : identifiant de l'operation voulue
 * @return operation
 */
function MYSQL_get_operation($o)
{
    MYSQL_ASSERT(isset($o->id), "Invalid parameters for MYSQL_get_operation function.");

    /* connexion à la BD */
    $link = mysql_connect(LDC_MYSQL_HOST, LDC_MYSQL_USER, LDC_MYSQL_PASSWD);
    mysql_select_db(LDC_MYSQL_DB, $link);

    $id = mysql_real_escape_string($o->id);
    $query = "SELECT 
	    operations.id,
	    operations.date,
	    operations.recurring,
	    operations.account,
	    operations.confirm,
	    operations.description,
	    op_cat.value as cat_value,
	    op_cat.cat_id,
	    op_labels.label_id,
	    labels.name as label_name
	FROM operations, op_cat, cat, op_labels, labels
	WHERE operations.id = op_cat.op_id
	AND op_labels.label_id = labels.id
	AND operations.id = op_labels.op_id
	AND operations.id = '$id'";
    
    $ret = my_query($query);
    while($line = mysql_fetch_assoc($ret)) {
	extract($line);
	$tab[$id]['cat'][$cat_id]      = $cat_value;
	$tab[$id]['labels'][$label_id] = $label_name;
    }
    /* mise en forme sous forme d'objets */
    $object->id = $id;
    $object->date = $date;
    $object->confirm = $confirm;
    $object->account = $account;
    $object->recurring = $recurring;
    $object->description = $description;
    $cat_nb = 0;
    foreach($tab[$id]['cat'] as $cat_id => $cat_value) {
	$object->cats[$cat_nb]->id   = $cat_id;
	$object->cats[$cat_nb]->value = $cat_value;
	$cat_nb++;
    }
    foreach($tab[$id]['labels'] as $label_id => $label_name) {
	$object->labels[] = $label_name;
    }

    mysql_close();
    return $object;
}


function MYSQL_update_operation($o)
{
    MYSQL_ASSERT(isset($o->id), "Invalid parameters MYSQL_update_operation()");

    /* connection a la base de données */
    $link = mysql_connect(LDC_MYSQL_HOST, LDC_MYSQL_USER, LDC_MYSQL_PASSWD);
    mysql_select_db(LDC_MYSQL_DB, $link);

    /* retrieve corresponding operation */
    $mysql_o = get_operation($o);
    foreach($o as $attr => $value) {
	$mysql_o->$attr = $value;
    }
    update_operation($mysql_o);

    /* op-cat */
    if (isset($o->cats)) {
	del_opcat_from_op($o);
	foreach ($o->cats as $cat) {
	    add_opcat($o->id, $cat->{'id'}, $cat->{'value'});
	}
    }

    /* op-labels */
    if (isset($o->labels)) {
	del_oplabel_from_op($o);
	foreach ($o->labels as $name) {
	    $name = mysql_real_escape_string($name);
	    $label_id = get_label_id_from_name($name);
	    if ($label_id == -1) {
		$label_id = add_label($name);
	    }
	    /* on a l'id correspondant au tag, on peut ajouter la relatetion  operation-tag */
	    add_oplabel($o->id, $label_id);
	}  
    }
    return 1;

}

function MYSQL_del_operation($o)
{
    MYSQL_ASSERT(isset($o->id), "Invalid parameters for MYSQL_del_operation function.");

    /* connexion à la BD */
    $link = mysql_connect(LDC_MYSQL_HOST, LDC_MYSQL_USER, LDC_MYSQL_PASSWD);
    mysql_select_db(LDC_MYSQL_DB, $link);

    /* suppression de l'operation */
    del_operation($o);
    del_opcat_from_op($o);
    del_oplabel_from_op($o);

    mysql_close();
    return 1;
}


function MYSQL_get_operations($o)
{
    /* connexion à la BD */
    $link = mysql_connect(LDC_MYSQL_HOST, LDC_MYSQL_USER, LDC_MYSQL_PASSWD);
    mysql_select_db(LDC_MYSQL_DB, $link);

    MYSQL_ASSERT(isset($o->date_begin), "");
    MYSQL_ASSERT(isset($o->date_end), "");
    $date_begin = mysql_real_escape_string($o->date_begin);
    $date_end = mysql_real_escape_string($o->date_end);


    $query = "SELECT
	operations.id,
	operations.date,
	operations.recurring,
	operations.account,
	operations.confirm,
	operations.description,
	op_cat.value as cat_value,
	op_cat.cat_id,
	op_labels.label_id,
	labels.name as label_name
	FROM operations, op_cat, cat, op_labels, labels
	WHERE  date >= '$date_begin'
	AND date <= '$date_end'
	AND operations.id = op_cat.op_id
	AND op_labels.label_id = labels.id
	AND operations.id = op_labels.op_id
	ORDER BY date";
    
    $ret = my_query($query);
    $i = 0;
    while($line = mysql_fetch_assoc($ret)) {
	extract($line);
	$tab[$id]['value']             = $value;
	$tab[$id]['date']              = $date;
	$tab[$id]['confirm']           = $confirm;
	$tab[$id]['account']           = $account;
	$tab[$id]['description']       = $description;
	$tab[$id]['recurring']         = $recurring;
	$tab[$id]['cat'][$cat_id]      = $cat_value;
	$tab[$id]['labels'][$label_id] = $label_name;
    }
    /* mise en forme sous forme d'objets */
    $nb = 0;
    foreach ($tab as $key => $value) {
	$object[$nb]->id = $key;
	$object[$nb]->date = $date;
	$object[$nb]->confirm = $confirm;
	$object[$nb]->description = $description;
	$object[$nb]->recurring = $recurring;
	$object[$nb]->account = $account;
	$cat_nb = 0;
	foreach($tab[$key]['cat'] as $cat_id => $cat_value) {
	    $object[$nb]->cats[$cat_nb]->id   = $cat_id;
	    $object[$nb]->cats[$cat_nb]->value = $cat_value;
	    $cat_nb++;
	}
	foreach($tab[$key]['labels'] as $label_id => $label_name) {
	    $object[$nb]->labels[] = $label_name;
	}
	$nb++;
    }
    mysql_close();
    return $object;
}



/********************************************************************************
* tools
********************************************************************************/
function my_query($query) {
    debug($query);
    MYSQL_ASSERT($ret = mysql_query($query), mysql_error());
    return $ret;
}


function MYSQL_ASSERT($bool, $msg)
{
    if (!$bool) {
	mysql_close();
	exit($msg);
    }
}




/********************************************************************************
* OPEARIONS
********************************************************************************/


function add_operation($o)
{
    MYSQL_ASSERT(isset($o->date),        "date not defined");
    MYSQL_ASSERT(isset($o->account),     "account not defined");
    MYSQL_ASSERT(isset($o->recurring),   "recurring not defined");
    MYSQL_ASSERT(isset($o->description), "description not defined");
    MYSQL_ASSERT(isset($o->confirm),     "confirm not defined");

    $query = "INSERT INTO `operations` VALUES (
		'',
		'".mysql_real_escape_string($o->date)."',
		'".mysql_real_escape_string($o->description)."',
		'".mysql_real_escape_string($o->account)."',
		'".mysql_real_escape_string($o->recurring)."',
		'".mysql_real_escape_string($o->confirm)."'
	)";
    my_query($query);
}


function get_operation($o)
{
    /* construct query */
    $query = "SELECT * FROM `operations` WHERE ";
    if (isset($o->id)) {
	$query .= "`id` = '".mysql_real_escape_string($o->id)."'";
    } else {
	MYSQL_ASSERT(isset($o->date),        "date not defined");
	MYSQL_ASSERT(isset($o->account),     "account not defined");
	MYSQL_ASSERT(isset($o->recurring),   "recurring not defined");
	MYSQL_ASSERT(isset($o->description), "description not defined");
	MYSQL_ASSERT(isset($o->confirm),     "confirm not defined");
	$query .= "`date` = '".mysql_real_escape_string($o->date)."' AND ";
	$query .= "`account` = '".mysql_real_escape_string($o->account)."' AND";
	$query .= "`recurring` = '".mysql_real_escape_string($o->recurring)."' AND";
	$query .= "`description` = '".mysql_real_escape_string($o->description)."' AND";
	$query .= "`confirm` = '".mysql_real_escape_string($o->confirm)."'";
    }
    $query .= " ORDER BY `operations`.`id` DESC LIMIT 1";

    /* execute query */
    $ret = my_query($query);
    $line = mysql_fetch_assoc($ret);
    MYSQL_ASSERT($line, "mysql_fetch_assoc()");

    /* return result in object */
    return (object) $line;
}

function update_operation($o)
{
    MYSQL_ASSERT(isset($o->id), "id not defined");
    /* construct query */
    $query = "UPDATE `operations` SET ";
    if (isset($o->date)) {
	$query .= "`date` = '$o->date', ";
    }
    if (isset($o->description)) {
	$query .= "`description` = '$o->description', ";
    }
    if (isset($o->value)) {
	$query .= "`value` = '$o->value' ,";
    }
    if (isset($o->account)) {
	$query .= "`account` = '$o->account', ";
    }
    if (isset($o->recurring)) {
	$query .= "`recurring` = '$o->recurring', ";
    }
    if (isset($o->confirm)) {
	$query .= "`confirm` = '$o->confirm' ,";
    }
    $query = substr($query, 0, strlen($query) - 1);
    my_query($query);
    return $o;
}

function del_operation($o)
{
    $query = "DELETE FROM `operations` WHERE `id` = ".mysql_real_escape_string($o->id);
    my_query($query);
    return $o;
}


/********************************************************************************
* CATEGORIES
********************************************************************************/
function add_cat($o)
{
    MYSQL_ASSERT(isset($o->father_id), "Invalid parameters update_cat()");
    MYSQL_ASSERT(isset($o->name), "Invalid parameters update_cat()");
    MYSQL_ASSERT(isset($o->color), "Invalid parameters update_cat()");
    $query = "INSERT INTO `cat` VALUES ('', '$o->father_id', '$o->name', '$o->color')";
    my_query($query);
    return get_cat($o);
}


function update_cat($o)
{
    MYSQL_ASSERT(isset($o->id), "Invalid parameters update_cat()");
    $query = "UPDATE `cat` SET
    	`name`      = '$o->name',
	`father_id` = '$o->father_id',
	`color`     = '$o->color'
	WHERE `id`  = '$o->id'";
    my_query($query);
    return $o;
}

function del_cat($o)
{
    MYSQL_ASSERT(isset($o->id), "Invalid parameters update_cat()");
    $query = "DELETE FROM `cat` WHERE `id` = '$o->id'";
    my_query($query);
    return $o;
}

function get_cat($o)
{
    /* construct query */
    $query = "SELECT * FROM `cat` WHERE ";
    if (isset($o->id)) {
	$query .= "`id` = '".mysql_real_escape_string($o->id)."'";
    } else {
	MYSQL_ASSERT(isset($o->father_id), "");
	MYSQL_ASSERT(isset($o->color), "");
	MYSQL_ASSERT(isset($o->name), "");
	$query .= "
	    `father_id` = '".mysql_real_escape_string($o->father_id)."'
    	    AND `name` = '".mysql_real_escape_string($o->name)."'
	    AND `color` = '".mysql_real_escape_string($o->color)."'";
    }

    /* execute query */
    $ret = my_query($query);
    $line = mysql_fetch_assoc($ret);
    MYSQL_ASSERT($line, "mysql_fetch_assoc()");

    /* return result in object */
    return (object) $line;
}

/********************************************************************************
* OPEARIONS-CATEGORIES
********************************************************************************/
function add_opcat($op_id, $cat_id, $cat_value)
{
    $query = "INSERT into `op_cat` VALUES ('', '$op_id', '$cat_id', '$cat_value')";
    my_query($query);
}

function del_opcat_from_op($o)
{
    MYSQL_ASSERT(isset($o->id), "");
    $query = "DELETE FROM `op_cat` WHERE `op_id` = '".mysql_real_escape_string($o->id)."'";
    my_query($query);
}

function get_opcat_from_op($op_id)
{
    $query = "SELECT * FROM `op_cat` WHERE `op_id` = $op_id";
    $ret = my_query($query);
    $i = 0;
    while($line = mysql_fetch_assoc($ret)) {
	extract($line);
	$o[$i]->id       = $id;
	$o[$i]->op_id    = $op_id;
	$o[$i]->cat_id   = $cat_id;
	$o[$i]->value    = $value;
	$i++;
    }
    if ($i == 0) {
	return false;
    }
    return $o;
}

/********************************************************************************
* LABELS
********************************************************************************/
function add_label($name)
{
    $query = "INSERT INTO labels VALUES ('', '$name')";
    my_query($query);
    $id = get_label_id_from_name($name);
    if ($id == -1) {
	error("Cannot retrieve label id");
    }
    return $id;
}

function get_label_id_from_name($name)
{
    $query = "SELECT id FROM labels WHERE name='$name'";
    $ret = my_query($query);
    $nb = mysql_num_rows($ret);
    if ($nb == 1) {
	$line = mysql_fetch_assoc($ret);
	debug("Label $name (".$line['id'].") found");
	return $line['id'];
    } else {
	debug("Label $name not found!");
	return -1;
    }
}

function del_label($name)
{
    $query = "DELETE FROM `labels` WHERE `name` = '$name' LIMIT 1";
    my_query($query);
}

function get_label_nb($name)
{
    $query = "SELECT `id` FROM `labels` WHERE `name` = '$name'";
    $ret = my_query($query);
    return mysql_num_rows($ret);
}

function get_label($id)
{
    $query = "SELECT * FROM `labels` WHERE id='$id'";
    $ret = my_query($query);
    $line = mysql_fetch_assoc($ret);
    if ($line == false) {
	return false;
    }
    $label->id = $id;
    $label->name = $line['name'];
    return $label;
}




/********************************************************************************
* OPEATIONS-LABELS
********************************************************************************/
function add_oplabel($op_id, $label_id)
{
    $query = "INSERT INTO `op_labels` VALUES ('', '$op_id', '$label_id')";
    my_query($query);
}

function del_oplabel_from_op($o)
{
    MYSQL_ASSERT(isset($o->id), "invalid parameters del_oplabel_from_op()");
    $query = "DELETE FROM `op_labels` WHERE `op_id` = '".mysql_real_escape_string($o->id)."'";
    my_query($query);
}



function get_oplabel_from_op($op_id)
{
    $query = "SELECT * FROM `op_labels` WHERE `op_id` = $op_id";
    $ret = my_query($query);
    $i = 0;
    while($line = mysql_fetch_assoc($ret)) {
	extract($line);
	$o[$i]->id       = $id;
	$o[$i]->op_id    = $op_id;
	$o[$i]->label_id = $label_id;
	$i++;
    }
    if ($i == 0) {
	return false;
    }
    return $o;
}

?>
