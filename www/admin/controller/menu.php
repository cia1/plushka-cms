<?php
/* Управление меню, скрытым меню и пунктами меню. Виджеты не имеют жёсткой привязки к меню - чтобы дать возможность размещать несколько виджетов одного и того же меню */
class sController extends controller {

	public function right($right,$action) {
		if(isset($right['menu.*'])) return true; else return false;
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Создание нового меню. Вызывается при нажатии на "новое меню" при создании виджета "меню" */
	public function actionItemMenu() {
		if(isset($_GET['id'])) { //Идентификатор пункта меню задан - загрузить данные
			$db=core::db();
			$data=$db->fetchArrayOnceAssoc('SELECT * FROM menu WHERE id='.(int)$_GET['id']);
		} else $data=array('id'=>null,'title'=>''); //ИД пункта меню нет - пустой массив
		$f=core::form();
		$f->hidden('id',$data['id']);
		$f->text('title','Название',$data['title']);
		$f->submit('Сохранить');
		return $f;
	}

	public function actionItemMenuSubmit($data) {
		$m=core::model('menu');
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'title'=>array('string',true)
		))) return false;
		core::redirect('?controller=menu&action=items&menuId='.$data['id']);
	}

	/* Список пунктов меню */
	public function actionItems($hidden=false) {
		$menuId=$_GET['menuId'];
		$this->button('action=item&menuId='.$menuId,'new','Создать новый пункт меню','Создать');
		//Подготовить древовидный массив пунктов меню (меню может быть многоуровневым)
		$db=core::db();
		$db->query('SELECT id,parentId,link,title_'._LANG.',sort FROM menuItem WHERE menuId='.$db->escape($menuId).' ORDER BY sort');
		$data=array();
		while($item=$db->fetch()) {
			if(!isset($data[$item[1]])) $data[$item[1]]=array();
			$data[$item[1]][]=$item;
		}
		//Теперь сформировать таблицу рекурсивным вызовом.
		$t=core::table();
		$t->rowTh('Заголовок|Ссылка|Порядок|'); //Заголовок таблицы (<tr><th>...</tr>)
		$this->_buildViewTable($data,$t);
		return $t;
	}

	/* Добавляет пункты меню к таблице $table, используя данные массива $d.
	$parentId - ИД родителя, для которого добавлять пункты меню; $level - уровень вложенности (отвечает за отступ названия пункта меню от края) */
	private function _buildViewTable($d,$table,$parentId=0,$level=0) {
		if(!isset($d[$parentId])) return;
		$data=$d[$parentId];
		$cnt=count($data);
		for($i=0;$i<$cnt;$i++) {
			$table->text(str_repeat("&nbsp;&nbsp; - ",$level).'<a href="'.core::link('menu&action=item&id='.$data[$i][0]).'">'.$data[$i][3].'</a>');
			$table->text($data[$i][2]);
			$table->upDown('?controller=menu&id='.$data[$i][0].'&action=',$data[$i][4],$cnt); //Кнопки сортировки (выше/ниже)
			$table->delete('?controller=menu&action=delete&id='.$data[$i][0],'Подтвердите удаление.\n\nУдаление пункта меню может повлечь удаление соответствующих данных, на которые ссылается это меню.');
			if(isset($d[$data[$i][0]])) $this->_buildViewTable($d,$table,$data[$i][0],$level+1); //Если есть вложенные пункты меню, то добавить к таблице их
		}
	}

	public function actionItem() {
		$db=core::db();
		if(isset($_GET['id'])) { //Редактирование - загрузить данные
			$this->data=$db->fetchArrayOnceAssoc('SELECT i.id id,i.parentId parentId,i.menuId menuId,i.link link,i.title_'._LANG.' title,i.typeId typeId,t.controller controller,t.action action FROM menuItem i LEFT JOIN menuType t ON t.id=i.typeId WHERE i.id='.$_GET['id']);
			$this->data['type']=array($this->data['typeId'],$this->data['controller'],$this->data['action']);
		} else { //Новый пункт меню
			//ИД меню сохраняется в сессии, т.к. возможны случаи (на будущее в общем-то) кодга из меню будет переход на другую страницу админки, а потом возврат
			if(isset($_GET['menuId'])) $_SESSION['_menuId']=$_GET['menuId'];
			else {
				$_GET['menuId']=$_SESSION['_menuId'];
				unset($_SESSION['_menuId']);
			}
			$this->data=array('id'=>null,'menuId'=>$_GET['menuId'],'link'=>'','title'=>'','typeId'=>'','parentId'=>0,'type'=>null);
			if(isset($_GET['type'])) $this->data['type']=$db->fetchArrayOnce('SELECT id,controller,action FROM menuType WHERE id='.$_GET['type']);
		}
		//Загрузить список всех типов меню, чтобы предоставить пользователю выбрать
		$this->type=$db->fetchArrayAssoc('SELECT * FROM menuType ORDER BY controller,id');
		$this->script('jquery.form');
		return 'Item';
	}

	public function actionItemSubmit($data) {
		unset($_SESSION['_menuId']);
		$model=core::model('menuItem');
		$db=core::db();
		if(!$data['parentId']) $data['parentId']='0';
		//Подготовить массив с правилами валидации
		$validate=array(
			'id'=>array('primary'),
			'parentId'=>array('integer'),
			'menuId'=>array('string'),
			'link'=>array('string'),
			'title_'._LANG=>array('string','заголовок ссылки в меню','max'=>50),
			'typeId'=>array('integer')
		);
		//Если это новый пункт меню, то вычислить индекс сортировки (задаёт порядок пунктов)
		if(!$data['id']) {
			$validate['sort']=array('integer');
			$sort=(int)$db->fetchValue('SELECT MAX(sort) FROM menuItem WHERE menuId='.$db->escape($data['menuId']).' AND parentId='.$data['parentId']);
			$data['sort']=$sort+1;
		} else {
			//Если пункт меню перенесён (сменен родитель), то пересчитать индексы сортировки
			$old=$db->fetchArrayOnce('SELECT parentId,sort FROM menuItem WHERE id='.$data['id']);
			if($old[0]!=$data['parentId']) {
				$db->query('UPDATE menuItem SET sort=sort-1 WHERE menuId='.$data['menuId'].' AND parentId='.$old[0].' AND sort>'.$old[1]);
				$validate['sort']=array('integer');
				$sort=(int)$db->fetchValue('SELECT MAX(sort) FROM menuItem WHERE menuId='.$data['menuId'].' AND parentId='.$data['parentId']);
				$data['sort']=$sort+1;
			}
		}
		$model->set($data);
		if(!$model->save($validate)) return false;
		core::redirect('?controller=menu&action=items&menuId='.$data['menuId'],'Изменения сохранены');
	}

	/* Порядок пунктов меню (выше) */
	public function actionUp() {
		$id=$_GET['id'];
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT menuId,sort,parentId FROM menuItem WHERE id='.$id);
		$sort=$data[1]-1;
		if($data[1]) {
			$db->query('UPDATE menuItem SET sort='.$data[1].' WHERE menuId='.$db->escape($data[0]).' AND parentId='.$data[2].' AND sort='.$sort);
			$db->query('UPDATE menuItem SET sort='.$sort.' WHERE id='.$id);
		}
		if($data[0]) $menuId='&menuId='.$data[0]; else $menuId='';
		core::redirect('?controller=menu&action=items'.$menuId);
	}

	/* Порядок пунктов меню (ниже) */
	public function actionDown() {
		$id=$_GET['id'];
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT menuId,sort,parentId FROM menuItem WHERE id='.$id);
		$maxSort=$db->fetchValue('SELECT max(sort) FROM menuItem WHERE menuId='.$db->escape($data[0]).' AND parentId='.$data[2]);
		$sort=$data[1]+1;
		if($data[1]!=$maxSort) {
			$db->query('UPDATE menuItem SET sort='.$data[1].' WHERE menuId='.$db->escape($data[0]).' AND parentId='.$data[2].' AND sort='.$sort);
			$db->query('UPDATE menuItem SET sort='.$sort.' WHERE id='.$id);
		}
		if($data[0]) $menuId='&menuId='.$data[0]; else $menuId='';
		core::redirect('?controller=menu&action=items'.$menuId);
	}

	/* Удаление пункта меню */
	public function actionDelete() {
		$id=$_GET['id'];
		$db=core::db();
		if($db->fetchValue('SELECT 1 FROM menuItem WHERE parentId='.$id)) {
			controller::$error='Меню содержит вложенные пункты меню. Удаление невозможно.';
			return false;
		}
		$data=$db->fetchArrayOnce('SELECT menuId,link,sort FROM menuItem WHERE id='.$id);
		if(!core::hook('menuItemDelete',$data[1],$data[0])) return false; //Очень важное прерывание, которое позволяет модулям удалить неиспользуемые более данные
		$db->query('UPDATE menuItem SET sort=sort-1 WHERE menuId='.$data[0].' AND sort>'.$data[2]); //Чтобы числа сортировки были "ровными"
		$db->query('DELETE FROM menuItem WHERE id='.$id);
		if($data[0]) $menuId='&menuId='.$data[0]; else $menuId='';
		core::redirect('?controller=menu&action=items'.$menuId,'Пункт меню удалён');
	}

	/* Выводит пункты скрытого меню */
	public function actionHidden() {
		$_GET['menuId']=0; //без лишнего фанатизма в структурировании кода
		return $this->actionItems(true);
	}

/* ----------------------------------------------------------------------------------- */


/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Виджет меню. На форме список с существующими меню */
	public function actionWidgetList($menuId) {
		$this->menuId=$menuId;
		//Ссылка для создания нового меню с последующим возвратом к виджету */
		$this->newItemLink=core::link('menu&action=itemMenu').'&backlink='.urlencode('?controller=section&action=widget&type=menu&amp;section='.$_GET['section']);
		return 'WidgetList';
	}

	public function actionWidgetListSubmit($data) {
		return $data['menuId'];
	}
/* ----------------------------------------------------------------------------------- */

}
?>