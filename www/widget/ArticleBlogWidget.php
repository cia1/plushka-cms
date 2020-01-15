<?php
namespace plushka\widget;
use plushka\core\plushka;
use plushka\core\Widget;

/**
 * Блог или список статей
 * @property-read array $options:
 *  int $categoryId ИД категории статей
 *  string $linkType Тип ссылки ("blog" или "link")
 *  int countPreview Количество записей с аннотацией (блог)
 *  int countLink количество статей в виде ссылки
 * @property-read string $categoryAlias Псевдоним категории
 * @property-read array[] $itemsPreview Статьи в виде блога
 * @property-read array[] $itemsLink Статьи в виде ссылок
 */
class ArticleBlogWidget extends Widget {

	public function __invoke() {
		if(isset($this->options['linkType'])===false) $this->options['linkType']='blog';
		if(isset($this->options['countPreview'])===false) $this->options['countPreview']=0; else $this->options['countPreview']=(int)$this->options['countPreview'];
		if(isset($this->options['countLink'])===false) $this->options['countLink']=0; else $this->options['countLink']=(int)
		$this->options['countLink'];
		$db=plushka::db();
		$countTotal=$this->options['countPreview']+$this->options['countLink'];
		$this->categoryAlias=$db->fetchValue('SELECT alias FROM article_category_'._LANG.' WHERE id='.$this->options['categoryId']);
		$db->query('SELECT id,alias,date,title,text1 FROM article_'._LANG.' WHERE categoryId='.$this->options['categoryId'].' AND (date<'.time().' OR date IS NULL) ORDER BY sort,date DESC LIMIT 0,'.$countTotal);
		$cnt=$this->options['countPreview'];
		$this->itemsPreview=[];
		while($cnt && $item=$db->fetchAssoc()) {
			$this->itemsPreview[]=$item;
			$cnt--;
		}
		$this->itemsLink=[];
		while($item=$db->fetchAssoc()) $this->itemsLink[]=$item;
		return 'Blog';
	}

	public function adminLink(): array {
		return [
			['article.category','?controller=article&action=article&categoryId='.$this->options['categoryId'],'new','Добавить статью']
		];
	}

	public function adminLink2($data): array {
		return [
			['article.article','?controller=article&action=article&id='.$data['id'],'edit','Редактировать статью','Изменить']
		];
	}

}