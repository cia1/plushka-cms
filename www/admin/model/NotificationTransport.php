<?php
namespace plushka\admin\model;
use plushka\admin\core\FormEx;
use plushka\admin\core\plushka;
use plushka\model\Notification;

/**
 * Транспорт (канал) передачи уведомлений
 * На основе этого класса реализуются конкретные транспорты
 */
abstract class NotificationTransport {

	/**
	 * Возвращает список зарегистрированных транспортов
	 * @return string[]
	 */
	public static function getList(): array {
		$path=plushka::path().'admin/model/';
		$d=opendir($path);
		$transport=[];
		while($f=readdir($d)) {
			if($f==='.' || $f==='..' || $f==='notificationTransport.php' || substr($f,-4)!=='.php' || substr($f,0,21)!=='notificationTransport') continue;
			$transport[]=substr($f,0,-4);
		}
		closedir($d);
		return $transport;
	}

	/**
	 * Созадёт экземпляр класса транспорта
	 * @param string $className Имя транспорта (короткое имя класса)
	 * @return self
	 */
	public static function instance(string $className): self {
		$cfg=strtolower($className[21]).substr($className,22);
		$cfg=plushka::config('notification',$cfg);
		plushka::import('admin/model/'.$className);
		return new $className($cfg);
	}

	/**
	 * Возвращает название (заголовок) транспорта
	 * @param string $className Имя транспорта (краткое имя класса)
	 * @return string
	 */
	public static function title(string $className): string {
		$className=str_replace('Transport','',$className);
		plushka::import('model/notification');
		plushka::import('model/'.$className);
		/** @var Notification $public */
		$public=new $className();
		return $public->title();
	}

	/**
	 * Метод должен добавить необходимые поля к форме настройки транспорта
	 * @param FormEx $form
	 */
	abstract public function formAppend(FormEx $form): void;

	/**
	 * Обрабатывает данные формы, преобразуя их в конфигурацию
	 * В случае неуспешной валидации следует вызвать plushka::error().
	 * @param array $data Данные формы
	 * @return mixed Конфигурация транспорта
	 * @see self::formAppend()
	 */
	abstract public function form2Setting(array $data);

	/** @var array Конифигурация транспорта */
	private $_setting;

	/**
	 * NotificationTransport constructor.
	 * @param array $setting
	 */
	public function __construct(array $setting) {
		$this->_setting=$setting;
	}

	/**
	 * @param string $attribute Имя атрибута конфигурации
	 * @return mixed|null
	 */
	public function __get(string $attribute) {
		return $this->_setting[$attribute] ?? null;
	}

	/**
	 * Возвращает название (идентификатор) транспорта, определяя его по имени класса
	 * @return string
	 */
	public function getId(): string {
		$id=get_class($this);
		return strtolower($id[21]).substr($id,22);
	}

}