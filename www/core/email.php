<?php
define('SMTP_PORT',465);
// Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
/* Служит для отправки электронных писем */
class email {

	private $_parts=array();
	private	$_from;
	private $_subject='';
	private $_replyTo;
	private $_message;
	private $_smtpHost;
	private $_smtpUser;
	private $_smtpPassword;
	private $_smtpEmail;
	public $_returnPath;

	/* Инициализирует SMTP, если используется режим SMTP */
	public function __construct() {
		$cfg=core::config();
		if(isset($cfg['smtpHost'])) {
			$this->_smtpHost=$cfg['smtpHost'];
			$this->_smtpUser=$cfg['smtpUser'];
			$this->_smtpPassword=$cfg['smtpPassword'];
			$this->_smtpEmail=$cfg['smtpEmail'];
		}
	}

	/* Задаёт поле "от кого" (почта и псевдоним) */
	public function from($email,$name=null) {
		if(!$name) $name=$email;
		$this->_from='From: =?UTF-8?B?'.base64_encode($name).'?= <'.$email.">\r\n";
	}

	/* Задаёт тему письма */
	public function subject($value) { $this->_subject=$value; }

	/* Задаёт текст письма (HTML) */
	public function message($value) { $this->_message=$value; }

	/* Задаёт текс письма (HTML), используя HTML-шаблон. $data - ассоциативный массив, содержащий данные, которые должны быть подставлены в письмо */
	public function messageTemplate($fileName,$data) {
		if(substr($_SERVER['REQUEST_URI'],0,7)=='/admin/') $adminPath='admin/'; else $adminPath='';
		$this->_message=file_get_contents(core::path().$adminPath.'data/email/'.$fileName.'.html');
		$this->_message=str_replace(array('{{siteLink}}','{{siteName}}'),
			array('http://'.$_SERVER['HTTP_HOST'].core::url(),$_SERVER['HTTP_HOST']),
			$this->_message
		);
		foreach($data as $tag=>$value) $this->_message=str_replace('{{'.$tag.'}}',$value,$this->_message);
	}

	/* Задаёт адрес возврата письма */
	public function returnPath($email) { $this->_returnPath=$email; }

	/* Задаёт адрес для ответов (e-mail и псевдоним) */
	public function replyTo($email,$name=null) {
		if(!$name) $name=$email;
		$this->_replyTo='Reply-To: =?UTF-8?B?'.base64_encode($name).'?= <'.$email.">\r\n";
	}

	/* Добавляет письму вложение
	$filename - путь к файлу, $id - псевдоним (имя) файла, $type - MIME-тип */
	public function attach($filename,$id,$type='application/octet-stream') {
		$this->_parts[]=array('ctype'=>$type,'message'=>base64_encode(file_get_contents($filename)),'name'=>$id,'encoding'=>'base64','id'=>$id,'filename'=>substr($filename,strrpos($filename,'/')+1));
	}

	/* Добавляет к письму вложенное изображение и возвращает идентификатор, по которому можно вставить это изображение в текст письма */
	public function attachImage($filename,$type=null) {
		static $_id;
		if(!$type) $type=substr($filename,strrpos($filename,'.')+1);
		$type=strtolower($type);
		switch($type) {
		case 'jpg': case 'jpeg':
			$type='image/jpeg';
			$ext='jpg';
			break;
		case 'gif':
			$type='image/gif';
			$ext='gif';
			break;
		case 'png':
			$type='image/png';
			$ext='png';
			break;
		}
		if(!$_id) $_id=0;
		$id='image'.++$_id.'.'.$ext;
		$this->_parts[]=array('ctype'=>$type,'message'=>base64_encode(file_get_contents($filename)),'name'=>$id,'encoding'=>'base64','id'=>$id);
		return $id;
	}

	public function getImg($id) {
		return '<IMG src="cid:'.$id.'" />';
	}

	/* Отправляет письмо на указанный адрес $email */
	public function send($email) {
		if($this->_smtpHost) return $this->sendSmtp($email);
		$mime='';
		if($this->_from) $mime.=$this->_from;
		if($this->_replyTo) $mime.=$this->_replyTo;
		if($this->_returnPath) $mime.='Return-path: '.$this->_returnPath."\n";
		$mime.="MIME-Version: 1.0\n".$this->_buildMultipart();
		return mail($email,'=?UTF-8?B?'.base64_encode($this->_subject).'?=','',$mime);
	}

	/* Выполняет отправку письма средствами SMTP */
	private function sendSmtp($email) {
		$s='Date: '.date('D, d M Y H:i:s')." UT\r\n"
		.'Subject: =?UTF-8?B?'.base64_encode($this->_subject)."?=\r\n"
		.'To: '.$email."\r\n";
		if($this->_from) $s.=$this->_from;
		if($this->_replyTo) $s.=$this->_replyTo;
		if($this->_returnPath) $s.='Return-path: '.$this->_returnPath."\n";
		$s.="MIME-Version: 1.0\r\n".$this->_buildMultipart();
		if(!$socket=fsockopen($this->_smtpHost,SMTP_PORT,$errno,$errstr,30)) {
			controller::$error=$errno."&lt;br&gt;".$errstr;
			return false;
		}
		if(!$this->_parseAnswer($socket,'220')) return false;
		fputs($socket,'HELO '.$this->_smtpHost."\r\n");
		if(!$this->_parseAnswer($socket,'250')) {
			controller::$error='Ошибка отправки smtp #250';
			return false;
		}
		fputs($socket,"AUTH LOGIN\r\n");
		if(!$this->_parseAnswer($socket,'334')) {
			controller::$error='Ошибка отправки smtp #334-1';
			fclose($socket);
			return false;
		}
		fputs($socket,base64_encode($this->_smtpUser)."\r\n");
		if(!$this->_parseAnswer($socket,'334')) {
			controller::$error='Ошибка отправки smtp #334-2';
			fclose($socket);
			return false;
		}
		fputs($socket,base64_encode($this->_smtpPassword)."\r\n");
		if(!$this->_parseAnswer($socket,'235')) {
			controller::$error='Ошибка отправки smtp #235';
			fclose($socket);
			return false;
		}
		fputs($socket,'MAIL FROM: <'.$this->_smtpEmail.">\r\n");
		if(!$this->_parseAnswer($socket,'250')) {
			controller::$error='Ошибка отправки smtp #250-1';
			fclose($socket);
			return false;
		}
		fputs($socket,'RCPT TO: <'.$email.">\r\n");
		if(!$this->_parseAnswer($socket,'250')) {
			controller::$error='Ошибка отправки smtp #250-2';
			fclose($socket);
			return false;
		}
		fputs($socket,"DATA\r\n");
		if(!$this->_parseAnswer($socket,'354')) {
			controller::$error='Ошибка отправки smtp #354';
			fclose($socket);
			return false;
		}
		fputs($socket,$s."\r\n.\r\n");
		if(!$this->_parseAnswer($socket,'250')) {
			controller::$error='Ошибка отправки smtp #250-3';
			fclose($socket);
			return false;
		}
		fputs($socket,"QUIT\r\n");
		fclose($socket);
		return true;
	}

	private function _buildMultipart() {
		$boundary=md5(uniqid(time()));
		$multipart="Content-Type: multipart/related;\r\n\tboundary=\"".$boundary."\"\r\n\r\nThis is a MIME encoded message.\r\n\r\n--".$boundary;
		$part=array('ctype'=>'text/html; charset="UTF-8"','message'=>$this->_message,'name'=>'');
		$multipart.="\n".$this->_buildMessage($part).'--'.$boundary;
		for($i=count($this->_parts)-1;$i>=0;$i--) $multipart.="\n".$this->_buildMessage($this->_parts[$i]).'--'.$boundary;
		return $multipart.="--\n";
	}

	private function _buildMessage($part) {
		$s='Content-Type: '.$part['ctype'].($part['name'] ? '; name="'.$part['name'].'"' : '')."\r\n".($part['name'] ? 'Content-ID: <'.$part['name'].">\r\n" : '')."Content-Transfer-Encoding: ".(isset($part['encoding']) ? $part['encoding'] : '8bit')."\r\n";
		if(isset($part['filename'])) $s.='Content-Disposition: attachment; filename="'.$part['filename'].'"'."\r\n";
		$s.="\r\n".$part['message']."\r\n\r\n";
		return $s;
	}

	private function _parseAnswer($socket,$response) {
		$serverResponse='';
		while(substr($serverResponse,3,1)!=' ') if(!($serverResponse=fgets($socket,256))) return false;
		if(!(substr($serverResponse,0,3)==$response)) return false;
    return true;
	}

}
?>