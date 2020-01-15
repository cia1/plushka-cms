<?php
namespace plushka\admin\model;
use plushka\admin\core\ModelRuleTrait;
use plushka\admin\core\plushka;
use plushka\core\Model;

/**
 * AR-модель "Документация"
 * @property string $alias Псевдоним статьи документации
 */
class Documentation extends Model {

	use ModelRuleTrait;

	function __construct(string $db=null) {
		parent::__construct('documentation',$db);
		$this->multiLanguage();
	}

	public function rule(): array {
		return $this->commonRuleAppend([
			'id'=>['primary'],
			'parentId'=>['integer'],
			'alias'=>['latin','псевдоним',true],
			'text2'=>['html']
		],'title,alias,metaTitle,metaDescription,metaKeyword');
	}

	/**
	 * @inheritDoc
	 * @param int|null $id
	 */
	protected function afterInsert($id=null): void {
		plushka::hook('modify','documentation/view/'.$this->alias,true); //Обновить дату изменения статьи
	}

	protected function afterUpdate($id=null): void {
		$this->afterInsert($id);
	}

}