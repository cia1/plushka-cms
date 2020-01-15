<?php
namespace plushka\admin\controller;
use plushka\admin\core\Controller;
use plushka\admin\core\FormEx;
use plushka\admin\core\plushka;
use plushka\admin\core\Table;

/**
 * Управление меню
 *
 * `/admin/menu/itemMenu` - создание нового меню
 * `/admin/menu/items?menuId={menuId}` - список пунктов меню
 * `/admin/menu/item?id={itemId}[&menuId={menuId}]` - создание/редактирование пункта меню
 * `/admin/menu/up?id={itemId}` - порядок пунктов меню: поднять выше
 * `/admin/menu/down?id={itemId}` - порядок пунктов меню: спустить ниже
 * `/admin/menu/delete?id={itemId}` - удаление пункта меню
 * `/admin/menu/widgetList` - виджет "Меню"
 *
 * @property-read int    $menuId      (actionWidgetList)
 * @property-read string $newItemLink (actionWidgetList)
 * @property-read array  $data        (actionItem)
 * @property-read array  $type        (actionItem)
 */
class MenuController extends Controller {

	public function right(): array {
		return [
			'itemMenu'=>'menu.*',
			'items'=>'menu.*',
			'item'=>'menu.*',
			'up'=>'menu.*',
			'down'=>'menu.*',
			'delete'=>'menu.*',
			'hidden'=>'menu.*',
			'widgetList'=>'menu.*'
		];
	}

	/**
	 * Создание нового меню. Вызывается при нажатии на "новое меню" при создании виджета "меню"
	 * @return FormEx
	 */
	public function actionItemMenu(): FormEx {
		if(isset($_GET['id'])===true) { //Идентификатор пункта меню задан - загрузить данные
			$db=plushka::db();
			$data=$db->fetchArrayOnceAssoc('SELECT * FROM menu WHERE id='.(int)$_GET['id']);
		} else $data=['id'=>null,'title'=>'']; //ИД пункта меню нет - пустой массив
		$f=plushka::form();
		$f->hidden('id',$data['id']);
		$f->text('title','Название',$data['title']);
		$f->submit('Сохранить');
		return $f;
	}

	public function actionItemMenuSubmit(array $data): void {
		$m=plushka::model('menu');
		$m->set($data);
		if(!$m->save([
			'id'=>['primary'],
			'title'=>['string',true]
		])) return;
		plushka::redirect('menu/items?menuId='.$data['id']);
	}

	/**
	 * Список пунктов меню
	 * @return Table
	 */
	public function actionItems(): Table {
		$menuId=(int)$_GET['menuId'];
		$this->button('menu/item?menuId='.$menuId,'new','Создать новый пункт меню','Создать');
		//Подготовить древовидный массив пунктов меню (меню может быть многоуровневым)
		$db=plushka::db();
		$db->query('SELECT id,parentId,link,title_'._LANG.',sort FROM menu_item WHERE menuId='.$db->escape($menuId).' ORDER BY sort');
		$data=[];
		while($item=$db->fetch()) {
			if(isset($data[$item[1]])===false) $data[$item[1]]=[];
			$data[$item[1]][]=$item;
		}
		//Теперь сформировать таблицу рекурсивным вызовом.
		$t=plushka::table();
		$t->rowTh('Заголовок|Ссылка|Порядок|'); //Заголовок таблицы (<tr><th>...</tr>)
		$this->_buildViewTable($data,$t);
		return $t;
	}

	protected function helpItems() {
		return 'core/menu';
	}

	/**
	 * Создание/редактирование пункта меню
	 * @return string
	 */
	public function actionItem(): string {
		$db=plushka::db();
		if(isset($_GET['id'])===true) { //Редактирование - загрузить данные
			$this->data=$db->fetchArrayOnceAssoc('SELECT i.id id,i.parentId parentId,i.menuId menuId,i.link link,i.title_'
				._LANG.' title,i.typeId typeId,t.controller controller,t.action action FROM menu_item i LEFT JOIN menu_type t ON t.id=i.typeId WHERE i.id='.(int)$_GET['id']);
			$this->data['type']=[$this->data['typeId'],$this->data['controller'],$this->data['action']];
		} else { //Новый пункт меню
			//ИД меню сохраняется в сессии, т.к. возможны случаи (на будущее в общем-то) кодга из меню будет переход на другую страницу админки, а потом возврат
			if(isset($_GET['menuId'])===true) $_SESSION['_menuId']=(int)$_GET['menuId'];
			else {
				$_GET['menuId']=$_SESSION['_menuId'];
				unset($_SESSION['_menuId']);
			}
			$this->data=['id'=>null,'menuId'=>(int)$_GET['menuId'],'link'=>'','title'=>'','typeId'=>'','parentId'=>0,
				'type'=>null];
			if(isset($_GET['type'])===true) $this->data['type']=$db->fetchArrayOnce('SELECT id,controller,action FROM menu_type WHERE id='.(int)$_GET['type']);
		}
		//Загрузить список всех типов меню, чтобы предоставить пользователю выбрать
		$this->type=$db->fetchArrayAssoc('SELECT * FROM menu_type ORDER BY controller,id');
		$this->js('jquery.form');
		return 'Item';
	}

	public function actionItemSubmit(array $data): void {
		unset($_SESSION['_menuId']);
		$model=plushka::model('menu_item');
		$model->multiLanguage();
		$db=plushka::db();
		if(!$data['parentId']) $data['parentId']='0';
		//Подготовить массив с правилами валидации
		$validate=[
			'id'=>['primary'],
			'parentId'=>['integer'],
			'menuId'=>['string'],
			'link'=>['string'],
			'title'=>['string','заголовок ссылки в меню','max'=>50],
			'typeId'=>['integer']
		];
		//Если это новый пункт меню, то вычислить индекс сортировки (задаёт порядок пунктов)
		if(!$data['id']) {
			$validate['sort']=['integer'];
			$sort=(int)$db->fetchValue('SELECT MAX(sort) FROM menu_item WHERE menuId='.$db->escape($data['menuId']).' AND parentId='.$data['parentId']);
			$data['sort']=$sort+1;
		} else {
			//Если пункт меню перенесён (сменен родитель), то пересчитать индексы сортировки
			$old=$db->fetchArrayOnce('SELECT parentId,sort FROM menu_item WHERE id='.$data['id']);
			if($old[0]!=$data['parentId']) {
				$db->query('UPDATE menu_item SET sort=sort-1 WHERE menuId='.$data['menuId'].' AND parentId='.$old[0].' AND sort>'.$old[1]);
				$validate['sort']=['integer'];
				$sort=(int)$db->fetchValue('SELECT MAX(sort) FROM menu_item WHERE menuId='.$data['menuId'].' AND parentId='.$data['parentId']);
				$data['sort']=$sort+1;
			}
		}
		$model->set($data);
		if($model->save($validate)===false) return;
		plushka::redirect('menu/items?menuId='.$data['menuId'],'Изменения сохранены');
	}

	/**
	 * Порядок пунктов меню: поднять выше
	 */
	public function actionUp(): void {
		$id=(int)$_GET['id'];
		$db=plushka::db();
		$data=$db->fetchArrayOnce('SELECT menuId,sort,parentId FROM menu_item WHERE id='.$id);
		$sort=$data[1]-1;
		if($data[1]) {
			$db->query('UPDATE menu_item SET sort='.$data[1].' WHERE menuId='.$db->escape($data[0]).' AND parentId='.$data[2].' AND sort='.$sort);
			$db->query('UPDATE menu_item SET sort='.$sort.' WHERE id='.$id);
		}
		if($data[0]) $menuId='&menuId='.$data[0]; else $menuId='';
		plushka::redirect('menu/items'.$menuId);
	}

	/**
	 * Порядок пунктов меню: спустить ниже
	 */
	public function actionDown(): void {
		$id=(int)$_GET['id'];
		$db=plushka::db();
		$data=$db->fetchArrayOnce('SELECT menuId,sort,parentId FROM menu_item WHERE id='.$id);
		$maxSort=$db->fetchValue('SELECT max(sort) FROM menu_item WHERE menuId='.$db->escape($data[0]).' AND parentId='.$data[2]);
		$sort=$data[1]+1;
		if($data[1]!=$maxSort) {
			$db->query('UPDATE menu_item SET sort='.$data[1].' WHERE menuId='.$db->escape($data[0]).' AND parentId='.$data[2].' AND sort='.$sort);
			$db->query('UPDATE menu_item SET sort='.$sort.' WHERE id='.$id);
		}
		if($data[0]) $menuId='&menuId='.$data[0]; else $menuId='';
		plushka::redirect('menu/items'.$menuId);
	}

	/**
	 * Удаление пункта меню
	 */
	public function actionDelete(): void {
		$id=(int)$_GET['id'];
		$db=plushka::db();
		if($db->fetchValue('SELECT 1 FROM menu_item WHERE parentId='.$id)) {
			plushka::error('Меню содержит вложенные пункты меню. Удаление невозможно.');
			return;
		}
		$data=$db->fetchArrayOnce('SELECT menuId,link,sort FROM menu_item WHERE id='.$id);
		if(plushka::hook('menuItemDelete',$data[1],$data[0])===false) return; //Модули должны удалить неиспользуемые
		// более данные
		$db->query('UPDATE menu_item SET sort=sort-1 WHERE menuId='.$data[0].' AND sort>'.$data[2]); //Чтобы числа сортировки были "ровными"
		$db->query('DELETE FROM menu_item WHERE id='.$id);
		if($data[0]) $menuId='&menuId='.$data[0]; else $menuId='';
		plushka::success('Пункт меню удалён');
		plushka::redirect('menu/items'.$menuId);
	}

	/**
	 * Список пунктов скрытого меню
	 * @return Table
	 */
	public function actionHidden(): Table {
		$_GET['menuId']=0;
		return $this->actionItems();
	}

	protected function helpHidden(): string {
		return 'core/menu#hidden';
	}

	/**
	 * Виджет "меню" (выводит список пунктов)
	 * @param int $menuId
	 * @return string
	 */
	public function actionWidgetList(int $menuId): string {
		$this->menuId=$menuId;
		//Ссылка для создания нового меню с последующим возвратом к виджету */
		$this->newItemLink=plushka::link('admin/menu&action=itemMenu').'&backlink='.urlencode('admin/section/widget?type=menu&section='.$_GET['section']);
		return 'WidgetList';
	}

	public function actionWidgetListSubmit(array $data): int {
		return $data['menuId'];
	}

	private function _buildViewTable(array $d,Table $table,int $parentId=0,int $level=0): void {
		if(isset($d[$parentId])===false) return;
		$data=$d[$parentId];
		$cnt=count($data);
		for($i=0;$i<$cnt;$i++) {
			$table->text(str_repeat("&nbsp;&nbsp; - ",$level).'<a href="'.plushka::link('admin/menu&action=item&id='.$data[$i][0]).'">'.$data[$i][3].'</a>');
			$table->text($data[$i][2]);
			$table->upDown('id='.$data[$i][0],$data[$i][4],$cnt); //Кнопки сортировки (выше/ниже)
			$table->delete('id='.$data[$i][0],'delete','Подтвердите удаление.\n\nУдаление пункта меню может повлечь удаление соответствующих данных, на которые ссылается это меню.');
			//Если есть вложенные пункты меню, то добавить их к таблице
			if(isset($d[$data[$i][0]])===true) {
				$this->_buildViewTable($d,$table,$data[$i][0],$level+1);
			}
		}
	}

}