<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
/* Класс для обработки изображение (обрезка, сжатие, водный знак) */
class picture {
	private $_src; //содержит исходное изображение
	private $_type; //расширение исходного файла, используется при сохранении в файл, если имя файла не содержит расширения
	private $_srcW; //шинира исходного изображения
	private $_srcH; //высота исходного изображения
	private $_dstW; //ширина генерируемого изображения (для масштабирования)
	private $_dstH; //высота генерируемого изображения (для масштабирования)
	private $_x1; //верхний левый угол исходного изображения (для обрезки)
	private $_y1; //верхний левый угол исходного изображения (для обрезки)
	private $_x2; //нижний правый угол исходного изображения (для обрезки)
	private $_y2; //нижний правый угол исходного изображения (для обрезки)

//	private $_dstX;
//	private $_dstY;

	private $_watermark; //содержит изображение водного знака
	private $_wX;
	private $_wY;
	private $_wW;
	private $_wH;

	/* Открывает файл изображения, проверяет что это действительно изображение
	$file - имя файла, массив, экземпляр picture или целое число;
	int $file и int $height - ширина и высота пустого изображения */
	public function __construct($file,$height=null) {
		if($file instanceof picture) {
			$this->_src=$file->gd();
			$this->_srcW=$file->width();
			$this->_srcH=$file->height();
			$this->_type=$file->type();
		} elseif($height!==null) {
			$this->_type='png';
			$this->_src=imagecreatetruecolor($file,$height);
			$this->_srcW=(int)$file;
			$this->_srcH=(int)$height;
			imagesavealpha($this->_src,true);
			$color=imagecolorallocatealpha($this->_src,0,0,0,127);
			imagefill($this->_src,0,0,$color);
		} elseif(is_resource($file)) {
			$this->_src=$file;
			$this->_type='png';
			$this->_srcW=imagesx($this->_src);
			$this->_srcH=imagesy($this->_src);
		} else {
			if(is_array($file)) {
				$this->_type=substr($file['type'],strrpos($file['type'],'/')+1);
				$file=$file['tmpName'];
			} else {
				$file=core::path().$file;
				$this->_type=substr($file,strrpos($file,'.')+1);
			}
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
				core::error(LNGFileIsNotImage.'('.$file.')');
				return false;
			}
			if(!$this->_src) {
				core::error(LNGFileTypeNotSupport.' ('.$file.')');
				return;
			}
			$this->_srcW=imagesx($this->_src);
			$this->_srcH=imagesy($this->_src);
		}
		$this->_x1=$this->_y1=0;
		$this->_x2=$this->_dstW=$this->_srcW;
		$this->_y2=$this->_dstH=$this->_srcH;
	}

	public function __destruct() {
		if($this->_src) unset($this->_src);
	}

	//Возвращает высоту исходного изображения
	public function height() {
		return $this->_srcH;
	}

	//Возвращает широту исходного изображения
	public function width() {
		return $this->_srcW;
	}

	//Возвращает тип изображения
	public function type() {
		return $this->_type;
	}

	//Обрезает исходное изображение по краям по заданной ширине и высоте ($x2===null && $y2===null)
	//Если заданы $x2 и $y2, то все четыре параметра воспринимаются как координаты прямоугольника
	//picture::crop() должен быть вызван до picture::resize()
	public function crop($width=null,$height=null,$x2=null,$y2=null) {
		if($x2===null && $y2===null) { //обрезка по ширине и высоте
			if($width && $width<$this->_srcW) {
				$this->_x1=($this->_srcW-$width)/2;
				$this->_x2=$width+$this->_x1;
			}
			if($height && $height<$this->_srcH) {
				$this->_y1=($this->_srcH-$height)/2;
				$this->_y2=$height+$this->_y1;
			}
		} else { //обрезка по координатам
			if($x2>$this->_srcW) $x2=$this->_srcW;
			if($y2>$this->_srcH) $y2=$this->_srcH;
			$this->_x1=(int)$width;
			$this->_y1=(int)$height;
			$this->_x2=(int)$x2;
			$this->_y2=(int)$y2;
		}
		//если изображение не масшатибровалось, то изменить размеры генерируемого изображения
		if($this->_dstW==$this->_srcW) $this->_dstW=($this->_x2-$this->_x1);
		if($this->_dstH==$this->_srcH) $this->_dstH=($this->_y2-$this->_y1);
		return true;
	}

	/* Сжимает или растягивает изображение до указанных размеров */
	public function resize($width=null,$height=null) {
		$srcW=$this->_x2-$this->_x1;
		$srcH=$this->_y2-$this->_y1;
		if(!$width && !$height) {
			$this->_dstW=$srcW;
			$this->_dstH=$srcH;
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
				if(($srcW-$w)>($srcH-$h)) $height=null; else $width=null;
			} elseif($w>$h) $height=null; else $width=null;
		}
		if($symbolWidth=='<') {
			$width=(int)substr($width,1);
			if($srcW<$width) $width=$srcW;
		} elseif($symbolWidth=='>') {
			$width=(int)substr($width,1);
			if($srcW>$width) $width=$srcW;
		}
		if($symbolHeight=='<') {
			$height=(int)substr($height,1);
			if($srcH<$height) $height=$srcH;
		} elseif($symbolHeight=='>') {
			$height=(int)substr($height,1);
			if($srcH>$height) $height=$srcH;
		}
		if(!$width && $height) $width=round($srcW/$srcH*$height);
		elseif(!$height && $width) $height=round($srcH/$srcW*$width);
		$this->_dstW=$width;
		$this->_dstH=$height;
	}

	/* Накладывает водный знак
	$f - имя файла изображения, экземпляр picture или imageGD; $x и $y задают отступы от краёв изображения */
	public function watermark($f,$x,$y) {
		if($f instanceof picture) {
			$this->_watermark=$f->gd();
		} elseif(is_string($f)) {
			$ext=strtolower(substr($f,strrpos($f,'.')+1));
	    $f=core::path().$f;
			switch($ext) {
			case 'jpg': case 'jpeg':
				$this->_watermark=imagecreatefromjpeg($f);
				break;
			case 'gif':
				$this->_watermark=imagecreatefromgif($f);
				break;
			case 'png':
				$this->_watermark=imagecreatefrompng($f);
				imagealphablending($this->_watermark,true);
				break;
			default:
				core::error(LNGFileIsNotImage);
				return false;
			}
		} else {
			$this->_watermark=$f;
			imagealphablending($this->_watermark,true);
		}
		$this->_wW=imagesx($this->_watermark);
		$this->_wH=imagesy($this->_watermark);
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

	/* Возвращает объект image, содержащий обработанное изображение */
	public function gd() {
		//если размеры изображения совпадают с исходными, то нет надобности дублировать исходное изображение
		if($this->_x1===0 && $this->_y1===0 && $this->_dstW===$this->_srcW && $this->_dstH===$this->_srcH) {
			$dst=$this->_src;
		} else {
			$dst=imagecreatetruecolor($this->_dstW,$this->_dstH);
			$color=imagecolorallocatealpha($dst,0,0,0,127);
			imagefill($dst,0,0,$color);
			if($this->_type=='png') {
				imagealphablending($dst,true);
				imagesavealpha($dst,true);
			}
	//echo 'FROM POINT: ',$this->_x1,'x',$this->_y1,'<br>';echo 'FROM SIZE: ',round($this->_x2-$this->_x1),'x',round($this->_y2-$this->_y1),'<br>';echo 'TO POINT: 0x0<br>';echo 'TO SIZE: ',$this->_dstW,'x',$this->_dstH,'<br>';
			imagecopyresampled($dst,$this->_src,
				0,0, //dst_x, dst_y
				$this->_x1,$this->_y1, //src_x, src_y
				$this->_dstW,$this->_dstH, //dst_w, dst_h
				round($this->_x2-$this->_x1),round($this->_y2-$this->_y1) //src_w, src_h
			);
		}
		//сбросить для последующих операций
		$this->_x1=$this->_y1=0;
		$this->_x2=$this->_dstW=$this->_srcW;
		$this->_y2=$this->_dstH=$this->_srcH;
		if($this->_watermark) {
			imagecopy($dst,$this->_watermark,$this->_wX,$this->_wY,0,0,$this->_wW,$this->_wH);
			$this->_watermark=null;
		}
		return $dst;
	}

	/* Выполняет все действия обработки и сохраняет изображение в файл.
	Тип файла определяется расширением $fileName, если оно задано, возвращает имя файба без директория, но с расширением */
	public function save($fileName,$quality=100) {
		$type=strrpos($fileName,'.');
		if($type) $type=strtolower(substr($fileName,$type+1));
		if($type==='jpg' || $type==='jpeg' || $type==='gif' || $type==='png') {
			$this->_type=$type;
		} else $fileName.='.'.$type;
		$dst=$this->gd();
		switch($this->_type) {
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