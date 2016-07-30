<?php
/* Управление интернет-магазином (настройка магазина, импорт) */
class sController extends controller {

	public function right($right,$action) {
		switch($action) {
		case 'Setting': case 'ProductGroup': case 'ProductGroupDelete':
			if(isset($right['shopSetting.setting'])) return true; else return false;
		case 'FeatureList': case 'FeatureGroupItem': case 'FeatureGroupDelete': case 'FeatureItem': case 'FeatureDelete': case 'CategoryFeature':
			if(isset($right['shopSetting.feature'])) return true; else return false;
		case 'Import': case 'ImportStart': case 'ImportProcess':
			if(isset($right['shopSetting.import'])) return true; else return false;
		case 'MenuCategory': case 'MenuIndex': case 'WidgetCategory': case 'WidgetProductGroup': case 'WidgetCart': case 'WidgetFeatureSearch':
			return true;
		}
		return false;
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Общие настройки */
	public function actionSetting() {
		$u=core::user();
		if($u->group==255 || isset($u->right['shopSetting.feature'])) $this->button('?controller=shopSetting&action=featureList','layout','Характеристики товаров');
		if($u->group==255 || isset($u->right['shopSetting.import'])) $this->button('?controller=shopSetting&action=import','install','Импорт товаров','Импорт');
		if($u->group==255 || isset($u->right['shopContent.brand'])) $this->button('?controller=shopContent&action=brand','brand','Управление производителями','Производители');
		$cfg=core::config('shop');
		$this->form1=core::form();
		//Общие
		$this->form1->html('<fieldset><legend>Общие</legend>');
		$this->form1->text('productOnPage','Товаров на странице',$cfg['productOnPage']);
		$this->form1->submit('Сохранить');

		//Изображения
		$this->form1->html('</fieldset><fieldset><legend>Изображения</legend>');

		$this->form1->html('<h3>Полноразмерное изображение товара</h3>');
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
		$this->form1->select('productFullWidthType','Ширина',array(array('','исходный размер/пропорционально исходному'),array('<','не более заданной:'),array('=','точное значение:')),$productFullWidthType);
		$this->form1->text('productFullWidth','Размер (пикс.)',$cfg['productFullWidth']);
		$this->form1->select('productFullHeightType','Высота',array(array('','исходный размер/пропорционально исходному'),array('<','не более заданной:'),array('=','точное значение:')),$productFullHeightType);
		$this->form1->text('productFullHeight','Размер (пикс.)',$cfg['productFullHeight']);

		$this->form1->html('<h3>Миниатюрное изображение товара</h3>');
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

		$this->form1->html('<h3>Изображение производителя (логотип)</h3>');
		if($cfg['brandHeight'][0]=='<') {
			$brandHeightType='<';
			$cfg['brandHeight']=substr($cfg['brandHeight'],1);
		} elseif($cfg['brandHeight']) $brandHeightType='=';
		else $brandHeightType='';
		if($cfg['brandWidth'][0]=='<') {
			$brandWidthType='<';
			$cfg['brandWidth']=substr($cfg['brandWidth'],1);
		} elseif($cfg['brandWidth']) $brandWidthType='=';
		else $brandWidthType='';
		$this->form1->select('brandWidthType','Ширина',array(array('','исходный размер/пропорционально исходному'),array('<','не более заданной:'),array('=','точное значение:')),$brandWidthType);
		$this->form1->text('brandWidth','Размер (пикс.)',$cfg['brandWidth']);
		$this->form1->select('brandHeightType','Высота',array(array('','исходный размер/пропорционально исходному'),array('<','не более заданной:'),array('=','точное значение:')),$brandHeightType);
		$this->form1->text('brandHeight','Размер (пикс.)',$cfg['brandHeight']);

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
			$t->delete('?controller=shopSetting&id='.$item[0].'&action=productGroupDelete');
		}
		$this->productGroup=$t;

		return 'Setting';
	}

	public function actionSettingSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config('shop');
		if(core::error()) return false;
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

		if($data['brandWidthType']=='') $cfg->brandWidth=null;
		elseif($data['brandWidthType']=='<') $cfg->brandWidth='<'.$data['brandWidth'];
		else $cfg->brandWidth=(int)$data['brandWidth'];
		if($data['brandHeightType']=='') $cfg->brandHeight=null;
		elseif($data['brandHeightType']=='<') $cfg->brandHeight='<'.$data['brandHeight'];
		else $cfg->brandHeight=(int)$data['brandHeight'];

		$cfg->productOnPage=(int)$data['productOnPage'];
		if(!$cfg->save('shop')) return false;
		$f=fopen(core::path().'data/email/shopOrderAdmin.html','w');
		fwrite($f,$data['htmlAdmin']);
		fclose($f);

		core::redirect('?controller=shopSetting&action=setting','Изменения сохранены');
	}

	/* Создание или изменение группы товаров (форма находится на странице "общие настройки") */
	public function actionProductGroupSubmit($data) {
		$m=core::model('shpProductGroup');
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'title'=>array('string','Заголовок',true)
		))) return false;
		core::redirect('?controller=shopSetting&action=setting');
	}

	/* Удаление группы товаров */
	public function actionProductGroupDelete() {
		$db=core::db();
		$db->query('DELETE FROM shpProductGroupItem WHERE groupId='.$_GET['id']);
		$db->query('DELETE FROM shpProductGroup WHERE id='.$_GET['id']);
		core::redirect('?controller=shopSetting&action=setting');
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
			$this->button('?controller=shopSetting&action=featureItem&gid='.$_GET['gid'],'layoutNew','Создать характеристику');
			core::import('admin/model/shop');
			$data=shop::featureList($_GET['gid']);
			if(!$data) $this->null=true; //Признак пустого списка
			else {
				$this->null=false;
				$t=core::table();
				$t->rowTh('|');
				foreach($data as $item) {
					$t->text($item['title']);
					$t->itemDelete('?controller=shopSetting&id='.$item['id'].'&gid='.$_GET['gid'].'&action=feature');
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
		if(isset($_GET['gid'])) $gid=(int)$_GET['gid']; else $gid=$_POST['shopSetting']['groupId']; //Группа характеристик
		$f=core::form();
		$f->hidden('id',$data['id']);
		$f->hidden('groupId',$gid);
		$f->text('title','Название',$data['title']);
		$f->select('type','Тип',array(array('checkbox','да/нет'),array('text','текст'),array('select','список значений')),$data['type'],null,'onchange="if(this.value==\'select\') $(\'.data\').show(); else $(\'.data\').hide();"');
		$f->textarea('data','Возможные значения:',$data['data']);
		$f->checkbox('variant','Модификации (варианты)',$data['variant']);
		$f->text('unit','ЕИ (текст)',$data['unit']);
		$f->submit();
		if($data['type']!='select') $f->html('<script>setTimeout(function() { $(\'.data\').hide(); },100);</script>');
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
		core::redirect('?controller=shopSetting&action=featureList&gid='.$data['groupId']);
	}

	/* Удаление характеристики */
	public function actionFeatureDelete() {
		core::import('admin/model/shop');
		shop::featureDelete($_GET['id']);
		core::redirect('?controller=shopSetting&action=featureList&gid='.$_GET['gid']);
	}

	/* Сопоставление характеристик товаров для категории */
	public function actionCategoryFeature() {
		$this->scriptAdmin('shop');
		core::import('admin/model/shop');
		if(isset($_GET['id'])) $this->id=$_GET['id']; else $this->id=$_POST['shopSetting']['categoryId'];
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
		core::redirect('?controller=shopSetting&action=categoryFeature&cid='.$data['categoryId'],'Изменения сохранены');
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
			core::error('Не удалось загрузить excel-файл');
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
			$cfg->save('admin/shop');
		}
		//Удалить временные файлы
		$f=core::path().'tmp/shopImport.log';
		if(file_exists($f)) unlink($f);
		$f=core::path().'tmp/shopImportId.txt';
		if(file_exists($f)) unlink($f);
		core::redirect('?controller=shopSetting&action=importStart');
	}

	/* Запуск импорта */
	public function actionImportStart() {
		$this->link=core::url().'admin/index2.php?controller=shopSetting&action=importProcess&stage=load&row=0&_front';
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
			if($count!=ROW_LIMIT) $this->link='index2.php?controller=shopSetting&action=importProcess&stage=clear'; else $this->link='index2.php?controller=shopSetting&action=importProcess&stage=load&row='.$start;
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
		$categoryAlias=strrpos($_GET['link'],'/');
		if($categoryAlias) $categoryAlias=substr($_GET['link'],$categoryAlias+1);
		$f=core::form();
		$f->select('categoryAlias','Категория','SELECT alias,title FROM shpCategory WHERE parentId=0',$categoryAlias,'- все категории -');
		$f->submit('Продолжить');

		$this->cite='Укажите раздел (категорию) интернет-магазина, которая должна открываться при переходе по этому пункту меню. Если выбрано "- все категории -", то будет открыта страница со списком всех разделов магазина.';
		return $f;
	}

	public function actionMenuCategorySubmit($data) {
		if($data['categoryAlias']) return 'shop/'.$data['categoryAlias']; else return 'shop';
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
		if(!$data) {
			$this->price=false;
			$this->brand=false;
			$dataFeature=array();
		} else {
			$this->price=$data['price'];
			$this->brand=$data['brand'];
			$dataFeature=$data['feature'];
		}
		core::import('admin/model/shop');
		$this->feature=shop::featureList();
		foreach($this->feature as $groupId=>&$groupData) {
			foreach($groupData['data'] as &$featureData) {
				$featureId=$featureData['id'];
				if(isset($dataFeature[$featureId])) {
					$featureData['checked']=true;
					$featureData['displayType']=$dataFeature[$featureId]['type'];
					if($dataFeature[$featureId]['type']=='range') {
						$featureData['min']=$dataFeature[$featureId]['min'];
						$featureData['max']=$dataFeature[$featureId]['max'];
					}
				} else {
					$featureData['checked']=false;
					$featureData['displayType']=null;
					if($featureData['type']=='text') $featureData['min']=$featureData['max']=0;
				}
			}
		}
		$this->style('shop');
		return 'WidgetFeatureSearch';
	}

	//Рисует выпадающий список для выбора типа характеристики
	public static function renderFeatureSelect($type,$id,$displayType) { ?>
		<select name="shopSetting[type][<?=$id?>]" onchange="shopSelectType(this);">
			<?php switch($type) {
			case 'text': ?>
				<option value="list"<?php if($displayType=='list') echo ' checked="checked"'; ?>>загрузить список</option>
				<option value="range"<?php if($displayType=='range') echo ' checked="checked"'; ?>>диапазон целых чисел</option>
				<?php break;
			case 'select': ?>
				<option value="select"<?php if($displayType=='select') echo ' checked="checked"'; ?>>выпадающий список</option>
				<option value="checkboxList"<?php if($displayType=='checkboxList') echo ' checked="checked"'; ?>>список чекбоксов</option>
				<?php break;
			case 'checkbox': ?>
				<option value="checkbox">флажок</option>
				<?php break;
			} ?>
		</select>
	<?php	}

	//Рисует поля дополнительных данных для характеристики
	public static function renderFeatureData($data) {
		switch($data['type']) {
		case 'text': ?>
			<div class="range"<?php if($data['displayType']!='range') echo ' style="display:none;"'; ?>>
			минимум: <input type="text" name="shopSetting[min][<?=$data['id']?>]" value="<?=$data['min']?>" />
			максимум: <input type="text" name="shopSetting[max][<?=$data['id']?>]" value="<?=$data['max']?>" />
			</div>
			<?php
			break;
		}
	}
	public function actionWidgetFeatureSearchSubmit($data) {
		$out=array('feature'=>array());
		if(isset($data['price'])) $out['price']=true; else $out['price']=false;
		if(isset($data['brand'])) $out['brand']=true; else $out['brand']=false;
		foreach($data['checked'] as $featureId) {
			$displayType=$data['type'][$featureId];
			$feature=array('type'=>$displayType);
			if($displayType=='range') {
				$feature['min']=(int)$data['min'][$featureId];
				$feature['max']=(int)$data['max'][$featureId];
			}
			$out['feature'][$featureId]=$feature;
		}
		return $out;
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- PRIVATE ---------------------------------------------------------------- */
/* ----------------------------------------------------------------------------------- */
}
?>