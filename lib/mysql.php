<?php
# Modified by seaif@zealv.com
# Modified by BrocheXu on 2013.12.19

class datamanager {
	var $DEBUG_ERR = true;
	var $DEBUG_SQLALL = true;
	var $DEBUG_SQL = true;
	
	var $querynum = 0;
	var $link;
	var $charset;

	

	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $halt = true) {
		if($pconnect) {
			if(!$this->link = @mysql_pconnect($dbhost, $dbuser, $dbpw)) {
				$halt && $this->halt('Can not connect to MySQL server');
			}
		} else {
			if(!$this->link = @mysql_connect($dbhost, $dbuser, $dbpw, 1)) {
				$halt && $this->halt('Can not connect to MySQL server');
			}
		}
		if($this->version() > '4.1') {
			if($this->charset) {
				@mysql_query("SET character_set_connection=$this->charset, character_set_results=$this->charset, character_set_client=binary", $this->link);
			}
			if($this->version() > '5.0.1') {
				@mysql_query("SET sql_mode=''", $this->link);
			}
		}
		if($dbname) {
			@mysql_select_db($dbname, $this->link);
		}
	}

	function select_db($dbname) {
		return mysql_select_db($dbname, $this->link);
	}

	function version() {
		return mysql_get_server_info($this->link);
	}

	function _error() {
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}

	function _errno() {
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}
	
	function _query($sql, $type = '') {
		if ($this->DEBUG_SQLALL) {
			error_log(
				"SQL: {$sql} \r\n",
				3, "logs/mysql_sql_all.log"
			);
		}
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->link)) && $type != 'SILENT') {
			$this->halt('MySQL Query Error', $sql);
		}
		$this->querynum++;
		return $query;
	}
	
	function _fetch($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function _free($query) {
		return mysql_free_result($query);
	}

	function _affected_rows() {
		return mysql_affected_rows($this->link);
	}

	function _num_rows($query) {
		return mysql_num_rows($query);
	}

	function _num_fields($query) {
		return mysql_num_fields($query);
	}

	function insert_id() {
		return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function scalar($sql) {
		$query = $this->_query($sql);
		return @mysql_result($query, 0, 0);
	}
	
	function fetch($sql) {
		$query = $this->_query($sql);
		return $this->_fetch($query);
	}
	
	function query($sql) {
		$query = $this->_query($sql);
		while($value = $this->_fetch($query)){
			$v[] = $value;
		}
		$this->_free($query);
		if (empty($v)) {
			$v = array();
		}
		return $v;
	}
	
	function execute($sql) {
		if ($this->DEBUG_SQL) {
			error_log(
				"SQL: {$sql} \r\n",
				3, "logs/mysql_sql.log"
			);
		}
		$this->_query($sql);
		return $this->insert_id();
	}

	function close() {
		return mysql_close($this->link);
	}

	function halt($message = '', $sql = '') {
		if($this->DEBUG_ERR) {
			$dberror = $this->_error();
			$dberrno = $this->_errno();
			error_log(
				"Time: ".date('Y-m-d H:i:s',time())." \r\nMessage: {$message} \r\n SQL: {$sql} \r\n Error: {$dberror} \r\n Errno.: {$dberrno} \r\n \r\n",
				3, "logs/mysql_error.log"
			);
		}
		die("MySQL Error: $dberrno.");
	}
}

$db = new datamanager;
$db->charset = "utf8";
$db->connect('115.29.238.177', 'root', 'CB1qaz2wsx', 'alipay', 0);

function echoresponse($result,$error = 0,$errormsg = '') {
		global $cbserrors;
	
		$response['result'] = $result;
		$response['error'] = $error;
		if (!$errormsg) {
			$response['errormsg'] = $cbserrors[$error];
		} else {
			$response['errormsg'] = $errormsg;
		}
		echo json_encode($response);
	}
?>