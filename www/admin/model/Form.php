<?php
namespace plushka\admin\model;
use plushka\admin\core\plushka;

/**
 * Помощник создания универсальной формы
 */
class Form {

	/** @var int Идентификатор формы */
	private $_id;
	private $_sort=0;

	/**
	 * Удаляет форму
	 * @param int $id ИД формы
	 * @return bool
	 */
	public static function drop(int $id): bool {
		$db=plushka::db();
		$db->query('DELETE FROM frm_field WHERE formId='.$id);
		$db->query('DELETE FROM frm_form WHERE id='.$id);
		return $db->affected()>0;
	}

	/**
	 * Создаёт форму и сохраняет в базе данных
	 * Параметр $email может содержать строку "cfg", что означает, что нужно взять адрес из общих настроек сайта
	 * @param string      $title          Название
	 * @param string      $subject        Тема письма
	 * @param string|null $script         Имя скрипта, обрабатывающего форму
	 * @param string|null $successMessage Сообщение об успешно отправленной форме
	 * @param string|null $email          Адрес электронной почты, на который нужно отправить данные формы
	 * @return int|null
	 */
	public function create(string $title,string $subject,$script=null,$successMessage=null,string $email=null): ?int {
		if($email===null) $email='cfg';
		$data=[
			'title'=>$title,
			'email'=>$email,
			'subject'=>$subject,
			'successMessage'=>$successMessage,
			'script'=>$script
		];
		$m=plushka::model('frm_form');
		$m->set($data);
		if($m->save([
				'id'=>['primary'],
				'title'=>['string'],
				'email'=>['string'],
				'subject'=>['string'],
				'successMessage'=>['string'],
				'script'=>['string']
			])===false) return null;
		$this->_id=$m->id; //Идентификатор формы для последующей работы
		$this->_sort=0;
		return $this->_id;
	}

	/**
	 * Добавляет к форме текстовое поле
	 * @param string $title    Название
	 * @param bool   $required Обязательное
	 * @param string $default  Значение по умолчанию
	 * @return int|null ИД поля
	 */
	public function addFieldText(string $title,bool $required=false,string $default=''): ?int {
		return $this->_field($title,'text',null,$default,$required);
	}

	/**
	 * Добавляет к форме группу переключателей
	 * Параметр $list может быть строкой, разделённой символами "|"
	 * @param string       $title   Название
	 * @param array|string $list    Список значений
	 * @param string       $default Значение по умолчанию
	 * @return int|null ИД поля
	 */
	public function addFieldRadio(string $title,$list,string $default=null): ?int {
		if(is_array($list)===true) $list=implode('|',$list);
		return $this->_field($title,'radio',$list,$default,false);
	}

	/**
	 * Добавляет к форме выдающий список
	 * @param string       $title   Название
	 * @param array|string $list    Список значений
	 * @param string       $default Значение по умолчанию
	 * @return int|null ИД поля
	 */
	public function addFieldSelect(string $title,$list,string $default=null): ?int {
		if(is_array($list)===true) $list=implode('|',$list);
		return $this->_field($title,'select',$list,$default,false);
	}

	/**
	 * Добавляет к форме чекбокс
	 * @param string               $title Название
	 * @param int|string|bool|null $default
	 * @return int|null ИД поля
	 */
	public function addFieldCheckbox(string $title,$default=null): ?int {
		if($default===1 || $default==='1' || $default===true) $default='1'; else $default='0';
		return $this->_field($title,'checkbox',null,$default,false);
	}

	/**
	 * Добавляет к форме многострочное текстовое поле
	 * @param string $title    Название
	 * @param bool   $required Обязательное
	 * @param string $default  Значение по умолчанию
	 * @return int|null ИД поля
	 */
	public function addFieldTextarea(string $title,bool $required=false,string $default=''): ?int {
		return $this->_field($title,'textarea',null,$default,$required);
	}

	/**
	 * Добавляет к форме поле для ввода адреса электронной почты
	 * @param string $title    Название
	 * @param bool   $required Обязательное
	 * @param string $default  Значение по умолчанию
	 * @return int|null ИД поля
	 */
	public function addFieldEmail(string $title,bool $required=false,string $default='cfg'): ?int {
		return $this->_field($title,'email',null,$default,$required);
	}

	/**
	 * Добавляет к форме поле для загрузки файла
	 * @param string $title    Название
	 * @param bool   $required Обязательное
	 * @return int|null ИД поля
	 */
	public function addFieldFile(string $title,bool $required=false): ?int {
		return $this->_field($title,'file',null,null,$required);
	}

	/**
	 * Возвращает идентификатор формы
	 * @return int
	 */
	public function id(): int {
		return $this->_id;
	}

	/**
	 * Добавляет к форме поле
	 * @param string $title        Название
	 * @param string $htmlType     Тип
	 * @param mixed  $data         Дополнительные данные, зависящие от $htmlType
	 * @param string $defaultValue Значение по умолчанию
	 * @param bool   $required     Обязательно ли поле для заполнения
	 * @return int|null
	 */
	private function _field(string $title,string $htmlType,$data,string $defaultValue,bool $required): ?int {
		$this->_sort++;
		$m=plushka::model('frm_field');
		$data=[
			'formId'=>$this->_id,
			'title'=>$title,
			'htmlType'=>$htmlType,
			'data'=>$data,
			'defaultValue'=>$defaultValue,
			'required'=>$required,
			'sort'=>$this->_sort
		];
		$m->set($data);
		if($m->save([
				'id'=>['primary'],
				'formId'=>['integer'],
				'title'=>['string','заголовок',true,'max'=>25],
				'htmlType'=>['string'],
				'data'=>['string'],
				'defaultValue'=>['string'],
				'required'=>['integer'],
				'sort'=>['integer']
			])===false) return null;
		return $m->id;
	}

}