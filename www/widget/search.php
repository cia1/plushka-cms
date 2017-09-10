<?php
/* Текстовое поле "поиск по сайту" */
class widgetSearch extends widget {

	public function __invoke() {
		core::language('search');
		if(isset($_GET['keyword'])) $this->keyword=$_GET['keyword']; else $this->keyword='';
		return true;
	}

	public function render($view=null) { ?>
		<form action="<?=core::link('search')?>">
			<input type="text" name="search[keyword]" value="<?=$this->keyword?>" placeholder="<?=LNGFind?>" class="input" id="searchKeyword" />
			<input type="submit" value="<?=LNGFind?>" class="button" />
		</form>
		<?php
	}

}