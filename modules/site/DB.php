<?php

class DB {

	private $link;
	public $lastSelectRowsNumber;

    public function __construct($host, $login, $password, $database) {
        $this->connect($host, $login, $password, $database);
        mysql_query('SET NAMES utf8');
    }

    public function connect($host, $login, $password, $database) {
        $this->link = mysql_connect($host, $login, $password);
		if (!$this->link || !mysql_select_db($database, $this->link)) {
            return mysql_errno($this->link);
		}
        return 0;
    }

    public function disconnect() {
        mysql_close($this->link);
    }

    public function query($query) {
        $parameters_count = func_num_args();
        $parameters = func_get_args();
        for ($i = 1; $i < count($parameters_count); $i++) {
            $parameters[$i] = mysql_real_escape_string($parameters[$i], $this->link);
        }
        $query = trim(call_user_func_array('sprintf', $parameters));
        $result = mysql_query($query, $this->link);
        if (strtolower(substr($query, 0, 6)) == 'select') {
            return $this->getSelectResult($result);
        }
        return $result;
    }

    public function getSelectResult($result) {
        $array = array();
        if ($result != false) {
			$this->lastSelectRowsNumber = mysql_num_rows($result);
            for ($i = 0; $i < $this->lastSelectRowsNumber; $i++) {
                $array[$i] = mysql_fetch_assoc($result);
                for (reset($array[$i]); $j = key($array[$i]); next($array[$i])) {
                    $array[$i][$j] = stripslashes($array[$i][$j]);
                }
            }
        }
        return $array;
    }
	
	public function affectedRows() {
		return mysql_affected_rows($this->link);
	}

}

?>