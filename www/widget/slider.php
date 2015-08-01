<?php
/* Слайдер */
class widgetSlider extends widget {

	public static $index;

	public function __invoke() {
		$this->index++;
		echo core::script('jquery.min');
		echo core::script('slider');
		echo '<link href="'.core::url().'public/css/slider.css" rel="stylesheet" type="text/css" media="all" />';
		$this->cfg=core::config('slider-'.$this->options['id']);
		return 'Slider'.$this->options['view'];
	}

	public function adminLink() {
		return array(
			array('slider.*','?controller=slider&action=image&id='.$this->options['id'],'image','Упраление слайдами')
		);
	}

}
?>