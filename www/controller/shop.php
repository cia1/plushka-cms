<?php
/* Интернет-магазин (кроме оформления заказа)
	ЧПУ: /shop - главная страница магазина ("верхняя категория")
	/shop//псевдоним_категории (actionCategory) - категория
	/shop/псевдоним_категории/псевдоним_товара (actionProduct) - страница товара
*/
class sController extends controller {

	public function __construct($action) {
		parent::__construct();
		if($this->url[1]=='Index') $this->categoryAlias=null;
		else $this->categoryAlias=$_GET['corePath'][1];
		if(count($this->url)==2) $this->url[1]='Category'; else $this->url[1]='Product';
		core::language('shop');
	}

	public function actionIndex() { core::error404(); }

	/* Категория интренет-магазина */
	public function actionCategory() {
		//Если нет categoryAlias, то это "верхняя" категория
		if(!$this->categoryAlias) $this->category=array('id'=>0,'parentId'=>0,'title'=>'Каталог товаров','text1'=>'','metaTitle'=>'','metaKeyword'=>'','metaDescription'=>'');
		else {
			$db=core::db();
			$this->category=$db->fetchArrayOnceAssoc('SELECT id,parentId,title,text1,metaTitle,metaKeyword,metaDescription FROM shpCategory WHERE alias='.$db->escape($this->categoryAlias));
			if(!$this->category) core::error404();
		}

		core::import('model/shop');
		$this->categoryList=shop::categoryList($this->category['id']); //$this->categoryList содержит список категорий
		if($this->category['id']) {
			if(isset($_GET['sort'])) {
				if($_GET['sort']=='dateASC') $sort='id ASC'; elseif($_GET['sort']=='dateDESC') $sort='id DESC';
				else $sort=str_replace(array('ASC','DESC'),array(' ASC', ' DESC'),$_GET['sort']);
			} else $sort='id DESC';
			$this->productList=shop::productCategory($this->category['id']); //$this->productList содержит список товаров
			$this->paginationCount=shop::foundRows();
			$cfg=core::config('shop');
			$this->productOnPage=$cfg['productOnPage'];
			if($sort!='title ASC') $this->sort=' <a href="?sort=titleASC&'.$link.'" class="sortA">'.LNGname.' &darr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort=' <span class="sortC">'.LNGname.' &darr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='title DESC') $this->sort.=' <a href="?sort=titleDESC&'.$link.'" class="sortB">'.LNGname.' &uarr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.=' <span class="sortD">'.LNGname.' &uarr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='price ASC') $this->sort.=' <a href="?sort=priceASC&'.$link.'" class="sortA">'.LNGprice.' &darr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.=' <span class="sortC">'.LNGprice.' &darr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='price DESC') $this->sort.=' <a href="?sort=priceDESC&'.$link.'" class="sortB">'.LNGprice.' &uarr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.=' <span class="sortD">'.LNGprice.' &uarr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='id ASC') $this->sort.='<a href="?sort=dateASC&'.$link.'" class="sortA">'.LNGdate.' &darr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.='<span class="sortC">'.LNGdate.' &darr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='id DESC') $this->sort.='<a href="'.core::link('shop/category/'.$this->category['id'].'?'.$link).'" class="sortB">'.LNGdate.' &uarr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.='<span class="sortD">'.LNGdate.' &uarr;</span>&nbsp;&nbsp;&nbsp;';
		} else {
			$this->productList=null;
			$this->sort='';
		}

		if($this->category['metaTitle']) $this->metaTitle=$this->category['metaTitle']; else $this->metaTitle=$this->category['title'];
		$this->metaKeyword=$this->category['metaKeyword'];
		$this->metaDescription=$this->category['metaDescription'];
		if($this->category['id']==0) $this->pageTitle=''; else $this->pageTitle=$this->category['title'];
		return 'Category';
	}

	protected function breadcrumbCategory() {
		if(!isset($this->categoryAlias)) return array(LNGShop);
		return array('<a href="'.core::link('shop').'">'.LNGShop.'</a>');
	}

	protected function adminCategoryLink() {
		$data=array();
		if($this->category['id']!=0) {
			$data[]=array('shopContent.category','?controller=shopContent&action=category&id='.$this->category['id'],'edit','Редактировать категорию &laquo;'.$this->category['title'].'&raquo;');
			$data[]=array('shopContent.category','?controller=shopContent&action=categoryDelete&id='.$this->category['id'],'delete','Удалить категорию &laquo;'.$this->category['title'].'&raquo;','Удалить','if(!confirm(\'Подтвердите удаление.\')) return false;');
		}
		$data[]=array('shopContent.product','?controller=shopContent&action=productList&id='.$this->category['id'],'list','Открыть список товаров');
		$data[]=array('shopContent.category','?controller=shopContent&action=category&parent='.$this->category['id'],'new','Добавить категорию в &laquo;'.$this->category['title'].'&raquo;');
		if($this->category['id']!=0) $data[]=array('shopContent.product','?controller=shopContent&action=product&category='.$this->category['id'],'productNew','Добавить новый товар в категорию &laquo;'.$this->category['title'].'&raquo;');
		$data[]=array('shopSetting.feature','?controller=shopSetting&action=categoryFeature&id='.$this->category['id'],'layout','Характеристики товаров этой категории');
		return $data;
	}

	/* Страница товара */
	public function actionProduct() {
		core::import('model/shop');
		$this->product=shop::productByAlias($this->url[2],true); //полная информация о товаре, $this->url[3] - идентификатор товара
		if(!$this->product) core::error404();

		$this->js('jquery.min');
		$this->js('shadowbox/shadowbox');
		$this->style('../js/shadowbox/shadowbox');
		if($this->product['metaTitle']) $this->metaTitle=$this->product['metaTitle']; else $this->metaTitle=$this->product['title'];
		$this->pageTitle=$this->product['title'];
		$this->metaKeyword=$this->product['metaKeyword'];
		$this->metaDescription=$this->product['metaDescription'];
		return 'Product';
	}

	protected function breadcrumbProduct() {
		$db=core::db();
		return array('<a href="'.core::link('shop').'">'.LNGShop.'</a>','<a href="'.core::link('shop/'.$this->categoryAlias).'">'.$this->product['categoryTitle'].'</a>');
	}

	protected function adminProductLink() {
		return array(
			array('shopContent.product','?controller=shopContent&action=product&id='.$this->product['id'],'edit','Редактировать товар &laquo;'.$this->product['title'].'&raquo;'),
			array('shopContent.product','?controller=shopContent&action=productDelete&id='.$this->product['id'],'delete','Удалить товар &laquo;'.$this->product['title'].'&raquo;','Удалить','if(!confirm(\'Подтвердите удаление.\')) return false;'),
			array('shopContent.product','?controller=shopContent&action=productImage&id='.$this->product['id'],'image','Управление изображениями'),
			array('shopContent.product','?controller=shopContent&action=productMove&id='.$this->product['id'],'move','Перенести в другую категорию'),
			array('shopContent.variant','?controller=shopContent&action=variant&pid='.$this->product['id'],'shopVariant','Варианты (модификации) товара')
		 );
	}

	public function actionVariant() {}

	/* Добавляет товар в корзину, обращение AJAX-запросом */
	public function actionAddToCartSubmit($data) {
		$db=core::db();
		$data['quantity']=(int)$data['quantity'];
		$product=$db->fetchArrayOnce('SELECT title,price,categoryId,alias FROM shpProduct WHERE id='.(int)$data['id']);
		if(!$product || !$data['quantity']) die(LNGProductDoesntExists);
		if(!isset($_SESSION['cart'])) $_SESSION['cart']=array();
		if(isset($_SESSION['cart'][$data['id']])) $_SESSION['cart'][$data['id']]['quantity']+=$data['quantity'];
		else $_SESSION['cart'][$data['id']]=array('alias'=>$product[3],'title'=>$product[0],'categoryId'=>$product[2],'quantity'=>$data['quantity'],'price'=>$product[1]);
		die(LNGProductAddedToCart);
	}

}
?>
