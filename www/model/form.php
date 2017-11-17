<?php
/* Модель "универсальная форма", может быть использована другими модулями */
core::import('core/form');
core::language('form');
class mForm extends form {

	public $title;
	public $formView;
	public $field;

	public function __construct($id=null) {
		if($id) {
			parent::__construct();
			return $this->load($id);
		}
	}

	/* Строит форму, загружая все поля по указанному идентификатору формы */
	public function load($id) {
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT title_'._LANG.',formView FROM frmForm WHERE id='.$id);
		if(!$data) core::error404();
		$this->title=$data[0]; //заголовок формы (может быть заголовок страницы)
		$this->formView=$data[1]; //MVC-представление
		//Загрузить все поля формы
		$this->field=$db->fetchArrayAssoc('SELECT id,title_'._LANG.' title,htmlType,data_'._LANG.' data,defaultValue,required FROM frmField WHERE formId='.$id.' ORDER BY sort');
		for($i=0,$cnt=count($this->field);$i<$cnt;$i++) {
			$type=$this->field[$i]['htmlType'];
			if($type=='radio' || $type=='select' || $type=='listBox') {
				$data=explode('|',$this->field[$i]['data']);
				for($y=0,$cntY=count($data);$y<$cntY;$y++) $data[$y]=array($data[$y],$data[$y]);
				if($type=='select' && !$this->field[$i]['required']) {
					array_unshift($data,array('','('.LNGselect.')'));
				}
				$this->field[$i]['data']=$data;
			}
		}
		return true;
	}

	public function render($action=null,$html=null) {
		if($action) $this->action=$action;
		if($this->formView) {
			$view=$this->formView;
			$this->formView=null; //render() может быть вызван дважды: один раз из контроллера и один раз из представления, поэтому убрать, чтобы небыло зацикливания
			include(core::path().'view/form'.ucfirst($view).'.php');
		} else { //представление не задано, использовать стандартный рендер базового класса
			//Добавить поля в базовый класс формы
			for($i=0,$cnt=count($this->field);$i<$cnt;$i++) {
				$title=$this->field[$i]['title'];
				if($this->field[$i]['required']) $title.='<span class="required">*</span>';
				$type=$this->field[$i]['htmlType'];
				if($type=='radio' || $type=='select' || $type=='listBox') $this->$type($this->field[$i]['id'],$title,$this->field[$i]['data'],$this->field[$i]['defaultValue']);
				else $this->$type($this->field[$i]['id'],$title,$this->field[$i]['defaultValue']);
			}
			$this->submit(LNGSend);
			unset($this->field);
			return parent::render(null,$html);
		}
	}

	/* Выполняет настроенное действие по обработке формы
	$id - идентификатор формы, $data - данные (из $_POST) */
	public function execute($id,$data) {
		core::language('form');
		$this->data=$data;
		$db=core::db();
		$this->form=$db->fetchArrayOnceAssoc('SELECT title_'._LANG.' title,email,subject_'._LANG.' subject,redirect,script FROM frmForm WHERE id='.$id);
		if(!$this->form) core::error404();
		//Если задан пользовательский скрипт обработки (до валидации), то сначала вызвать его.
		if($this->form['script']) {
			$f=core::path().'data/'.$this->form['script'].'Before.php';
			if(file_exists($f)) if(!include($f)) return false; //false расценивается как неудача.
		}
		//Стандартная валидация полей формы
		$m=core::model();
		$m->set($data);
		$db->query('SELECT id,title_'._LANG.',htmlType,data_'._LANG.',required FROM frmField WHERE formId='.$id.' ORDER BY sort');
		$this->field=$validate=array();
		while($item=$db->fetch()) {
			$fldName=$item[0];
			$value=$data[$fldName];
			if($item[2]=='file') {
				if($item[4] && !$data['fld'.$item[0]]['size']) {
					core::error(sprintf(LNGFieldCannotByEmpty,$item[1]));
					return false;
				}
				if($item[3]) {
					$type=explode(',',$item[3]);
					$ext=strtolower($data[$item[0]]['name']);
					$ext=substr($ext,strrpos($ext,'.')+1);
					if(!in_array($ext,$type)) {
						core::error(LNGFileTypeNotSupport);
						return false;
					}
				}
			}
			if($item[2]!='email') $type='string'; else $type='email';
			if($item[2]=='radio' || $item[2]=='select') {
				$d=explode('|',$item[3]);
				if(array_search($value,$d)===false) {
					core::error(sprintf(LNGFieldIllegalValue,$item[1]));
					return false;
				}
			}
			$this->field[]=array('id'=>$fldName,'title'=>$item[1],'required'=>(bool)$item[4],'htmlType'=>$item[2]);
			if($item[2]!='file') $validate[$fldName]=array($type,$item[1],(bool)$item[4]);
		}
		$m->set($data);
		if(!$m->validate($validate)) return false;
		unset($validate);

		$cfg=core::config();
		if($this->form['email']=='cfg') {
			$this->form['email']=$cfg['adminEmailEmail'];
		}
		//Если задан пользовательский скрипт обработки, то вызвать его
		if($this->form['script']) {
			$f=core::path().'data/'.$this->form['script'].'After.php';
			if(file_exists($f)) if(!include($f)) return false; //false расценивается как неудача - нужно прервать дальнейшую работу
		}
		//Отправить письмо, если задан e-mail адрес.
		if($this->form['email']) {
			if(!$this->form['subject']) $this->form['subject']=sprintf(LNGMessageFromSite,$_SERVER['HTTP_HOST']);
			core::import('core/email');
			$e=new email();
			$e->from($cfg['adminEmailEmail'],$cfg['adminEmailName']);
			$e->subject($this->form['subject']);
			$s='<table>';
			for($i=0;$i<count($this->field);$i++) {
				if($this->field[$i]['htmlType']=='textarea') $s.='<tr><td colspan="2"><b>'.$this->field[$i]['title'].'</b></td></tr><tr><td colspan="2"><i>'.$data[$this->field[$i]['id']].'</i></td></tr>';
				elseif($this->field[$i]['htmlType']=='file') {
					$s.='<tr><td><b>'.$this->field[$i]['title'].'</b></td><td><i>'.($data[$this->field[$i]['id']]['size'] ? $data[$this->field[$i]['id']]['name'] : '('.LNGnotLoaded.')').'</i></td></tr>';
					$e->attach($data[$this->field[$i]['id']]['tmpName'],core::translit($data[$this->field[$i]['id']]['name']));
				}
				else $s.='<tr><td><b>'.$this->field[$i]['title'].'</b></td><td><i>'.$data[$this->field[$i]['id']].'</i></td></tr>';
			}
			$s.='</table>';
			$e->message('<p>'.sprintf(LNGNewMessageOnSite,'<a href="http://'.$_SERVER['HTTP_HOST'].core::url().'">'.$_SERVER['HTTP_HOST'].core::url().'</a>').'</p><hr />'.$s);
			if(!$e->send($this->form['email'])) return false;
		}
		return true;
	}

}