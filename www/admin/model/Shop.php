<?php
namespace plushka\admin\model;

/* Библиотека, содержащая наиболее часто используемые инструменты для административной части интернет-магазина */
class Shop {

	/* Рекурсивно удаляет категорию товаров с ИД $id со всеми товарами */
	public static function deleteCategory($id) {
		$db=plushka::db();
		$id=(int)$id;
		$alias=$db->fetchValue('SELECT alias FROM shp_category WHERE id='.$id);
		if(!$alias) return false;
		$items=$db->fetchArray('SELECT id FROM shp_category WHERE parentId='.$id);
		for($i=0,$cnt=count($items);$i<$cnt;$i++) {
			shop::deleteCategory($items[$i][0]); //Удалить вложенные категории
		}
		//Удалить все товары в категории
		$items=$db->fetchArray('SELECT id,image FROM shp_product WHERE categoryId='.$id);
		foreach($items as $item) {
			self::deleteProduct($item[0],$item[1]);
		}
		//Удалить изображение категории
		$img=$db->fetchValue('SELECT image FROM shp_category WHERE id='.$id);
		if($img) {
			$f=plushka::path().'public/shop-category/'.$img;
			if(file_exists($f)) unlink($f);
		}
		$db->query('DELETE FROM shp_category WHERE id='.$id);
		plushka::hook('pageDelete','shop/'.$alias,true);
		return true;
	}

	/* Удаляет товар с идентификатором $id (может быть массивом идентификаторов, а также все его модификации.
	array $image - список изображений товара (позволяет сэкономить один SQL-запрос, если указано) */
	public static function deleteProduct($id,$image=null) {
		if(is_array($id)) $id=implode(',',$id);
		$db=plushka::db();
		//Удалить изображения товара
		if(!$image) {
			$db->query('SELECT image FROM shp_product WHERE id IN('.$id.')');
			$image='';
			while($item=$db->fetch()) {
				if(!$item[0]) continue;
				if($image) $image.=','.$item[0]; else $image=$item[0];
			}
		}
		if($image) {
			$image=explode(',',$image);
			$path=plushka::path().'public/shop-product/';
			foreach($image as $item) {
				$f=$path.$item;
				if(file_exists($f)) unlink($f);
				$f=$path.'_'.$item;
				if(file_exists($f)) unlink($f);
			}
		}
		$data=$db->fetchArrayOnce('SELECT c.alias,p.alias FROM shp_product p LEFT JOIN shp_category c ON c.id=p.categoryId WHERE id='.$id);
		$db->query('DELETE FROM shp_variant WHERE productId IN('.$id.')');
		$db->query('DELETE FROM shp_product_feature WHERE productId IN('.$id.')');
		$db->query('DELETE FROM shp_product_group_item WHERE productId IN('.$id.')');
		$db->query('DELETE FROM shp_product WHERE id IN('.$id.')');
		plushka::hook('pageDelete','shop/'.$data[0].'/'.$data[1],true);
		return true;
	}

	/* Возвращает массив, содержащий список характеристик по указанным условиям.
	int $groupId - группа характеристик, int $category - категория товаров, array $out - характеристики, котрые нужно пропустить */
	public static function featureList($groupId=null,$category=null,$out=null) {
		$db=plushka::db();
		//Построить SQL-запрос в соответствии с заданными условиями
		if($category==='') $category='999999999';
		if($groupId) { //От этого параметра зависит, какие требуются данные
			if($category!==null) $q='SELECT id,title FROM shp_feature WHERE groupId='.$groupId.' AND id IN('.$category.')';
			else $q='SELECT id,title FROM shp_feature WHERE groupId='.$groupId;
		} else {
			$q='SELECT f.id f1,f.title f2,g.id f3,g.title f4,f.type f5 FROM shp_feature f INNER JOIN shp_feature_group g ON g.id=f.groupId';
			if($category) $q.=' WHERE f.id IN('.$category.')';
			$q.=' ORDER BY f.groupId';
		}
		if($groupId) $data=$db->fetchArrayAssoc($q);
		else {
			$db->query($q);
			$data=array();
			$gid=null;
			while($item=$db->fetch()) {
				if($gid!=$item[2]) {
					$gid=$item[2];
					$data[$gid]=array('title'=>$item[3],'data'=>array());
					$data0=&$data[$gid]['data'];
				}
				$data0[]=array('id'=>$item[0],'title'=>$item[1],'type'=>$item[4]);
			}
		}
		//Исключить лишние характеристики
		if($out) {
			foreach($out as $id=>$outItem) {
				if(!isset($data[$id])) continue;
				$dataItem=$data[$id]['data'];
				$cnt1=count($dataItem);
				$cnt2=0;
				foreach($outItem['data'] as $item) {
					$oid=$item['id'];
					foreach($dataItem as $i=>$item2) {
						if($item2['id']==$oid) {
							unset($dataItem[$i]);
							$cnt2++;
							break;
						}
					}
				}
				if($cnt1==$cnt2) unset($data[$id]);
			}
		}
		return $data;
	}

	/* Возвращает список характеристик товара для категории $categoryId. Если задан $productId, то также будут загружены значения характеристик */
	public static function featureProduct($categoryId,$productId=0) {
		$db=plushka::db();
		$feature=$db->fetchValue('SELECT feature FROM shp_category WHERE id='.$categoryId);
		if(!$feature) return array();
		if($productId) $db->query('SELECT f.id f0,f.title f1,g.id f2,g.title f3,f.type f4,p.value f5,f.unit f6,f.data f7 FROM shp_feature f INNER JOIN shp_feature_group g ON g.id=f.groupId LEFT JOIN shp_product_feature p ON p.featureId=f.id AND p.productId='.$productId.' WHERE f.id IN('.$feature.')');
		else $db->query('SELECT f.id f0,f.title f1,g.id f2,g.title f3,f.type f4,"" f5,f.unit f6,f.data f7 FROM shp_feature f INNER JOIN shp_feature_group g ON g.id=f.groupId WHERE f.id IN('.$feature.')');
		$data=array();
		$gid=null;
		while($item=$db->fetch()) {
			if($gid!=$item[2]) {
				$gid=$item[2];
				$data[$gid]=array('title'=>$item[3],'data'=>array());
				$data0=&$data[$gid]['data'];
			}
			if($item[4]!='checkbox' && $item[6]) {
				$i1=strlen($item[5]);
				$i2=strlen($item[6])+1;
				if(substr($item[5],$i1-$i2)==' '.$item[6]) $item[5]=substr($item[5],0,$i1-$i2);
			}
			if($item[4]=='select') {
				$d=explode('|',$item[7]);
				for($i=0,$cnt=count($d);$i<$cnt;$i++) $d[$i]=array($d[$i],$d[$i]);
			} else $d=null;
			$data0[]=array('id'=>$item[0],'title'=>$item[1].($item[6] ? ' ('.$item[6].')' : ''),'type'=>$item[4],'value'=>$item[5],'data'=>$d);
		}
		return $data;
	}

	/* Возвращает характеристики для модификаций товаров для категории $categoryId, если $variant задан, то к результату будут добавлены значения характеристик модификации */
	public function featureVariant($categoryId,$variant=null) {
		$db=plushka::db();
		$feature=$db->fetchValue('SELECT feature FROM shp_category WHERE id='.$categoryId);
		if(!$feature) return array();
		if($variant) $variant=unserialize($variant);
		$db->query('SELECT f.id f0,f.title f1,g.id f2,g.title f3,f.type f4,f.unit f5 FROM shp_feature f INNER JOIN shp_feature_group g ON g.id=f.groupId WHERE f.id IN('.$feature.') AND f.variant=1');
		$data=array();
		$gid=null;
		while($item=$db->fetch()) {
			$id=$item[0];
			if($gid!=$item[2]) {
				$gid=$item[2];
				$data[$gid]=array('title'=>$item[3],'data'=>array());
				$data0=&$data[$gid]['data'];
			}
			if(isset($variant[$id])) $value=$variant[$id]; else $value=null;
			if($item[4]!='checkbox' && $value) { //если значение есть
				$i1=strlen($value);
				$i2=strlen($item[5])+1;
				if(substr($value,$i1-$i2)==' '.$item[5]) $value=substr($value,0,$i1-$i2);
			}
			$data0[]=array('id'=>$id,'title'=>$item[1],'type'=>$item[4],'value'=>$value);
		}
		return $data;
	}

	/* Удаляет группу характеристик со всеми характеристиками */
	public static function featureGroupDelete($id) {
		$id=(int)$id;
		$db=plushka::db();
		//Удалить все характеристики этой группы
		$s='';
		$db->query('SELECT id FROM shp_feature WHERE groupId='.$id);
		$s='';
		while($item=$db->fetch()) if($s) $s.=','.$item[0]; else $s=$item[0];
		if($s) {
			if(!self::featureDelete($s)) return false;
		}
		$db->query('DELETE FROM shp_feature_group WHERE id='.$id);
		return true;
	}

	/* Удаляет характеристики с ИД $id (список через запятую), а также разрушает все зависимости */
	public static function featureDelete($id) {
		$db=plushka::db();
		if(!self::featureCategoryDelete($id,true)) return false;
		$db->query('DELETE FROM shp_feature WHERE id IN('.$id.')');
//		$db->query('DELETE FROM shp_product_feature WHERE id IN('.$id.')');
		return true;
	}

	/* Удаляет характеристики $id (список через запятую) из всех категорий (shp_category.feature).
	bool $update - надо или нет обновлять список характеристик самой категории */
	public static function featureCategoryDelete($id,$update=false,$categoryId=null) {
		$id0=explode(',',$id);
		if(!self::featureVariantDelete($id)) return false; //Удалить характеристику из всех модификаций товаров
		$db=plushka::db();
		if($update) {
			$data=$db->fetchArray('SELECT id,feature FROM shp_category WHERE feature!='.$db->escape(''));
			foreach($data as $item) {
				$feature=implode(',',array_diff(explode(',',$item[1]),$id0));
				if($item[1]!=$feature) {
					$db->query('UPDATE shp_category SET feature='.$db->escape($feature).' WHERE id='.$item[0]);
				}
			}
		}
		//Если задан ИД категории, то удалить значения характеристик только у товаров этой категории, иначе удалить значения характеристик всех товаров
		if(!$categoryId) {
			$db->query('DELETE FROM shp_product_feature WHERE featureId IN('.$id.')');
		} else {
			$db->query('SELECT id FROM shp_product WHERE categoryId='.$categoryId);
			$pid='';
			while($item=$db->fetch()) {
				if($pid) $pid.=','.$item[0]; else $pid=$item[0];
			}
			$db->query('DELETE FROM shp_product_feature WHERE productId IN('.$pid.') AND featureId IN('.$id.')');
		}
		return true;
	}

	/* Удаляет характеристики $id (строка через запятую) из всех модификаций товаров (shp_variant.feature) */
	public static function featureVariantDelete($id) {
		$db=plushka::db();
		$data=$db->fetchArray('SELECT id,feature FROM shp_variant');
		$cnt=count($id);
		foreach($data as $item) {
			if(!$item[1]) continue;
			$feature=unserialize($item[1]);
			$change=false;
			for($i=0;$i<$cnt;$i++) {
				if(isset($feature[$id[$i]])) {
				 	unset($feature[$id[$i]]);
				 	$change=true;
				}
			}
			if($change) {
				if($feature) $feature=$db->escape(serialize($feature)); else $feature='null';
				$db->query('UPDATE shp_variant SET feature='.$feature.' WHERE id='.$item[0]);
			}
		}
		return true;
	}

}
?>
