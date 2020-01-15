<?php
namespace plushka\admin\controller;
use plushka\admin\core\Config;
use plushka\admin\core\Controller;
use plushka\admin\core\FormEx;
use plushka\admin\core\plushka;
use plushka\admin\core\Table;
use plushka\admin\model\Language;

/**
 * Управление языками сайта
 * @package multilanguage
 *
 * `/admin/language` - список языков
 * `/admin/language/add` - добавление языка
 * `/admin/language/delete` - удаление языка
 * `/admin/language/setting` - настройка правил переключения между языками
 */
class LanguageController extends Controller {

	public function right(): array {
		return [
			'index'=>'language.*',
			'add'=>'language.*',
			'delete'=>'language.*',
			'setting'=>'language.rule'
		];
	}

	/**
	 * Список языков
	 * @return Table
	 */
	public function actionIndex(): Table {
		$this->button('language/add','new','Добавить язык');
		$cfg=plushka::config();
		$table=plushka::table();
		$table->rowTh('Язык|');
		foreach($cfg['languageList'] as $item) {
			if($item===$cfg['languageDefault']) {
				$table->text('<b>'.$item.' (основной)</b>');
				$table->text('');
			} else {
				$table->text($item);
				$table->editDelete('id='.$item);
			}
		}
		$this->cite='Удаление языков не приводит к удалению файлов локализации, вы должны сделать это самостоятельно, если требуется.';
		return $table;
	}

	protected function helpIndex(): string {
		return 'core/language';
	}

	/**
	 * Добавление языка
	 * @return FormEx
	 */
	public function actionAdd(): FormEx {
		$form=plushka::form();
		$form->text('alias','Псевдоним языка');
		$form->submit();
		$this->cite='<b>Псевдоним</b> - это две латинские буквы, обозначающие язык';
		return $form;
	}

	public function actionAddSubmit(array $data) {
		//Валидация
		$validator=plushka::validator($data);
		if($validator->validate([
				'alias'=>['latin','псевдоним',true,'max'=>2]
			])===false) return;
		//Модифицировать СУБД
		if(Language::create($data['alias'])===false) return;
		//Обновить конфигурационный файл
		$cfg=new Config('_core');
		/** @noinspection PhpUndefinedFieldInspection */
		$lst=$cfg->languageList;
		/** @noinspection PhpUndefinedFieldInspection */
		if(in_array($validator->alias,$lst)) {
			plushka::error('Этот язык уже используется');
			return;
		}
		/** @noinspection PhpUndefinedFieldInspection */
		$lst[]=$validator->alias;
		/** @noinspection PhpUndefinedFieldInspection */
		$cfg->languageList=$lst;
		$cfg->save('_core');
		plushka::redirect('language');
	}

	/**
	 * Удаление языка
	 * @return string
	 */
	public function actionDelete(): string {
		$languageDefault=plushka::config('_core','languageDefault');
		if($languageDefault===$_GET['id']) {
			plushka::error('Это основной язык сайта, его удалить нельзя');
			return '_empty';
		}
		//Модифицировать СУБД
		if(Language::delete($_GET['id'])===false) return '_empty';
		//Обновить конфигурационный файл
		$cfg=new Config('_core');
		/** @noinspection PhpUndefinedFieldInspection */
		$lst=$cfg->languageList;
		unset($lst[array_search($_GET['id'],$lst)]);
		$lst=array_values($lst);
		/** @noinspection PhpUndefinedFieldInspection */
		$cfg->languageList=$lst;
		$cfg->save('_core');
		plushka::redirect('language');
		return null;
	}

	/**
	 * Настройка правил переключения между языками
	 * @return FormEx
	 */
	public function actionSetting(): FormEx {
		$form=plushka::form();
		$cfg=plushka::config();
		$cfgLanguage=$cfg['language'] ?? null;
		foreach($cfg['languageList'] as $item) {
			$form->text($item,'Название языка <b>'.$item.'</b>',$cfgLanguage['lang'][$item]);
		}
		$form->textarea('rule','Не мультиязычные страницы',implode("\n",$cfgLanguage['rule']));
		$form->submit();
		$this->cite='На этой странице задаётся список разделов сайта, дочерние элементы которых не являются мультиязычными, тоесть элементов этого раздела сайта может не существовать на других языках. В этом случае, для элементов этого раздела сайта, переключатель языков (виджет language) будет "вести" на один уровень выше.<br />
		Пример: <b>article/blog/news</b> - на всех статьях этого блога переключатель языка будет "вести" на "article/blog/news", а не на саму статью (article/blog/news/some_article)';
		return $form;
	}

	protected function helpSetting(): string {
		return 'core/language#widget';
	}

	public function actionSettingSubmit(array $data) {
		$cfg=plushka::config();
		$cfgLanguage=new Config();
		foreach($cfg['languageList'] as $item) $lang[$item]=$data[$item];
		/** @noinspection PhpUndefinedFieldInspection */
		/** @noinspection PhpUndefinedVariableInspection */
		$cfgLanguage->lang=$lang;

		$data=explode("\n",$data['rule']);
		for($i=0,$cnt=count($data);$i<$cnt;$i++) {
			$item=trim($data[$i]);
			if($item[0]=='/') $item=substr($item,1);
			if($item[strlen($item)-1]=='/') $item=substr($item,0,-1);
			if(!$item) unset($data[$i]);
			$data[$i]=$item;
		}
		$data=array_values($data);
		/** @noinspection PhpUndefinedFieldInspection */
		$cfgLanguage->rule=$data;
		if($cfgLanguage->save('language')===false) return;
		plushka::redirect('language/setting','Сохранено');
	}

}