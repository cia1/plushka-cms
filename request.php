<?php class request {

	private $_ch;
	private $_charset;
	private $_cookie=array(); //содержит массив кукисов или имя файла, если был вызван self::cookieFile
	private $_referer;
	private $_head;
	private $_content;
	private $_custom; //для дополнительных произвольных заголовков
	private $_verbose;

	public function __construct($charset='UTF-8',$userAgent=null) {
		$this->_charset=strtoupper($charset);
		if($this->_charset=='WINDOWS-1251' || $cthis->_harset=='CP-1251') $this->_charset='CP1251';
		$this->_ch=curl_init();
		curl_setopt($this->_ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($this->_ch,CURLOPT_TIMEOUT,120);
		curl_setopt($this->_ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->_ch,CURLOPT_HEADER,1);
		if(!$userAgent && isset($_SERVER['HTTP_USER_AGENT'])) $userAgent=$_SERVER['HTTP_USER_AGENT'];
		if($userAgent) {
			@curl_setopt($this->_ch,CURLOPT_USERAGENT,$userAgent);
		}
		$this->_custom=array();
	}

	public function __destruct() {
		curl_close($this->_ch);
		if($this->_verbose) fclose($this->_verbose);
	}

	public function verbose($value) {
 		if($value) curl_setopt($this->_ch,CURLOPT_VERBOSE,1); else curl_setopt($this->_ch,CURLOPT_VERBOSE,0);
		if(is_string($value)) {
			$this->_verbose=fopen($value,'a');
			curl_setopt($this->_ch,CURLOPT_STDERR,$this->_verbose);
		}
	}

	public function cookieFile($fname) {
		if(!$fname) $this->_cookie=array();
		else {
			$this->_cookie=$fname;
			curl_setopt($this->_ch,CURLOPT_COOKIEJAR,$fname);
			curl_setopt($this->_ch,CURLOPT_COOKIEFILE,$fname);
		}
	}

	//Добавляет произвольный заголовок
	public function custom($name,$value,$forewer=false) {
		if($value===false) foreach($this->_custom as $i=>$item) {
			if($item[0]==$name) {
				unset($this->_custom[$i]);
				return true;
			}
			return false;
		}
		$this->_custom[]=array($name,$value,$forewer);
	}

	//Выполняет GET-запрос
	public function get($url,$redirect=true) {
		curl_setopt($this->_ch,CURLOPT_POST,0);
		return $this->_exec($url,$redirect);
	}

	//Выполняет GET-запрос и возвращает не обработанные данные (для бинарных ответов, которые почему-то не удаётся распарсить)
	public function getSource($url,$tmpFile=null) {
		curl_setopt($this->_ch,CURLOPT_POST,0);
		curl_setopt($this->_ch,CURLOPT_HEADER,0);
		if($tmpFile && !is_string($this->_cookie)) curl_setopt($this->_ch,CURLOPT_COOKIEJAR,$tmpFile);
		$this->_prepare($url);
		$this->_content=curl_exec($this->_ch);
		curl_setopt($this->_ch,CURLOPT_HEADER,1);
		if($tmpFile && !is_string($this->_cookie)) {
			$f=file($tmpFile);
			for($i=4,$cnt=count($f);$i<$cnt;$i++) {
				$s=explode("\t",$f[$i]);
				$this->_cookie[$s[5]]=trim($s[6]);
			}
			unlink($tmpFile);
		}
		return $this->_content;
	}

	//Выполняет POST-запрос
	public function post($url,$data,$redirect=true) {
		if(is_array($data)) $data=http_build_query($data);
		curl_setopt($this->_ch,CURLOPT_POST,1);
		curl_setopt($this->_ch,CURLOPT_POSTFIELDS,$data);
		return $this->_exec($url,$redirect);
	}

	//Возвращает "сырые" заголовки ответа
	public function head($data=null) {
		if($data) curl_setopt($this->_ch,CURLOPT_HTTPHEADER,$data);
		return $this->_head;
	}

	//Возвращает содержимое ответа
	public function content($convert=true) {
		if(!$convert) return $this->_content;
		$i=preg_match('/Content-Type:.+charset\s*?=\s*?[\'"]?([a-zA-Z0-9-]+)/',$this->_head,$s);
		if($i) $charset=trim(strtoupper($s[1]));
		else {
			$i=preg_match('/<meta\s+http-equiv="Content-Type"[^>]+charset=([a-z0-9-]+)[\'"]?/is',$this->_content,$s);
			if(!$i) $charset='UTF-8';
			else $charset=strtoupper($s[1]);
		}
		if($charset=='WINDOWS-1251' || $charset=='CP-1251') $charset='CP1251';
		if($charset==$this->charset) return $this->_content;
		return iconv($charset,$this->_charset.'//IGNORE',$this->_content);
	}

	public function session($data=null) {
		if(isset($data['charset'])) $this->_charset=$data['charset'];
		if(isset($data['cookie'])) $this->_cookie=$data['cookie'];
		if(isset($data['referer'])) $this->_referer=$data['referer'];
		if(isset($data['custom'])) $this->_custom=$data['custom'];
		return array(
			'charset'=>$this->_charset,
			'cookie'=>$this->_cookie,
			'referer'=>$this->_referer,
			'custom'=>$this->_custom
		);
	}

	//Очищает кукисы и реферер
	public function clear() {
		if(is_string($this->_cookie) && file_exists($this->_cookie)) unlink($this->_cookie);
		else $this->_cookie=array();
		$this->_referer=null;
		$this->_custom=array();
	}

	//Устанавливает или возвращает кукисы
	public function cookie($data=null) {
		if(is_string($this->_cookie)) return null;
		if($data) {
			if(is_array($data)) {
				foreach($data as $key=>$value) $data[$key]=urlencode($value);
			}
			$this->_cookie=$data;
		}
		return $this->_cookie;
	}

	//выполняет get или post запрос
	private function _exec($url,$redirect=true) {
		$this->_prepare($url);
		$data=curl_exec($this->_ch);
		if(!$data) return false;
		$this->_head=substr($data,0,curl_getinfo($this->_ch,CURLINFO_HEADER_SIZE));
		$this->_head=str_replace("HTTP/1.1 100 Continue\r\n",'',$this->_head);
		if(is_array($this->_cookie)) { //вырезать кукисы, если они хранятся в памяти
			$i=preg_match_all("/Set-Cookie: (.*?)=(.*?);/i",$this->_head,$s);
			if($i) { //тут надо ещё проверить какие кукисы убиваются, тоесть проверить время.
				foreach($s[1] as $key=>$value) $this->_cookie[$value]=$s[2][$key];
			}
		}
		if($redirect) {
			$i=preg_match('/Location: (.+)/',$this->_head,$s);
			if($i) {
				$s=trim($s[1]);
				return $this->get($s,true);
			}
		}
		$this->_content=substr($data,curl_getinfo($this->_ch,CURLINFO_HEADER_SIZE));
		$s=trim(substr($this->_head,strpos($this->_head,'HTTP/1.1 ')+9,5));
		return (int)$s;
	}

	//Подготавливает данные для выполнения curl_exec, нужен для self::getSourlse
	private function _prepare($url) {
		if(is_array($this->_cookie)) { //если хранятся не в файлах
			$cookie='';
			foreach($this->_cookie as $key=>$value) {
				if($cookie) $cookie.='; ';
				$cookie.=$key.'='.$value;
			}
			curl_setopt($this->_ch,CURLOPT_COOKIE,$cookie);
		}
		if(!$url || $url=='#') $url=$this->_referer;
		elseif($url[0]=='/' || $url[0]=='?') $url=substr($this->_referer,0,strpos($this->_referer,'/',7)).$url;
		curl_setopt($this->_ch,CURLOPT_URL,$url);
		if($this->_referer) curl_setopt($this->_ch,CURLOPT_REFERER,$this->_referer);
		$this->_referer=$url;
		if($this->_custom) {
			$custom=array();
			foreach($this->_custom as $item) $custom[]=$item[0].': '.$item[1];
			curl_setopt($this->_ch,CURLOPT_HTTPHEADER,$custom);
			unset($custom);
			foreach($this->_custom as $i=>$item) {
				if(!$item[2]) unset($this->_custom[$i]);
			}
		}

	}

} ?>