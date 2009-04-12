<?php


/**
 * @author David
 *
 */
class EponaTools {

    private $xml;

    function __construct() {
        $this->xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    }

    function assert($cond, $str) {
        if (!$cond) {
            $this->xml .= "<assert>$str</assert>\n";
            print($this->xml);
            exit(0);
        }
    }

    function error($str) {
        $this->xml .= "<error>$str</error>\n";
        print($this->xml);
        exit(0);
    }




}
?>
