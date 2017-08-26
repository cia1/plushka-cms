<?php
function linkSource($value) {
	static $db;
	if(!$db) $db=core::db();
	$value=explode(':',$value);
	switch($value[0]) {
	case 'event_ru': case 'event_en':
var_dump('SELECT alias FROM '.$value[0].' WHERE id='.intVal($value[1]));
		$alias=$db->fetchValue('SELECT alias FROM '.$value[0].' WHERE id='.intVal($value[1]));
		return '<a href="""></a>';
		var_dump($alias);
		exit;
	case 'object':
	case 'city':
	}
	var_dump($value);
	return 'asdf';
}

function linkTo($value) {

}