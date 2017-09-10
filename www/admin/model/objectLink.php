<?php
/* Предназначен для определения количества ссылок на тот или иной элемент сайта (меню, виджет).
Используется при удалении контента при удалении виджета или пункта меню. Работает, конечно, не идеально, но достаточно эффективно. */
class modelObjectLink {

	/* Возвращает количество виджетов с именем $name во всех секциях и имеющих заданные настройки $data */
	public static function fromSectionWidget($name,$data=null) {
		$db=core::db();
		$db->query('SELECT data FROM widget WHERE name='.$db->escape($name));
		$cnt=0;
		while($item=$db->fetch()) $cnt+=modelObjectLink::_compareData($data,$item[0]);
		return $cnt;
	}

	/* Возвращает количество виджетов с именем $name, определённых в шаблоне с именем $template и заданных настройках виджета $data */
	public static function fromTemplateWidget($name,$data=null,$template=null) {
		//.ini-файлы создаются во время кеширвания шаблона
		$basedir=core::path().'cache/template/';
		if($template) $template=array($template.'.ini'); else {
			$d=opendir($basedir);
			$template=array();
			while($item=readdir($d)) {
				if($item=='.' || $item=='..') continue;
				if(substr($item,strlen($item)-4)!='.ini') continue;
				$template[]=$item;
			}
			closedir($d);
		}
		$cnt=0;
		foreach($template as $item) {
			$cfg=include($basedir.$item);
			foreach($cfg['widget'] as $widget) {
				if($item[0]!=$name) continue;
				$cnt+=modelObjectLink::_compareData($data,$widget[1]);
			}
		}
		return $cnt;
	}

	/* Сравнивает данные виджетов, возвращает 1 если данные в $dataBase совпадают с данными в $dataItem. */
	private static function _compareData($dataBase,$dataItem) {
		if(!$dataBase) return 1;
		if(is_string($dataBase) || is_integer($dataBase)) {
			if($dataBase==$dataItem) return 1; else return 0;
		} else {
			$dataItem=unserialize($dataItem);
			foreach($dataBase as $i=>$item) {
				if($item!=$dataItem[$i]) return 0;
			}
			return 1;
		}
	}

}