<?php

class Logger {

    private $class = "undefined";
    private $level = 0;
    private $handle;

    function __construct($name, $level) {
        $this->class = "$name";
        $this->level = $level;
        $this->handle = fopen('server.log', 'w+');        
    }
    
    function __destruct() {
		if ($this->handle)
			fclose($this->handle);
    }

    private function display($level, $msg) {
        if ($level > $this->level) {
            return;
        }
        if ($this->handle)
			fwrite($this->handle, "[$this->class] $msg\n");
        else
			print "[$this->class] $msg";
    }

    function debug($msg) {
        $this->display(4, $msg);
    }

    function info($msg) {
        $this->display(3, $msg);
    }

    function warn($msg) {
        $this->display(2, $msg);
    }

    function err($msg) {
        $this->display(1, $msg);
    }



}
?>
