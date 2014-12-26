<?php
/* Установка и удаление модулей. Выполняется по шагам, чтобы при возникновении ошибок в любой момент можно было сделать откат */
class sController extends controller {

	public function right($right) {
		if(isset($right['module.*'])) return true; else return false;
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Список установленных модулей */
	public function actionIndex() {
		$this->button('?controller=module&action=install','install','Установить модуль');
		core::import('admin/model/module');
		$items=module::getList();
		$status=array(0=>'не установлен',1=>'установка, шаг 2',2=>'установка, шаг 3',3=>'установка, шаг 4',4=>'установка, шаг 5',5=>'установка, шаг 6',6=>'установка, шаг 7',7=>'установка, шаг 8',8=>'установка, шаг 9',100=>'работает');
		//Сформировать таблицу (список модулей)
		$t=core::table();
		$t->rowTh('ID|Модуль|Версия|Состояние|URL|');
		foreach($items as $i=>$item) {
			$t->text($i);
			$t->text($item['name']);
			$t->text($item['version']);
			$t->text($status[$item['status']]);
			$t->text($item['url']);
			//Ссылка "удалить" или "отменить", "продолжить установку" в зависимости от состояния модуля
			if($i=='core') $s='';
			else {
				$s='<a href="'.core::link('?controller=module&action=uninstall&id='.$i).'" onclick="return confirm(\'Подтвердите удаление модуля\');">'.($item['status']==0 || $item['status']==100 ? 'Удалить' : 'Отменить установку').'</a>';
				if($item['status']<99) $s.=' | <a href="'.core::link('?controller=module&action=install&id='.$i).'">Продолжить</a>';
			}
			$t->text($s,null,'style="text-align:center;"');
		}
		$this->cite='<u>Внимание</u>! Удаление или некорректная установка многих модулей может привести к нарушению работоспособности сайта. Некоторые модули в представленном списке являются необходимыми для корректной работы системы.';
		return $t;
	}

	/* Начало установки: выводит информацию о модуле. Он должен быть помещён в директорий /tmp */
	public function actionInstall() {
		core::import('admin/model/module');
		$module=module::info(); //Информация о найденном в директории /tmp модуле
		if(!$module) {
			controller::$error='В директории /tmp нет файла module.ini. Возможно устанавливаемый модуль не загружен?';
			return 'Message';
		} elseif($module['status']==100) { //Такой модуль уже установлен
			controller::$error='Модуль &laquo;'.$module['name'].' версия '.$module['version'].'&raquo; уже установлен.';
			return 'Message';
		}
		$this->pageTitle='Установка модуля';
		$this->module=$module;
		return 'Info';
	}

	/* Непосредственно начинает процесс установки */
	public function actionInstallStart() {
		core::import('admin/model/module');
		$module=module::info(); //Информация о модуле
		$s='_install'.$module['status']; //Этап установки (если была прервана)
		//Обновить права текущего пользователя, чтобы не нужно было делать "выйти-войти"
		$u=core::user();
		foreach($module['right'] as $item) {
			$group=explode(',',$item[2]);
			if(in_array($u->group,$group)) $u->right[$item[0]]=($item[3] ? true : false);
		}
		if($this->$s($module)) core::redirect('?controller=module','Модуль установлен');
		return 'Info';
	}

	/* Начинает процесс удаления модуля */
	public function actionUninstall() {
		core::import('admin/model/module');
		$module=core::configAdmin('../module/'.$_GET['id']); //Информация об установленном модуле
		module::explodeData($module); //В конфигурации информация хранится в сжатом виде - разобрать её на массивы
		$module['id']=$_GET['id']; //Идентификатор модуля (строка)
		$db=core::db();
		//Сразу же убрать права текущего пользователя, чтоб не пришлось делать "выйти-войти"
		$u=core::user();
		foreach($module['right'] as $item) {
			if(isset($u->right[$item])) unset($u->right[$item]);
		}
		if(self::_uninstall1($module)) core::redirect('?controller=module','Модуль удалён'); //Нужный этап удаления
		return $this->actionIndex();
	}
/* ----------------------------------------------------------------------------------- */


/* ----------- PRIVATE --------------------------------------------------------------- */
	/* Проверяет зависимости модуля от других (ему может потребоваться для работы какой-то модуль) */
	private static function _install0(&$module) {
		//$depend - список зависимостей в виде строки "module1 ver 1.0, module2 ver 1.4..."
		$depend=explode(',',$module['depend']);
		foreach($depend as $item) {
			$i=strrpos($item,'ver');
			$m=trim(substr($item,0,$i));
			$version1=(int)str_replace('.','',trim(substr($item,$i+3)));
			$version2=module::version($m);
			if(!$version2) {
				controller::$error='Устанавливаемый модуль требует наличия зависимого модуля &laquo;'.$m.'&raquo; (версия  '.trim(substr($item,$i+3)).').';
				return false;
			}
			if($version1>(int)str_replace('.','',$version2)) {
				controller::$error='Зависимый модуль &laquo;'.$m.'&raquo; имеет версию '.$version2.', но устанавливаемый модуль требует версию '.trim(substr($item,$i+3)).'.';
				return false;
			}
		}
		return self::_install1($module); //Следующий этап установки
	}

	/* Создаёт и обновляет конфигурацию модуля (/admin/module/ИД.php) */
	private static function _install1(&$module) {
		module::create($module['id'],$module['name'],$module['version'],$module['url']); //Создать конфигурацию и заполнить основные данные
		module::depend($module['id'],$module['depend']); //Устанавливает у зависимых модулей пометку об устанавливаемом модуле
		module::status($module['id'],1); //Сохранить этап установки
		return self::_install2($module);
	}

	/* Выполняет специальный скрипт установки, если он есть у данного модуля */
	private static function _install2(&$module) {
		$s=core::path().'tmp/install.php'; //Должен вернуть false, если установить модуль нельзя и true, если всё нормально
		if(file_exists($s)) {
			include($s);
			if(function_exists('installBefore')) { //"перед установкой"
				if(!installBefore()) {
					if(!controller::$error) controller::$error='По неизвестной причине установка модуля невозможна';
					return false;
				}
			}
		}
		module::status($module['id'],2); //Сохранить этап установки
		return self::_install3($module); //Перейти к следующему этапу
	}

	/* Добавление прав */
	private static function _install3(&$module) {
		module::right($module['id'],$module['right']);
		module::status($module['id'],3);
		return self::_install4($module);
	}

	/* ДОбавление типов виджетов */
	private function _install4(&$module) {
		module::widget($module['id'],$module['widget']);
		module::status($module['id'],4);
		return self::_install5($module);
	}

	/* Добавление типов меню */
	private static function _install5(&$module) {
		module::menu($module['id'],$module['menu']);
		module::status($module['id'],5);
		return self::_install6($module);
	}

	/* Добавление обработчиков событий (админка и общедоступная часть) */
	private static function _install6(&$module) {
		module::hook($module['id']);
		module::status($module['id'],6);
		return self::_install7($module);
	}

	/* Выполнение специальных SQL-запросов, предусмотренных модулем */
	private static function _install7(&$module) {
		module::sql($module['id']);
		module::status($module['id'],7);
		return self::_install8($module);
	}

	/* Проверяет наличие на сайте файлов модуля. Установка невозможна, если хотя бы один файл уже существует. */
	private static function _install8(&$module) {
		$exists=&module::fileList($module['id']);
		if($exists!==true) {
			controller::$error='Установка невозможна, так как некоторые файлы уже существуют. Список конфликтов:<ul><li>'.implode('</li><li>',$exists).'</li></ul>';
			return false;
		}
		module::status($module['id'],8);
		return self::_install9($module);
	}

	/* Копирует файлы модуля */
	private static function _install9(&$module) {
		module::copy($module['id']);
		module::status($module['id'],9);
		return self::_install10($module);
	}

	/* Выполняет специальный скрипт, выполняющий какие-либо действия после завершения установки модуля */
	private static function _install10(&$module) {
		$f1=core::path().'tmp/install.php';
		$f2=core::path().'admin/module/'.$module['id'].'.install.php';
		if(file_exists($f1)) {
			copy($f1,$f2);
			include_once($f1);
			if(function_exists('installAfter')) { //"после установки"
				if(!installAfter()) return false;
			}
		}
		module::status($module['id'],100);
		return true;
	}

	//Первый этап удаления: возможно ли сейчас удалить модуль? */
	private static function _uninstall1(&$module) {
		core::import('admin/model/module');
		//Запретить удаление, если этот модуль необходим для работы других
		$depend=module::dependSearch($_GET['id']);
		if($depend) {
			controller::$error='Этот модуль используется другими модулями: &laquo;'.implode('&raquo;,&laquo;',$depend).'&raquo;. Удаление невозможно.';
			return false;
		}
		$db=core::db();
		//Запретить удаление, если в меню есть ссылки на страницы этого модуля
		if(is_array($module['menu'])) $s=implode(',',$module['menu']); else $s=$module['menu'];
		$items=$db->fetchArray('SELECT title FROM menuItem WHERE typeId IN('.$s.')');
		if($items) {
			$s='';
			foreach($items as $item) if($s) $s.='&raquo;,&laquo;'.$item[0]; else $s=$item[0];
			controller::$error='Удаление модуля невозможно: существуют ссылки в меню (пункты &laquo;'.$s.'&raquo;). Удалите эти пункты мени и повторите попытку.';
			return false;
		}
		//Запретить удаление, если существуют виджеты этого модуля (по хорошему нужно ещё проверять .ini-файл шаблона)
		$s='';
		foreach($module['widget'] as $item) {
			if($s) $s.=',';
			$s.=$db->escape($item);
		}
		$items=$db->fetchArray('SELECT title FROM widget WHERE name IN('.$s.')');
		if($items) {
			$s='';
			foreach($items as $item) if($s) $s.='&raquo;,&laquo;'.$item[0]; else $s=$item[0];
			controller::$error='Удаление модуля невозможно: существуют созданные виджеты (&laquo;'.$s.'&raquo;). Сначала удалите их.';
			return false;
		}
		module::status($_GET['id'],99);
		return self::_uninstall2($module);
	}

	/* Выполняет непосредственно удаление модуля */
	private static function _uninstall2(&$module) {
		//Если существует специальный скрипт удаления, то выполнить сначала его
		$f=core::path().'admin/module/'.$module['id'].'.install.php';
		if(file_exists($f)) {
			include($f);
			if(function_exists('uninstallBefore')) { //"перед удалением"
				if(!uninstallBefore()) return false;
			}
		}
		module::dropHook($module,true); //Удалить обработчики событий общедоступной части
		module::dropHook($module,false); //Удалить обработчики событий админки
		module::dropDb($module); //Удалить все таблицы модуля
		if(function_exists('uninstallAfter')) uninstallAfter(); //Скрипт "после удаления"
		if(file_exists($f)) unlink($f); //Удалить сам скрипт удаления
		if(!module::unlink($module['file'])) return false; //Удалить все файлы модуля
		return module::delete($module['id']); //Удалить конфигурацию модуля
	}

/* ----------------------------------------------------------------------------------- */

} ?>