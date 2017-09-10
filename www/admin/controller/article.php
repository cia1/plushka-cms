<?php
/* Управление статьями, блогами, списками статей.
Это один из случаев, когда категории статей не создаются/удаляются при создании/удалении виджета/пункта меню.
Несомненно это неправильно и нужно использовать внешнюю ссылку в меню. Но что делать с виджетом, если нужен блог уже существующей категории? */
class sController extends controller {

	public function right() {
		return array(
			'Category'=>'article.category',
			'Feature'=>'*',
			'Article'=>'article.article',
			'ArticleDelete'=>'article.article',
			'MenuArticle'=>'article.article',
			'MenuBlog'=>'article.category',
			'WidgetBlog'=>'article.category',
			'MenuList'=>'*'
		);
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */

	/* Создание или редактирование категории статей */
	public function actionCategory() {
		$db=core::db();
		if(isset($_GET['id'])) { //Выбрать категорию по ИД
			$data=$db->fetchArrayOnceAssoc('SELECT * FROM articleCategory_'._LANG.' WHERE id='.(int)$_GET['id']);
		} elseif(isset($_GET['link']) && $_GET['link']) { //Выбрать категорию по псевдониму
			$data=$db->fetchArrayOnceAssoc('SELECT * FROM articleCategory_'._LANG.' WHERE alias='.$db->escape(substr($_GET['link'],strrpos($_GET['link'],'/')+1)));
		} else $data=array('id'=>null,'parentId'=>0,'title'=>'','alias'=>'','onPage'=>20,'metaTitle'=>'','metaKeyword'=>'','metaDescription'=>'','text1'=>'');
		if(!$data) core::error404();
		$f=core::form();
		$f->hidden('id',$data['id']);
		$f->hidden('parentId',$data['parentId']);
		$f->text('title','Название',$data['title']);
		$f->text('alias','URL (псевдоним)',$data['alias']);
		$f->editor('text1','Вступительный текст',$data['text1']);
		$f->text('onPage','Кол-во статей на странице',$data['onPage']);
		$f->text('metaTitle','meta Заголовок',$data['metaTitle']);
		$f->text('metaKeyword','meta Ключевые слова',$data['metaKeyword']);
		$f->text('metaDescription','meta Описание',$data['metaDescription']);
		$f->submit('Сохранить');
		$this->cite='Содержимое поля <b>Вступительный текст</b> публикуется над списком статей.';
		return $f;
	}

	public function actionCategorySubmit($data) {
	if(!$this->_saveCategory($data)) return false; //Этот же механизм используется в меню, поэтому вынесен в отдельную функцию
		core::success('Изменения сохранены');
		core::redirect('?controller=article&action=category&id='.$data['id']);
	}

	//Список не опубликованных статей
	public function actionFeature() {
		$db=core::db();
		$db->query('SELECT id,date,title FROM article_'._LANG.' WHERE categoryId='.(int)$_GET['categoryId'].' AND date>'.time());
		$table=core::table();
		$table->rowTh(array('Дата','Заголовок',''));
		while($item=$db->fetch()) {
			$table->text(date('d.m.Y',$item[1]));
			$table->link($item[2],'article/article&id='.$item[0]);
			$table->delete('?controller=article&action=articleDelete&id='.$item[0]);
		}
		return $table;
	}

	/* Создание или редактирование статьи (отдельной или в составе блога) */
	public function actionArticle() {
		core::import('admin/model/article');
		$article=new article();
		if($_POST) $article->set($_POST['article']); //просто чтобы избежать повторного обращения к базе данных
		elseif(isset($_GET['id'])) {
			if(!$article->loadById($_GET['id'])) core::error404();
		} elseif(isset($_GET['alias'])) {
			if(!$article->loadByAlias($_GET['alias'])) core::error404();
		}
		elseif(isset($_GET['categoryId'])) $article->categoryId=$_GET['categoryId'];
		$form=core::form();
		$form->hidden('id',$article->id);
		$form->hidden('categoryId',$article->categoryId);
		$form->text('title','Название (заголовок)',$article->title);
		$form->text('alias','URL (псевдоним)',$article->alias);
		$form->text('metaTitle','meta Заголовок',$article->metaTitle);
		$form->text('metaKeyword','meta Ключевые слова',$article->metaKeyword);
		$form->text('metaDescription','meta Описание',$article->metaDescription);
		$form->date('date','Дата публикации',$article->date);
		if($article->categoryId && !isset($_GET['list'])) $form->editor('text1','Краткое описание (вступление)',$article->text1);
		$form->editor('text2','Текст статьи',$article->text2);
		$form->submit('Сохранить');

		$this->cite='<b>Enter</b> - новый абзац. <b>Shift + Enter</b> - новая строка, вставить пустую строку.';
		return $form;
	}

	public function actionArticleSubmit($data) {
		if(!self::_saveArticle($data)) return false; //этот же механизм используется в меню
		core::success(($data['id'] ? 'Изменения сохранены' : 'Статья создана'));
		core::redirect('?controller=article&action=article&id='.$data['id']);
	}

	/* Удаление статьи (форма подтверждения) */
	public function actionArticleDelete() {
		core::import('admin/model/article');
		$article=new article();
		$article->delete($_GET['id']);
		core::redirect('?controller=article&acttion=article');
	}
/* --------------------------------------------------------------------------------------------- */



/* --- MENU ------------------------------------------------------------------------------------ */
	/* Простая статья. Ссылка: article/view/ПСЕВДОНИМ */
	public function actionMenuArticle() {
		if(isset($_GET['link']) && $_GET['link']) $_GET['alias']=substr($_GET['link'],13);
		return $this->actionArticle();
	}

	public function actionMenuArticleSubmit($data) {
		if($data['id']) $title='Изменения сохранены'; else $title='Статья создана';
		if(!self::_saveArticle($data)) return false; //этот же механизм при простом создании/редактировании статьи
		return 'article/view/'.$data['alias'];
	}

	/* Блог */
	public function actionMenuBlog() {
		return $this->actionCategory();
	}

	public function actionMenuBlogSubmit($data) {
		if(!$this->_saveCategory($data)) return false; //этот же механизм при простом создании/редактировании блога
		return 'article/blog/'.$data['alias'];
	}

	/* Список статей */
	public function actionMenuList() {
		return $this->actionCategory();
	}

	public function actionMenuListSubmit($data) {
		if(!$this->_saveCategory($data)) return false; //этот же механизм при простом создании/редактировании списка статей
		return 'article/list/'.$data['alias'];
	}
/* ----------------------------------------------------------------------------------- */



/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Блог и список статей
	array $data: categoryId - ИД категории; int linkType - тип ссылок (blog или list);
	int countPreview - количество записей в виде блога; int countLink - количество записей в виде ссылок; */
	public function actionWidgetBlog($data=null) {
		if(!$data) $data=array('categoryId'=>null,'countPreview'=>0,'countLink'=>0,'linkType'=>'blog');
		$f=core::form();
		$newCategoryLink=core::link('article&action=category').'&backlink='.urlencode('?controller=section&action=widget&amp;section='.$_GET['section'].'&type=blog&lang='._LANG);
		$f->listBox('categoryId','Категория','SELECT id,title FROM articleCategory_'._LANG,$data['categoryId'],'< создать новую категорию >','onclick="if(this.value==\'\') document.location=\''.$newCategoryLink.'\';"');
		$f->select('linkType','Вид ссылок на статьи',array(array('blog','article/blog/...'),array('list','article/list/...')),$data['linkType']);
		$f->text('countPreview','Количество анонсов статей',$data['countPreview']);
		$f->text('countLink','Количество ссылок на статьи',$data['countLink']);
		$f->submit();
		$f->cite='Если <b>Публиковать название на экране</b> установлен, то в начале модуля будет выведен текст, введённый в поле &laquo;Название&raquo;. В списке <b>Категория</b> выберите категорию статей, которая должна быть исползована в блоге.<br /><b>Количество анонсов</b> - в виде анонсов статей с кнопкой "читать далее", <b>Количество ссылок</b> - в виде ссылок на статьи. Если в обоих полях есть какое-то количество, то будут выведены сначала анонсы, затем ссылки в указанных количествах.<br /><b>Вид ссылок на сататьи</b> определяет какими будут ссылки на материалы: <i>http://example.com/article/<b>blog</b>/(category)/(article)</i> (в виде анонсов) или <i>http://example.com/article/<b>list</b>/(category)/(article)</i> (в виде списка).';
		return $f;
	}

	public function actionWidgetBlogSubmit($data) {
		$data['countPreview']=(int)$data['countPreview'];
		$data['countLink']=(int)$data['countLink'];
		return $data;
	}
/* ----------------------------------------------------------------------------------- */



/* ---------- PRIVATE ---------------------------------------------------------------- */
	/* Выполняет валидацию и сохранение категории статей в базе данных */
	private function _saveCategory($data) {
		//Проверить уникальность псевдонима
		$db=core::db();
		if($data['id']) $err=$db->fetchValue('SELECT id FROM articleCategory_'._LANG.' WHERE alias='.$db->escape($data['alias']).' AND id<>'.$data['id']);
		else $err=$db->fetchValue('SELECT id FROM articleCategory_'._LANG.' WHERE alias='.$db->escape($data['alias']));
		if($err) {
			core::error('Такой псевдоним уже используется для другой категории статей (блога)');
			return false;
		}
		$m=core::model('articleCategory');
		$m->multiLanguage();
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'parentId'=>array('id','',true),
			'title'=>array('string','Заголовок',true),
			'alias'=>array('latin','URL (псевдоним)',true),
			'metaTitle'=>array('string'),
			'metaKeyword'=>array('string'),
			'metaDescription'=>array('string'),
			'text1'=>array('html','Вступительный текст'),
			'onPage'=>array('integer','кол-во статей на странице','min'=>0,'max'=>255)
		))) return false;
		core::hook('modify','article/blog/'.$m->alias); //Обновить дату изменения страницы
		core::hook('modify','article/list/'.$m->alias);
		return true;
	}

	/* Выполняет валидацию и сохранение статьи в базе данных */
	private static function _saveArticle($data) {
		core::import('admin/model/article');
		$article=new article();
		$article->set($data);
		if(!$article->save()) return false;
		return true;
	}
/* ----------------------------------------------------------------------------------- */
}