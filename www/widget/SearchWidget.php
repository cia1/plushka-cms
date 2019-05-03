<?php
namespace plushka\widget;
use plushka;

/* Текстовое поле "поиск по сайту" */
class SearchWidget extends \plushka\core\Widget {

	public function __invoke() {
		plushka::language('search');
		if(isset($_GET['keyword'])) $this->keyword=$_GET['keyword']; else $this->keyword='';
		return true;
	}

	public function render($view) { ?>
		<form action="<?=plushka::link('search')?>">
			<input type="text" name="search[keyword]" value="<?=$this->keyword?>" placeholder="<?=LNGFind?>" class="input" id="searchKeyword" />
			<input type="submit" value="<?=LNGFind?>" class="button" />
		</form>
		<?php
	}

}