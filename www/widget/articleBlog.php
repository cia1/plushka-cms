<?php
/* Реализует блог и список статей
array $options: int categoryId - ИД категории статей; string linkType - тип ссылки (blog или link);
int countPreview - количество записей с аннотацией (блог); int countLink -  количество записей без аннотации (только ссылка); */
class widgetArticleBlog extends widget {

	public function __invoke() {
		if(!isset($this->options['linkType'])) $this->options['linkType']='blog';
		if(!isset($this->options['countPreview'])) $this->options['countPreview']=0; else $this->options['countPreview']=(int)$this->options['countPreview'];
		if(!isset($this->options['countLink'])) $this->options['countLink']=0; else $this->options['countLink']=(int)$this->options['countLink'];
		$db=core::db();
		$countTotal=$this->options['countPreview']+$this->options['countLink'];
		$this->categoryAlias=$db->fetchValue('SELECT alias FROM article_category_'._LANG.' WHERE id='.$this->options['categoryId']);
		$db->query('SELECT id,alias,date,title,text1 FROM article_'._LANG.' WHERE categoryId='.$this->options['categoryId'].' AND (date<'.time().' OR date IS NULL) ORDER BY sort,date DESC LIMIT 0,'.$countTotal);
		$cnt=$this->options['countPreview'];
		$this->itemsPreview=array();
		while($cnt && $item=$db->fetchAssoc()) {
			$this->itemsPreview[]=$item;
			$cnt--;
		}
		$this->itemsLink=array();
		while($item=$db->fetchAssoc()) $this->itemsLink[]=$item;
		return 'Blog';
	}

	public function adminLink() {
		return array(
			array('article.category','?controller=article&action=article&categoryId='.$this->options['categoryId'],'new','Добавить статью')
		);
	}

	public function adminLink2($data) { // кнопки админки для каждой записи блога
		return array(
			array('article.article','?controller=article&action=article&id='.$data['id'],'edit','Редактировать статью','Изменить')
		);
	}

}