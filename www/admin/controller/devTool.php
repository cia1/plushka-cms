<?php
/* Инструменты разработчика */
class sController extends controller {

	/* Запрос выбора нужной операции */
	public function actionIndex() {
		$this->config=core::config();
		$this->formDbCompare=core::form(); //сравнение структуры баз данных
		$this->formDbCompare->submit('Сравнить структуру баз данных MySQL ('.$this->config['mysqlDatabase'].') и SQLite');
		$this->formDbMove=core::form(); //копирование данных базы данных
		$this->formDbMove->select('src','Направление:',array(
			array('mysql','MySQL ('.$this->config['mysqlDatabase'].') -> SQLite'),
			array('sqlite','SQLite -> MySQL ('.$this->config['mysqlDatabase'].')')
		));
		$this->formDbMove->submit('Скопировать');
		$this->formClear=core::form(); //удалине контента
		$this->formClear->submit('Очистить');
		if(file_exists(core::path().'tmp/dump.sql')) $this->backupDate=filectime(core::path().'tmp/dump.sql'); else $this->backupDate=null;

		$this->formExport=core::form(); //экспорт модуля
		$cfg=core::configAdmin('_module');
		$module=array();
		foreach($cfg as $id=>$item) {
			if($id=='core') continue;
			$module[]=array($id,$item['name']);
		}
		$this->formExport->select('id','Модуль',$module);
		$this->formExport->submit('Экспорт модуля в директорий /tmp');
		$f=core::path().'admin/data/devTool-image.php';
		if(file_exists($f)) {
			$this->imageDate=filemtime($f);
			$this->formImageCompare=core::form(); //сравнение текущего состояния системы со сделанным ранее снимком
			$this->formImageCompare->submit('Сравнить');
		} else $this->imageDate=null;
		$this->formImageMake=core::form(); //создание снимка состояния системы
		$this->formImageMake->submit('Сделать снимок');
		//Форма генератора кода модели
		$form=core::form();
		$form->select('dbDriver','СУБД',array(array('mysql','MySQLi'),array('sqlite','SQLite')),$this->config['dbDriver'],null,'id="dbDriver"');
		$form->select('table','Таблица',array());
		$form->checkbox('comment','Комментарии',true);
		$form->checkbox('save','Сохранить в файл',false);
		$form->submit();
		$this->formCodeModel=$form;
		//Форма генератора кода прав доступа
		$d=opendir(core::path().'admin/controller');
		$controller=array();
		while($f=readdir($d)) {
			if($f=='.' || $f=='..') continue;
			$f=substr($f,0,strlen($f)-4);
			$controller[]=array($f,$f);
		}
		unset($f);
		unset($d);
		$form=core::form();
		$form->select('controller','Контроллер',$controller);
		$form->submit();
		$this->formCodeRight=$form;
		return 'Index';
	}

	/* Настройки очистки движка от контента */
	public function actionSetting() {
		$cfg=core::configAdmin('devTool');
		$f=core::form();
		$struc=self::_structureDb(false);
		$html='';
		foreach($struc as $item) {
			$html.='<label><input type="checkbox" name="devTool[noClearTable][]" value="'.$item.'" '.(in_array($item,$cfg['noClearTable']) ? ' checked="checked"' : '').'/> '.$item.'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		$f->html('<dt style="width:100%;height:auto;">Не очищать эти таблицы:</dt><dd style="width:100%;height:auto;">'.$html.'<br /><br /></dd>');
		$f->textarea('clearFile','Очистить эти директории<span style="font-size:0.9em;font-style:italic;"><br />Можно использовать символы &laquo;*&raquo; и  &laquo;?&raquo;</span>',implode("\n",$cfg['clearFile']));
		$f->textarea('dropTable','Таблицы, которые нужно удалить',implode("\n",$cfg['dropTable']));
		$f->textarea('unlinkFile','Удалить эти файлы',implode("\n",$cfg['unlinkFile']));
		$f->submit();
		$this->cite='Разумеется, данные настройки зависят от установленных модулей.';
		return $f;
	}

	public function actionSettingSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config();
		$cfg->noClearTable=$data['noClearTable'];
		$cfg->clearFile=explode("\n",$data['clearFile']);
		$cfg->dropTable=explode("\n",$data['dropTable']);
		$cfg->unlinkFile=explode("\n",$data['unlinkFile']);
		$cfg->save('admin/devTool');
		core::success('Настройки сохранены');
		core::redirect('devTool');
	}


	/* Выполняет сравнение структуры баз данных двух СУБД */
	public function actionCompare() {
		$structureMySQL=self::_structureMySQL();
		$structureSQLite=self::_structureSQLite();
		$this->noTableSQLite=$this->noTableMySQL=array();
		//Наличие и отсутсвите таблиц
		foreach($structureMySQL as $item1=>$fields) if(!isset($structureSQLite[$item1])) $this->noTableSQLite[]=$item1;
		foreach($structureSQLite as $item1=>$fields) if(!isset($structureMySQL[$item1])) $this->noTableMySQL[]=$item1;
		//Структура таблиц (поля)
		$this->noFieldMySQL=$this->noFieldSQLite=array();
		foreach($structureMySQL as $item1=>$fields) {
			if(!isset($structureSQLite[$item1])) continue;
			$item2=$structureSQLite[$item1];
			foreach($fields as $item) if(!in_array($item,$item2)) $this->noFieldSQLite[]=array($item1,$item);
		}
		foreach($structureSQLite as $item1=>$fields) {
			if(!isset($structureMySQL[$item1])) continue;
			$item2=$structureMySQL[$item1];
			foreach($fields as $item) if(!in_array($item,$item2)) $this->noFieldMySQL[]=array($item1,$item);
		}
		return 'Compare';
	}

	/* Копирует данные из одной СУБД в другую */
	public function actionCopy() {}
	public function actionCopySubmit($data) {
		set_time_limit(0);
		if($data['src']=='mysql') {
			$struc=self::_structureMySQL();
			$dbSrc=core::mysql();
			$dbDst=core::sqlite();
		} elseif($data['src']=='sqlite') {
			$struc=self::_structureSQLite();
			$dbSrc=core::sqlite();
			$dbDst=core::mysql();
		}
		$error=array();
		foreach($struc as $table=>$field) {
			$dbDst->query('DELETE FROM `'.$table.'`');
			$q='INSERT INTO `'.$table.'` (`'.implode('`,`',$field).'`) VALUES (';
			$dbSrc->query('SELECT * FROM `'.$table.'`');
			$cnt=count($field);
				$success=0;
			while($item=$dbSrc->fetch()) {
				$s=$q;
				for($i=0;$i<$cnt;$i++) {
					if($i) $s.=',';
					if($item[$i]===null) $s.='null'; else $s.=$dbDst->escape($item[$i]);
				}
				$s.=')';
				if($dbDst->query($s)) $success++; else {
					if(!isset($error[$table])) $error[$table]=$field[0].'</b>: '.$item[0];
					else $error[$table].=', '.$item[0];
				}
			}
			echo '<br />Таблица <b>',$table,'</b> перенесена (',$success,' строк).';
			flush();
		}
		if($error) {
			echo '<p>Однако не все записи удалось перенести:<ul></p>';
			foreach($error as $table=>$item) {
				echo '<li><b>',$table,'.',$item,'</li>';
			}
			echo '</ul>';
		}
		echo '<p>Копирование завершено.</p>';
	}

	/*Очистка сайта от всего контента */
	public function actionClear() {
		$cfg=core::config();
		$dbDriver=$cfg['dbDriver'];
		if($dbDriver=='mysql') $structure=self::_structureMySQL(false); else $structure=self::_structureSQLite(false);
		$cfg=core::configAdmin('devTool');
		//Очистка таблиц
		$db=core::db();
		foreach($structure as $item) {
			if(in_array($item,$cfg['noClearTable'])) continue;
			$db->query('DELETE FROM `'.$item.'`');
			if($dbDriver=='mysql') $db->query('ALTER TABLE `'.$item.'` AUTO_INCREMENT=1');
//			else $db->query('UPDATE sqlite_sequence SET seq=1 WHERE Name=\''.$item.'\'');
		}
		//Разрушение таблиц
		foreach($cfg['dropTable'] as $item) {
			$db->query('DROP TABLE IF EXISTS `'.$item.'`');
		}
		//Особые случаи с базой данных
		$db->query('DELETE FROM menu WHERE id>0');
		$db->query('DELETE FROM user WHERE login NOT IN('.$db->escape('admin').','.$db->escape('editor').','.$db->escape('root').')');
		unset($structure);

		//Чистка директориев
		foreach($cfg['clearFile'] as $item) {
			$i=strpos($item,'*');
			if($i===false) {
				if(strpos($item,'?')===false) {
					self::_unlink($item);
					continue;
				}
			}
			$item=glob(core::path().$item);
			foreach($item as $item2) {
				if(file_exists($item2)) unlink($item2);
			}
		}
		//Удаление конкретных файлов
		$path=core::path();
		foreach($cfg['unlinkFile'] as $item) {
			if(file_exists($path.$item)) unlink($path.$item);
		}
		echo '<p>Очистка завершена.</p>';
	}

	/* Создание бэкапа MySQL */
	public function actionBackupCreate() {
		$cfg=core::config();
		$cmd='mysqldump';
		if($cfg['mysqlHost']!='127.0.0.1' && $cfg['mysqlHost']!='localhost') $cmd.=' -h '.$cfg['mysqlHost'];
		$cmd.=' -u'.$cfg['mysqlUser'];
		if($cfg['mysqlPassword']) $cmd.=' -p'.$cfg['mysqlPassword'];
		$cmd.=' '.$cfg['mysqlDatabase'].' > '.core::path().'tmp/dump.sql';
		exec($cmd);
		core::redirect('?controller=devTool&t='.time());
	}

	/* Удаляет файл бэкапа */
	public function actionBackupDelete() {
		$f=core::path().'tmp/dump.sql';
		if(file_exists($f)) unlink($f);
		core::redirect('?controller=devTool&t='.time());
	}

	/* Экспорт модуля в директорий /tmp */
	public function actionExport() {}
	public function actionExportSubmit($id) {
		$id=$id['id']; //имя модуля
		self::_unlink('tmp');
		$m=core::configAdmin('../module/'.$id);
		core::import('admin/model/module');
		module::explodeData($m);
		$sql1=$sql2='';
		$db0=core::db();
		$db1=core::mysql();
		$db2=core::sqlite();
		$right='';
		foreach($m['right'] as $item) {
			$data=$db0->fetchArrayOnce('SELECT description,groupId,picture FROM userRight WHERE module='.$db0->escape($item));
			$right.='right: '.$item."\t".$data[0]."\t".$data[2]."\n";
		}
		foreach($m['widget'] as $item) {
			$data=$db0->fetchArrayOnce('SELECT title,controller,action FROM widgetType WHERE name='.$db0->escape($item));
			$widget.='widget: '.$item."\t".$data[0]."\t".$data[1]."\t".$data[2]."\n";
		}
		$menu='';
		foreach($m['menu'] as $item) {
			$data=$db0->fetchArrayOnce('SELECT title,controller,action FROM menuType WHERE id='.$db0->escape($item));
			$menu.='menu: '.$data[0]."\t".$data[1]."\t".$data[2]."\n";
		}
		//Конструирование SQL-запроса (для двух СУБД)
		$lang=core::config();
		$lang=$lang['languageDefault'];
		foreach($m['table'] as $item) { //перебор таблиц из конфигурационного файла модуля
			$i=strpos($item,'(');
			if($i) {
				$langField=explode(' ',substr($item,$i+1,strlen($item)-$i-2));
				$item=substr($item,0,$i);
			}
			$langItem=$item;
			if(substr($item,strlen($item)-5)=='_LANG') {
				$item=substr($item,0,strlen($item)-4).$lang;
			}
			$db1->query('SHOW CREATE TABLE `'.$item.'`');
			$data=$db1->fetch();
			$data=$data[1];
			$i=strpos($data,' ENGINE=');
			if($i) $data=substr($data,0,$i);
			foreach($langField as $fld) {
				$data=str_replace($fld.'_'.$lang,$fld.'_LANG',$data);
			}
			if($langItem!=$item) $data=str_replace($item,$langItem,$data);
			$sql1.=$data.";\n";
			$data=$db2->fetchValue("SELECT sql FROM sqlite_master WHERE type='table' AND tbl_name='".$item."'").";\n";
			if($langItem!=$item) $data=str_replace($item,$langItem,$data);
			$sql2.=$data;
		}

		$path1=str_replace('\\','/',core::path());
		$path2=$path1.'tmp/';
		foreach($m['file'] as $item) {
			$f1=$path1.$item;
			$f2=$path2.$item;
			if(is_dir($f1)) mkdir($f2); else self::_copy($f1,$f2);
		}
		$f=fopen(core::path().'tmp/install.mysql.sql','w');
		fwrite($f,$sql1);
		fclose($f);
		unset($sql1);
		$f=fopen(core::path().'tmp/install.sqlite.sql','w');
		fwrite($f,$sql2);
		fclose($f);
		unset($sql2);
		$f1=core::path().'admin/module/'.$id.'.install.php';
		if(file_exists($f1)) {
			copy($f1,core::path().'tmp/install.php');
		}
		$cfg=core::configAdmin('_module');
		$f=fopen(core::path().'tmp/module.ini','w');
		fwrite($f,'id: '.$id."\n");
		fwrite($f,'name: '.$cfg[$id]['name']."\n");
		fwrite($f,'version: '.$cfg[$id]['version']."\n");
		fwrite($f,'url: '.$cfg[$id]['url']."\n");
		fwrite($f,$right);
		fwrite($f,$menu);
		fwrite($f,$widget);
		fclose($f);
		echo '<p>Модуль экспортирован в директорий /tmp.</p>';
	}

	/* Создаёт снимок */
	public function actionImageMake() {
		core::import('admin/core/config');
		$cfg=new config();
		$cfg->file=self::_scan(core::path()); //сканирование файловой системы
		$cfg->structure=self::_structureDb();
		$db=core::db();
		$db->query('SELECT name FROM widgetType');
		$tmp=array();
		while($item=$db->fetch()) $tmp[]=$item[0];
		$cfg->widgetType=$tmp;
		$tmp=array();
		$db->query('SELECT controller,action FROM menuType');
		while($item=$db->fetch()) $tmp[]=$item[0].'.'.$item[1];
		$cfg->menuType=$tmp;
		$tmp=array();
		$db->query('SELECT module FROM userRight');
		while($item=$db->fetch()) $tmp[]=$item[0];
		$cfg->userRight=$tmp;
		$cfg->save('../admin/data/devTool-image');
		core::success('Снимок создан');
		core::redirect('devTool');
	}

	/* Сравнение состояния сайта со сделанным ранее снимком */
	public function actionImageCompare() {
		//Сканирование файловой системы
		$cfg=core::configAdmin('../data/devTool-image');
		if(function_exists('array_column')) { //PHP 5.5 и позже
			$imageFile=array_column($cfg['file'],0);
			$imageSize=array_column($cfg['file'],1);
			$tmp=self::_scan(core::path());
			$currentFile=array_column($tmp,0);
			$currentSize=array_column($tmp,1);
		} else { //до PHP 5.5
			$imageFile=$imageSize=array();
			foreach($cfg['file'] as $item) {
				$imageFile[]=$item[0];
				$imageSize[]=$item[1];
			}
			$tmp=self::_scan(core::path());
			$currentFile=$currentSize=array();
			foreach($tmp as $item) {
				$currentFile[]=$item[0];
				$currentSize[]=$item[1];
			}
		}
		$this->fileCreate=$this->fileDelete=$this->fileChange=array();
		$pathLength=strlen(core::path());
		foreach($currentFile as $i=>$item) { //перебор существующих файлов
			$y=array_search($item,$imageFile);
			if($y===false) $this->fileCreate[]=$item;
			elseif($currentSize[$i]!=$imageSize[$y]) $this->fileChange[]=$item;
		}
		foreach($imageFile as $i=>$item) { //перебор файлов снимка
			if(!in_array($item,$currentFile)) $this->fileDelete[]=$item;
		}
		unset($imageFile);
		unset($imageSize);
		unset($currentFile);
		unset($currentSize);
		//Сверка структуры базы данных
		$structure=self::_structureDb();
		$tmp=$cfg['structure'];
		$this->tableCreate=$this->tableDrop=$this->tableModify=array();
		foreach($structure as $table=>$item) {
			if(!isset($tmp[$table])) $this->tableCreate[]=$table;
			elseif($tmp[$table]!=$item) $this->tableModify[]=$table;
		}
		foreach($tmp as $table=>$s) {
			if(!isset($structure[$table])) $this->tableDrop[]=$table;
		}
		//Виджеты (только создание новых)
		$db=core::db();
		$db->query('SELECT name,title FROM widgetType');
		$tmp=$cfg['widgetType'];
		$this->widgetCreate=array();
		$moduleWidget='';
		while($item=$db->fetch()) {
			if(in_array($item[0],$tmp)) continue;
			$this->widgetCreate[]=$item[1].' ('.$item[0].')';
			if($moduleWidget) $moduleWidget.=',';
			$moduleWidget.=$item[0];
		}
		//Типы меню
		$db->query('SELECT controller,action,title,id FROM menuType');
		$tmp=$cfg['menuType'];
		$this->menuCreate=array();
		$exists=array();
		$moduleMenu='';
		while($item=$db->fetch()) {
			$s=$item[0].'.'.$item[1];
			if(in_array($s,$tmp)) $exists[]=$s;
			else {
				$this->menuCreate[]=$item[2].' ('.$s.')';
				if($moduleMenu) $moduleMenu.=',';
				$moduleMenu.=$item[3];
			}
		}
		$this->menuDelete=array_diff($tmp,$exists); //удалённые или изменённые типы меню
		//Сверка наборов прав доступа
		$db->query('SELECT module,description FROM userRight');
		$tmp=$cfg['userRight'];
		$this->rightCreate=$this->rightDelete=array();
		$moduleRight=''; //Для построения шаблона модуля
		while($item=$db->fetch()) {
			if(in_array($item[0],$tmp)) $exists[]=$item[0];
			else {
				$this->rightCreate[]=$item[1].' ('.$item[0].')';
				if($moduleRight) $moduleRight.=',';
				$moduleRight.=$item[0];
			}
		}
		$this->rightDelete=array_diff($tmp,$exists); //удалённые права
		//Сгенерировать шаблон файла модуля на основании выявленных изменений
		$this->module="<?php return array(\n'depend'=>'ИМЯ_МОДУЛЯ ver МИНИМАЛЬНАЯ_ВЕРСИЯ', //указать, если модуль зависит от других модулей\n";
		if($this->rightCreate) $this->module.="'right'=>'".$moduleRight."',\n";
		unset($moduleRight);
		if($this->widgetCreate) $this->module.="'widget'=>'".$moduleWidget."',\n";
		unset($moduleWidget);
		if($this->menuCreate) $this->module.="'menu'=>'".$moduleMenu."',\n";
		unset($moduleMenu);
		if($this->tableCreate) $this->module.="'table'=>'".implode(',',$this->tableCreate)."',\n";
		if($this->fileCreate) $this->module.="'file'=>array(\n\t'".implode("',\n\t'",$this->fileCreate)."'\n)\n";
		$this->module.="); ?>";

		return 'ImageCompare';
	}

	public function actionTableList() {
		if($_GET['driver']=='sqlite') $structure=self::_structureSQLite(false);
		else $structure=self::_structureMySQL(false);
		echo '<select name="devTool[table]">';
		foreach($structure as $item) { ?>
			<option value="<?=$item?>"><?=$item?></option>
		<?php }
		echo '</select>';
	}

	public function actionCodeModel() {
		$f=core::form();
		$f->textarea('html',$this->table.(isset($_POST['devTool']['save']) ? '<br />Модель сохранена в файл /model/'.$this->table.'.php' : '').'.php',$this->template);
		return $f;
	}

	public function actionCodeModelSubmit($data) {
		$template=file_get_contents(core::path().'admin/data/devTool-model.php.txt');
		if(isset($data['comment'])) $template=str_replace(array('{{comment}}','{{/comment}}'),'',$template);
		else $template=preg_replace('|\{\{comment\}\}.*?\{\{/comment\}\}|is','',$template);

		if($_GET['driver']=='sqlite') $db=core::sqlite(); else $db=core::mysql();
		$structure=$db->getCreateTableQuery($data['table']);
		if(preg_match('|PRIMARY\s+KEY\s?\(([^)]+)|is',$structure,$primary)) {
			$primary=str_replace(array("'",'"','`'),'',trim($primary[1]));
		} else $primary=null;
		$structure=$db->parseStructure($structure);
		$rule=array();
		foreach($structure as $field=>$item) {
			if($field==$primary) $s="'".$field."'=>array('primary')";
			else {
				$type=self::_sqlType($item);
				$s="'".$field."'=>array('".$type[1]."','".strtoupper($field)."'";
				if(strpos($item,'NOT NULL')) $s.=',true';
				$max=self::_sqlMax($type[0],$item);
				if($max) $s.=",'max'=>".$max;
				$s.=')';
			}
			$rule[$field]=$s;
		}
		$rule=implode(",\n\t\t\t",$rule);
		$template=str_replace(
			array('{{table}}','{{fields}}','{{validateRule}}'),
			array($data['table'],implode(',',array_keys($structure)),$rule),
			$template
		);
		if(isset($data['save'])) {
			$f=fopen(core::path().'model/'.$data['table'].'.php','w');
			fwrite($f,$template);
			fclose($f);
		}
		$this->template=$template;
		$this->table=$data['table'];
	}

	public function actionCodeRight() {
		if(!$this->data) return '_empty';
		$f=core::form();
		$code="\tpublic function right() {\n\t\treturn array(\n\t\t\t'".
		implode($this->data,"'=>'',\n\t\t\t'").
		"'=>''\n\t\t);\n\t}";
		$f->textarea('code','',$code);
		return $f;
	}
	public function actionCodeRightSubmit($data) {
		$f=file_get_contents(core::path().'admin/controller/'.$data['controller'].'.php');
		if(!$f) {
			core::error('Не могу прочитать файл /admin/controller/'.$data['controller'].'.php');
			return;
		}
		preg_match_all('~function\s+action([a-zA-Z0-9_]+)~',$f,$f);
		$data=array();
		foreach($f[1] as $item) {
			if(substr($item,-6)=='Submit') continue;
			$data[]=$item;
		}
		$this->data=$data;
	}




/* --- PRIVALTE -------------------------------------------------------------------------------- */
	private static function _structureDb($field=true) {
		$cfg=core::config();
		if($cfg['dbDriver']=='mysql') return self::_structureMySQL($field); else return self::_structureSQLite($field);
	}

	/* Возвращает массив, содержащий структуру базы данных MySQL */
	private static function _structureMySQL($field=true) {
		$data=array();
		$db=core::mysql();
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

	/* Возвращает массив, содержащий структуру базы данных SQLite */
	private static function _structureSQLite($field=true) {
		$data=array();
		$db=core::sqlite();
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

	private static function _sqlType($query,$name) {
		$query=strtoupper($query);
		preg_match('|[a-zA-Z0-9_-]+|',$query,$type);
		$type=$type[0];
		switch($type) {
		case 'TINYINT': case 'SMALLINT': case 'MEDIUMINT': case 'BIGINT': case 'INT': case 'INTEGER':
			if(strpos($query,'UNSIGNED')) return array($type,'integer'); else return array($type,'float');
		case 'FLOAT': case 'DOUBLE':
			return array($type,'float');
		case 'SMALLTEXT': case 'MEDIUMTEXT': case 'LARGETEXT': case 'TEXT':
			return array($type,'html');
		case 'CHAR': case 'VARCHAR': case 'ENUM': case 'STRING': default:
			return array($type,'string');
		}
	}

	private static function _sqlMax($type,$query) {
		if($type=='ENUM') return false;
		switch($type) {
		case 'TINYINT': case 'SMALLINT': case 'MEDIUMINT': case 'BIGINT': case 'INT': case 'INTEGER':
			if($type=='TINYINT') $max=255;
			elseif($type=='SMALLINT') $max=65535;
			else $max=false;
			if($max && strpos($query,'UNSIGNED')) return round($max/2);
			return $max;
		case 'FLOAT': case 'DOUBLE': return false;
		}
		//Это строка...
		$query=substr($query,strlen($type));
		$query=str_replace(array(' ',"\t","'",'"','`'),'',$query);
		if($query[0]!='(') return false;
		return $query=(int)substr($query,1);
	}

	/* Рекурсивно удаляет файлы и директории */
	private static function _unlink($path) {
		if($path[strlen($path)-1]!='/') $path.='/';
		$s=core::path().$path;
		if(!is_dir($s)) return;
		$d=opendir($s);
		while($f=readdir($d)) {
			if($f=='.' || $f=='..') continue;
			if(is_dir($s.$f)) {
				self::_unlink($path.$f);
				rmdir($s.$f);
			} else unlink($s.$f);
		}
		closedir($d);
	}

	/* Копирует файл $f1 в $f2, создавая все несуществующие директории в $f2 */
	private static function _copy($f1,$f2) {
		$s=$f2;
		$i=strrpos($s,'/');
		if($i) {
			$s=substr($s,0,$i);
			$create=array();
			while(!is_dir($s)) {
				$create[]=$s;
				$s=substr($s,0,strrpos($s,'/'));
			}
			for($i=count($create)-1;$i>=0;$i--) mkdir($create[$i]);
		}
		copy($f1,$f2);
		return true;
	}

	private static function _scan($path) {
		$data=array();
		$pathRoot=substr($path,strlen(core::path()));
		$d=opendir($path);
		while($f=readdir($d)) {
			if($f=='.' || $f=='..' || $f=='cache' || $f=='database3.db' || $f=='tmp' || $f=='devTool-image.php') continue;
			$full=$path.$f;
			if(is_dir($full)) $data=array_merge($data,self::_scan($full.'/'));
			else $data[]=array($pathRoot.$f,filesize($full));
		}
		closedir($d);
		return $data;
	}

}