<?php
/* Контактные формы */
class sController extends controller {

	public function right($right) {
		if(isset($right['form.*'])) return true; else return false;
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Настройка основных параметров формы */
	public function actionForm() {
		return $this->_form(isset($_GET['id']) ? $_GET['id'] : null);
	}

	public function actionFormSubmit($data) {
		if(!$this->_formSubmit($data)) return false;
		core::redirect('?controller=form&action=form&id='.$data['id'],'Изменения сохранены');
	}

	/* Список полей формы */
	public function actionField() {
		$this->button('?controller=form&action=fieldItem&formId='.$_GET['id'],'new','Добавить поле');
		//Заполнить строки в модели table
		$t=core::table();
		$t->rowTh('Заголовок|Тип поля|Обязательное||');
		$db=core::db();
		$items=$db->fetchArray('SELECT id,title_'._LANG.',htmlType,required,sort FROM frmField WHERE formId='.$_GET['id'].' ORDER BY sort');
		$type=array('text'=>'текстовое поле','radio'=>'переключатель','select'=>'выпадающий список','checkbox'=>'да/нет','textarea'=>'многострочный текст','email'=>'E-mail','file'=>'файл','captcha'=>'каптча');
		for($i=0,$cnt=count($items);$i<$cnt;$i++) {
			$item=$items[$i];
			$t->link($item[1],'?controller=form&action=fieldItem&formId='.$_GET['id'].'&id='.$item[0]);
			$t->text($type[$item[2]]);
			$t->text(($item[3] ? 'да' : 'нет'));
			$t->upDown('?controller=form&formId='.$_GET['id'].'&id='.$item[0].'&action=',$item[4],$cnt);
			$t->itemDelete('?controller=form&formId='.$_GET['id'].'&id='.$item[0].'&action=field');
		}
		unset($items);
		return $t;
	}

	/* Создание или редактирование одного поля формы */
	public function actionFieldItem() {
		if(isset($_GET['id'])) { //редактирование поля - загрузить данные по умолчанию
			$db=core::db();
			$data=$db->fetchArrayOnceAssoc('SELECT id,title_'._LANG.' title,htmlType,data_'._LANG.' data,defaultValue,required FROM frmField WHERE id='.$_GET['id']);
			$data['value']=str_replace('|',"\n",$data['data']);
		} else $data=array('id'=>null,'title'=>'','htmlType'=>'text','required'=>0,'value'=>'','defaultValue'=>'');
		if(isset($_POST['form'])) $formId=$_POST['form']['formId']; else $formId=$_GET['formId'];
		//Сформировать HTML-форму
		$f=core::form();
		$f->hidden('id',$data['id']);
		$f->hidden('formId',$formId);
		$f->text('title','Название',$data['title']);
		$f->select('htmlType','Тип',array(array('text','текстовое поле'),array('radio','переключатель'),array('select','выпадающий список'),array('checkbox','да,нет'),array('textarea','многострочный текст'),array('email','e-mail'),array('file','файл'),array('captcha','каптча')),$data['htmlType']);
		$f->textarea('value','Список значений',$data['value']);
		$f->text('defaultValue','Значение по умолчанию',$data['defaultValue']);
		$f->text('fileType','Тип файла (раширения через запятую)',($data['htmlType']=='file' ? $data['data'] : ''));
		$f->checkbox('required','Обязательное',$data['required']);
		$f->submit('Продолжить');
		$this->f=$f;
		//Отобразить или скрыть соответствующие поля по умолчанию (при помощи JavaScript)
		if($data['htmlType']=='select' || $data['htmlType']=='radio') $this->value=true; else $this->value=false;
		if($data['htmlType']=='file') $this->fileType=true; else $this->fileType=false;
		if($data['htmlType']=='captcha') {
			$this->required=false;
			$this->defaultValue=false;
		} else {
			$this->required=true;
			$this->defaultValue=true;
		}
		return 'Field';
	}

	public function actionFieldItemSubmit($data) {
		//Значения для списков задаются в текстовом поле, сохраняются в БД строкой с разделителем "|"
		if($data['htmlType']=='radio' || $data['htmlType']=='select') $data['data']=str_replace(array("\n","\r"),array('|',''),$data['value']);
		elseif($data['htmlType']=='file') $data['data']=strtolower(str_replace(array('.',' '),'',$data['fileType']));
		else $data['data']=null;
		if($data['htmlType']=='captcha') {
			$data['defaultValue']=null;
			$data['required']=true;
		}
		$validate=array(
			'id'=>array('primary'),
			'formId'=>array('integer'),
			'title'=>array('string','название',true),
			'htmlType'=>array('string'),
			'data'=>array('string'),
			'defaultValue'=>array('string'),
			'required'=>array('boolean'),
		);
		if(!$data['id']) { //Если это новое поле, то вычислить индекс сортировки (целое число)
			$db=core::db();
			$sort=$db->fetchValue('SELECT max(sort) FROM frmField WHERE formId='.$data['formId']);
			$data['sort']=++$sort;
			$validate['sort']=array('integer');
		}
		$m=core::model('frmField');
		$m->set($data);
		$m->multiLanguage();
		if(!$m->save($validate)) return false;
		core::redirect('?controller=form&action=field&id='.$data['formId']);
	}

	/* Изменить порядок полей: поднять выше */
	public function actionUp() {
		$db=core::db();
		$current=(int)$db->fetchValue('SELECT sort FROM frmField WHERE id='.$_GET['id']);
		if($current) {
			$db->query('UPDATE frmField SET sort='.$current.' WHERE formId='.$_GET['formId'].' AND sort='.(--$current));
			$db->query('UPDATE frmField SET sort='.$current.' WHERE id='.$_GET['id']);
		}
		core::redirect('?controller=form&action=field&id='.$_GET['formId']);
	}

	/* Изменить порядок полей: спустить ниже */
	public function actionDown() {
		$db=core::db();
		$current=(int)$db->fetchValue('SELECT sort FROM frmField WHERE id='.$_GET['id']);
		$max=(int)$db->fetchValue('SELECT MAX(sort) FROM frmField WHERE formId='.$_GET['formId']);
		if($current!=$max) {
			$db->query('UPDATE frmField SET sort='.$current.' WHERE formId='.$_GET['formId'].' AND sort='.(++$current));
			$db->query('UPDATE frmField SET sort='.$current.' WHERE id='.$_GET['id']);
		}
		core::redirect('?controller=form&action=field&id='.$_GET['formId']);
	}

	/* Удалить поле формы */
	public function actionFieldDelete() {
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT sort,formId FROM frmField WHERE id='.$_GET['id']);
		if(!$data) core::error404();
		$db->query('UPDATE frmField SET sort=sort-1 WHERE formId='.$data[1].' AND sort>'.$data[0]);
		$db->query('DELETE FROM frmField WHERE id='.$_GET['id']);
		core::redirect('?controller=form&action=field&id='.$data[1]);
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- MENU ------------------------------------------------------------------- */
	/* Общие настройки формы. Ссылка: form/ИД */
	public function actionMenuForm() {
		if(isset($_GET['link']) && $_GET['link']) $id=(int)substr($_GET['link'],strrpos($_GET['link'],'/')+1); else $id=null;
		return $this->_form($id);
	}

	public function actionMenuFormSubmit($data) {
		$id=$this->_formSubmit($data);
		if(!$id) return false;
		return 'form/'.$id;
	}

/* ----------------------------------------------------------------------------------- */



/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Общие настройки формы
	int $data - ИД формы */
	public function actionWidgetForm($data=null) {
		return $this->_form($data);
	}

	public function actionWidgetFormSubmit($data) {
		$id=$this->_formSubmit($data);
		if(!$id) return false;
		return $id;
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- PRIVATE ---------------------------------------------------------------- */
	/* Выводит HTML-форму с общими настройками формы. Вынесена в отдельную функцию т.к. используется в нескольких местах */
	private function _form($data=null) {
		//Загрузить данные формы в зависимости от того, что содержится в $data:
		//это может быть ассоциативный массив, содержащий все настройки, число - идентификатор формы или NULL
		if($data && !is_array($data)) {
			$db=core::db();
			$data=$db->fetchArrayOnceAssoc('SELECT id,title_'._LANG.' title,email,subject_'._LANG.' subject,successMessage_'._LANG.' successMessage,redirect,formView,script FROM frmForm WHERE id='.$data);
			if(!$data['email']) $data['emailSource']='no';
			elseif($data['email']=='cfg') $data['emailSource']='cfg';
			else $data['emailSource']='other';
		} elseif(!$data) $data=array('id'=>null,'title'=>'','emailSource'=>'cfg','email'=>'','successMessage'=>'','redirect'=>'','formView'=>'','script'=>'','subject'=>'');
		if($data['id']) $this->button('?controller=form&action=field&id='.$data['id'],'field','Поля формы');
		//Отобразить/скрыть поле e-mail при помощи JavaScript. Если $data['email']='cfg' - означает что адрес нужно взять из общих настроек сайта
		if($data['emailSource']=='no') $this->showSubject=false; else $this->showSubject=true;
		if($data['emailSource']=='other') $this->showEmail=true; else {
			$this->showEmail=false;
			if($data['emailSource']=='cfg') $data['email']='';
		}
		$f=core::form();
		$f->hidden('id',$data['id']);
		$f->hidden('cacheTime',30);
		$f->text('title','Заголовок страницы',$data['title']);
		$f->radio('emailSource','Адрес отправки',array(array('no','не отправлять e-mail'),array('cfg','e-mail в общих настройках'),array('other','другой адрес:')),$data['emailSource']);
		$f->text('email','E-mail',$data['email']);
		$f->text('subject','Тема письма',$data['subject']);
		$f->editor('successMessage','Сообщение после отправки',$data['successMessage']);
		$f->text('redirect','Редирект после отправки формы',$data['redirect']);
		$s='<b>Редирект</b> - URL адрес, на который будет осуществлён переход после успешной отправки формы.';
		//Только для суперпользователя показывать поля, связанные со сложной обработкой данных формы
		if(core::userGroup()==255) {
			$f->text('formView','Индивидуальное представление',$data['formView']);
			$f->text('script','PHP-скрипт',$data['script'],'id="script"');
			$s.='<br />Не меняйте содержимое полей <b>индивидуальное представление</b> и <b>PHP-скрипт</b> если вы не уверены в их предназначении.';
		} else {
			$f->hidden('formView',$data['formView']);
			$f->hidden('script',$data['script']);
		}
		$f->submit('Продолжить');
		$this->f=$f;

		$this->cite='<span id="scriptComment"></span>'.$s;
		return 'Form';
	}

	/* Выполняет валидацию и сохранение данных формы в БД */
	private function _formSubmit($data) {
		$m=core::model('frmForm');
		$validate=array(
			'id'=>array('primary'),
			'title'=>array('string','заголовок страницы',true),
			'email'=>array('email','E-mail'),
			'subject'=>array('string','Тема письма'),
			'successMessage'=>array('html','Сообщение при успешной отправке'),
			'redirect'=>array('string'),
			'formView'=>array('string'),
			'script'=>array('script')
		);
		if($data['emailSource']=='no') $data['email']=''; elseif($data['emailSource']=='cfg') {
			$data['email']='cfg'; //Это означает, что e-mail нужно взять из общих настроек сайта
			$validate['email'][0]='string';
		}
		$m->set($data);
		$m->multiLanguage();
		if(!$m->save($validate)) return false;
		return $m->id;
	}
/* ----------------------------------------------------------------------------------- */

}
?>