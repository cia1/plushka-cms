<?php

include('./core/core.php');

core::import('core/model');
class test extends model {

	public function __construct($table=null,$db='db') {
		parent::__construct('widget');
		$this->multiLanguage();
	}

	protected function fieldList($action) {
		if($action==='load') {
return '*';
		} else {

		}
die("EE");
	}

	protected function rule() {
		return array(
			'id'=>array('primary'),
			'groupId'=>array('integer','group id',true,'max'=>255)
		);
	}

}

$tmp=new test();
$tmp->load('id=1');


var_dump($tmp);