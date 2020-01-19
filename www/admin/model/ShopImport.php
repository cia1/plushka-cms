<?php
namespace plushka\admin\model;

/* Осуществляет импорт данных в интернет-магазин */
class ShopImport {

	/* Загружает данные из файла excel
	array $cfg - настройки импорта, int $first - номер строки, с которой начать импорт, $count - количество загружаемых строк (за один проход) */
	public static function loadXLS($cfg,$first,$count=1000) {
		plushka::import('admin/model/excel_reader2');
		$d=new Spreadsheet_Excel_Reader(plushka::path().'tmp/shopImport.xls',false,'UTF8');
		if($d->error) {
			plushka::error('Ошибка загрузки документа');
			return false;
		}
		$last=$first+$count;
		$count=$d->rowCount();
		if($count<$last) $last=$count;
		$db=plushka::db();
		//Подготовить данные для цикла
		$productField=$featureField=array();
		$insert='INSERT INTO shp_product (categoryId,alias';
		foreach($cfg as $key=>$value) {
			if($key=='firstRow' || $key=='unique' || $key=='categoryTitle' || $key=='alias' || $key=='notDelete') continue;
			if(substr($key,0,7)=='feature') $featureField[(int)substr($key,7)]=$value;
			else {
				$productField[$key]=$value;
				$insert.=','.$key;
			}
		}
		$insert.=') VALUES (';
		$unique=$cfg[$cfg['unique']];
		$idList=$qFeature='';
		//Перебор строк excel
		$timestamp=time();
		$timeIndex=0;
		for($i=$first;$i<$last;$i++) {
			$uniqueId=$d->val($i,$unique);
			if(!$uniqueId) {
				shopLog::add($i,'Уникальное поле не имеет значения');
				continue;
			}
			$id=$db->fetchValue('SELECT id FROM shp_product WHERE '.$cfg['unique'].'='.$db->escape($uniqueId));
			if(!$id) { //этого товара нет в базе данных - создать его
				//Поиск подходящей категории
				$s=$d->val($i,$cfg['categoryTitle']);
				$categoryId=self::_categoryTitle($s);
				if(!$categoryId) {
					shopLog::add($i,'Категория "'.$s.'" не существует');
					continue;
				}
				$q=$insert.$categoryId.',';
				if($cfg['alias']) { //псевдоним товара есть в загружаемой таблице
					$s=$d->val($i,$cfg['alias']);
					if(!$s) {
						shopLog::add($i,'Псевдоним не задан');
						continue;
					}
					$q.=$db->escape($s);
				} else { //псевдонима нет в загружаемой таблице - нужно генерировать
					$q.=$timestamp.($timeIndex++);
				}
				foreach($productField as $item) {
					$q.=','.$db->escape($d->val($i,$item));
				}
				$q.=')';
				$db->query($q);
				$id=$db->insertId();
			} else { //этот товар уже есть в базе данных - обновить информацию
				$q='';
				foreach($productField as $key=>$value) {
					if($q) $q.=',';
					$q.=$key.'='.$db->escape($d->val($i,$value));
				}
				$q='UPDATE shp_product SET '.$q.' WHERE id='.$id;
				$db->query($q);
				$db->query('DELETE FROM shp_product_feature WHERE productId='.$id);
			}
			//Добавление характеристик
			if($featureField) {
				foreach($featureField as $fid=>$value) {
					if($qFeature) $qFeature.=',';
					$qFeature.='('.$id.','.$fid.','.$db->escape($d->val($i,$value)).')';
				}
			}
			if($idList) $idList.=','.$id; else $idList=$id; //Список идентификаторов добавленных/обновлённых товаров (для удаления прочих товаров)
		}
		if($qFeature) {
			$q='INSERT INTO shp_product_feature (productId,featureId,value) VALUES '.$qFeature;
			$db->query($q);
		}
		if($idList) {
			$f=fopen(plushka::path().'tmp/shopImportId.txt','a');
			fwrite($f,$idList.',');
			fclose($f);
		}
		return $last-$first;
	}

	/* Импорт товаров завершён. Удалить товары, которых нет в excel, а также разрушить временные файлы */
	public static function clear() {
		$id=file_get_contents(plushka::path().'tmp/shopImportId.txt'); //список ид импортированных товаров
		if(!$id) {
			plushka::error('Ничего не сделано');
			return array(0,0);
		}
		$id=substr($id,0,strlen($id)-1);
		$data=array(substr_count($id,',')+1,0);
		$cfg=plushka::config('admin/shop');
		if($cfg['notDelete']) return $data; //Надо ли удалять прочие товары?
		//Определить список всех товаров, которые нужно удалить, добавить их ИД в $id, а также удалить изображения этих товаров
		$db=plushka::db();
		$db->query('SELECT id,image FROM shp_product WHERE id NOT IN('.$id.')');
		$path=plushka::path().'public/shop-product/';
		$id='';
		while($item=$db->fetch()) {
			if($item[1]) {
				$image=explode(',',$item[1]);
				foreach($image as $item) {
					$f=$path.$item;
					if(file_exists($f)) unlink($f);
					$f=$path.'_'.$item;
					if(file_exists($f)) unlink($f);
				}
			}
			if($id) $id.=','.$item[0]; else $id=$item[0];
		}
		if($id) {
			$db->query('DELETE FROM shp_product_group_item WHERE productId IN('.$id.')');
			$db->query('DELETE FROM shp_variant WHERE productId IN('.$id.')');
			$db->query('DELETE FROM shp_product_feature WHERE productId IN('.$id.')');
			$db->query('DELETE FROM shp_product WHERE id IN('.$id.')');
			$data[]=substr_count($id,',')+1;
		} else $data[]=0;
		return $data;
	}

	/* Возвращает ИД категории по её заголовку */
	private static function _categoryTitle($value) {
		static $_category;
		if($_category && isset($_category[$value])) return $_category[$value];
		$db=plushka::db();
		$id=$db->fetchValue('SELECT id FROM shp_category WHERE title='.$db->escape($value));
		if(!$id) return false;
		$_category[$value]=$id;
		return $id;
	}

}

/* Предназначен для записи лога в файл */
class shopLog {

	private static $_f;

	public function __destruct() {
		if(self::$_f) fclose(self::$_f);
	}

	public static function add($i,$message) {
		if(!self::$_f) self::$_f=fopen(plushka::path().'tmp/shopImport.log','a');
		fwrite(self::$_f,'<b>#'.$i.'</b> '.$message."\n");
	}

}
?>
