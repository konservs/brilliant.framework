<?php
namespace Brilliant\cms;
trait BSingleton{
	protected static $instance;
	/**
	 * Singleton initializer
	 *
	 * @return static
	 */
	final public static function getInstance(){
		return isset(self::$instance)?self::$instance:self::$instance=new self;
    		}
	/**
	 *
	 */
	final public function __construct() {
		$this->init();
		}
	/**
	 *
	 */
	protected function init(){
		}
	/**
	 *
	 */
	final private function __wakeup(){
		}
	/**
	 *
	 */
	final private function __clone(){
		}
	}


/*class A  {
    use Singleton;

    protected function init() {
        $this->foo = 1;
        echo "Hi!\n";
    }
}
*/
