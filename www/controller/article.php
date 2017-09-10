<?php
/* Реализует страницы простых сатей, блога статей (новостная лента), списка статей (категория статей)
	ЧПУ: /article/view/ПСЕВДОНИМ (actionView) - отдельная статья;
	/article/blog/КАТЕГОРИЯ (actionBlog), /article/list/КАТЕГОРИЯ (actionList) - блог или список статей;
	/article/blog/КАТЕГОРИЯ/ПСЕВДОНИМ (actionView), /article/list/КАТЕГОРИЯ/ПСЕВДОНИМ (actionView) - отдельная статья в блоге или списке
*/
class sController extends controller {

	/* Одиночная статья или статья блога (подробно) */
	public function actionView() {
		$db=core::db();
		if($_GET['corePath'][1]=='blog' || $_GET['corePath'][1]=='list') $this->data=$db->fetchArrayOnceAssoc('SELECT a.id id,a.title title,a.metaTitle metaTitle,a.metaKeyword metaKeyword,a.metaDescription metaDescription,a.date date,a.text2 text2,a.categoryId categoryId FROM article_'._LANG.' a INNER JOIN articleCategory_'._LANG.' c ON c.id=a.categoryId AND c.alias='.$db->escape($this->url[2]).' WHERE a.alias='.$db->escape($this->url[3]));
		else $this->data=$db->fetchArrayOnceAssoc('SELECT id,title,metaTitle,metaKeyword,metaDescription,date,text2,categoryId FROM article_'._LANG.' WHERE alias='.$db->escape($this->url[2]));
		if(!$this->data) core::error404();
		if($this->data['metaTitle']) $this->metaTitle=$this->data['metaTitle']; else $this->metaTitle=$this->data['title'];
		if($this->data['metaKeyword']) $this->metaKeyword=$this->data['metaKeyword'];
		if($this->data['metaDescription']) $this->metaDescription=$this->data['metaDescription'];
		$this->pageTitle=$this->data['title'];

		return 'View';
	}

	protected function breadcrumbView() {
		if($_GET['corePath'][1]=='view') return array();
		else {
			$db=core::db();
			return array('<a href="'.core::link('article/'.$_GET['corePath'][1].'/'.$this->url[2]).'">'.$db->fetchValue('SELECT title FROM articleCategory_'._LANG.' WHERE id='.$this->data['categoryId']).'</a>');
		}
	}

	protected function adminViewLink() {
		if($_GET['corePath'][1]=='list') $s='&list'; elseif($_GET['corePath'][1]=='blog') $s='&blog'; else $s='';
		return array(
			array('article.article','?controller=article&action=article&id='.$this->data['id'].$s,'edit','Редактировать статью'),
			array('article.article','?controller=article&action=articleDelete&id='.$this->data['id'],'delete','Удалить статью &laquo;'.$this->data['title'].'&raquo;','Удалить','if(!confirm(\'Подтвердите удаление.\')) return false;')
		);
	}

	/* Блог статей/новостная лента */
	public function actionBlog() {
		if(count($this->url)>=4) {
			$this->url[1]='View';
			return $this->actionView();
		}
		if(isset($this->url[2])) $categoryAlias=$this->url[2]; else $categoryAlias='blog';
		$db=core::db();
		$this->category=$db->fetchArrayOnceAssoc('SELECT id,title,metaTitle,metaKeyword,metaDescription,text1,onPage FROM articleCategory_'._LANG.' WHERE alias='.$db->escape($categoryAlias));
		if(!$this->category) core::error404();
		$this->category['alias']=$categoryAlias;
		if($this->category['metaTitle']) $this->metaTitle=$this->category['metaTitle']; else $this->metaTitle=$this->category['title'];
		if($this->category['metaKeyword']) $this->metaKeyword=$this->category['metaKeyword'];
		if($this->category['metaDescription']) $this->metaDescription=$this->category['metaDescription'];
		$this->pageTitle=$this->category['title'];
		$this->items=$db->fetchArrayAssoc('SELECT id,alias,title,text1,date FROM article_'._LANG.' WHERE categoryId='.$this->category['id'].' AND (date<'.time().' OR date IS NULL) ORDER BY sort,date DESC,id DESC',$this->category['onPage']);
		$this->totalCount=$db->foundRows();
		return 'Blog';
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

	/* Список статей (категория) */
	public function actionList() {
		if(count($this->url)>=4) {
			$this->url[1]='View';
			return $this->actionView();
		}
		if(isset($this->url[2])) $categoryAlias=$this->url[2]; else $categoryAlias='blog';
		$db=core::db();
		$this->category=$db->fetchArrayOnceAssoc('SELECT id,title,metaTitle,metaKeyword,metaDescription,text1 FROM articleCategory_'._LANG.' WHERE alias='.$db->escape($categoryAlias));
		if(!$this->category) core::error404();
		$this->category['alias']=$categoryAlias;
		if($this->category['metaTitle']) $this->metaTitle=$this->category['metaTitle']; else $this->metaTitle=$this->category['title'];
		if($this->category['metaKeyword']) $this->metaKeyword=$this->category['metaKeyword'];
		if($this->category['metaDescription']) $this->metaDescription=$this->category['metaDescription'];
		$this->pageTitle=$this->category['title'];
		$this->items=$db->fetchArrayAssoc('SELECT id,alias,title,text1,date FROM article_'._LANG.' WHERE categoryId='.$this->category['id'].' AND (date<'.time().' OR date IS NULL) ORDER BY sort,date DESC,id DESC');
		return 'List';
	}

	protected function adminListLink() {
		return array(
			array('article.category','?controller=article&action=category&id='.$this->category['id'],'edit','Править заголовок, мета-теги, описание'),
			array('article.article','?controller=article&action=article&categoryId='.$this->category['id'].'&list','new','Добавить новую статью в категорию'),
			array('article.article','?controller=article&action=feature&categoryId='.$this->category['id'].'&list','status0','Не опубликованные статьи')
		);
	}

}