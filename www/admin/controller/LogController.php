<?php
namespace plushka\admin\controller;
use plushka\admin\model\Log;

class LogController extends \plushka\admin\core\Controller {

	public function right() {
		return array(
			'index'=>'log.*',
			'log'=>'log.*'
		);
	}

	//Список журналов регистрации
	public function actionIndex() {
		$table=plushka::table();
		$table->rowTh('Журналы регистраций');
		$data=Log::getList();
		foreach($data as $item) {
			$table->link('log/log?id='.$item['file'],$item['title']);
		}
		return $table;
	}

	//Лог журнала регистрации
	public function actionLog() {
		if(isset($_GET['keyword'])) $this->keyword=$_GET['keyword']; else $this->keyword=null;
		$log=new Log($_GET['id'],$this->keyword,(isset($_GET['page']) ? $_GET['page'] : null));
		if(plushka::error(false)) plushka::error404();
		$title=$log->title();
		$table=plushka::table();
		foreach($title as $item) $table->th($item);
		unset($title);
		$this->table=$table;
		foreach($log as $i=>$item) {
			foreach($item as $value) $table->text($value);
		}
		$this->count=$log->count();
		//Определить реальный номер страницы для пагинации
		if(!isset($_GET['page'])) {
			$_GET['page']=ceil($this->count/LOG_LIMIT_ON_PAGE);
		}
		return 'Log';
	}

}