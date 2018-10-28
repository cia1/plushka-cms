<?php
/* Управление статьями, блогами, списками статей.
Это один из случаев, когда категории статей не создаются/удаляются при создании/удалении виджета/пункта меню.
Несомненно это неправильно и нужно использовать внешнюю ссылку в меню. Но что делать с виджетом, если нужен блог уже существующей категории? */
class sController extends controller {

	public function right() {
		return array(
			'category'=>'article.category',
			'feature'=>'*',
			'article'=>'article.article',
			'articleDelete'=>'article.article',
			'menuArticle'=>'article.article',
			'menuBlog'=>'article.category',
			'widgetBlog'=>'article.category',
			'menuList'=>'*'
		);
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */

	/* Создание или редактирование категории статей */
	public function actionCategory($category=null) {
		if($category===null) {
			$category=core::model('admin/articleCategory');
			if(isset($_GET['id'])) { //Выбрать категорию по ИД
				if($category->loadById($_GET['id'])===false) core::error404();
			} elseif(isset($_GET['link']) && $_GET['link']) { //Выбрать категорию по псевдониму
				if($category->loadByAlias(substr($_GET['link'],strrpos($_GET['link'],'/')+1))===false) core::error404();
			} else $category->init();
			if(isset($_GET['parent'])) $category->parentId=intVal($_GET['parent']);
		}
		$form=core::form();
		$form->hidden('id',$category->id);
		$form->hidden('parentId',$category->parentId);
		$form->commonAppend($category,'title, alias, metaTitle, metaDescription, metaKeyword');
		$form->text('onPage','Кол-во статей на странице',$category->onPage);
		$form->editor('text1','Вступительный текст',$category->text1);
		$form->submit('Сохранить');
		$this->cite='Содержимое поля <b>Вступительный текст</b> публикуется над списком статей.';
		return $form;
	}

	public function actionCategorySubmit($data) {
		$category=core::model('admin/articleCategory');
		$category->set($data);
		if($category->save()===false) return $category;
		core::redirect('article/category?id='.$category->id,'Изменения сохранены');
	}

	//Список не опубликованных статей
	public function actionFeature() {
		core::import('admin/model/articleCategory');
		$table=core::table();
		$table->rowTh(array('Дата','Заголовок',''));
		foreach(articleCategory::featureList($_GET['categoryId']) as $item) {
			$table->text($item['date']);
			$table->link('article/article?id='.$item['id'],$item['title']);
			$table->delete('id='.$item['id']);
		}
		return $table;
	}

	protected function helpFeature() {
		return 'core/article#feature';
	}

	/* Создание или редактирование статьи (отдельной или в составе блога) */
	public function actionArticle($article=null) {
		if($article===null) {
			core::import('admin/model/article');
			$article=new article();
			if($_POST) $article->set($_POST['article']); //просто чтобы избежать повторного обращения к базе данных
			elseif(isset($_GET['id'])) {
				if(!$article->loadById($_GET['id'])) core::error404();
			} elseif(isset($_GET['alias'])) {
				if(!$article->loadByAlias($_GET['alias'])) core::error404();
			}
			elseif(isset($_GET['categoryId'])) $article->categoryId=$_GET['categoryId'];
		}
		$form=core::form();
		$form->hidden('id',$article->id);
		$form->hidden('categoryId',$article->categoryId);
		$form->commonAppend($article,'title,alias,metaTitle,metaDescription,metaKeyword');
		$form->date('date','Дата публикации',$article->date);
		if($article->categoryId && !isset($_GET['list'])) $form->editor('text1','Краткое описание (вступление)',$article->text1);
		$form->editor('text2','Текст статьи',$article->text2);
		$form->submit('Сохранить');

		$this->cite='<b>Enter</b> - новый абзац. <b>Shift + Enter</b> - новая строка, вставить пустую строку.';
		return $form;
	}

	public function actionArticleSubmit($data) {
		$article=core::model('admin/article');
		$article->set($data);
		if($article->save()===false) return $article;
		core::success(($data['id'] ? 'Изменения сохранены' : 'Статья создана'));
		core::redirect('article/article?id='.$article->id);
	}

	/* Удаление статьи (форма подтверждения) */
	public function actionArticleDelete() {
		core::import('admin/model/article');
		$article=new article();
		$article->delete($_GET['id']);
		core::redirect('article/article');
	}
/* --------------------------------------------------------------------------------------------- */



/* --- MENU ------------------------------------------------------------------------------------ */
	/* Простая статья. Ссылка: article/view/ПСЕВДОНИМ */
	public function actionMenuArticle() {
		if(isset($_GET['link']) && $_GET['link']) $_GET['alias']=substr($_GET['link'],13);
		return $this->actionArticle();
	}

	public function actionMenuArticleSubmit($data) {
		$article=core::model('admin/article');
		$article->set($data);
		if($article->save()===false) return false;
		return 'article/view/'.$article->alias;
	}

	/* Блог */
	public function actionMenuBlog($category=null) {
		return $this->actionCategory($category);
	}

	public function actionMenuBlogSubmit($data) {
		$category=core::model('admin/articleCategory');
		$category->set($data);
		if($category->save()===false) return $category;
		return 'article/blog/'.$category->alias;
	}

	/* Список статей */
	public function actionMenuList($category=null) {
		return $this->actionCategory($category);
	}

	public function actionMenuListSubmit($data) {
		$category=core::model('admin/articleCategory');
		$category->set($data);
		if($category->save()===false) return $category;
		return 'article/list/'.$category->alias;
	}
/* ----------------------------------------------------------------------------------- */



/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Блог и список статей
	array $data: categoryId - ИД категории; int linkType - тип ссылок (blog или list);
	int countPreview - количество записей в виде блога; int countLink - количество записей в виде ссылок; */
	public function actionWidgetBlog($data=null) {
		if(!$data) $data=array('categoryId'=>null,'countPreview'=>0,'countLink'=>0,'linkType'=>'blog');
		$form=core::form();
		$newCategoryLink=core::link('admin/article&action=category').'&backlink='.urlencode('admin/section/widget?section='.$_GET['section'].'&type=blog&lang='._LANG);
		$form->listBox('categoryId','Категория','SELECT id,title FROM article_category_'._LANG,$data['categoryId'],'< создать новую категорию >','onclick="if(this.value==\'\') document.location=\''.$newCategoryLink.'\';"');
		$form->select('linkType','Вид ссылок на статьи',array(array('blog','article/blog/...'),array('list','article/list/...')),$data['linkType']);
		$form->text('countPreview','Количество анонсов статей',$data['countPreview']);
		$form->text('countLink','Количество ссылок на статьи',$data['countLink']);
		$form->submit();
		$form->cite='Если <b>Публиковать название на экране</b> установлен, то в начале модуля будет выведен текст, введённый в поле &laquo;Название&raquo;. В списке <b>Категория</b> выберите категорию статей, которая должна быть исползована в блоге.<br /><b>Количество анонсов</b> - в виде анонсов статей с кнопкой "читать далее", <b>Количество ссылок</b> - в виде ссылок на статьи. Если в обоих полях есть какое-то количество, то будут выведены сначала анонсы, затем ссылки в указанных количествах.<br /><b>Вид ссылок на сататьи</b> определяет какими будут ссылки на материалы: <i>http://example.com/article/<b>blog</b>/(category)/(article)</i> (в виде анонсов) или <i>http://example.com/article/<b>list</b>/(category)/(article)</i> (в виде списка).';
		return $form;
	}

	public function actionWidgetBlogSubmit($data) {
		$data['countPreview']=(int)$data['countPreview'];
		$data['countLink']=(int)$data['countLink'];
		return $data;
	}
/* ----------------------------------------------------------------------------------- */

}