<?php
/* Слайдер. Настройки хранит в конфигурационном файле */
class sController extends controller {

	public function right() {
		return array(
			'Image'=>'slider.*',
			'Item'=>'slider.*',
			'Delete'=>'slider.*',
			'Widget'=>'slider.*'
		);
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Список элементов (изображений) слайдера */
	public function actionImage() {
		$this->button('?controller=slider&action=item&id='.$_GET['id'],'new','Добавить слайд');
		$cfg=core::config('slider-'.$_GET['id']); //Конфигурация слайда
		$data=$cfg['data'];
		$t=core::table();
		$t->rowTh('Изображение|Текст|');
		$url=core::url().'public/slider/'.$_GET['id'].'.';
		foreach($data as $i=>$item) {
			$t->text('<img src="'.$url.$item['image'].'" />',null,'style="width:120px;text-align:center;"');
			$t->text(strip_tags($item['html']));
			$t->itemDelete('?controller=slider&id='.$_GET['id'].'&index='.$i);
		}
		return $t;
	}

//	public function actionImageSubmit($data) {}

	/* Создание или изменение слайда */
	public function actionItem() {
		if(isset($_GET['index'])) { //Изменение
			$cfg=core::config('slider-'.$_GET['id']);
			$data=array('index'=>$_GET['index'],'html'=>$cfg['data'][$_GET['index']]['html']);
		} else $data=array('index'=>'','html'=>'');
		$f=core::form();
		$f->hidden('id',$_GET['id']);
		$f->hidden('index',$_GET['index']);
		$f->file('image','Изображение');
		$f->editor('html','Текст',$data['html']);
		$f->submit();
		return $f;
	}

	public function actionItemSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config('slider-'.$data['id']);
		$list=$cfg->data; //Список слайдов
		if($data['index']==='') $index=count($list); else $index=(int)$data['index'];
		if(!isset($list[$index])) $list[$index]=array('image'=>'','html'=>'');
		//Если загружен файл изображения
		if($data['image']['size']) {
			$ext=substr($data['image']['name'],strrpos($data['image']['name'],'.')+1);
			copy($data['image']['tmpName'],core::path().'public/slider/'.$data['id'].'.'.$index.'.'.$ext);
			$list[$index]['image']=$index.'.'.$ext;
		}
		$list[$index]['html']=$data['html'];
		$cfg->data=$list;
		$cfg->save('slider-'.$data['id']);
		core::redirect('?controller=slider&action=image&id='.$data['id']);
	}

	/* Удаление слайда */
	public function actionDelete() {
		core::import('admin/core/config');
		$cfg=new config('slider-'.$_GET['id']); //Конфигурация
		$data=$cfg->data;
		$f=$data[$_GET['index']][0];
		if($f) { //Если есть изображение, то удалить его
			$f=core::path().'public/slider/'.$_GET['id'].'.'.$f;
			if(file_exists($f)) unlink($f);
		}
		unset($data[$_GET['index']]);
		$cfg->data=$data;
		$cfg->save('slider-'.$_GET['id']);
		core::redirect('?controller=slider&action=image&id='.$_GET['id']);
	}

/* ----------------------------------------------------------------------------------- */

/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Слайдер
	Параметры: string $view - имя файла MVC-представления; int $id - ИД слайдера */
	public function actionWidget($data) {
		if(!$data) {
			$data['view']=null;
			$data['id']='';
		}
		$f=core::form();
		$f->hidden('id',$data['id']);
		$f->select('view','Макет',array(array('NoOneFreeHorizontal','без превью горизонтальная прокрутка')),$data['view']);
		$f->submit('Продолжить');
		return $f;
	}

	public function actionWidgetSubmit($data) {
		core::import('admin/core/config');
		if($data['id']) $cfg=new config('slider-'.$data['id']);
		else {
			$data['id']=time();
			$cfg=new config();
			$cfg->data=array();
		}
		$cfg->save('slider-'.$data['id']);
		return array('id'=>$data['id'],'view'=>$data['view']);
	}

/* ----------------------------------------------------------------------------------- */

}
?>