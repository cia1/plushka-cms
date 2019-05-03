<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\core;
use plushka;
use plushka\admin\core\config;

/**
 * Класс, выполняющий базовое кэширование шаблона и структуры базы данных
 */
class Cache {

	/**
	 * Создаёт кэш шаблона
	 * @param string $name Имя шаблона
	 */
	public static function template($name) {
		if(substr($_SERVER['REQUEST_URI'],0,7)=='/admin/') $adminPath='admin/'; else $adminPath='';
		$template=file_get_contents(plushka::path().$adminPath.'template/'.$name.'.html');
		$template=str_replace('{{metaTitle}}','<?=$this->metaTitle?>',$template);
		$template=str_replace('{{metaKeyword}}','<?php if($this->metaKeyword) echo \'<meta name="keyword" content="\'.$this->metaKeyword.\'" />\'; ?>',$template);
		$template=str_replace('{{metaDescription}}','<?php if($this->metaDescription) echo \'<meta name="description" content="\'.$this->metaDescription.\'" />\'; ?>',$template);
		$template=str_replace('{{head}}','<?=$this->_head?>',$template);
		$template=str_replace('{{pageTitle}}','<?php if($this->pageTitle) echo \'<h1 class="pageTitle">\'.$this->pageTitle.\'</h1>\'; ?>',$template);
		$template=str_replace('{{breadcrumb}}','<?php $this->breadcrumb(); ?>',$template);
		$section=$widget=array(); //Список секций и виджетов в шаблоне - эта информация может кому-то понадобиться в последствии
		$template=preg_replace_callback('/{{section\[([^\]]+)\]}}/',function($data) use(&$section) {
			if(!isset($data[1])) return;
			$section[]=$data[1];
			return '<?=plushka::section(\''.$data[1].'\')?>';
		},$template);
		$template=preg_replace_callback('/{{widget\[([^\]]+)\](?:\[([^\]]*?)\]|)(?:\[([^\]]*?)\]|)(?:\[([^\]]+)\]|)}}/',function($data) use(&$widget) {
			if(!isset($data[1])) return;
			$s='<?=plushka::widget(\''.$data[1].'\'';
			if(isset($data[2])) {
				$sub=$data[2]."\0";
				$quote=false;
				$isArray=false;
				$start=0;
				$param='';
				for($i=0,$len=strlen($sub);$i<$len;$i++) {
					if(!$quote && ($sub[$i]=='"' || $sub[$i]=="'")) {
						$quote=$sub[$i];
						$start++;
						continue;
					}
					if($quote && $sub[$i]==$quote) {
						$quote=false;
						continue;
					}
					if($sub[$i]==':' && !$quote) {
						if($param) $param.=',';
						$param.="'".str_replace(array('"',"'"),'',substr($sub,$start,($i-$start)))."'=>";
						$start=$i+1;
					}
					if(!$quote && ($sub[$i]==',' || $sub[$i]==="\0")) {
						$value=str_replace(array('"',"'"),'',substr($sub,$start,($i-$start)));
						if(is_numeric($value)) $param.=$value; else $param.="'".$value."'";
						$start=$i+1;
						if($sub[$i]==',') $isArray=true;
					}
				}
				if($isArray) $s.=',array('.$param.')';
				else $s.=','.$param;
			} else $param=null;
			$widget[]=array($data[1],$param);
			if(isset($data[3]) && (int)$data[3]) $s.=','.(int)$data[3];
			if(isset($data[4]) && $data[4]) {
				if(!isset($data[3]) || !$data[3]) $s.=',null';
				$s.=",'".$data[4]."'";
			}
			$s.=')?>';
			return $s;
		},$template);

		$i=strpos($template,'{{content}}');
		$f=fopen(plushka::path().$adminPath.'cache/template/'.$name.'Head.php','w');

		$use=[];
		if($i!==false) {
			$top=substr($template,0,$i);
			$cnt=preg_match_all('~<\?php.*?use\s+([\\\a-z-A-Z0-9_]+);~',$top,$tmp);
			for($y=0;$y<$cnt;$y++) $use[]=$tmp[1][$y];
			fwrite($f,$top);
			unset($tmp,$top);
		} else fwrite($f,$template);
		fclose($f);

		$f=fopen(plushka::path().$adminPath.'cache/template/'.$name.'Footer.php','w');
		if($i!==false) {
			if(count($use)>0) {
				fwrite($f,'<?php ');
				foreach($use as $item) fwrite($f,'use '.$item.'; ');
				fwrite($f,'?>');
			}
			fwrite($f,substr($template,$i+11));
		}
		fclose($f);
		$s='<?php return array(\'widget\'=>array(';
		$cnt=count($widget);
		for($i=0;$i<$cnt;$i++) {
			if($i) $s.=',';
			$s.='array(\''.$widget[$i][0].'\','.($widget[$i][1]===null ? 'NULL' : $widget[$i][1]).')';
		}
		$s.='),\'section\'=>array(\''.implode('\',\'',$section).'\')); ?>';
		$f=fopen(plushka::path().$adminPath.'cache/template/'.$name.'.ini','w');
		fwrite($f,$s);
	}

	/**
	 * Кэширует информацию о мультиязычных таблицах основной (plushka::db()) базы данных
	 */
	static function languageDatabase() {
		$cfg=plushka::config();
		if($cfg['dbDriver']=='mysql') $dbStructure=self::_structureMySQL();
		else $dbStructure=self::_structureSQLite();
		if(isset($cfg['languageList'])) $languageList=$cfg['languageList']; else $languageList=array($cfg['languageDefault']);
		$lang=array();
		foreach($dbStructure as $table=>$fieldList) {
			if(substr($table,-3,1)=='_') {
				if(in_array(substr($table,-2),$languageList)) {
					$table=substr($table,0,strlen($table)-3);
					if(!isset($lang[$table])) $lang[$table]=true;
					continue;
				}
			}
			$field=array();
			foreach($fieldList as $item) {
				if(substr($item,-3,1)=='_') {
					if(in_array(substr($item,-2),$languageList)) {
						$item=substr($item,0,strlen($item)-3);
						if(!in_array($item,$field)) $field[]=$item;
					}
				}
			}
			if($field) $lang[$table]=$field;
		}
		$cfg=new Config();
		$cfg->setData($lang);
		$cfg->save('../cache/language-database');
	}

	/**
	 * Загружает структуру базы данных MySQL
	 * @param bool $field Загружать список полей таблиц (true) или только список  таблиц (false)
	 * @return array
	 */
	private static function _structureMySQL($field=true) {
		$data=array();
		$db=plushka::mysql();
		$db->query('SHOW TABLES');
		if(!$field) {
			while($item=$db->fetch()) $data[]=$item[0];
			return $data;
		}
		while($item=$db->fetch()) $data[$item[0]]=array();
		foreach($data as $table=>$value) {
			$db->query('SHOW FIELDS FROM `'.$table.'`');
			while($item=$db->fetch()) $data[$table][]=$item[0];
		}
		return $data;
	}

	/**
	 * Загружает структуру базы данных SQLite
	 * @param bool $field Загружать список полей таблиц (true) или только список  таблиц (false)
	 * @return array
	 */
	private static function _structureSQLite($field=true) {
		$data=array();
		$db=plushka::sqlite();
		$db->query('SELECT name FROM sqlite_master WHERE type="table"');
		if(!$field) {
			while($item=$db->fetch()) $data[]=$item[0];
			return $data;
		}
		while($item=$db->fetch()) $data[$item[0]]=array();
		foreach($data as $table=>$value) {
			$db->query('PRAGMA table_info('.$table.')');
			while($item=$db->fetch()) $data[$table][]=$item[1];
		}
		return $data;
	}

}