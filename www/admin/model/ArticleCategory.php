<?php
namespace plushka\admin\model;
use plushka\admin\core\ModelRuleTrait;
use plushka\admin\core\plushka;
use plushka\core\Model;

/**
 * AR-модель "Категория статей"
 * @property string   $alias    Псевдоним
 * @property int|null $parentId ИД родительской категории
 * @property int      $onPage   Количество статей на одной странице, может быть переопределён в виджете
 * @property string   $text1    Текст "сверху", над списком категорий
 */
class ArticleCategory extends Model {

	use ModelRuleTrait;

	/**
	 * Возвращает список ещё не опубликованных статей в категории
	 * @param int $categoryId ID категории
	 * @return array
	 */
	public static function featureList(int $categoryId): array {
		$db=plushka::db();
		$db->query('SELECT id,date,title FROM article_'._LANG.' WHERE categoryId='.$categoryId.' AND date>'.time());
		$data=[];
		while($item=$db->fetchAssoc()) {
			$item['date']=date('d.m.Y',$item['date']);
			$data[]=$item;
		}
		return $data;
	}

	public function __construct(string $db='db') {
		parent::__construct('article_category',$db);
		$this->multiLanguage();
	}

	public function init(): void {
		$this->_data=[
			'onPage'=>20
		];
	}

	/**
	 * Загружает данные в модель по псевдониму категории
	 * @param $alias
	 * @return bool
	 */
	public function loadByAlias(string $alias): bool {
		return $this->load('alias='.$this->db->escape($alias));
	}

	protected function rule(): array {
		return $this->commonRuleAppend(
			[
				'id'=>['primary'],
				'parentId'=>['integer','родительская категория'],
				'onPage'=>['integer','Количество статей в списке',true,'min'=>1,'max'=>255],
				'text1'=>['html','Краткий текст (введение)'],
				'text2'=>['html','Текст статььи'],
			],
			'title,alias,metaTitle,metaDescription,metaKeyword'
		);
	}

	protected function beforeInsertUpdate(/** @noinspection PhpUnusedParameterInspection */ $id,$field=null) {
		//Проверить уникальность псевдонима
		$sql='SELECT 1 FROM article_category_'._LANG.' WHERE alias='.$this->db->escape($this->alias);
		if($this->id) $sql.=' AND id<>'.$this->id;
		if($this->db->fetchValue($sql)!==null) {
			plushka::error('Такой псевдоним уже используется для другой категории');
			return false;
		}
		return true;
	}

	protected function afterInsert($id=null): void {
		plushka::hook('modify','article/blog/'.$this->alias,false);
		plushka::hook('modify','article/list/'.$this->alias,false);
	}

	protected function afterUpdate($id=null): void {
		$this->afterInsert($id);
	}

	/**
	 * @inheritDoc
	 * @param int|null $id
	 */
	public function delete($id=null,bool $validateAffected=false): bool {
		if($id!==null) $this->load((int)$id,'id,alias');
		if(parent::delete($id,$validateAffected)===false) return false;
		plushka::hook('pageDelete','article/blog/'.$this->alias,false);
		plushka::hook('pageDelete','article/list/'.$this->alias,false);
		return true;
	}

}