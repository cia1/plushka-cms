<?php
use plushka\admin\core\MysqliEx;

class MysqliTest extends \PHPUnit\Framework\TestCase {

	private const TABLE_STUCTURE=[
		'id'=>'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
		'alias'=>'CHAR(80) NOT NULL',
		'title'=>'VARCHAR(200) NOT NULL'
	];

	private const TEST_DATA=[
		10=>['id'=>10,'alias'=>'one','title'=>'the first'],
		11=>['alias'=>'two','title'=>'the second'],
		12=>['alias'=>'three','title'=>'the third']
	];

	public function testConnect() {
		$db=new MysqliEx();
		$this->assertNULL(plushka::error());
		return $db;
	}

	/**
	 * @depends testConnect
	 */
	public function testCreateTable($db) {
		$db->query('DROP TABLE IF EXISTS phpunit_test_tmp');
		$db->create('phpunit_test_tmp',self::TABLE_STUCTURE);
		$this->assertNULL(plushka::error());
		return $db;
	}

	/**
	 * @depends testCreateTable
	 */
	public function testInsert($db) {
		$db->insert('phpunit_test_tmp',array_values(self::TEST_DATA));
		$this->assertSame($db->affected(),count(self::TEST_DATA));
		$this->assertSame($db->insertId(),11);
		return $db;
	}

	/**
	 * @depends testInsert
	 */
	public function testSelect($db) {
		$id=array_keys(self::TEST_DATA)[0];
		$this->assertSame(
			$db->fetchValue('SELECT title FROM phpunit_test_tmp WHERE id='.$id),
			self::TEST_DATA[$id]['title']
		);
		$data=[];
		foreach(self::TEST_DATA as $id=>$item) {
			$_item=['id'=>null];
			$item=array_merge($_item,$item);
			$item['id']=(string)$id;
			$data[]=$item;
		}
		$this->assertSame(
			$db->fetchArrayAssoc('SELECT * FROM phpunit_test_tmp',100),
			$data
		);
		$this->assertSame($db->foundRows(),count($data));
		return $db;
	}

	/**
	 * @depends testSelect
	 */
	public function testUpdate($db) {
		$id=array_keys(self::TEST_DATA)[0];
		$db->query('UPDATE phpunit_test_tmp SET title='.$db->escape('Any title').' WHERE id='.$id);
		$this->assertSame($db->affected(),1);
		$this->assertSame($db->fetchValue('SELECT title FROM phpunit_test_tmp WHERE id='.$id),'Any title');
		return $db;
	}

	/**
	 * @depends testUpdate
	 */
	public function testDropTable($db) {
		$db->query('DROP TABLE phpunit_test_tmp');
//		$db->query('SELECT * FROM phpunit_test_tmp');
		$this->assertNULL(plushka::error());
	}

}