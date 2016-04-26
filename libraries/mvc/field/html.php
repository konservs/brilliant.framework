<?php
/**
 * Component field class to edit HTML.
 * 
 * @author Andrii Biriev <a@konservs.com>
 */
bimport('mvc.field');
class BControllerField_html extends BControllerField{
	/**
	 * Generate html input
	 * 
	 * @return string HTML formated string
	 */
	public function adminhtml(){
		return '<textarea class="tinymce form-control" name="'.$this->getid($this->id).'">'.
			htmlspecialchars($this->value).
			'</textarea>'.
			'<script type="text/javascript" src="//'.BHOSTNAME_STATIC.'/admin/js/tinymce/tinymce.min.js"></script>'.
			'<script>$(document).ready(function(){'.PHP_EOL.
			'	var ro=$(\'textarea.tinymce\').hasClass(\'disabled\');'.PHP_EOL.
			'	tinymce.init({'.PHP_EOL.
			'	selector:"textarea.tinymce",'.PHP_EOL.
			'	plugins: ['.PHP_EOL.
			'		"advlist autolink lists link image charmap print preview anchor",'.PHP_EOL.
			'		"searchreplace visualblocks code fullscreen",'.PHP_EOL.
			'		"insertdatetime media table contextmenu paste moxiemanager"'.PHP_EOL.
			'		],'.PHP_EOL.
			'	gecko_spellcheck:true,'.PHP_EOL.
			'	toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | fullscreen",'.PHP_EOL.
			'	autosave_ask_before_unload: false,'.PHP_EOL.
			'	readonly: ro'.PHP_EOL.
			'	});'.PHP_EOL.
			'});</script>';
		}
	/**
	 * Init control with start value
	 */
	public function initialize($val){
		$this->value=$val;
		}
	}
