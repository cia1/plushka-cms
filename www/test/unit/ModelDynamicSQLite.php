<?php
class ModelDynamicSQLiteTest extends \PHPUnit\Framework\TestCase {

	protected const DB_DRIVER='sqlite';
	protected const TABLE_1=[
		'id'=>'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
		'alias'=>'CHAR(50) NOT NULL'
	];
	private const TEMP_TABLE_2=[
	];

	public static function setUpBeforeClass(): void {
		$cfg=plushka::config();
		$cfg['dbDriver']=static::DB_DRIVER;
		self::_drop();
		$db=plushka::db();
		$db->create('unit_test_1',self::TABLE_1);
	}

	public function testInsertPKExists() {}

	public function testLoadByWhere() {}

	public function testLoadById() {}

	public function testUpbateByPK() {}

	public function testDelete() {}

	public function testInsertPKNotExists() {}


	private static function _drop() {
		$db=plushka::db();
		$db->query('DROP TABLE IF EXISTS unit_test_1');
		$db->query('DROP TABLE IF EXISTS unit_test_2');
	}
}