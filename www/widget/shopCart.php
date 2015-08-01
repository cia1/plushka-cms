<?php
/* Корзина
array $options: bool product - выводить или нет список товаров в корзинце; bool total - выводить или нет итоговые сумму и количество;
bool checkout - ссылка на страницу оформления заказа */
class widgetShopCart extends widget {

	public function __invoke() { return true; }

	public function render() {
		if(!$this->options) $this->options=array();
		$this->options=array_merge(array('product'=>true,'total'=>true,'checkout'=>true),$this->options); //значения по умолчанию
		echo '<div id="cart">';
		if(!isset($_SESSION['cart']) || !count($_SESSION['cart'])) echo 'Корзина пуста';
		else {
			$total=0;
			echo '<ul>';
			foreach($_SESSION['cart'] as $item) {
				$total+=$item['quantity']*$item['price'];
				if($this->options['product']) echo '<li>'.$item['title'].' <span class="quantity">'.$item['quantity'].'</span><span class="price">'.$item['price'].' руб.</li>';
			}
			echo '</ul>';
			if($this->options['total']) echo '<p>Итого: '.$total.' руб.</p>';
			if($this->options['checkout']) {
				echo '<p><a href="'.core::link('checkout').'">Оформить заказ</a></p>';
			}
		}
		echo '</div>';
	}

}
?>