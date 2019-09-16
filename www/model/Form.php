<?php
namespace plushka\model;
use plushka\core\plushka;
use plushka\core\Validator;
use plushka\core\Email;
use plushka\core\Form as FormCore;
use plushka\core\HTTPException;
plushka::language('form');

/**
 * Модель "универсальная форма"
 */
class Form extends FormCore {

	/** @var string Заголовок формы */
	public $title;
	/** @var string|null MVC-представление формы */
	public $formView;
	/** @var array Массив полей формы */
	public $field;
	/** @var array Информация о форме */
	protected $form;
	/** @var array Данные формы */
	protected $data;

	/**
	 * Строит форму, загружая все поля из базы данных
	 * @param int $id Идентификатор формы
	 * @return bool Была ли загружена форма
	 */
	public function load(int $id): bool {
		$db=plushka::db();
		$data=$db->fetchArrayOnce('SELECT title_'._LANG.',formView FROM frm_form WHERE id='.$id);
		if($data===null) return false;
		$this->title=$data[0]; //заголовок формы (может быть заголовок страницы)
		$this->formView=$data[1]; //MVC-представление
		//Загрузить все поля формы
		$this->field=$db->fetchArrayAssoc('SELECT id,title_'._LANG.' title,htmlType,data_'._LANG.' data,defaultValue,required FROM frm_field WHERE formId='.$id.' ORDER BY sort');
		for($i=0,$cnt=count($this->field);$i<$cnt;$i++) {
			$type=$this->field[$i]['htmlType'];
			if($type==='radio' || $type==='select' || $type==='listBox') {
				$data=explode('|',$this->field[$i]['data']);
				for($y=0,$cntY=count($data);$y<$cntY;$y++) $data[$y]=array($data[$y],$data[$y]);
				if($type==='select' && !$this->field[$i]['required']) {
					array_unshift($data,array('','('.LNGselect.')'));
				}
				$this->field[$i]['data']=$data;
			}
		}
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function render(string $action=null,string $html=null): void {
		if($action!==null) $this->action=$action;
		if($this->formView!==null && $this->formView!=='') {
			$view=$this->formView;
			$this->formView=null; //render() может быть вызван дважды: один раз из контроллера и один раз из представления, поэтому убрать, чтобы небыло зацикливания
            /** @noinspection PhpIncludeInspection */
			include(plushka::path().'view/form'.ucfirst($view).'.php');
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
			parent::render(null,$html);
		}
	}

	/**
	 * Выполняет настроенное действие по обработке формы
	 * @param int $id Идентификатор формы
	 * @param string[] $data Данные (из $_POST)
	 * @return bool Данные формы валидны и обработка прошла успешно
	 */
	public function execute(int $id,array $data): bool {
		plushka::language('form');
		$this->data=$data;
		$db=plushka::db();
		$this->form=$db->fetchArrayOnceAssoc('SELECT title_'._LANG.' title,email,subject_'._LANG.' subject,redirect,script,notification FROM frm_form WHERE id='.$id);
		if(!$this->form) throw new HTTPException(404);
		if($this->form['notification']) $this->form['notification']=json_decode($this->form['notification'],true);
		else $this->form['notification']=null;
		//Если задан пользовательский скрипт обработки (до валидации), то сначала вызвать его.
		if($this->form['script']) {
			$f=plushka::path().'data/'.$this->form['script'].'Before.php';
			if(file_exists($f)===true) {
			    /** @noinspection PhpIncludeInspection */
			    if(!include($f)) return false; //false расценивается как неудача.
            }
		}

		//Подготовка данных для валидации
		$db->query('SELECT id,title_'._LANG.',htmlType,data_'._LANG.',required FROM frm_field WHERE formId='.$id.' ORDER BY sort');
		$this->field=$rule=[];
		while($item=$db->fetch()) {
			$fldName=$item[0];
			$value=$data[$fldName];
			if($item[2]==='file') {
				if($item[4] && !$data['fld'.$item[0]]['size']) {
					plushka::error(sprintf(LNGFieldCannotByEmpty,$item[1]));
					return false;
				}
				if($item[3]) {
					$type=explode(',',$item[3]);
					$ext=strtolower($data[$item[0]]['name']);
					$ext=substr($ext,strrpos($ext,'.')+1);
					if(!in_array($ext,$type)) {
						plushka::error(LNGFileTypeNotSupport);
						return false;
					}
				}
			}
			if($item[2]!=='email') $type='string'; else $type='email';
			if($item[2]==='radio' || $item[2]==='select') {
				$d=explode('|',$item[3]);
				if(array_search($value,$d)===false) {
					plushka::error(sprintf(LNGFieldIllegalValue,$item[1]));
					return false;
				}
			}
			$this->field[]=[
				'id'=>$fldName,
				'title'=>$item[1],
				'required'=>(bool)$item[4],
				'htmlType'=>$item[2]
			];
			if($item[2]!=='file') $rule[$fldName]=[$type,$item[1],(bool)$item[4]];
		}

		$validator=new Validator();
		$validator->set($data);
		if($validator->validate($rule)===false) return false;
		unset($rule);

		$cfg=plushka::config();
		if($this->form['email']==='cfg') {
			$this->form['email']=$cfg['adminEmailEmail'];
		}
		//Если задан пользовательский скрипт обработки, то вызвать его
		if($this->form['script']) {
			$f=plushka::path().'data/'.$this->form['script'].'After.php';
			if(file_exists($f)===true) {
			    /** @noinspection PhpIncludeInspection */
			    if(!include($f)) return false; //false расценивается как неудача - нужно прервать дальнейшую работу
            }
		}
		//Отправить письмо, если задан e-mail адрес.
		if($this->form['email']) {
			if(!$this->form['subject']) $this->form['subject']=sprintf(LNGMessageFromSite,$_SERVER['HTTP_HOST']);
			$e=new Email();
			$e->from($cfg['adminEmailEmail'],$cfg['adminEmailName']);
			$e->subject($this->form['subject']);
			$s='<table>';
			for($i=0,$cnt=count($this->field);$i<$cnt;$i++) {
				if($this->field[$i]['htmlType']==='textarea') $s.='<tr><td colspan="2"><b>'.$this->field[$i]['title'].'</b></td></tr><tr><td colspan="2"><i>'.$data[$this->field[$i]['id']].'</i></td></tr>';
				elseif($this->field[$i]['htmlType']==='file') {
					$s.='<tr><td><b>'.$this->field[$i]['title'].'</b></td><td><i>'.($data[$this->field[$i]['id']]['size'] ? $data[$this->field[$i]['id']]['name'] : '('.LNGnotLoaded.')').'</i></td></tr>';
					$e->attach($data[$this->field[$i]['id']]['tmpName'],plushka::translit($data[$this->field[$i]['id']]['name']));
				}
				else $s.='<tr><td><b>'.$this->field[$i]['title'].'</b></td><td><i>'.$data[$this->field[$i]['id']].'</i></td></tr>';
			}
			$s.='</table>';
			$e->message('<p>'.sprintf(LNGNewMessageOnSite,'<a href="http://'.$_SERVER['HTTP_HOST'].plushka::url().'">'.$_SERVER['HTTP_HOST'].plushka::url().'</a>').'</p><hr />'.$s);
			if($e->send($this->form['email'])==false) return false;
		}
		//Отправить уведомление
		if($this->form['notification'] && plushka::moduleExists('notification')) {
			plushka::import('model/notification');
			$transport=Notification::instance($this->form['notification']['transport'],$this->form['notification']['userId']);
			if($transport!==null) {
				$transport->send('Получено сообщение с формы "'.$this->form['title'].'"');
			}
		}
		return true;
	}

}
