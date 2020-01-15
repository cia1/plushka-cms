<?php
namespace plushka\admin\controller;
use plushka\admin\core\Controller;
use plushka\admin\core\plushka;
use plushka\core\HTTPException;

class ManualController extends Controller {

	public $content;

	/**
	 * @return string
	 * @throws HTTPException
	 */
	public function actionDialog() {
		$path=explode('/',$_GET['path']);
		$link=plushka::config('admin/_module');
		if(isset($link[$path[0]])===false) throw new HTTPException(404);
		$link=$link[$path[0]];
		if(isset($link['manual'])===false) throw new HTTPException(404);
		$path[0]=$link['manual'];
		$path=implode('/',$path);
		$i=strpos($path,'#');
		if($i!==false) {
			$anchor=substr($path,$i);
			$path=substr($path,0,$i);
		} else $anchor='';
		$path=$path.'?frame'.$anchor;
		$this->content='<iframe src="'.$path.'"></iframe>';
		$this->css('admin/manual');
		return '_empty';
	}

}