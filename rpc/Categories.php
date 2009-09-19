<?php

class Categories {

    private $fathers;
    private $catsById;
    private $cats;
    private $logger;

    const DEBUG_LEVEL = 255;

    function __construct() {
        $this->logger = new Logger('Categories', Categories::DEBUG_LEVEL);
    }

    function getXml() {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
        $xml .= "<categories>\n";
        foreach($this->catsById as $key => $value) {
            $name = $value->getName();
            $fatherId = $value->getFather();
            $xml .= "\t<categorie id=\"$key\" name=\"$name\" father_id=\"$fatherId\"/>\n";
        }
        $xml .= "</categories>\n";
        return utf8_encode($xml);
    }

    function add($cat) {
        $id = $cat->getId();
        $this->catsById["$id"] = $cat;
        $this->cats[] = $cat;
        $this->fathers[$cat->getFather()][] = $cat->getId();
    }

    function size() {
        return sizeof($this->cats);
    }

    function getCategorie($i) {
        if ($i < 0 || $i > $this->size()) {
            die("Categorie $i erreur");
        }
        return $this->cats[$i];
    }

    function getCatFromId($id) {
        if (isset($this->catsById[$id])) {
            return $this->catsById[$id];
        } else {
            $this->logger->err("Cat Id $id not found!");
            return FALSE;
        }
    }

    function getChildren($father_id) {
        if (!isset($this->fathers[$father_id])) {
            return FALSE;
        }
        return $this->fathers[$father_id];
    }

}
?>
