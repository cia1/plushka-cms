<?php
//���� ���� �������� ������ ����������. ������� ��������� �� �������������.
/* ��������� ��������� � ���� MySQL */
class sqlite {

	private static $_connectId; //������������� ����������� (���� ����������� ��� ����)
	private $_queryId; //������������� ������� (�������� ��� ������ ����������� ������)

	/* ���������� �������������� ������ $value � ������������ ��������� */
	public static function escape($value) { return "'".SQLite3::escapeString($value)."'"; }

	/* ���������� �������������� ������ $value */
	public static function getEscape($value) { return SQLite3::escapeString($value); }

	/* ������������ � ���� */
	public function __construct($fname=null) {
		if(!$fname) $fname=core::path().'data/database3.db';
		self::$_connectId=new SQLite3($fname);
		self::$_connectId->createFunction('CONCAT',function() {
			return implode('',func_get_args());
		},-1);
	}

	/* ��������� ������������ SQL-������
	$limit - ���������� ��������� �� �������� (��� ���������), $page - ����� �������� (��� ���������) */
	public function query($query,$limit=null,$page=null) {
		if($limit!==null) {
			if($page) $page=(int)$page; else {
				if(isset($_GET['page'])) $page=((int)$_GET['page'])-1; else $page=0;
			}
			$this->_total=$this->fetchValue('SELECT COUNT(*)'.substr($query,stripos($query,' FROM ')));
			$query.=' LIMIT '.($page*$limit).','.$limit;
		}
		$this->_queryId=self::$_connectId->query($query);
		if($this->_queryId) return true;
		$cfg=core::config();
		if($cfg['debug']) echo '<p>SQLITE QUERY ERROR: '.$query.'</p>';
		return false;
	}

	/* ���������� ���������� ��������� ����� (� �������� ��� ���������) */
	public function foundRows() {
		return $this->_total;
	}

	/* ���������� ��������� ������ �� ������� � ���� ������� */
	public function fetch() {
		return $this->_queryId->fetchArray(SQLITE3_NUM);
	}

	/* ���������� ��������� ������ �� ������� � ���� �������������� ������� */
	public function fetchAssoc() {
		return $this->_queryId->fetchArray(SQLITE3_ASSOC);
	}

	/* ���������� ��������� ������ �� ������� � ���� �������������� ������� */
	public function fetchArrayOnce($query) {
		$query.=' LIMIT 0,1';
		$this->query($query);
		return $this->_queryId->fetchArray(SQLITE3_NUM);
	}

	/* ��������� ������ $query � ���������� ������ ������ � ���� �������������� ������� */
	public function fetchArrayOnceAssoc($query) {
		$query.=' LIMIT 0,1';
		$this->query($query);
		return $this->_queryId->fetchArray(SQLITE3_ASSOC);
	}

	/* ��������� ������ $query � ���������� ��� ������ � ���� ������� */
	public function fetchArray($query) {
		$this->query($query);
		$data=array();
		while($item=$this->_queryId->fetchArray(SQLITE3_NUM)) $data[]=$item;
		return $data;
	}

	/* ��������� ������ $query � ���������� ��� ������ � ���� �������������� ������� */
	public function fetchArrayAssoc($query,$limit=null,$page=null) {
		$this->query($query);
		$data=array();
		while($item=$this->_queryId->fetchArray(SQLITE3_ASSOC)) $data[]=$item;
		return $data;
	}

	/* ��������� ������ $query � ���������� ������������ �������� (������ ���� ������ ������) */
	public function fetchValue($query) {
		return self::$_connectId->querySingle($query);
	}

	/* ���������� �������� ���������� ����� ����������� ����� ������ */
	public function insertId() {
		return self::$_connectId->lastInsertRowID();
	}

	/* ���������� ���������� ��������� � ����������� ������� */
	public function affected() {
		return self::$_connectId->changes();
	}

/*
	public static function like($mask,$value) {
		return preg_match('/'.str_replace('%','.?',preg_quote($mask,'/')).'/i',$value);
	}
*/
}
?>