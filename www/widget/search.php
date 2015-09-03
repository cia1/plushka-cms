<?php
/* Текстовое поле "поиск по сайту" */
class widgetSearch extends widget {

	public function __invoke() { return true; }

	public function render() {
		if(isset($_GET['keyword'])) $keyword=$_GET['keyword']; else $keyword='';
		?>
		<form action="<?=core::link('search')?>">
			<input type="text" name="search[keyword]" value="<?=$keyword?>" placeholder="<?=LNGFind?>" class="input" id="searchKeyword" />
			<input type="submit" value="<?=LNGFind?>" class="button" />
		</form>
		<?php
	}

}
?>