<?php

require_once "tools.php";



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
function MYSQL_operation_add($o)
{
    MYSQL_ASSERT(isset($o->date),        "date not defined");
    MYSQL_ASSERT(isset($o->value),       "value not defined");
    MYSQL_ASSERT(isset($o->account),     "account not defined");
    MYSQL_ASSERT(isset($o->recurring),   "recurring not defined");
    MYSQL_ASSERT(isset($o->description), "description not defined");
    MYSQL_ASSERT(isset($o->confirm),     "confirm not defined");

    $query = "INSERT INTO operations VALUES (
		'',
		'".mysql_real_escape_string($o->date)."',
		'".mysql_real_escape_string($o->value)."',
		'".mysql_real_escape_string($o->description)."',
		'".mysql_real_escape_string($o->account)."',
		'".mysql_real_escape_string($o->recurring)."',
		'".mysql_real_escape_string($o->confirm)."'
	)";
    my_query($query);
    return MYSQL_operation_get($o);
}



function MYSQL_operation_get($o)
{
    /* construct query */
    $query = "SELECT * FROM `operations` WHERE ";
    if (isset($o->id)) {
	$query .= "`id` = '".mysql_real_escape_string($o->id)."'";
    } else {
	MYSQL_ASSERT(isset($o->date),        "date not defined");
	MYSQL_ASSERT(isset($o->value),       "value not defined");
	MYSQL_ASSERT(isset($o->account),     "account not defined");
	MYSQL_ASSERT(isset($o->recurring),   "recurring not defined");
	MYSQL_ASSERT(isset($o->description), "description not defined");
	MYSQL_ASSERT(isset($o->confirm),     "confirm not defined");
	$query .= "`date` = '".mysql_real_escape_string($o->date)."' AND ";
	$query .= "`value` = '".mysql_real_escape_string($o->value)."' AND";
	$query .= "`account` = '".mysql_real_escape_string($o->account)."' AND";
	$query .= "`recurring` = '".mysql_real_escape_string($o->recurring)."' AND";
	$query .= "`description` = '".mysql_real_escape_string($o->description)."' AND";
	$query .= "`confirm` = '".mysql_real_escape_string($o->confirm)."'";
    }
    $query .= " ORDER BY `operations`.`id` DESC";

    /* execute query */
    $ret = my_query($query);
    $line = mysql_fetch_assoc($ret);
    MYSQL_ASSERT($line, "mysql_fetch_assoc()");

    /* return result in object */
    return (object) $line;
}

function MYSQL_operation_update($o)
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

function MYSQL_operation_del($o)
{
    $query = "DELETE FROM `operations` WHERE `id` = ".mysql_real_escape_string($o->id);
    my_query($query);
    return $o;
}


/********************************************************************************
* CATEGORIES
********************************************************************************/
function MYSQL_cat_add($o)
{
    $query = "INSERT INTO `cat` VALUES ('', '$o->father_id', '$o->name', '$o->color')";
    my_query($query);
    return MYSQL_cat_get($o);
}


function MYSQL_cat_update($o)
{
    $query = "UPDATE `cat` SET
    	`name`      = '$o->name',
	`father_id` = '$o->father_id',
	`color`     = '$o->color'
	WHERE `id`  = '$o->id'";
    my_query($query);
    return $o;
}

function MYSQL_cat_del($o)
{
    $query = "DELETE FROM `cat` WHERE `id` = '$o->id'";
    my_query($query);
    return $o;
}

function MYSQL_cat_get($o)
{
    /* construct query */
    $query = "SELECT * FROM `cat` WHERE ";
    foreach($o as $attr => $value) {
	$attr = mysql_real_escape_string("$attr");
	$value = mysql_real_escape_string("$value");
	$query .= " `$attr` = '$value' AND ";
    }
    $query = substr($query, 0, strlen($query) - 4);

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
function MYSQL_opcat_add($op_id, $cat_id, $cat_value)
{
    $query = "INSERT into `op_cat` VALUES ('', '$op_id', '$cat_id', '$cat_value')";
    my_query($query);
}

function MYSQL_opcat_del_from_op($op_id)
{
    $query = "DELETE FROM `op_cat` WHERE `op_id` = $op_id";
    my_query($query);
}

function MYSQL_opcat_get_from_op($op_id)
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
function MYSQL_label_add($name)
{
    $query = "INSERT INTO labels VALUES ('', '$name')";
    my_query($query);
    $id = MYSQL_label_get_from_name($name);
    if ($id == -1) {
	error("Cannot retrieve label id");
    }
    return $id;
}

function MYSQL_label_get_from_name($name)
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

function MYSQL_label_del($name)
{
    $query = "DELETE FROM `labels` WHERE `name` = '$name' LIMIT 1";
    my_query($query);
}

function MYSQL_label_get_nb($name)
{
    $query = "SELECT `id` FROM `labels` WHERE `name` = '$name'";
    $ret = my_query($query);
    return mysql_num_rows($ret);
}

function MYSQL_label_get($id)
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
function MYSQL_oplabel_add($op_id, $label_id)
{
    $query = "INSERT INTO `op_labels` VALUES ('', '$op_id', '$label_id')";
    my_query($query);
}

function MYSQL_oplabel_del_from_op($op_id)
{
    $query = "DELETE FROM `op_labels` WHERE `op_id` = $op_id";
    my_query($query);
}



function MYSQL_oplabel_get_from_op($op_id)
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
