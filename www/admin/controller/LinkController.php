<?php
namespace plushka\admin\controller;
use plushka\admin\core\Controller;
use plushka\admin\core\FormEx;
use plushka\admin\core\plushka;

/**
 * Управление произвольными ссылками в меню
 *
 * `/admin/link/menuLink` - меню "Произвольная ссылка"
 */
class LinkController extends Controller {

	public function right(): array {
		return [
			'menuLink'=>'menu.*'
		];
	}

	/**
	 * Произвольная ссылка в меню
	 * @return FormEx
	 */
	public function actionMenuLink(): FormEx {
		$form=plushka::form();
		$form->text('link','Ссылка',$_GET['link']);
		$form->submit('Продолжить');
		return $form;
	}

	public function actionMenuLinkSubmit(array $data): string {
		$i=strlen('http://'.$_SERVER['HTTP_HOST']);
		if(substr($data['link'],0,$i)==='http://'.$_SERVER['HTTP_HOST']) $data['link']=substr($data['link'],$i+1);
		return $data['link'];
	}

}