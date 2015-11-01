<?php
//Форма с основными данными
$f=core::form();
$f->hidden('menuId',$this->data['menuId']);
$f->hidden('typeId',$this->data['typeId'],'id="typeId"'); //Обновляется при выборе какого-либо типа (для новых пунктов меню)
$f->hidden('id',$this->data['id']);
$f->hidden('link',$this->data['link'],'id="menuLink"'); //Ссылка будет погружена сюда после обработки формы модуля
$f->select('parentId','Родительское меню','SELECT id,title_'._LANG.' FROM menuItem WHERE menuId='.$this->data['menuId'].' AND parentId=0 ORDER BY sort',$this->data['parentId'],' ( нет ) ');
$f->text('title_'._LANG,'Заголовок ссылки в меню',$this->data['title'],'id="menuTitle"');
$f->label('Ссылка',$this->data['link'],'id="menuUrl"');
$f->render();
?>
<hr style="clear:both;width:90%;margin:0 auto;border:none;border-bottom:1px solid #eee;margin-bottom:10px;" />
<div id="_type"><h3>Выберите тип создаваемой страницы:</h3>
<?php
//Если новый пункт меню, то вывести список типов
if(!$this->data['type']) {
	$s=$controller='';
	foreach($this->type as $item) {
		if($controller!=$item['controller']) {
			if($controller) $s.='</div>';
			$s.='<div style="width:33%;float:left;">';
			$controller=$item['controller'];
		}
		$s.='<p onclick="loadForm('.$item['id'].',\''.$item['controller'].'\',\''.$item['action'].'\');">'.$item['title'].'</p>';
	}
	echo $s.'</div>';
}
?>
<?php if(!$this->data['type']) echo '<cite>На этом этапе укажите сущность (тип страницы), которая должна открываться при переходе по ссылке меню (статья, блог, внешняя ссылка и т.д.)</cite>'; ?>
</div>

<script>
function loadForm(id,controller,action) {
	jQuery('#typeId').val(id);
	var url='<?=core::url()?>admin/index2.php?controller='+controller+'&action='+action+'&_lang=<?=_LANG?>'<?php if(isset($_GET['_front'])) echo '+"&_front"'; ?>+"&link=<?=urlencode($this->data['link'])?>";
	$('#_type').load(url,function(data) { //После загрузки формы модуля
		setTimeout(function() {
			var f=$('#_type form');
			var action=f.attr('action')+'&_serialize';
			f.attr('action',action);
			f.ajaxForm(function(data) { //После нажатия submit формы модуля
				if(!$('#menuTitle').val()) {
					_showError('Обязательно укажите заголвок пункта меню');
					$('#menuTitle').focus();
					return;
				}
				data1=data.split("\n");
				if(data1[0]!='OK') _showError(data);
				else {
					$('#menuLink').val(data1[2]);
					$('form[name=menu]').submit(); //Отправить форму пункта меню
				}
			});
		},51);
	});
	jQuery('#menuUrl').html('<?=core::url()?>'+controller);
}
jQuery('form[name=menu]').submit(function() {
	if(!jQuery('#menuLink').val()) return false;
});
<?php if($this->data['type']) echo 'loadForm('.$this->data['type'][0].',"'.$this->data['type'][1].'","'.$this->data['type'][2].'");'; ?>
</script>