<?php
//============================================================
// Sets of functions and classes to work with MySQL database
//
// Author: Andrii Biriev, b@brilliant.ua
//============================================================
namespace Brilliant\SQL;

use Brilliant\Log\BLog;

class BMySQL {
	/**
	 * @var bool
	 */
	protected $db_connected;
	/**
	 * @var \mysqli
	 */
	protected $mysqli;
	/**
	 * @var BMySQL | null
	 */
	protected static $instance = NULL;
	/**
	 * @var int
	 */
	public $queries_count = 0;
	/**
	 * @var string
	 */
	public $db_host;
	/**
	 * @var string
	 */
	public $db_username;
	/**
	 * @var string
	 */
	public $db_password;
	/**
	 * @var string
	 */
	public $db_name;

	/**
	 * BMySQL constructor.
	 */
	public function __construct() {
		$this->db_connected = FALSE;
		$this->queries_count = 0;
		$this->logsuffix = '[MySQL]';

		$this->db_host = MYSQL_DB_HOST;
		$this->db_username = MYSQL_DB_USERNAME;
		$this->db_password = MYSQL_DB_PASSWORD;
		$this->db_name = MYSQL_DB_NAME;
		$this->db_port = 3306;
	}

	/**
	 * Returns the global Session object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return BMySQL|null
	 */
	public static function getInstance() {
		if (!is_object(self::$instance)) {
			self::$instance = new BMySQL();
		}
		return self::$instance;
	}

	/**
	 * The count of MySQL queries...
	 *
	 * @return int
	 */
	public static function getQueriesCount() {
		if (!is_object(self::$instance)) return 0;
		return self::$instance->queries_count;
	}

	/**
	 * Get instance if all is ok
	 *
	 * @return BMySQL|null
	 */
	public static function getInstanceAndConnect() {
		if (!is_object(self::getInstance())) {
			return NULL;
		}
		if (!self::$instance->tryConnect()) {
			return NULL;
		}
		return self::$instance;
	}

	/**
	 * Try to connect
	 * @return bool
	 */
	public function tryConnect() {
		if ($this->db_connected) {
			return TRUE;
		}
		if (!class_exists('mysqli')) {
			BLog::addToLog($this->logsuffix . ': MySQLi class not found', LL_ERROR);
			return FALSE;
		}
		$this->mysqli = new \mysqli($this->db_host, $this->db_username, $this->db_password, $this->db_name, $this->db_port);
		if ((empty($this->mysqli)) || (mysqli_connect_errno())) {
			BLog::addToLog($this->logsuffix . ': ' . mysqli_connect_error(), LL_ERROR);
			return FALSE;
		}
		if (!$this->mysqli->set_charset("utf8")) return FALSE;
		$this->db_connected = TRUE;
		return TRUE;
	}

	/**
	 * Real query
	 *
	 * @param $sql
	 * @return bool
	 */
	public function realQuery($sql) {
		BLog::addToLog($this->logsuffix . ' Query: ' . $sql);
		$this->queries_count++;
		$r = $this->mysqli->real_query($sql);
		if ((DEBUG_MODE) && (empty($r))) {
			BLog::addToLog($this->logsuffix . ' query failed!', LL_ERROR);
			BLog::addToLog($this->logsuffix . ' query="' . $sql . '";', LL_ERROR);
			BLog::addToLog($this->logsuffix . ' query error=' . $this->lastError(), LL_ERROR);
		}
		return $r;
	}

	/**
	 * SQL query
	 *
	 * @param $sql
	 * @return \mysqli_result | null
	 */
	public function query($sql) {
		BLog::addToLog($this->logsuffix . ' Query: ' . $sql);
		if (empty($this->mysqli)) {
			BLog::addToLog($this->logsuffix . '$this->mysqli is empty!', LL_ERROR);
			return null;
		}

		$this->queries_count++;
		$r = $this->mysqli->query($sql);
		if (empty($r)) {
			BLog::addToLog($this->logsuffix . ' query="' . $sql . '";', LL_ERROR);
			BLog::addToLog($this->logsuffix . ' query error=' . $this->lastError(), LL_ERROR);
		}
		return $r;
	}

	/**
	 * @param $sql
	 * @return bool
	 */
	public function multiQuery($sql) {
		$this->queries_count++;
		return $this->mysqli->multi_query($sql);
	}

	/**
	 * Escape string for MySQL queries
	 *
	 * @param $s string
	 * @param bool $EMPTY_NULL
	 * @return string
	 */
	public function escapeString($s, $EMPTY_NULL = false) {
		if ((!is_string($s)) && (!is_numeric($s))) {
			$s = '';
		}
		if (($EMPTY_NULL) && (empty($s))) {
			return 'NULL';
		}
		return '"' . $this->mysqli->real_escape_string($s) . '"';
	}

	/**
	 * Escape DateTime
	 *
	 * @param $dt \DateTime
	 * @param bool $EMPTY_NULL
	 * @return string
	 */
	public function escapeDateTime($dt, $EMPTY_NULL = true) {
		/*if(!is_string($s)){
			$s='';
			}*/
		if (($EMPTY_NULL) && (empty($dt))) {
			return 'NULL';
		}
		$str = '"' . $dt->format('Y-m-d H:i:s') . '"';
		return $str;
	}

	/**
	 * @param $q \mysqli_result
	 *
	 * @return array an associative array of strings representing the fetched row
	 * in the result set.
	 */
	public function fetch($q) {
		if (empty($q)) {
			return NULL;
		}
		return $q->fetch_assoc();
	}

	/**
	 * Get last error
	 *
	 * @return string
	 */
	public function lastError() {
		if (empty($this->mysqli)) {
			return 'MySQLi not created!';
		} else {
			return $this->mysqli->error;
		}
	}

	/**
	 * Query and fetch
	 *
	 * @param $sql
	 * @return array|int
	 */
	public function queryAndFetch($sql) {
		$q = $this->query($sql);
		if (empty($q)) {
			return array();
		}
		$res = array();
		while ($l = ($this->fetch($q))) {
			$res[] = $l;
		}
		return $res;
	}

	/**
	 * Get affected rows
	 *
	 * @return int
	 */
	public function affectedRows() {
		return $this->mysqli->affected_rows;
	}

	/**
	 * Start transaction
	 *
	 * @return \mysqli_result|null
	 */
	public function startTransaction() {
		return $this->query('start transaction');
	}

	/**
	 * Commit transaction
	 *
	 * @return \mysqli_result|null
	 */
	public function commit() {
		return $this->query('commit');
	}

	/**
	 * Rollback transaction
	 *
	 * @return \mysqli_result|null
	 */
	public function rollback() {
		return $this->query('rollback');
	}

	/**
	 * Get inserted ID
	 *
	 * @return mixed
	 */
	public function insertId() {
		return $this->mysqli->insert_id;
	}
}
