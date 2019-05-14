<?php
namespace plushka\admin\controller;
use plushka\admin\model\Documentation;

class DocumentationController extends \plushka\admin\core\Controller {

	public function actionIndex() {
		if(isset($_GET['path'])===false) $path=array(null,'index');
		else $path=explode('/',$_GET['path']);
		$link=plushka::config('admin/_module','core');
		$link=str_replace('manual','documentation',$link['manual']);
		$path[0]=$link;
		$path=implode('/',$path);
		$i=strpos($path,'#');
		if($i!==false) {
			$anchor=substr($path,$i);
			$path=substr($path,0,$i);
		} else $anchor='';
		$path=$path.'?frame'.$anchor;
		$this->content='<iframe src="'.$path.'"></iframe>';
		$this->css('admin/manual');
		return '_empty';
	}

	/* Создание или редактирование статьи */
	public function actionArticle() {
		$article=new Documentation();
		if($_POST) $article->set($_POST['article']); //просто чтобы избежать повторного обращения к базе данных
		elseif(isset($_GET['id'])) {
			if(!$article->loadById($_GET['id'])) plushka::error404();
		}
		elseif(isset($_GET['parentId'])) $article->parentId=$_GET['parentIdId'];
		$form=plushka::form();
		$form->hidden('id',$article->id);
		$form->hidden('parentId',$article->parentId);
		$form->commonAppend($article,'title,alias,metaTitle,metaDescription,metaKeyword');
		$form->date('date','Дата публикации',$article->date);
		if($article->categoryId && !isset($_GET['list'])) $form->editor('text1','Краткое описание (вступление)',$article->text1);
		$form->editor('text2','Текст статьи',$article->text2);
		$form->submit('Сохранить');
		return $form;
	}

	public function actionArticleSubmit($data) {
		$documentation=new Documentation();
		$documentation->set($data);
		if(!$documentation->save()) return false;
		plushka::success(($data['id'] ? 'Изменения сохранены' : 'Статья создана'));
		plushka::redirect('documentation/article?id='.$documentation->id);
	}

}