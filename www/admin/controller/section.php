<?php
/* Управление секциями */
class scontroller extends controller {

	public function right() {
		return array(
			'index'=>'section.*',
			'up'=>'section.*',
			'down'=>'section.*',
			'delete'=>'section.*',
			'widget'=>'section.*'
		);
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Список виджетов в секции */
	public function actionIndex() {
		$this->section=$_GET['name']; //Имя секции
		$this->button('section/widget?section='.$this->section,'new','Добавить новый виджет','Создать');
		$t=core::table();
		$t->rowTh('Пользователи|Виджет|Тип||');
		$db=core::db();
		$items=$db->fetchArray('SELECT w.id,w.title_'._LANG.',t.title,s.sort,COUNT(s.widgetId) cnt,w.groupId FROM widget w LEFT JOIN section s ON s.widgetId=w.id LEFT JOIN widget_type t ON t.name=w.name WHERE w.section='.$db->escape($this->section).' GROUP BY w.id,t.title,s.sort ORDER BY s.sort');
		if($items) for($i=0,$cnt=count($items);$i<$cnt;$i++) {
			if($items[$i][4]==0) $items[$i][1].='<img src="'.core::url().'admin/public/icon/attention16.png" alt="не используется" title="Данный виджет не отображается ни на одной странице!" />';
			$t->text(($items[$i][5] ? $items[$i][5] : 'все'));
			$t->link('section/widget?id='.$items[$i][0].'&section='.$_GET['name'],$items[$i][1]);
			$t->text($items[$i][2]);
			$t->upDown('id='.$items[$i][0],$items[$i][3],$cnt);
			$t->delete('name='.$this->section.'&id='.$items[$i][0]);
		}
		unset($items);
		$this->table=$t;
		return 'Index';
	}

	protected function helpIndex() {
		return 'core/section';
	}

	/* Изменение порядка виджетов (выше) */
	public function actionUp() {
		$id=(int)$_GET['id']; //Идентификатор
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT name,sort FROM section WHERE widgetId='.$id);
		if($data[1]>=2) {
			$newSort=$data[1]-1;
			$db->query('UPDATE section SET sort='.$data[1].' WHERE name='.$db->escape($data[0]).' AND sort='.$newSort);
			$db->query('UPDATE section SET sort='.$newSort.' WHERE widgetId='.$id);
		}
		core::redirect('section?name='.$data[0]);
	}

	/* Изменение порядка виджетов (ниже) */
	public function actionDown() {
		$id=(int)$_GET['id'];
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT name,sort FROM section WHERE widgetId='.$id);
		$lastSort=$db->fetchValue('SELECT max(sort) FROM section WHERE name='.$db->escape($data[0]));
		if($data[1]<$lastSort) {
			$newSort=$data[1]+1;
			$db->query('UPDATE section SET sort='.$data[1].' WHERE name='.$db->escape($data[0]).' AND sort='.$newSort);
			$db->query('UPDATE section SET sort='.$newSort.' WHERE widgetId='.$id);
		}
		core::redirect('section?name='.$data[0]);
	}

	/* Удаление виджета */
	public function actionDelete() {
		$id=(int)$_GET['id'];
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT id,name,data FROM widget WHERE id='.$id);
		//Спровоцировать событие "widgetDelete" чтобы дать возможность разрушить зависимые данные виджета.
		//Параметры: (string) имя виджета, (int) его ИД, (mixed) настройки виджета
		if(substr($data[2],0,2)=='a:' && $data[2][strlen($data[2])-1]=='}') $data[2]=unserialize($data[2]);
		if(core::hook('widgetDelete',$data[1],$data[0],$data[2])===false) return $this->actionIndex();
		//Удалить из БД, а также "сдвинуть" виджеты в секции (изменить сортировку)
		$data=$db->fetchArrayOnce('SELECT name,sort FROM section WHERE widgetId='.$id);
		if($data) { //бывает, что виджет не опубликован ни на одной странице
			$db->query('UPDATE section SET sort=sort-1 WHERE name='.$db->escape($data[0]).' AND sort>'.$data[1]);
		}
		$db->query('DELETE FROM section WHERE widgetId='.$id);
		$db->query('DELETE FROM widget WHERE id='.$id);
		core::redirect('section?name='.$_GET['name'],'Виджет удалён');
	}

	/* Создание или изменение виджета секции */
	public function actionWidget() {
		$db=core::db();
		if(isset($_GET['id'])) { //Изменение
			$this->data=$db->fetchArrayOnceAssoc('SELECT w.id id,w.name name,w.data data,w.title_'._LANG.' title,w.cache cache,w.publicTitle publicTitle,t.controller controller,t.action action,w.section,w.groupId,w.cssClass FROM widget w INNER JOIN widget_type t ON t.name=w.name WHERE w.id='.$_GET['id']);
			$this->data['type']=array($this->data['id'],$this->data['controller'],$this->data['action']); //Нужен для загрузки (ajax) формы модуля.
			if($this->data['groupId']!==null) $this->data['groupId']=$this->data['groupId'];
			//Загрузить список страниц, на которых публикуется виджет
			$db->query('SELECT url FROM section WHERE widgetId='.$this->data['id']);
			$url=array();
			while($item=$db->fetch()) $url[]=$item[0];
		} else { //Создание
			$this->data=array('id'=>null,'groupId'=>null,'name'=>null,'title'=>'','cache'=>0,'data'=>null,'publicTitle'=>false,'cssClass'=>'');
			//Тип виджета может быть определён ранее - тогда в представлении нужно "подгрузить" (AJAX) соответствующую форму модуля
			//Иначе загрузить список типов, чтобы предоставить выбор
			if(isset($_GET['type'])) {
				$this->data['name']=$_GET['type'];
				$data=$db->fetchArrayOnce('SELECT controller,action FROM widget_type WHERE name='.$db->escape($this->data['name']));
				$this->data['controller']=$data[0];
				$this->data['action']=$data[1];
			} else $this->type=$db->fetchArrayAssoc('SELECT * FROM widget_type ORDER BY controller,name');
			$this->data['section']=$_GET['section'];
			$url=array();
		}
		//Постройка древовидного массива, содержащего все пункты всех меню. Нужен чтобы вывести чекбоксы для отметки страниц.
		$db->query('SELECT i.id,i.parentId,m.title,i.title_'._LANG.',i.link FROM menu_item i LEFT JOIN menu m ON m.id=i.menuId ORDER BY i.menuId,i.parentId,i.sort');
		$this->pageMenu=array(0=>array('title'=>null,'child'=>array(),'parent'=>null));
		while($item=$db->fetch()) $this->pageMenu[$item[0]]=array('menuTitle'=>$item[2],'title'=>$item[3],'parent'=>$item[1],'child'=>array(),'link'=>$item[4]);
		foreach($this->pageMenu as $id=>$null) {
			if(!$id) continue;
			$item=&$this->pageMenu[$id];
			$this->pageMenu[$item['parent']]['child'][]=&$item;
		}
		$this->userGroupList=$db->fetchArray('SELECT id,name FROM user_group ORDER BY id');
		array_unshift($this->userGroupList,array('0','не авторизованные'));
		$this->pageMenu=$this->pageMenu[0]['child']; //Теперь уже лишний "обвес" не нужен
		$this->pageOther=self::_pageOther($url,$this->pageMenu); //Обработка $this->pageMenu и создание $this->pageOther
		$this->js('jquery.form');
		return 'Widget';
	}

	protected function helpWidget() {
		return 'core/section#widgetNew';
	}

	public function actionWidgetSubmit($data) {
		$model=core::model('widget');
		$model->set($data);
		$model->multiLanguage();
		if($data['id']) $isNew=false; else $isNew=true;
		$rule=array(
			'id'=>array('primary'),
			'groupId'=>array('integer','группа пользователей','max'=>254),
			'section'=>array('string'),
			'name'=>array('latin','Имя',true),
			'data'=>array('html'),
			'cache'=>array('integer','Время кеширования'),
			'title'=>array('string','Описание',true),
			'publicTitle'=>array('boolean')
		);
		$user=core::user();
		if($user->group==255) $rule['cssClass']=array('string');
		if(!$model->save($rule)) return false;
		//Проверить правильность ссылок, перечисленных в поле "другие страницы"
		$url2=str_replace(array("\n",';',"\t",' '),array(',',',',',',''),$data['url2']);
		$url2=explode(',',$url2);
		for($i=0,$cnt=count($url2);$i<$cnt;$i++) {
			$item=$url2[$i];
			if(!$item) continue;
			if(!isset($data['url'][$item])) {
				$i2=strlen($item)-1;
				$s=$item[$i2];
				if($s=='/' || $s=='.') $a=array(1=>true); else $a=array();
				if($s=='/' || $s=='*') $a[2]=true;
				if($s!='/' && $s!='.' && $s!='*') {
					core::error('Все ссылки в списке <b>другие URL</b> должны заканчиваться символами &laquo;/&raquo;,&laquo;.&raquo; или &laquo;*&raquo;');
					return false;
				}
				$s=substr($item,0,$i2);
				$data['url'][$s]=$a;
			}
		}
		//Преобразовать страницы, отмеченные чекбоксами в строки, заканчивающиеся символами ".", "*" или "/"
		$url=array();
		foreach($data['url'] as $link=>$item) {
			if(isset($item[1]) && isset($item[2])) $link.='/';
			elseif(isset($item[1])) $link.='.';
			else $link.='*';
			$url[]=$link;
		}
		$db=core::db();
		//Если виджет уже существует, то выяснить с каких страниц был убран этот виджет - это нужно для того, чтобы корректно обработать событие "widgetPageDelete"
		if(!$isNew) {
			$db->query('SELECT url,sort FROM section WHERE widgetId='.$model->id);
			$delete=$delete0=$add=array();
			$sort=null;
			while($item=$db->fetch()) {
				if(!$sort) $sort=$item[1];
				$i=array_search($item[0],$url);
				if($i!==false) unset($url[$i]);
				else {
					$delete0[]=$item[0];
					$i1=strlen($item[0])-1;
					$s=$item[0][$i1];
					if($s=='/') {
						$s=$item[0];
						$s[$i1]='.';
						$i2=array_search($s,$url);
						if($i2!==false) {
							unset($url[$i2]);
							$add[]=$s;
							$s[$i1]='*';
							$delete[]=$s;
						} else {
							$s[$i1]='*';
							$i2=array_search($s,$url);
							if($i2!==false) {
								unset($url[$i2]);
								$add[]=$s;
								$s[$i1]='.';
								$delete[]=$s;
							} else $delete[]=$item[0];
						}
					} else {
						if(!in_array(substr($item[0],0,$i1).'/',$url)) $delete[]=$item[0];
					}
				}
			}
			if($delete0) {
				//Если есть страницы, с которых виджет был удалён, то спровоцировать событие "удаление виджета со страниц". Это позволит виджетам удалить сопутствующий контент
				//Параметры: (string) - имя виджета; (int) - ИД виджета; (array) - список страниц, с которых был убран виджет
				if($delete) {
					if(!core::hook('widgetPageDelete',$data['name'],$model->id,$delete)) return false;
				}
				$db->query('DELETE FROM section WHERE widgetId='.$model->id.' AND url IN ("'.implode('","',$delete0).'")');
			}
		}
		if($sort===null) { //это новый виджет или не опубликован ни на одной странице
			$sort=(int)$db->fetchValue('SELECT max(sort) FROM section WHERE name='.$db->escape($data['section']));
			$sort++;
		}
		if($url) core::hook('widgetPageAdd',$data['name'],$model->id,$url); //Событие "виджет добавлен на страницы сайта
		if($add) {
			for($i=0,$cnt=count($add);$i<$cnt;$i++) {
				$add[$i]=array(
					'name'=>$data['section'],
					'url'=>$add[$i],
					'widgetId'=>$model->id,
					'sort'=>$sort
				);
			}
			$db->insert('section',$add);
		}
		if($url) {
			for($i=0,$cnt=count($url);$i<$cnt;$i++) {
				$url[$i]=array(
					'name'=>$data['section'],
					'url'=>$url[$i],
					'widgetId'=>$model->id,
					'sort'=>$sort
				);
			}
			$db->insert('section',$url);
		}
		core::redirect('section/widget?id='.$data['section'],'Изменения сохранены');
	}
/* ----------------------------------------------------------------------------------- */


// ----------- PRIVATE --------------------------------------------------------------- //
	/* Подготавливает массив меню ($menu) и возвращает массив со старицами, для которых нет соответствующего пункта меню */
	private static function _pageOther($url,&$menu,$level=0) {
		static $title;
		//$urlClear - список страниц без символа ".", "/" или "*"
		$urlClear=array();
		foreach($url as $item) $urlClear[]=substr($item,0,strlen($item)-1);
		$other=array();
		foreach($menu as $i=>$null) {
			$item=&$menu[$i];
			if($title!=$item['menuTitle']) {
				$title=$item['menuTitle'];
			} else unset($item['menuTitle']);
			$y=array_search($item['link'],$urlClear);
			if($y!==false) {
				$type=$url[$y];
				$type=$type[strlen($type)-1];
				if($type=='.' || $type=='/') $item['checked1']=true; else $item['checked1']=false;
				if($type=='*' || $type=='/') $item['checked2']=true; else $item['checked2']=false;
				unset($url[$y]);
			} else $item['checked2']=$item['checked1']=false;
			$item['level']=$level;
			if($item['child']) $url=self::_pageOther($url,$item['child'],$level+1);
		}
		return $url;
	}

}
?>