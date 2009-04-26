<?php
require_once('./MySQL.php');
require_once('./Categories.php');

/**
 * @author David
 *
 */
class monaAPI {

    private $mysql;
    private $logger;
    const DEBUG_LEVEL = 255;

    function __construct() {
        $this->logger = new Logger('monaAPI', monaAPI::DEBUG_LEVEL);
        $this->mysql = new MySQL('127.0.0.1', 'root', 'Kamikas1', 'mona');
    }

    function getCategories() {
        $query = "SELECT * FROM cat";
        $result = $this->mysql->query($query);
        $cats = new Categories();
        while ($line = mysql_fetch_assoc($result)) {
            extract($line);
            $cats->add(new Categorie($id, $name, $father_id));
            $this->logger->debug("Ajout de la catégorie $name ($id - $father_id)");
        }
        return $cats;
    }


    /**
     * Ajout une catégorie dans la base
     *
     * @param $name nom de la categorie
     * @param $fatherId id du pere
     * @return l'identifiant de la nouvelle catégorie
     */
    function addCategorie($name, $fatherId) {
        /* ajout dans la base */
        $query = " INSERT INTO cat (`id` , `father_id` , `name` , `color`)
         VALUES ( NULL , '$fatherId', '$name', '');";
        $result = $this->mysql->query($query);
        /* on cherche le nouvel id */
        $query = "SELECT id FROM cat WHERE name = '$name' AND father_id = '$fatherId'";
        $result = $this->mysql->query($query);
        $line = mysql_fetch_assoc($result);
        return $line['id'];
    }

    function removeCategorie($id) {
        /* ajout dans la base */
        $query = " DELETE FROM cat WHERE id = '$id'";
        $result = $this->mysql->query($query);
        return $result;
    }

    function updateCategorie($id, $name, $fatherId) {
        /* ajout dans la base */
        $query = " UPDATE cat SET name='$name', father_id='$fatherId' WHERE id = '$id'";
        $result = $this->mysql->query($query);
        return $result;
    }


    /**
     * Fonction qui retourne (XML) toutes les opérations comprises entre la date de début et la date de fin.
     * @author David Moreau
     * @param $dateBegin : date de début (incluse)
     * @param $dateEnd : date de fin (incluse)
     * @return xml string
     */
    function getOperationsXml($dateBegin, $dateEnd) {
        $query = "SELECT operations.id, operations.date, operations.value, operations.confirm, op_cat.value as cat_value, op_cat.cat_id, op_labels.label_id, labels.name as label_name
             FROM operations, op_cat, cat, op_labels, labels
            WHERE  date >= $dateBegin
            AND date <= $dateEnd
            AND operations.id = op_cat.op_id
            AND op_cat.cat_id = cat.id
            AND op_labels.label_id = labels.id
            AND operations.id = op_labels.op_id";
        $res = $this->mysql->query($query);

        /* Triage du résulat dans un tableau */
        while($line = mysql_fetch_assoc($res)) {
            //print_r($line);
            extract($line);
            $tab[$id]['value']             = $value;
            $tab[$id]['date']              = $date;
            $tab[$id]['confirm']           = $confirm;
            $tab[$id]['cat'][$cat_id]      = $cat_value;
            $tab[$id]['labels'][$label_id] = $label_name;
        }
        
        /* Génération du XML */
        $xml =  "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
        $xml .= "<operations>\n";
        foreach($tab as $key => $value) {
            $xml .= "\t<operation id=\"$key\" >\n";
            $xml .= "\t\t<value>".$value['value']."</value>\n";
            $xml .= "\t\t<date>".$value['date']."</date>\n";
            /* labels */
            $xml .= "\t\t<labels>\n";
            foreach($value['labels'] as $key2 => $value2) {
                $xml .= "\t\t\t<label id=\"$key2\">$value2</label>\n";
            }
            $xml .= "\t\t</labels>\n";
            /* categories */
            $xml .= "\t\t<categories>\n";
            foreach($value['cat'] as $key3 => $value3) {
                $xml .= "\t\t\t<categorie id=\"$key3\">$value3</categorie>\n";
            }
            $xml .= "\t\t</categories>\n";

            $xml .= "\t\t<confirm>$confirm</confirm>\n";
            $xml .= "\t</operation>\n";
        }
        $xml .= "</operations>\n";
        return utf8_encode($xml);
    }

    
    /*
     * Ajoute une opération dans la base 
     */
    function addOperation($date, $total, $cat_tab, $labels)
    {
		/* on ajoute l'opération */
		$query = "INSERT INTO operations VALUES ('', '$date', '$total', '', '', '', '')";
		$res = $this->mysql->query($query);
       /* on cherche le nouvel id */
        $query = "SELECT id FROM operations WHERE date = '$date' AND value = '$total'";
        $result = $this->mysql->query($query);
        $line = mysql_fetch_assoc($result);
        $op_id = $line['id'];
        
        /* on ajoute les categories */        
        $query = "INSERT into `op_cat` VALUES ";        
		foreach($cat_tab as $key => $tab) {
			/* construction de la requÃªte SQL */
			if ($key != 0) {
				$query .= ', ';
			}
			$cat_id =$tab['id'];
			$value = $tab['somme'];
			$query .= "('', '$op_id', '$cat_id', '$value')";
		}
		$query .= ';';
        $result = $this->mysql->query($query);
        
        /* Gestion des labels */
	    $nb_labels = count($labels);
		for($i=0; $i<$nb_labels; $i++) {
			/* creer les tags qui n'existent pas */
			$query = "SELECT id FROM labels WHERE name='$labels[$i]'"; 	
			$result = $this->mysql->query($query);
		    $line = mysql_fetch_assoc($result);  
		    if (isset($line['id'])) {
		        // le label existe
		        $label_id = $line['id'];
		    } else {
		        // le label n'existe pas
		        // on l'ajoute
		        $query = "INSERT INTO labels VALUES ('', '$labels[$i]')";
		        $result = $this->mysql->query($query);
		        // on récupère l'id
		        $query = "SELECT id FROM labels WHERE name='$labels[$i]'";
				$result = $this->mysql->query($query);
				$line = mysql_fetch_assoc($result);
				$label_id = $line['id'];
		    }
		    /* on a l'id correspondant au tag, on peut ajouter la relatetion 
		       operation-tag */
		    $query = "INSERT INTO `op_labels` VALUES ('', '$op_id', '$label_id')";
		    $result = $this->mysql->query($query);
		}
		return $op_id;        
    }
   
     /*
     * Delete une opération dans la base 
     */
    function removeOperation($id) {
		$query = "DELETE FROM operations WHERE id = '$id'";
		$this->logger->debug($query);
		$ret = $this->mysql->query($query);
		if ($ret == FALSE) {
			return FALSE;
		}
		$query = "DELETE FROM op_cat WHERE op_id = $id";
		$this->logger->debug($query);
		$ret = $this->mysql->query($query);
		if ($ret == FALSE) {
			return FALSE;
		}		
		$query = "DELETE FROM op_labels WHERE op_id = $id";
		$this->logger->debug($query);

		$ret = $this->mysql->query($query);
		if ($ret == FALSE) {
			return FALSE;
		}
		return TRUE;		
	}   

}
?>
