<div class="tab">
	<?php $this->form1->render(); ?>
	<script type="text/javascript">
	$('dd.productFullWidthType select').change(function(a) {
		if(this.value=='') $('.productFullWidth').hide(); else $('.productFullWidth').show();
	}).change();
	$('dd.productFullHeightType select').change(function(a) {
		if(this.value=='') $('.productFullHeight').hide(); else $('.productFullHeight').show();
	}).change();
	$('dd.productThumbWidthType select').change(function(a) {
		if(this.value=='') $('.productThumbWidth').hide(); else $('.productThumbWidth').show();
	}).change();
	$('dd.productThumbHeightType select').change(function(a) {
		if(this.value=='') $('.productThumbHeight').hide(); else $('.productThumbHeight').show();
	}).change();
	$('dd.categoryWidthType select').change(function(a) {
		if(this.value=='') $('.categoryWidth').hide(); else $('.categoryWidth').show();
	}).change();
	$('dd.categoryHeightType select').change(function(a) {
		if(this.value=='') $('.categoryHeight').hide(); else $('.categoryHeight').show();
	}).change();
	$('dd.brandWidthType select').change(function(a) {
		if(this.value=='') $('.brandWidth').hide(); else $('.brandWidth').show();
	}).change();
	$('dd.brandHeightType select').change(function(a) {
		if(this.value=='') $('.brandHeight').hide(); else $('.brandHeight').show();
	}).change();
	</script>

	<fieldset><legend>Группы товаров</legend>
		<?php $this->productGroup->render(); ?><br />
		<h3 id="groupHeading">Новая группа</h3>
		<?php
		$f=core::form();
		$f->hidden('id','','id="groupId"');
		$f->text('title','Название группы','','id="groupTitle"');
		$f->reset('Очистить','id="groupReset"');
		$f->submit('Продолжить');
		$f->render('shop&action=productGroup');
		?>
		<script type="text/javascript">
		function productGroup(id,title) {
			document.getElementById('groupId').value=id;
			document.getElementById('groupTitle').value=title;
			document.getElementById('groupHeading').innerHTML='Редактирование группы товаров &laquo;'+title+'&raquo;';
			return false;
		}
		jQuery('#groupReset').click(function() {
			document.getElementById('groupId').value='';
			document.getElementById('groupHeading').innerHTML='Новая группа';
		});
		</script>
	</fieldset>
</div>
<script type="text/javascript">
	setTimeout(function() { $('.tab').tab(); },200);
</script>