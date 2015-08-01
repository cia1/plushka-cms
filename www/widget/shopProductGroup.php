<?php
/* Товары в группе
int $options - идентификатор группы товаров */
class widgetShopProductGroup extends widget {

	public function __invoke() { return true; }

	public function render() {
		core::import('model/shop');
		$items=shop::productGroup($this->options);
		foreach($items as $item) {
			$url=core::url().'public/shop-product/_';
			echo '<div class="item">
			<a href="'.$item['link'].'"><p class="title">'.$item['title'].'</p>
			<img src="'.$url.$item['mainImage'].'" alt="'.$item['title'].'" /></a>
			<span class="price">'.$item['price'].' руб.</span>
			</div>';
		}
	}

}
?>