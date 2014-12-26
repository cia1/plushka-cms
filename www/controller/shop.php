<?php
/* Интернет-магазин (кроме оформления заказа)
	ЧПУ: /shop - главная страница магазина
	/shop/category (actionCategory) - "верхняя" категория интернет-магазина
	/shop/category/ИД (actionCategory) - категория
	/shop/category/ИД/псевдоним (actionProduct) - страница товара
*/
class sController extends controller {

	public function __construct($action) {
		parent::__construct();
		if($action=='category') {
			if(isset($this->url[3])) $this->url[1]='product';
			if(!isset($this->url[2])) $this->url[2]=0; else $this->url[2]=(int)$this->url[2];
		}
	}

	public function actionIndex() { core::error404(); }

	/* Категория интренет-магазина */
	public function actionCategory() {
		if($this->url[2]!=0) { //если ИД категории не задан, то это "верхняя" категория
			$db=core::db();
			$this->category=$db->fetchArrayOnceAssoc('SELECT id,parentId,title,text1,metaTitle,metaKeyword,metaDescription FROM shpCategory WHERE id='.$this->url[2]);
			if(!$this->category) core::error404();
		} else $this->category=array('id'=>0,'parentId'=>0,'title'=>'Каталог товаров','text1'=>'','metaTitle'=>'','metaKeyword'=>'','metaDescription'=>'');
		core::import('model/shop');
		$this->categoryList=shop::categoryList($this->url[2]); //$this->categoryList содержит список категорий
		if($this->category['id']) {
			if(isset($_GET['sort'])) {
				if($_GET['sort']=='dateASC') $sort='id ASC'; elseif($_GET['sort']=='dateDESC') $sort='id DESC';
				else $sort=str_replace(array('ASC','DESC'),array(' ASC', ' DESC'),$_GET['sort']);
			} else $sort='id DESC';
			$this->productList=shop::productCategory($this->category['id']); //$this->productList содержит список товаров
			if($sort!='title ASC') $this->sort=' <a href="?sort=titleASC&'.$link.'" class="sortA">имя &darr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort=' <span class="sortC">имя &darr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='title DESC') $this->sort.=' <a href="?sort=titleDESC&'.$link.'" class="sortB">имя &uarr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.=' <span class="sortD">имя &uarr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='price ASC') $this->sort.=' <a href="?sort=priceASC&'.$link.'" class="sortA">цена &darr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.=' <span class="sortC">цена &darr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='price DESC') $this->sort.=' <a href="?sort=priceDESC&'.$link.'" class="sortB">цена &uarr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.=' <span class="sortD">цена &uarr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='id ASC') $this->sort.='<a href="?sort=dateASC&'.$link.'" class="sortA">дата &darr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.='<span class="sortC">дата &darr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='id DESC') $this->sort.='<a href="'.core::link('shop/category/'.$this->category['id'].'?'.$link).'" class="sortB">дата &uarr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.='<span class="sortD">дата &uarr;</span>&nbsp;&nbsp;&nbsp;';
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
		if(!$this->url[2]) return array('Магазин');
		return array('<a href="'.core::link('shop/category').'">Магазин</a>');
	}

	protected function adminCategoryLink() {
		$data=array();
		if($this->category['id']!=0) {
			$data[]=array('shop.category','?controller=shop&action=category&id='.$this->category['id'],'edit','Редактировать категорию &laquo;'.$this->category['title'].'&raquo;');
			$data[]=array('shop.category','?controller=shop&action=categoryDelete&id='.$this->category['id'],'delete','Удалить категорию &laquo;'.$this->category['title'].'&raquo;','Удалить','if(!confirm(\'Подтвердите удаление.\')) return false;');
		}
		$data[]=array('shop.category','?controller=shop&action=category&parent='.$this->category['id'],'new','Добавить категорию в &laquo;'.$this->category['title'].'&raquo;');
		if($this->category['id']!=0) $data[]=array('shop.product','?controller=shop&action=product&category='.$this->category['id'],'productNew','Добавить новый товар в категорию &laquo;'.$this->category['title'].'&raquo;');
		$data[]=array('shop.feature','?controller=shop&action=categoryFeature&id='.$this->category['id'],'layout','Характеристики товаров этой категории');
		return $data;
	}

	/* Страница товара */
	public function actionProduct() {
		core::import('model/shop');
		$this->product=shop::productByAlias($this->url[3],true); //полная информация о товаре, $this->url[3] - идентификатор товара
		if(!$this->product) core::error404();

		$this->script('jquery.min');
		$this->script('shadowbox/shadowbox');
		$this->style('../js/shadowbox/shadowbox');
		if($this->product['metaTitle']) $this->metaTitle=$this->product['metaTitle']; else $this->metaTitle=$this->product['title'];
		$this->pageTitle=$this->product['title'];
		$this->metaKeyword=$this->product['metaKeyword'];
		$this->metaDescription=$this->product['metaDescription'];
		return 'Product';
	}

	protected function breadcrumbProduct() {
		$db=core::db();
		return array('<a href="'.core::link('shop/category').'">Магазин</a>','<a href="'.core::link('shop/category/'.$this->url[2]).'">'.$this->product['categoryTitle'].'</a>');
	}

	protected function adminProductLink() {
		return array(
			array('shop.product','?controller=shop&action=product&id='.$this->product['id'],'edit','Редактировать товар &laquo;'.$this->product['title'].'&raquo;'),
			array('shop.product','?controller=shop&action=productDelete&id='.$this->product['id'],'delete','Удалить товар &laquo;'.$this->product['title'].'&raquo;','Удалить','if(!confirm(\'Подтвердите удаление.\')) return false;'),
			array('shop.product','?controller=shop&action=productImage&id='.$this->product['id'],'image','Управление изображениями'),
			array('shop.product','?controller=shop&action=productMove&id='.$this->product['id'],'move','Перенести в другую категорию'),
			array('shop.variant','?controller=shop&action=variant&pid='.$this->product['id'],'shopVariant','Варианты (модификации) товара')
		 );
	}

	public function actionVariant() {}

	/* Добавляет товар в корзину, обращение AJAX-запросом */
	public function actionAddToCartSubmit($data) {
		$db=core::db();
		$data['quantity']=(int)$data['quantity'];
		$product=$db->fetchArrayOnce('SELECT title,price,categoryId,alias FROM shpProduct WHERE id='.(int)$data['id']);
		if(!$product || !$data['quantity']) die('Товар не существует');
		if(!isset($_SESSION['cart'])) $_SESSION['cart']=array();
		if(isset($_SESSION['cart'][$data['id']])) $_SESSION['cart'][$data['id']]['quantity']+=$data['quantity'];
		else $_SESSION['cart'][$data['id']]=array('alias'=>$product[3],'title'=>$product[0],'categoryId'=>$product[2],'quantity'=>$data['quantity'],'price'=>$product[1]);
		die('OK');
	}

}
?>