<?php
/* Управление произвольным HTML-кодом на сайте */
class sController extends controller {

	public function right($right) {
		if(isset($right['html.*'])) return true; else return false;
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Форма для редактирования текста */
	public function actionItem() {
		if($_GET['id']) { //Текст уже существует - загрузить его
			$data=array('filename'=>$_GET['id']);
			$data['text']=file_get_contents(core::path().'data/widgetHtml/'.$data['filename'].'.html');
		} else $data=array('filename'=>'','text'=>''); //Текста нет - пустой массив, чтобы небыло warning
		$f=core::form();
		$f->hidden('filename',$data['filename']);
		$f->editor('html','Содержимое (html):',$data['text']);
		$f->submit('Готово');
		return $f;
	}

	public function actionItemSubmit($data) {
		if(!$this->_save($data)) return false;
		core::redirect('?controller=html&action=item&id='.$data['filename'],'Изменения сохранены');
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Редактирование текста или создание нового блока текста */
	public function actionWidgetHtml($data=null) {
		if($data) $this->data=array('section'=>$_GET['section'],'filename'=>$data,'text'=>file_get_contents(core::path().'data/widgetHtml/'.$data.'.html'));
		else $this->data=array('section'=>$_GET['section'],'filename'=>null,'text'=>'');
		return 'Widget';
	}

	public function actionWidgetHtmlSubmit($data) {
		//Если это новый блок текста, то "придумать" имя файла исходя из названия секции, в которой находится виджет
		if(!$data['filename']) {
			$data['filename']=$data['section'];
			$d=opendir(core::path().'data/widgetHtml');
			$index=1;
			$len=strlen($data['section'])+1;
			while($f=readdir($d)) {
				if($f=='.' || $f=='..') continue;
				if(substr($f,0,$len)==$data['section'].'.') {
					$i=(int)substr($f,$len);
					if($i>=$index) $index=$i+1;
				}
			}
			$data['filename']=$data['section'].'.'.$index;
		}
		if(!$this->_save($data)) return false;
		return $data['filename'];
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- PRIVATE ---------------------------------------------------------------- */
	/* Сохраняет текст в файл */
	private function _save($data) {
		$f=fopen(core::path().'data/widgetHtml/'.$data['filename'].'.html','w');
		fwrite($f,$data['html']);
		fclose($f);
		return true;
	}
/* ----------------------------------------------------------------------------------- */
}
?>