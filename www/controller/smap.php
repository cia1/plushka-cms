<?php
/* Генерирует карту сайта, .htaccess перенаправляет сюда запрос /sitemap.xml.
Скрипты для каждого из модулей находятся в директории /sitemap, каждый из этих скриптов должен вывести XML-код. */
class sController extends controller {

	/* Главный XML-файл карты сайта */
	public function actionIndex() {
		header("Content-Type: text/xml; Charset=UTF-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
		$d=opendir(core::path().'sitemap');
		while($f=readdir($d)) {
			if($f=='.' || $f=='..') continue;
			$i=strrpos($f,'.');
			if(substr($f,$i+1)!='php') continue;
			$f=substr($f,0,$i);
			echo '<sitemap><loc>http://'.$_SERVER['HTTP_HOST'].core::url().'index2.php?controller=smap&amp;action=render&amp;id='.$f."</loc></sitemap>\n";
		}
		echo '</sitemapindex>';
		exit;
	}

	/* Генерирует XML-код для каждого модуля */
	public function actionRender() {
		header("Content-Type: text/xml; Charset=UTF-8");
		if(!ctype_alnum($_GET['id'])) core::error404();
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
		include(core::path().'sitemap/'.$_GET['id'].'.php');
		echo '</urlset>';
		exit;
	}

}