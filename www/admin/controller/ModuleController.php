<?php
namespace plushka\admin\controller;
use plushka;
use plushka\admin\model\Module;

/* Установка и удаление модулей. Выполняется по шагам, чтобы при возникновении ошибок в любой момент можно было сделать откат */
class ModuleController extends \plushka\admin\core\Controller {

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Список установленных модулей */
	public function actionIndex() {
		$this->button('module/installTmp','install','Установить модуль из директория');
		$this->button('module/installZip','install','Установить модуль из архива');
		$items=Module::getList();
		$status=array(0=>'не установлен',1=>'установка, шаг 2',2=>'установка, шаг 3',3=>'установка, шаг 4',4=>'установка, шаг 5',5=>'установка, шаг 6',6=>'установка, шаг 7',7=>'установка, шаг 8',8=>'установка, шаг 9',100=>'работает');
		//Сформировать таблицу (список модулей)
		$table=plushka::table();
		$table->rowTh('ID|Модуль|Версия|Состояние|URL|<input type="submit" class="buttom" value="Удалить">|');
		foreach($items as $i=>$item) {
			$table->text($i);
			$table->text($item['name']);
			$table->text($item['version']);
			$table->text($status[$item['status']]);
			$table->text($item['url']);
			$table->checkbox('id',$i);
			//Ссылка "удалить" или "отменить", "продолжить установку" в зависимости от состояния модуля
			if($i=='core') $s='';
			else {
				if($item['status']==100) $s='<a href="'.plushka::link('admin/module/uninstall?id='.$i).'" onclick="return confirm(\'Подтвердите удаление модуля\');">'.($item['status']==0 || $item['status']==100 ? 'Удалить' : 'Отменить установку').'</a>';
				else $s='<a href="'.plushka::link('admin/module/uninstall?id='.$i).'" onclick="return confirm(\'Подтвердите отмену установки\');">'.($item['status']==0 || $item['status']==100 ? 'Удалить' : 'Отменить установку').'</a>';
				if($item['status']<99) $s.=' | <a href="'.plushka::link('admin/module/installTmp?id='.$i).'">Продолжить</a>';
			}
			$table->text($s,null,'style="text-align:center;"');
		}
		$this->table=$table;
		$this->cite='<u>Внимание</u>! Удаление или некорректная установка многих модулей может привести к нарушению работоспособности сайта. Некоторые модули в представленном списке являются необходимыми для корректной работы системы.';
		return 'Index';
	}

	protected function helpIndex() {
		return 'core/module';
	}

	//Начало установки модуля: загрузка архива модуля
	public function actionInstallZip() {
		$f=plushka::form();
		$f->file('archive','.zip-архив');
		$f->submit('Продолжить','submit');
		return $f;
	}

	protected function helpInstallZip() {
		return 'core/module#zip';
	}

	public function actionInstallZipSubmit($data) {
		if(!class_exists('ZipArchive')) {
			plushka::error('Расширение ZipArchive не установлено на вашем сервере. Самостоятельно распакуйте архив в директорий '.$_SERVER['DOCUMENT_ROOT'].'/tmp и установите модуль.');
			return false;
		}
		if(!$data['archive']['size']) {
			plushka::error('Архив не загружен.');
			return false;
		}
		//Очистить директорий /tmp
		Module::clearDirectory(plushka::path().'tmp',false);
		//Извлечь содержимое архива в /tmp
		$zip=new ZipArchive();
		if($zip->open($data['archive']['tmpName'])!==true) {
			plushka::error('Ошибка при попытке открыть архив');
			return false;
		}
		if(!$zip->extractTo(plushka::path().'tmp')) {
			plushka::error('Ошибка при попытке распаковать архив');
			return false;
		}
		$this->url[1]='installTmp';
		return $this->actionInstallTmp();
	}

	/* Начало установки модуля: выводит информацию о модуле. Он должен быть помещён в директорий /tmp */
	public function actionInstallTmp() {
		$module=Module::info(); //Информация о найденном в директории /tmp модуле
		if(!$module) {
			plushka::error('В директории /tmp нет файла module.ini. Возможно устанавливаемый модуль не загружен?');
			return '_empty';
		} elseif($module['status']==100) $this->moduleExists=true; //такой модуль уже установлен
		else $this->moduleExists=false;
		$this->pageTitle='Установка модуля';
		$this->module=$module;
		return 'Info';
	}

	protected function helpInstallTmp() {
		return 'core/module#tmp';
	}

	/* Непосредственно начинает процесс установки */
	public function actionInstallStart() {
		$module=Module::info(); //Информация о модуле
		//Обновить права текущего пользователя, чтобы не нужно было делать "выйти-войти"
		$u=plushka::user();
		foreach($module['right'] as $item) {
			if(isset($item[2])) $group=explode(',',$item[2]); else $group=array();
			if(in_array($u->group,$group)) $u->right[$item[0]]=($item[3] ? true : false);
		}
		if($module['status']==100) $module['status']=0; else $module['status']++; //сброс статуса в случае обновления
		$s='_install'.$module['status']; //Этап установки (если была прервана)
		if($this->$s($module)) {
			//Удалить атрибут currentVersion
			$cfg=new \plushka\admin\core\Config('admin/module/'.$module['id']);
			unset($cfg->currentVersion);
			$cfg->save('admin/module/'.$module['id']);
			plushka::redirect('module','Модуль установлен');
		}
		return 'Info';
	}

	/* Начинает процесс удаления модуля */
	public function actionUninstall() {
		if(!is_array($_GET['id'])) $_GET['id']=array($_GET['id']);
		foreach($_GET['id'] as $id) {
			$module=plushka::config('admin/../module/'.$id); //Информация об установленном модуле
			Module::explodeData($module); //В конфигурации информация хранится в сжатом виде - разобрать её на массивы
			$module['id']=$id; //Идентификатор модуля (строка)
			if(!self::_uninstall($module)) return $this->actionIndex();
			unset($module);
		}
		plushka::redirect('module','Модуль удалён');
	}
/* ----------------------------------------------------------------------------------- */


/* ----------- PRIVATE --------------------------------------------------------------- */
	private static function _uninstall($module) {
		//Сразу же убрать права текущего пользователя, чтоб не пришлось делать "выйти-войти"
		if(!$module['currentVersion']) {
			$u=plushka::user();
			foreach($module['right'] as $item) {
				if(isset($u->right[$item])) unset($u->right[$item]);
			}
			return self::_uninstall1($module);
		} else return self::_uninstall2($module);
	}

	/* Проверяет зависимости модуля от других (ему может потребоваться для работы какой-то модуль) */
	private static function _install0(&$module) {
		//$depend - список зависимостей в виде строки "module1 ver 1.0, module2 ver 1.4..."
		if($module['depend']) {
			$depend=explode(',',$module['depend']);
			foreach($depend as $item) {
				$i=strrpos($item,'ver');
				$m=trim(substr($item,0,$i));
				$version1=(int)str_replace('.','',trim(substr($item,$i+3)));
				$version2=Module::version($m);
				if(!$version2) {
					plushka::error('Устанавливаемый модуль требует наличия зависимого модуля &laquo;'.$m.'&raquo; (версия  '.trim(substr($item,$i+3)).').');
					return false;
				}
				if($version1>(int)str_replace('.','',$version2)) {
					plushka::error('Зависимый модуль &laquo;'.$m.'&raquo; имеет версию '.$version2.', но устанавливаемый модуль требует версию '.trim(substr($item,$i+3)).'.');
					return false;
				}
			}
		}
		return self::_install1($module); //Следующий этап установки
	}

	/* Создаёт и обновляет конфигурацию модуля (/admin/module/ИД.php) */
	private static function _install1(&$module) {
		Module::create($module['id'],$module['name'],$module['version'],$module['url'],$module['currentVersion']); //Создать конфигурацию и заполнить основные данные
		Module::depend($module['id'],$module['depend']); //Устанавливает у зависимых модулей пометку об устанавливаемом модуле
		Module::status($module['id'],1); //Сохранить этап установки
		return self::_install2($module);
	}

	/* Выполняет специальный скрипт установки, если он есть у данного модуля */
	private static function _install2(&$module) {
		$s=plushka::path().'tmp/install.php'; //Должен вернуть false, если установить модуль нельзя и true, если всё нормально
		if(file_exists($s)) {
			include($s);
			if(function_exists('installBefore')) { //"перед установкой"
				if(!installBefore($module['currentVersion'])) {
					if(!plushka::error()) plushka::error('По неизвестной причине установка модуля невозможна');
					return false;
				}
			}
		}
		Module::status($module['id'],2); //Сохранить этап установки
		return self::_install3($module); //Перейти к следующему этапу
	}

	/* Добавление прав */
	private static function _install3(&$module) {
		Module::right($module['id'],$module['right']);
		Module::status($module['id'],3);
		return self::_install4($module);
	}

	/* Добавление типов виджетов */
	private static function _install4(&$module) {
		Module::widget($module['id'],$module['widget']);
		Module::status($module['id'],4);
		return self::_install5($module);
	}

	/* Добавление типов меню */
	private static function _install5(&$module) {
		Module::menu($module['id'],$module['menu']);
		Module::status($module['id'],5);
		return self::_install6($module);
	}

	/* Добавление обработчиков событий (админка и общедоступная часть) */
	private static function _install6(&$module) {
		return self::_install7($module);
	}

	/* Выполнение специальных SQL-запросов, предусмотренных модулем */
	private static function _install7(&$module) {
		if(!$module['currentVersion']) {
			Module::sql($module['id']);
			Module::status($module['id'],7);
		}
		return self::_install8($module);
	}

	/* Проверяет наличие на сайте файлов модуля. Установка невозможна, если хотя бы один файл уже существует. */
	private static function _install8(&$module) {
		$exists=Module::fileList($module['id'],(bool)$module['currentVersion']);
		if(is_array($exists) && !$module['currentVersion']) {
			plushka::error('Установка невозможна, так как некоторые файлы уже существуют. Список конфликтов:<ul><li>'.implode('</li><li>',$exists).'</li></ul>');
			return false;
		}
		Module::status($module['id'],8);
		return self::_install9($module);
	}

	/* Копирует файлы модуля */
	private static function _install9(&$module) {
		Module::copy($module['id']);
		Module::status($module['id'],9);
		return self::_install10($module);
	}

	/* Выполняет специальный скрипт, выполняющий какие-либо действия после завершения установки модуля */
	private static function _install10(&$module) {
		$f1=plushka::path().'tmp/install.php';
		if(file_exists($f1)) {
			$f2=plushka::path().'admin/module/'.$module['id'].'.install.php';
			copy($f1,$f2);
			include_once($f1);
			if(function_exists('installAfter')) { //"после установки"
				if(!installAfter($module['currentVersion'])) return false;
			}
		}
		Module::status($module['id'],100);
		return true;
	}

	//Первый этап удаления: возможно ли сейчас удалить модуль? */
	private static function _uninstall1(&$module) {
		//Запретить удаление, если этот модуль необходим для работы других
		$depend=Module::dependSearch($module['id']);
		if($depend) {
			plushka::error('Этот модуль используется другими модулями: &laquo;'.implode('&raquo;,&laquo;',$depend).'&raquo;. Удаление невозможно.');
			return false;
		}
		$db=plushka::db();
		//Запретить удаление, если в меню есть ссылки на страницы этого модуля
		if(is_array($module['menu'])) $s=implode(',',$module['menu']); else $s=$module['menu'];
		if($s) $items=$db->fetchArray('SELECT title_'._LANG.' title FROM menu_item WHERE typeId IN('.$s.')'); else $items=null;
		if($items) {
			$s='';
			foreach($items as $item) if($s) $s.='&raquo;,&laquo;'.$item[0]; else $s=$item[0];
			plushka::error('Удаление модуля невозможно: существуют ссылки в меню (пункты &laquo;'.$s.'&raquo;). Удалите эти пункты мени и повторите попытку.');
			return false;
		}
		//Запретить удаление, если существуют виджеты этого модуля (по хорошему нужно ещё проверять .ini-файл шаблона)
		$s='';
		foreach($module['widget'] as $item) {
			if($s) $s.=',';
			$s.=$db->escape($item);
		}
		if($s) $items=$db->fetchArray('SELECT title_'._LANG.' title FROM widget WHERE name IN('.$s.')'); else $items=null;
		if($items) {
			$s='';
			foreach($items as $item) if($s) $s.='&raquo;,&laquo;'.$item[0]; else $s=$item[0];
			plushka::error('Удаление модуля невозможно: существуют созданные виджеты (&laquo;'.$s.'&raquo;). Сначала удалите их.');
			return false;
		}
		Module::status($module['id'],99);
		return self::_uninstall2($module);
	}

	/* Выполняет непосредственно удаление модуля */
	private static function _uninstall2(&$module) {
		//Если существует специальный скрипт удаления, то выполнить сначала его
		$f=plushka::path().'admin/module/'.$module['id'].'.install.php';
		if(file_exists($f)) {
			include($f);
			if(function_exists('uninstallBefore')) { //"перед удалением"
				if(!uninstallBefore()) return false;
			}
		}
		if(isset($module['currentVersion'])) $currentVersion=$module['currentVersion']; else $currentVersion=null;
		if(!$currentVersion) { //удалять только если модуль не был ранее установлен
			Module::dropDb($module); //Удалить все таблицы модуля
		}
		if(function_exists('uninstallAfter')) uninstallAfter($currentVersion); //Скрипт "после удаления"
		if($currentVersion) {
			Module::status($module['id'],100);
			return true;
		}
		if(file_exists($f)) unlink($f); //Удалить сам скрипт удаления
		if(!Module::unlink($module['file'])) return false; //Удалить все файлы модуля
		return Module::delete($module['id']); //Удалить конфигурацию модуля
	}

/* ----------------------------------------------------------------------------------- */

}
