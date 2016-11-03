<?php
/**
 * View for article
 *
 * @author Andrii Biriev, a@konservs.com
 */
defined('BEXEC') or die('No direct access!');

bimport('mvc.component');
bimport('mvc.view');
bimport('cms.breadcrumbs');

class View_content_category extends BView{
	/**
	 * Prepare breadcrumbs object
	 */
	public function prepare_breadcrumbs(){
		$lang=BLang::$langcode;
		$brouter=BRouter::getInstance();
		$this->breadcrumbs_add_homepage();
		//Parent categories
		/*if(!empty($this->category)){
			$cats=$this->category->getCategoryTree();
			foreach($cats as $cat){
				if($cat->id != $this->category->id){
					$this->breadcrumbs_add(
						'//'.$brouter->generateurl('content',$lang,array('view'=>'blog','id'=>$cat->id)),
						$cat->getname(),
						true);
					}
				}
			//Our category
			$this->breadcrumbs_add(
				'',
				$this->category->getname(),
				false);
			}*/
		}
	/**
	 *
	 */
	public function generateheaders(){
		$this->setlastmodified($this->article->modified);
		//Get heading (H1 tag)
		$this->heading=$this->article->geth1();
		if(empty($this->heading)){
			$this->heading=$this->article->getname();
			}
		//Get Title
		$this->title=$this->article->gettitle();
		if(empty($this->title)){
			$this->title=$this->article->getname();
			}
		$this->settitle($this->title);
		//Get META tags...


/*
			$title=$this->category->gettitle($lang);
			if(empty($title)){
				$title=$this->category->getname($lang);
				}
			$this->settitle($title);
			$this->setlastmodified($this->category->modified);
			$this->addmeta('description',$this->category->getmetadesc($lang));
			$this->addmeta('keywords',$this->category->getmetakeyw($lang));
			}*/

		//Some useful links
		$brouter=BRouter::getInstance();
		//$this->url_nolang='//'.$brouter->generateURL('content','ua',array('view'=>'article','id'=>$this->article->id));
		//
		$cat=$this->article->getcategory();
		$this->category_link=$cat->getURL();
		$this->category_name=$cat->getname();
		}
	/**
	 * Prepare HTML
	 */
	public function generate($data){
		$this->articles=$data->articles;
		$this->category=$data->category;
		if(empty($this->category)){
			return 'Could not load category!';
			}
		//
		$this->generateheaders();
		$this->prepare_breadcrumbs();
		$this->setcache('true');
		return $this->template_load();
		}
	}
