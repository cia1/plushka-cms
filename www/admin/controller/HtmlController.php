<?php
namespace plushka\admin\controller;
use plushka\admin\core\Controller;
use plushka\admin\core\FormEx;
use plushka\admin\core\plushka;
use plushka\admin\model\Html;

/**
 * Управление произвольными HTML-блоками кода на сайте
 *
 * `/admin/html/item` - редактирование текста в блоке
 * `/admin/html/widgetHtml` - виджет "Произвольный HTML"
 */
class HtmlController extends Controller {

	public function right(): array {
		return [
			'item'=>'html.*',
			'widgetHtml'=>'html.*'
		];
	}

	/**
	 * Редактирование текста в блоке
	 * @return mixed
	 */
	public function actionItem(): FormEx {
		$html=new Html();
		if(isset($_GET['id'])===true && $_GET['id']) { //Текст уже существует - загрузить его
			$html->load($_GET['id']);
		} else $html->init($_GET['section'] ?? null); //Текста нет - пустой массив, чтобы небыло warning
		return $html->form();
	}

	public function actionItemSubmit(array $data): void {
		$html=new Html();
		$html->html=$data['html'];
		if($html->save($data['fileName'])===false) return;
		plushka::success('Изменения сохранены');
		plushka::redirect('html/item?id='.$data['fileName']);
	}

	/**
	 * Редактирование текста или создание нового блока текста
	 * @param mixed $data
	 * @return FormEx
	 */
	public function actionWidgetHtml($data=null): FormEx {
		$html=new Html();
		if($data) $html->load($data);
		$html->section=$_GET['section'];
		return $html->form();
	}

	public function actionWidgetHtmlSubmit(array $data) {
		$html=new Html();
		$html->html=$data['html'];
		if($html->save($data['fileName'] ?? null)===false) return false;
		return $data['fileName'];
	}

}