<?php
bimport('sql.mysql');

class BSearchSphinxQL extends BMySQL{
	protected static $sphinxinstance=NULL;
	/**
	 *
	 */
	public function __construct(){
		$this->db_connected=FALSE;
		$this->queries_count=0;
		$this->logsuffix='[Sphinx]';

		$this->db_host='127.0.0.1';//MYSQL_DB_HOST;
		$this->db_username='root';//MYSQL_DB_USERNAME;
		$this->db_password='';//MYSQL_DB_PASSWORD;
		$this->db_name=NULL;//MYSQL_DB_NAME;
		$this->db_port=9306;
		}
	/**
	 *
	 */
	public static function getInstance(){
		if (!is_object(self::$sphinxinstance)){
			self::$sphinxinstance=new BSearchSphinxQl();
			}
		return self::$sphinxinstance;
		}
	/**
	 *
	 */
	public static function getInstanceAndConnect(){
		if(!is_object(self::getInstance())){
			return NULL;
			}
		if(!self::$sphinxinstance->TryConnect()){
			return NULL;
			}
		return self::$sphinxinstance;
		}
	}
