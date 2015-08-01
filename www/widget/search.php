<?php
/* Текстовое поле "поиск по сайту" */
class widgetSearch extends widget {

	public function __invoke() { return true; }

	public function render() {
		if(isset($_GET['keyword'])) $keyword=$_GET['keyword']; else $keyword='Поиск...';
		?>
		<form action="<?=core::link('search')?>" onsubmit="if(document.getElementById('searchKeyword').value=='Поиск...') return false;">
			<input type="text" name="search[keyword]" value="<?=$keyword?>" class="input" onfocus="if(this.value=='Поиск...') this.value='';" onblur="if(!this.value) this.value='Поиск...';" id="searchKeyword" />
			<input type="submit" value="Найти" class="button" />
		</form>
		<?php
	}

}
?>