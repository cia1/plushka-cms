<?php
//Возвращает строку HTML-чекбоксов меню (вызывается рекурсивно). Сугубо генерация HTML, поэтому в MVC-представлении
function getHTMLMenu($data,$level=0) {
	$html='';
	foreach($data as $item) {
		if(isset($item['menuTitle'])) $html.='<b>'.$item['menuTitle'].'</b><br />';
		$html.=str_repeat('&nbsp;',$level*11).'<input type="checkbox" class="checkbox" name="section[url]['.$item['link'].'][1]" title="на этй странице"'.($item['checked1'] ? ' checked="checked"' : '').' />'
		.'<input type="checkbox" class="checkbox" name="section[url]['.$item['link'].'][2]" title="на вложенных страницах"'.($item['checked2'] ? ' checked="checked"' : '').' /> '
		.$item['title'].'<br />';
		$html.=getHTMLMenu($item['child'],$level+1);
	}
	return $html;
}
$f=core::form();
$f->hidden('id',$this->data['id']);
$f->hidden('name',$this->data['name'],'id="widgetName"');
$f->hidden('data',null,'id="widgetData"');
$f->hidden('section',$this->data['section'],'id="widgetSection"');
$f->hidden('cache','','id="cacheTime"');
$f->label('Секция',$this->data['section']);
$f->text('title','Название',$this->data['title'],'id="widgetTitle"');
$f->checkbox('publicTitle','Публиковать название виджета',$this->data['publicTitle']);
//Сформировать список чекбоксов
$f->html('<dt>Страницы</dt><dd id="_admPageCheck" style="height:auto;">'.getHTMLMenu($this->pageMenu).'</dd>');
$f->text('url2','другие URL (через запятую)',implode($this->pageOther,', '),'id="url2"');
$f->render('section/widget&section='.$_GET['section']);
?>
<div id="_type"><h3>Выберите тип виджета:</h3>
<?php
if(!$this->data['name']) {
	$s=$controller='';
	foreach($this->type as $item) {
		if($controller!=$item['controller']) {
			if($controller) $s.='</div>';
			$s.='<div style="width:33%;float:left;">';
			$controller=$item['controller'];
		}
		$s.='<p onclick="loadForm(\''.$item['name'].'\',\''.$item['controller'].'\',\''.$item['action'].'\');">'.$item['title'].'</p>';
	}
	echo $s;
	echo '</div>';
}
	?>
</div>
<script>
function loadForm(name,controller,action) {
	jQuery('#widgetName').val(name);
	var url='<?=core::url()?>admin/index2.php?controller='+controller+'&action='+action<?php if(isset($_GET['_front'])) echo '+"&_front"'; ?>+"&section=<?=$this->data['section']?>&_serialize";
	jQuery('#_type').load(url,{'data':'<?=$this->data['data']?>'},function() {
			var f=$('#_type form');
			var action=f.attr('action')+'&_serialize';
			f.attr('action',action);
			f.ajaxForm(function(data) {
				if(!$('#widgetTitle').val()) {
					_showError('Обязательно укажите заголовок (название) виджета');
					$('#widgetTitle').focus();
					return;
				}
				data1=data.split("\n");
				if(data1[0]!='OK') _showError(data);
				else {
					document.getElementById('cacheTime').value=data1[1];
					$('#widgetData').val(data1[2]);
					$('form[name=section]').submit();
				}
			});
	});
}
jQuery('form[name=section]').submit(function() {
	if(!jQuery('#widgetName').val()) return false;
	if(!jQuery('#_admPageCheck input:checked').length && !$('#url2').val()) {
		alert('Необходимо выбрать хотя бы одну страницу, на которой будет публиковаться виджет');
		return false;
	}
});
<?php if($this->data['name']) echo 'loadForm("'.$this->data['name'].'","'.$this->data['controller'].'","'.$this->data['action'].'");'; ?>
</script>
<cite>В блоке <b>Страницы</b> отметье те страницы, на которых должен публиковаться виджет.<br />
Вложенные страницы - страницы, находящиеся "глубже" указанной, например если есть такая структура: &laquo;Главная &rarr; Новости &rarr; Первая новость&raquo;, то "Первая новость" является вложенной по отношению к "Новости". Если у пункта "Новости" отмечено "на этой странице" и не отмечено "на вложенных страницах", то виджет будет публиковаться в разделе "Новости", но не будет на странице конкретной новости.<br />
В поле <b>другие URL</b> впишите страницы (через запятую), на которых должен публиковаться виджет, но которых нет в списке выше. В конце каждой страницы нужно поставить символ &laquo;.&raquo; (только на текущей странице), &laquo;*&raquo; (только на вложенных страницах) или &laquo;/&raquo; (на текущей и вложенных страницах). Например: "shop/cat1/,article/view/rules.,some/link/3*"</cite>