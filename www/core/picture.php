<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
/* Класс для обработки изображение (обрезка, сжатие, водный знак) */
class picture {
	private $_src;
	private $_type;
	private $_x1;
	private $_y2;
	private $_srcW;
	private $_srcH;
	private $_dstW;
	private $_dstH;
	private $_w;
	private $_wX;
	private $_wY;
	private $_wW;
	private $_wH;

	/* Открывает файл изображения, проверяет что это действительно изображение
	$filename - имя файла, если массив, то воспринимается как файл из массива $_FILES */
	public function __construct($file) {
		if(is_array($file)) {
			$this->_type=substr($file['type'],strrpos($file['type'],'/')+1);
			$file=$file['tmpName'];
		} else $this->_type=substr($file,strrpos($file,'.')+1);
		$type=strtolower($this->_type);
		switch($type) {
		case 'jpg': case 'jpeg':
			$this->_src=imagecreatefromjpeg($file);
			break;
		case 'gif':
			$this->_src=imagecreatefromgif($file);
			break;
		case 'png':
			$this->_src=imagecreatefrompng($file);
			break;
		default:
			controller::$error=LNGFileIsNotImage;
			return false;
		}
		if(!$this->_src) {
			controller::$error=LNGFileTypeNotSupport;
			return;
		}
		$this->_x1=$this->_y1=0;
		$this->_srcW=imagesx($this->_src);
		$this->_srcH=imagesy($this->_src);
		$this->_dstW=$this->_srcW;
		$this->_dstH=$this->_srcH;
	}

	//Возвращает высоту исходного изображения
	public function height() {
		return $this->_srcH;
	}

	//Возвращает широту исходного изображения
	public function width() {
		return $this->_srcW;
	}

/*
	public function cropByCoord($x1,$y1,$x2=null,$y2=null) { die('crop function not emplemented'); }

	public function cropBySize($x1,$y1,$width=null,$heigh=null) { die('crop function noe emplemented'); }
*/
	/* Сжимает или растягивает изображение до указанных размеров */
	public function resize($width=null,$height=null) {
		if(!$width && !$height) {
			$this->_dstW=$this->_srcW;
			$this->_dstH=$this->_srcH;
			return true;
		}
		if($width) {
			if($width[0]=='<' || $width[0]=='>') $symbolWidth=$width[0]; else $symbolWidth='=';
		} else $symbolWidth='';
		if($height) {
			if($height[0]=='<' || $height[0]=='>') $symbolHeight=$height[0]; else $symbolHeight='=';
		} else $symbolHeight='';
		if(!is_int($width) && !is_int($height)) {
			$w=(int)substr($width,1);
			$h=(int)substr($height,1);
			if($symbolWidth=='<' && $symbolHeight=='<') {
				if(($this->_srcW-$w)>($this->_srcH-$h)) $height=null; else $width=null;
			} elseif($w>$h) $height=null; else $width=null;
		}
		if($symbolWidth=='<') {
			$width=(int)substr($width,1);
			if($this->_srcW<$width) $width=$this->_srcW;
		} elseif($symbolWidth=='>') {
			$width=(int)substr($width,1);
			if($this->_srcW>$width) $width=$this->_srcW;
		}
		if($symbolHeight=='<') {
			$height=(int)substr($height,1);
			if($this->_srcH<$height) $height=$this->_srcH;
		} elseif($symbolHeight=='>') {
			$height=(int)substr($height,1);
			if($this->_srcH>$height) $height=$this->_srcH;
		}
		if(!$width && $height) $width=round($this->_srcW/$this->_srcH*$height);
		elseif(!$height && $width) $height=round($this->_srcH/$this->_srcW*$width);
		$this->_dstW=$width;
		$this->_dstH=$height;
	}

	/* Накладывает водный знак
	$f - имя файла изображения, $x и $y задают отступы от краёв изображения */
	public function watermark($f,$x,$y) {
		$ext=strtolower(substr($f,strrpos($f,'.')+1));
		switch($ext) {
		case 'jpg': case 'jpeg':
			$this->_w=imagecreatefromjpeg($f);
			break;
		case 'gif':
			$this->_w=imagecreatefromgif($f);
			break;
		case 'png':
			$this->_w=imagecreatefrompng($f);
			imagealphablending($this->_w,true);
			break;
		default:
			controller::$error=LNGFileIsNotImage;
			return false;
		}
		$this->_wW=imagesx($this->_w);
		$this->_wH=imagesy($this->_w);
		if($x[0]=='-') {
			$minus=true;
			$x=substr($x,1);
		}else $minus=false;
		if($x[strlen($x)-1]=='%') {
			$x=(int)$x;
			$x=round($this->_dstW/100*$x-($this->_wW/100*$x));
		} else $x=(int)$x;
		if($minus) $this->_wX=$this->_dstW-$this->_wW-$x; else $this->_wX=$x;
		if($y[0]=='-') {
			$minus=true;
			$y=substr($y,1);
		}else $minus=false;
		if($y[strlen($y)-1]=='%') {
			$y=(int)$y;
			$y=round($this->_dstH/100*$y-($this->_wH/100*$y));
		} else $y=(int)$y;
		if($minus) $this->_wY=$this->_dstH-$this->_wH-$y; else $this->_wY=$y;
	}

	/* Выполняет все действия обработки и сохраняет изображение в файл.
	Тип файла определяется расширением $fileName, если оно задано, возвращает имя файба без директория, но с расширением */
	public function save($fileName,$quality=100) {
		$type=strrpos($fileName,'.');
		if($type) $type=strtolower(substr($fileName,$type+1));
		if($type!='jpg' && $type!='jpeg' && $type!='gif' && $type!='png') {
			$type=$this->_type;
			$fileName.='.'.$type;
		}
		$dst=imagecreatetruecolor($this->_dstW,$this->_dstH);
		if($type=='png') {
			$transparent=imagecolorallocatealpha($dst,0,0,0,127);
			imagefill($dst,0,0,$transparent);
			imagecolortransparent($dst,$transparent);
			imagesavealpha($dst,true);
		}
		imagecopyresampled($dst,$this->_src,0,0,$this->_x1,$this->_y1,$this->_dstW,$this->_dstH,$this->_srcW,$this->_srcH);
		if($this->_x1!=0 || $this->_y1!=0 || $this->_srcW!=$this->_dstW || $this->_srcH!=$this->_dstH) {
			$this->_x1=$this->_y1=0;
			$this->_dstW=$this->_srcW;
			$this->_dstH=$this->_srcH;
		}
		if($this->_w) {
			imagecopy($dst,$this->_w,$this->_wX,$this->_wY,0,0,$this->_wW,$this->_wH);
			$this->_w=null;
		}
		switch($type) {
		case 'jpg': case 'jpeg':
			imagejpeg($dst,core::path().$fileName,$quality);
			break;
		case 'gif':
			imagegif($dst,core::path().$fileName);
			break;
		case 'png':
			imagepng($dst,core::path().$fileName);
			break;
		}
		$i=strrpos($fileName,'/');
		if(!$i) return $fileName; else return substr($fileName,$i+1);
	}

}
?>