<?php
/* Управление чатами */
class sController extends controller {

	public function right() {
		return array(
			'Index'=>'chat.moderate',
			'Ban'=>'chat.moderate',
			'WidgetChat'=>'*'
		);
	}

	public function __construct() {
		parent::__construct();
		if(isset($_REQUEST['id'])) $this->id=(int)$_REQUEST['id']; //идентификатор чата
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Список сообщений (для модерирования) */
	public function actionIndex() {
		$this->items=$this->_load(true);
		return 'Index';
	}

	/* Удаление сообщения админом и, возможно, временная блокировка */
	public function actionBan() {
		$t=$_GET['t']; //расценивается как номер сообщения
		$items=$this->_load(); //загрузить список сообщений
		//Найти нужное сообщение, в $items0 загрузить все сообщения кроме искомого
		$items0='';
		foreach($items as $item) {
			if($item['time']==$t) {
				$ban=$item;
				continue;
			}
			$items0.=$item['source'];
		}
		if(isset($_GET['ban'])) {
			$db=core::db();
			$db->query('DELETE FROM chatBan WHERE date<'.time());
			$db->query('INSERT INTO chatBan (date,ip) VALUES ('.(time()+604800).','.$db->escape($ban['ip']).')');
		}
		//Сохранить сообщения (без удалённого)
		$f=fopen(core::path().'data/chat.'.$this->id.'.txt','w');
		fwrite($f,$items0);
		fclose($f);
		core::redirect('?controller=chat&id='.$this->id);
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Последние сообщения чата */
	public function actionWidgetChat($data=null) {
		if(!$data) $data=array('id'=>null,'count'=>20);
		$f=core::form();
		$f->hidden('id',$data['id']); //идентификатор чата
		$f->text('count','Количество сообщений',$data['count']);
		$f->submit('Продолжить');

		return $f;
	}

	public function actionWidgetChatSubmit($data) {
		$data['id']=(int)$data['id'];
		$data['count']=(int)$data['count'];
		if(!$data['count']) $data['count']=20;
		//Если ИД нет, то это новый виджет - нужно найти максимальный ИД перебирая файлы чатов
		if(!$data['id']) {
			$path=core::path().'data/';
			$d=opendir($path);
			$id=1;
			while($f=readdir($d)) {
				if($f=='.' || $f=='..') continue;
				$i2=strrpos($f,'.');
				if(substr($f,$i2+1)!='txt' || substr($f,0,5)!='chat.') continue;
				$i1=(int)substr($f,5,$i2-5);
				if($id<$i1) $id=$i1+1;
			}
			$data['id']=$id;
			closedir($d);
		}
		//Создать пустой файл для сообщений чата
		$f=fopen(core::path().'data/chat.'.$data['id'].'.txt','w');
		fclose($f);
		unset($data['submit']);
		return $data;
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- PRIVATE ---------------------------------------------------------------- */
	/* Возвращает массив, содержащий сообщения чата. Если задан $message, в массив попадают также текст сообщений чата, иначе - только информация о пользователе */
	private function _load($message=false) {
		$items=file(core::path().'data/chat.'.$this->id.'.txt');
		for($i=0,$cnt=count($items);$i<$cnt;$i++) {
			$item=trim($items[$i]);
			$item=explode('|||',$item);
			if(strlen($item[1])>20) $item[1]=substr($item[1],0,17).'...';
			if($message) {
				$items[$i]=array('time'=>$item[0],'date'=>date('d.m.Y H:i',$item[0]),'name'=>$item[1],'message'=>(strlen($item[2])>70 ? substr($item[2],0,67).'...' : $item[2]),'ip'=>$item[3]);
			} else {
				$items[$i]=array('time'=>$item[0],'name'=>$item[1],'ip'=>$item[3],'source'=>$items[$i]);
			}
		}
		return $items;
	}
/* ----------------------------------------------------------------------------------- */
}
?>