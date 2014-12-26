<?php
/* ЧПУ: /chat/ИД (actionIndex) - страница чата
*/
class sController extends controller {

	public function __construct() {
		parent::__construct();
		if($this->url[1]=='Post' || $this->url[1]=='Load') $this->id=(int)$_GET['id'];
		else {
			$this->id=(int)$this->url[1];
			$this->url[1]='Index';
		}
		if(!$this->id) core::error404();
	}

	/* Основное окно чата (без сообщений) */
	public function actionIndex() {
		if(!file_exists(core::path().'data/chat.'.$this->id.'.txt')) core::error404(); //содержит сообещния чата
		$this->script('jquery.min');
		$this->script('jquery.form');
		$this->login=$this->_login();

		$this->pageTitle=$this->metaTitle='Чат';
		$this->style('chat');
		return 'Index';
	}

	public function adminIndexLink() {
		return array(
			array('chat.moderate','?controller=chat&id='.$this->id,'edit','Модерировать')
		);
	}

	/* Выводит сообщения чата, вызывается AJAX-запросом */
	public function actionLoad() {
		$t=(int)$_GET['t'];
		$this->_printData($t);
		exit;
	}

	/* Обработка отправленного сообщения */
	public function actionPostSubmit($data) {
		$message=false; //содержит сообщение об ошибке, если она произошла
		if(isset($data['login'])) { //проверка имени пользователя, если пользователь авторизован, то этого поля быть не должно.
			$data['login']=str_replace(array("\n",'|||'),array(' ','||'),trim(strip_tags($data['login'])));
			if(!$data['login']) $message='Имя пользователя задано неверно.';
			else {
				$db=core::db();
				if($db->fetchValue('SELECT 1 FROM user WHERE login='.$db->escape($data['login']))) {
					$message='Это имя пользователя уже кем-то используется, попробуйте другое.';
				}
			}
		} else $data['login']=$this->_login();
		if(!$data['login'] || !$data['message']) exit;
		$_SESSION['chatLogin']=$data['login'];
		$items=file(core::path().'data/chat.'.$this->id.'.txt'); //содержит список сообщений
		//Проверка валидности текста сообщения
		$last=explode('|||',$items[0]);
		if($last[1]==$data['login'] && time()<$last[0]+30) $message='Вы только что уже отправили сообщение. Подждите минуту.';
		$data['message']=strip_tags($data['message']);
		if(!$message) {
			$i=preg_match('/[a-z0-9_\.-]+\.(ru|com|net|org|name|su|biz|info|us)([^a-z]{1}.*?)?$/i',$data['message'],$res);
			if($i) $message='Пожалуйста, не указывайте в чате какие-либо сайты.';
		}
		if(!$message) {
			$db=core::db();
			if($db->fetchValue('SELECT 1 FROM chatBan WHERE ip='.$db->escape($this->_ip()).' AND date>'.time())) {
				$message='Извините, но вам временно запрещено оставлять сообщения.';
			}
		}
		//Если сообщение валидно, то сохранить новое сообщение
		if(!$message) {
			if(count($items)>=100) array_pop($items);
			$f=fopen(core::path().'data/chat.'.$this->id.'.txt','w');
			fwrite($f,time().'|||'.$data['login'].'|||'.str_replace("\n",' ',str_replace('|||','||',$data['message'])).'|||'.$this->_ip()."\n");
			fwrite($f,implode('',$items));
			fclose($f);
		}
		$this->_printData((int)$data['time'],$message);
		exit;
	}
	// ------------------------------ //


	// ---------- PRIVATE ---------- //
	/* Возвращает логин пользователя (если авторизован или направлял уже сообщения) */
	private static function _login() {
		if(isset($_SESSION['chatLogin'])) return $_SESSION['chatLogin'];
		else {
			$u=core::user();
			if($u->group>=200) return 'Админ.';
			elseif($u->id) return $u->login;
		}
		return false;
	}

	/* Выводит список сообщений (для AJAX-запроса) */
	private function _printData($time=null,$message=false) {
		if($message) echo time().'|||чат|||<i>'.$message.'</i>'."\n";
		$f=fopen(core::path().'data/chat.'.$this->id.'.txt','r');
		$online=array();
		$now=time()-2000;
		while($item=fgets($f)) {
			$i=(int)substr($item,0,10);
			if($i>$time) echo $item;
			if($i>$now) {
				$s=substr($item,13,strpos($item,'|||',14)-13);
				if(!in_array($s,$online)) $online[]=$s;
			}
		}
		if($online) echo implode("|||",$online);
		fclose($f);
	}

	private static function _ip() {
 		if(!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
 		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
		return $_SERVER['REMOTE_ADDR'];
	}
	// ------------------------------ //

}
?>