<?php
use plushka\admin\core\SqliteEx;
plushka::import('test/unit/MysqliTest');

class SqliteTest extends MysqliTest {

	protected const PRIMARY_AS_STRING=false;

	public function testConnect() {
		$db=new SqliteEx();
		$this->assertNULL(plushka::error());
		return $db;
	}

}