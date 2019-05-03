<?php
namespace plushka\controller;
use plushka;
use plushka\model\Form;

/* Корзина и оформление заказа
	ЧПУ: /checkout/ (actionIndex) - страница корзины и формы оформления заказа
	/checkout/checkout (actionCheckout) - обработка формы оформления заказа
	/checkout/success (actionSuccess) - сообщение после успешной отправки заказа
*/
class CheckoutController extends \plushka\core\Controller {

	public function __construct() {
		parent::__construct();
		plushka::language('shop');
	}

	/* Корзина с формой оформления заказа */
	public function actionIndex() {
		if(isset($_GET['delete'])) unset($_SESSION['cart'][$_GET['delete']]);
		$cfg=plushka::config('shop');
		$this->form=new Form($cfg['formId']);

		$this->js('jquery.min');
		$this->js('jquery.form');
		$this->pageTitle=$this->metaTitle=LNGCart;
		return 'Index';
	}

	public function adminIndexLink() {
		$cfg=plushka::config('shop');
		return array(
			array('shopSetting.setting','?controller=form&action=form&id='.$cfg['formId'],'setting','Настройка формы'),
			array('shopSetting.setting','?controller=form&action=field&id='.$cfg['formId'],'field','Управление полями формы')
		);
	}

	/* Вывод списка товаров в корзине (для AJAX-запросов), MVC-представление в файле /view/cart.phh */
	public function actionCart() {
		if(isset($_GET['delete'])) unset($_SESSION['cart'][$_GET['delete']]);
		plushka::import('view/cart');
	}

	/* Обновление количества товаров в корзине */
	public function actionCartSubmit($data) {
		foreach($data['quantity'] as $id=>$value) {
			$value=(int)$value;
			if(!$value) unset($_SESSION['cart'][$id]); else $_SESSION['cart'][$id]['quantity']=$value;
		}
		if($_SERVER['SCRIPT_NAME']==plushka::url().'index.php') plushka::redirect('checkout');
	}

	protected function breadcrumbIndex() {
		return array('<a href="'.plushka::link('shop/category').'">'.LNGShop.'</a>','{{pageTitle}}');
	}

	public function actionCheckout() {
		return $this->actionIndex();
	}

	/* Обработка формы заказа */
	public function actionCheckoutSubmit($data) {
		if(!count($_SESSION['cart'])) plushka::redirect('checkout');
		$m=new Form();
		$cfg=plushka::config('shop');
		if(!$m->execute($cfg['formId'],$data)) return false;
	}

}