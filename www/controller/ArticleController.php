<?php
namespace plushka\controller;
use plushka\core\plushka;
use plushka\core\Controller;
use plushka\core\HTTPException;
use plushka\model\Article;

/**
 * Статьи
 * ЧПУ:
 * /article/view/ПСЕВДОНИМ (actionView) - отдельная статья;
 * /article/blog/КАТЕГОРИЯ (actionBlog), /article/list/КАТЕГОРИЯ (actionList) - блог или список статей;
 * /article/blog/КАТЕГОРИЯ/ПСЕВДОНИМ (actionView), /article/list/КАТЕГОРИЯ/ПСЕВДОНИМ (actionView) - отдельная статья в блоге или списке
 *
 * @property-read array|null $category Категория для действия "view"
 * @property-read array|null $article Стаья для действия "view"
 * @property-read array $articles Статьи для действий "blog" и "view"
 * @property-read int $foundRows Кол-во статей в пагинации для действий "blog" и "view"
 */
class ArticleController extends Controller {

    /**
     * Независимая статья или статья в блоге
     * @return string
     * @throws HTTPException
     */
	public function actionView() {
		if($_GET['corePath'][1]==='blog' || $_GET['corePath'][1]==='list') {
            $this->category=Article::categoryByAlias($this->url[2],'title');
            if($this->category===null) throw new HTTPException(404);
            $alias=$this->url[3];
        } else $alias=$this->url[2];
		$this->article=Article::articleByAlias($alias);
		if($this->article===null) throw new HTTPException(404);
		if($this->article['metaTitle']!==null) $this->metaTitle=$this->article['metaTitle']; else $this->metaTitle=$this->article['title'];
		if($this->article['metaKeyword']) $this->metaKeyword=$this->article['metaKeyword'];
		if($this->article['metaDescription']) $this->metaDescription=$this->article['metaDescription'];
		$this->pageTitle=$this->article['title'];
		return 'View';
	}

	protected function breadcrumbView() {
		if($_GET['corePath'][1]==='view') return ['{{pageTitle}}'];
		else return [
		    '<a href="'.plushka::link('article/'.$_GET['corePath'][1].'/'.$this->category['alias']).'">'.$this->category['title'].'</a>',
            '{{pageTitle}}'
        ];
	}

	protected function adminViewLink() {
		if($_GET['corePath'][1]==='list') $s='&list'; elseif($_GET['corePath'][1]==='blog') $s='&blog'; else $s='';
		return array(
			array('article.article','?controller=article&action=article&id='.$this->article['id'].$s,'edit','Редактировать статью'),
			array('article.article','?controller=article&action=articleDelete&id='.$this->article['id'],'delete','Удалить статью &laquo;'.$this->article['title'].'&raquo;','Удалить','if(!confirm(\'Подтвердите удаление.\')) return false;')
		);
	}

    /**
     * Блог статей
     * @return string
     */
	public function actionBlog() {
	    if(isset($this->url[3])===true) {
			$this->url[1]='View';
			return $this->actionView();
		}
	    $this->category=Article::categoryByAlias($this->url[2]);
	    if($this->category===null) throw new HTTPException('404');
		if($this->category['metaTitle']) $this->metaTitle=$this->category['metaTitle']; else $this->metaTitle=$this->category['title'];
		if($this->category['metaKeyword']) $this->metaKeyword=$this->category['metaKeyword'];
		if($this->category['metaDescription']) $this->metaDescription=$this->category['metaDescription'];
		$this->pageTitle=$this->category['title'];
		$this->articles=Article::articleList($this->category['id'],$this->category['onPage']);
		$this->foundRows=Article::foundRows();
		return 'Blog';
	}

	protected function breadcrumbBlog() {
		return array('{{pageTitle}}');
	}

	/* Блог статей/новостная лента */
	protected function adminBlogLink() {
		return array(
			array('article.category','?controller=article&action=category&id='.$this->category['id'],'edit','Править заголовок, мета-теги блога'),
			array('article.article','?controller=article&action=article&categoryId='.$this->category['id'].'&blog','new','Добавить новую статью в блог'),
			array('article.article','?controller=article&action=feature&categoryId='.$this->category['id'].'&blog','status0','Не опубликованные статьи')
		);
	}

	protected function adminBlogLink2($data) {
		return array(
			array('article.article','?controller=article&action=article&id='.$data['id'].'&blog','edit','Редактировать статью &laquo;'.$data['title'].'&raquo;'),
			array('article.article','?controller=article&action=articleDelete&id='.$data['id'],'delete','Удалить статью &laquo;'.$data['title'].'&raquo;','Удалить','if(!confirm(\'Подтвердите удаление.\')) return false;')
		);
	}

    /**
     * Категория (список статей)
     * @return string
     */
	public function actionList() {
        if(isset($this->url[3])===true) {
			$this->url[1]='view';
			return $this->actionView();
		}
        $this->category=Article::categoryByAlias($this->url[2]);
        if($this->category===null) throw new HTTPException('404');
		if($this->category['metaTitle']) $this->metaTitle=$this->category['metaTitle']; else $this->metaTitle=$this->category['title'];
		if($this->category['metaKeyword']) $this->metaKeyword=$this->category['metaKeyword'];
		if($this->category['metaDescription']) $this->metaDescription=$this->category['metaDescription'];
		$this->pageTitle=$this->category['title'];
        $this->articles=Article::articleList($this->category['id'],$this->category['onPage']);
        $this->foundRows=Article::foundRows();
		return 'List';
	}

	protected function breadcrumbList() {
		return array('{{pageTitle}}');
	}

	protected function adminListLink() {
		return array(
			array('article.category','?controller=article&action=category&id='.$this->category['id'],'edit','Править заголовок, мета-теги, описание'),
			array('article.article','?controller=article&action=article&categoryId='.$this->category['id'].'&list','new','Добавить новую статью в категорию'),
			array('article.article','?controller=article&action=feature&categoryId='.$this->category['id'].'&list','status0','Не опубликованные статьи')
		);
	}

}
