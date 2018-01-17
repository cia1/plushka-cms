<?php class sController extends controller {

	public function actionDialog() {
		$path=explode('/',$_GET['path']);
		$link=core::configAdmin('_module');
		if(!isset($link[$path[0]])) core::error404();
		$link=$link[$path[0]];
		if(!isset($link['documentation'])) core::error404();
		$path[0]=$link['documentation'];
		$path=implode('/',$path);
		$i=strpos($path,'#');
		if($i!==false) {
			$anchor=substr($path,$i);
			$path=substr($path,0,$i);
		} else $anchor='';
		$path=$path.'?frame'.$anchor;
		$this->content='<iframe src="'.$path.'"></iframe>';
		$this->style('documentation');
		return '_empty';
	}

}