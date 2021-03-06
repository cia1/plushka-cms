<?php
/* Корзина
array $options: bool product - выводить или нет список товаров в корзинце; bool total - выводить или нет итоговые сумму и количество;
bool checkout - ссылка на страницу оформления заказа */
class widgetShopCart extends widget {

	public function __invoke() {
		core::language('shop');
		return true;
	}

	public function render($view) {
		if(!$this->options) $this->options=array();
		$this->options=array_merge(array('product'=>true,'total'=>true,'checkout'=>true),$this->options); //значения по умолчанию
		echo '<div id="cart">';
		if(!isset($_SESSION['cart']) || !count($_SESSION['cart'])) echo LNGCartEmpty;
		else {
			$total=0;
			echo '<ul>';
			foreach($_SESSION['cart'] as $item) {
				$total+=$item['quantity']*$item['price'];
				if($this->options['product']) echo '<li>'.$item['title'].' <span class="quantity">'.$item['quantity'].'</span><span class="price">'.$item['price'].' '.LNGcurrency.'</li>';
			}
			echo '</ul>';
			if($this->options['total']) echo '<p>'.LNGTotal.': '.$total.' '.LNGcurrency.'</p>';
			if($this->options['checkout']) {
				echo '<p><a href="'.core::link('checkout').'">'.LNGCheckout.'</a></p>';
			}
		}
		echo '</div>';
	}

}
?>