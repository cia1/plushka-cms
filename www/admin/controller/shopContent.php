<?php
/* Управление интернет-магазином (контент) */
class sController extends controller {

	public function right($right,$action) {
		switch($action) {
		case 'Category': case 'CategoryDelete':
			if(isset($right['shopContent.category'])) return true; else return false;
		case 'ProductList': case 'Product': case 'ProductDelete': case 'ProductImage': case 'ProductImageMain': case 'ProductImageDelete': case 'ProductMove':
			if(isset($right['shopContent.product'])) return true; else return false;
		case 'Variant': case 'VariantItem': case 'VariantDelete':
			if(isset($right['shopContent.variant'])) return true; else return false;
		case 'Brand': case 'BrandItem': case 'BrandDelete':
			if(isset($right['shopContent.brand'])) return true; else return false;
		}
		return false;
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */

	/* Создание или изменение категории товаров */
	public function actionCategory() {
		if(isset($_GET['id'])) { //Изменение
			$db=core::db();
			$data=$db->fetchArrayOnceAssoc('SELECT * FROM shpCategory WHERE id='.$_GET['id']);
			if(!$data) core::error404();
		} else $data=array('id'=>null,'parentId'=>(isset($_GET['parent']) ? $_GET['parent'] : 0),'alias'=>'','title'=>'','text1'=>'','metaTitle'=>'','metaKeyword'=>'','metaDescription'=>'');
		$f=core::form();
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
		$db=core::db();
		$validate=array(
			'id'=>array('primary'),
			'parentId'=>array('id','',true),
			'alias'=>array('latin','псевдоним',true,'max'=>50),
			'title'=>array('string','Заголовок',true),
			'text1'=>array('html','Вступительный текст'),
			'metaTitle'=>array('string'),
			'metaKeyword'=>array('string'),
			'metaDescription'=>array('string')
		);
		if(!$data['id']) { //Если категория новая, то узнать индекс сортировки
			$data['sort']=$db->fetchValue('SELECT MAX(sort) FROM shpCategory WHERE parentId='.$data['parentId']);
			$validate['sort']=array('integer');
		}
		if(!$data['alias']) $data['alias']=core::translit($data['title']);
		$model=core::model('shpCategory');
		$model->set($data);
		if(!$model->save($validate)) return false;
		//Обработка изображений категории (если загружены)
		if($data['image']['size']) {
			core::import('core/picture');
			$ext=strtolower(substr($data['image']['name'],strrpos($data['image']['name'],'.')+1)); //расширение файла изображения
			$picture=new picture($data['image']['tmpName'],$ext);
			if(controller::$error) return false; //Файл не является изображением?
			//Удалить существующее изображение, если таковое есть
			if($data['id']) {
				$fname=$db->fetchValue('SELECT image FROM shpCategory WHERE id='.$data['id']);
				if($fname) $fname=core::path().'public/shop-category/'.$fname;
				if($fname && file_exists($fname)) unlink($fname);
			}
			$cfg=core::config('shop');
			$picture->resize($cfg['categoryWidth'],$cfg['categoryHeight']);
			if(!$picture->save(core::path().'public/shop-category/'.$model->id,85,$ext)) return false;
			$db->query('UPDATE shpCategory SET image='.$db->escape($model->id.'.'.$ext).' WHERE id='.$model->id);
		}
		core::redirect('?controller=shopContent&action=category&id='.$data['id'],'Изменения сохранены');
	}

	/* Удаление категории товаров */
	public function actionCategoryDelete() {
		core::import('admin/model/shop');
		shop::deleteCategory($_GET['id']);
		core::redirect('?controller=shop','Категория удалена');
	}

	//Выводит список товаров в категории
	public function actionProductList() {
		$this->button('?controller=shopContent&action=productDelete','delete','Удалить',null,'onclick="document.forms.productList.action=this.href;document.forms.productList.submit();return false;"');
		$db=core::db();
		$db->query('SELECT id,title FROM shpProduct WHERE categoryId='.(int)$_GET['id'],100);
		$table=core::table();
		$table->rowTh('checkbox[id]|Название|');
		while($item=$db->fetch()) {
			$table->checkbox('shopContent','id',$item[0]);
			$table->text($item[1]);
			$table->text('');
		}
		$this->paginationCount=$db->foundRows();
		$this->table=$table;
		return 'ProductList';
	}
	/* Создание или изменение товара */
	public function actionProduct() {
		$db=core::db();
		if(isset($_GET['id'])) { //Изменение - загрузить данные этого товара
			$data=$db->fetchArrayOnceAssoc('SELECT * FROM shpProduct WHERE id='.$_GET['id']);
			if(!$data) core::error404();
			$group=$db->fetchArray('SELECT id,title,productId AS checked FROM shpProductGroup g LEFT JOIN shpProductGroupItem i ON i.groupId=g.id AND i.productId='.$data['id']);
		} else {
			$data=array('id'=>null,'categoryId'=>$_GET['category'],'brandId'=>null,'title'=>'','alias'=>'','text1'=>'','text2'=>'','price'=>0,'metaTitle'=>'','metaKeyword'=>'','metaDescription'=>'');
			$group=$db->fetchArray('SELECT id,title,NULL AS checked FROM shpProductGroup');
		}

		$f=core::form();
		$f->hidden('id',$data['id']);
		$f->hidden('categoryId',$data['categoryId']);
		$f->text('title','Заголовок (название)',$data['title']);
		$f->text('alias','URL (псевдоним)',$data['alias']);
		$f->text('price','Базовая цена',$data['price']);
		$f->select('brandId','Производитель','SELECT id,title FROM shpBrand ORDER BY title',$data['brandId'],'(нет)');
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
		core::import('admin/model/shop');
		$feature=shop::featureProduct($data['categoryId'],$data['id']);
		foreach($feature as $item1) {
			$f->html('<p><b>'.$item1['title'].'</b></p>');
			foreach($item1['data'] as $item2) {
				$f->field($item2['type'],'feature]['.$item2['id'],'&nbsp;'.$item2['title'],$item2['value'],$item2['data']);
			}
		}
		$f->submit('Сохранить');
		$this->cite='<b>Группы</b> позволяют объединять любые товары. Наиболее часто группы используются для публикации избранных товаров в какой-либо области сайта.';
		$this->scriptAdmin('shop');
		return $f;
	}

	public function actionProductSubmit($data) {
		$model=core::model('shpProduct');
		if(!$data['alias']) $data['alias']='';
		$model->set($data);
		if(!$model->save(array(
			'id'=>array('primary'),
			'categoryId'=>array('id','',true),
			'brandId'=>array('id',''),
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
			$db=core::db();
			$db->query('UPDATE shpProduct SET alias='.$db->escape($model->id).' WHERE id='.$model->id);
		}
		//Обновить группы, к которым привязан этот товар
		$values='';
		if($data['group']) {
			foreach($data['group'] as $id=>$value) {
				if(!$values) $values='('.$id.','.$model->id.')';
				else $values.=',('.$id.','.$model->id.')';
			}
		}
		$db=core::db();
		$db->query('DELETE FROM shpProductGroupItem WHERE productId='.$model->id);
		if($data['group']) $db->query('INSERT INTO shpProductGroupItem (groupId,productId) VALUES '.$values);
		//Обновить характеристики
		$db->query('DELETE FROM shpProductFeature WHERE productId='.$model->id);
		if(isset($data['feature'])) {
			$feature=$db->fetchArray('SELECT id,unit,type FROM shpFeature WHERE id IN('.implode(',',array_keys($data['feature'])).')');
			foreach($feature as $item) {
				$id=$item[0];
				if($item[2]=='checkbox') {
					if(isset($data['feature'][$id])) $value='да'; else $value='нет';
				} else $value=$data['feature'][$id];
				if(!$value) continue;
				if($item[1]) $value.=' '.$item[1];
				$db->query('INSERT INTO shpProductFeature (productId,featureId,value) VALUES ('.$model->id.','.$id.','.$db->escape($value).')');
			}
		}
		core::hook('modify','shop/category/'.$model->categoryId.'/'.$model->alias);
		core::redirect('?controller=shopContent&action=product&id='.$model->id,'Изменения сохранены');
	}

	/* Удаление товара */
	public function actionProductDelete() {
		core::import('admin/model/shop');
		shop::deleteProduct($_GET['id']);
		core::redirect('?controller=shop','Товар удалён');
	}

	//Удаление нескольких товаров
	public function actionProductDeleteSubmit($data) {
		core::import('admin/model/shop');
		shop::deleteProduct($data['id']);
		core::redirect('?controller=shop','Товары удалены');
	}
	/* Список изображений товара (для добавления/удаления) */
	public function actionProductImage() {
		$db=core::db();
		if(isset($_POST['shopContent']['id'])) $_GET['id']=$_POST['shopContent']['id'];
		$data=$db->fetchArrayOnce('SELECT mainImage,image FROM shpProduct WHERE id='.$_GET['id']);
		if(!$data[1]) $this->image=array(); else $this->image=explode(',',$data[1]);
		$this->mainImage=$data[0]; //Главное изображение
		$this->form=core::form();
		$this->form->hidden('id',$_GET['id']);
		$this->form->file('image','Изображение');
		$this->form->submit('Продолжить');
		return 'ProductImage';
	}

	public function actionProductImageSubmit($data) {
		$db=core::db();
		$image=$db->fetchArrayOnce('SELECT mainImage,image FROM shpProduct WHERE id='.$data['id']);
		$mainImage=$image[0]; //Основное изображение
		if($image[1]) $image=explode(',',$image[1]); else $image=array();
		$index=count($image)+1; //Для формирования имени файла
		core::import('core/picture');
		$cfg=core::config('shop');
		$ext=strtolower(substr($data['image']['name'],strrpos($data['image']['name'],'.')+1));
		$picture=new picture($data['image']['tmpName'],$ext);
		if(controller::$error) return false;
		$picture->resize($cfg['productFullWidth'],$cfg['productFullHeight']);
		$fname=$data['id'].'.'.$index;
		if(!$picture->save(core::path().'public/shop-product/'.$fname,100,$ext)) return false;
		$picture->resize($cfg['productThumbWidth'],$cfg['productThumbHeight']);
		if(!$picture->save(core::path().'public/shop-product/_'.$fname,80,$ext)) return false;
		$image[]=$fname.'.'.$ext;
		$q='UPDATE shpProduct SET image='.$db->escape(implode(',',$image));
		if(!$mainImage) $q.=',mainImage='.$db->escape($fname.'.'.$ext); //Если ранее небыло
		$db->query($q.' WHERE id='.$data['id']);
		core::redirect('?controller=shopContent&action=productImage&id='.$data['id']);
	}

	/* Установить выбранное изображение товара главным */
	public function actionProductImageMain() {
		$db=core::db();
		$db->query('UPDATE shpProduct SET mainImage='.$db->escape($_GET['image']).' WHERE id='.$_GET['id']);
		core::redirect('?controller=shopContent&action=productImage&id='.$_GET['id']);
	}

	/* Удаление изображения товара */
	public function actionProductImageDelete() {
		$db=core::db();
		$fname=core::path().'public/shop-product/'.$_GET['image'];
		if(file_exists($fname)) unlink($fname);
		$fname=core::path().'public/shop-product/_'.$_GET['image'];
		if(file_exists($fname)) unlink($fname);
		$data=$db->fetchArrayOnce('SELECT mainImage,image FROM shpProduct WHERE id='.$_GET['id']);
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
		$q='UPDATE shpProduct SET image='.$db->escape(implode($image,',')).',mainImage=';
		if(!$data[0]) $q.='NULL'; else $q.=$db->escape($data[0]);
		$db->query($q.' WHERE id='.$_GET['id']);
		core::redirect('?controller=shopContent&action=productImage&id='.$_GET['id']);
	}

	/* Переместить товар в другую категорию */
	public function actionProductMove() {
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT alias,categoryId FROM shpProduct WHERE id='.$_GET['id']);
		core::import('model/shop');
		$list=array();
		$in=shop::categoryTree();
		$this->_flatCategory($in[0]['child'],$list); //Плоский список категорий в $list
		unset($in);
		$f=core::form();
		$f->select('categoryId','Категория',$list,$data[1]);
		$f->hidden('id',$_GET['id']);
		$f->hidden('alias',$data[0]);
		$f->submit('Продолжить');
		return $f;
	}

	public function actionProductMoveSubmit($data) {
		$db=core::db();
		$db->query('UPDATE shpProduct SET categoryId='.$data['categoryId'].' WHERE id='.$data['id']);
		core::redirectPublic('shop/category/'.$data['categoryId'].'/'.$data['alias'],'Товар перенесён');
	}

	/* Список модификаций товара */
	public function actionVariant() {
		$db=core::db();
		$categoryId=$db->fetchValue('SELECT categoryId FROM shpProduct WHERE id='.$_GET['pid']); //Для определения списка характеристик
		$this->button('?controller=shopContent&action=variantItem&cid='.$categoryId.'&pid='.$_GET['pid'],'new','Создать модификацию товара');
		$db=core::db();
		$t=core::table();
		$t->rowTh('Название|');
		$db->query('SELECT id,title FROM shpVariant WHERE productId='.$_GET['pid']);
		while($item=$db->fetch()) {
			$t->link($item[1],'?controller=shopContent&action=variantItem&cid='.$categoryId.'&id='.$item[0]);
			$t->delete('?controller=shopContent&action=variantDelete&pid='.$_GET['pid'].'&id='.$item[0]);
		}
		return $t;
	}

	/* Создание или изменение модификации товара */
	public function actionVariantItem() {
		if(isset($_GET['id'])) { //Изменение
			$db=core::db();
			$data=$db->fetchArrayOnceAssoc('SELECT id,productId,title,feature FROM shpVariant WHERE id='.$_GET['id']);
		} else $data=array('id'=>null,'productId'=>$_GET['pid'],'title'=>'','feature'=>null);
		if(isset($_GET['cid'])) $cid=$_GET['cid']; else $cid=$_POST['shopContent']['categoryId']; //ИД категории
		$f=core::form();
		$f->hidden('id',$data['id']);
		$f->hidden('categoryId',$cid);
		$f->hidden('productId',$data['productId']);
		$f->text('title','Название (модель)',$data['title']);
		//Подключить список характеристик к форме
		core::import('admin/model/shop');
		$feature=shop::featureVariant($cid,$data['feature']); //По $data['feature'] определяются значения характеристик
		foreach($feature as $item1) {
			$f->html('<p><b>'.$item1['title'].'</b></p>');
			foreach($item1['data'] as $item2) {
				$f->field($item2['type'],'feature]['.$item2['id'],'&nbsp;'.$item2['title'],$item2['value']);
			}
		}
		$f->submit();
		return $f;
	}

	public function actionVariantItemSubmit($data) {
		//$feature - список характеристик, заданных для категории
		$db=core::db();
		$feature=$db->fetchValue('SELECT c.feature f0 FROM shpProduct p INNER JOIN shpCategory c ON c.id=p.categoryId WHERE p.id='.$data['productId']);
		//Обработка характеристик варианта товара, добавление единицы измерения (по модификациям поиска нет, поэтому можно ЕИ присоединить сразу)
		if($feature) {
			$feature=$db->fetchArray('SELECT id,unit,type FROM shpFeature WHERE id IN('.$feature.') AND variant=1');
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
		$m=core::model('shpVariant');
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'productId'=>array('integer'),
			'title'=>array('string','название',true),
			'feature'=>array('string')
		))) return false;
		if($isNew) $db->query('UPDATE shpProduct SET variant=variant+1 WHERE id='.$data['productId']); //Если новый товар, то обновить счётчик модификаций товаров
		core::redirect('?controller=shopContent&action=variant&pid='.$data['productId']);
	}

	/* Удаление модификации товара */
	public function actionVariantDelete() {
		$db=core::db();
		$db->query('DELETE FROM shpVariant WHERE id='.$_GET['id']);
		$db->query('UPDATE shpProduct SET variant=variant-1 WHERE id='.$_GET['pid']);
		core::redirect('?controller=shopContent&action=variant&pid='.$_GET['pid']);
	}

	//Упралвение производителями
	public function actionBrand() {
		$this->button('?controller=shopContent&action=brandItem','new','Добавить производителя');
		$db=core::db();
		$db->query('SELECT id,title,image FROM shpBrand ORDER BY title');
		$table=core::table();
		$table->rowTh('|');
		$url=core::url().'public/shop-brand/';
		while($item=$db->fetch()) {
			$table->text('<img src="'.$url.$item[2].'" /> '.$item[1],null,'style="vertical-align:middle;"');
			$table->itemDelete('?controller=shopContent&id='.$item[0].'&action=brand');
		}
		return $table;
	}

	//Создание/редактирование производителя
	public function actionBrandItem() {
		if(isset($_GET['id'])) {
			$db=core::db();
			$data=$db->fetchArrayOnceAssoc('SELECT id,alias,title,image,text1 FROM shpBrand WHERE id='.(int)$_GET['id']);
		} else $data=array('id'=>null,'alias'=>'','title'=>'','image'=>null,'text1'=>'');
		$form=core::form();
		$form->hidden('id',$data['id']);
		$form->text('title','Заголовок',$data['title']);
		$form->text('alias','Псевдоним (URL)',$data['alias']);
		$form->file('image','Изображение');
		$form->editor('text1','Статья',$data['text1']);
		$form->submit();
		return $form;
	}

	public function actionBrandItemSubmit($data) {
		$model=core::model('shpBrand');
		$model->set($data);
		if(!$model->save(array(
			'id'=>array('primary'),
			'title'=>array('string','заголовок',true,'max'=>50),
			'alias'=>array('latin','псевдоним (URL)',true,'max'=>50),
			'text1'=>array('html')
		))) return false;

		if($data['image']['size']) {
			core::import('core/picture');
			$cfg=core::config('shop');
			$picture=new picture($data['image']['tmpName'],$data['image']['type']);
			if(controller::$error) return false;
			$db=core::db();
			//Удалить старое изображение, если загружено новое
			if($data['id']) {
				$oldImage=$db->fetchValue('SELECT image FROM shpBrand WHERE id='.(int)$data['id']);
				if($oldImage) {
					$f=core::path().'/public/shop-brand/'.$oldImage;
					if(file_exists($f)) unlink($f);
				}
			}
			$picture->resize($cfg['brandWidth'],$cfg['brandHeight']);
			$extension=$picture->save(core::path().'public/shop-brand/'.$model->id);
			$db->query('UPDATE shpBrand SET image='.$db->escape($model->id.'.'.$extension).' WHERE id='.$model->id);
		}
		core::redirect('?controller=shopContent&action=brand');
	}

	//Удаление производителя
	public function actionBrandDelete() {
		$id=(int)$_GET['id'];
		$db=core::db();
		$image=$db->fetchValue('SELECT image FROM shpBrand WHERE id='.$id);
		if($image) {
			$image=core::path().'public/shop-brand/'.$image;
			if(file_exists($image)) unlink($image);
		}
		$db->query('UPDATE shpProduct SET brandId=null WHERE brandId='.$id);
		$db->query('DELETE FROM shpBrand WHERE id='.$id);
		core::redirect('?controller=shopContent&action=brand');
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