<?php
/* Библиотека с часто используемыми функциями модуля "catalog" */
class catalog {

	/* Выводит HTML-представление одного из полей каталога. $data содержит описание поля и его значение */
	public static function render($data) {
		if(isset($data['type'])) $type=$data['type']; else $type=$data['layout']['type']; //тип поля
		switch($type) {
		case 'integer': case 'float': case 'string': case 'list': case 'text': //простой текст
			self::renderText($data['value']);
			break;
		case 'image':
			self::renderImage($data['value']); //изображение
			break;
		case 'galleryWithoutThumbnail':
			self::renderGalleryWithoutThumbnail($data['value']); //много изображений (галерея) без миниатюр
			break;
		case 'galleryWithThumbnail':
			self::renderGalleryWithThumbnail($data['value']); //много изображений (галерея) с миниатюрами
			break;
		case 'boolean':
			self::renderBoolean($data['value']); //"да" или "нет"
			break;
		default:
			echo $data['type'].' IS NOT EMPLEMENTED (/model/catalog.php)!';
		}
	}

	public static function renderText($value) {
		echo $value;
	}

	public static function renderImage($value) {
		echo '<img src="'.$value.'" alt="" />';
	}

	public static function renderGalleryWithoutThumbnail($data) {
		if(!$data) return;
		$data=explode('|',$data);
		$url=core::url().'public/catalog/';
		echo '<br /><img src="'.$url.implode('" alt="" /><img src="'.$url,$data).'" alt="" />';
	}

	public static function renderGalleryWithThumbnail($data) {
		static $_index=0;
		$_index++;
		if(!$data) return;
		$data=explode('|',$data);
		$url=core::url().'public/catalog/';
		core::widget('shadowbox');
		echo '<br />';
		foreach($data as $item) { ?>
			<a href="<?=$url.$item?>" rel="shadowbox[gal-<?=$_index?>]">
				<img src="<?=$url?>_<?=$item?>" alt="" />
			</a>
		<?php }
	}

	public static function renderBoolean($value) {
		if($value==1) echo LNGyes; else echo LNGno;
	}


}
?>