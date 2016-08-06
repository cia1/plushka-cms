<?php
/* Библиотека часто используемых функций модуля "shop" (интернет-магазин) */
class shop {

	private static $_foundRows;

	/* Для кеширования дерева каталогов */
	public static function cacheCategoryTree() {
		$data=core::cache('shopCategoryList',function() {
			$db=core::db();
			$db->query('SELECT id,alias,parentId,title,image FROM shpCategory ORDER BY parentId,sort,id');
			$data=array(0=>array('child'=>array()));
			while($item=$db->fetch()) $data[$item[0]]=array('id'=>$item[0],'alias'=>$item[1],'title'=>$item[3],'parentId'=>$item[2],'image'=>$item[4],'child'=>array());
			foreach($data as $id=>&$item) {
				if($id==0) continue;
				$item['id']=(int)$item['id'];
				$parentId=(int)$item['parentId'];
				$data[$parentId]['child'][]=&$item;
			}
			$data['ROOT']=$data[0]['child'];
			unset($data[0]['child']);
			return $data;
		},10);
		return $data;
	}

	/* Возвращает список дочерних категорий для категории с идентификатором $id */
	public static function categoryList($id) {
		$db=core::db();
		$data=$db->fetchArrayAssoc('SELECT id,alias,title,image FROM shpCategory WHERE parentId='.$id.' ORDER BY sort,id');
		for($i=0;$i<count($data);$i++) {
			$data[$i]['link']=core::link('shop/'.$data[$i]['alias']);
			if($data[$i]['image']) $data[$i]['image']=core::url().'public/shop-category/'.$data[$i]['image'];
		}
		return $data;
	}

	/* Возвращает подготовленный список товаров, находящихся в заданной категории
	$categoryId - ИД категории; $feature - список характеристик, которые также нужно извлечь из БД */
	public static function productCategory($categoryId=null,$feature=false) {
		//Построить SQL-запрос с учётом параметров поиска:
		//$qSelect - выборка, $qCount - отдельный запрос для подсчёта количества товаров (для пагинации)
		$qSelect='SELECT p.id id,p.alias alias,c.alias categoryAlias,p.title title,p.price price,p.mainImage mainImage,p.text1 text1';
		$qCount='SELECT COUNT(id) FROM shpProduct p';
		if($feature) { //если характеристики заданы, то присоединить их к запросу
			$s=' FROM shpProduct p';
			foreach($feature as $item) {
				$qSelect.=',pf'.$item.'.value feature'.$item;
				$s.=' LEFT JOIN shpProductFeature pf'.$item.' ON pf'.$item.'.productId=p.id AND pf'.$item.'.featureId='.$item;
			}
			$q.=$s;
		} else $qSelect.=' FROM shpProduct p';
		$qSelect.=' INNER JOIN shpCategory c ON c.id=p.categoryId';
		//Задан отбор по характеристикам
		$db=core::db();
		if(isset($_GET['feature'])) {
			foreach($_GET['feature'] as $id=>$item) {
				if(!$item) continue;
				$id=(int)$id;
				if(!$id) continue;
				if(is_array($item)) {
					$value='';
					foreach($item as $s) {
						if($value) $value.=',';
						$value.=$db->escape($s);
					}
					$value=' IN('.$value.')';
				} else $value='='.$db->escape($item);
				$qSelect.=' INNER JOIN shpProductFeature wpf'.$id.' ON wpf'.$id.'.featureId='.$id.' AND wpf'.$id.'.productId=p.id AND wpf'.$id.'.value'.$value;
				$qCount.=' INNER JOIN shpProductFeature wpf'.$id.' ON wpf'.$id.'.featureId='.$id.' AND wpf'.$id.'.productId=p.id AND wpf'.$id.'.value'.$value;
			}
		}
		$qSelect.=' WHERE';
		$qCount.=' WHERE';
		if($categoryId) {
			$qSelect.=' p.categoryId='.$categoryId;
			$qCount.=' p.categoryId='.$categoryId;
		}
		if(isset($_GET['sort'])) { //задана какая-то сортировка
			if($_GET['sort']=='dateASC') $sort='id ASC'; elseif($_GET['sort']=='dateDESC') $sort='id DESC';
			else $sort=str_replace(array('ASC','DESC'),array(' ASC', ' DESC'),$_GET['sort']);
		} else $sort='id DESC';
		//фильтр по цене
		if(isset($_GET['price1']) && $_GET['price1']) $q.=' AND p.price>='.(float)$_GET['price1'];
		if(isset($_GET['price2']) && $_GET['price2']) $q.=' AND p.price<='.(float)$_GET['price2'];
		$qSelect.=' ORDER BY '.$db->getEscape($sort);
		$cfg=core::config('shop');
		if(isset($_GET['page']) && $_GET['page']>1) $page=$_GET['page']-1; else $page=0;
		$qSelect.=' LIMIT '.($page*$cfg['productOnPage']).','.$cfg['productOnPage'];
		self::$_foundRows=$db->fetchValue($qCount);
		return self::_loadList($qSelect);
	}

	public static function foundRows() {
		return self::$_foundRows;
	}
	/* Возвращает список товаров, принадлежащих к группе с идентификатором $id */
	public static function productGroup($id) {
		$q='SELECT p.id id,p.alias alias,c.alias categoryAlias,p.title title,p.price price,p.mainImage mainImage,p.text1 text1 FROM shpProductGroupItem gi INNER JOIN shpProduct p ON p.id=gi.productId INNER JOIN shpCategory c ON c.id=p.categoryId WHERE gi.groupId='.$id.' ORDER BY p.id DESC';
		return self::_loadList($q);
	}

	/* Возвращает полную информацию о товаре по его идентификатору */
	public static function productById($id) {
		$db=core::db();
		$id=(int)$id;
		if(!$id) return null;
		$data=$db->fetchArrayOnceAssoc('SELECT p.*,b.title brand FROM shpProduct p LEFT JOIN shpBrand b ON b.id=p.brandId WHERE id='.$id);
		if(!$data['image']) $data['image']=array(); else {
			$data['image']=explode(',',$data['image']);
			unset($data['image'][0]);
		}
		return $data;
	}

	/* Возвращает полную информацию о товаре по его псевдониму
	string $alias - псевдоним товара; bool $variant - также извлечь вариатны товаров*/
	public static function productByAlias($alias,$variant=false) {
		$db=core::db();
		$data=$db->fetchArrayOnceAssoc('SELECT p.id,p.categoryId,p.title,p.text1,p.text2,p.price,p.mainImage,p.image,p.metaTitle,p.metaKeyword,p.metaDescription'.($variant ? ',p.variant variantCount' : '').',c.title categoryTitle,c.feature,b.title brand FROM shpProduct p LEFT JOIN shpCategory c ON c.id=p.categoryId LEFT JOIN shpBrand b ON b.id=p.brandId WHERE p.alias='.$db->escape($alias));
		if(!$data) return false;
		if(!$data['image']) $data['image']=array(); else {
			$data['image']=explode(',',$data['image']);
			unset($data['image'][0]);
		}
		if(!$data['mainImage']) $data['mainImage']='noimage.jpg';
		self::_appendFeature($data); //Подключить характеристики
		//Подключить варианты (модификации)
		if($variant && $data['variantCount']) {
			$db->query('SELECT id,title,feature FROM shpVariant WHERE productId='.$data['id'].' ORDER BY title');
			$variant=array();
			while($item=$db->fetchAssoc()) {
				if($item['feature']) $item['feature']=unserialize($item['feature']); else $item['feature']=array();
				foreach($item['feature'] as $id=>$f) {
					$item['feature'][$id]=array('title'=>$data['feature'][$id]['title'],'value'=>$f);
				}
				$variant[]=$item;
			}
			$data['variant']=$variant;
			$data['variantCount']=(int)$data['variantCount'];
		}
		//Удалить пустые характеристики
		foreach($data['feature'] as $id=>$item) {
			if($item['value']==='' || $item['value']===NULL) unset($data['feature'][$id]);
		}
		return $data;
	}

	/* Возвращает обработанный список товаров, выбранных по SQL-запросу $query */
	private static function _loadList($query) {
		$db=core::db();
		$data=$db->fetchArrayAssoc($query);
		$uri=core::url().'public/shop-brand/';
		for($i=0,$cnt=count($data);$i<$cnt;$i++) {
			$data[$i]['link']=core::link('shop/'.$data[$i]['categoryAlias'].'/'.$data[$i]['alias']);
			$data[$i]['price']=self::_price($data[$i]['price']);
			if(!$data[$i]['mainImage']) $data[$i]['mainImage']='noimage.jpg';
		}
		return $data;
	}

	private static function _price($price) {
		if(!$price) return '<i>- нет -</i>';
		$len=strlen($price);
		if($len>3) $price=substr($price,0,$len-3).' '.substr($price,$len-3);
		return $price;
	}

	/* Присоединяет к переданному массиву, содержащему информацию о товаре, все характеристики этого товара */
	private static function _appendFeature(&$product) {
		$db=core::db();
		if(!$product['feature']) {
			$product['feature']=array();
			return;
		}
		$db->query('SELECT f.id f0,f.title f1,g.id f2,g.title f3,p.value f4 FROM shpFeature f INNER JOIN shpFeatureGroup g ON g.id=f.groupId LEFT JOIN shpProductFeature p ON p.featureId=f.id AND p.productId='.$product['id'].' WHERE f.id IN('.$product['feature'].')');
		$data=array();
		$gid=null;
		while($item=$db->fetch()) {
			$data[$item[0]]=array('title'=>$item[1],'value'=>$item[4]);
		}
		$product['feature']=$data;
	}

} ?>