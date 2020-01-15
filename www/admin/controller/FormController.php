<?php
namespace plushka\admin\controller;
use plushka\admin\core\Controller;
use plushka\admin\core\FormEx;
use plushka\admin\core\FormField;
use plushka\admin\core\plushka;
use plushka\admin\core\Table;
use plushka\core\HTTPException;
use plushka\model\Notification;

/**
 * Управление универсальными контактыми формами
 * `/admin/form/form?id={formId}` - основные параметры формы
 * `/admin/form/field?id={formId}` - список полей формы
 * `/admin/form/fieldItem?[formId={formId}][&id={fieldId}] - создание/редактирование поля
 * `/admin/form/up?formId={formId}&id={fieldId} - изменить порядок полей: поднять выше
 * `/admin/form/fieldDelete?id={fieldId} - удалить поле
 * `/admin/form/menuForm` - меню "Общие настройки формы"
 * `/admin/form/widgetForm` - виджет "Контактная Форма"
 *
 * @property-read bool   $showSubject  (actionForm, actionMenuForm, actionWidgetForm)
 * @property-read bool   $showEmail    (actionForm, actionMenuForm, actionWidgetForm)
 * @property-read bool   $value        (actionFieldItem)
 * @property-read FormEx $form         (actionFieldItem, actionForm, actionMenuForm, actionWidgetForm)
 * @property-read bool   $fileType     (actionFieldItem)
 * @property-read bool   $required     (actionFieldItem)
 * @property-read        $defaultValue (actionFieldItem)
 */
class FormController extends Controller {

	public function right(): array {
		return [
			'form'=>'form.*',
			'field'=>'form.*',
			'fieldItem'=>'form.*',
			'up'=>'form.*',
			'down'=>'form.*',
			'fieldDelete'=>'form.*',
			'menuForm'=>'form.*',
			'widgetForm'=>'form.*'
		];
	}

	/**
	 * Основные параметры формы
	 * @return string
	 */
	public function actionForm(): string {
		return $this->_form($_GET['id'] ?? null);
	}

	public function actionFormSubmit(array $data): void {
		if($this->_formSubmit($data)===null) return;
		plushka::success('Изменения сохранены');
		plushka::redirect('form/form?id='.$data['id']);
	}

	/**
	 * Список полей формы
	 * @return Table
	 */
	public function actionField(): Table {
		$formId=(int)$_GET['id'];
		$this->button('form/fieldItem?formId='.$formId,'new','Добавить поле');
		//Заполнить строки в модели table
		$table=plushka::table();
		$table->rowTh('Заголовок|Тип поля|Обязательное||');
		$db=plushka::db();
		$items=$db->fetchArray('SELECT id,title_'._LANG.',htmlType,required,sort FROM frm_field WHERE formId='.$formId.' ORDER BY sort');
		$type=['text'=>'текстовое поле','radio'=>'переключатель','select'=>'выпадающий список','checkbox'=>'да/нет','textarea'=>'многострочный текст','email'=>'E-mail','file'=>'файл','captcha'=>'каптча'];
		for($i=0,$cnt=count($items);$i<$cnt;$i++) {
			$item=$items[$i];
			$table->link('form/fieldItem?formId='.$formId.'&id='.$item[0],$item[1]);
			$table->text($type[$item[2]]);
			$table->text(($item[3] ? 'да' : 'нет'));
			$table->upDown('formId='.$formId.'&id='.$item[0],$item[4],$cnt);
			$table->itemDelete('formId='.$formId.'&id='.$item[0],'field');
		}
		unset($items);
		return $table;
	}

	/**
	 * Создание/редактирование поля формы
	 * @return string
	 */
	public function actionFieldItem(): string {
		$fieldId=isset($_GET['id'])===true ? (int)$_GET['id'] : null;
		if($fieldId!==null) { //редактирование поля - загрузить данные по умолчанию
			$db=plushka::db();
			$data=$db->fetchArrayOnceAssoc('SELECT id,title_'._LANG.' title,htmlType,data_'._LANG.' data,defaultValue,required FROM frm_field WHERE id='.$fieldId);
			$data['value']=str_replace('|',"\n",$data['data']);
		} else $data=[
			'id'=>null,
			'title'=>'',
			'htmlType'=>'text',
			'required'=>0,
			'value'=>'',
			'defaultValue'=>''
		];
		if(isset($_POST['form'])===true) $formId=$_POST['form']['formId']; else $formId=(int)$_GET['formId'];
		//Сформировать HTML-форму
		$form=plushka::form();
		$form->hidden('id',$data['id']);
		$form->hidden('formId',$formId);
		$form->text('title','Название',$data['title']);
		$form->select('htmlType','Тип',[['text','текстовое поле'],['radio','переключатель'],['select','выпадающий список'],['checkbox','да,нет'],['textarea','многострочный текст'],['email','e-mail'],['file','файл'],['captcha','каптча']],$data['htmlType']);
		$form->textarea('value','Список значений',$data['value']);
		$form->text('defaultValue','Значение по умолчанию',$data['defaultValue']);
		$form->text('fileType','Тип файла (раширения через запятую)',($data['htmlType']=='file' ? $data['data'] : ''));
		$form->checkbox('required','Обязательное',$data['required']);
		$form->submit('Продолжить');
		$this->form=$form;
		//Отобразить или скрыть соответствующие поля по умолчанию (при помощи JavaScript)
		if($data['htmlType']==='select' || $data['htmlType']==='radio') $this->value=true; else $this->value=false;
		if($data['htmlType']==='file') $this->fileType=true; else $this->fileType=false;
		if($data['htmlType']==='captcha') {
			$this->required=false;
			$this->defaultValue=false;
		} else {
			$this->required=true;
			$this->defaultValue=true;
		}
		return 'Field';
	}

	public function actionFieldItemSubmit(array $data): void {
		$formField=new FormField();
		$formField->set($data);
		if($formField->save()===false) return;
		plushka::redirect('form/field?id='.$formField->formId);
	}

	/**
	 * Изменить порядок полей: поднять выше
	 */
	public function actionUp(): void {
		$formId=(int)$_GET['formId'];
		$fieldId=(int)$_GET['id'];
		$db=plushka::db();
		$current=(int)$db->fetchValue('SELECT sort FROM frm_field WHERE id='.$fieldId);
		if($current) {
			$db->query('UPDATE frm_field SET sort='.$current.' WHERE formId='.$formId.' AND sort='.(--$current));
			$db->query('UPDATE frm_field SET sort='.$current.' WHERE id='.$fieldId);
		}
		plushka::redirect('form/field?id='.$_GET['formId']);
	}

	/**
	 * Изменить порядок полей: спустить ниже
	 */
	public function actionDown(): void {
		$formId=(int)$_GET['formId'];
		$fieldId=(int)$_GET['id'];
		$db=plushka::db();
		$current=(int)$db->fetchValue('SELECT sort FROM frm_field WHERE id='.$fieldId);
		$max=(int)$db->fetchValue('SELECT MAX(sort) FROM frm_field WHERE formId='.$formId);
		if($current!=$max) {
			$db->query('UPDATE frm_field SET sort='.$current.' WHERE formId='.$formId.' AND sort='.(++$current));
			$db->query('UPDATE frm_field SET sort='.$current.' WHERE id='.$fieldId);
		}
		plushka::redirect('form/field?id='.$_GET['formId']);
	}

	/**
	 * Удалить поле формы
	 */
	public function actionFieldDelete() {
		$fieldId=(int)$_GET['id'];
		$db=plushka::db();
		$data=$db->fetchArrayOnce('SELECT sort,formId FROM frm_field WHERE id='.$fieldId);
		if($data===null) throw new HTTPException(404);
		$db->query('UPDATE frm_field SET sort=sort-1 WHERE formId='.$data[1].' AND sort>'.$data[0]);
		$db->query('DELETE FROM frm_field WHERE id='.$fieldId);
		plushka::redirect('form/field?id='.$data[1]);
	}

	/**
	 * Меню "Общие настройки формы"
	 * @return string
	 */
	public function actionMenuForm(): string {
		if(isset($_GET['link'])===true && $_GET['link']) $id=(int)substr($_GET['link'],strrpos($_GET['link'],'/')+1);
		else $id=null;
		return $this->_form($id);
	}

	public function actionMenuFormSubmit(array $data) {
		$id=$this->_formSubmit($data);
		if($id===null) return false;
		return 'form/'.$id;
	}

	/**
	 * Виджет "Форма"
	 * Выводит общие настройки формы
	 * @param int $formId ИД формы
	 * @return string
	 */
	public function actionWidgetForm(int $formId=null): string {
		return $this->_form($formId);
	}

	public function actionWidgetFormSubmit(array $data) {
		$id=$this->_formSubmit($data);
		if($id===null) return false;
		return $id;
	}

	/**
	 * Выводит HTML-форму с общими настройками формы. Вынесена в отдельную функцию т.к. используется в нескольких местах
	 * @param array|int|null $data
	 * @return string
	 */
	private function _form($data=null): string {
		//Загрузить данные формы в зависимости от того, что содержится в $data:
		//это может быть ассоциативный массив, содержащий все настройки, число - идентификатор формы или NULL
		if($data!==null && is_array($data)===false) {
			$db=plushka::db();
			$data=$db->fetchArrayOnceAssoc('SELECT id,title_'._LANG.' title,email,subject_'._LANG.' subject,successMessage_'._LANG.' successMessage,redirect,formView,script,notification FROM frm_form WHERE id='.$data);
			if(!$data['email']) $data['emailSource']='no';
			elseif($data['email']=='cfg') $data['emailSource']='cfg';
			else $data['emailSource']='other';
		} elseif($data===null) $data=[
			'id'=>null,
			'title'=>'',
			'emailSource'=>'cfg',
			'email'=>'',
			'successMessage'=>'',
			'redirect'=>'',
			'formView'=>'',
			'script'=>'',
			'subject'=>''
		];
		if($data['id']) $this->button('form/field?id='.$data['id'],'field','Поля формы');
		//Отобразить/скрыть поле e-mail при помощи JavaScript. Если $data['email']='cfg' - означает что адрес нужно взять из общих настроек сайта
		if($data['emailSource']==='no') $this->showSubject=false; else $this->showSubject=true;
		if($data['emailSource']==='other') $this->showEmail=true; else {
			$this->showEmail=false;
			if($data['emailSource']==='cfg') $data['email']='';
		}
		if($data['notification']==='') $data['notification']=['transport'=>null,'userId'=>null];
		else $data['notification']=json_decode($data['notification'],true);
		$f=plushka::form();
		$f->hidden('id',$data['id']);
		$f->hidden('cacheTime',30);
		$f->text('title','Заголовок страницы',$data['title']);
		$f->radio('emailSource','Адрес отправки',[['no','не отправлять e-mail'],['cfg','e-mail в общих настройках'],['other','другой адрес:']],$data['emailSource']);
		$f->text('email','E-mail',$data['email']);
		$f->text('subject','Тема письма',$data['subject']);
		$f->editor('successMessage','Сообщение после отправки',$data['successMessage']);
		$f->text('redirect','Редирект после отправки формы',$data['redirect']);
		$s='<b>Редирект</b> - URL адрес, на который будет осуществлён переход после успешной отправки формы.';
		//Только для суперпользователя показывать поля, связанные со сложной обработкой данных формы
		if(plushka::userGroup()==255) {
			$f->text('formView','Индивидуальное представление',$data['formView']);
			$f->text('script','PHP-скрипт',$data['script'],'id="script"');
			$s.='<br />Не меняйте содержимое полей <b>индивидуальное представление</b> и <b>PHP-скрипт</b> если вы не уверены в их предназначении.';
		} else {
			$f->hidden('formView',$data['formView']);
			$f->hidden('script',$data['script']);
		}

		if(plushka::moduleExists('notification')) {
			$transport=Notification::transportList(plushka::userId(),true);
			//исключить уведомления e-mail
			foreach($transport as $i=>$item) if($item->id()==='email') {
				unset($transport[$i]);
				break;
			}
			if(count($transport)>0) {
				$f->html('<h3>Уведомления</h3>');
				$f->select('notification][userId','Получатель','SELECT id,login FROM user WHERE groupId>=200 ORDER BY login',$data['notification']['userId'],'(не требуется)');
				foreach($transport as $i=>$item) $transport[$i]=[$item->id(),$item->title()];
				$f->select('notification][transport','Метод отправки',$transport,$data['notification']['transport'],'(не требуется)');
			}
		}
		$f->submit('Продолжить');
		$this->form=$f;

		$this->cite='<span id="scriptComment"></span>'.$s;
		return 'Form';
	}

	/**
	 * Выполняет валидацию и сохранение данных формы в БД
	 * @param array $data
	 * @return int|null
	 */
	private function _formSubmit(array $data): ?int {
		$m=plushka::model('frm_form');
		$validate=[
			'id'=>['primary'],
			'title'=>['string','заголовок страницы',true],
			'email'=>['email','E-mail'],
			'subject'=>['string','Тема письма'],
			'successMessage'=>['html','Сообщение при успешной отправке'],
			'redirect'=>['string'],
			'formView'=>['string'],
			'script'=>['string'],
			'notification'=>['string']
		];
		if($data['emailSource']==='no') $data['email']=''; elseif($data['emailSource']==='cfg') {
			$data['email']='cfg'; //Это означает, что e-mail нужно взять из общих настроек сайта
			$validate['email'][0]='string';
		}
		if(isset($data['notification'])===true && $data['notification']['userId'] && $data['notification']['transport']) {
			$data['notification']['userId']=(int)$data['notification']['userId'];
			$data['notification']=json_encode($data['notification']);
		} else $data['notification']=null;
		$m->set($data);
		$m->multiLanguage();
		if(!$m->save($validate)) return null;
		return $m->id;
	}

}