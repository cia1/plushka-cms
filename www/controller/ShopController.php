<?php
namespace plushka\controller;
use plushka;
use plushka\model\Shop;

/* Интернет-магазин (кроме оформления заказа)
	ЧПУ: /shop - главная страница магазина ("верхняя категория")
	/shop//псевдоним_категории (actionCategory) - категория
	/shop/псевдоним_категории/псевдоним_товара (actionProduct) - страница товара
*/
class ShopController extends \plushka\core\Controller {

	public function __construct() {
		parent::__construct();
		plushka::language('shop');
		if($this->url[1]==='addToCart') return;
		if($this->url[1]==='index') $this->categoryAlias=null;
		else $this->categoryAlias=$_GET['corePath'][1];
		if(count($this->url)===2) $this->url[1]='category'; else $this->url[1]='product';
	}

	public function actionIndex() { plushka::error404(); }

	/* Категория интренет-магазина */
	public function actionCategory() {
		//Если нет categoryAlias, то это "верхняя" категория
		if(!$this->categoryAlias) $this->category=array('id'=>0,'parentId'=>0,'title'=>'Каталог товаров','text1'=>'','metaTitle'=>'','metaKeyword'=>'','metaDescription'=>'');
		else {
			$db=plushka::db();
			$this->category=$db->fetchArrayOnceAssoc('SELECT id,parentId,title,text1,metaTitle,metaKeyword,metaDescription FROM shp_category WHERE alias='.$db->escape($this->categoryAlias));
			if(!$this->category) plushka::error404();
		}

		$this->categoryList=Shop::categoryList($this->category['id']); //$this->categoryList содержит список категорий
		if($this->category['id']) {
			if(isset($_GET['sort'])) {
				if($_GET['sort']=='dateASC') $sort='id ASC'; elseif($_GET['sort']=='dateDESC') $sort='id DESC';
				else $sort=str_replace(array('ASC','DESC'),array(' ASC', ' DESC'),$_GET['sort']);
			} else $sort='id DESC';
			$this->productList=Shop::productCategory($this->category['id']); //$this->productList содержит список товаров
			$this->paginationCount=Shop::foundRows();
			$cfg=plushka::config('shop');
			$this->productOnPage=$cfg['productOnPage'];
			if($sort!='title ASC') $this->sort=' <a href="?sort=titleASC'.'" class="sortA">'.LNGname.' &darr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort=' <span class="sortC">'.LNGname.' &darr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='title DESC') $this->sort.=' <a href="?sort=titleDESC'.'" class="sortB">'.LNGname.' &uarr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.=' <span class="sortD">'.LNGname.' &uarr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='price ASC') $this->sort.=' <a href="?sort=priceASC'.'" class="sortA">'.LNGprice.' &darr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.=' <span class="sortC">'.LNGprice.' &darr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='price DESC') $this->sort.=' <a href="?sort=priceDESC'.'" class="sortB">'.LNGprice.' &uarr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.=' <span class="sortD">'.LNGprice.' &uarr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='id ASC') $this->sort.='<a href="?sort=dateASC'.'" class="sortA">'.LNGdate.' &darr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.='<span class="sortC">'.LNGdate.' &darr;</span>&nbsp;&nbsp;&nbsp;';
			if($sort!='id DESC') $this->sort.='<a href="'.plushka::link('shop/category/'.$this->category['id']).'" class="sortB">'.LNGdate.' &uarr;</a>&nbsp;&nbsp;&nbsp;'; else $this->sort.='<span class="sortD">'.LNGdate.' &uarr;</span>&nbsp;&nbsp;&nbsp;';
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
		return array('<a href="'.plushka::link('shop').'">'.LNGShop.'</a>','{{pageTitle}}');
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
		$this->product=Shop::productByAlias($this->url[2],true); //полная информация о товаре, $this->url[3] - идентификатор товара
		if(!$this->product) plushka::error404();

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
		$db=plushka::db();
		return array('<a href="'.plushka::link('shop').'">'.LNGShop.'</a>','<a href="'.plushka::link('shop/'.$this->categoryAlias).'">'.$this->product['categoryTitle'].'</a>','{{pageTitle}}');
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
		$db=plushka::db();
		$data['quantity']=(int)$data['quantity'];
		$product=$db->fetchArrayOnce('SELECT p.title,p.price,p.alias,c.alias link FROM shp_product p LEFT JOIN shp_category c ON c.id=p.categoryId WHERE p.id='.(int)$data['id']);
		if(!$product || !$data['quantity']) die(LNGProductDoesntExists);
		if(!isset($_SESSION['cart'])) $_SESSION['cart']=array();
		if(isset($_SESSION['cart'][$data['id']])) $_SESSION['cart'][$data['id']]['quantity']+=$data['quantity'];
		else $_SESSION['cart'][$data['id']]=array('title'=>$product[0],'price'=>$product[1],'quantity'=>$data['quantity'],'link'=>plushka::link('shop/'.$product[3].'/'.$product[2]));
		die(LNGProductAddedToCart);
	}

}
?>
