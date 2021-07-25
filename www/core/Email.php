<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\core;

/**
 * Реализует отправку электронных писем
 */
class Email {

	/** @var array Массив MIME-частей письма */
	private $_parts=[];
	/** @var string Заголовок "From" */
	private $_from;
	/** @var string Тема письма */
	private $_subject='';
	/** @var string|null Заголовок "Reply-To" */
	private $_replyTo;
	/** @var string HTML-текст письма */
	private $_message;
	/** @var string|null Адрес SMTP-сервера, если используется метод отправки "SMTP" */
	private $_smtpHost;
	/** @var int|null Порт SMTP-сервера, если используется метод отправки "SMTP" */
	private $_smtpPort;
	/** @var string|null Имя пользователя, если используется метод отправки "SMTP" */
	private $_smtpUser;
	/** @var string|null Пароль пользователя, если используется метод отправки "SMTP" */
	private $_smtpPassword;
	/** @var string|null E-mail для заголовка "Return-path" */
	public $_returnPath;

	public function __construct() {
		$cfg=core::config();
		if(isset($cfg['smtpHost'])===true) {
			$this->_smtpHost=$cfg['smtpHost'];
			$this->_smtpPort=$cfg['smtpPort'];
			$this->_smtpUser=$cfg['smtpUser'];
			$this->_smtpPassword=$cfg['smtpPassword'];
		}
	}

	/**
	 * Настраивает поле "от кого"
	 * @param string      $email Адрес электронной почты отправителя
	 * @param string|null $name  Имя отправителя
	 */
	public function from(string $email,string $name=null): void {
		if($name===null) $name=$email;
		$this->_from='From: =?UTF-8?B?'.base64_encode($name).'?= <'.$email.">\r\n";
	}

	/**
	 * Устанавливает тему письма
	 * @param string $value Тема письма
	 */
	public function subject(string $value): void {
		$this->_subject=$value;
	}

	/**
	 * Устанавливает текст письма в формате HTML
	 * @param string $value Текст письма
	 */
	public function message(string $value): void {
		$this->_message=trim($value);
	}

	/**
	 * Устанавливает текст письма используя HTML-шаблон письма.
	 * @param string   $fileName - относительное имя файла письма ([/admin]/data/email/{$fileName}.html)
	 * @param string[] $data     Данные в формате "ключ-значение" для подстановки в шаблон письма
	 */
	public function messageTemplate(string $fileName,array $data): void {
		if(substr($fileName,0,6)==='admin/') $fileName=core::path().'admin/data/email/'.substr($fileName,6).'.html';
		else $fileName=core::path().'data/email/'.$fileName.'.'._LANG.'.html';
		$this->_message=file_get_contents($fileName);
		$this->_message=str_replace(['{{siteLink}}','{{siteName}}'],
			['http://'.$_SERVER['HTTP_HOST'].core::url(),$_SERVER['HTTP_HOST']],
			$this->_message
		);
		foreach($data as $tag=>$value) $this->_message=str_replace('{{'.$tag.'}}',$value,$this->_message);
		$this->_message=trim($this->_message);
	}

	/**
	 * Устанавливает адрес для возврата письма, если его не удалось доставить адресату
	 * @param string $email E-mail
	 */
	public function returnPath(string $email): void {
		$this->_returnPath=$email;
	}

	/**
	 * Устанавливает получателя ответа на письмо
	 * @param string      $email E-mail
	 * @param string|null $name  Имя получателя
	 */
	public function replyTo(string $email,string $name=null): void {
		if($name===null) $name=$email;
		$this->_replyTo='Reply-To: =?UTF-8?B?'.base64_encode($name).'?= <'.$email.">\r\n";
	}

	/**
	 * Добавляет вложение к письму
	 * @param string $filename Абсолютное имя файла вложения
	 * @param string $id       Псевдоним имени файла
	 * @param string $type     MIME-тип файла
	 */
	public function attach(string $filename,string $id,string $type='application/octet-stream'): void {
		$this->_parts[]=[
			'ctype'=>$type,
			'message'=>base64_encode(file_get_contents($filename)),
			'name'=>$id,
			'encoding'=>'base64',
			'id'=>$id,
			'filename'=>substr($filename,strrpos($filename,'/')+1)
		];
	}

	/**
	 * Добавляет к письму вложенное изображение и возвращает идентификатор, по которому можно вставить это изображение в текст письма
	 * @param string      $filename Абослютное имя файла изображения
	 * @param string|null $type     MIME-тип изображения, если не задан будет определён из имени файла
	 * @return string Псевдоним имени файла
	 * @see email::getImg()
	 */
	public function attachImage(string $filename,string $type=null): string {
		static $_id;
		if($type===null) $type=substr($filename,strrpos($filename,'.')+1);
		$type=strtolower($type);
		switch($type) {
			case 'jpg':
			case 'jpeg':
			case 'image/jpeg':
			default:
				$type='image/jpeg';
				$ext='jpg';
				break;
			case 'gif':
			case 'image/gif':
				$type='image/gif';
				$ext='gif';
				break;
			case 'png':
			case 'image/png':
				$type='image/png';
				$ext='png';
				break;
		}
		if($_id===null) $_id=0;
		$id='image'.++$_id.'.'.$ext;
		$this->_parts[]=[
			'ctype'=>$type,
			'message'=>base64_encode(file_get_contents($filename)),
			'name'=>$id,
			'encoding'=>'base64',
			'id'=>$id
		];
		return $id;
	}

	/**
	 * Возвращает HTML-тег <img>, ссылающийся на вложенное изображение
	 * @param string $id Псевдоним имени файла
	 * @return string
	 * @see self::attachImage()
	 */
	public function getImg(string $id): string {
		return '<IMG src="cid:'.$id.'" />';
	}

	/**
	 * Отправляет сформированное письмо
	 * @param string $email E-mail получателя
	 * @return bool TRUE при успехе и FALSE при неудаче
	 */
	public function send(string $email): bool {
		$boundary=md5(uniqid(time()));
		$header='';
		if($this->_from) $header.=$this->_from;
		if($this->_replyTo) $header.=$this->_replyTo;
		if($this->_returnPath) $header.='Return-path: '.$this->_returnPath."\n";
		$header.='MIME-Version: 1.0'.PHP_EOL.'Content-Type: multipart/related; boundary="'.$boundary.'"';
		$message=$this->_buildMultipart($boundary);
		if($this->_smtpHost) return $this->sendSmtp($email,$message,$header);
		if(!mail($email,'=?UTF-8?B?'.base64_encode($this->_subject).'?=',$message,$header)) {
			core::error(LNGCouldnotSendLetter);
			return false;
		}
		return true;
	}

	/**
	 * Выполняет отправку письма посредством SMTP
	 * @param string $email   E-mail получателя
	 * @param string $message Сформированное письмо
	 * @param string $header  Дополнительные заголовки
	 * @return bool TRUE при успехе и FALSE при неудаче
	 */
	private function sendSmtp(string $email,string $message,string $header): bool {
		$s='Date: '.date('D, d M Y H:i:s')." UT\r\n"
			.'Subject: =?UTF-8?B?'.base64_encode($this->_subject)."?=\r\n"
			.'To: '.$email."\r\n";
		$s.=$header.PHP_EOL.PHP_EOL.$message;
		if(!$socket=fsockopen($this->_smtpHost,$this->_smtpPort,$errno,$errstr,30)) {
			core::error($errno."&lt;br&gt;".$errstr);
			return false;
		}
		if(!$this->_parseAnswer($socket,'220')) return false;
		if(substr($this->_smtpHost,0,6)=='ssl://') $this->_smtpHost=substr($this->_smtpHost,6);
		fputs($socket,'EHLO '.$this->_smtpHost."\r\n");
		if(!$this->_parseAnswer($socket,'250')) {
			core::error(LNGSMTPError.' #250');
			return false;
		}
		fputs($socket,"AUTH LOGIN\r\n");
		if(!$this->_parseAnswer($socket,'334')) {
			core::error(LNGSMTPError.' #334-1');
			fclose($socket);
			return false;
		}
		fputs($socket,base64_encode($this->_smtpUser)."\r\n");
		if(!$this->_parseAnswer($socket,'334')) {
			core::error(LNGSMTPError.' #334-2');
			fclose($socket);
			return false;
		}
		fputs($socket,base64_encode($this->_smtpPassword)."\r\n");
		if(!$this->_parseAnswer($socket,'235')) {
			core::error(LNGSMTPError.' #235');
			fclose($socket);
			return false;
		}
		fputs($socket,'MAIL FROM: <'.$this->_smtpUser.">\r\n");
		if(!$this->_parseAnswer($socket,'250')) {
			core::error(LNGSMTPError.' #250-1');
			fclose($socket);
			return false;
		}
		fputs($socket,'RCPT TO: <'.$email.">\r\n");
		if(!$this->_parseAnswer($socket,'250')) {
			core::error(LNGSMTPError.' #250-2');
			fclose($socket);
			return false;
		}
		fputs($socket,"DATA\r\n");
		if(!$this->_parseAnswer($socket,'354')) {
			core::error(LNGSMTPError.' #354');
			fclose($socket);
			return false;
		}
		fputs($socket,$s."\r\n.\r\n");
		if(!$this->_parseAnswer($socket,'250')) {
			core::error(LNGSMTPError.' #250-3');
			fclose($socket);
			return false;
		}
		fputs($socket,"QUIT\r\n");
		fclose($socket);
		return true;
	}

	private function _buildMultipart(string $boundary): string {
		$multipart='This is a MIME encoded message.'.PHP_EOL.PHP_EOL.'--'.$boundary;
		$part=['ctype'=>'text/html; charset="UTF-8"','message'=>$this->_message,'name'=>''];
		$multipart.=PHP_EOL.$this->_buildMessage($part).'--'.$boundary;
		for($i=count($this->_parts)-1;$i>=0;$i--) $multipart.="\n".$this->_buildMessage($this->_parts[$i]).'--'.$boundary;
		$multipart.='--'.PHP_EOL;
		return $multipart;
	}

	private function _buildMessage(array $part): string {
		if(isset($part['encoding'])===true && $part['encoding']==='base64') {
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
		if(isset($part['filename'])===true) $s.='Content-Disposition: attachment; filename="'.$part['filename'].'"'.PHP_EOL;
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