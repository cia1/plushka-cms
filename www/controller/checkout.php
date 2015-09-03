<?php
/* Корзина и оформление заказа
	ЧПУ: /checkout/ (actionIndex) - страница корзины и формы оформления заказа
	/checkout/checkout (actionCheckout) - обработка формы оформления заказа
	/checkout/success (actionSuccess) - сообщение после успешной отправки заказа
*/
class sController extends controller {

	/* Корзина с формой оформления заказа */
	public function actionIndex() {
		if(isset($_GET['delete'])) unset($_SESSION['cart'][$_GET['delete']]);
		core::import('model/form');
		$cfg=core::config('shop');
		$this->form=new mForm($cfg['formId']);

		$this->script('jquery.min');
		$this->script('jquery.form');
		$this->pageTitle=$this->metaTitle=LNGCart;
		return 'Index';
	}

	public function adminIndexLink() {
		$cfg=core::config('shop');
		return array(
			array('shopSetting.setting','?controller=form&action=form&id='.$cfg['formId'],'setting','Настройка формы'),
			array('shopSetting.setting','?controller=form&action=field&id='.$cfg['formId'],'field','Управление полями формы')
		);
	}

	/* Вывод списка товаров в корзине (для AJAX-запросов), MVC-представление в файле /view/cart.phh */
	public function actionCart() {
		if(isset($_GET['delete'])) unset($_SESSION['cart'][$_GET['delete']]);
		core::import('view/cart');
	}

	/* Обновление количества товаров в корзине */
	public function actionCartSubmit($data) {
		foreach($data['quantity'] as $id=>$value) {
			$value=(int)$value;
			if(!$value) unset($_SESSION['cart'][$id]); else $_SESSION['cart'][$id]['quantity']=$value;
		}
		if($_SERVER['SCRIPT_NAME']==core::url().'index.php') core::redirect('checkout');
	}

	protected function breadcrumbIndex() {
		return array('<a href="'.core::link('shop/category').'">'.LNGShop.'</a>');
	}

	public function actionCheckout() {
		return $this->actionIndex();
	}

	/* Обработка формы заказа */
	public function actionCheckoutSubmit($data) {
		if(!count($_SESSION['cart'])) core::redirect('checkout');
		core::import('model/form');
		$m=new mForm();
		$cfg=core::config('shop');
		if(!$m->execute($cfg['formId'],$data)) return false;
	}

	/* Сообщение об успехе по умолчанию */
//	public function actionSuccess() { return 'Success'; }

//	protected function breadcrumbSuccess() {
//		return array('Магазин');
//	}

}
?>