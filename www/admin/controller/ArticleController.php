<?php
namespace plushka\admin\controller;
use plushka\admin\core\Controller;
use plushka\admin\core\FormEx;
use plushka\admin\core\plushka;
use plushka\admin\core\Table;
use plushka\admin\model\Article;
use plushka\admin\model\ArticleCategory;
use plushka\core\HTTPException;

/**
 * Управление статьями, блогами и списками статей
 * Категории статей не создаются/удаляются при созданиее/удалении виджета или пункта меню, как это сделано во многих
 * других случаях. Такое поведение нужно для создания ссылок на уже суещствующие разделы сайта.
 *
 * `/admin/article/category` - создание/редактирование категории статей
 * `/admin/article/feature` - список не опубликованных в категории статей
 * `/admin/article/article` - создание/редактирование отдельной статьи
 * `/admin/article/articleDelete` - удаление статьи
 * `/admin/article/menuArticle` - меню "Статья"
 * `/admin/article/menuBlog` - меню "Блог"
 * `/admin/article/menuList` - меню "Плоский список статей"
 * `/admin/article/wigetBlog` - виджет "Блок и список статей"
 */
class ArticleController extends Controller {

	public function right(): array {
		return [
			'category'=>'article.category',
			'feature'=>'*',
			'article'=>'article.article',
			'articleDelete'=>'article.article',
			'menuArticle'=>'article.article',
			'menuBlog'=>'article.category',
			'widgetBlog'=>'article.category',
			'menuList'=>'*'
		];
	}

	/**
	 * Категория статей
	 * @param ArticleCategory $category
	 * @return FormEx
	 * @throws HTTPException
	 */
	public function actionCategory(ArticleCategory $category=null) {
		if($category===null) {
			/** @var ArticleCategory $category */
			$category=plushka::model('admin/articleCategory');
			if(isset($_GET['id'])) { //Выбрать категорию по ИД
				if($category->loadById($_GET['id'])===false) throw new HTTPException(404);
			} elseif(isset($_GET['link']) && $_GET['link']) { //Выбрать категорию по псевдониму
				if($category->loadByAlias(substr($_GET['link'],strrpos($_GET['link'],'/')+1))===false) throw new HTTPException(404);
			} else $category->init();
			if(isset($_GET['parent'])) $category->parentId=intVal($_GET['parent']);
		}
		$form=plushka::form();
		$form->hidden('id',$category->id);
		$form->hidden('parentId',$category->parentId);
		$form->commonAppend($category,'title, alias, metaTitle, metaDescription, metaKeyword');
		$form->text('onPage','Кол-во статей на странице',$category->onPage);
		$form->editor('text1','Вступительный текст',$category->text1);
		$form->submit('Сохранить');
		$this->cite='Содержимое поля <b>Вступительный текст</b> публикуется над списком статей.';
		return $form;
	}

	public function actionCategorySubmit(array $data): ?ArticleCategory {
		/** @var ArticleCategory $category */
		$category=plushka::model('admin/articleCategory');
		$category->set($data);
		if($category->save()===false) return $category;
		plushka::redirect('article/category?id='.$category->id,'Изменения сохранены');
		return null;
	}

	/**
	 * Список не опубликованных статей в категории
	 * @return Table
	 */
	public function actionFeature(): Table {
		$table=plushka::table();
		$table->rowTh(['Дата','Заголовок','']);
		foreach(ArticleCategory::featureList($_GET['categoryId']) as $item) {
			$table->text($item['date']);
			$table->link('article/article?id='.$item['id'],$item['title']);
			$table->delete('id='.$item['id']);
		}
		return $table;
	}

	protected function helpFeature(): string {
		return 'core/article#feature';
	}

	/**
	 * Создание или редактирование статьи (отдельной или в составе блога)
	 * @param Article $article
	 * @return FormEx
	 * @throws HTTPException
	 */
	public function actionArticle(Article $article=null): FormEx {
		if($article===null) {
			$article=new Article();
			if($_POST) $article->set($_POST['article']); //чтобы избежать повторного обращения к базе данных
			elseif(isset($_GET['id'])===true) {
				if($article->loadById($_GET['id'])===false) throw new HTTPException(404);
			} elseif(isset($_GET['alias'])===true) {
				if($article->loadByAlias($_GET['alias'])===false) throw new HTTPException(404);
			} elseif(isset($_GET['categoryId'])===true) $article->categoryId=$_GET['categoryId'];
		}
		$form=plushka::form();
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

	public function actionArticleSubmit(array $data) {
		$article=plushka::model('admin/article');
		$article->set($data);
		if($article->save()===false) return $article;
		plushka::success(($data['id'] ? 'Изменения сохранены' : 'Статья создана'));
		plushka::redirect('article/article?id='.$article->id);
		return null;
	}

	/**
	 * Удаление статьи
	 */
	public function actionArticleDelete(): void {
		$article=new Article();
		$article->delete($_GET['id']);
		plushka::redirect('article/article');
	}

	/**
	 * Меню "Статья"
	 * Выводит форму создания/редактирования статьи
	 * @return FormEx
	 */
	public function actionMenuArticle(): FormEx {
		if(isset($_GET['link']) && $_GET['link']) $_GET['alias']=substr($_GET['link'],13);
		return $this->actionArticle();
	}

	public function actionMenuArticleSubmit(array $data) {
		/** @var Article $article */
		$article=plushka::model('admin/article');
		$article->set($data);
		if($article->save()===false) return false;
		return 'article/view/'.$article->alias;
	}

	/**
	 * Меню "Блог"
	 * @param ArticleCategory $category
	 * @return FormEx
	 */
	public function actionMenuBlog(ArticleCategory $category=null): FormEx {
		return $this->actionCategory($category);
	}

	public function actionMenuBlogSubmit(array $data) {
		/** @var ArticleCategory $category */
		$category=plushka::model('admin/articleCategory');
		$category->set($data);
		if($category->save()===false) return $category;
		return 'article/blog/'.$category->alias;
	}

	/**
	 * Меню "Плоский список статей"
	 * @param ArticleCategory $category
	 * @return FormEx
	 */
	public function actionMenuList(ArticleCategory $category=null): FormEx {
		return $this->actionCategory($category);
	}

	public function actionMenuListSubmit(array $data) {
		/** @var ArticleCategory $category */
		$category=plushka::model('admin/articleCategory');
		$category->set($data);
		if($category->save()===false) return $category;
		return 'article/list/'.$category->alias;
	}

	/**
	 * Виджет "блог и список статей"
	 * @param array $data :
	 *                    int $categoryId ИД категории
	 *                    string $linkType Тип ссылок ("blog" или "list")
	 *                    int $countPreview - количество записей в виде блога
	 *                    int $countLink - количество записей в виде ссылок
	 * @return FormEx
	 */
	public function actionWidgetBlog(array $data=null): FormEx {
		if($data===null) $data=['categoryId'=>null,'countPreview'=>0,'countLink'=>0,'linkType'=>'blog'];
		$form=plushka::form();
		$newCategoryLink=plushka::link('admin/article&action=category').'&backlink='.urlencode('admin/section/widget?section='.$_GET['section'].'&type=blog&lang='._LANG);
		$form->listBox('categoryId','Категория','SELECT id,title FROM article_category_'._LANG,$data['categoryId'],'< создать новую категорию >','onclick="if(this.value==\'\') document.location=\''.$newCategoryLink.'\';"');
		$form->select('linkType','Вид ссылок на статьи',[['blog','article/blog/...'],['list','article/list/...']],$data['linkType']);
		$form->text('countPreview','Количество анонсов статей',$data['countPreview']);
		$form->text('countLink','Количество ссылок на статьи',$data['countLink']);
		$form->submit();
		$form->cite='Если <b>Публиковать название на экране</b> установлен, то в начале модуля будет выведен текст, введённый в поле &laquo;Название&raquo;. В списке <b>Категория</b> выберите категорию статей, которая должна быть исползована в блоге.<br /><b>Количество анонсов</b> - в виде анонсов статей с кнопкой "читать далее", <b>Количество ссылок</b> - в виде ссылок на статьи. Если в обоих полях есть какое-то количество, то будут выведены сначала анонсы, затем ссылки в указанных количествах.<br /><b>Вид ссылок на сататьи</b> определяет какими будут ссылки на материалы: <i>http://example.com/article/<b>blog</b>/(category)/(article)</i> (в виде анонсов) или <i>http://example.com/article/<b>list</b>/(category)/(article)</i> (в виде списка).';
		return $form;
	}

	public function actionWidgetBlogSubmit(array $data) {
		$data['countPreview']=(int)$data['countPreview'];
		$data['countLink']=(int)$data['countLink'];
		return $data;
	}

}