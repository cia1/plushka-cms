<?php class request {

	private $_ch;
	private $_charset;
	private $_cookie;
	private $_referer;
	private $_head;
	private $_content;

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
	}

	//Выполняет GET-запрос
	public function get($url,$redirect=true) {
		curl_setopt($this->_ch,CURLOPT_POST,0);
		return $this->_exec($url,$redirect);
	}

	//Выполняет POST-запрос
	public function post($url,$data,$redirect=true) {
		if(is_array($post)) $post=http_build_query($post);
		curl_setopt($this->_ch,CURLOPT_POST,1);
		curl_setopt($this->_ch,CURLOPT_POSTFIELDS,$post);
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
			$i=preg_match('/<meta\s+http-equiv="Content-Type"[^>]+charset=([a-z0-9-]+)[\'"]?/is',$data,$s);
			if(!$i) $charset='UTF-8';
			else $charset=strtoupper($s[1]);
		}
		if($charset=='WINDOWS-1251' || $charset=='CP-1251') $charset='CP1251';
		if($charset==$this->charset) return $this->_content;
		return iconv($charset,$this->_charset.'//IGNORE',$this->_content);
	}

	//Очищает кукисы и реферер
	public function clear() {
		$this->_cookie=array();
		$this->_referer=null;
	}

	//Устанавливает или возвращает кукисы
	public function cookie($data=null) {
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
		curl_setopt($this->_ch,CURLOPT_COOKIE,$this->_cookie);
		if(!$url || $url=='#') $url=$this->_referer;
		elseif($url[0]=='/' || $url[0]=='?') $url=substr($this->_referer,0,strpos($this->_referer,'/',7)).$url;
		curl_setopt($this->_ch,CURLOPT_URL,$url);
		if($this->_referer) curl_setopt($this->_ch,CURLOPT_REFERER,$this->_referer);
		$this->_referer=$url;

		$data=curl_exec($this->_ch);
		if(!$data) {
//			controller::$error='Страница недоступна';
			return false;
		}

		$this->_head=substr($data,0,curl_getinfo($this->_ch,CURLINFO_HEADER_SIZE));
		$this->_head=str_replace("HTTP/1.1 100 Continue\r\n",'',$this->_head);
		$i=preg_match_all("/Set-Cookie: (.*?)=(.*?);/i",$this->_head,$s);
		if($i) { //тут надо ещё проверить какие кукисы убиваются, тоесть проверить время.
			foreach($s[1] as $key=>$value) $this->_cookie[$value]=$s[2][$key];
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

} ?>