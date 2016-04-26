<?php
/**
 * Pretty class for phones formating
 * 
 * @author Andrii Biriev
 * @author Andrii Karepin
 * @copyright © Brilliant IT corporation, http://it.brilliant.ua
 */
class BPhone{
	public $id=0;
	public $checked=0;
	public $checkcode='';
	public $codesent=NULL;
	public $codechecked=NULL;
	public function init_string($str){
		//works only for Ukraine, 9 numbers in phone
		$this->tel=substr($str, -9);
		$this->op_code=380;
		}
//	protected $user=0;//user id
	/**
	 * Load phone data from array
	 */
	public function init_array($arr){
		$this->id=(int)$arr['id'];
		$this->user=isset($arr['user'])?$arr['user']:NULL;
		$this->firm=isset($arr['firm'])?$arr['firm']:NULL;
		$this->checked=(int)$arr['checked'];
		$this->checkcode=$arr['checkcode'];
		$this->codesent=empty($arr['codesent'])?NULL:new DateTime($arr['codesent']);
		$this->codechecked=empty($arr['codechecked'])?NULL:new DateTime($arr['codechecked']);
		$this->op_code=(int)$arr['op_code'];//380
		$this->tel=(int)$arr['tel'];//380
		$this->call_from=$arr['call_from'];//empty($arr['call_from'])?NULL:new DateTime($arr['call_from']);
		$this->call_to=$arr['call_to'];//empty($arr['call_to'])?NULL:new DateTime($arr['call_to']);
		$this->call_name=$arr['call_name'];
		}
	/**
	 * format: +xxxxxxxxxxxx
	 */
	public function pretty_print($options=array()){
		if(empty($this->op_code)){
			$this->op_code='380';
			}
		$html='+'.$this->op_code.$this->tel;
		return $html;
		}

	/**
	 * format: +38 (xxx) xxx-xx-xx
	 */
	public function pretty_print_2($options=array()){
		$html='+38&nbsp;(0'.substr($this->tel, 0, 2).')&nbsp;'.substr($this->tel, 2, 3).'-'.substr($this->tel, 5, 2).'-'.substr($this->tel, 7, 2);
		return $html;
		}
	/**
	 * format: (xxx) xxx-xx-xx
	 */
	public function pretty_print_3($options=array()){
		$html='(0'.substr($this->tel, 0, 2).')&nbsp;'.substr($this->tel, 2, 3).'-'.substr($this->tel, 5, 2).'-'.substr($this->tel, 7, 2);
		return $html;
		}
	}
