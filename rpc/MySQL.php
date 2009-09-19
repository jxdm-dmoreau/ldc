<?php
require_once('./Logger.php');
class MySQL {

    private $server = '127.0.0.1';
    private $user = 'root';
    private $base = 'mona';
    private $passwd = 'Kamikas1';
    private $link;
    private $nbRequest;
    private $logger;

    const LOGGER_LEVEL = 255;

    /* Constructor */
    function  __construct() {
        $this->logger = new Logger('MySQL', MySQL::LOGGER_LEVEL);
        $this->nbRequest = 0;
        $this->link = mysql_connect($this->server, $this->user, $this->passwd);
        if (!$this->link) {
            $this->logger->err("Impossible de se connecter : ".mysql_error());
            die();
        }
        if (!mysql_select_db($this->base)) {
            $this->logger->err("Impossible de selectionner la base : ".mysql_error());
            die();
        }
    }


  function query($query) {
    $this->logger->debug($query);
    $result = mysql_query($query);
    if (!$result) {
        $this->logger->err(mysql_error());
    }
    $this->nbRequest++;
    return $result;
  }

  function close() {
        mysql_close($this->link);
  }


}
?>
