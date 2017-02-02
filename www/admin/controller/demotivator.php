<?php
/* Управление демотиваторами */
class sController extends controller {

	public function right() {
		return array(
			'Setting'=>'domotivator.setting',
			'Hidden'=>'demotivator.moderate',
			'Edit'=>'demotivator.moderate',
			'Delete'=>'demotivator.moderate',
			'MenuConstructor'=>'domotivator.setting',
			'MenuGallery'=>'domotivator.setting',
			'WidgetLast'=>'domotivator.setting'
		);
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Общие настройки модуля демотиваторов */
	public function actionSetting() {
		$cfg=core::config('demotivator');
		$f=core::form();
		$f->text('imageWidthMax','Максимально допустимая ширина рисунка',$cfg['imageWidthMax']);
		$f->text('paddingTop','Отступ сверху',$cfg['paddingTop']);
		$f->text('paddingBottom','Отступ снизу',$cfg['paddingBottom']);
		$f->text('paddingX','Отступ слева и справа',$cfg['paddingX']);
		$f->text('backgroundColor','Цвет фона',$cfg['backgroundColor']);
		$f->text('textColor','Цвет текста',$cfg['textColor']);
		$f->text('fontFamily','Шрифт',$cfg['fontFamily']);
		$f->text('fontSize','Размер шрифта по умолчанию',$cfg['fontSize']);
		$f->text('userGroup','Группа пользователей',$cfg['userGroup']);
		$f->text('watermarkText','Текст водного знака',$cfg['watermarkText']);
		$f->text('watermarkColor','Цвет водного знака',$cfg['watermarkColor']);
		$f->submit('Сохранить');
		$this->cite='Если в поле <b>группа пользователей</b> указано число отличное от нуля, то создавать демотиваторы смогут только пользователи этой группы';
		return $f;
	}

	public function actionSettingSubmit($data) {
		$f='data/'.strtolower(str_replace(' ','-',$data['fontFamily'])).'.ttf';
		if(!file_exists(core::path().$f)) {
			core::error('Не найден файл /'.$f);
			return false;
		}
		core::import('admin/core/config');
		$cfg=new config();
		$cfg->imageWidthMax=(int)$data['imageWidthMax'];
		$cfg->paddingTop=(int)$data['paddingTop'];
		$cfg->paddingBottom=(int)$data['paddingBottom'];
		$cfg->paddingX=(int)$data['paddingX'];
		$cfg->backgroundColor=$data['backgroundColor'];
		$cfg->textColor=$data['textColor'];
		$cfg->fontFamily=$data['fontFamily'];
		$cfg->fontSize=(int)$data['fontSize'];
		$cfg->userGroup=(int)$data['userGroup'];
		$cfg->watermarkText=$data['watermarkText'];
		$cfg->watermarkColor=$data['watermarkColor'];
		$cfg->save('demotivator');
		core::success('Изменения сохранены');
		core::redirect('?controller=demotivator&action=setting');
	}

	/* Ожидающие модерации или скрытые админом демотиваторы */
	public function actionHidden() {
		$t=&core::table();
		$t->rowTh('Изображение|Название|');
		$db=core::db();
		$db->query('SELECT id,title,image FROM demotivator WHERE status=0 ORDER BY date DESC');
		$url=core::url().'public/demotivator/';
		while($item=$db->fetch()) {
			$t->link('<img src="'.$url.$item[2].'" style="height:60px;" />','?controller=demotivator&action=edit&id='.$item[0],null,'align="center"');
			$t->link($item[1],'?controller=demotivator&action=edit&id='.$item[0]);
			$t->delete('?controller=demotivator&action=delete&id='.$item[0]);
		}

		return $t;
	}

	/* Редактирование демотиватора */
	public function actionEdit() {
		$db=core::db();
		$data=$db->fetchArrayOnceAssoc('SELECT * FROM demotivator WHERE id='.$_GET['id']);
		$f=core::form();
		$f->hidden('id',$data['id']);
		$f->text('title','Название',$data['title']);
		$f->date('date','Дата',date('d.m.Y',$data['date']));
		$f->text('metaKeyword','Ключевые слова',$data['metaKeyword']);
		$f->text('metaDescription','Описание',$data['metaDescription']);
		$f->checkbox('status','Опубликовано',$data['status']);
		$f->submit('Сохранить');

		return $f;
	}

	public function actionEditSubmit($data) {
		$m=core::model('demotivator');
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'title'=>array('string'),
			'date'=>array('date','дата',true),
			'metaKeyword'=>array('string'),
			'metaDescription'=>array('string'),
			'status'=>array('boolean')
		))) return false;
		core::success('Изменения сохранены');
		core::redirect('?controller=demotivator&action=edit&id='.$m->id);
	}

	/* Удаление демотиватора */
	public function actionDelete() {
		$db=core::db();
		$f=core::path().'public/demotivator/'.$db->fetchValue('SELECT image FROM demotivator WHERE id='.(int)$_GET['id']);
		if(file_exists($f)) unlink($f);
		$db->query('DELETE FROM demotivator WHERE id='.(int)$_GET['id']);
		core::success('Демотиватор удалён');
		core::redirect('?controller=demotivator&action=hidden');
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- MENU ------------------------------------------------------------------- */
	/* Реализует ссылку на конструктор демотиваторов. Каких-либо параметров не требуется. */
	public function actionMenuConstructor() {
		$f=core::form();
		$f->submit('Продолжить','submit');

		return $f;
	}

	public function actionMenuConstructorSubmit($data) {
		return 'demotivator/construct';
	}

	/* Реализует ссылку на галерею демотиваторов. Каких-либо параметров не требуется. */
	public function actionMenuGallery() {
		$f=core::form();
		$f->submit('Продолжить','submit');

		return $f;
	}

	public function actionMenuGallerySubmit($data) {
		return 'demotivator';
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Последний добавленный демотиватор. */
	public function actionWidgetLast() {
		$f=core::form();
		$f->hidden('cacheTime',10); //время кеширования виджета
		$f->submit('Продолжить');
		return $f;
	}

	public function actionWidgetLastSubmit($data) {
		return '';
	}
/* ----------------------------------------------------------------------------------- */

}
?>