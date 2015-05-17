<table cellpadding="0" cellspacing="0" border="0"><tr><td>
<p>Все характеристики:</p>
<select id="feature1" size="2">
<?php foreach($this->feature1 as $id=>$data) {
echo '<option value="#'.$id.'" class="head">'.$data['title'].'</option>';
	foreach($data['data'] as $item) {
		echo '<option value="'.$item['id'].'" class="g'.$id.'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$item['title'].'</option>';
	}
} ?>
</select>
</td><td style="width:80px;text-align:center;">
<input type="button" value="  >>  " onclick="featureMove(1,2);" /><br /><br />
<input type="button" value="  <<  " onclick="featureMove(2,1);" />
</td><td>
<p>Выбранные характеристики:</p>
<select id="feature2" size="2">
<?php foreach($this->feature2 as $id=>$data) {
echo '<option value="#'.$id.'" class="head">'.$data['title'].'</option>';
	foreach($data['data'] as $item) {
		echo '<option value="'.$item['id'].'" class="g'.$id.'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$item['title'].'</option>';
	}
} ?>
</select>

</td><td style="padding-left:15px;vertical-align:top;"><cite>Переместите в правую колонку нужные характеристики. Товары в данной категории будут обладать выбранными характеристиками.<br />Вы можете организовать глобальный поиск по характеристикам, если одни и те же характеристики будут привязаны к разным категориям.<br /><br /><b>Внимание!</b> Удалённые на этой странице характеристики будут также удалены у ВСЕХ товаров в данной категории!</cite></td></tr></table>
<style type="text/css">
#feature1,#feature2 {height:300px;width:300px;}
#feature1 {float:left;}
#feature2 {float:right;}
option.head {background:#eee;}
</style>

<form action="<?=core::link('?controller=shopSetting&action=categoryFeature')?>" method="post" onSubmit="featureCategorySubmit();">
<input type="hidden" name="shopSetting[categoryId]" value="<?=$this->id?>" />
<input type="hidden" name="shopSetting[feature]" value="" id="featureField" />
<input type="submit" class="button" value="Продолжить" style="margin-top:20px;" />
</form>