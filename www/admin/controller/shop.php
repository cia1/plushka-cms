<?php
/* Управление интернет-магазином (пора бы разбить этот контроллер на две части - файл стал достаточно велик) */
class sController extends controller {

	public function right($right,$action) {
		switch($action) {
		case 'Setting': case 'ProductGroup': case 'ProductGroupDelete':
			if(isset($right['shop.setting'])) return true; else return false;
		case 'Category': case 'CategoryDelete':
			if(isset($right['shop.category'])) return true; else return false;
		case 'Product': case 'ProductDelete': case 'ProductImage': case 'ProductImageMain': case 'ProductImageDelete': case 'ProductMove':
			if(isset($right['shop.product'])) return true; else return false;
		case 'FeatureList': case 'FeatureGroupItem': case 'FeatureGroupDelete': case 'FeatureItem': case 'FeatureDelete': case 'CategoryFeature':
			if(isset($right['shop.feature'])) return true; else return false;
		case 'Variant': case 'VariantItem': case 'VariantDelete':
			if(isset($right['shop.variant'])) return true; else return false;
		case 'Import': case 'ImportStart': case 'ImportProcess':
			if(isset($right['shop.import'])) return true; else return false;
		case 'MenuCategory': case 'MenuIndex': case 'WidgetCategory': case 'WidgetProductGroup': case 'WidgetCart': case 'WidgetFeatureSearch':
			return true;
		}
		return false;
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Общие настройки */
	public function actionSetting() {
		$u=core::user();
		if(isset($u->right['shop.feature'])) $this->button('?controller=shop&action=featureList','layout','Характеристики товаров');
		if(isset($u->right['shop.import'])) $this->button('?controller=shop&action=import','install','Импорт товаров','Импорт');
		$cfg=core::config('shop');
		$this->form1=core::form();
		//Общие
		$this->form1->html('<fieldset><legend>Общие</legend>');
		$this->form1->text('productOnPage','Товаров на странице',$cfg['productOnPage']);
		$this->form1->submit('Сохранить');

		//Изображения
		$this->form1->html('</fieldset><fieldset><legend>Изображения</legend>');
		if($cfg['productFullWidth'][0]=='<') {
			$productFullWidthType='<';
			$cfg['productFullWidth']=substr($cfg['productFullWidth'],1);
		} elseif($cfg['productFullWidth']) $productFullWidthType='=';
		else $productFullWidthType='';
		if($cfg['productFullHeight'][0]=='<') {
			$productFullHeightType='<';
			$cfg['productFullHeight']=substr($cfg['productFullHeight'],1);
		} elseif($cfg['productFullHeight']) $productFullHeightType='=';
		else $productFullHeightType='';
		if($cfg['productThumbWidth'][0]=='<') {
			$productThumbWidthType='<';
			$cfg['productThumbWidth']=substr($cfg['productThumbWidth'],1);
		} elseif($cfg['productThumbWidth']) $productThumbWidthType='=';
		else $productThumbWidthType='';
		if($cfg['productThumbHeight'][0]=='<') {
			$productThumbHeightType='<';
			$cfg['productThumbHeight']=substr($cfg['productThumbHeight'],1);
		} elseif($cfg['productThumbHeight']) $productThumbHeightType='=';
		else $productThumbHeightType='';
		$this->form1->html('<h3>Полноразмерное изображение товара</h3>');
		$this->form1->select('productFullWidthType','Ширина',array(array('','исходный размер/пропорционально исходному'),array('<','не более заданной:'),array('=','точное значение:')),$productFullWidthType);
		$this->form1->text('productFullWidth','Размер (пикс.)',$cfg['productFullWidth']);
		$this->form1->select('productFullHeightType','Высота',array(array('','исходный размер/пропорционально исходному'),array('<','не более заданной:'),array('=','точное значение:')),$productFullHeightType);
		$this->form1->text('productFullHeight','Размер (пикс.)',$cfg['productFullHeight']);
		$this->form1->html('<h3>Миниатюрное изображение товара</h3>');
		$this->form1->select('productThumbWidthType','Ширина',array(array('','исходный размер/пропорционально исходному'),array('<','не более заданной:'),array('=','точное значение:')),$productThumbWidthType);
		$this->form1->text('productThumbWidth','Размер (пикс.)',$cfg['productThumbWidth']);
		$this->form1->select('productThumbHeightType','Высота',array(array('','исходный размер/пропорционально исходному'),array('<','не более заданной:'),array('=','точное значение:')),$productThumbHeightType);
		$this->form1->text('productThumbHeight','Размер (пикс.)',$cfg['productThumbHeight']);

		$this->form1->html('<h3>Изображение категории</h3>');
		if($cfg['categoryWidth'][0]=='<') {
			$categoryWidthType='<';
			$cfg['categoryWidth']=substr($cfg['categoryWidth'],1);
		} elseif($cfg['categoryWidth']) $categoryWidthType='=';
		else $categoryWidthType='';
		if($cfg['categoryHeight'][0]=='<') {
			$categoryHeightType='<';
			$cfg['categoryHeight']=substr($cfg['categoryHeight'],1);
		} elseif($cfg['categoryHeight']) $categoryHeightType='=';
		else $categoryHeightType='';
		$this->form1->select('categoryWidthType','Ширина',array(array('','исходный размер/пропорционально исходному'),array('<','не более заданной:'),array('=','точное значение:')),$categoryWidthType);
		$this->form1->text('categoryWidth','Размер (пикс.)',$cfg['categoryWidth']);
		$this->form1->select('categoryHeightType','Высота',array(array('','исходный размер/пропорционально исходному'),array('<','не более заданной:'),array('=','точное значение:')),$categoryHeightType);
		$this->form1->text('categoryHeight','Размер (пикс.)',$cfg['categoryHeight']);
		$this->form1->submit('Сохранить');
		$this->form1->html('</fieldset><fieldset><legend>Шаблоны писем</legend>');

		//Шаблоны писем
		$html=file_get_contents(core::path().'data/email/shopOrderAdmin.html');
		$this->form1->editor('htmlAdmin','Сообщение администратору',$html);
		$this->form1->submit('Сохранить');
		$this->form1->html('<cite>Вы можете использовать следующие теги:<br /><b>{{siteName}}</b> - имя домена сайта, <b>{{siteLink}}</b> - ссылка на главную страницу сайта, <b>{{form}}</b> - информация о заказе, <b>{{cart}}</b> - список (таблица) товаров, <b>{{totalQuantity}}</b> - общее количество товарных позиций, <b>{{totalCost}}</b> - сумма заказа.');

		$this->form1->html('</fieldset>');

		//Группы товаров
		$t=core::table();
		$t->rowTh('|');
		$db=core::db();
		$db->query('SELECT id,title FROM shpProductGroup ORDER BY title');
		while($item=$db->fetch()) {
			$t->text('<a href="#" onclick="return productGroup('.$item[0].',\''.$item[1].'\');">'.$item[1].'</a>');
			$t->delete('?controller=shop&id='.$item[0].'&action=productGroupDelete');
		}
		$this->productGroup=$t;

		return 'Setting';
	}

	public function actionSettingSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config('shop');
		if(controller::$error) return false;
		if($data['productFullWidthType']=='') $cfg->productFullWidth=null;
		elseif($data['productFullWidthType']=='<') $cfg->productFullWidth='<'.$data['productFullWidth'];
		else $cfg->productFullWidth=(int)$data['productFullWidth'];
		if($data['productFullHeightType']=='') $cfg->productFullHeight=null;
		elseif($data['productFullHeightType']=='<') $cfg->productFullHeight='<'.$data['productFullHeight'];
		else $cfg->productFullHeight=(int)$data['productFullHeight'];
		if($data['productThumbWidthType']=='') $cfg->productThumbWidth=null;
		elseif($data['productThumbWidthType']=='<') $cfg->productThumbWidth='<'.$data['productThumbWidth'];
		else $cfg->productThumbWidth=(int)$data['productThumbWidth'];
		if($data['productThumbHeightType']=='') $cfg->productThumbHeight=null;
		elseif($data['productThumbHeightType']=='<') $cfg->productThumbHeight='<'.$data['productThumbHeight'];
		else $cfg->productThumbHeight=(int)$data['productThumbHeight'];

		if($data['categoryWidthType']=='') $cfg->categoryWidth=null;
		elseif($data['categoryWidthType']=='<') $cfg->categoryWidth='<'.$data['categoryWidth'];
		else $cfg->categoryWidth=(int)$data['categoryWidth'];
		if($data['categoryHeightType']=='') $cfg->categoryHeight=null;
		elseif($data['categoryHeightType']=='<') $cfg->categoryHeight='<'.$data['categoryHeight'];
		else $cfg->categoryHeight=(int)$data['categoryHeight'];

		$cfg->productOnPage=(int)$data['productOnPage'];
		if(!$cfg->save('shop')) return false;
		$f=fopen(core::path().'data/email/shopOrderAdmin.html','w');
		fwrite($f,$data['htmlAdmin']);
		fclose($f);

		core::redirect('?controller=shop&action=setting','Изменения сохранены');
	}

	/* Создание или изменение группы товаров (форма находится на странице "общие настройки") */
	public function actionProductGroupSubmit($data) {
		$m=core::model('shpProductGroup');
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'title'=>array('string','Заголовок',true)
		))) return false;
		core::redirect('?controller=shop&action=setting');
	}

	/* Удаление группы товаров */
	public function actionProductGroupDelete() {
		$db=core::db();
		$db->query('DELETE FROM shpProductGroupItem WHERE groupId='.$_GET['id']);
		$db->query('DELETE FROM shpProductGroup WHERE id='.$_GET['id']);
		core::redirect('?controller=shop&action=setting');
	}

	/* Общий список всех характеристик товаров */
	public function actionFeatureList() {
		$db=core::db();
		$this->featureGroup=$db->fetchArray('SELECT id,title FROM shpFeatureGroup');
		if(isset($_GET['gid'])) $this->gid=(int)$_GET['gid']; else $this->gid=null;

		$this->button('#','new','Создать категорию','Создать','onclick="featureGroupNew();return false;"');
		if($this->gid) {
			//Если выбрана категория характеристик, то добавить кнопки
			$this->button('#','delete','Удалить категорию','Удалить','onclick="featureGroupDelete();return false;"');
			$this->button('?controller=shop&action=featureItem&gid='.$_GET['gid'],'layoutNew','Создать характеристику');
			core::import('admin/model/shop');
			$data=shop::featureList($_GET['gid']);
			if(!$data) $this->null=true; //Признак пустого списка
			else {
				$this->null=false;
				$t=core::table();
				$t->rowTh('|');
				foreach($data as $item) {
					$t->text($item['title']);
					$t->itemDelete('?controller=shop&id='.$item['id'].'&gid='.$_GET['gid'].'&action=feature');
				}
				$this->t=$t;
			}
		}
		$this->scriptAdmin('shop');
		return 'FeatureList';
	}

	public static function actionFeatureGroupItemSubmit($data) {
		$db=core::db();
		if(isset($data['id'])) {
			$db->query('UPDATE shpFeatureGroup SET title='.$db->escape($data['title']).' WHERE id='.(int)$data['id']);
			echo "OK\n".$data['id'];
		}	else {
			$db->query('INSERT INTO shpFeatureGroup (title) VALUES ('.$db->escape($data['title']).')');
			echo "OK\n".$db->insertId();
		}
		exit;
	}

	/* Удаление группы характеристик товаров */
	public static function actionFeatureGroupDeleteSubmit($data) {
		core::import('admin/model/shop');
		shop::featureGroupDelete($data['id']);
		echo 'OK';
		exit;
	}

	/* Создание или изменение характеристики товаров */
	public function actionFeatureItem() {
		if(isset($_GET['id'])) { //Изменение
			$db=core::db();
			$data=$db->fetchArrayOnceAssoc('SELECT id,title,type,unit,variant,data FROM shpFeature WHERE id='.$_GET['id']);
			$data['data']=str_replace('|',"\n",$data['data']);
		} else $data=array('id'=>null,'title'=>'','type'=>'text','unit'=>'','variant'=>false,'data'=>'');
		if(isset($_GET['gid'])) $gid=(int)$_GET['gid']; else $gid=$_POST['shop']['groupId']; //Группа характеристик
		$f=core::form();
		$f->hidden('id',$data['id']);
		$f->hidden('groupId',$gid);
		$f->text('title','Название',$data['title']);
		$f->select('type','Тип',array(array('checkbox','да/нет'),array('text','текст'),array('select','список значений')),$data['type'],null,'onchange="if(this.value==\'select\') $(\'.data\').show(); else $(\'.data\').hide();"');
		$f->textarea('data','Возможные значения:',$data['data']);
		$f->checkbox('variant','Модификации (варианты)',$data['variant']);
		$f->text('unit','ЕИ (текст)',$data['unit']);
		$f->submit();
		if($data['type']!='select') $f->html('<script type="text/javascript">setTimeout(function() { $(\'.data\').hide(); },100);</script>');
		$this->cite='Если <b>Модификации</b> отмечен, то характеристика также будет использоваться для модификаций товаров';
		return $f;
	}

	public function actionFeatureItemSubmit($data) {
		//Если снят чекбокс "модификации" (характеристика также для модификаций товаров), то, возможно, нужно удалить характеристики модификаций существующих товаров
		$variantDelete=false;
		if($data['id'] && !isset($data['variant'])) {
			$db=core::db();
			if($db->fetchValue('SELECT variant FROM shpFeature WHERE id='.$data['id'])=='1') $variantDelete=true;
		}
		$m=core::model('shpFeature');
		if($data['type']!='select') $data['data']=null; else $data['data']=str_replace(array("\r","\n"),array('','|'),$data['data']);
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'type'=>array('string'),
			'groupId'=>array('integer','группа',true),
			'title'=>array('string','заголовок',true),
			'unit'=>array('string'),
			'variant'=>array('boolean'),
			'data'=>array('string')
		))) return false;
		if($variantDelete) {
			core::import('admin/model/shop');
			shop::featureCategoryDelete($data['id']);
		}
		core::redirect('?controller=shop&action=featureList&gid='.$data['groupId']);
	}

	/* Удаление характеристики */
	public function actionFeatureDelete() {
		core::import('admin/model/shop');
		shop::featureDelete($_GET['id']);
		core::redirect('?controller=shop&action=featureList&gid='.$_GET['gid']);
	}

	/* Создание или изменение категории товаров */
	public function actionCategory() {
		if(isset($_GET['id'])) { //Изменение
			$db=core::db();
			$data=$db->fetchArrayOnceAssoc('SELECT * FROM shpCategory WHERE id='.$_GET['id']);
			if(!$data) core::error404();
		} else $data=array('id'=>null,'parentId'=>(isset($_GET['parent']) ? $_GET['parent'] : 0),'title'=>'','text1'=>'','metaTitle'=>'','metaKeyword'=>'','metaDescription'=>'');
		$f=core::form();
		$f->hidden('id',$data['id']);
		$f->hidden('parentId',$data['parentId']);
		$f->text('title','Заголовок',$data['title']);
		$f->editor('text1','Вступительный текст',$data['text1']);
		if($data['id']!=='0') $f->file('image','Изображение');
		$f->text('metaTitle','meta Заголовок',$data['metaTitle']);
		$f->text('metaKeyword','meta Ключевые слова',$data['metaKeyword']);
		$f->text('metaDescription','meta Описание',$data['metaDescription']);
		$f->submit('Сохранить');

		$this->cite='Рисунок, загруженный в поле <b>изображение</b> будет публиковаться в списке категорий.';
		return $f;
	}

	public function actionCategorySubmit($data) {
		$db=core::db();
		$validate=array(
			'id'=>array('primary'),
			'parentId'=>array('id','',true),
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
				if($fname) $fname=core::path().'public/categoryImage/'.$fname;
				if($fname && file_exists($fname)) unlink($fname);
			}
			$cfg=core::config('shop');
			$picture->resize($cfg['categoryWidth'],$cfg['categoryHeight']);
			if(!$picture->save(core::path().'public/categoryImage/'.$model->id,85,$ext)) return false;
			$db->query('UPDATE shpCategory SET image='.$db->escape($model->id.'.'.$ext).' WHERE id='.$model->id);
		}
		core::redirect('?controller=shop&action=category&id='.$data['id'],'Изменения сохранены');
	}

	/* Удаление категории товаров */
	public function actionCategoryDelete() {
		core::import('admin/model/shop');
		shop::deleteCategory($_GET['id']);
		core::redirect('?controller=shop','Категория удалена');
	}

	/* Сопоставление характеристик товаров для категории */
	public function actionCategoryFeature() {
		$this->scriptAdmin('shop');
		core::import('admin/model/shop');
		if(isset($_GET['id'])) $this->id=$_GET['id']; else $this->id=$_POST['shop']['categoryId'];
		$db=core::db();
		$feature=$db->fetchValue('SELECT feature FROM shpCategory WHERE id='.$this->id);
		$this->feature2=shop::featureList(null,$feature); //Уже отмеченные для категории
		$this->feature1=shop::featureList(null,null,$this->feature2); //Не отмеченные
		return 'CategoryFeature';
	}

	public function actionCategoryFeatureSubmit($data) {
		//$lst1 - отмеченные в форме
		$lst1=explode(',',$data['feature']);
		$db=core::db();
		//$lst2 - отмеченные ранее
		$lst2=$db->fetchValue('SELECT feature FROM shpCategory WHERE id='.$data['categoryId']);
		if($lst2) $lst2=explode(',',$lst2); else $lst2=array();
		//$lst - характеристики, которые были сняты
		$lst=array_diff($lst2,$lst1);
		if($lst) {
			core::import('admin/model/shop');
			shop::featureCategoryDelete(implode(',',$lst),false,$data['categoryId']); //Убрать у всех товаров и модификаций (в будущем будет чекбоксе на форме)
		}
		$db->query('UPDATE shpCategory SET feature='.$db->escape($data['feature']).' WHERE id='.$data['categoryId']);
		core::redirect('?controller=shop&action=categoryFeature&cid='.$data['categoryId'],'Изменения сохранены');
	}

	/* Создание или изменение товара */
	public function actionProduct() {
		$db=core::db();
		if(isset($_GET['id'])) { //Изменение - загрузить данные этого товара
			$data=$db->fetchArrayOnceAssoc('SELECT * FROM shpProduct WHERE id='.$_GET['id']);
			if(!$data) core::error404();
			$db->query('SELECT id,title,productId AS checked FROM shpProductGroup g LEFT JOIN shpProductGroupItem i ON i.groupId=g.id AND i.productId='.$data['id']);
		} else {
			$data=array('id'=>null,'categoryId'=>$_GET['category'],'title'=>'','alias'=>'','text1'=>'','text2'=>'','price'=>0,'metaTitle'=>'','metaKeyword'=>'','metaDescription'=>'');
			$db->query('SELECT id,title,NULL AS checked FROM shpProductGroup');
		}

		$f=core::form();
		$f->hidden('id',$data['id']);
		$f->hidden('categoryId',$data['categoryId']);
		$f->text('title','Заголовок (название)',$data['title']);
		$f->text('alias','URL (псевдоним)',$data['alias']);
		$f->text('price','Базовая цена',$data['price']);
		$f->editor('text1','Описание 1 (краткое)',$data['text1']);
		$f->editor('text2','Описание 2 (полное)',$data['text2']);
		$f->text('metaTitle','meta Заголовок',$data['metaTitle']);
		$f->text('metaKeyword','meta Ключевые слова',$data['metaKeyword']);
		$f->text('metaDescription','meta Описание',$data['metaDescription']);
		$f->html('<h1 style="clear:both;">Группы</h1>');
		//Добавить в форму группы товаров
		while($item=$db->fetch()) {
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
		core::redirect('?controller=shop&action=product&id='.$model->id,'Изменения сохранены');
	}

	/* Удаление товара */
	public function actionProductDelete() {
		core::import('admin/model/shop');
		shop::deleteProduct($_GET['id']);
		core::redirect('?controller=shop','Товар удалён');
	}

	/* Список изображений товара (для добавления/удаления) */
	public function actionProductImage() {
		$db=core::db();
		if(isset($_POST['shop']['id'])) $_GET['id']=$_POST['shop']['id'];
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
		if(!$picture->save(core::path().'public/productImage/'.$fname,100,$ext)) return false;
		$picture->resize($cfg['productThumbWidth'],$cfg['productThumbHeight']);
		if(!$picture->save(core::path().'public/productImage/_'.$fname,80,$ext)) return false;
		$image[]=$fname.'.'.$ext;
		$q='UPDATE shpProduct SET image='.$db->escape(implode(',',$image));
		if(!$mainImage) $q.=',mainImage='.$db->escape($fname.'.'.$ext); //Если ранее небыло
		$db->query($q.' WHERE id='.$data['id']);
		core::redirect('?controller=shop&action=productImage&id='.$data['id']);
	}

	/* Установить выбранное изображение товара главным */
	public function actionProductImageMain() {
		$db=core::db();
		$db->query('UPDATE shpProduct SET mainImage='.$db->escape($_GET['image']).' WHERE id='.$_GET['id']);
		core::redirect('?controller=shop&action=productImage&id='.$_GET['id']);
	}

	/* Удаление изображения товара */
	public function actionProductImageDelete() {
		$db=core::db();
		$fname=core::path().'public/productImage/'.$_GET['image'];
		if(file_exists($fname)) unlink($fname);
		$fname=core::path().'public/productImage/_'.$_GET['image'];
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
		core::redirect('?controller=shop&action=productImage&id='.$_GET['id']);
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
		$this->button('?controller=shop&action=variantItem&cid='.$categoryId.'&pid='.$_GET['pid'],'new','Создать модификацию товара');
		$db=core::db();
		$t=core::table();
		$t->rowTh('Название|');
		$db->query('SELECT id,title FROM shpVariant WHERE productId='.$_GET['pid']);
		while($item=$db->fetch()) {
			$t->link($item[1],'?controller=shop&action=variantItem&cid='.$categoryId.'&id='.$item[0]);
			$t->delete('?controller=shop&action=variantDelete&pid='.$_GET['pid'].'&id='.$item[0]);
		}
		return $t;
	}

	/* Создание или изменение модификации товара */
	public function actionVariantItem() {
		if(isset($_GET['id'])) { //Изменение
			$db=core::db();
			$data=$db->fetchArrayOnceAssoc('SELECT id,productId,title,feature FROM shpVariant WHERE id='.$_GET['id']);
		} else $data=array('id'=>null,'productId'=>$_GET['pid'],'title'=>'','feature'=>null);
		if(isset($_GET['cid'])) $cid=$_GET['cid']; else $cid=$_POST['shop']['categoryId']; //ИД категории
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
		core::redirect('?controller=shop&action=variant&pid='.$data['productId']);
	}

	/* Удаление модификации товара */
	public function actionVariantDelete() {
		$db=core::db();
		$db->query('DELETE FROM shpVariant WHERE id='.$_GET['id']);
		$db->query('UPDATE shpProduct SET variant=variant-1 WHERE id='.$_GET['pid']);
		core::redirect('?controller=shop&action=variant&pid='.$_GET['pid']);
	}

	/* Настраиваемый импорт данных из Excel. В будущем нужно вынести настройки отдельно. */
	public function actionImport() {
		$cfg=core::configAdmin('shop'); //Конфигурация в /admin/config/shop.php
		$field=array( //Основные поля
//			array('categoryId','Категория (идентификатор)',false),
//			array('categoryAlias','Категория (псевдоним)',false),
			array('categoryTitle','Категория (название)',false),
			array('alias','Псевдоним товара',true),
			array('title','Название товара',true),
			array('text1','Краткое описание',false),
			array('text2','Полное описание',false),
			array('price','Цена',false),
			array('metaTitle','meta Заголовок',true),
			array('metaDescription','meta Описание',false),
			array('metaKeyword','meta Ключевые слова',false),
			array('quantity','Остаток на складе',false)
		);
		$f=core::form();
		$f->file('file','Файл с данными');
		$f->text('firstRow','Первая строка с данными',$cfg['firstRow']);
		$f->checkbox('notDelete','Не удалять товары',$cfg['notDelete']);
		$f->html('<h2>Определение столбцов</h2>');
		//$unique - список уникальных полей, по которым можно сопоставить товары
		$unique=array();
		foreach($field as $item) {
			if(isset($cfg[$item[0]])) $value=$cfg[$item[0]]; else $value=null;
			$f->text($item[0],$item[1],$value);
			if($item[2]) $unique[]=array($item[0],$item[1]);
		}
		//Добавить к форме, а также к списку уникальный полей все характеристики
		$db=core::db();
		$db->query('SELECT g.title gTitle,f.id,f.title FROM shpFeature f INNER JOIN shpFeatureGroup g ON g.id=f.groupId ORDER BY g.title,f.title');
		$gTitle='';
		while($item=$db->fetch()) {
			if($gTitle!=$item[0]) {
				$gTitle=$item[0];
				$f->html('<h3>'.$gTitle.'</h3>');
				$unique[]=array('','+ '.$gTitle);
			}
			$unique[]=array('feature'.$item[1],'&nbsp;&nbsp;&nbsp;&nbsp;'.$item[2],true);
			if(isset($cfg['feature'.$item[1]])) $value=$cfg['feature'.$item[1]]; else $value=null;
			$f->text('feature'.$item[1],'&nbsp;&nbsp;&nbsp;&nbsp;'.$item[2],$value);
		}
		$f->label('','');
		$f->select('unique','Уникальное поле',$unique,$cfg['unique']);
		$f->submit();
		$this->cite='Импорт данных производится в формате <b>XLS</b>. В блоке <b>определение столбцов</b> необходимо указать название (символы A-Z) столбца в EXCEL-документе, в котором находятся соответствующие данные. Оставьте поле пустым для тех данных, которые не нужно импортировать.<br /><b>Уникальное поле</b> - поле, по которому будет проведён поиск товаров на сайте (для исключения ненужных копий).';
		return $f;
	}

	public function actionImportSubmit($data) {
		//Переместить загруженный файл во временный директорий (/tmp/shopImport.xls)
		if(!move_uploaded_file($data['file']['tmpName'],core::path().'tmp/shopImport.xls')) {
			controller::$error='Не удалось загрузить excel-файл';
			return false;
		}
		//Сохранить настройки импорта в файл конфигурации
		$cfg=core::configAdmin('shop'); // /admin/config/shop.php
		unset($data['file']); //это не нужно
		if($data!=$cfg) { //А были ли изменения в конфигурации?
			core::import('admin/core/config');
			$cfg=new config();
			foreach($data as $key=>$value) {
				if(!$value) continue;
				$cfg->$key=$value;
			}
			if(isset($data['notDelete'])) $cfg->notDelete=true; else $cfg->notDelete=false;
			$cfg->save('../admin/config/shop');
		}
		//Удалить временные файлы
		$f=core::path().'tmp/shopImport.log';
		if(file_exists($f)) unlink($f);
		$f=core::path().'tmp/shopImportId.txt';
		if(file_exists($f)) unlink($f);
		core::redirect('?controller=shop&action=importStart');
	}

	/* Запуск импорта */
	public function actionImportStart() {
		$this->link=core::url().'admin/index2.php?controller=shop&action=importProcess&stage=load&row=0&_front';
		return 'ImportStart';
	}

	/* Процесс импорта (очередная "пачка" строк). Позиция определяется из параметра $_GET['row'] */
	public function actionImportProcess() {
		core::import('admin/model/shopImport');
	 	if($_GET['stage']=='load') { //"В работе"
			define('ROW_LIMIT',100); //Обрабатывать по 100 товаров за раз
			$cfg=core::configAdmin('shop');
			$start=(int)$_GET['row']; //Строка, с которой начинать обработку
			if(!$start) $start=$cfg['firstRow'];
			$count=shopImport::loadXLS($cfg,$start,ROW_LIMIT);
			$start=$start+$count;
			$this->total=$start-$cfg['firstRow'];
			if($count!=ROW_LIMIT) $this->link='index2.php?controller=shop&action=importProcess&stage=clear'; else $this->link='index2.php?controller=shop&action=importProcess&stage=load&row='.$start;
			return 'ImportLoad';
		}
		if($_GET['stage']=='clear') { //Импорт завершён - удалить временные файлы
			$data=shopImport::clear();
			$this->rowCount=$data[0];
			$this->deleteCount=$data[1];
			$f=core::path().'tmp/shopImport.log';
			if(file_exists($f)) $this->log=nl2br(file_get_contents($f)); else $this->log=null; //Тут содержится лог импорта
			return 'ImportEnd';
	 	}
	}

/* ----------------------------------------------------------------------------------- */


/* ---------- MENU ------------------------------------------------------------------- */
	/* Категория интернет-магазина. Ссылка shop/category/ИД */
	public function actionMenuCategory() {
		$categoryId=(int)strrpos($_GET['link'],'/');
		if($categoryId) $categoryId=substr($_GET['link'],$categoryId+1);
		$f=core::form();
		$f->select('categoryId','Категория','SELECT id,title FROM shpCategory WHERE parentId=0',$categoryId,'- все категории -');
		$f->submit('Продолжить');

		$this->cite='Укажите раздел (категорию) интернет-магазина, которая должна открываться при переходе по этому пункту меню. Если выбрано "- все категории -", то будет открыта страница со списком всех разделов магазина.';
		return $f;
	}

	public function actionMenuCategorySubmit($data) {
		if($data['categoryId']) return 'shop/category/'.$data['categoryId']; else return 'shop/category';
	}

	/* Главная страница сайта (пока нет таковой) */
	public function actionMenuIndex() {
		$f=core::form();
		$f->submit('Продолжить');
		return $f;
	}

	public function actionMenuIndexSubmit($data) {
		return 'shop';
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Дерево категорий */
	public function actionWidgetCategory() {
		$f=core::form();
		$f->submit('Продолжить','submit');
		return $f;
	}

	public function actionWidgetCategorySubmit($data) {
		return '';
	}

	/* Список товаров, находящихся в заданной группе
	Параметры: (int) categoryId - ИД категории */
	public function actionWidgetProductGroup($data=null) {
		$f=core::form();
		$f->hidden('cacheTime','30'); //Время кеширования
		$f->select('categoryId','Категория','SELECT id,title FROM shpProductGroup',$data);
		$f->submit('Продолжить');
		return $f;
	}

	public function actionWidgetProductGroupSubmit($data) {
		return $data['categoryId'];
	}

	/* Корзина
	Параметры: bool $product - выводить или нет список товаров в корзине; bool $total - выводить итоговые значения;
	bool $checkout - выводить ссылку на страницу оформления заказа */
	public function actionWidgetCart($data=null) {
		if(!$data) $data=array('total'=>true,'checkout'=>true,'product'=>null);
		$f=core::form();
		$f->checkbox('product','Список товаров',$data['product']);
		$f->checkbox('total','Итоговые значения',$data['total']);
		$f->checkbox('checkout','Ссылка на оформление заказа',$data['checkout']);
		$f->submit('Продолжить');
		return $f;
	}

	public function actionWidgetCartSubmit($data) {
		if(isset($data['product'])) $data['product']=true; else $data['product']=false;
		if(isset($data['total'])) $data['total']=true; else $data['total']=false;
		if(isset($data['checkout'])) $data['checkout']=true; else $data['checkout']=false;
		return $data;
	}

	/* Поиск по характеристикам товара
	Параметры: bool $price - поиск по цене; array $feature - список характеристик и их настройки */
	public function actionWidgetFeatureSearch($data=null) {
		if(!$data) $this->data=array(); else $this->data=$data;
		core::import('admin/model/shop');
		$this->feature=shop::featureList();
		return 'WidgetFeatureSearch';
	}

	public function actionWidgetFeatureSearchSubmit($data) {
		$out=array('feature'=>array());
		if(isset($data['price'])) $out['price']=true; else $out['price']=false;
		foreach($data['item'] as $id=>$null) {
			$type=$data['type'][$id];
			$d=array('type'=>$type);
			if($type=='slider') {
				$d['min']=(int)$data['min'][$id];
				$d['max']=(int)$data['max'][$id];
				if(!$d['max']) continue;
			}
			$out['feature'][$id]=$d;
		}
		return $out;
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