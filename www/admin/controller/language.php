<?php
//Управление языками сайта. Модуль multilanguage
class sController extends controller {

	public function right() {
		return array(
			'Index'=>'language.*',
			'Add'=>'language.*',
			'Delete'=>'language.*',
			'Setting'=>'language.rule'
		);
	}

	//Список языков
	public function actionIndex() {
		$this->button('?controller=language&action=add','new','Добавить язык');
		$cfg=core::config();
		$table=core::table();
		$table->rowTh('Язык|');
		foreach($cfg['languageList'] as $item) {
			if($item==$cfg['languageDefault']) {
				$table->text('<b>'.$item.' (основной)</b>');
				$table->text('');
			} else {
				$table->text($item);
				$table->editDelete('language&id='.$item);
			}
		}
		$this->cite='Удаление языков не приводит к удалению файлов локализации, вы должны сделать это самостоятельно, если требуется.';
		return $table;
	}

	//Добавление языка
	public function actionAdd() {
		$form=core::form();
		$form->text('alias','Псевдоним языка');
		$form->submit();
		$this->cite='<b>Псевдоним</b> - это две латинские буквы, обозначающие язык';
		return $form;
	}

	public function actionAddSubmit($data) {
		//Валидация
		$model=core::model();
		$model->set($data);
		if(!$model->validate(array(
			'alias'=>array('latin','псевдоним',true,'max'=>2)
		))) return false;
		//Модифицировать СУБД
		core::import('admin/model/language');
		if(!language::create($data['alias'])) return false;
		//Обновить конфигурационный файл
		core::import('admin/core/config');
		$cfg=new config('_core');
		$lst=$cfg->languageList;
		if(in_array($model->alias,$lst)) {
			core::error('Этот язык уже используется');
			return false;
		}
		$lst[]=$model->alias;
		$cfg->languageList=$lst;
		$cfg->save('_core');
		core::redirect('?controller=language');
	}

	//Удаление языка
	public function actionDelete() {
		$cfg=core::config();
		if($cfg['languageDefault']==$_GET['id']) {
			core::error('Это основной язык сайта, его удалить нельзя');
			return '_empty';
		}
		//Модифицировать СУБД
		core::import('admin/model/language');
		if(!language::delete($_GET['id'])) return false;
		//Обновить конфигурационный файл
		core::import('admin/core/config');
		$cfg=new config('_core');
		$lst=$cfg->languageList;
		unset($lst[array_search($_GET['id'],$lst)]);
		$lst=array_values($lst);
		$cfg->languageList=$lst;
		$cfg->save('_core');
		core::redirect('?controller=language');
	}

	//Правила переключения между языками
	public function actionSetting() {
		$form=core::form();
		$cfg=core::config();
		$cfgLanguage=core::config('language');
		foreach($cfg['languageList'] as $item) {
			$form->text($item,'Название языка <b>'.$item.'</b>',$cfgLanguage['lang'][$item]);
		}
		$form->textarea('rule','Не мультиязычные страницы',implode("\n",$cfgLanguage['rule']));
		$form->submit();
		$this->cite='На этой странице задаётся список разделов сайта, дочерние элементы которых не являются мультиязычными, тоесть элементов этого раздела сайта может не существовать на других языках. В этом случае, для элементов этого раздела сайта, переключатель языков (виджет language) будет "вести" на один уровень выше.<br />
		Пример: <b>article/blog/news</b> - на всех статьях этого блога переключатель языка будет "вести" на "article/blog/news", а не на саму статью (article/blog/news/some_article)';
		return $form;
	}

	public function actionSettingSubmit($data) {
		$cfg=core::config();
		core::import('admin/core/config');
		$cfgLanguage=new config();
		foreach($cfg['languageList'] as $item) $lang[$item]=$data[$item];
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
		$cfgLanguage->rule=$data;
		if(!$cfgLanguage->save('language')) return false;
		core::redirect('?controller=language&action=setting','Сохранено');
		}

}