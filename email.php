<?php
// Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
/* Служит для отправки электронных писем */
class email {

	private $_parts=array();
	private	$_from;
	private $_subject='';
	private $_replyTo;
	private $_message;
	private $_smtpHost;
	private $_smtpPort;
	private $_smtpUser;
	private $_smtpPassword;
	public $_returnPath;
	public $error;

	/* Инициализирует SMTP, если используется режим SMTP */
	public function __construct($host=null,$port=null,$user=null,$password=null) {
		if($host) {
			$this->_smtpHost=$host;
			$this->_smtpPort=$port;
			$this->_smtpUser=$user;
			$this->_smtpPassword=$password;
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
	public function message($value) { $this->_message=trim($value); }

	/* Задаёт текс письма (HTML), используя HTML-шаблон. $data - ассоциативный массив, содержащий данные, которые должны быть подставлены в письмо */
/*
	public function messageTemplate($fileName,$data) {
		if(substr($fileName,0,6)=='admin/') $fileName=core::path().'admin/data/email/'.substr($fileName,6).'.html';
		else $fileName=core::path().'data/email/'.$fileName.'.'._LANG.'.html';
		$this->_message=file_get_contents($fileName);
		$this->_message=str_replace(array('{{siteLink}}','{{siteName}}'),
			array('http://'.$_SERVER['HTTP_HOST'].core::url(),$_SERVER['HTTP_HOST']),
			$this->_message
		);
		foreach($data as $tag=>$value) $this->_message=str_replace('{{'.$tag.'}}',$value,$this->_message);
		$this->_message=trim($this->_message);
	}
*/
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
		$boundary=md5(uniqid(time()));
		$header='';
		if($this->_from) $header.=$this->_from;
		if($this->_replyTo) $header.=$this->_replyTo;
		if($this->_returnPath) $header.='Return-path: '.$this->_returnPath."\n";
		$header.='MIME-Version: 1.0'.PHP_EOL.'Content-Type: multipart/related; boundary="'.$boundary.'"';
		$message=$this->_buildMultipart($boundary);
		if($this->_smtpHost) return $this->sendSmtp($email,$message,$header);
		if(!mail($email,'=?UTF-8?B?'.base64_encode($this->_subject).'?=',$message,$header)) {
			$this->error=LNGCouldnotSendLetter;
			return false;
		}
		return true;
	}

	/* Выполняет отправку письма средствами SMTP */
	private function sendSmtp($email,$message,$header) {
		$s='Date: '.date('D, d M Y H:i:s')." UT\r\n"
		.'Subject: =?UTF-8?B?'.base64_encode($this->_subject)."?=\r\n"
		.'To: '.$email."\r\n";
		$s.=$header.PHP_EOL.PHP_EOL.$message;
		if(!$socket=fsockopen($this->_smtpHost,$this->_smtpPort,$errno,$errstr,30)) {
			$this->error=$errno."&lt;br&gt;".$errstr;
			return false;
		}
		if(!$this->_parseAnswer($socket,'220')) return false;
		if(substr($this->_smtpHost,0,6)=='ssl://') $this->_smtpHost=substr($this->_smtpHost,6);
		fputs($socket,'EHLO '.$this->_smtpHost."\r\n");
		if(!$this->_parseAnswer($socket,'250')) {
			$this->error=LNGSMTPError.' #250';
			return false;
		}
		fputs($socket,"AUTH LOGIN\r\n");
		if(!$this->_parseAnswer($socket,'334')) {
			$this->error=LNGSMTPError.' #334-1';
			fclose($socket);
			return false;
		}
		fputs($socket,base64_encode($this->_smtpUser)."\r\n");
		if(!$this->_parseAnswer($socket,'334')) {
			$this->error=LNGSMTPError.' #334-2';
			fclose($socket);
			return false;
		}
		fputs($socket,base64_encode($this->_smtpPassword)."\r\n");
		if(!$this->_parseAnswer($socket,'235')) {
			$this->error=LNGSMTPError.' #235';
			fclose($socket);
			return false;
		}
		fputs($socket,'MAIL FROM: <'.$this->_smtpUser.">\r\n");
		if(!$this->_parseAnswer($socket,'250')) {
			$this->error=LNGSMTPError.' #250-1';
			fclose($socket);
			return false;
		}
		fputs($socket,'RCPT TO: <'.$email.">\r\n");
		if(!$this->_parseAnswer($socket,'250')) {
			$this->error=LNGSMTPError.' #250-2';
			fclose($socket);
			return false;
		}
		fputs($socket,"DATA\r\n");
		if(!$this->_parseAnswer($socket,'354')) {
			$this->error=LNGSMTPError.' #354';
			fclose($socket);
			return false;
		}
		fputs($socket,$s."\r\n.\r\n");
		if(!$this->_parseAnswer($socket,'250')) {
			$this->error=LNGSMTPError.' #250-3';
			fclose($socket);
			return false;
		}
		fputs($socket,"QUIT\r\n");
		fclose($socket);
		return true;
	}

	private function _buildMultipart($boundary) {
		$multipart='This is a MIME encoded message.'.PHP_EOL.PHP_EOL.'--'.$boundary;
		$part=array('ctype'=>'text/html; charset="UTF-8"','message'=>$this->_message,'name'=>'');
		$multipart.=PHP_EOL.$this->_buildMessage($part).'--'.$boundary;
		for($i=count($this->_parts)-1;$i>=0;$i--) $multipart.="\n".$this->_buildMessage($this->_parts[$i]).'--'.$boundary;
		return $multipart.="--".PHP_EOL;
	}

	private function _buildMessage($part) {
		if(isset($part['encoding']) && $part['encoding']=='base64') {
			$i=0;
			$content='';
			do {
				if($content) $content.="\n";
				$_s=substr($part['message'],$i,76);
				$i+=76;
				$content.=$_s;
			} while(strlen($_s)==76);
		} else $content=$part['message'];
		$s='Content-Type: '.$part['ctype'].($part['name'] ? '; name="'.$part['name'].'"' : '')."\r\n".($part['name'] ? 'Content-ID: <'.$part['name'].">\r\n" : '')."Content-Transfer-Encoding: ".(isset($part['encoding']) ? $part['encoding'] : '8bit').PHP_EOL;
		if(isset($part['filename'])) $s.='Content-Disposition: attachment; filename="'.$part['filename'].'"'.PHP_EOL;
		$s.=PHP_EOL.$content.PHP_EOL.PHP_EOL;
		return $s;
	}

	private function _parseAnswer($socket,$response) {
		$serverResponse='';
		while(substr($serverResponse,3,1)!=' ') if(!($serverResponse=fgets($socket,256))) return false;
		if(!(substr($serverResponse,0,3)==$response)) return false;
    return true;
	}

}