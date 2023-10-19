<?php

class db {

	var $db_id = false;
	var $query_num = 0;
	var $query_list = array();
	var $mysql_error = '';
	var $mysql_version = '';
	var $mysql_error_num = 0;
	var $mysql_extend = "MySQLi";
	var $MySQL_time_taken = 0;
	var $query_id = false;

	function connect($db_user,$db_pass,$db_name,$db_location = 'localhost',$show_error=1)
	{
		$db_location = explode(":",$db_location);

		if (isset($db_location[1])) {

			$this->db_id = @mysqli_connect($db_location[0], $db_user, $db_pass, $db_name, $db_location[1]);

		} else {

			$this->db_id = @mysqli_connect($db_location[0], $db_user, $db_pass, $db_name);

		}

		if(!$this->db_id) {
			if($show_error == 1) {
				$this->display_error(mysqli_connect_error(), '1');
			} else {
				return false;
			}
		}

		$this->mysql_version = mysqli_get_server_info($this->db_id);

		if(!defined('COLLATE'))
		{
			define ("COLLATE", "cp1251");
		}

		mysqli_set_charset ($this->db_id , COLLATE );
		$this->sql_mode();

		$timezone = 'Europe/Moscow';
		$time = new \DateTime('now', new DateTimeZone($timezone));
		$timezoneOffset = $time->format('P');
		$this->query("SET time_zone='$timezoneOffset';");

		return true;
	}

	function query($query, $show_error=true)
	{
		$time_before = $this->get_real_time();

		if(!$this->db_id) $this->connect(DBUSER, DBPASS, DBNAME, DBHOST);

		if(!($this->query_id = mysqli_query($this->db_id, $query) )) {

			$this->mysql_error = mysqli_error($this->db_id);
			$this->mysql_error_num = mysqli_errno($this->db_id);

			if($show_error) {
				$this->display_error($this->mysql_error, $this->mysql_error_num, $query);
			}
		}

		$this->MySQL_time_taken += $this->get_real_time() - $time_before;

//			$this->query_list[] = array( 'time'  => ($this->get_real_time() - $time_before),
//										 'query' => $query,
//										 'num'   => (count($this->query_list) + 1));

		$this->query_num++;

		return $this->query_id;
	}

	function get_row($query_id = '')
	{
		if ($query_id == '') $query_id = $this->query_id;

		return mysqli_fetch_assoc($query_id);
	}

	function get_affected_rows()
	{
		return mysqli_affected_rows($this->db_id);
	}

	function get_array($query_id = '')
	{
		if ($query_id == '') $query_id = $this->query_id;

		return mysqli_fetch_array($query_id);
	}

	function super_query($query, $multi = false)
	{

		if(!$multi) {

			$this->query($query);
			$data = $this->get_row();
			$this->free();
			return $data;

		} else {
			$this->query($query);

			$rows = array();
			while($row = $this->get_row()) {
				$rows[] = $row;
			}

			$this->free();

			return $rows;
		}
	}

	function num_rows($query_id = '')
	{
		if ($query_id == '') $query_id = $this->query_id;

		return mysqli_num_rows($query_id);
	}

	function insert_id()
	{
		return mysqli_insert_id($this->db_id);
	}

	function get_result_fields($query_id = '') {

		if ($query_id == '') $query_id = $this->query_id;

		while ($field = mysqli_fetch_field($query_id))
		{
            $fields[] = $field;
		}

		return $fields;
   	}

	function safesql( $source )
	{
		if(!$this->db_id) $this->connect(DBUSER, DBPASS, DBNAME, DBHOST);

		if ($this->db_id) return mysqli_real_escape_string ($this->db_id, $source);
		else return addslashes($source);
	}

	function free( $query_id = '' )
	{

		if ($query_id == '') $query_id = $this->query_id;

		@mysqli_free_result($query_id);
	}

	function close()
	{
		@mysqli_close($this->db_id);
		$this->db_id = false;
	}

	function get_real_time()
	{
		list($seconds, $microSeconds) = explode(' ', microtime());
		return ((float)$seconds + (float)$microSeconds);
	}

	function sql_mode()
	{
		$remove_modes = array( 'STRICT_TRANS_TABLES', 'STRICT_ALL_TABLES', 'ONLY_FULL_GROUP_BY', 'NO_ZERO_DATE', 'NO_ZERO_IN_DATE', 'TRADITIONAL' );

		$res = $this->query( "SELECT @@SESSION.sql_mode" );
		$this->query_num --;
		$row = $this->get_array();

		if ( !$row[0] ) {
			return;
		}

		$modes_array = explode( ',', $row[0] );
		$modes_array = array_change_key_case( $modes_array, CASE_UPPER );

		foreach ( $modes_array as $key => $value ) {
			if ( in_array( $value, $remove_modes ) ) {
				unset( $modes_array[ $key ] );
			}
		}

		$mode_list = implode(',', $modes_array);

		if($row[0] != $mode_list) {
			$this->query( "SET SESSION sql_mode='{$mode_list}'" );
			$this->query_num --;
		}

	}

	function display_error($error, $error_num, $query = '')
	{

		$query = htmlspecialchars($query, ENT_QUOTES, 'ISO-8859-1');
		$error = htmlspecialchars($error, ENT_QUOTES, 'ISO-8859-1');

		$trace = debug_backtrace();

		$level = 0;
		if ($trace[1]['function'] == "query" ) $level = 1;
		if ($trace[2]['function'] == "super_query" ) $level = 2;

		$trace[$level]['file'] = str_replace(ROOT_DIR, "", $trace[$level]['file']);

		echo <<<HTML
        MySQL Error!
        MySQL error in file: {$trace[$level]['file']} at line {$trace[$level]['line']}
        Error Number: {$error_num}
        The Error returned was: {$error}
        SQL query: {$query}
HTML;

		exit();
	}

}

$db = New db;