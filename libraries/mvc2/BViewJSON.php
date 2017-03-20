<?php
/**
 * Created by PhpStorm.
 */

class BViewJSON extends BView{
	public function generate($data){
		return json_encode($data->json);
		}
	}