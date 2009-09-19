<?php

require_once "tools.php";



/********************************************************************************
* tools
********************************************************************************/
function my_query($query) {
    debug($query);
    $ret = mysql_query($query);
    if (!$ret) {
        error(mysql_error());
    }
    return $ret;
}



/********************************************************************************
* OPEARIONS
********************************************************************************/
function MYSQL_operation_add($date, $value, $description, $account, $recurring, $confirm)
{
    $query = "INSERT INTO operations VALUES (
		'',
		'$date',
		'$value',
		'$description',
		'$account',
		'$recurring',
		'$confirm'
	)";
    my_query($query);
    $id = MYSQL_operation_get_id($date, $value, $description, $account, $recurring, $confirm);
    if ($id == -1) {
	error("Cannot retrieve operation");
    }
    return $id;
}

function MYSQL_operation_get($id)
{
    $query = "SELECT * from `operations` WHERE `id` = '$id'";
    $line = mysql_fetch_assoc(my_query($query));
    if ($line == false) {
	return false;
    }
    $op->id = $id;
    $op->date = $line['date'];
    $op->value = $line['value'];
    $op->description = $line['description'];
    $op->account = $line['account'];
    $op->reccurring = $line['recurring'];
    $op->confirm = $line['confirm'];
    return $op;
}

function MYSQL_operation_update($id, $date, $value, $desc, $account, $recurring, $confirm)
{
    $query = "UPDATE `operations` SET
	`date` = '$date',
	`value` = '$value',
	`description` = '$desc',
	`account` = '$account',
	`recurring` = '$recurring',
    	`confirm` = '$confirm'
	WHERE `id` = $id";
    my_query($query);
}

function MYSQL_operation_del($id)
{
    $query = "DELETE FROM `operations` WHERE `id` = $id LIMIT 1";
    my_query($query);
}


function MYSQL_operation_get_id($date, $value, $description, $account, $recurring, $confirm)
{
    $query = "SELECT id FROM operations WHERE
	    date = '$date' AND
	    value = '$value' AND
	    description = '$description' AND
	    account = '$account' AND
	    recurring = '$recurring' AND
	    confirm = '$confirm' LIMIT 1";
    $ret = my_query($query);

    $nb = mysql_num_rows($ret);
    if ($nb == 1) {
	$line = mysql_fetch_assoc($ret);
	debug("Operation ".$line['id']." found");
	return $line['id'];
    } else {
	debug("Operation not found nb=$nb");
	return -1;
    }
}


/********************************************************************************
* CATEGORIES
********************************************************************************/
function MYSQL_cat_add($father_id, $name, $color)
{
    $query = "INSERT INTO `cat` VALUES ('', '$father_id', '$name', '$color')";
    my_query($query);
    $id = MYSQL_cat_get_from($father_id, $name);
    if ($id == -1) {
	error("Cannot retrieve cat id");
    }
    return $id;
}

function MYSQL_cat_get_from($father_id, $name)
{
    $query = "SELECT id FROM `cat` WHERE name='$name'AND father_id='$father_id' LIMIT 1";
    $ret = my_query($query);
    $nb = mysql_num_rows($ret);
    if ($nb == 1) {
	$line = mysql_fetch_assoc($ret);
	return $line['id'];
    } else {
	debug("cat $name not found!");
	return -1;
    }
}

function MYSQL_cat_update($id, $father_id, $name)
{
    $query = "UPDATE `cat` SET
    	`name` = '$name',
	`father_id` = '$father_id'
	WHERE `id` = $id";
    my_query($query);
}

function MYSQL_cat_del($id)
{
    $query = "DELETE FROM `cat` WHERE `id` = '$id'";
    my_query($query);
}

function MYSQL_cat_get($id)
{
    $query = "SELECT * FROM `cat` WHERE id='$id'";
    $ret = my_query($query);
    $line = mysql_fetch_assoc($ret);
    if ($line == false) {
	return false;
    }
    $cat->id = $id;
    $cat->father_id = $line['father_id'];
    $cat->name = $line['name'];
    $cat->color = $line['color'];
    return $cat;
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
