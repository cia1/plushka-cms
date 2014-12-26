<?php
/* Реализует пагинацию (строку с цифрами). Номер текущей страницы берёт из $_GET['page'].
array $options: int limit - количество элементов на странице; int count - полное количество элементов */
class widgetPagination extends widget {

	public function action() {
		if($this->options['limit']>=$this->options['count'] || !$this->options['count']) return false;
		return true;
	}

	public function render() {
		if(isset($_GET['page'])) $page=(int)$_GET['page']; else $page=1;
		if(!isset($this->options['link'])) {
			$link='?page=';
			$this->options['link']=substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'?page='));
		}
		elseif(strrpos($this->options['link'],'?')) $link=$this->options['link'].'&page=';
		else $link=$this->options['link'].'?page=';
		$lastPage=ceil($this->options['count']/$this->options['limit']);
		if($page!=1) echo '<a href="'.$this->options['link'].'">1</a>';
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

} ?>