<?php
/* Инструменты разработчика */
class sController extends controller {

	public function right($action) {
		return false;
	}

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
		//Форма генератора кода
		return 'Index';
	}

	/* Настройки очистки движка от контента */
	public function actionSetting() {
		$cfg=core::configAdmin('devTool');
		$f=core::form();
		$struc=self::_structureDb(false);
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
		$cfg->save('../admin/config/devTool');
		core::redirect('devTool','Настройки сохранены');
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
			$right.='right: '.$item."\t".$data[0]."\t".$data[1]."\t".$data[2]."\n";
		}
		foreach($m['widget'] as $item) {
			$data=$db0->fetchArrayOnce('SELECT title,controller,action FROM widgetType WHERE name='.$db0->escape($item));
//			$sql1.='INSERT INTO widgetType (name,title,controller,action) VALUES ('.$db1->escape($item).','.$db1->escape($data[0]).','.$db1->escape($data[1]).','.$db1->escape($data[2]).");\n";
//			$sql2.='INSERT INTO widgetType (name,title,controller,action) VALUES ('.$db2->escape($item).','.$db2->escape($data[0]).','.$db2->escape($data[1]).','.$db2->escape($data[2]).");\n";
			$widget.='widget: '.$item."\t".$data[0]."\t".$data[1]."\t".$data[2]."\n";
		}
		$menu='';
		foreach($m['menu'] as $item) {
			$data=$db0->fetchArrayOnce('SELECT title,controller,action FROM menuType WHERE id='.$db0->escape($item));
//			$sql1.='INSERT INTO menuType (title,controller,action) VALUES ('.$db1->escape($data[0]).','.$db1->escape($data[1]).','.$db1->escape($data[2]).");\n";
//			$sql2.='INSERT INTO menuType (title,controller,action) VALUES ('.$db2->escape($data[0]).','.$db2->escape($data[1]).','.$db2->escape($data[2]).");\n";
			$menu.='menu: '.$data[0]."\t".$data[1]."\t".$data[2]."\n";
		}
//		core::import('admin/core/config');
//		$data=array();
//		if($m['hook1']) {
//			mkdir(core::path().'tmp/config');
//			foreach($m['hook1'] as $item) {
//				if(!isset($data[$item[0]])) $data[$item[0]]=array();
//				$data[$item[0]][]=$item[1];
//			}
//			$cfg=new config();
//			$cfg->setData($data);
//			$cfg->save('../tmp/config/_hook');
//		}
//		if($m['hook2']) {
//			foreach($m['hook2'] as $item) {
//				if(!isset($data[$item[0]])) $data[$item[0]]=array();
//				$data[$item[0]][]=$item[1];
//			}
//			mkdir(core::path().'tmp/admin');
//			mkdir(core::path().'tmp/admin/config');
//			$cfg=new config();
//			$cfg->setData($data);
//			$cfg->save('../tmp/admin/config/_hook');
//		}
		foreach($m['table'] as $item) {
			$db1->query('SHOW CREATE TABLE `'.$item.'`');
			$data=$db1->fetch();
			$data=$data[1];
			$i=strpos($data,' ENGINE=');
			if($i) $data=substr($data,0,$i);
			$sql1.=$data.";\n";
			$sql2.=$db2->fetchValue("SELECT sql FROM sqlite_master WHERE type='table' AND tbl_name='".$item."'").";\n";
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
		core::redirect('devTool','Снимок создан');
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

/* ДОбавляет новые права */
//	public function actionRight() {
//		if(controller::$error) $this->view='Index'; else $this->view=null;
//	}
//	public function actionRightSubmit($data) {
//		$db=core::db();
//		if($db->fetchValue('SELECT 1 FROM userRight WHERE module='.$db->escape($data['module']))) {
//			controller::$error='Группа прав для модуля &laquo;'.$data['module'].'&raquo; уже существует';
//			return false;
//		}
//		$db->query('INSERT INTO userRight (module,description,groupId,picture) VALUES('.$db->escape($data['module']).','.$db->escape($data['description']).','.$db->escape($data['group']).','.($data['picture'] ? $db->escape($data['picture']) : 'NULL').')');
//		echo 'Новая группа прав создана';
//	}

}
?>