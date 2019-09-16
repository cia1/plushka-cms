<?php
namespace plushka\controller;
use plushka\core\plushka;

/* Результаты поиска по сайту и форма поиска
	Для вывода контента генерируется событие с именем "search"
 */
class SearchController extends \plushka\core\Controller {

	function __construct() {
		parent::__construct();
		plushka::language('search');
	}

	public function actionIndex() {
		if(isset($_GET['keyword'])) $this->keyword=$_GET['keyword'];
		else $this->keyword=null;
		$this->form=plushka::form();
		$this->form->method='get';
		$this->form->text('keyword',LNGSearch.':',$this->keyword);
		$this->form->submit(LNGFind);

		$this->metaTitle=$this->pageTitle=LNGSearchAtSite;
		return 'Index';
	}

	public function actionIndexSubmit($data) {}

}