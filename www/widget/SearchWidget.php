<?php
namespace plushka\widget;
use plushka\core\plushka;
use plushka\core\Widget;

/**
 * Текстовое поле "поиск по сайту"
 */
class SearchWidget extends Widget {

	private $_keyword;

	public function __invoke(): bool {
		plushka::language('search');
		if(isset($_GET['keyword'])===true) $this->_keyword=$_GET['keyword']; else $this->_keyword='';
		return true;
	}

	public function render($view): void { ?>
      <form action="<?=plushka::link('search')?>">
          <input type="text" name="search[keyword]" value="<?=$this->_keyword?>" placeholder="<?=LNGFind?>"
                 class="input" id="searchKeyword"/>
          <input type="submit" value="<?=LNGFind?>" class="button"/>
      </form>
		<?php
	}

}
