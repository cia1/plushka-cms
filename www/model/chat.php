<?php
define('CHAT_LOGIN_FILTER','root,admin,fuck');
/*
Формат файла /data/chat/{ID}.txt:
ВРЕМЯ \t ЛОГИН|ИД_ПОЛЬЗОВАТЕЛЯ \t ЛОГИН_КОМУ|ИД_КОМУ \t ТЕКСТ_СООБЩЕНИЯ \t АТРИБУТЫ_ТЕКСТА
*/
class chat {

	//Возвращает массив сообщений: если $limit<1000, то последние $limit сообщений, иначе начиная с $limit (если не указано, то возвращает все сообщения)
	public static function content($chatId,$limit=0) {
		$chatId=core::translit($chatId);
		$f=fopen(core::path().'data/chat/'.$chatId.'.txt','r');
		$data=array();
		$cnt=0;
		while($item=fgets($f)) {
			$cnt++;
			$item=explode("\t",$item);
			if($limit>1000 && $item[0]<=$limit) break;
			$from=explode('|',$item[1]);
			$to=explode('|',$item[2]);
			$item=array('time'=>$item[0],'fromLogin'=>$from[0],'fromId'=>$from[1],'toLogin'=>$to[0],'toId'=>$to[1],'message'=>$item[3],'attribute'=>rtrim($item[4]));
			$data[]=$item;
			if($limit<=1000 && $cnt==$limit) break;
		}
		fclose($f);
		$data=array_reverse($data);
		return $data;
	}

	//Возвращает список смайлов
	public static function smile() {
		$d=opendir(core::path().'public/chat-smile');
		$smile=array();
		$url=core::url().'public/chat-smile/';
		while($f=readdir($d)) {
			if($f=='.' || $f=='..' || substr($f,-4)!='.gif') continue;
			$smile[substr($f,0,strlen($f)-4)]=$url.$f;
		}
		closedir($d);
		return $smile;
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

	//Проверяет и фильтрует текст сообщения, добавляет смайлы
	public static function filterMessage($message) {
		core::language('chat');
		$message=trim(str_replace(array("\n","\t",'|'),array(' ',' ','/'),strip_tags($message)));
		//Проверка длины сообщения
		if(mb_strlen($message,'UTF-8')<2) {
			core::error(LNGMessageTooShort);
			return false;
		}
		if(mb_strlen($message,'UTF-8')>420) {
			core::error(LNGMessageTooLong);
			return false;
		}
		//Чёрный список (стоп-слова)
		$blacklist=core::config('chat-blacklist');
		foreach($blacklist as $item) {
			$i=mb_stripos($message,$item,0);
			if($i!==false) {
				core::error(LNGChatBlacklist);
				return false;
			}
		}
		//Фильтр адресов сайтов
//		if(core::userGroup()<200) {
			$cfg=core::config('chat');
			if($cfg['linkFilter']) {
				$cnt=preg_match_all('~[^\s]+[a-z0-9_\.-]+\.(?:ru|com|net|org|name|su|biz|info|us|cc)\b~i',$message,$tmp);
				for($i=0;$i<$cnt;$i++) {
					if($tmp[0][$i]!=$_SERVER['HTTP_HOST']) {
						core::error(LNGDontWriteAnyLinks);
						return false;
					}
				}
			}
//		}
		//Добавить смайлы
		$smile1=self::smile();
		$smile2=array();
		foreach($smile1 as $id=>$item) {
			$smile1[$id]='[['.$id.']]';
			$smile2[]='<img src="'.$item.'" alt="'.$id.'" />';
		}
		$smile1=array_values($smile1);
		$message=str_replace($smile1,$smile2,$message);
		unset($smile1);
		unset($smile2);
		return $message;
	}

	//Добавляет сообщение в чат и возвращает строку сообщения
	public static function post($chatId,$fromLogin,$message,$toLogin=null,$toId=null) {
		$chatId=core::translit($chatId);
		$userId=core::userId();
		$cfg=core::config('chat');
		if(!$userId) $_SESSION['chatLogin']=$fromLogin;
		elseif(isset($cfg['loginAlias'][$fromLogin])) $fromLogin=$cfg['loginAlias'][$fromLogin];
		$s=microtime(true)."\t".$fromLogin."|".$userId."\t".$toLogin.'|'.$toId."\t".$message."\t";
		$data=file(core::path().'data/chat/'.$chatId.'.txt');
		$f=fopen(core::path().'data/chat/'.$chatId.'.txt','w');
		fwrite($f,$s."\n");
		for($i=0,$cnt=count($data);$i<$cnt && $i<$cfg['messageCount'];$i++) {
			fwrite($f,$data[$i]);
		}
		fclose($f);
		return $s;
	}

}