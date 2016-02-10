<?php

// andmebaasi klass

class W_DATABASE {
	var $connection, $host, $database, $charset, $collation, $query, $result, $error_msg, $error, $rows, $insert_id;

	function connect($host = false, $database = false, $username = false, $password = false, $charset = false, $collation = false) {
	    if (!$host) {
	        $host       = DB_HOST;
	        $database   = DB_NAME;
	        $username   = DB_USER;
	        $password   = DB_PASS;
	        $charset    = DB_CHARSET;
	        $collation  = DB_COLLATION;
	    }

		if (!$this->connection = @mysql_connect($host, $username, $password, false))
			die("Connection to database server has failed.<br/>". @mysql_error($this->connection));

		if (!@mysql_select_db($database, $this->connection))
			die("Database not found.<br/>". @mysql_error($this->connection));

		if ($charset && $collation)
			@mysql_query("set names '". $charset. "' collate '". $collation. "'");
	}

	function switch_db($database, $charset = false, $collation = false) {
		if (!@mysql_select_db($database, $this->connection))
			die("Database not found.<br>". @mysql_error($this->connection));

		if ($charset && $collation)
			$this->query("set names '". $charset. "' collate '". $collation. "'");
	}

	function query($query, $values = false) {
		$this->rows = $this->error = $param_count = 0;
		$this->error_msg = "";

        $param = [];
		$using = false;

		if ($this->result)
			@mysql_free_result($this->result);

		$this->query = "prepare prep_query from '". $query. "'";

		if (!$this->result = @mysql_query($this->query, $this->connection))
			return $this->error();

		if ($values) {
			foreach ($values as $value) {
				$this->query = "set @param". $param_count. " = '". mysql_real_escape_string($value). "'";
				$param[] = "@param". $param_count;

				if (!$this->result = @mysql_query($this->query, $this->connection))
					return $this->error();

				$param_count++;
			}

			$using = " using ". implode(", ", $param);
		}

		$this->query = "execute prep_query". $using;

		$this->result = @mysql_query($this->query, $this->connection);
		$this->rows = @mysql_num_rows($this->result);
		$this->insert_id = @mysql_insert_id($this->connection);

		return $this->result;
	}

	function error() {
		$this->error = @mysql_errno($this->connection);
		$this->error_msg = @mysql_error($this->connection). " [". $this->query. "]";

		return false;
	}

	function get_obj() {
		return @mysql_fetch_object($this->result);
	}

	function get_all() {
		$all = [];

		while ($obj = @mysql_fetch_object($this->result))
			if ($obj)
				$all[] = $obj;

		return $all;
	}

	function free() {
		@mysql_free_result($this->result);
	}

    function close() {
		@mysql_close($this->connection);
	}
}

?>
