<?php
use plushka\admin\core\SqliteEx;
plushka::import('test/unit/MysqliTest');

class SqliteTest extends MysqliTest {

	public function testConnect() {
		$db=new SqliteEx();
		$this->assertNULL(plushka::error());
		return $db;
	}

}