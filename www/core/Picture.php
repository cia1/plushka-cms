<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\core;

/**
 * Предназначен для обработки изображений
 */
class Picture {

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
	private $_watermark; //содержит изображение водного знака
	private $_wX;
	private $_wY;
	private $_wW;
	private $_wH;

	/**
	 * Открывает файл изображения, проверяет что это действительно изображение
	 * @param string|array|picture|int $fileOrWidth Имя файла (string), файл из $_FILES (array) или ширина в пикселях (int)
	 * @param integer|null             $height      Ширина изображения, если создаётся новое
	 */
	public function __construct($fileOrWidth,int $height=null) {
		if($fileOrWidth instanceof self) {
			$this->_src=$fileOrWidth->gd();
			$this->_srcW=$fileOrWidth->width();
			$this->_srcH=$fileOrWidth->height();
			$this->_type=$fileOrWidth->type();
		} elseif($height!==null) {
			$this->_type='png';
			$this->_src=imagecreatetruecolor($fileOrWidth,$height);
			$this->_srcW=(int)$fileOrWidth;
			$this->_srcH=(int)$height;
			imagesavealpha($this->_src,true);
			$color=imagecolorallocatealpha($this->_src,0,0,0,127);
			imagefill($this->_src,0,0,$color);
		} elseif(is_resource($fileOrWidth)===true) {
			$this->_src=$fileOrWidth;
			$this->_type='png';
			$this->_srcW=imagesx($this->_src);
			$this->_srcH=imagesy($this->_src);
		} else {
			if(is_array($fileOrWidth)===true) {
				$this->_type=substr($fileOrWidth['type'],strrpos($fileOrWidth['type'],'/')+1);
				$fileOrWidth=$fileOrWidth['tmpName'];
			} else {
				$fileOrWidth=core::path().$fileOrWidth;
				$this->_type=substr($fileOrWidth,strrpos($fileOrWidth,'.')+1);
			}
			$type=strtolower($this->_type);
			switch($type) {
				case 'jpg':
				case 'jpeg':
					$this->_src=imagecreatefromjpeg($fileOrWidth);
					break;
				case 'gif':
					$this->_src=imagecreatefromgif($fileOrWidth);
					break;
				case 'png':
					$this->_src=imagecreatefrompng($fileOrWidth);
					break;
				default:
					core::error(LNGFileIsNotImage.'('.$fileOrWidth.')');
					return;
			}
			if(!$this->_src) {
				core::error(LNGFileTypeNotSupport.' ('.$fileOrWidth.')');
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

	/**
	 * Возвращает исходную высоту изображения
	 * @return integer
	 */
	public function height(): int {
		return $this->_srcH;
	}

	/**
	 * Возвращает исходную широту изображения
	 * @return integer
	 */
	public function width(): int {
		return $this->_srcW;
	}

	/**
	 * Возвращает тип изображения
	 * @return string
	 */
	public function type(): string {
		return $this->_type;
	}

	/**
	 * Обрезает исходное изображение по краям.
	 * Если заданы только $width и $height, то обрезает по краям до указанных размеров. Если заданы все четыре параметра, то они воспринимаются как координаты (в пикселях) вырезаемого прямоугольника.
	 * self::crop() должен быть вызван до self::resize().
	 * @param int|null $width  ширина изображения
	 * @param int|null $height высота изображения
	 * @param int|null $x2     отступ по оси X второй точки вырезаемой области
	 * @param int|null $y2     отсут по оси Y второй точки вырезаемой области
	 */
	public function crop(int $width=null,int $height=null,int $x2=null,int $y2=null): void {
		if($x2===null && $y2===null) { //обрезка по ширине и высоте
			if($width>0 && $width<$this->_srcW) {
				$this->_x1=($this->_srcW-$width)/2;
				$this->_x2=$width+$this->_x1;
			}
			if($height>0 && $height<$this->_srcH) {
				$this->_y1=($this->_srcH-$height)/2;
				$this->_y2=$height+$this->_y1;
			}
		} else { //обрезка по координатам
			if($x2>$this->_srcW) $x2=$this->_srcW;
			if($y2>$this->_srcH) $y2=$this->_srcH;
			$this->_x1=$width;
			$this->_y1=$height;
			$this->_x2=$x2;
			$this->_y2=$y2;
		}
		//если изображение не масшатибровалось, то изменить размеры генерируемого изображения
		if($this->_dstW===$this->_srcW) $this->_dstW=($this->_x2-$this->_x1);
		if($this->_dstH===$this->_srcH) $this->_dstH=($this->_y2-$this->_y1);
	}

	/**
	 * Сжимает или растягивает изображение до указанных размеров
	 * Размер может быть указан в виде строки с приставкой "<" (не больше) или ">" (не меньше)
	 * @param int|string|null $width  Ширина изображения
	 * @param int|string|null $height Высота изображения
	 */
	public function resize($width=null,$height=null): void {
		$srcW=$this->_x2-$this->_x1;
		$srcH=$this->_y2-$this->_y1;
		if($width===null && $height===null) {
			$this->_dstW=$srcW;
			$this->_dstH=$srcH;
			return;
		}
		if($width!==null) {
			if($width[0]==='<' || $width[0]==='>') $symbolWidth=$width[0]; else $symbolWidth='=';
		} else $symbolWidth='';
		if($height!==null) {
			if($height[0]==='<' || $height[0]==='>') $symbolHeight=$height[0]; else $symbolHeight='=';
		} else $symbolHeight='';
		if(is_int($width)===false && is_int($height)===false) {
			$w=(int)substr($width,1);
			$h=(int)substr($height,1);
			if($symbolWidth==='<' && $symbolHeight==='<') {
				if(($srcW-$w)>($srcH-$h)) $height=null; else $width=null;
			} elseif($w>$h) $height=null;
			else $width=null;
		}
		if($symbolWidth==='<' && $width!==null) {
			$width=(int)substr($width,1);
			if($srcW<$width) $width=$srcW;
		} elseif($symbolWidth==='>') {
			$width=(int)substr($width,1);
			if($srcW>$width) $width=$srcW;
		}
		if($symbolHeight==='<' && $height!==null) {
			$height=(int)substr($height,1);
			if($srcH<$height) $height=$srcH;
		} elseif($symbolHeight==='>') {
			$height=(int)substr($height,1);
			if($srcH>$height) $height=$srcH;
		}
		if($width===null && $height!==null) $width=round($srcW/$srcH*$height);
		elseif($height===null && $width!==null) $height=round($srcH/$srcW*$width);
		$this->_dstW=$width;
		$this->_dstH=$height;
	}

	/**
	 * Накладывает водный знак
	 * Если $x и $y - это строка с приставкой "-", то отступ будет отсчитываться от правого и нижнего краёв соответственно. Параметры $x и $y могут быть указаны в процентах.
	 * @param string|Picture|resource Изображение водного знака
	 * @param int|string $x Местоположение по оси X
	 * @param int|string $y Местоположение по оси Y
	 * @return bool Успешно ли загружено изображение
	 */
	public function watermark($image,$x,$y): bool {
		if($image instanceof Picture) {
			$this->_watermark=$image->gd();
		} elseif(is_string($image)===true) {
			$ext=strtolower(substr($image,strrpos($image,'.')+1));
			$image=core::path().$image;
			switch($ext) {
				case 'jpg':
				case 'jpeg':
					$this->_watermark=imagecreatefromjpeg($image);
					break;
				case 'gif':
					$this->_watermark=imagecreatefromgif($image);
					break;
				case 'png':
					$this->_watermark=imagecreatefrompng($image);
					imagealphablending($this->_watermark,true);
					break;
				default:
					core::error(LNGFileIsNotImage);
					return false;
			}
		} else {
			$this->_watermark=$image;
			imagealphablending($this->_watermark,true);
		}
		$this->_wW=imagesx($this->_watermark);
		$this->_wH=imagesy($this->_watermark);
		if($x[0]==='-') {
			$minus=true;
			$x=substr($x,1);
		} else $minus=false;
		if($x[strlen($x)-1]==='%') {
			$x=(int)$x;
			$x=round($this->_dstW/100*$x-($this->_wW/100*$x));
		} else $x=(int)$x;
		if($minus===true) $this->_wX=$this->_dstW-$this->_wW-$x; else $this->_wX=$x;
		if($y[0]==='-') {
			$minus=true;
			$y=substr($y,1);
		} else $minus=false;
		if($y[strlen($y)-1]==='%') {
			$y=(int)$y;
			$y=round($this->_dstH/100*$y-($this->_wH/100*$y));
		} else $y=(int)$y;
		if($minus===true) $this->_wY=$this->_dstH-$this->_wH-$y; else $this->_wY=$y;
		return true;
	}

	/**
	 * Возвращает объект image, содержащий обработанное изображение
	 * @return resource
	 */
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

	/**
	 * Выполняет все действия обработки и сохраняет изображение в файл
	 * Имя файла может не содержать расширения файла
	 * @param string   $fileName Имя файла
	 * @param int|null $quality  Коэфициент качества для формата JPEG
	 * @return string краткое имя файла сохранённого изображения
	 */
	public function save(string $fileName,int $quality=100): string {
		$type=strrpos($fileName,'.');
		if($type!==false) $type=strtolower(substr($fileName,$type+1));
		if($type==='jpg' || $type==='jpeg' || $type==='gif' || $type==='png') {
			$this->_type=$type;
		} else $fileName.='.'.$type;
		$dst=$this->gd();
		switch($this->_type) {
			case 'jpg':
			case 'jpeg':
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
		if($i===false) return $fileName; else return substr($fileName,$i+1);
	}

}
