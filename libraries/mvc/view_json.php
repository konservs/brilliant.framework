<?php
/**
 *
 */
bimport('mvc.view');

class BViewJsonn extends BView{
	/**
	 *
	 */
	public function generate($data){
		if($data->error!=0){
			return 'ERROR: '.$data->error;
			}
		return json_encode($data->json);
		}
	}
