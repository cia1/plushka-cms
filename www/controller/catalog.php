<?php
/* Реализует страницы универсального каталога: список элементов каталога (настраиваемый блог), детальная информация элемента
 Все поля в базе данных хранятся в одной таблице с именем catalog_XX, XX - идентификатор каталога. Таблица создаётся и изменяется автоматически через админку.
 ЧПУ: /catalog/ИД (actionIndex) - список элементов каталога; /catalog/ИД/ПСЕВДОНИМ (actionView) - отдельный элемент каталога
*/
class sController extends controller {

	public function __construct() {
		parent::__construct();
		$this->layoutId=(int)$this->url[1]; //Идентификатор каталога (на сайте может быть несколько каталогов)
		if(!$this->layoutId) core::error404();
		if(file_exists(core::path().'config/catalogLayout/'.$this->layoutId.'.php')) $this->layout=core::config('catalogLayout/'.$this->layoutId); //Конфигурация каталога, содержит список полей и другую информацию
		else core::error404();
		if(count($this->url)==2) $this->url[1]='Index'; else $this->url[1]='View'; //http://example.com/catalog/ИД/элемент_каталога
		core::import('model/catalog'); //Содержит методы для генерации HTML-представления полей каталога
	}

	/* Список элементов каталога */
	public function actionIndex() {
		//Вступительный текст, хранится в отдельном файле
		$f=core::path().'data/catalog/'.$this->layoutId.'.html';
		if(file_exists($f)) $this->text1=file_get_contents($f); else $this->text1='';
		$view=$this->layout['view1']; //Содержит поля для "списка элементов каталога"
		//Составить SQl-запрос для выборки элементов каталога
		$q='SELECT alias,title';
		$layout=array();
		$layoutData=$this->layout['data'];
		foreach($view as $item) {
			$id=$item[0];
			$l=$layoutData[$id];
			if($item[1]) $layout[$id]=array('title'=>$l[0]); else $layout[$id]=array('title'=>false);
			$layout[$id]['type']=$l[1];
			$q.=','.$id;
		}
		$q.=' FROM catalog_'.$this->layoutId;
		//если в $_GET есть параметры для поиска...
		$where='';
		$db=core::db();
		foreach($_GET as $key=>$value) {
			if($key!='title' && !isset($layoutData[$key])) continue;
			if($layout[$key]['type']=='integer') $value=explode('-',$value);
			if(!is_array($value)) {
				if($where) $where.=' AND ';
				if($key=='title') $where.='title LIKE '.$db->escape('%'.$value.'%');
				else $where.=$key.'='.$db->escape($value);
			} else {
				$v1=(float)$value[0];
				$v2=(float)$value[1];
				if(!$v1 && !$v2) continue;
				if($where) $where.=' AND ';
				if($v1 && $v2) $where.=$key.' BETWEEN '.$v1.' AND '.$v2;
				elseif($v1) $where.=$key.'>'.$v1;
				elseif($v2) $where.=$key.'<'.$v2;
			}
		}
		if($where) $q.=' WHERE '.$where;
		if($this->layout['sort']) $q.=' ORDER BY '.$this->layout['sort'];
		$db->query($q,$this->layout['onPage']);
		$isLink=(bool)$this->layout['view2']; //А может быть детальной информации для элементов каталога вообще не предусмотрено?
		$this->onPage=$this->layout['onPage'];
		$cnt=count($view);
		$ids=array_keys($layout);
		//загрузка обработанных элементов в $this->data для последующего рендера в представлении
		$this->data=array();
		while($item=$db->fetchAssoc()) {
			$data=array('alias'=>$item['alias'],'title'=>$item['title']);
			$data['field']=array();
			foreach($ids as $id) {
				if($layout[$id]['type']=='image') {
					$v=core::url().'public/catalog/'.$item[$id];
				} else $v=$item[$id];
				$data['field'][$id]=array('value'=>$v,'layout'=>$layout[$id]);
			}
			if($isLink) $data['link']=core::link('catalog/'.$this->layoutId.'/'.$item['alias']); else $data['link']=null;
			$this->data[]=$data;
		}
		unset($this->layout);
		$this->foundRows=$db->foundRows();
		$this->pageTitle=$this->metaTitle='Каталог';
		return 'List';
	}

	public function adminIndexLink() {
		return array(
			array('catalog.layout','?controller=catalog&action=layoutData&lid='.$this->layoutId,'field','Управление полями каталога'),
			array('catalog.layout','?controller=catalog&action=layoutView&lid='.$this->layoutId.'&view=view1','layout','Макет списка'),
			array('catalog.layout','?controller=catalog&action=layoutView&lid='.$this->layoutId.'&view=view2','layout','Макет записи'),
			array('catalog.item','?controller=catalog&action=text&lid='.$this->layoutId,'edit','Редактировать статью'),
			array('catalog.item','?controller=catalog&action=item&lid='.$this->layoutId,'new','Добавить элемент')
		);
	}

	/* Детальная информация об элементе каталога */
	public function actionView() {
		$view=$this->layout['view2']; //Содержит перечень полей для страницы элемента каталога
		//Загрузка данных об элементе каталога в $data
		$q='SELECT id,title,metaTitle,metaKeyword,metaDescription';
		for($i=0,$cnt=count($view);$i<$cnt;$i++) $q.=','.$view[$i][0];
		$db=core::db();
		$q.=' FROM catalog_'.$this->layoutId.' WHERE alias='.$db->escape($this->url[2]);
		$data=$db->fetchArrayOnceAssoc($q);
		if(!$data) core::error404();
		//Обработать данные в $data и загрузить подготовленные данные в $this->data для передачи представлению
		$this->data=array();
		foreach($view as $field) { //перебрать все поля
			$id=$field[0];
			if($field[1]) $field=array('title'=>$this->layout['data'][$id][0]); else $field=array('title'=>false);
			$field['type']=$this->layout['data'][$id][1];
			$field['value']=$data[$id];
			if($field['type']=='image') $field['value']=core::url().'public/catalog/'.$field['value'];
			elseif($field['type']=='gallery') {
				$v=$field['value'];
				if($v) {
					$v=explode('|',$v);
					for($i=0,$cnt=count($v);$i<$cnt;$i++) $v[$i]=core::url().'public/catalog/'.$v[$i];
				} else $v=array();
			}
			$this->data[$id]=$field;
		}
		unset($this->layout);
		$this->id=$data['id'];
		$this->pageTitle=$data['title'];
		if($data['metaTitle']) $this->metaTitle=$data['metaTitle']; else $this->metaTitle=$this->pageTitle;
		$this->metaKeyword=$data['metaKeyword'];
		$this->metaDescription=$data['metaDescription'];
		return 'View';
	}

	public function breadcrumbView() {
		return array('<a href="'.core::link('catalog/'.$this->layoutId).'">Каталог</a>');
	}

	public function adminViewLink() {
		return array(
			array('catalog.layout','?controller=catalog&action=layoutView&view=view2&lid='.$this->layoutId,'layout','Макет записи'),
			array('catalog.edit','?controller=catalog&action=item&lid='.$this->layoutId.'&id='.$this->id,'edit','Изменить элемент'),
			array('catalog.edit','?controller=catalog&action=delete&lid='.$this->layoutId.'&id='.$this->id,'delete','Удалить элемент','Удалить','if(!confirm(\'Подтвердите удаление.\')) return false;')
		);
	}

}
?>