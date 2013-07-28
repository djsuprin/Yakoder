<?php

class DB {

    // Database connection data
	const HOST = 'HOSTNAME';
    const USER = 'USERNAME';
    const PASSWORD = 'PASSWORD';
    const SCHEMA = 'SCHEMA';
	const PORT = 3306;

	private $link;
	public $lastSelectRowsNumber;

    public function __construct($host, $login, $password, $database, $port = 3306) {
        $this->connect($host, $login, $password, $database, $port);
        $this->link->query('SET NAMES utf8');
    }

    public function connect($host, $login, $password, $database, $port) {
        $this->link = new mysqli($host, $login, $password, $database, $port);
		return $this->link->connect_errno;
    }

    public function disconnect() {
		$this->link->close();
    }

    public function query($query) {
        $parameters_count = func_num_args();
        $parameters = func_get_args();
        for ($i = 1; $i < count($parameters_count); $i++) {
            $parameters[$i] = $this->link->real_escape_string($parameters[$i]);
        }
        $query = trim(call_user_func_array('sprintf', $parameters));
        $result = $this->link->query($query);
        if (strtolower(substr($query, 0, 6)) == 'select') {
            return $this->getSelectResult($result);
        }
        return $result;
    }
	
	public function runSqlScript($script) {
		$this->link->autocommit(FALSE);
		$result = true;
		if ($this->link->multi_query($script)) {
			do {
				$this->link->store_result();
				if ($this->link->errno != 0) {
					echo $this->link->error;
					$result = false;
					break;
				}
			} while ($this->link->next_result());
		} else {
			$result = false;
		}
		if ($result) {
			$this->link->commit();
		} else {
			$this->link->rollback();
		}
		$this->link->autocommit(TRUE);
		return $result;
	}

    public function getSelectResult($result) {
        $array = array();
        if ($result != false) {
            for ($i = 0; $i < $result->num_rows; $i++) {
                $array[$i] = $result->fetch_assoc();
                for (reset($array[$i]); $j = key($array[$i]); next($array[$i])) {
                    $array[$i][$j] = stripslashes($array[$i][$j]);
                }
            }
        }
        return $array;
    }
	
	public function affectedRows() {
		return $this->link->affected_rows;
	}

}

?>