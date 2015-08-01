<iframe style="display:block;width:95%;height:250px;margin:0 auto;border:1px solid black;" src="<?=core::url()?>admin/index2.php?controller=map&action=map&centerLatitude=<?=$this->data['centerLatitude']?>&centerLongitude=<?=$this->data['centerLongitude']?>&zoom=<?=$this->data['zoom']?>&type=<?=$this->data['type']?>"></iframe>
<br /><br />
<fieldset id="markerForm" style="display:none;">
	<legend>Выбранная метка</legend>
	<?php $this->formMarker->render(); ?>
</fieldset>

<p><br /></p>
<?php $this->formMap->render(null,'onsubmit="this[\'map[marker]\'].value=mapMarker.getJson();"'); ?>
<script>
$('iframe').load(function() {
	<?php foreach($this->data['marker'] as $item) { ?>
	mapMarker.add(false,{title:"<?=$item['title']?>",latitude:<?=$item['latitude']?>,longitude:<?=$item['longitude']?>});
	<?php } ?>
});
</script>