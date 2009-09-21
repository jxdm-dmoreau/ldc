<?php

function debug($msg)
{
    if (strcmp(LDC_LOG_LEVEL, 'DEBUG') == 0) {
	$line = date('Y-m-d H:i:s')."\t"."DEBUG"."\t".$_SERVER['PHP_SELF']."\t".$msg;
	display($line);
    }
}

function error($msg)
{
    $line = date('Y-m-d H:i:s')."\t"."ERROR"."\t".$_SERVER['PHP_SELF']."\t".$msg;
    display($line);
    exit(1);
}

function warning($msg)
{
    $line = date('Y-m-d H:i:s')."\t"."WARNING"."\t".$_SERVER['PHP_SELF']."\t".$msg;
    display($line);
}

function display($line) {
    //$line = str_replace("\n",' ', $line);
    $line .= "\n";
    if($fp = fopen(LDC_LOG_FILE, "a+")) {
        fwrite($fp, $line, 1024);
        fclose($fp);
    } else {
	die(LDC_LOG_FILE." not opened");
    }
}





?>
