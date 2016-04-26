<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 27.01.16
 * Time: 17:43
 */

class BViewJSON extends BView{
	public function generate($data){
		return json_encode($data->json);
		}
	}