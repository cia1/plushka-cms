<?php
namespace plushka\controller;
use plushka;
use plushka\core\Picture;

/* ЧПУ: /demotivator (actionIndex) - список демотиваторов
	/demotivator/construct (actionConstruct) - конструктор демотиваторов (шаг 1 и шаг 2)
	/demotivator/rename/ИД (actionRename) - конструктор демотиваторов (шаг 3)
	/demotivator/success/ИД (actionSuccess) - сообщение об успешно созданном демотиваторе
*/
class DemotivatorController extends \plushka\core\Controller {

	public function __construct() {
		parent::__construct();
		plushka::language('demotivator');
	}

	/* Список демотиваторов */
	public function actionIndex() {
		$db=plushka::db();
		$db->query('SELECT * FROM demotivator WHERE status=1 ORDER BY date DESC');
		$this->items=array();
		$url=plushka::url().'public/demotivator/';
		while($item=$db->fetchAssoc()) {
			$item['date']=date('d.m.Y H:i',$item['date']);
			$item['image']=$url.$item['image'];
			$this->items[]=$item;
		}
		$this->pageTitle=$this->metaTitle='Демотиваторы';
		$this->css('demotivator');
		$this->js('shadowbox/shadowbox');
		$this->css('../js/shadowbox/shadowbox');
		return 'Index';
	}

	protected function breadcrumbIndex() {
		return array('{{pageTitle}}');
	}

	public function adminIndexLink() {
		return array(
			array('demotivator.moderate','?controller=demotivator&action=hidden','list','Не опубликованные')
		);
	}

	public function adminIndexLink2($data) {
		return array(
			array('demotivator.moderate','?controller=demotivator&action=edit&id='.$data['id'],'edit','Изменить'),
			array('demotivator.moderate','?controller=demotivator&action=delete&id='.$data['id'],'delete','Удалить','Удалить','if(!confirm(\'Подтвердите удаление.\')) return false;'),
		);
	}

	/* Конструктор демотиватора (шаг 1 и шаг 2 из 3х) */
	public function actionConstruct() {
		unset($_SESSION['demId']);
		$this->cfg=plushka::config('demotivator');
		if(!isset($this->image)) { //шаг 2 (ввод надписи)
			if($this->cfg['userGroup'] && $this->cfg['userGroup']=plushka::userGroup()) plushka::redirect('user/login');
			$f=plushka::form();
			$f->file('image',LNGUploadImage);
			$f->submit(LNGContinue,'submit');
			$view=$f;
		} else { //шаг 1 (загрузка изображения)
			$s=getimagesize(plushka::path().'public/demotivator/tmp/'.$this->image);
			$_SESSION['demImage']=array(
				'file'=>$this->image,
				'width'=>$s[0],
				'height'=>$s[1]
			);
			$this->imageWidth=$s[0];
			$this->defaultInputStyle='font-family:'.$this->cfg['fontFamily'].';font-size:'.$this->cfg['fontSize'].'px;color:#'.$this->cfg['textColor'].';';
			$view='Construct2';
			$this->js('jquery.min');
		}

		$this->pageTitle=$this->metaTitle=LNGDesigner;
		$this->css('demotivator');
		return $view;
	}

	public function actionConstructSubmit($data) {
		if(!isset($data['step2'])) return $this->_construct1($data); //шаг 2
		else return $this->_construct2($data); //шаг 1
	}

	public function breadcrumbConstruct() {
		return array('<a href="'.plushka::link('demotivator').'">'.LNGDemotivators.'</a>','{{pageTitle}}');
	}

	public function adminConstructLink() {
		return array(
			array('demotivator.setting','?controller=demotivator&action=setting','setting','Настройки')
		);
	}

	/* Конструктор демотиватора, шаг 3 из 3х (имя автора, название) */
	public function actionRename() {
		if(!isset($_SESSION['demId'])) plushka::redirect('demotivator/construct');
		$f=plushka::form();
		$f->hidden('id',$id);
		if(!plushka::userId()) $f->text('author',LNGAuthorName);
		$f->text('title',LNGDemotivatorName);
		$f->submit();
		$this->f=$f;
		$this->img=(int)$this->url[2].'.jpg';

		$this->pageTitle=$this->metaTitle=LNGDemotivatorCreating;
		$this->css('demotivator');
		return 'Rename';
	}

	public function actionRenameSubmit($data) {
		if(!isset($_SESSION['demId'])) plushka::redirect('demotivator/construct');
		$data['id']=$_SESSION['demId'];
		if(plushka::userId()) {
			$u=plushka::user();
			$data['author']=$u->login;
		}
		$m=plushka::model('demotivator');
		$data['status']=true;
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'author'=>array('string'),
			'title'=>array('string',LNGDemotivatorname,true,'min'=>2),
			'status'=>array('boolean')
		))) return false;
		plushka::redirect('demotivator/success/'.$this->url[2]);
	}

	/* Сообщение об успешно созданном демотиваторе, ссылка на картинку */
	public function actionSuccess() {
		$this->link=plushka::url().'public/demotivator/'.(int)$this->url[2].'.jpg';
		$this->pageTitle=$this->metaTitle=LNGDemotivatorCreated;
		return 'Success';
	}

	public function breadcrumbSuccess() {
		return array('<a href="'.plushka::link('demotivator').'">'.LNGDemotivators.'</a>','{{pageTitle}}');
	}


	/* --- PRIVATE --- */

	/* submit-действие (шаг 1) */
	private function _construct1($data) {
		$path='public/demotivator/tmp/';
		//Предварительно удалить устаревшие файлы чтобы не накапливать мусор
		$d=opendir(plushka::path().$path);
		while($f=readdir($d)) {
			if($f=='.' || $f=='..') continue;
			$t=filectime($path.$f);
			if($t<(time()-600)) unlink($path.$f);
		}
		closedir($d);
		$p=new Picture($data['image']);
		if(plushka::error()) return false;
		$this->cfg=plushka::config('demotivator');
		$p->resize('<'.$this->cfg['imageWidthMax'],null);
		$f=mktime();
		$this->image=$p->save($path.$f);
	}

	/* submit-действие (шаг 2) */
	private function _construct2($data) {
		$cfg=plushka::config('demotivator');
		$data['size']=explode('|',$data['size']);
		$fontFile=plushka::path().'data/'.strtolower(str_replace(' ','-',$cfg['fontFamily'])).'.ttf';

		//предварительно расчитать размеры для надписи
		$textWidth=$textHeight=array();
		$textHeightTotal=0;
		for($i=0,$cnt=count($data['text']);$i<$cnt;$i++) {
			$data['size'][$i]=(int)$data['size'][$i];
			$fs=$data['size'][$i];
			$text=$data['text'][$i];
			$xyz=imagettfbbox($fs,0,$fontFile,$text);
			$textWidth[]=$xyz[2]-$xyz[0];
			$textHeightTotal+=$textHeight[]=$xyz[1]-$xyz[7];
			if($textHeightTotal) $textHeightTotal+=$textHeight[$i]*0.5;
		}
		$fullWidth=$_SESSION['demImage']['width']+$cfg['paddingX']*2;
		$fullHeight=$_SESSION['demImage']['height']+$cfg['paddingTop']+$cfg['paddingBottom']+$textHeightTotal+20;
		$ext=substr($_SESSION['demImage']['file'],strrpos($_SESSION['demImage']['file'],'.')+1);
		if($ext=='jpeg' || $ext=='jpg') $src=imagecreatefromjpeg(plushka::path().'public/demotivator/tmp/'.$_SESSION['demImage']['file']);
		elseif($ext=='png') $src=imagecreatefrompng(plushka::path().'public/demotivator/tmp/'.$_SESSION['demImage']['file']);
		elseif($ext=='gif') $src=imagecreatefromgif(plushka::path().'public/demotivator/tmp/'.$_SESSION['demImage']['file']);
		//создать рисунок
		$dst=imagecreatetruecolor($fullWidth,$fullHeight);
		$c=imagecolorallocate($dst,intval($cfg['backgroundColor'][0].$cfg['backgroundColor'][1],16),intval($cfg['backgroundColor'][2].$cfg['backgroundColor'][3],16),intval($cfg['backgroundColor'][4].$cfg['backgroundColor'][5],16));
		imagecolortransparent($dst,$c);
		imagefill($dst,0,0,$c);
		imagecopy($dst,$src,$cfg['paddingX'],$cfg['paddingTop'],0,0,$_SESSION['demImage']['width'],$_SESSION['demImage']['height']);
		unset($src);
		$offsetY=$_SESSION['demImage']['height']+$cfg['paddingTop']+20;
		$c=imagecolorallocate($dst,intval($cfg['textColor'][0].$cfg['textColor'][1],16),intval($cfg['textColor'][2].$cfg['textColor'][3],16),intval($cfg['textColor'][4].$cfg['textColor'][5],16));
		for($i=0,$cnt=count($data['text']);$i<$cnt;$i++) {
			$offsetY+=$textHeight[$i];
			imagettftext($dst,$data['size'][$i],0,($fullWidth-$textWidth[$i])/2,$offsetY,$c,$fontFile,$data['text'][$i]);
			$offsetY+=$textHeight[$i]*0.5;
		}
		//наложить водный знак (текст)
		if($cfg['watermarkText']) {
			$c=imagecolorallocate($dst,intval($cfg['watermarkColor'][0].$cfg['watermarkColor'][1],16),intval($cfg['watermarkColor'][2].$cfg['watermarkColor'][3],16),intval($cfg['watermarkColor'][4].$cfg['watermarkColor'][5],16));
			imagettftext($dst,9,90,$fullWidth-5,$fullHeight-5,$c,$fontFile,$text);
		}
		$id=mktime();
		imagejpeg($dst,plushka::path().'public/demotivator/'.$id.'.jpg',100);
		unlink(plushka::path().'public/demotivator/tmp/'.$_SESSION['demImage']['file']);
		unset($_SESSION['demImage']);
		//добавить в галлерею
		$db=plushka::db();
		$db->insert('demotivator',array(
			'title'=>'',
			'image'=>$id.'.jpg',
			'date'=>$id,
			'status'=>0
		));
		$_SESSION['demId']=$db->insertId();
		plushka::redirect('demotivator/rename/'.$id);
	}

}
?>