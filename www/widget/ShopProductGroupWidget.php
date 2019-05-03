<?php
namespace plushka\widget;
use plushka;
use plushka\model\shop;

/* Товары в группе
int $options - идентификатор группы товаров */
class ShopProductGroupWidget extends \plushka\core\Widget {

	public function __invoke() {
		plushka::language('shop');
		return true;
	}

	public function render($view) {
		$items=shop::productGroup($this->options);
		foreach($items as $item) {
			$url=plushka::url().'public/shop-product/_';
			echo '<div class="item">
			<a href="'.$item['link'].'"><p class="title">'.$item['title'].'</p>
			<img src="'.$url.$item['mainImage'].'" alt="'.$item['title'].'" /></a>
			<span class="price">'.$item['price'].' '.LNGcurrency.'</span>
			</div>';
		}
	}

}