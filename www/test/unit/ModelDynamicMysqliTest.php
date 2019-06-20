<?php
use plushka\admin\core\MysqliEx;
use plushka\admin\core\SqliteEx;

class ModelDynamicMysqliTest extends \PHPUnit\Framework\TestCase {

	protected const DB_DRIVER='mysql';
	protected const TABLE_1=[
		'id'=>'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
		'alias'=>'CHAR(50) NOT NULL'
	];
	protected const TABLE_1_RULE=[
		'id'=>['primary'],
		'alias'=>['latin','псевдоним',true]
	];
	private const TEMP_TABLE_2=[
	];

	public static function setUpBeforeClass(): void {
		$cfg=plushka::config();
		$cfg['dbDriver']=static::DB_DRIVER;
		self::_drop();
		$db=self::_getDb();
		$db->create('unit_test_1',self::TABLE_1);
	}

	public function testInsertPKExists() {
		$model=plushka::model('unit_test_1');
//		var_dump($model);
		die("EEEEEEEEEEEEEEEEE");

	}

//	public function testLoadByWhere() {}

//	public function testLoadById() {}

//	public function testUpbateByPK() {}

//	public function testDelete() {}

//	public function testInsertPKNotExists() {}


	private static function _getDb() {
		if(self::DB_DRIVER==='mysql') return new MysqliEx();
		else return new SqliteEx();
	}

	private static function _drop() {
		$db=plushka::db();
		$db->query('DROP TABLE IF EXISTS unit_test_1');
		$db->query('DROP TABLE IF EXISTS unit_test_2');
	}
}