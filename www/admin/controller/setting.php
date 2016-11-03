<?php
/* Общие настройки сайта */
class sController extends controller {

	public function right($right,$action) {
		if($action=='Url') if(isset($right['setting.url'])) return true; else return false;
		if(isset($right['setting.core'])) return true; else return false;
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Общие основные настройки сайта */
	public function actionCore() {
		$this->button('?controller=setting&action=cache','delete','Очистить кеш');
		$cfg=core::config();
		if(isset($cfg['smtpHost'])) {
			$method='smtp';
		} else {
			$method='email';
			$cfg['smtpHost']=null;
			$cfg['smtpPort']=null;
			$cfg['smtpUser']=null;
			$cfg['smtpPassword']=null;
		}
		$f=core::form();
		$f->checkbox('debug','Режим отладки',$cfg['debug']);
		$f->text('adminEmailEmail','E-mail администрации',$cfg['adminEmailEmail']);
		$f->text('adminEmailName','Имя администрации (e-mail)',$cfg['adminEmailName']);
		$f->select('method','Метод отправки почты',array(array('smtp','SMTP'),array('email','PHP')),$method);
		$f->text('smtpHost','SMTP хост',$cfg['smtpHost']);
		$f->text('smtpPort','SMTP порт',$cfg['smtpPort']);
		$f->text('smtpUser','SMTP логин',$cfg['smtpUser']);
		$f->text('smtpPassword','SMTP пароль',$cfg['smtpPassword']);
		$f->submit('Сохранить');
		$this->form=$f;
		return 'Core';
	}

	public function actionCoreSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config('_core');
		if(core::error()) return false;
		if(isset($data['debug'])) $cfg->debug=true; else $cfg->debug=false;
		$cfg->adminEmailEmail=$data['adminEmailEmail'];
		$cfg->adminEmailName=$data['adminEmailName'];
		if($data['method']=='smtp') {
			$cfg->smtpHost=$data['smtpHost'];
			$cfg->smtpPort=$data['smtpPort'];
			$cfg->smtpUser=$data['smtpUser'];
			$cfg->smtpPassword=$data['smtpPassword'];
		} else {
			$cfg->delete('smtpHost');
			$cfg->delete('smtpPort');
			$cfg->delete('smtpUser');
			$cfg->delete('smtpPassword');
		}
		if(!$cfg->save('_core')) return false;
		core::redirect('?controller=setting','Изменения сохранены');
	}

	/* Настройка подмены ссылок (настройка ЧПУ) */
	public function actionUrl() {
		$cfg=core::config();
		$link='';
		foreach($cfg['link'] as $src=>$dst) $link.=$src.'='.$dst."\n";
		$f=core::form();
		$f->text('mainPath','Главная страница (относительный url)',$cfg['mainPath']);
		$f->textarea('link','Преобразование URL',$link);
		$f->submit('Сохранить');
		$this->cite='Здесь вы можете изменить вид ссылок на страницы сайта. Впишите строки вида: <b>реальная_ссыла</b>=<b>короткая_ссылка</b>, например: <b>article/view/service</b>=<b>service</b> (теперь страница http://example.com/article/view/service будет доступна по адресу http://example.com/service).';
		return $f;
	}

	public function actionUrlSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config('_core');
		$cfg->mainPath=$data['mainPath'];
		$link=explode("\n",$data['link']);
		$cnt=count($link);
		$newLink=array();
		for($i=0;$i<$cnt;$i++) {
			$item=explode('=',$link[$i]);
			if(count($item)!=2) continue;
			$item[0]=trim($item[0]);
			if($item[0][0]=='/') $item[0]=substr($item[0],1);
			$newLink[$item[0]]=trim($item[1]);
		}
		$cfg->link=$newLink;
		$cfg->save('_core');
		core::redirect('?controller=setting&action=url','Изменения сохранены');
	}

	/* Очищает весь кеш */
	public function actionCache() {
		$this->_clearFolder(core::path().'cache/');
		core::redirect('?controller=setting','Кеш очищен');
	}
/* ----------------------------------------------------------------------------------- */

	/* Рекурсивно удаляет все файлы из указанного директория */
	private static function _clearFolder($path) {
		$d=opendir($path);
		while($f=readdir($d)) {
			if($f=='.' || $f=='..') continue;
			$f=$path.$f;
			if(is_dir($f)) self::_clearFolder($f.'/');
			else unlink($f);
		}
		closedir($d);
	}

}
?>