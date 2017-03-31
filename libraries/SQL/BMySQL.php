<?php
//============================================================
// Sets of functions and classes to work with MySQL database
//
// Author: Andrii Biriev, b@brilliant.ua
//============================================================
namespace Brilliant\sql;
use Brilliant\log\BLog;

class BMySQL{
	protected $db_connected;
	protected $mysqli;
	protected static $instance=NULL;
	public $queries_count=0;
	public $db_host;
	public $db_username;
	public $db_password;
	public $db_name;
	//====================================================
	//
	//====================================================
	public function __construct(){
		$this->db_connected=FALSE;
		$this->queries_count=0;
		$this->logsuffix='[MySQL]';

		$this->db_host=MYSQL_DB_HOST;
		$this->db_username=MYSQL_DB_USERNAME;
		$this->db_password=MYSQL_DB_PASSWORD;
		$this->db_name=MYSQL_DB_NAME;
		$this->db_port=3306;
		}
	//================================================================================
	// Returns the global Session object, only creating it
	// if it doesn't already exist.
	//================================================================================
	public static function getInstance(){
		if(!is_object(self::$instance)){
			self::$instance=new BMySQL();
			}
		return self::$instance;
		}
	//================================================================================
	//The count of MySQL queries...
	//================================================================================
	public static function getQueriesCount(){
		if (!is_object(self::$instance))
			return 0;
		return self::$instance->queries_count;
		}
	//================================================================================
	//
	//================================================================================
	public static function getInstanceAndConnect(){
		if(!is_object(self::getInstance()))return NULL;
		if(!self::$instance->TryConnect())return NULL;
		return self::$instance;
		}
	//================================================================================
	//
	//================================================================================
	public function tryConnect(){
		if($this->db_connected){
			return TRUE;
			}
		if(!class_exists('mysqli')){
			BLog::addToLog($this->logsuffix.': MySQLi class not found',LL_ERROR);
			return FALSE;
			}
		$this->mysqli=new \mysqli($this->db_host, $this->db_username, $this->db_password, $this->db_name, $this->db_port);
		if((empty($this->mysqli))||(mysqli_connect_errno())){
			BLog::addToLog($this->logsuffix.': '.mysqli_connect_error(),LL_ERROR);
			return FALSE;
			}
		if(!$this->mysqli->set_charset("utf8"))return FALSE;
		$this->db_connected=TRUE;
		return TRUE;
		}
	/**
	 * Real query
	 */
	public function realQuery($sql){
		BLog::addToLog($this->logsuffix.' Query: '.$sql);
		$this->queries_count++;
		$r=$this->mysqli->real_query($sql);
		if((DEBUG_MODE)&&(empty($r))){
			BLog::addToLog($this->logsuffix.' query failed!',LL_ERROR);
			BLog::addToLog($this->logsuffix.' query="'.$sql.'";',LL_ERROR);
			BLog::addToLog($this->logsuffix.' query error='.$this->lasterror(),LL_ERROR);
			}
		return $r;
		}
	/**
	 * SQL query
	 */
	public function query($sql){
		BLog::addToLog($this->logsuffix.' Query: '.$sql);
		if(empty($this->mysqli)){
			BLog::addToLog($this->logsuffix.'$this->mysqli is empty!',LL_ERROR);
			return false;
			}

		$this->queries_count++;
		$r=$this->mysqli->query($sql);
		if(empty($r)){
			BLog::addToLog($this->logsuffix.' query="'.$sql.'";',LL_ERROR);
			BLog::addToLog($this->logsuffix.' query error='.$this->lasterror(),LL_ERROR);
			}
		return $r;
		}
	//====================================================
	//
	//====================================================
	public function multiQuery($sql){
		$this->queries_count++;
		return $this->mysqli->multi_query($sql);
		}
	//====================================================
	//
	//====================================================
	public function escapeString($s, $EMPTY_NULL=false){
		if((!is_string($s))&&(!is_numeric($s))){
			$s='';
			}
		if(($EMPTY_NULL)&&(empty($s))){
			return 'NULL';
			}
		return '"'.$this->mysqli->real_escape_string($s).'"';
		}
	/**
	 *
	 */
	public function escapeDateTime($dt, $EMPTY_NULL=true){
		/*if(!is_string($s)){
			$s='';
			}*/
		if(($EMPTY_NULL)&&(empty($dt))){
			return 'NULL';
			}
		$str='"'.$dt->format('Y-m-d H:i:s').'"';
		return $str;
		}

	//====================================================
	//
	//====================================================
	public function fetch($q){
		if(empty($q)){
			return NULL;
			}
		return $q->fetch_assoc();
		}
	//====================================================
	//
	//====================================================
	public function lastError(){
		if(empty($this->mysqli)){
			return 'MySQLi not created!';
			}else{
			return $this->mysqli->error;
			}
		}
	//====================================================
	//
	//====================================================
	public function queryAndFetch($sql){
		$q=$this->Query($sql);
		if(empty($q))return 0;
		$res=array();
		while($l=($this->fetch($q)))
			$res[]=$l;
		return $res;
		}
	//====================================================
	//
	//====================================================
	public function affectedRows(){
		return $this->mysqli->affected_rows;
		}
	//====================================================
	//
	//====================================================
	public function startTransaction(){
		return $this->Query('start transaction');
		}
	//====================================================
	//
	//====================================================
	public function commit(){
		return $this->Query('commit');
		}
	//====================================================
	//
	//====================================================
	public function rollback(){
		return $this->Query('rollback');
		}
	//====================================================
	//
	//====================================================
	public function insertId(){
		return $this->mysqli->insert_id;
		}
	}
