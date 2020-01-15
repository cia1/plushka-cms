<?php
namespace plushka\admin\controller;
use plushka\admin\core\Controller;
use plushka\admin\core\plushka;
use plushka\admin\core\Config;

/* Универсальный каталог */
class CatalogController extends Controller {

	public function right() {
		return array(
			'layoutData'=>'catalog.layout',
			'layoutDataItem'=>'catalog.layout',
			'layoutDataDelete'=>'catalog.layout',
			'layoutView'=>'catalog.layout',
			'text'=>'catalog.content',
			'item'=>'catalog.content',
			'galleryDelete'=>'catalog.content',
			'delete'=>'catalog.content',
			'field'=>'*',
			'widgetSearch'=>'*',
			'menuCatalog'=>'*'
		);
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Настройка полей каталога */
	public function actionLayoutData() {
		$this->button('catalog/layoutDataItem?lid='.$_GET['lid'],'new','Добавить поле');
		$layout=plushka::config('catalogLayout/'.(int)$_GET['lid']); //конфигурация каталога
		//Сформировать таблицу, в которой перечислены все существующие поля
		$t=plushka::table();
		$t->rowTh('#|Заголовок|Тип|');
		$t->row('<tr><td style="color:gray;width:120px;">title</td><td style="color:gray">Заголовок/название</td><td style="color:gray;">специальный</td><td></td></tr>');
		$t->row('<tr><td style="color:gray">alias</td><td style="color:gray">Псевдоним</td><td style="color:gray;">специальный</td><td></td></tr>');
		$t->row('<tr><td style="color:gray">metaTitle</td><td style="color:gray">meta Заголовок</td><td style="color:gray;">специальный</td><td></td></tr>');
		$t->row('<tr><td style="color:gray">metaKeyword</td><td style="color:gray">meta Ключевые слова</td><td style="color:gray;">специальный</td><td></td></tr>');
		$t->row('<tr><td style="color:gray">metaDescription</td><td style="color:gray">meta Описание</td><td style="color:gray;">специальный</td><td></td></tr>');
		foreach($layout['data'] as $id=>$item) {
			$t->text($id);
			$t->text($item[0]);
			$t->text(self::_typeDescription($item[1])); //текстовое описание типа поля
			$t->itemDelete('lid='.$_GET['lid'].'&id='.$id,'layoutData');
		}
		$this->cite='Здесь представлен список всех полей записи. Серым цветом выделены системные поля, их удалить или изменить нельзя. Порядок полей задаётся на страницах &laquo;<a href="'.plushka::link('admin/catalog/layoutView?lid='.$_GET['lid'].'&view=view1').'">Макет списка</a>&raquo; и &laquo;<a href="'.plushka::link('admin/catalog/layoutView?lid='.$_GET['lid'].'&view=view2').'">Макет записи</a>&raquo;';
		return $t;
	}

	/* Создание/изменение поля */
	public function actionLayoutDataItem() {
		if(isset($_GET['lid'])) $layoutId=(int)$_GET['lid']; else $layoutId=(int)$_POST['catalog']['lid'];
		$cfg=plushka::config('catalogLayout/'.$layoutId);
		if(isset($_GET['id'])) { //изменение
			$data=$cfg['data'][$_GET['id']];
			$id=$_GET['id'];
		} else { //создание
			$data=array('','integer');
			$id=0;
			while(true) {
				if(!isset($cfg['data']['fld'.(++$id)])) break;
			}
			$id='fld'.$id;
		}
		$f=plushka::form();
		$f->hidden('layoutId',$layoutId);
		if($data[1]=='image' || $data[1]=='gallery') {
			if($data['width'][0]=='<') {
				$data['widthSize']=substr($data['width'],1);
				$data['width']='<';
			} elseif($data['width']) {
				$data['widthSize']=$data['width'];
				$data['width']='=';
			} else $data['widthSize']='';
			if($data['height'][0]=='<') {
				$data['heightSize']=substr($data['height'],1);
				$data['height']='<';
			} elseif($data['height']) {
				$data['heightSize']=$data['height'];
				$data['height']='=';
			} else $data['heightSize']='';
			//миниатюра
			if($data[1]=='gallery' && array_key_exists('thumbWidth',$data)) {
				if($data['thumbWidth'][0]=='<') {
					$data['thumbWidthSize']=substr($data['thumbWidth'],1);
					$data['thumbWidth']='<';
				} elseif($data['thumbWidth']) {
					$data['thumbWidthSize']=$data['thumbWidth'];
					$data['thumbWidth']='=';
				} else $data['thumbWidthSize']='';
				if($data['thumbHeight'][0]=='<') {
					$data['thumbHeightSize']=substr($data['thumbHeight'],1);
					$data['thumbHeight']='<';
				} elseif($data['thumbHeight']) {
					$data['thumbHeightSize']=$data['thumbHeight'];
					$data['thumbHeight']='=';
				} else $data['thumbHeightSize']='';
				$data['thumbnail']=true;
			}	else {
				$data['thumbWidth']=$data['thumbHeight']=$data['thumbWidthSize']=$data['thumbHeightSize']='';
				$data['thumbnail']=false;
			}
		} else $data['width']=$data['height']=$data['widthSize']=$data['heightSize']=$data['thumbWidth']=$data['thumbHeight']=$data['thumbWidthSize']=$data['thumbHeightSize']=$data['thumbnail']=null;
		if($data[1]!='select' && $data[1]!='list') $data[2]='';
		$f->hidden('oldId',$id);
		$f->text('id','Идентификатор',$id);
		$f->text('title','Заголовок',$data[0],null,'asdf');
		$f->select('type','Тип данных',array(array('integer','целое число'),array('float','дробное число'),array('string','строка'),array('text','текст'),array('boolean','да/нет'),array('date','дата'),array('image','изображение'),array('list','список'),array('gallery','галерея')),$data[1],null,'id="catalogType"');

		$f->select('width','Ширина',array(array('','исходная/пропорциональная'),array('=','указать размер'),array('<','указать максимальную')),$data['width'],null,' id="width"');
		$f->text('widthSize','ширина (пикс.)',$data['widthSize']);
		$f->select('height','Высота',array(array('','исходная/пропорциональная'),array('=','указать размер'),array('<','указать максимальную')),$data['height'],null,' id="height"');
		$f->text('heightSize','высота (пикс.)',$data['heightSize']);
		$f->checkbox('thumbnail','Миниатюры изображений',$data['thumbnail'],'id="thumbnail"');
		$f->select('thumbWidth','Ширина миниатюры',array(array('','исходная/пропорциональная'),array('=','указать размер'),array('<','указать максимальную')),$data['thumbWidth'],null,' id="thumbWidth"');
		$f->text('thumbWidthSize','ширина миниатюры (пикс.)',$data['thumbWidthSize']);
		$f->select('thumbHeight','Высота миниатюры',array(array('','исходная/пропорциональная'),array('=','указать размер'),array('<','указать максимальную')),$data['thumbHeight'],null,' id="thumbHeight"');
		$f->text('thumbHeightSize','высота миниатюры (пикс.)',$data['thumbHeightSize']);

		$f->textarea('value','Значение',$data[2]);
		$f->submit();
		$this->js('admin/catalog');
		$this->cite='<b>Идентификатор</b> - это имя поля в таблице базы данных, а также имя переменной в представлении. Меняйте это значение только в том случае, если уверены, что это необходимо.';
		return $f;
	}

	public function actionLayoutDataItemSubmit($data) {
		$layoutId=(int)$data['layoutId'];
		$cfg=new Config('catalogLayout/'.$layoutId);
		$layoutData=$cfg->data;
		if(!$data['title']) {
			plushka::error('Поле &laquo;заголовок&raquo; не может быть пустым');
			return false;
		}
		//Проверить уникальность и валидность идентификатора, если он был изменён
		$id=$data['id'];
		$oldId=$data['oldId'];
		if($id!=$oldId) {
			$id=trim($id);
			if(!preg_match('/^[a-zA-Z0-9_]+$/',$id)) {
				plushka::error('Поле &laquoидентификатор&raquo; может содержать только латинские буквы, цифры и знак "_"');
				return false;
			}
			$_id=strtoupper($id);
			if(in_array($_id,array('ID','ALIAS','TITLE','METATITLE','METAKEYWORD','METADESCRIPTION','SELECT','UPDATE','ALTER','DROP','ASC','DESC','AS','LIMIT','NULL','IN','DISTINCT','FROM','BETWEEN','JOIN','LEFT','RIGHT','WHERE','ORDER','BY','HAVING','GROUP'))) {
				plushka::error('Идентификатор не может быть '.$_id);
				return false;
			}
			if(isset($cfg->data[$id])) {
				plushka::error('Поле с таким идентификатором уже существует');
				return false;
			}
		}
		if(isset($cfg->data[$oldId])) $isNew=false; else $isNew=true;
		//Обновить структуру таблицы catalog_ИД - изменить тип поля
		$db=plushka::db();
		if($isNew) {
			$db->alterAdd('catalog_'.$layoutId,$id,self::_type($data['type']));
		} else $db->alterChange('catalog_'.$layoutId,$oldId,$id,self::_type($data['type']));
		if(plushka::error()) return false;
		//Обновить данные о поле в конфигурации каталога
		$fld=$cfg->data;
		$d=array($data['title'],$data['type']);
		if($data['type']=='list') $d[]=str_replace("\r",'',$data['value']);
		elseif($data['type']=='image' || $data['type']=='gallery') {
			if(!$data['width']) $d['width']=null; elseif($data['width']=='=') $d['width']=(int)$data['widthSize']; else $d['width']='<'.(int)$data['widthSize'];
			if(!$data['height']) $d['height']=null; elseif($data['height']=='=') $d['height']=(int)$data['heightSize']; else $d['height']='<'.(int)$data['heightSize'];
			if($data['type']=='gallery' && isset($data['thumbnail'])) { //если галерея с миниатюрами
				if(!$data['thumbWidth']) $d['thumbWidth']=null; elseif($data['thumbWidth']=='=') $d['thumbWidth']=(int)$data['thumbWidthSize']; else $d['thumbWidth']='<'.(int)$data['thumbWidthSize'];
				if(!$data['thumbHeight']) $d['thumbHeight']=null; elseif($data['thumbHeight']=='=') $d['thumbHeight']=(int)$data['thumbHeightSize']; else $d['thumbHeight']='<'.(int)$data['thumbHeightSize'];
			}
		}
		//Если у поля был изменён идентификатор, то изменить все связанные объекты
		if(!$isNew && $id!=$oldId) {
			unset($fld[$oldId]);
			//макет списка
			$view=$cfg->view1;
			foreach($view as &$item) {
				if($item[0]==$oldId) $item[0]=$id;
			}
			$cfg->view1=$view;
			//макет записи
			$view=$cfg->view2;
			foreach($view as &$item) {
				if($item[0]==$oldId) $item[0]=$id;
			}
			$cfg->view2=$view;
		}
		$fld[$id]=$d;
		$cfg->data=$fld;
		$cfg->save('catalogLayout/'.$layoutId);
		unset($cfg);
		//Если у поля был изменён идентификатор, то изменить ссылки на это поле в виджетах (в будущем это лучше вынести в обработчик собыия)
		if(!$isNew && $id!=$oldId) {
			$widget=$db->fetchArrayAssoc('SELECT id,data FROM widget WHERE name='.$db->escape('catalogSearch'));
			foreach($widget as $item) {
				$data=unserialize($item['data']);
				$change=false;
				foreach($data['fld'] as $_id=>$field) {
					if($_id!=$oldId) continue;
					$change=true;
					unset($data['fld'][$_id]);
					$data['fld'][$id]=$field;
				}
				if(!$change) continue;
				$db->query('UPDATE widget SET data='.$db->escape(serialize($data)).' WHERE id='.$item['id']);
			}
		}

		plushka::redirect('catalog/layoutData?lid='.$layoutId);
	}

	/* Удаление поля (это не совсем верно, т.к. нужно ещё удалять изображения для image и gallery */
	public function actionLayoutDataDelete() {
		//Удалить поле из конфигурации (список всех полей)
		$cfg=new Config('catalogLayout/'.$_GET['lid']);
		$data=$cfg->data;
		unset($data[$_GET['id']]);
		$cfg->data=$data;
		//Удалить поле из макета списка
		$view=$cfg->view1;
		for($i=0,$cnt=count($view);$i<$cnt;$i++) {
			if($view[$i][0]==$_GET['id']) {
				unset($view[$i]);
				break;
			}
		}
		$cfg->view1=$view;
		//Удалить поле из макета элемента
		$view=$cfg->view2;
		for($i=0,$cnt=count($view);$i<$cnt;$i++) {
			if($view[$i][0]==$_GET['id']) {
				unset($view[$i]);
				break;
			}
		}
		$cfg->view2=$view;
		$cfg->save('catalogLayout/'.$_GET['lid']);
		//Удалить поле из таблицы catalog_ИД
		$db=plushka::db();
		$db->alterDrop('catalog_'.$_GET['lid'],$_GET['id']);
		plushka::redirect('catalog/layoutData?lid='.$_GET['lid']);
	}

	/* Настройка макета списка или макета элемента */
	public function actionLayoutView() {
		$cfg=plushka::config('catalogLayout/'.(int)$_GET['lid']); //конфигурация каталога
		$this->layout=array();
		$tmpIndex=array();
		//Загрузить в $this->layout отмеченные поля
		foreach($cfg[$_GET['view']] as $item) {
			$this->layout[]=array('index'=>$item[0],'title'=>$cfg['data'][$item[0]][0],'showTitle'=>$item[1],'enabled'=>true);
			$tmpIndex[]=$item[0];
		}
		//Добавить к $this->layout неотмеченные в макете поля
		foreach($cfg['data'] as $id=>$item) {
			if(in_array($id,$tmpIndex)) continue;
			$this->layout[]=array('index'=>$id,'title'=>$item[0],'showTitle'=>true,'enabled'=>false);
		}
		//Если это макет списка, то также добавить к форме сортировку и количество элементов на странице
		if($_GET['view']=='view1') {
			$this->onPage=$cfg['onPage'];
			$this->sort=array();
			foreach($cfg['data'] as $id=>$item) {
				$this->sortList[]=array($id,$item[0].' АБВ');
				$this->sortList[]=array($id.' DESC',$item[0].' ВБА');
			}
			$this->sort=$cfg['sort'];
		}
		$this->cite='Отметьте флажок "включён" чтобы опубликовать на сайте соответствующий блок данных.';
		$this->css('admin/catalog');
		return 'LayoutView';
	}

	public function actionLayoutViewSubmit($data) {
		$cfg=new Config('catalogLayout/'.$_GET['lid']);
		$view=array();
		foreach($data['index'] as $index) {
			if(!isset($data['enabled'][$index])) continue;
			$view[]=array($index,(isset($data['showTitle'][$index]) ? true : false));
		}
		$cfg->$data['view']=$view;
		if(isset($data['onPage'])) {
			$cfg->onPage=(int)$data['onPage'];
			$cfg->sort=$data['sort'];
		}
		$cfg->save('catalogLayout/'.$_GET['lid']);
		plushka::success('Макет сохранён');
		plushka::redirect('catalog/layoutView?lid='.$_GET['lid'].'&view='.$data['view']);
	}

	/* Редактирование вступительного текста на странице со списком элементов каталога */
	public function actionText() {
		$text1=plushka::path().'data/catalog/'.$_GET['lid'].'.html'; //текст хранится в этом файле
		if(file_exists($text1)) $text1=file_get_contents($text1); else $text1='';
		$f=plushka::form();
		$f->hidden('lid',$_GET['lid']);
		$f->editor('text1','Введение',$text1);
		$f->submit('Сохранить');
		return $f;
	}

	public function actionTextSubmit($data) {
		$f=plushka::path().'data/catalog/'.$data['lid'].'.html';
		if(!$data['text1']) unlink($f);
		else {
			$f=fopen($f,'w');
			fwrite($f,$data['text1']);
			fclose($f);
		}
		plushka::success('Изменения сохранены');
		plushka::redirect('catalog?lid='.$data['lid']);
	}

	/* Создание или изменение элемента каталога */
	public function actionItem() {
		if(isset($_GET['lid'])) $lid=$_GET['lid']; else $lid=$_POST['catalog']['lid'];
		if(isset($_GET['id'])) { //редактирование элемента - загрузить данные
			$db=plushka::db();
			$data=$db->fetchArrayOnceAssoc('SELECT * FROM catalog_'.$lid.' WHERE id='.$_GET['id']);
		} else $data=array('id'=>null,'title'=>'','alias'=>null,'metaTitle'=>'','metaKeyword'=>'','metaDescription'=>'');
		//Настоить форму
		$cfg=plushka::config('catalogLayout/'.$lid);
		$f=plushka::form();
		$f->hidden('lid',$lid);
		$f->hidden('id',$data['id']);
		$f->text('title','Заголовок/название',$data['title']);
		$f->text('alias','Псевдоним',$data['alias']);
		foreach($cfg['data'] as $id=>$item) { //тип поля
			if(!isset($data[$id])) $data[$id]=null;
			switch($item[1]) {
			case 'integer': case 'float': case 'string':
				$f->text($id,$item[0],(isset($data[$id]) ? $data[$id] : ''));
				break;
			case 'boolean':
				$f->checkbox($id,$item[0],(isset($data[$id]) ? $data[$id] : ''));
				break;
			case 'text':
				if(!isset($data[$id])) $data[$id]='';
				$f->editor($id,$item[0],$data[$id]);
				break;
			case 'date':
				$f->date($id,$item[0],(isset($data[$id]) ? $data[$id] : ''));
				break;
			case 'image':
				$f->file($id,$item[0]);
				$f->html('<dt style="height:102px;"></dt><dd style="height:102px;"><img src="'.plushka::url().'public/catalog/'.$data[$id].'" style="max-height:100px;" /></dd>');
				$data[$id]=false;
				break;
			case 'list':
				$item[2]=explode("\n",$item[2]);
				$d=array();
				foreach($item[2] as $value) $d[]=array($value,$value);
				$f->select($id,$item[0],$d,(isset($data[$id]) ? $data[$id] : ''));
				break;
			case 'gallery':
				$f->file($id,$item[0],true);
				if($data[$id]) $gallery=explode('|',$data[$id]); else $gallery=array();
				$html='';
				foreach($gallery as $i=>$g) {
					$html.='<a href="'.plushka::link('admin/catalog/galleryDelete?lid='.$lid.'&id='.$data['id'].'&fld='.$id.'&index='.$i).'" title="Удалить"><img src="'.plushka::url().'public/catalog/'.$g.'" style="width:40px;" /></a>';
				}
				$f->html('<dd class="gallery"s>'.$html.'</dd>');
				break;
			}
		}
		$f->text('metaTitle','meta Заголовок',$data['metaTitle']);
		$f->text('metaKeyword','meta Ключевые слова',$data['metaKeyword']);
		$f->text('metaDescription','meta Описание',$data['metaDescription']);
		$f->submit();
		$this->css('admin/catalog');
		$this->js('admin/catalog');
		return $f;
	}

	public function actionItemSubmit($data) {
		$oldId=(int)$data['id'];
		$cfg=plushka::config('catalogLayout/'.$data['lid']);
		$m=plushka::model('catalog_'.$data['lid']);
		$m->set($data);
		$validate=array(
			'id'=>array('primary'),
			'title'=>array('string','заголовок/название',true),
			'alias'=>array('latin','псевдоним',true),
			'metaTitle'=>array('string'),
			'metaKeyword'=>array('string'),
			'metaDescription'=>array('string')
		);
		$uploadImage=$uploadGallery=array(); //тут будет информация о загруженных изображениях, заполняется в процессе перебора всех полей
		$db=plushka::db();
		$old=''; //SQL-запрос на выборку старых данных для удаления файлов изображений, если они будут заменены
		//Теперь перебрать все поля и настроить валидатор
		foreach($cfg['data'] as $id=>$item) {
			switch($item[1]) {
			case 'integer':
				$validate[$id]=array('integer',$item[0]);
				break;
			case 'float':
				$validate[$id]=array('float',$item[0]);
				break;
			case 'string': case 'list':
				$validate[$id]=array('string',$item[0]);
				break;
			case 'text':
				$validate[$id]=array('html',$item[0]);
				break;
			case 'boolean':
				$validate[$id]=array('boolean');
				break;
			case 'date':
				$validate[$id]=array('date',$item[0]);
				break;
			case 'image':
				//Проверить тип загружаемого файла
				$img=$data[$id];
				if(!$img['size']) continue;
				$ext=strtolower(substr($img['name'],strrpos($img['name'],'.')+1));
				if($ext!='gif' && $ext!='jpg' && $ext!='jpeg' && $ext!='png') {
					plushka::error('Файл в поле &laquo;'.$cfg['data'][$id][0].'&raquo; должен быть изображением');
					return false;
				}
				$uploadImage[]=$id;
				if($old) $old.=',';
				$old.=$id;
				break;
			case 'gallery':
				//Проверить тип загружаемых файлов
				$img=$data[$id];
				if(!$img[0]['size']) $img=array();
				for($i=0,$cnt=count($img);$i<$cnt;$i++) {
					$ext=strtolower(substr($img[$i]['name'],strrpos($img[$i]['name'],'.')+1));
					if($ext!='gif' && $ext!='jpg' && $ext!='jpeg' && $ext!='png') {
						plushka::error('Файлы в поле &laquo;'.$cfg['data'][$id][0].'&raquo; должны быть изображениями');
						return false;
					}
					if(!in_array($id,$uploadGallery)) $uploadGallery[]=$id;
				}
				if($old) $old.=','.$id; else $old=$id;
			 	break;
			}
		}
		if(!$m->save($validate)) return false;
		//Запись сохранена в БД. Теперь загрузить новые изображения и удалить старые (для полей типа "image" и "gallery")
		$q='';
		if($old) $old=$db->fetchArrayOnceAssoc('SELECT '.$old.' FROM catalog_'.$data['lid'].' WHERE id='.$oldId);
		foreach($uploadImage as $id) {
			if($oldId) { //удалить сначала старый файл изображения
				$f=plushka::path().'public/catalog/'.$oldImage[$id];
				if(file_exists($f)) unlink($f);
			}
			$p=new \plushka\core\Picture($data[$id]);
			$p->resize($cfg['data'][$id]['width'],$cfg['data'][$id]['height']);
			$f=$data['lid'].'.'.$m->id.'-'.$id;
			$f=$p->save('public/catalog/'.$f);
			if($q) $q.=',';
			$q.=$id.'='.$db->escape($f);
		}
		foreach($uploadGallery as $id) {
			$t=$old[$id];
			if(!$t) $index=0; else {
				$t=explode('|',$t);
				$index=0;
				foreach($t as $_index) {
					$i=(int)substr($_index,strrpos($_index,'-')+1);
					if($i>$index) $index=$i;
				}
			}
			unset($t);
			$q0=$old[$id];
			if(isset($cfg['data'][$id]['thumbWidth'])) $thumbnail=true; else $thumbnail=false;
			for($i=0,$cnt=count($data[$id]);$i<$cnt;$i++) {
				$index++;
				$p=new \plushka\core\Picture($data[$id][$i]);
				$p->resize($cfg['data'][$id]['width'],$cfg['data'][$id]['height']);
				$f=$data['lid'].'.'.$m->id.'-'.$id.'-'.$index;
				$f=$p->save('public/catalog/'.$f);
				if($q0) $q0.='|'.$f; else $q0=$f;
				//миниатюра
				if($thumbnail) {
					$p->resize($cfg['data'][$id]['thumbWidth'],$cfg['data'][$id]['thumbHeight']);
					$f='_'.$data['lid'].'.'.$m->id.'-'.$id.'-'.$index;
					$p->save('public/catalog/'.$f);
				}
			}
			if($q) $q.=',';
			$q.=$id.'='.$db->escape($q0);
		}
		if($q) {
			$q='UPDATE catalog_'.$data['lid'].' SET '.$q.' WHERE id='.$m->id;
			$db->query($q);
		}
		plushka::hook('modify','catalog/'.$data['lid'].'/'.$m->alias,true); //Обновить дату изменения страницы
		plushka::success('Изменения сохранены');
		plushka::redirect('catalog/item?lid='.$data['lid']);
	}

	/* Удаление изображения из галереи элемента каталога */
	public function actionGalleryDelete() {
		$db=plushka::db();
		$lid=(int)$_GET['lid']; //ИД каталога
		$id=(int)$_GET['id']; //ИД записи
		$fld=$_GET['fld']; //Имя поля галереи
		$index=(int)$_GET['index']; //Номер фотографии в галереи
		$data=$db->fetchArrayOnce('SELECT '.$fld.' FROM catalog_'.$lid.' WHERE id='.$id);
		if(!$data) return;
		$data=explode('|',$data[0]);
		$f=plushka::path().'public/catalog/'.$data[$index];
		if(file_exists($f)) unlink($f);
		$f=plushka::path().'public/catalog/_'.$data[$index];
		if(file_exists($f)) unlink($f);
		unset($data[$index]);
		$db->query('UPDATE catalog_'.$lid.' SET '.$fld.'='.$db->escape(implode('|',$data)).' WHERE id='.$id);
		die('OK');
	}

	/* Удаление элемента каталога */
	public function actionDelete() {
		$lid=(int)$_GET['lid'];
		$id=(int)$_GET['id'];
		//Нужно удалить все изображения элемента каталога
		$db=plushka::db();
		$cfg=plushka::config('catalogLayout/'.$lid); //конфигурация каталога
		$q='';
		//В $fld выбрать все поля типа "image" и "gallery"
		$fld=array();
		foreach($cfg['data'] as $id0=>$item) {
			if($item[1]=='image' || $item[1]=='gallery') {
				if($q) $q.=',';
				$q.=$id0;
				$fld[]=$id0;
			}
		}
		//Если поля с изображениями вообще есть, то удалить файлы изображений
		if($q) {
			$img=$db->fetchArrayOnceAssoc('SELECT '.$q.' FROM catalog_'.$lid.' WHERE id='.$id);
			foreach($fld as $id0) {
				$item=$img[$id0];
				$type=$cfg['data'][$id0][1];
				if($type=='image') {
					$item=plushka::path().'public/catalog/'.$item;
					if(file_exists($item)) unlink($item);
				} else {
					if(!$item) continue;
					$item=explode('|',$item);
					$path=plushka::path().'public/catalog/';
					foreach($item as $f) {
						$f=$path.$f;
						if(file_exists($f)) unlink($f);
						$f=$path.'_'.$f;
						if(file_exists($f)) unlink($f);
					}
				}
			}
		}
		$alias=$db->fetchValue('SELECT alias FROM catalog_'.$lid.' WHERE id='.$id);
		$db->query('DELETE FROM catalog_'.$lid.' WHERE id='.$id);
		plushka::hook('pageDelete','catalog/'.$lid.'/'.$alias,true);
		plushka::success('Элемент каталога удалён');
		plushka::redirect('catalog?lid='.$lid);
	}

	/* Выводит HTML-форму со списком полей для настройки фильтра.
	Вызывается AJAX-запросом при настройке виджета поиск (фильтр). Вероятно это нужно было сделать в submit-действии. */
	public function actionField() {
		$fld=json_decode($_GET['fld'],true); //Настройки и данные полей
		$cfg=plushka::config('catalogLayout/'.(int)$_GET['id']); //конфигурация каталога
		$this->data=array(); //Тут будут подготовленные данные для формирования HTML-формы
		//Просто формирует в $this->data удобочитаемый массив информации
		foreach($cfg['data'] as $i=>$field) {
			if($field[1]=='text' || $field[1]=='image' || $field[1]=='gallery') continue;
			$item=array('index'=>$i,'type'=>$field[1],'title'=>$field[0],'description'=>self::_typeHTML($field[1]),'checked'=>isset($fld[$i]));
			switch($field[1]) {
			case 'integer': case 'float': case 'date':
				if(isset($fld[$i])) {
					$item['min']=$fld[$i]['min'];
					$item['max']=$fld[$i]['max'];
					$item['step']=$fld[$i]['step'];
				} else {
					$item['min']='';
					$item['max']='';
					$item['step']='';
				}
			case 'integer': case 'float':
				$item['range']=isset($fld[$i]['range']);
				break;
			}
			$this->data[]=$item;
		}
		return 'FieldAjax';
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Настраиваемый поиск (фильтр) по полям каталога */
	public function actionWidgetSearch($data) {
		if(!$data) $data=array('id'=>null,'fld'=>array('title'=>false)); //новый виджет
		//Поиск в меню всех каталогов, чтобы предоставить пользователю выбор (каталог создаётся только из меню)
		$catalogList=array();
		$db=plushka::db();
		$db->query('SELECT link,title_'._LANG.' FROM menu_item WHERE link LIKE '.$db->escape('catalog/%'));
		while($item=$db->fetch()) {
			$catalogList[]=array((int)substr($item[0],strrpos($item[0],'/')+1),$item[1]);
		}
		$this->f=plushka::form();
		if(!count($catalogList)) {
			plushka::error('На сайте нет ни одного каталога.');
			return 'WidgetSearch';
		}
		$this->f->select('id','Каталог',$catalogList,$data['id']);
		$this->f->checkbox('fld][title][set','Название',$data['fld']['title']);
		$this->f->html('<div id="fieldList">Загрузка...</div>'); //В этот блок ajax'ом будет загружена форма. Нужно для того, чтобы предоставить возможность изменить каталог (а соответственно и список полей)
		$this->f->submit();
		unset($data['fld']['title']);
		$this->fld=json_encode($data['fld']); //для передачи ajax-запросу
		$this->js('admin/catalog');
		return 'WidgetSearch';
	}

	public function actionWidgetSearchSubmit($data) {
		$fld=&$data['fld'];
		foreach($fld as $key=>$item) {
			if(!isset($item['set'])) unset($fld[$key]); else {
				if(count($fld[$key])==1) $fld[$key]=true; else {
					unset($fld[$key]['set']);
					if(isset($fld[$key]['range'])) $fld[$key]['range']=true;
					if(isset($fld[$key]['min'])) $fld[$key]['min']=(float)$fld[$key]['min'];
					if(isset($fld[$key]['max'])) $fld[$key]['max']=(float)$fld[$key]['max'];
					if(isset($fld[$key]['step'])) $fld[$key]['step']=(float)$fld[$key]['step'];
				}
			}
		}
		return $data;
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- MENU ------------------------------------------------------------------- */
	/* Ссылка на каталог из меню */
	public function actionMenuCatalog() {
		$f=plushka::form();
		if(isset($_GET['link']) && $_GET['link']) { //пункт меню уже существует: данные по умолчанию
			$lid=(int)substr($_GET['link'],strpos($_GET['link'],'/')+1); //ИД каталога
			$cfg=plushka::config('catalogLayout/'.$lid); //конфигурация каталога
			$f->hidden('lid',$lid);
		} else $f->hidden('lid',0);
		$f->submit('Продолжить');
		return $f;
	}

	public function actionMenuCatalogSubmit($data) {
		if($data['lid']==0) { //новая ссылка (новый каталог)
			//Найти следующий номер каталога (ИД)
			$path=plushka::path().'config/catalogLayout/';
			$d=opendir($path);
			$index=1;
			while($f=readdir($d)) {
				$f=(int)$f;
				if($f>=$index) $index=$f+1;
			}
			//Создать таблицу в БД (для SQLite будет выполнено преобразование описаний полей MySQL)
			$db=plushka::db();
			$db->create('catalog_'.$index,array(
				'id'=>array('INT UNSIGNED NOT NULL','primary',true),
				'alias'=>'CHAR(30) NOT NULL',
				'title'=>'VARCHAR(150) NOT NULL',
				'metaTitle'=>'VARCHAR(300)',
				'metaKeyword'=>'VARCHAR(300)',
				'metaDescription'=>'VARCHAR(300)'
			));
			//Создать новую конфигурацию каталога и записать данные по умолчанию
			$cfg=new Config();
			$cfg->onPage=30;
			$cfg->data=array();
			$cfg->view1=array();
			$cfg->view2=array();
			$cfg->sort=null;
			$cfg->save('catalogLayout/'.$index);
		} else { //ссылка (каталог) уже существует
			$index=$data['lid'];
		}
		return 'catalog/'.$index;
	}

/* ----------------------------------------------------------------------------------- */

	/* Описание полей MySQL для добавления или изменения полей каталога */
	private static function _type($type) {
		switch($type) {
		case 'integer':
			return 'MEDIUMINT UNSIGNED';
		case 'float':
			return 'FLOAT';
		case 'string': case 'gallery':
			return 'VARCHAR(300) NOT NULL DEFAULT ""';
		case 'text':
			return 'MEDIUMTEXT';
		case 'boolean':
			return 'TINYINT UNSIGNED NOT NULL DEFAULT 0';
		case 'date':
			return 'INT UNSIGNED';
		case 'image':
			return 'CHAR(50)';
		case 'list':
			return 'CHAR(50)';
		}
	}

	/* Возвращает описание типа $type */
	private static function _typeDescription($type) {
		switch($type) {
		case 'integer': return 'целое число';
		case 'float': return 'дробное число';
		case 'string': return 'строка';
		case 'text': return 'текст';
		case 'boolean': return 'да/нет';
		case 'date': return 'дата';
		case 'image': return 'изображение';
		case 'list': return 'список';
		case 'gallery': return 'галерея';
		}
	}

	/* Возвращает опиание поля поиска для типа поля $type */
	private static function _typeHTML($type) {
		switch($type) {
		case 'integer': return 'список';
		case 'float': return 'список';
		case 'string': return 'текст';
		case 'boolean': return 'да/нет';
		case 'date': return 'дата';
		case 'list': return 'список';
		case 'gallery': return 'галерея';
		}
	}

}
?>
