<?php
namespace plushka\admin\controller;
use plushka\admin\core\Controller;
use plushka\admin\core\plushka;
use plushka\admin\model\Shop;
use plushka\core\HTTPException;
use plushka\core\Picture;

/* Управление интернет-магазином (контент) */
class ShopContentController extends Controller {

	public function right() {
		return array(
			'category'=>'shopContent.category',
			'categoryDelete'=>'shopContent.category',
			'productList'=>'shopContent.product',
			'product'=>'shopContent.product',
			'productDelete'=>'shopContent.product',
			'productImage'=>'shopContent.product',
			'productImageMain'=>'shopContent.product',
			'productImageDelete'=>'shopContent.product',
			'productMove'=>'shopContent.product',
			'variant'=>'shopContent.variant',
			'variantItem'=>'shopContent.variant',
			'variantDelete'=>'shopContent.variant',
			'brand'=>'shopContent.brand',
			'brandItem'=>'shopContent.brand',
			'brandDelete'=>'shopContent.brand'
		);
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */

	/**
	 * Создание или изменение категории товаров
	 * @throws HTTPException
	 */
	public function actionCategory() {
		if(isset($_GET['id'])) { //Изменение
			$db=plushka::db();
			$data=$db->fetchArrayOnceAssoc('SELECT * FROM shp_category WHERE id='.$_GET['id']);
			if($data===null) throw new HTTPException(404);
		} else $data=array('id'=>null,'parentId'=>(isset($_GET['parent']) ? $_GET['parent'] : 0),'alias'=>'','title'=>'','text1'=>'','metaTitle'=>'','metaKeyword'=>'','metaDescription'=>'');
		$f=plushka::form();
		$f->hidden('id',$data['id']);
		$f->hidden('parentId',$data['parentId']);
		$f->text('title','Заголовок',$data['title']);
		$f->text('alias','Псевдоним',$data['alias']);
		$f->editor('text1','Вступительный текст',$data['text1']);
		if($data['id']!=='0') $f->file('image','Изображение');
		$f->text('metaTitle','meta Заголовок',$data['metaTitle']);
		$f->text('metaKeyword','meta Ключевые слова',$data['metaKeyword']);
		$f->text('metaDescription','meta Описание',$data['metaDescription']);
		$f->submit('Сохранить');

		$this->cite='Рисунок, загруженный в поле <b>изображение</b> будет публиковаться в списке категорий.<br /><b>Псевдоним</b> - часть URL-адреса категории, оставьте поле пустым, чтобы сгенерировать его автоматически на основании заголовка категории.';
		return $f;
	}

	public function actionCategorySubmit($data) {
		$db=plushka::db();
		$validate=array(
			'id'=>array('primary'),
			'parentId'=>array('integer','',true),
			'alias'=>array('latin','псевдоним',true,'max'=>50),
			'title'=>array('string','Заголовок',true),
			'text1'=>array('html','Вступительный текст'),
			'metaTitle'=>array('string'),
			'metaKeyword'=>array('string'),
			'metaDescription'=>array('string')
		);
		if(!$data['id']) { //Если категория новая, то узнать индекс сортировки
			$data['sort']=$db->fetchValue('SELECT MAX(sort) FROM shp_category WHERE parentId='.$data['parentId']);
			$validate['sort']=array('integer');
		}
		if(!$data['alias']) $data['alias']=plushka::translit($data['title']);
		$model=plushka::model('shp_category');
		$model->set($data);
		if(!$model->save($validate)) return false;
		//Обработка изображений категории (если загружены)
		if($data['image']['size']) {
			$picture=new Picture($data['image']);
			if(plushka::error()) return false; //Файл не является изображением?
			//Удалить существующее изображение, если таковое есть
			if($data['id']) {
				$fname=$db->fetchValue('SELECT image FROM shp_category WHERE id='.$data['id']);
				if($fname) $fname=plushka::path().'public/shop-category/'.$fname;
				if($fname && file_exists($fname)) unlink($fname);
			}
			$cfg=plushka::config('shop');
			$picture->resize($cfg['categoryWidth'],$cfg['categoryHeight']);
			$fname=$picture->save('public/shop-category/'.$model->id,85);
			if(!$fname) return false;
			$db->query('UPDATE shp_category SET image='.$db->escape($fname).' WHERE id='.$model->id);
		}
		plushka::redirect('shopContent/category?id='.$data['id'],'Изменения сохранены');
	}

	/* Удаление категории товаров */
	public function actionCategoryDelete() {
		Shop::deleteCategory($_GET['id']);
		plushka::redirect('shop','Категория удалена');
	}

	//Выводит список товаров в категории
	public function actionProductList() {
		$this->button('shopContent/productDelete','delete','Удалить',null,'onclick="document.forms.productList.action=this.href;document.forms.productList.submit();return false;"');
		$db=plushka::db();
		$db->query('SELECT id,title FROM shp_product WHERE categoryId='.(int)$_GET['id'],100);
		$table=plushka::table();
		$table->rowTh('checkbox[id]|Название|');
		while($item=$db->fetch()) {
			$table->checkbox('id',$item[0],'shopContent');
			$table->text($item[1]);
			$table->text('');
		}
		$this->paginationCount=$db->foundRows();
		$this->table=$table;
		return 'ProductList';
	}

	/**
	 * Создание или изменение товара
	 * @throws HTTPException
	 */
	public function actionProduct() {
		$db=plushka::db();
		if(isset($_GET['id'])) { //Изменение - загрузить данные этого товара
			$data=$db->fetchArrayOnceAssoc('SELECT * FROM shp_product WHERE id='.$_GET['id']);
			if($data===null) throw new HTTPException(404);
			$group=$db->fetchArray('SELECT id,title,productId AS checked FROM shp_product_group g LEFT JOIN shp_product_group_item i ON i.groupId=g.id AND i.productId='.$data['id']);
		} else {
			$data=array('id'=>null,'categoryId'=>$_GET['category'],'brandId'=>null,'title'=>'','alias'=>'','text1'=>'','text2'=>'','price'=>0,'metaTitle'=>'','metaKeyword'=>'','metaDescription'=>'');
			$group=$db->fetchArray('SELECT id,title,NULL AS checked FROM shp_product_group');
		}

		$f=plushka::form();
		$f->hidden('id',$data['id']);
		$f->hidden('categoryId',$data['categoryId']);
		$f->text('title','Заголовок (название)',$data['title']);
		$f->text('alias','URL (псевдоним)',$data['alias']);
		$f->text('price','Базовая цена',$data['price']);
		$f->select('brandId','Производитель','SELECT id,title FROM shp_brand ORDER BY title',$data['brandId'],'(нет)');
		$f->editor('text1','Описание 1 (краткое)',$data['text1']);
		$f->editor('text2','Описание 2 (полное)',$data['text2']);
		$f->text('metaTitle','meta Заголовок',$data['metaTitle']);
		$f->text('metaKeyword','meta Ключевые слова',$data['metaKeyword']);
		$f->text('metaDescription','meta Описание',$data['metaDescription']);
		$f->html('<h1 style="clear:both;">Группы</h1>');
		//Добавить в форму группы товаров
		foreach($group as $item) {
			$f->checkbox('group]['.$item[0],$item[1],($item[2] ? true : false));
		}
		//Добавить в форму характеристики (привязаны к категории)
		$f->html('<h1 style="clear:both;">Характеристики</h1>');
		$feature=Shop::featureProduct($data['categoryId'],$data['id']);
		foreach($feature as $item1) {
			$f->html('<p><b>'.$item1['title'].'</b></p>');
			foreach($item1['data'] as $item2) {
				$type=$item2['type'];
				if($type=='select') {
					$f->select('feature]['.$item2['id'],'&nbsp;'.$item2['title'],$item2['data'],$item2['value']);
				} else {
					$f->$type('feature]['.$item2['id'],'&nbsp;'.$item2['title'],$item2['value']);
				}
			}
		}
		$f->submit('Сохранить');
		$this->cite='<b>Группы</b> позволяют объединять любые товары. Наиболее часто группы используются для публикации избранных товаров в какой-либо области сайта.';
		$this->js('admin/shop');
		return $f;
	}

	public function actionProductSubmit($data) {
		$model=plushka::model('shp_product');
		if(!$data['alias']) $data['alias']='';
		$model->set($data);
		if(!$model->save(array(
			'id'=>array('primary'),
			'categoryId'=>array('integer','категория',true),
			'brandId'=>array('integer'),
			'title'=>array('string','Заголовок',true),
			'alias'=>array('latin','max'=>25),
			'text1'=>array('html','Описание 1 (краткое)'),
			'text2'=>array('html','Описание 2 (полное)'),
			'metaTitle'=>array('string'),
			'metaKeyword'=>array('string'),
			'metaDescription'=>array('string'),
			'price'=>array('float','Цена')
		))) return false;
		//Если псевдоним не задан, то использовать ИД товара
		if(!$data['alias']) {
			$db=plushka::db();
			$db->query('UPDATE shp_product SET alias='.$db->escape($model->id).' WHERE id='.$model->id);
		}
		//Обновить группы, к которым привязан этот товар
		$query=array();
		if($data['group']) {
			foreach($data['group'] as $id=>$value) {
				$query[]=array(
					'groupId'=>$id,
					'productId'=>$model->id
				);
			}
		}
		$db=plushka::db();
		$db->query('DELETE FROM shp_product_group_item WHERE productId='.$model->id);
		if($data['group']) $db->insert('shp_product_group_item',$query);
		unset($query);
		//Обновить характеристики
		$db->query('DELETE FROM shp_product_feature WHERE productId='.$model->id);
		if(isset($data['feature'])) {
			$feature=$db->fetchArray('SELECT id,unit,type FROM shp_feature WHERE id IN('.implode(',',array_keys($data['feature'])).')');
			$query=array();
			foreach($feature as $item) {
				$id=$item[0];
				if($item[2]=='checkbox') {
					if(isset($data['feature'][$id])) $value='да'; else $value='нет';
				} else $value=$data['feature'][$id];
				if(!$value) continue;
				if($item[1]) $value.=' '.$item[1];
				$query[]=array(
					'productId'=>$model->id,
					'featureId'=>$id,
					'value'=>$value
				);
			}
			unset($feature);
			$db->insert('shp_product_feature',$query);
			unset($query);
		}
		$alias=$db->fetchValue('SELECT alias FROM shp_category WHERE id='.$model->categoryId);
		plushka::hook('modify','shop/'.$alias.'/'.$model->alias,true);
		plushka::redirect('shopContent/product?id='.$model->id,'Изменения сохранены');
	}

	/* Удаление товара */
	public function actionProductDelete() {
		Shop::deleteProduct($_GET['id']);
		plushka::redirect('shop','Товар удалён');
	}

	//Удаление нескольких товаров
	public function actionProductDeleteSubmit($data) {
		Shop::deleteProduct($data['id']);
		plushka::redirect('shop','Товары удалены');
	}
	/* Список изображений товара (для добавления/удаления) */
	public function actionProductImage() {
		$db=plushka::db();
		if(isset($_POST['shopContent']['id'])) $_GET['id']=$_POST['shopContent']['id'];
		$data=$db->fetchArrayOnce('SELECT mainImage,image FROM shp_product WHERE id='.$_GET['id']);
		if(!$data[1]) $this->image=array(); else $this->image=explode(',',$data[1]);
		$this->mainImage=$data[0]; //Главное изображение
		$this->form=plushka::form();
		$this->form->hidden('id',$_GET['id']);
		$this->form->file('image','Изображение');
		$this->form->submit('Продолжить');
		return 'ProductImage';
	}

	public function actionProductImageSubmit($data) {
		$db=plushka::db();
		$image=$db->fetchArrayOnce('SELECT mainImage,image FROM shp_product WHERE id='.$data['id']);
		$mainImage=$image[0]; //Основное изображение
		if($image[1]) $image=explode(',',$image[1]); else $image=array();
		$index=count($image)+1; //Для формирования имени файла
		$cfg=plushka::config('shop');
		$picture=new Picture($data['image']);
		if(plushka::error()) return false;
		$picture->resize($cfg['productFullWidth'],$cfg['productFullHeight']);
		$fname=$data['id'].'.'.$index;
		$fname=$picture->save('public/shop-product/'.$fname,100);
		if(!$fname) return false;
		$picture->resize($cfg['productThumbWidth'],$cfg['productThumbHeight']);
		if(!$picture->save('public/shop-product/_'.$fname,80)) return false;
		$image[]=$fname;
		$q='UPDATE shp_product SET image='.$db->escape(implode(',',$image));
		if(!$mainImage) $q.=',mainImage='.$db->escape($fname); //Если ранее небыло
		$db->query($q.' WHERE id='.$data['id']);
		plushka::redirect('shopContent/productImage?id='.$data['id']);
	}

	/* Установить выбранное изображение товара главным */
	public function actionProductImageMain() {
		$db=plushka::db();
		$db->query('UPDATE shp_product SET mainImage='.$db->escape($_GET['image']).' WHERE id='.$_GET['id']);
		plushka::redirect('shopContent/productImage?id='.$_GET['id']);
	}

	/* Удаление изображения товара */
	public function actionProductImageDelete() {
		$db=plushka::db();
		$fname=plushka::path().'public/shop-product/'.$_GET['image'];
		if(file_exists($fname)) unlink($fname);
		$fname=plushka::path().'public/shop-product/_'.$_GET['image'];
		if(file_exists($fname)) unlink($fname);
		$data=$db->fetchArrayOnce('SELECT mainImage,image FROM shp_product WHERE id='.$_GET['id']);
		$image=explode(',',$data[1]);
		for($i=0;$i<count($image);$i++) {
			if($image[$i]==$_GET['image']) {
				if($data[0]==$_GET['image']) {
					if($i>0) $data[0]=$image[$i-1];
					elseif(count($image)>1) $data[0]=$image[1];
					else $data[0]=null;
				}
				unset($image[$i]);
				break;
			}
		}
		$q='UPDATE shp_product SET image='.$db->escape(implode($image,',')).',mainImage=';
		if(!$data[0]) $q.='NULL'; else $q.=$db->escape($data[0]);
		$db->query($q.' WHERE id='.$_GET['id']);
		plushka::redirect('shopContent/productImage?id='.$_GET['id']);
	}

	/* Переместить товар в другую категорию */
	public function actionProductMove() {
		$db=plushka::db();
		$data=$db->fetchArrayOnce('SELECT alias,categoryId FROM shp_product WHERE id='.$_GET['id']);
		$list=array();
		$in=\plushka\model\Shop::categoryTree();
		$this->_flatCategory($in[0]['child'],$list); //Плоский список категорий в $list
		unset($in);
		$f=plushka::form();
		$f->select('categoryId','Категория',$list,$data[1]);
		$f->hidden('id',$_GET['id']);
		$f->hidden('alias',$data[0]);
		$f->submit('Продолжить');
		return $f;
	}

	public function actionProductMoveSubmit($data) {
		$db=plushka::db();
		$db->query('UPDATE shp_product SET categoryId='.$data['categoryId'].' WHERE id='.$data['id']);
		plushka::redirectPublic('shop/category/'.$data['categoryId'].'/'.$data['alias'],'Товар перенесён');
	}

	/* Список модификаций товара */
	public function actionVariant() {
		$db=plushka::db();
		$categoryId=$db->fetchValue('SELECT categoryId FROM shp_product WHERE id='.$_GET['pid']); //Для определения списка характеристик
		$this->button('shopContent/variantItem?cid='.$categoryId.'&pid='.$_GET['pid'],'new','Создать модификацию товара');
		$db=plushka::db();
		$t=plushka::table();
		$t->rowTh('Название|');
		$db->query('SELECT id,title FROM shp_variant WHERE productId='.$_GET['pid']);
		while($item=$db->fetch()) {
			$t->link('shopContent/variantItem?cid='.$categoryId.'&id='.$item[0],$item[1]);
			$t->delete('pid='.$_GET['pid'].'&id='.$item[0],'variant');
		}
		return $t;
	}

	/* Создание или изменение модификации товара */
	public function actionVariantItem() {
		if(isset($_GET['id'])) { //Изменение
			$db=plushka::db();
			$data=$db->fetchArrayOnceAssoc('SELECT id,productId,title,feature FROM shp_variant WHERE id='.$_GET['id']);
		} else $data=array('id'=>null,'productId'=>$_GET['pid'],'title'=>'','feature'=>null);
		if(isset($_GET['cid'])) $cid=$_GET['cid']; else $cid=$_POST['shopContent']['categoryId']; //ИД категории
		$f=plushka::form();
		$f->hidden('id',$data['id']);
		$f->hidden('categoryId',$cid);
		$f->hidden('productId',$data['productId']);
		$f->text('title','Название (модель)',$data['title']);
		//Подключить список характеристик к форме
		$feature=Shop::featureVariant($cid,$data['feature']); //По $data['feature'] определяются значения характеристик
		foreach($feature as $item1) {
			$f->html('<p><b>'.$item1['title'].'</b></p>');
			foreach($item1['data'] as $item2) {
				$type=$item2['type'];
				if($type=='select') {
					$f->select('feature]['.$item2['id'],'&nbsp;'.$item2['title'],$item2['data'],$item2['value']);
				} else {
					$f->$type('feature]['.$item2['id'],'&nbsp;'.$item2['title'],$item2['value']);
				}
			}
		}
		$f->submit();
		return $f;
	}

	public function actionVariantItemSubmit($data) {
		//$feature - список характеристик, заданных для категории
		$db=plushka::db();
		$feature=$db->fetchValue('SELECT c.feature f0 FROM shp_product p INNER JOIN shp_category c ON c.id=p.categoryId WHERE p.id='.$data['productId']);
		//Обработка характеристик варианта товара, добавление единицы измерения (по модификациям поиска нет, поэтому можно ЕИ присоединить сразу)
		if($feature) {
			$feature=$db->fetchArray('SELECT id,unit,type FROM shp_feature WHERE id IN('.$feature.') AND variant=1');
			$fData=array();
			foreach($feature as $item) {
				$id=$item[0];
				if($data['feature'][$id]==='') continue;
				if($item[2]=='checkbox') {
					if(isset($data['feature'][$id])) $value='да'; else $value='нет';
				} else $value=$data['feature'][$id];
				if($item[1]) $value.=' '.$item[1];
				$fData[$id]=$value;
			}
			$data['feature']=serialize($fData);
		} else $data['feature']=null;
		if(!$data['id']) $isNew=true; else $isNew=false;
		$m=plushka::model('shp_variant');
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'productId'=>array('integer'),
			'title'=>array('string','название',true),
			'feature'=>array('string')
		))) return false;
		if($isNew) $db->query('UPDATE shp_product SET variant=variant+1 WHERE id='.$data['productId']); //Если новый товар, то обновить счётчик модификаций товаров
		plushka::redirect('shopContent/variant?pid='.$data['productId']);
	}

	/* Удаление модификации товара */
	public function actionVariantDelete() {
		$db=plushka::db();
		$db->query('DELETE FROM shp_variant WHERE id='.$_GET['id']);
		$db->query('UPDATE shp_product SET variant=variant-1 WHERE id='.$_GET['pid']);
		plushka::redirect('shopContent/variant?pid='.$_GET['pid']);
	}

	//Упралвение производителями
	public function actionBrand() {
		$this->button('shopContent/brandItem','new','Добавить производителя');
		$db=plushka::db();
		$db->query('SELECT id,title,image FROM shp_brand ORDER BY title');
		$table=plushka::table();
		$table->rowTh('|');
		$url=plushka::url().'public/shop-brand/';
		while($item=$db->fetch()) {
			$table->text('<img src="'.$url.$item[2].'" /> '.$item[1],null,'style="vertical-align:middle;"');
			$table->itemDelete('id='.$item[0],'brand');
		}
		return $table;
	}

	//Создание/редактирование производителя
	public function actionBrandItem() {
		if(isset($_GET['id'])) {
			$db=plushka::db();
			$data=$db->fetchArrayOnceAssoc('SELECT id,alias,title,image,text1 FROM shp_brand WHERE id='.(int)$_GET['id']);
		} else $data=array('id'=>null,'alias'=>'','title'=>'','image'=>null,'text1'=>'');
		$form=plushka::form();
		$form->hidden('id',$data['id']);
		$form->text('title','Заголовок',$data['title']);
		$form->text('alias','Псевдоним (URL)',$data['alias']);
		$form->file('image','Изображение');
		$form->editor('text1','Статья',$data['text1']);
		$form->submit();
		return $form;
	}

	public function actionBrandItemSubmit($data) {
		$model=plushka::model('shp_brand');
		$model->set($data);
		if(!$model->save(array(
			'id'=>array('primary'),
			'title'=>array('string','заголовок',true,'max'=>50),
			'alias'=>array('latin','псевдоним (URL)',true,'max'=>50),
			'text1'=>array('html')
		))) return false;

		if($data['image']['size']) {
			$cfg=plushka::config('shop');
			$picture=new Picture($data['image']);
			if(plushka::error()) return false;
			$db=plushka::db();
			//Удалить старое изображение, если загружено новое
			if($data['id']) {
				$oldImage=$db->fetchValue('SELECT image FROM shp_brand WHERE id='.(int)$data['id']);
				if($oldImage) {
					$f=plushka::path().'/public/shop-brand/'.$oldImage;
					if(file_exists($f)) unlink($f);
				}
			}
			$picture->resize($cfg['brandWidth'],$cfg['brandHeight']);
			$fname=$picture->save('public/shop-brand/'.$model->id);
			$db->query('UPDATE shp_brand SET image='.$db->escape($fname).' WHERE id='.$model->id);
		}

		plushka::redirect('shopContent/brand');
	}

	//Удаление производителя
	public function actionBrandDelete() {
		$id=(int)$_GET['id'];
		$db=plushka::db();
		$image=$db->fetchValue('SELECT image FROM shp_brand WHERE id='.$id);
		if($image) {
			$image=plushka::path().'public/shop-brand/'.$image;
			if(file_exists($image)) unlink($image);
		}
		$db->query('UPDATE shp_product SET brandId=null WHERE brandId='.$id);
		$db->query('DELETE FROM shp_brand WHERE id='.$id);
		plushka::redirect('shopContent/brand');
	}

/* ----------------------------------------------------------------------------------- */



/* ---------- PRIVATE ---------------------------------------------------------------- */
	/* Помещает в $out плоский список категорий товаров */
	private static function _flatCategory($in,&$out,$level=0) {
		$s=str_repeat('&nbsp;',$level*3);
		foreach($in as $id=>$item) {
			if($item['title']) $out[]=array($id,$s.$item['title']);
			if($item['child']) self::_flatCategory($item['child'],$out,$level+1);
		}
	}
/* ----------------------------------------------------------------------------------- */
}
?>
