<?php
namespace plushka\admin\controller;
use plushka\admin\model\Html;

/* Управление произвольным HTML-кодом на сайте */
class HtmlController extends \plushka\admin\core\Controller {

	public function right() {
		return array(
			'item'=>'html.*',
			'widgetHtml'=>'html.*'
		);
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Форма для редактирования текста */
	public function actionItem() {
		$html=new Html();
		if($_GET['id']) { //Текст уже существует - загрузить его
			if(!$html->load($_GET['id'])) plushka::error404();
		} else $html->init(); //Текста нет - пустой массив, чтобы небыло warning
		return $html->form();
	}

	public function actionItemSubmit($data) {
		$html=new Html();
		$html->html=$data['html'];
		if(!$html->save($data['fileName'])) return false;
		plushka::success('Изменения сохранены');
		plushka::redirect('html/item?id='.$data['fileName']);
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Редактирование текста или создание нового блока текста */
	public function actionWidgetHtml($data=null) {
		$html=new Html();
		if($data) {
			if(!$html->load($data)) plushka::error404();
		}
		$html->section=$_GET['section'];
		return $html->form();
	}

	public function actionWidgetHtmlSubmit($data) {
		//Если это новый блок текста, то "придумать" имя файла исходя из названия секции, в которой находится виджет
		if(!$data['fileName']) $data['fileName']=Html::fileNameBySection($data['section']);
		$html=new Html();
		$html->html=$data['html'];
		if(!$html->save($data['fileName'])) return false;
		return $data['fileName'];
	}
/* ----------------------------------------------------------------------------------- */

}