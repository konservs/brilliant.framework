<?php
/**
 * Social authorization
 *
 * @author: Andrii Biriev
 */
bimport('users.social.adapterinterface');

class BSocialAuther{
	/**
	 * Adapter manager
	 *
	 * @var BSocialAdapterInterface
	 */
	protected  $adapter = null;

	/**
	 * Constructor.
	 *
	 * @param BSocialAdapterInterface $adapter
	 * @throws Exception\InvalidArgumentException
	 */
	public function __construct($adapter){
		if ($adapter instanceof BSocialAdapterInterface) {
			$this->adapter = $adapter;
			}
		else {
			throw new Exception\InvalidArgumentException(
				'SocialAuther only expects instance of the type' .
				'SocialAuther\Adapter\BSocialAdapterInterface.'
				);
			}
		}
	/**
	 * Call method authenticate() of adater class
	 *
	 * @return bool
	 */
	public function authenticate(){
		return $this->adapter->authenticate();
		}
	/**
	 * Call method of this class or methods of adapter class
	 *
	 * @param $method
	 * @param $params
	 * @return mixed
	 */
	public function __call($method, $params){
		if (method_exists($this, $method)) {
			return $this->$method($params);
			}
		if (method_exists($this->adapter, $method)) {
			return $this->adapter->$method();
			}
		}
	}
