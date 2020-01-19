<?php
namespace plushka\admin\model;
use plushka\admin\core\plushka;

/**
 * Служебный помощник, высчитывающий количество ссылок на объекты
 * Используется для определения можно ли удалять связанные с виджетом или меню ресурсов
 */
class ObjectLink {

	/**
	 * Возвращает количество ссылок на виджет из секций
	 * @param string     $name Имя виджета
	 * @param mixed|null $data Данные виджета
	 * @return int
	 */
	public static function fromSectionWidget(string $name,$data=null): int {
		$db=plushka::db();
		$db->query('SELECT data FROM widget WHERE name='.$db->escape($name));
		$cnt=0;
		while($item=$db->fetch()) $cnt+=self::_compareData($data,$item[0]);
		return $cnt;
	}

	/**
	 * Возвращает количество ссылок на виджет из шаблонов
	 * @param string      $name     Имя виджета
	 * @param mixed|null  $data     Данные виджета
	 * @param string|null $template Шаблон
	 * @return int
	 */
	public static function fromTemplateWidget(string $name,$data=null,string $template=null): int {
		$basedir=plushka::path().'cache/template/';
		if($template!==null) $template=[$template.'.ini']; else {
			$d=opendir($basedir);
			$template=[];
			while($item=readdir($d)) {
				if($item==='.' || $item==='..') continue;
				if(substr($item,strlen($item)-4)!=='.ini') continue;
				$template[]=$item;
			}
			closedir($d);
		}
		$cnt=0;
		foreach($template as $item) {
			/** @noinspection PhpIncludeInspection */
			$cfg=include($basedir.$item);
			foreach($cfg['widget'] as $widget) {
				if($item[0]!==$name) continue;
				$cnt+=self::_compareData($data,$widget[1]);
			}
		}
		return $cnt;
	}

	private static function _compareData($dataBase,$dataItem): int {
		if(!$dataBase) return 1;
		if(is_array($dataBase)===false && is_object($dataBase)===false) {
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