<?php
namespace plushka\admin\controller;
use plushka\admin\core\Controller;
use plushka\admin\core\plushka;

/* Управление демотиваторами */
class DemotivatorController extends Controller {

	public function right() {
		return array(
			'setting'=>'domotivator.setting',
			'hidden'=>'demotivator.moderate',
			'edit'=>'demotivator.moderate',
			'delete'=>'demotivator.moderate',
			'menuConstructor'=>'domotivator.setting',
			'menuGallery'=>'domotivator.setting',
			'widgetLast'=>'domotivator.setting'
		);
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Общие настройки модуля демотиваторов */
	public function actionSetting() {
		$cfg=plushka::config('demotivator');
		$f=plushka::form();
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
		if(!file_exists(plushka::path().$f)) {
			plushka::error('Не найден файл /'.$f);
			return false;
		}
		$cfg=new \plushka\admin\core\Config();
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
		plushka::success('Изменения сохранены');
		plushka::redirect('demotivator/setting');
	}

	/* Ожидающие модерации или скрытые админом демотиваторы */
	public function actionHidden() {
		$t=&plushka::table();
		$t->rowTh('Изображение|Название|');
		$db=plushka::db();
		$db->query('SELECT id,title,image FROM demotivator WHERE status=0 ORDER BY date DESC');
		$url=plushka::url().'public/demotivator/';
		while($item=$db->fetch()) {
			$t->link('demotivator/edit?id='.$item[0],'<img src="'.$url.$item[2].'" style="height:60px;" />',null,'align="center"');
			$t->link('demotivator/edit?id='.$item[0],$item[1]);
			$t->delete('id='.$item[0]);
		}

		return $t;
	}

	/* Редактирование демотиватора */
	public function actionEdit() {
		$db=plushka::db();
		$data=$db->fetchArrayOnceAssoc('SELECT * FROM demotivator WHERE id='.$_GET['id']);
		$f=plushka::form();
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
		$m=plushka::model('demotivator');
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'title'=>array('string'),
			'date'=>array('date','дата',true),
			'metaKeyword'=>array('string'),
			'metaDescription'=>array('string'),
			'status'=>array('boolean')
		))) return false;
		plushka::success('Изменения сохранены');
		plushka::redirect('demotivator/edit?id='.$m->id);
	}

	/* Удаление демотиватора */
	public function actionDelete() {
		$db=plushka::db();
		$f=plushka::path().'public/demotivator/'.$db->fetchValue('SELECT image FROM demotivator WHERE id='.(int)$_GET['id']);
		if(file_exists($f)) unlink($f);
		$db->query('DELETE FROM demotivator WHERE id='.(int)$_GET['id']);
		plushka::success('Демотиватор удалён');
		plushka::redirect('demotivator/hidden');
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- MENU ------------------------------------------------------------------- */
	/* Реализует ссылку на конструктор демотиваторов. Каких-либо параметров не требуется. */
	public function actionMenuConstructor() {
		$f=plushka::form();
		$f->submit('Продолжить','submit');

		return $f;
	}

	public function actionMenuConstructorSubmit($data) {
		return 'demotivator/construct';
	}

	/* Реализует ссылку на галерею демотиваторов. Каких-либо параметров не требуется. */
	public function actionMenuGallery() {
		$f=plushka::form();
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
		$f=plushka::form();
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