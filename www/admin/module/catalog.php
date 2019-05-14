<?php
return array(
'depend'=>'shadowbox ver 1.0',
'right'=>'catalog.layout,catalog.item',
'menu'=>'11',
'file'=>array(
	'admin/controller/CatalogController.php',
	'admin/hook/menuItemDelete.catalog.php',
	'admin/public/css/catalog.css',
	'admin/public/js/catalog.js',
	'admin/view/catalogFieldAjax.php',
	'admin/view/catalogLayoutView.php',
	'admin/view/catalogWidgetSearch.php',
	'config/catalogLayout/',
	'controller/CatalogController.php',
	'hook/search.catalog.php',
	'model/Catalog.php',
	'public/catalog/',
	'sitemap/catalog.php',
	'view/catalogList.php',
	'view/catalogView.php',
	'view/widgetCatalogSearch.php',
	'widget/CatalogSearchWidget.php'
 ),
'widget'=>'catalogSearch'
);