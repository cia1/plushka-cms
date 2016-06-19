<?php
/* Управление произвольным HTML-кодом на сайте */
class sController extends controller {

	public function right($right) {
		if(isset($right['html.*'])) return true; else return false;
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Форма для редактирования текста */
	public function actionItem() {
		core::import('admin/model/html');
		$html=new html();
		if($_GET['id']) { //Текст уже существует - загрузить его
			if(!$html->load($_GET['id'])) core::error404();
		} else $html->init(); //Текста нет - пустой массив, чтобы небыло warning
		return $html->form();
	}

	public function actionItemSubmit($data) {
		core::import('admin/model/html');
		$html=new html();
		$html->html=$data['html'];
		if(!$html->save($data['fileName'])) return false;
		core::redirect('?controller=html&action=item&id='.$data['filename'],'Изменения сохранены');
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Редактирование текста или создание нового блока текста */
	public function actionWidgetHtml($data=null) {
		core::import('admin/model/html');
		$html=new html();
		if($data) {
			if(!$html->load($data)) core::error404();
		}
		return $html->form();
	}

	public function actionWidgetHtmlSubmit($data) {
		core::import('admin/model/html');
		//Если это новый блок текста, то "придумать" имя файла исходя из названия секции, в которой находится виджет
		if(!$data['fileName']) $data['fileName']=html::fileNameBySection($data['section']);
		$html=new html();
		$html->html=$data['html'];
		if(!$html->save($data['fileName'])) return false;
		return $data['fileName'];
	}
/* ----------------------------------------------------------------------------------- */

}
?>