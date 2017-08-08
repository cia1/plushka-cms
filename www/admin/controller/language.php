<?php
//Управление языками сайта. Модуль multilanguage
class sController extends controller {

	public function right() {
		return array(
			'Index'=>'language.*',
			'Add'=>'language.*',
			'Delete'=>'language.*'
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

}