<?php
namespace Brilliant\Users;

use Brilliant\Users\BUsers;

/**
 * Basic class to control single user
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
class BUser extends \Brilliant\Items\BItemsItem {
	protected $collectionname = '\Brilliant\Users\BUsers';
	protected $tableName = 'users';
	/**
	 * @var DateTime
	 */
	public $created;
	/**
	 * @var DateTime
	 */
	public $modified;
	/**
	 * @var DateTime
	 */
	public $last_action;
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $password;

	/**
	 * Constructor - init fields...
	 */
	function __construct() {
		parent::__construct();
		$this->fieldAddRaw('email', 'string');
		$this->fieldAddRaw('status', 'enum', array('values' => array('P', 'N', 'D')));
		$this->fieldAddRaw('isadmin', 'enum', array('values' => array('Y', 'N')));
		$this->fieldAddRaw('password', 'string');
		$this->fieldAddRaw('name', 'string');
		$this->fieldAddRaw('firstname', 'string');
		$this->fieldAddRaw('lastname', 'string');
		$this->fieldAddRaw('middlename', 'string');
		$this->fieldAddRaw('last_action', 'dt', array('emptynull' => true));
		//Created & modified
		$this->fieldAddRaw('created', 'dt', array('readonly' => true));
		$this->fieldAddRaw('modified', 'dt', array('readonly' => true));
	}

	/**
	 * Get user name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 *
	 */
	public function setpassword($pass) {
		$bUsers = BUsers::getInstance();
		$hash = $bUsers->makepass($this->email, $pass);
		$this->password = $hash;
		return true;
	}

}
