<?php
namespace plushka\admin\controller;
use plushka\admin\core\Config;
use plushka\admin\core\Controller;
use plushka\admin\core\FormEx;
use plushka\admin\core\plushka;

/**
 * Общие настройки сайта
 *
 * `/admin/setting/core` - основные настройки сайта
 * `/admin/setting/url` - настройка подмены ссылок
 * `/admin/setting/cache` - очистка кеша
 *
 * @property-read FormEx $form (actionCore)
 */
class SettingController extends Controller {

	public function right(): array {
		return [
			'core'=>'setting.core',
			'url'=>'setting.url',
			'cache'=>'*'
		];
	}

	/**
	 * Основные настройки сайта
	 * @return string
	 */
	public function actionCore(): string {
		$this->button('setting/cache','delete','Очистить кеш');
		$cfg=plushka::config();
		if(isset($cfg['smtpHost'])===true) $method='smtp';
		else {
			$method='email';
			$cfg['smtpHost']=null;
			$cfg['smtpPort']=null;
			$cfg['smtpUser']=null;
			$cfg['smtpPassword']=null;
		}
		$f=plushka::form();
		$f->checkbox('debug','Режим отладки',$cfg['debug']);
		$f->text('adminEmailEmail','E-mail администрации',$cfg['adminEmailEmail']);
		$f->text('adminEmailName','Имя администрации (e-mail)',$cfg['adminEmailName']);
		$f->select('method','Метод отправки почты',[['smtp','SMTP'],['email','PHP']],$method);
		$f->text('smtpHost','SMTP хост',$cfg['smtpHost']);
		$f->text('smtpPort','SMTP порт',$cfg['smtpPort']);
		$f->text('smtpUser','SMTP логин',$cfg['smtpUser']);
		$f->text('smtpPassword','SMTP пароль',$cfg['smtpPassword']);
		$f->submit('Сохранить');
		$this->form=$f;
		return 'Core';
	}

	protected function helpCore(): string {
		return 'core/setting';
	}

	public function actionCoreSubmit(array $data): void {
		$cfg=new Config('_core');
		if(plushka::error()) return;
		/** @noinspection PhpUndefinedFieldInspection */
		$cfg->debug=isset($data['debug']);
		/** @noinspection PhpUndefinedFieldInspection */
		$cfg->adminEmailEmail=$data['adminEmailEmail'];
		/** @noinspection PhpUndefinedFieldInspection */
		$cfg->adminEmailName=$data['adminEmailName'];
		if($data['method']==='smtp') {
			/** @noinspection PhpUndefinedFieldInspection */
			$cfg->smtpHost=$data['smtpHost'];
			/** @noinspection PhpUndefinedFieldInspection */
			$cfg->smtpPort=$data['smtpPort'];
			/** @noinspection PhpUndefinedFieldInspection */
			$cfg->smtpUser=$data['smtpUser'];
			/** @noinspection PhpUndefinedFieldInspection */
			$cfg->smtpPassword=$data['smtpPassword'];
		} else {
			unset($cfg->smtpHost);
			unset($cfg->smtpPort);
			unset($cfg->smtpUser);
			unset($cfg->smtpPassword);
		}
		if($cfg->save('_core')===false) return;
		plushka::redirect('setting','Изменения сохранены');
	}

	/**
	 * Настройка подмены ссылок (настройка ЧПУ)
	 * @return FormEx
	 */
	public function actionUrl(): FormEx {
		$cfg=plushka::config();
		$link='';
		foreach($cfg['link'] as $src=>$dst) $link.=$src.'='.$dst."\n";
		$form=plushka::form();
		$form->text('mainPath','Главная страница (относительный url)',$cfg['mainPath']);
		$form->textarea('link','Преобразование URL',$link);
		$form->submit('Сохранить');
		$this->cite='Здесь вы можете изменить вид ссылок на страницы сайта. Впишите строки вида: <b>реальная_ссыла</b>=<b>короткая_ссылка</b>, например: <b>article/view/service</b>=<b>service</b> (теперь страница http://example.com/article/view/service будет доступна по адресу http://example.com/service).';
		return $form;
	}

	protected function helpUrl(): string {
		return 'core/link#replace';
	}

	public function actionUrlSubmit(array $data): void {
		$cfg=new Config('_core');
		/** @noinspection PhpUndefinedFieldInspection */
		$cfg->mainPath=$data['mainPath'];
		$link=explode("\n",$data['link']);
		$cnt=count($link);
		$newLink=[];
		for($i=0;$i<$cnt;$i++) {
			$item=explode('=',$link[$i]);
			if(count($item)!==2) continue;
			$item[0]=trim($item[0]);
			if($item[0][0]==='/') $item[0]=substr($item[0],1);
			$newLink[$item[0]]=trim($item[1]);
		}
		/** @noinspection PhpUndefinedFieldInspection */
		$cfg->link=$newLink;
		$cfg->save('_core');
		plushka::redirect('setting/url','Изменения сохранены');
	}

	/**
	 * Очищает весь кеш
	 */
	public function actionCache(): void {
		$this->_clearFolder(plushka::path().'cache/');
		plushka::redirect('setting','Кеш очищен');
	}

	//Рекурсивно удаляет все файлы из указанного директория
	private static function _clearFolder(string $path): void {
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