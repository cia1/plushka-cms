<?php
namespace plushka\widget;
use plushka\core\plushka;
use plushka\core\Widget;

/* Текстовое поле "поиск по сайту" */
class SearchWidget extends Widget {

	public function __invoke() {
		plushka::language('search');
		if(isset($_GET['keyword'])) $this->keyword=$_GET['keyword']; else $this->keyword='';
		return true;
	}

	public function render($view): void { ?>
		<form action="<?=plushka::link('search')?>">
			<input type="text" name="search[keyword]" value="<?=$this->keyword?>" placeholder="<?=LNGFind?>" class="input" id="searchKeyword" />
			<input type="submit" value="<?=LNGFind?>" class="button" />
		</form>
		<?php
	}

}
