<?php
define('CHAT_LOGIN_FILTER','root,admin,fuck');
/*
Формат файла /data/chat.txt:
ВРЕМЯ \t ЛОГИН|ИД_ПОЛЬЗОВАТЕЛЯ \t ЛОГИН_КОМУ|ИД_КОМУ \t ТЕКСТ_СООБЩЕНИЯ \t АТРИБУТЫ_ТЕКСТА
*/
class chat {

	//Возвращает массив сообщений, начиная с $timeFrom (если не указано, то возвращает все сообщения)
	public static function content($timeFrom=0) {
		$f=fopen(core::path().'/data/chat.txt','r');
		$data=array();
		while($item=fgets($f)) {
			$item=explode("\t",$item);
			if($item[0]<=$timeFrom) break;
			$from=explode('|',$item[1]);
			$to=explode('|',$item[2]);
			$item=array('time'=>$item[0],'fromLogin'=>$from[0],'fromId'=>$from[1],'toLogin'=>$to[0],'toId'=>$to[1],'message'=>$item[3],'attribute'=>rtrim($item[4]));
			$data[]=$item;
		}
		fclose($f);
		$data=array_reverse($data);
		return $data;
	}

	//Проверяет и фильтрует логин (вводится посетителем вручную)
	public static function filterLogin($login,$captcha) {
		core::language('chat');
		if(!$captcha || $captcha!=$_SESSION['captcha']) {
			core::error(LNGChatCaptchaIsWrong);
			return false;
		}
		$login=trim(strip_tags($login));
		if(!$login) {
			core::error(LNGLoginCannotByEmpty);
			return false;
		}
		//Фильтр запрещённых слов (частичное совпадение)
		$filter=explode(',',CHAT_LOGIN_FILTER);
		foreach($filter as $item) {
			if(strpos($login,$item)!==false) {
				core::error(LNGThisLoginCannotByUse);
				return false;
			}
		}
		$filter=explode(',',LNGLoginFilter);
		foreach($filter as $item) {
			if(strpos($login,$item)!==false) {
				core::error(LNGThisLoginCannotByUse);
				return false;
			}
		}
		//Логин не должен совпадать с именем зарегистрированного пользователя
		$db=core::db();
		if($db->fetchValue('SELECT 1 FROM user WHERE login='.$db->escape($login))) {
			core::error(LNGThisLoginAlreadyExists);
			return false;
		}
		return $login;
	}

	//Проверяет и фильтрует текст сообщения
	public static function filterMessage($message) {
		$message=trim(str_replace("\t",' ',strip_tags($message)));
		if(mb_strlen($message,'UTF-8')<2) {
			core::error(LNGMessageTooShort);
			return false;
		}
		if(mb_strlen($message,'UTF-8')>300) {
			core::error(LNGMessageTooLong);
		}
		return $message;
	}

	//Добавляет сообщение в чат и возвращает строку сообщения
	public static function post($fromLogin,$message,$toLogin=null,$toId=null) {
		$userId=core::userId();
		$cfg=core::config('chat');
		if(!$userId) $_SESSION['chatLogin']=$fromLogin;
		elseif(isset($cfg['loginAlias'][$fromLogin])) $fromLogin=$cfg['loginAlias'][$fromLogin];
		$s=microtime(true)."\t".$fromLogin."|".$userId."\t".$toLogin.'|'.$toId."\t".$message."\t";
		$data=file(core::path().'data/chat.txt');
		$f=fopen(core::path().'data/chat.txt','w');
		fwrite($f,$s."\n");
		for($i=0,$cnt=count($data);$i<$cnt && $i<$cfg['messageCount'];$i++) {
			fwrite($f,$data[$i]);
		}
		fclose($f);
		return $s;
	}
}