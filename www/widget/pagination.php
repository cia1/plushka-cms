<?php
/* Виджит пагинации
$this->options: string link - целевая ссылка (если не задано, то генерируется автоматически);
string pageName - имя GET-параметра номера страницы, по умолчанию "page";
int limit - кол-во элементов на странице;
int count - общее количество элементов;
*/
class widgetPagination extends widget {

	public function __invoke() {
		if($this->options['limit']>=$this->options['count'] || !$this->options['count']) return false;
		if(!isset($this->options['pageName'])) $this->options['pageName']='page';
		if(!isset($this->options['link'])) {
			$link=$_SERVER['REQUEST_URI'];
			$i=strpos($link,'?');
			if($i) $link=substr($link,0,$i);
			$uri=array();
			foreach($_GET as $key=>$value) {
			if($key=='corePath' || $key==$this->options['pageName']) continue;
				$uri[$key]=$value;
			}
			if(count($uri)) $link.='?'.http_build_query($uri).'&';
			$this->options['link']=$link;
		} else $this->options['link']=core::link($this->options['link']);
		$i=strpos($this->options['link'],'?');
		if($i!==false) $this->options['link'].='&'; else $this->options['link'].='?';
		$this->options['link'].=$this->options['pageName'].'=';
		return true;
	}

	public function render($view) {
		if(isset($_GET[$this->options['pageName']])) $page=(int)$_GET[$this->options['pageName']]; else $page=1;
		$lastPage=ceil($this->options['count']/$this->options['limit']);
		$link=$this->options['link'];
		if($page!=1) echo '<a href="'.substr($link,0,strlen($link)-strlen($this->options['pageName'])-2).'">1</a>';
		if($page>5) echo '<span>...</span>';
		if($page>3 && $page!=4) echo '<a href="'.$link.($page-3).'">'.($page-3).'</a>';
		if($page>2 && $page!=3) echo '<a href="'.$link.($page-2).'">'.($page-2).'</a>';
		if($page>1 && $page!=2) echo '<a href="'.$link.($page-1).'">'.($page-1).'</a>';
		echo '<span class="current">'.$page.'</span>';
		if($lastPage>$page && $page!=$lastPage-1) echo '<a href="'.$link.($page+1).'">'.($page+1).'</a>';
		if($lastPage>$page+1 && $page!=$lastPage-2) echo '<a href="'.$link.($page+2).'">'.($page+2).'</a>';
		if($lastPage>$page+2 && $page!=$lastPage-3) echo '<a href="'.$link.($page+3).'">'.($page+3).'</a>';
		if($lastPage-$page>4) echo '<span>...</span>';
		if($page!=$lastPage) echo '<a href="'.$link.$lastPage.'">'.$lastPage.'</a>';
	}

}