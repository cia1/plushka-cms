<?php return array(
'depend'=>'form ver 1.1',
'right'=>'shopContent.brand,shopContent.category,shopSetting.feature,shopContent.variant,shopSetting.setting,shopSetting.import',
'widget'=>'shopProductGroup,shopCart,shopCategory,shopFeatureSearch',
'menu'=>'5',
'table'=>'shpCategory,shpBrand,shpFeature,shpFeatureGroup,shpProduct,shpProductFeature,shpProductGroup,shpProductGroupItem,shpVariant',
'file'=>array(
	'admin/config/shop.php',
	'admin/controller/shopContent.php',
	'admin/controller/shopSetting.php',
	'admin/data/email/shopOrder.html',
	'admin/model/excel_reader2.php',
	'admin/model/shop.php',
	'admin/model/shopImport.php',
	'admin/public/css/shop.css',
	'admin/public/icon/brand16.png', /* !!! */
	'admin/public/icon/brand32.png',
	'admin/public/icon/productNew16.png',
	'admin/public/icon/shopVariant16.png',
	'admin/public/icon/shopSetting16.png',
	'admin/public/js/shop.js',
  'admin/public/shop-minus.png',
  'admin/public/shop-plus.png',
	'admin/view/shopContentProductImage.php',
	'admin/view/shopContentProductList.php',
	'admin/view/shopSettingCategoryFeature.php',
	'admin/view/shopSettingFeatureList.php',
	'admin/view/shopSettingImportEnd.php',
	'admin/view/shopSettingImportLoad.php',
	'admin/view/shopSettingImportStart.php',
	'admin/view/shopSettingSetting.php',
	'admin/view/shopSettingWidgetFeatureSearch.php',
	'config/shop.php',
	'controller/checkout.php',
	'controller/shop.php',
	'data/email/shopOrder.html',
	'data/shopAfter.php',
	'hook/search.shop.php',
	'model/shop.php',
	'public/shop-brand/',
	'public/shop-category/',
	'public/shop-product/',
	'sitemap/shop.php',
	'view/cart.php',
	'view/checkoutIndex.php',
	'view/checkoutSuccess.php',
	'view/shopCategory.php',
	'view/shopProduct.php',
	'widget/shopCart.php',
	'widget/shopCategory.php',
	'widget/shopFeatureSearch.php',
	'widget/shopProductGroup.php'
)
); ?>