<?php
namespace plushka\admin\model;
use plushka\admin\core\Config;
use plushka\admin\core\ModelRuleTrait;
use plushka\admin\core\plushka;
use plushka\core\Model;

/**
 * AR-модель "Статья"
 * @property int|null $categoryId ИД категории статей
 * @property string   $alias      Псевдоним
 * @property int      $date       Дата публикации
 * @property string   $text1      Аннотация
 * @property string   $text2      Полный текст
 */
class Article extends Model {

	use ModelRuleTrait;

	private $_oldAlias;

	/**
	 * @param string $db
	 */
	function __construct(string $db=null) {
		parent::__construct('article',$db);
		$this->multiLanguage();
	}

	/**
	 * Загружает данные в модель по псевдониму статьи
	 * @param string $alias
	 * @return bool
	 */
	public function loadByAlias(string $alias): bool {
		return $this->load('alias='.$this->db->escape($alias));
	}

	/**
	 * Удаляет статью
	 * Если модель не относится к какой-либо категории, то удаляет все мультиязычные варианты статьи
	 * @inheritDoc
	 * @param int|null $id
	 */
	public function delete($id=null,$validateAffected=false): bool {
		$id=(int)$id;
		$data=$this->db->fetchArrayOnce('SELECT categoryId,alias FROM article_'._LANG.' WHERE id='.$id);
		if($data[0]) $this->multiLanguage(false);
		if(parent::delete($id,$validateAffected)===false) return false;
		$this->multiLanguage(true);
		plushka::hook('pageDelete','article/view/'.$data[1],!$this->_multiLanguage);
		return true;
	}

	protected function rule(): array {
		return $this->commonRuleAppend(
			[
				'id'=>['primary'],
				'categoryId'=>['integer','Категория'],
				'text1'=>['html','Краткий текст (введение)'],
				'text2'=>['html','Текст статььи'],
				'date'=>['date','Дата публикации',false],
			],
			'title,alias,metaTitle,metaDescription,metaKeyword'
		);
	}

	protected function beforeInsertUpdate(/** @noinspection PhpUnusedParameterInspection */ $id,$field=null) {
		//Проверить уникальность псевдонима
		if($this->_data['id']) $this->_oldAlias=$this->db->fetchValue('SELECT alias FROM article_'._LANG.' WHERE id='.$this->_data['id']);
		else $this->_data['oldAlias']=null;
		if($this->_data['alias']!==$this->_oldAlias && $this->db->fetchValue('SELECT 1 FROM article_'._LANG.' WHERE categoryId='.$this->_data['categoryId'].' AND alias='.$this->db->escape($this->_data['alias']).($this->_data['id'] ? ' AND id!='.$this->_data['id'] : ''))) {
			plushka::error('Статья с таким псевдонимом уже существует. Совпадение псевдонимов допустимо только для статей, находящихся в разных категориях.');
			return false;
		}
		if($this->_data['categoryId']) $this->multiLanguage(false);
		return true;
	}

	protected function afterInsert($id=null): void {
		$this->multiLanguage(true);
		plushka::hook('modify','article/view/'.$this->alias,!$this->_multiLanguage); //Обновить дату изменения статьи
	}

	//Обновляет меню, а также проверяет URI главной страницы
	protected function afterUpdate($id=null): void {
		if($this->_oldAlias!=$this->_data['alias']) {
			$cfg1=plushka::config();
			$s='article/view/'.$this->_oldAlias;
			if($cfg1['mainPath']==$s || isset($cfg1['link'][$s])) {
				$cfg2=new Config('_core');
				if($cfg1['mainPath']==$s) {
					/** @noinspection PhpUndefinedFieldInspection */
					$cfg2->mainPath='article/view/'.$this->_data['alias'];
				}
				if(isset($cfg1['link'][$s])) {
					$alias=$cfg1['link'][$s];
					/** @noinspection PhpUndefinedFieldInspection */
					$link=$cfg2->link;
					unset($link[$s]);
					$link['article/view/'.$this->_data['alias']]=$alias;
					/** @noinspection PhpUndefinedFieldInspection */
					$cfg2->link=$link;
				}
				$cfg2->save('_core');
			}
			$this->db->query('UPDATE menu_item SET link='.$this->db->escape('article/view/'.$this->_data['alias']).' WHERE link='.$this->db->escape($s));
		}
		$this->afterInsert($id);
	}

}