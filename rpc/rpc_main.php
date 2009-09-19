<?php


function add_operation($json)
{
	$logger = new Logger(__FUNCTION__, 255);

        $logger->debug(print_r($json, true));

	$link = mysql_connect('127.0.0.1', 'root', 'Kamikas1');
	mysql_select_db('mona', $link);
		
	$json->date        = mysql_real_escape_string($json->date);
	$json->value       = mysql_real_escape_string($json->value);
	$json->description = mysql_real_escape_string($json->description);
	$json->account     = mysql_real_escape_string($json->account);
	$json->recurring   = mysql_real_escape_string($json->recurring);
	$json->confirm     = mysql_real_escape_string($json->confirm);
	


	$query = "INSERT INTO operations VALUES (
		'',
		'$json->date',
		'$json->value',
		'$json->description',
		'$json->account',
		'$json->recurring',
		'$json->confirm'
	)";
	
	$logger->debug($query);
	$ret = mysql_query($query, $link);
	if ($ret == false) {
		$logger->err(mysql_error());
		return false;
	}    
	
	$query = "SELECT id FROM operations WHERE
		 date = '$json->date' AND
		 value = '$json->value' AND
		 description = '$json->description' AND
		 account = '$json->account' AND
		 recurring = '$json->recurring' AND
		 confirm = '$json->confirm'";
	
	$logger->debug($query);
	$ret = mysql_query($query, $link);
	if ($ret == false) {
		$logger->err(mysql_error());
		return false;
	}        
        
	$nb = mysql_num_rows($ret);
	if ($nb == 0) {
		return false;
    }
	if ($nb > 1) {
		$logger->warn("Something wrong...");
	}
        
	$line = mysql_fetch_assoc($ret);

	$json->date        = stripslashes($json->date);
	$json->value       = stripslashes($json->value);
	$json->description = stripslashes($json->description);
	$json->account     = stripslashes($json->account);
	$json->recurring   = stripslashes($json->recurring);
	$json->confirm     = stripslashes($json->confirm);
	$json->id = $line['id'];

        /* on ajoute les categories */        
        $query = "INSERT into `op_cat` VALUES ";        
        foreach($json->cats as $cat) {
                /* construction de la requete SQL */
                $query .= "('', '$json->id', '$cat->id', '$cat->value'),";
        }
        $query[strlen($query)-1] = '';
        $query .= ';';
        $logger->debug($query);
        $ret = mysql_query($query);
        if ($ret == false) {
                $logger->err(mysql_error());
                return false;
        }

	return $json;
}


function del_operation($json)
{
	$logger = new Logger(__FUNCTION__, 255);

	$link = mysql_connect('127.0.0.1', 'root', 'Kamikas1');
	mysql_select_db('mona', $link);

        $query = "DELETE FROM operations WHERE id = '$json->id'";
        $logger->debug($query);
        $ret = mysql_query($query);
        if ($ret == false) {
                $logger->err(mysql_error());
                return false;
        }

        $query = "DELETE FROM op_cat WHERE op_id = $json->id";
        $logger->debug($query);
        $ret = mysql_query($query);
        if ($ret == false) {
                $logger->err(mysql_error());
                return false;
        }

        $query = "DELETE FROM op_labels WHERE op_id = $json->id";
        $logger->debug($query);
        $ret = mysql_query($query);
        if ($ret == false) {
                $logger->err(mysql_error());
                return false;
        }

        return TRUE;		
}
